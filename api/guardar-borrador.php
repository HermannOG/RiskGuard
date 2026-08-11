<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/auth.php';

$usuarioSesion = usuarioActual();
if (!$usuarioSesion || $usuarioSesion['rol'] !== 'empresa' || !$usuarioSesion['empresa_id']) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Solo una cuenta de empresa puede guardar progreso.']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/BorradorRepository.php';

$entrada = json_decode(file_get_contents('php://input'), true);
if (!$entrada) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos.']);
    exit;
}

$organizacion = trim((string) ($entrada['organizacion'] ?? ''));
$evaluador    = trim((string) ($entrada['evaluador'] ?? ''));
$fecha        = trim((string) ($entrada['fecha'] ?? ''));
$respuestas   = is_array($entrada['respuestas'] ?? null) ? $entrada['respuestas'] : [];

// Autoguardado: no exige que la evaluación esté completa, solo que haya
// al menos una respuesta para que valga la pena guardar algo.
if (empty($respuestas)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'No hay respuestas que guardar todavía.']);
    exit;
}

try {
    $repo = new BorradorRepository(db());
    $repo->guardar(
        (int) $usuarioSesion['empresa_id'],
        (int) $usuarioSesion['id'],
        $organizacion,
        $evaluador,
        $fecha,
        $respuestas
    );
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el progreso.']);
}