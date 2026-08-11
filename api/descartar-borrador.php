<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/auth.php';

$usuarioSesion = usuarioActual();
if (!$usuarioSesion || $usuarioSesion['rol'] !== 'empresa' || !$usuarioSesion['empresa_id']) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado.']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/BorradorRepository.php';

try {
    (new BorradorRepository(db()))->eliminar((int) $usuarioSesion['empresa_id']);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo descartar el progreso.']);
}