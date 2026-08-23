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

    // PDO::ATTR_TIMEOUT limita cuanto espera PHP a que MySQL responda al
    // CONECTAR (no limita consultas ya en curso). Sin esto, si el host
    // configurado no responde, PHP puede quedarse colgado por mucho mas
    // tiempo del esperado -- y como el servidor embebido de PHP atiende
    // una peticion a la vez, cualquier clic repetido mientras tanto se
    // encola detras, y varias peticiones terminan compitiendo por las
    // mismas filas al mismo tiempo cuando por fin se destraban.
    $pdo = new PDO($dsn, $cfgMonitor['user'], $cfgMonitor['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5,
    ]);

    $pdo->exec("SET time_zone = '-06:00'");

    return $pdo;
}