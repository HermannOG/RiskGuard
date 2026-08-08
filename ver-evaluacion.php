<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
requiereLogin();
$usuarioSesion = usuarioActual();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/EvaluacionRepository.php';

$pageTitleKey = "eval.dashboard.title";
include "includes/header.php";
include "includes/navbar.php";
require_once __DIR__ . '/includes/cuestionario-data.php';

$id = (int) ($_GET['id'] ?? 0);
$repo = new EvaluacionRepository(db());
$evaluacion = $repo->obtenerPorId($id);

// Control de acceso: una empresa solo puede ver sus propias evaluaciones.
if ($evaluacion && $usuarioSesion['rol'] === 'empresa' && (int) $evaluacion['empresa_id'] !== (int) $usuarioSesion['empresa_id']) {
    $evaluacion = null;
}

$volverUrl = $usuarioSesion['rol'] === 'admin' ? 'admin-evaluaciones.php' : 'panel-empresa.php';

$datosJs = null;
if ($evaluacion) {
    $resultadosControlDb = $repo->resultadosControl($id);
    $respuestasDb = $repo->respuestas($id);

    $resultadosControl = [];
    foreach ($controles as $c) {
        $r = $resultadosControlDb[$c['id']] ?? ['pctControl' => 0, 'madurez' => 0];
        $resultadosControl[] = [
            'id' => $c['id'], 'codigo' => $c['codigo'], 'nombre' => $c['nombre'], 'grupo' => $c['grupo'],
            'pctControl' => (int) round($r['pctControl']), 'madurez' => $r['madurez'],
        ];
    }

    $nivelesManual = [];
    $preguntasDetalle = [];
    $preguntasPorId = [];
    foreach ($controles as $c) {
        foreach ($c['preguntas'] as $p) {
            $preguntasPorId[$p['id']] = ['texto' => $p['texto'], 'control' => $c['codigo'] . ' — ' . $c['nombre']];
        }
    }
    foreach ($respuestasDb as $pid => $r) {
        $nivelesManual[$pid] = $r['nivelMadurez'];
        $info = $preguntasPorId[$pid] ?? ['texto' => '—', 'control' => '—'];
        $preguntasDetalle[] = [
            'id' => $pid, 'control' => $info['control'], 'texto' => $info['texto'],
            'respuesta' => $r['respuesta'], 'nivel' => $r['nivelMadurez'], 'comentario' => $r['comentario'],
        ];
    }
    usort($preguntasDetalle, fn($a, $b) => $a['id'] <=> $b['id']);

    $pct = [
        'C' => (float) $evaluacion['pct_confidencialidad'],
        'I' => (float) $evaluacion['pct_integridad'],
        'D' => (float) $evaluacion['pct_disponibilidad'],
    ];

    $datosJs = [
        'pct' => $pct,
        'global' => (float) $evaluacion['pct_global'],
        'resultadosControl' => $resultadosControl,
        'nivelesManual' => $nivelesManual,
        'preguntasDetalle' => $preguntasDetalle,
        'grupos' => $grupos,
        'recomendaciones' => $recomendaciones,
        'nivelesMadurez' => $niveles_madurez,
        'strings' => [
            'sinBrechas' => t('js.fallback.sinbrechas'),
            'madurezOk' => t('js.fallback.madurezok'),
            'nivelMadurezChart' => t('js.chart.nivelmadurez'),
            'madurez' => t('js.madurez.label'),
            'verde' => t('js.semaforo.verde'),
            'amarillo' => t('js.semaforo.amarillo'),
            'rojo' => t('js.semaforo.rojo'),
            'verdeTexto' => t('js.semaforo.verde.texto'),
            'amarilloTexto' => t('js.semaforo.amarillo.texto'),
            'rojoTexto' => t('js.semaforo.rojo.texto'),
        ],
    ];
}

$etiquetaRespuesta = ['si' => 'Sí', 'no' => 'No', 'na' => 'N/A'];
?>
    <link rel="stylesheet" href="<?php echo asset_url('assets/css/evaluacion.css'); ?>">

    <main class="flex-grow-1">
        <section class="section">
            <div class="container">
                <a href="<?php echo $volverUrl; ?>" class="btn btn-ghost mb-3">
                    <i class="fa-solid fa-arrow-left me-2"></i>Volver al listado
                </a>

                <?php if (!$evaluacion): ?>
                    <div class="alert alert-danger">Evaluación no encontrada o no tiene acceso a ella.</div>
                <?php else: ?>
                    <span class="section-eyebrow"><?php echo t('eval.dashboard.eyebrow'); ?></span>
                    <h1 class="section-title"><?php echo htmlspecialchars($evaluacion['empresa']); ?></h1>
                    <p class="section-lead">
                        Evaluador: <strong><?php echo htmlspecialchars($evaluacion['evaluador']); ?></strong>
                        · Fecha: <?php echo htmlspecialchars($evaluacion['fecha_evaluacion']); ?>
                        <?php if (!empty($evaluacion['area_evaluada'])): ?> · Área: <?php echo htmlspecialchars($evaluacion['area_evaluada']); ?><?php endif; ?>
                        <?php if (!empty($evaluacion['dba'])): ?> · DBA: <?php echo htmlspecialchars($evaluacion['dba']); ?><?php endif; ?>
                    </p>

                    <div id="eval-dashboard" class="eval-dashboard mt-4">
                        <div class="eval-global-card" id="eval-global-card">
                            <div class="eval-global-score">
                                <span id="eval-global-pct">0%</span>
                                <small><?php echo t('eval.global.label'); ?></small>
                            </div>
                            <div class="eval-global-meta">
                                <span id="eval-global-badge" class="eval-badge">—</span>
                                <p id="eval-global-text" class="eval-global-text"></p>
                            </div>
                        </div>

                        <div class="row g-4 mt-3" id="eval-dimension-cards"></div>

                        <div class="row g-4 mt-5">
                            <div class="col-md-7">
                                <div class="eval-chart-card eval-chart-card-sm">
                                    <h6><?php echo t('eval.chart.dimension'); ?></h6>
                                    <canvas id="chart-barras" height="220"></canvas>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="eval-chart-card eval-chart-card-sm">
                                    <h6><?php echo t('eval.chart.global'); ?></h6>
                                    <canvas id="chart-circular" height="220"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-5">
                            <div class="col-12">
                                <div class="eval-chart-card">
                                    <h6><?php echo t('eval.chart.preguntas'); ?></h6>
                                    <div class="eval-chart-preguntas-wrap">
                                        <canvas id="chart-madurez-preguntas"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="eval-recos-card mt-5">
                            <h6><i class="fa-solid fa-lightbulb me-2"></i><?php echo t('eval.recos.title'); ?></h6>
                            <ul id="eval-recos-list" class="eval-recos-list"></ul>
                        </div>

                        <div class="eval-weak-card mt-5">
                            <h6><i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo t('eval.weak.title'); ?></h6>
                            <ul id="eval-weak-list" class="eval-weak-list"></ul>
                        </div>

                        <div class="table-responsive mt-5">
                            <h6 class="mb-3">Respuestas detalladas</h6>
                            <table class="table table-striped align-middle">
                                <thead>
                                <tr>
                                    <th>Control</th>
                                    <th>Pregunta</th>
                                    <th>Respuesta</th>
                                    <th>Nivel madurez</th>
                                    <th>Comentario</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($datosJs['preguntasDetalle'] as $r): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['control']); ?></td>
                                        <td><?php echo htmlspecialchars($r['texto']); ?></td>
                                        <td><?php echo htmlspecialchars($etiquetaRespuesta[$r['respuesta']] ?? $r['respuesta']); ?></td>
                                        <td><?php echo $r['nivel'] !== null ? (int) $r['nivel'] : '—'; ?></td>
                                        <td><?php echo $r['comentario'] ? htmlspecialchars($r['comentario']) : '—'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

<?php if ($datosJs): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script id="ver-eval-data" type="application/json"><?php echo json_encode($datosJs); ?></script>
    <script src="<?php echo asset_url('assets/js/ver-evaluacion.js'); ?>"></script>
<?php endif; ?>

<?php include "includes/footer.php"; ?>