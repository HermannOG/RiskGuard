<?php
/**
 * Contrato que debe cumplir cualquier adaptador de motor de base de
 * datos para el Monitor de Salud (patron Adapter). Cada motor (MariaDB,
 * Oracle, Postgres, SQL Server...) implementa esta misma interfaz a su
 * manera -- el resto del sistema (MonitorRepository) no sabe ni le
 * importa de que motor vinieron los numeros.
 */
interface MonitorAdapterInterface
{
    /**
     * Devuelve las 10 lecturas crudas del instante actual, con las
     * mismas claves que existen en monitor_variables (p1..p4, m1..m3,
     * a1..a3).
     *
     * @return array<string,float>
     */
    public function obtenerLecturas(): array;
}