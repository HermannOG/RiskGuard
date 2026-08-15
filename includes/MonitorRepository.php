<?php
/**
 * Repositorio del Monitor de Salud.
 *
 * NOTA IMPORTANTE: el calculo de indices (normalizar + ponderar + ISBD +
 * semaforo) originalmente se diseno como un procedimiento almacenado SQL
 * (ver database/sp_calcular_indices_monitor.sql, probado y funcional en
 * MariaDB local). Ese procedimiento NO se pudo desplegar en InfinityFree
 * porque su plan gratuito no otorga el privilegio CREATE ROUTINE.
 *
 * Este metodo replica exactamente la MISMA logica SQL (mismo JOIN, mismo
 * CASE de normalizacion, misma agregacion ponderada) pero enviada como
 * una consulta directa via PDO en vez de un procedimiento guardado.
 */
class MonitorRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function calcularIndices(int $instanciaId, string $capturadoEn): array
    {
        $sql = "
            SELECT
                v.componente,
                SUM(
                    CASE v.tipo_normalizacion
                        WHEN 'directo' THEN LEAST(l.valor, 100)
                        WHEN 'inverso' THEN LEAST(GREATEST(100 - l.valor, 0), 100)
                        WHEN 'limite'  THEN LEAST(l.valor / NULLIF(v.limite_default, 0) * 100, 100)
                    END * v.peso_default
                ) / NULLIF(SUM(v.peso_default), 0) AS indice_ponderado
            FROM monitor_lecturas l
            JOIN monitor_variables v ON v.id = l.variable_id
            WHERE l.instancia_id = :instancia_id
              AND l.capturado_en = :capturado_en
            GROUP BY v.componente
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['instancia_id' => $instanciaId, 'capturado_en' => $capturadoEn]);

        $porComponente = ['procesos' => 0.0, 'memoria' => 0.0, 'archivos' => 0.0];
        foreach ($stmt->fetchAll() as $fila) {
            $porComponente[$fila['componente']] = (float) $fila['indice_ponderado'];
        }

        $ip = $porComponente['procesos'];
        $im = $porComponente['memoria'];
        $ia = $porComponente['archivos'];
        $isbd = ($ip * 0.25) + ($im * 0.60) + ($ia * 0.15);
        $estado = $this->determinarEstado($instanciaId, $isbd);

        $stmtInsert = $this->pdo->prepare("
            INSERT INTO monitor_indices
                (instancia_id, capturado_en, indice_procesos, indice_memoria, indice_archivos, indice_salud, estado)
            VALUES
                (:instancia_id, :capturado_en, :ip, :im, :ia, :isbd, :estado)
        ");
        $stmtInsert->execute([
            'instancia_id' => $instanciaId,
            'capturado_en' => $capturadoEn,
            'ip'           => $ip,
            'im'           => $im,
            'ia'           => $ia,
            'isbd'         => $isbd,
            'estado'       => $estado,
        ]);

        return [
            'indice_procesos' => round($ip, 2),
            'indice_memoria'  => round($im, 2),
            'indice_archivos' => round($ia, 2),
            'indice_salud'    => round($isbd, 2),
            'estado'          => $estado,
        ];
    }

    public function determinarEstado(int $instanciaId, float $isbd): string
    {
        $stmt = $this->pdo->prepare("
            SELECT estado
            FROM monitor_umbrales
            WHERE (instancia_id = :instancia_id OR instancia_id IS NULL)
              AND :isbd BETWEEN valor_min AND valor_max
            ORDER BY (instancia_id IS NOT NULL) DESC
            LIMIT 1
        ");
        $stmt->execute(['instancia_id' => $instanciaId, 'isbd' => $isbd]);
        $fila = $stmt->fetch();

        return $fila['estado'] ?? 'rojo';
    }

    public function registrarLectura(int $instanciaId, string $capturadoEn, string $variableId, float $valor): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO monitor_lecturas (instancia_id, capturado_en, variable_id, valor)
            VALUES (:instancia_id, :capturado_en, :variable_id, :valor)
        ");
        $stmt->execute([
            'instancia_id' => $instanciaId,
            'capturado_en' => $capturadoEn,
            'variable_id'  => $variableId,
            'valor'        => $valor,
        ]);
    }
    public function obtenerDetalleLecturas(int $instanciaId, string $capturadoEn, string $lang = 'es'): array
        {
            $colNombre = $lang === 'en' ? 'nombre_en' : 'nombre';
            $colDescripcion = $lang === 'en' ? 'descripcion_en' : 'descripcion';
            $colVerde = $lang === 'en' ? 'banda_verde_en' : 'banda_verde';
            $colAmarillo = $lang === 'en' ? 'banda_amarillo_en' : 'banda_amarillo';
            $colAnaranjado = $lang === 'en' ? 'banda_anaranjado_en' : 'banda_anaranjado';
            $colRojo = $lang === 'en' ? 'banda_rojo_en' : 'banda_rojo';

            $sql = "
                SELECT
                    l.variable_id,
                    v.componente,
                    v.{$colNombre} AS nombre,
                    v.{$colDescripcion} AS descripcion,
                    v.{$colVerde} AS banda_verde,
                    v.{$colAmarillo} AS banda_amarillo,
                    v.{$colAnaranjado} AS banda_anaranjado,
                    v.{$colRojo} AS banda_rojo,
                    l.valor AS valor_crudo,
                    CASE v.tipo_normalizacion
                        WHEN 'directo' THEN LEAST(l.valor, 100)
                        WHEN 'inverso' THEN LEAST(GREATEST(100 - l.valor, 0), 100)
                        WHEN 'limite'  THEN LEAST(l.valor / NULLIF(v.limite_default, 0) * 100, 100)
                    END AS valor_normalizado
                FROM monitor_lecturas l
                JOIN monitor_variables v ON v.id = l.variable_id
                WHERE l.instancia_id = :instancia_id
                  AND l.capturado_en = :capturado_en
                ORDER BY v.componente, l.variable_id
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['instancia_id' => $instanciaId, 'capturado_en' => $capturadoEn]);

            $detalle = [];
            foreach ($stmt->fetchAll() as $fila) {
                $fila['valor_crudo'] = (float) $fila['valor_crudo'];
                $fila['valor_normalizado'] = (float) $fila['valor_normalizado'];
                $fila['estado'] = $this->determinarEstado($instanciaId, $fila['valor_normalizado']);
                $detalle[] = $fila;
            }
            return $detalle;
        }
}