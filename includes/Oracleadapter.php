<?php
require_once __DIR__ . '/MonitorAdapterInterface.php';

/**
 * Adaptador de Oracle para el Monitor de Salud.
 * Traduce el contrato generico (p1..a3) a las vistas dinamicas de
 * Oracle (V$SESSION, V$PROCESS, V$SGA, V$SYSSTAT, V$DATAFILE,
 * DBA_FREE_SPACE...). No hace ningun calculo ni normalizacion -- solo
 * trae numeros crudos, igual que MariaDBAdapter.
 *
 * Requiere la extension oci8 de PHP + Oracle Instant Client instalados
 * en el servidor donde corre el sitio.
 */
class OracleAdapter implements MonitorAdapterInterface
{
    private $conn; // resource oci8

    public function __construct($ociConnection)
    {
        $this->conn = $ociConnection;
    }

    public function obtenerLecturas(): array
    {
        return [
            'p1' => $this->contarProcesos(),
            'p2' => $this->contarSesionesActivas(),
            'p3' => $this->contarSesionesBloqueadas(),
            'p4' => $this->contarOperacionesProlongadas(),
            'm1' => $this->porcentajeUsoBufferCache(),
            'm2' => $this->presionMemoria(),
            'm3' => $this->cacheHitRatio(),
            'a1' => $this->archivosFueraDeLinea(),
            'a2' => $this->porcentajeEspacioLibre(),
            'a3' => $this->archivosConProblemas(),
        ];
    }

    /**
     * Ejecuta una consulta y devuelve el primer valor de la primera
     * fila como float. Util para todas las consultas de agregacion
     * (COUNT, SUM, etc.) de este adaptador.
     */
    private function consultarValor(string $sql, array $binds = []): float
    {
        $stmt = oci_parse($this->conn, $sql);
        foreach ($binds as $nombre => $valor) {
            oci_bind_by_name($stmt, $nombre, $binds[$nombre]);
        }
        oci_execute($stmt);
        $fila = oci_fetch_row($stmt);
        oci_free_statement($stmt);

        return $fila && isset($fila[0]) ? (float) $fila[0] : 0.0;
    }

    private function contarProcesos(): float
    {
        return $this->consultarValor("SELECT COUNT(*) FROM v\$process");
    }

    private function contarSesionesActivas(): float
    {
        return $this->consultarValor(
            "SELECT COUNT(*) FROM v\$session WHERE status = 'ACTIVE' AND type != 'BACKGROUND'"
        );
    }

    private function contarSesionesBloqueadas(): float
    {
        return $this->consultarValor(
            "SELECT COUNT(*) FROM v\$session WHERE blocking_session IS NOT NULL"
        );
    }

    private function contarOperacionesProlongadas(int $umbralSegundos = 5): float
    {
        // v$session no tiene un equivalente 1:1 a TIME de MariaDB.
        // Usamos last_call_et (segundos desde la ultima llamada) sobre
        // sesiones activas como aproximacion de "consulta corriendo
        // hace rato".
        return $this->consultarValor(
            "SELECT COUNT(*) FROM v\$session
             WHERE status = 'ACTIVE' AND type != 'BACKGROUND' AND last_call_et > :umbral",
            ['umbral' => $umbralSegundos]
        );
    }

    private function porcentajeUsoBufferCache(): float
    {
        $usado = $this->consultarValor(
            "SELECT bytes FROM v\$sgainfo WHERE name = 'Buffer Cache Size'"
        );
        $maximo = $this->consultarValor(
            "SELECT bytes FROM v\$sgainfo WHERE name = 'Maximum SGA Size'"
        );

        return $maximo > 0 ? round($usado / $maximo * 100, 2) : 0.0;
    }

    private function presionMemoria(): float
    {
        // Proxy de presion de memoria: porcentaje de esperas por
        // buffers libres/ocupados sobre el total de eventos de espera
        // del sistema. Cuanto mas alto, mas presion.
        $esperasMemoria = $this->consultarValor(
            "SELECT SUM(total_waits) FROM v\$system_event
             WHERE event IN ('free buffer waits', 'buffer busy waits')"
        );
        $esperasTotales = $this->consultarValor(
            "SELECT SUM(total_waits) FROM v\$system_event"
        );

        return $esperasTotales > 0 ? round($esperasMemoria / $esperasTotales * 100, 2) : 0.0;
    }

    private function cacheHitRatio(): float
    {
        // Cache hit ratio clasico de Oracle: 1 - (lecturas fisicas /
        // lecturas logicas). Se devuelve el valor "directo" (mas alto
        // = mejor salud); la normalizacion inversa/directa segun
        // corresponda la aplica MonitorRepository con la config de
        // monitor_variables, igual que con MariaDBAdapter.
        $lecturaLogica = $this->consultarValor(
            "SELECT value FROM v\$sysstat WHERE name = 'session logical reads'"
        );
        $lecturaFisica = $this->consultarValor(
            "SELECT value FROM v\$sysstat WHERE name = 'physical reads'"
        );

        if ($lecturaLogica <= 0) {
            return 0.0;
        }

        $ratio = (1 - ($lecturaFisica / $lecturaLogica)) * 100;
        return round(max(0, min(100, $ratio)), 2);
    }

    private function archivosFueraDeLinea(): float
    {
        return $this->consultarValor(
            "SELECT COUNT(*) FROM v\$datafile WHERE status NOT IN ('ONLINE', 'SYSTEM')"
        );
    }

    private function porcentajeEspacioLibre(): float
    {
        $libre = $this->consultarValor("SELECT NVL(SUM(bytes), 0) FROM dba_free_space");
        $total = $this->consultarValor("SELECT NVL(SUM(bytes), 0) FROM dba_data_files");

        return $total > 0 ? round($libre / $total * 100, 2) : 0.0;
    }

    private function archivosConProblemas(): float
    {
        return $this->consultarValor(
            "SELECT COUNT(*) FROM v\$datafile WHERE status = 'RECOVER' OR enabled = 'READ ONLY'"
        );
    }
}
