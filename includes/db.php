<?php
/**
 * Conexión PDO a MySQL/MariaDB.
 * Lee las credenciales de includes/config.php (NO se sube a git — ver .gitignore).
 * Copia includes/config.example.php como includes/config.php y coloca ahí
 * las credenciales reales de tu base de datos (locales o de InfinityFree).
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $configPath = __DIR__ . '/config.php';
    if (!file_exists($configPath)) {
        throw new RuntimeException(
            'Falta includes/config.php. Copia includes/config.example.php, ' .
            'renómbralo y coloca tus credenciales reales de MySQL.'
        );
    }

    $config = require $configPath;

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['port'],
        $config['dbname']
    );

    $pdo = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Costa Rica no usa horario de verano: offset fijo -06:00 (funciona también
    // en hosting compartido sin depender de las tablas mysql.time_zone_name).
    $pdo->exec("SET time_zone = '-06:00'");

    return $pdo;
}
