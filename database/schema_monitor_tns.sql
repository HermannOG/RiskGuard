-- =====================================================================
-- Soporte de tnsnames.ora para instancias Oracle del Monitor de Salud.
--
-- Guarda el ALIAS TNS elegido al registrar la instancia (ej. 'XEPDB1'),
-- no el host/puerto/service_name a mano. host/puerto/nombre_bd se
-- siguen guardando también, pero solo como el último valor conocido:
-- en cada carga de la pantalla de salud, si tns_alias está presente,
-- esos tres campos se refrescan leyendo tnsnames.ora de nuevo (ver
-- includes/tnsnames-parser.php). Para instancias no-Oracle, o para
-- instancias Oracle registradas antes de este cambio, tns_alias queda
-- en NULL y todo sigue funcionando como antes (host/puerto/nombre_bd
-- manuales).
--
-- Ejecutar sobre la base de datos del Monitor (la de includes/config.php
-- -> 'monitor', separada de la principal de RiskGuard).
-- =====================================================================

ALTER TABLE monitor_instancias
    ADD COLUMN tns_alias VARCHAR(100) NULL AFTER nombre_bd;