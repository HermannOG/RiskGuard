<?php
/**
 * Motor de prueba de estres para instancias Oracle del Monitor de Salud.
 *
 * Genera carga REAL contra un database link ya existente, para poder ver
 * como cambian los indices (procesos/memoria/archivos) al capturar
 * mientras la base esta bajo presion. La carga vive DENTRO de Oracle como
 * jobs de DBMS_SCHEDULER en segundo plano -- no en el proceso PHP -- para
 * que sigan corriendo entre una peticion y la siguiente (el servidor PHP
 * atiende una peticion a la vez), y asi puedas capturar varias veces
 * durante la prueba.
 *
 * GARANTIA DE "dejar la base igual que antes":
 *   - Los jobs SOLO hacen lecturas a traves del link (SELECT COUNT(*)
 *     FROM all_objects@LINK). Nunca DML, ni DDL, ni cambios de parametros.
 *   - Cada job se crea con auto_drop => TRUE: al terminar su tiempo se
 *     borra solo. detenerYLimpiar() ademas fuerza el borrado de cualquier
 *     job que siga y cierra la sesion distribuida del link.
 *   - No crea tablas ni deja rastro fuera de los jobs temporales
 *     RISKGUARD_STRESS_*.
 *
 * Requiere que el usuario Oracle del monitor tenga el privilegio CREATE
 * JOB (directo o via rol) y que el database link ya exista.
 */
class OracleStress
{
    /** Prefijo de nombre de todos los jobs que crea esta clase. */
    private const PREFIJO_JOB = 'RISKGUARD_STRESS_';

    /** @var resource conexion oci8 */
    private $conn;

    public function __construct($ociConnection)
    {
        $this->conn = $ociConnection;
    }

    /**
     * Lista los database links visibles para el usuario actual
     * (ALL_DB_LINKS). Devuelve solo los nombres, ordenados.
     *
     * @return string[]
     */
    public function listarDbLinks(): array
    {
        $stmt = oci_parse($this->conn, 'SELECT db_link FROM all_db_links ORDER BY db_link');
        oci_execute($stmt);
        $links = [];
        while ($fila = oci_fetch_row($stmt)) {
            $links[] = $fila[0];
        }
        oci_free_statement($stmt);
        return $links;
    }

    /**
     * Valida que el nombre del database link sea un identificador Oracle
     * seguro. Como el nombre del link NO se puede pasar por bind (es un
     * identificador, no un valor), se interpola en el PL/SQL; esta
     * validacion es lo que evita inyeccion. Se permiten letras, digitos,
     * _ $ # y . (links calificados por dominio, ej. MILINK.WORLD).
     */
    public static function nombreLinkValido(string $link): bool
    {
        return $link !== ''
            && strlen($link) <= 128
            && preg_match('/^[A-Za-z0-9_$#.]+$/', $link) === 1;
    }

    /**
     * Lanza la prueba de estres: crea $sesiones jobs, cada uno haciendo
     * lecturas en bucle a traves del link durante $segundos segundos.
     *
     * @return int cantidad de jobs efectivamente creados
     */
    public function iniciar(string $dbLink, int $sesiones, int $segundos): int
    {
        if (!self::nombreLinkValido($dbLink)) {
            throw new InvalidArgumentException('Nombre de database link no valido: ' . $dbLink);
        }
        $sesiones = max(1, min($sesiones, 50));
        $segundos = max(1, min($segundos, 600));

        // Por si quedaron jobs de una corrida anterior, limpiamos primero.
        $this->detenerYLimpiar();

        // Bloque que ejecutara cada job: lee del link en bucle hasta que
        // se cumpla el tiempo. GET_TIME esta en centesimas de segundo.
        // El nombre del link ya esta validado; se cita entre comillas
        // dobles para respetar su forma exacta.
        $accion = sprintf(
            'DECLARE
                 v_fin NUMBER := DBMS_UTILITY.GET_TIME + (%d * 100);
                 v_n   NUMBER;
             BEGIN
                 WHILE DBMS_UTILITY.GET_TIME < v_fin LOOP
                     EXECUTE IMMEDIATE ' . "'" . 'SELECT COUNT(*) FROM all_objects@"%s"' . "'" . ' INTO v_n;
                 END LOOP;
             END;',
            $segundos,
            $dbLink
        );

        $creados = 0;
        for ($n = 1; $n <= $sesiones; $n++) {
            $jobName = self::PREFIJO_JOB . $n;
            $stmt = oci_parse($this->conn,
                'BEGIN
                     DBMS_SCHEDULER.CREATE_JOB(
                         job_name   => :jobname,
                         job_type   => ' . "'" . 'PLSQL_BLOCK' . "'" . ',
                         job_action => :accion,
                         start_date => SYSTIMESTAMP,
                         enabled    => TRUE,
                         auto_drop  => TRUE);
                 END;'
            );
            oci_bind_by_name($stmt, ':jobname', $jobName);
            oci_bind_by_name($stmt, ':accion', $accion);
            if (@oci_execute($stmt)) {
                $creados++;
            } else {
                $e = oci_error($stmt);
                oci_free_statement($stmt);
                // Si falla el primero, casi siempre es falta de privilegio
                // CREATE JOB: limpiamos lo que se haya creado y avisamos.
                $this->detenerYLimpiar();
                throw new RuntimeException(
                    'No se pudo crear el job de estres (revisa el privilegio CREATE JOB): '
                    . ($e['message'] ?? 'error desconocido')
                );
            }
            oci_free_statement($stmt);
        }
        return $creados;
    }

    /**
     * Estado actual de la prueba: cuantos jobs de estres existen y
     * cuantos estan corriendo en este instante.
     *
     * @return array{total:int, corriendo:int}
     */
    public function estado(): array
    {
        return [
            'total'     => $this->contar('user_scheduler_jobs'),
            'corriendo' => $this->contar('user_scheduler_running_jobs'),
        ];
    }

    private function contar(string $vista): int
    {
        $stmt = oci_parse($this->conn,
            "SELECT COUNT(*) FROM {$vista} WHERE job_name LIKE :patron"
        );
        $patron = self::PREFIJO_JOB . '%';
        oci_bind_by_name($stmt, ':patron', $patron);
        oci_execute($stmt);
        $fila = oci_fetch_row($stmt);
        oci_free_statement($stmt);
        return $fila ? (int) $fila[0] : 0;
    }

    /**
     * Detiene y elimina TODOS los jobs de estres, y cierra la sesion del
     * database link para no dejar rastro. Deja la base exactamente como
     * estaba antes de la prueba. Es idempotente: llamarla sin jobs no
     * hace nada.
     *
     * @return int jobs que quedaron sin poder borrar (0 = limpieza total)
     */
    public function detenerYLimpiar(): int
    {
        // Detiene (forzado) y borra cada job de estres. Cada operacion va
        // en su propio bloque con manejo de excepcion para que un job ya
        // terminado no aborte la limpieza de los demas.
        $limpieza = oci_parse($this->conn,
            'BEGIN
                 FOR r IN (SELECT job_name FROM user_scheduler_jobs
                           WHERE job_name LIKE ' . "'" . self::PREFIJO_JOB . '%' . "'" . ') LOOP
                     BEGIN
                         DBMS_SCHEDULER.STOP_JOB(r.job_name, force => TRUE);
                     EXCEPTION WHEN OTHERS THEN NULL;
                     END;
                     BEGIN
                         DBMS_SCHEDULER.DROP_JOB(r.job_name, force => TRUE);
                     EXCEPTION WHEN OTHERS THEN NULL;
                     END;
                 END LOOP;
             END;'
        );
        @oci_execute($limpieza);
        oci_free_statement($limpieza);

        // Cierra cualquier sesion distribuida abierta por los links, para
        // que no quede una conexion remota colgando tras la prueba.
        $cerrar = oci_parse($this->conn,
            'BEGIN
                 FOR r IN (SELECT db_link FROM v$dblink) LOOP
                     BEGIN
                         EXECUTE IMMEDIATE ' . "'" . 'ALTER SESSION CLOSE DATABASE LINK ' . "'" . ' || r.db_link;
                     EXCEPTION WHEN OTHERS THEN NULL;
                     END;
                 END LOOP;
             END;'
        );
        @oci_execute($cerrar);
        oci_free_statement($cerrar);

        return $this->contar('user_scheduler_jobs');
    }
}
