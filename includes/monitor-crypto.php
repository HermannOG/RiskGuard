<?php
/**
 * Cifrado simetrico simple para las credenciales guardadas en
 * monitor_instancias.password_enc. La clave vive en config.php (no se
 * sube a git), bajo la clave 'monitor_encryption_key'.
 */

function monitorEncryptionKey(): string
{
    $configPath = __DIR__ . '/config.php';
    $config = require $configPath;
    if (empty($config['monitor_encryption_key'])) {
        throw new RuntimeException(
            "Falta 'monitor_encryption_key' en includes/config.php. " .
            "Agrega cualquier cadena secreta larga, ej: 'cambia-esto-por-algo-largo-y-unico'."
        );
    }
    return $config['monitor_encryption_key'];
}

function monitorEncrypt(string $textoPlano): string
{
    $clave = monitorEncryptionKey();
    $iv = random_bytes(16);
    $cifrado = openssl_encrypt($textoPlano, 'AES-256-CBC', $clave, 0, $iv);
    return base64_encode($iv . $cifrado);
}

function monitorDecrypt(string $textoCifrado): string
{
    $clave = monitorEncryptionKey();
    $datos = base64_decode($textoCifrado);
    $iv = substr($datos, 0, 16);
    $cifrado = substr($datos, 16);
    return openssl_decrypt($cifrado, 'AES-256-CBC', $clave, 0, $iv);
}