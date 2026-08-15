<?php
/**
 * Conexion PDO a la base de datos del Monitor de Salud (SEPARADA de la
 * base de datos principal de RiskGuard -- ver includes/db.php).
 * Lee las credenciales de includes/config.php, bajo la clave 'monitor'.
 */

function dbMonitor(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $configPath = __DIR__ . '/config.php';
    if (!file_exists($configPath)) {
        throw new RuntimeException(
            'Falta includes/config.php. Copia includes/config.example.php, ' .
            'renombralo y coloca tus credenciales reales de MySQL.'
        );
    }

    $config = require $configPath;
    if (!isset($config['monitor'])) {
        throw new RuntimeException(
            'Falta la clave "monitor" en includes/config.php. Revisa ' .
            'includes/config.example.php para ver el formato esperado.'
        );
    }
    $cfgMonitor = $config['monitor'];

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $cfgMonitor['host'],
        $cfgMonitor['port'],
        $cfgMonitor['dbname']
    );

    $pdo = new PDO($dsn, $cfgMonitor['user'], $cfgMonitor['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $pdo->exec("SET time_zone = '-06:00'");

    return $pdo;
}