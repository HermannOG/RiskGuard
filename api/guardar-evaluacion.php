<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/auth.php';

if (!usuarioActual()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Debe iniciar sesión para guardar una evaluación.']);
    exit;
}
$usuarioSesion = usuarioActual();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/EmpresaRepository.php';
require_once __DIR__ . '/../includes/EvaluacionRepository.php';
require_once __DIR__ . '/../includes/cuestionario-data.php'; // define $controles

$entrada = json_decode(file_get_contents('php://input'), true);

if (!$entrada || empty($entrada['organizacion']) || empty($entrada['evaluador'])
    || empty($entrada['fecha']) || empty($entrada['respuestas'])) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos.']);
    exit;
}

$respuestasCrudas = $entrada['respuestas']; // { "1": {respuesta, nivel_madurez, comentario}, ... }
$dimensiones = ['C', 'I', 'D'];
$obtenido = ['C' => 0, 'I' => 0, 'D' => 0];
$maximo   = ['C' => 0, 'I' => 0, 'D' => 0];

$respuestasParaGuardar = [];
$resultadosControl = [];

function nivelMadurezPHP(float $pct): int
{
    if ($pct <= 0)   return 0;
    if ($pct <= 20)  return 1;
    if ($pct <= 45)  return 2;
    if ($pct <= 70)  return 3;
    if ($pct <= 90)  return 4;
    return 5;
}

foreach ($controles as $c) {
    $aplicables = 0;
    $cumplidas = 0;

    foreach ($c['preguntas'] as $p) {
        $entradaPregunta = $respuestasCrudas[$p['id']] ?? null;
        $valor = is_array($entradaPregunta) ? ($entradaPregunta['respuesta'] ?? null) : null;
        if (!in_array($valor, ['si', 'no', 'na'], true)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => "Falta o es inválida la respuesta {$p['id']}."]);
            exit;
        }

        $nivelMadurezManual = $entradaPregunta['nivel_madurez'] ?? null;
        if ($valor === 'na') {
            $nivelMadurezManual = 0;
        } elseif (!is_int($nivelMadurezManual) && !ctype_digit((string) $nivelMadurezManual)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => "Falta o es inválido el nivel de madurez de la pregunta {$p['id']}."]);
            exit;
        } else {
            $nivelMadurezManual = (int) $nivelMadurezManual;
        }
        if ($nivelMadurezManual < 0 || $nivelMadurezManual > 5) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => "Nivel de madurez fuera de rango en la pregunta {$p['id']}."]);
            exit;
        }

        // El comentario es opcional en general (se guarda si el usuario escribió algo
        // en cualquier pregunta) y obligatorio solo cuando la respuesta es "na" o el
        // nivel de madurez manual es 0.
        $requiereComentario = ($valor === 'na') || ($nivelMadurezManual === 0);
        $comentario = trim((string) ($entradaPregunta['comentario'] ?? ''));
        if ($requiereComentario && $comentario === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => "Falta el comentario obligatorio en la pregunta {$p['id']}."]);
            exit;
        }
        $comentario = $comentario === '' ? null : $comentario;

        $respuestasParaGuardar[] = [
            'pregunta_id'    => $p['id'],
            'respuesta'      => $valor,
            'nivel_madurez'  => $nivelMadurezManual,
            'comentario'     => $comentario,
        ];

        if ($valor === 'na') continue;
        $aplicables++;
        if ($valor === 'si') $cumplidas++;
    }

    $pctControl = $aplicables > 0 ? ($cumplidas / $aplicables) * 100 : 0;
    $madurez = nivelMadurezPHP($pctControl);

    $resultadosControl[] = [
        'id' => $c['id'],
        'pctControl' => round($pctControl, 2),
        'madurez' => $madurez,
    ];

    foreach ($dimensiones as $dim) {
        $pesoDim = ['C' => $c['peso_c'], 'I' => $c['peso_i'], 'D' => $c['peso_d']][$dim];
        $factorPeso = $c['peso'] * $pesoDim;
        $obtenido[$dim] += ($madurez / 5) * $factorPeso;
        $maximo[$dim]   += $factorPeso;
    }
}

$pct = [];
foreach ($dimensiones as $dim) {
    $pct[$dim] = $maximo[$dim] > 0 ? round(($obtenido[$dim] / $maximo[$dim]) * 100, 2) : 0;
}
$pctGlobal = round(($pct['C'] + $pct['I'] + $pct['D']) / 3, 2);

try {
    $pdo = db();
    $empresaRepo    = new EmpresaRepository($pdo);
    $evaluacionRepo = new EvaluacionRepository($pdo);

    // Si quien guarda es una cuenta de empresa, se usa el nombre de SU
    // empresa (no lo que venga del navegador) para evitar que se haga
    // pasar por otra organización.
    $nombreOrganizacion = $usuarioSesion['rol'] === 'empresa'
        ? $usuarioSesion['empresa_nombre']
        : trim($entrada['organizacion']);

    $empresaId = $empresaRepo->obtenerOCrear($nombreOrganizacion);

    $evaluacionId = $evaluacionRepo->guardar([
        'empresa_id'        => $empresaId,
        'usuario_id'        => $usuarioSesion['id'],
        'evaluador'         => trim($entrada['evaluador']),
        'area_evaluada'     => $entrada['area_evaluada'] ?? null,
        'dba'               => $entrada['dba'] ?? null,
        'fecha'             => $entrada['fecha'],
        'pct'               => $pct,
        'pct_global'        => $pctGlobal,
        'respuestas'        => $respuestasParaGuardar,
        'resultadosControl' => $resultadosControl,
    ]);

    echo json_encode([
        'ok' => true,
        'evaluacion_id' => $evaluacionId,
        'empresa_id' => $empresaId,
        'pct' => $pct,
        'pct_global' => $pctGlobal,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al guardar la evaluación.', 'detalle' => $e->getMessage()]);
}
