<?php
return [
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'dbname'   => 'riskguard',
    'user'     => 'root',
    'password' => '',

    // Base de datos SEPARADA del Monitor de Salud (segundo entregable).
    'monitor'  => [
        'host'     => '127.0.0.1',
        'port'     => '3306',
        'dbname'   => 'riskguard_monitor',
        'user'     => 'root',
        'password' => '',
    ],

    'monitor_encryption_key' => 'cambia-esto-por-una-frase-larga-solo-tuya',
];