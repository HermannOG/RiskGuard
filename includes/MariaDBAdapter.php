<?php
require_once __DIR__ . '/MonitorAdapterInterface.php';

/**
 * Adaptador de MariaDB/MySQL para el Monitor de Salud.
 * Traduce el contrato generico (p1..a3) a las consultas especificas de
 * este motor (information_schema, SHOW GLOBAL STATUS). No hace ningun
 * calculo ni normalizacion -- solo trae numeros crudos.
 */
class MariaDBAdapter implements MonitorAdapterInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerLecturas(): array
    {
        return [
            'p1' => $this->contarProcesos(),
            'p2' => $this->contarSesionesActivas(),
            'p3' => $this->contarSesionesBloqueadas(),
            'p4' => $this->contarOperacionesProlongadas(),
            'm1' => $this->porcentajeUsoBufferPool(),
            'm2' => $this->presionMemoria(),
            'm3' => $this->cacheHitRatio(),
            'a1' => $this->archivosFueraDeLinea(),
            'a2' => $this->espacioUsadoMB(),
            'a3' => $this->archivosConProblemas(),
        ];
    }

    private function contarProcesos(): float
    {
        return (float) $this->pdo->query(
            "SELECT COUNT(*) FROM information_schema.PROCESSLIST"
        )->fetchColumn();
    }

    private function contarSesionesActivas(): float
    {
        return (float) $this->pdo->query(
            "SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE COMMAND != 'Sleep'"
        )->fetchColumn();
    }

    private function contarSesionesBloqueadas(): float
    {
        return (float) $this->pdo->query(
            "SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE STATE LIKE '%lock%'"
        )->fetchColumn();
    }

    private function contarOperacionesProlongadas(int $umbralSegundos = 5): float
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE COMMAND != 'Sleep' AND TIME > :umbral"
        );
        $stmt->execute(['umbral' => $umbralSegundos]);
        return (float) $stmt->fetchColumn();
    }

    private function porcentajeUsoBufferPool(): float
    {
        $filas = $this->pdo->query(
            "SHOW GLOBAL STATUS WHERE Variable_name IN ('Innodb_buffer_pool_pages_data','Innodb_buffer_pool_pages_total')"
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        $total = (float) ($filas['Innodb_buffer_pool_pages_total'] ?? 0);
        $usadas = (float) ($filas['Innodb_buffer_pool_pages_data'] ?? 0);

        return $total > 0 ? round($usadas / $total * 100, 2) : 0.0;
    }

    private function presionMemoria(): float
    {
        $espera = (float) $this->pdo->query(
            "SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_wait_free'"
        )->fetch(PDO::FETCH_NUM)[1] ?? 0;

        return min($espera, 100);
    }

    private function cacheHitRatio(): float
    {
        $filas = $this->pdo->query(
            "SHOW GLOBAL STATUS WHERE Variable_name IN ('Innodb_buffer_pool_read_requests','Innodb_buffer_pool_reads')"
        )->fetchAll(PDO::FETCH_KEY_PAIR);

        $solicitudes = (float) ($filas['Innodb_buffer_pool_read_requests'] ?? 0);
        $lecturasDisco = (float) ($filas['Innodb_buffer_pool_reads'] ?? 0);

        if ($solicitudes <= 0) {
            return 100.0;
        }
        return round((1 - ($lecturasDisco / $solicitudes)) * 100, 2);
    }

    private function archivosFueraDeLinea(): float
    {
        return (float) $this->pdo->query(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema NOT IN ('information_schema','mysql','performance_schema','sys')
               AND engine IS NULL"
        )->fetchColumn();
    }

    private function espacioUsadoMB(): float
    {
        return (float) $this->pdo->query(
            "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2)
             FROM information_schema.tables"
        )->fetchColumn();
    }

    private function archivosConProblemas(): float
    {
        return $this->archivosFueraDeLinea();
    }
}