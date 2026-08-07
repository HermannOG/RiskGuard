<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
requiereLogin();
$usuarioSesion = usuarioActual();

$pageTitleKey = "eval.pagetitle";
include "includes/header.php";
include "includes/navbar.php";
include "includes/cuestionario-data.php";

// Orden de despliegue por dimensión dominante del control
$orden_grupos = ['I', 'D', 'C'];

$controles_por_grupo = ['C' => [], 'I' => [], 'D' => []];
foreach ($controles as $c) {
    $controles_por_grupo[$c['grupo']][] = $c;
}
?>

<link rel="stylesheet" href="<?php echo asset_url('assets/css/evaluacion.css'); ?>">

<main class="flex-grow-1">

    <!-- INTRO -->
    <section class="section eval-hero">
        <div class="container">
            <span class="section-eyebrow"><i class="fa-solid fa-clipboard-check me-2"></i><?php echo t('eval.eyebrow'); ?></span>
            <h1 class="section-title eval-title"><?php echo t('eval.title'); ?></h1>
            <p class="section-lead">
                <?php echo t('eval.lead'); ?>
            </p>
            <p class="section-lead">
                Sesión: <strong><?php echo htmlspecialchars($usuarioSesion['empresa_nombre'] ?? $usuarioSesion['nombre_usuario']); ?></strong>
                (<?php echo $usuarioSesion['rol'] === 'admin' ? 'administrador' : 'empresa'; ?>) ·
                <a href="logout.php">Cerrar sesión</a>
                <?php if ($usuarioSesion['rol'] === 'admin'): ?> · <a href="admin-evaluaciones.php">Panel admin</a><?php endif; ?>
            </p>
        </div>
    </section>

    <!-- CUESTIONARIO -->
    <section class="section section-alt">
        <div class="container">
            <form id="form-evaluacion">

                <div class="eval-meta-card">
                    <h2 class="eval-meta-title"><?php echo t('eval.meta.title'); ?></h2>
                    <div class="eval-meta-grid">
                        <div class="eval-meta-field">
                            <label for="meta-organizacion"><?php echo t('eval.meta.org'); ?></label>
                            <?php if ($usuarioSesion['rol'] === 'empresa'): ?>
                                <input type="text" id="meta-organizacion" name="organizacion" class="form-control"
                                       value="<?php echo htmlspecialchars($usuarioSesion['empresa_nombre']); ?>" readonly>
                            <?php else: ?>
                                <input type="text" id="meta-organizacion" name="organizacion" class="form-control" required>
                            <?php endif; ?>
                        </div>
                        <div class="eval-meta-field">
                            <label for="meta-evaluador"><?php echo t('eval.meta.evaluador'); ?></label>
                            <input type="text" id="meta-evaluador" name="evaluador" class="form-control" required
                                   value="<?php echo htmlspecialchars($usuarioSesion['nombre_usuario']); ?>">
                        </div>
                        <div class="eval-meta-field">
                            <label for="meta-fecha"><?php echo t('eval.meta.fecha'); ?></label>
                            <input type="text" id="meta-fecha-display" class="form-control eval-fecha-auto" value="<?php echo date('d/m/Y'); ?>" readonly aria-readonly="true" tabindex="-1">
                            <input type="hidden" id="meta-fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>

                <?php foreach ($orden_grupos as $g): ?>
                    <div class="eval-group">
                        <h2 class="eval-group-title">
                            <span class="eval-group-tag tag-<?php echo strtolower($g); ?>"><?php echo $g; ?></span>
                            <?php echo $grupos[$g]; ?>
                        </h2>

                        <div class="eval-questions">
                            <?php foreach ($controles_por_grupo[$g] as $c): ?>
                                <div class="eval-control" data-control-id="<?php echo $c['id']; ?>">
                                    <p class="eval-control-titulo">
                                        <span class="eval-control-codigo"><?php echo htmlspecialchars($c['codigo']); ?></span>
                                        <?php echo htmlspecialchars($c['nombre']); ?>
                                    </p>
                                    <?php foreach ($c['preguntas'] as $p): ?>
                                        <div class="eval-question" data-pregunta-id="<?php echo $p['id']; ?>">
                                            <p class="eval-question-text">
                                                <span class="eval-question-num"><?php echo $p['id']; ?>.</span>
                                                <?php echo htmlspecialchars($p['texto']); ?>
                                                <button type="button" class="eval-info-btn" data-bs-toggle="popover" data-bs-trigger="focus click" data-bs-placement="top" data-bs-content="<?php echo htmlspecialchars($p['ayuda']); ?>" aria-label="<?php echo t('eval.ayuda.label'); ?>">
                                                    <i class="fa-solid fa-circle-info"></i>
                                                </button>
                                            </p>
                                            <div class="eval-options" role="radiogroup" aria-label="Pregunta <?php echo $p['id']; ?>">
                                                <?php foreach ($opciones_respuesta as $valor => $etiqueta): ?>
                                                    <label class="eval-option">
                                                        <input type="radio" name="p<?php echo $p['id']; ?>" value="<?php echo $valor; ?>" required>
                                                        <span><?php echo $etiqueta; ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="eval-nivel-row">
                                                <label class="eval-nivel-label"><?php echo t('eval.nivel.label'); ?></label>
                                                <div class="eval-nivel-buttons" role="group" aria-label="<?php echo t('eval.nivel.label'); ?> <?php echo $p['id']; ?>">
                                                    <?php for ($n = 0; $n <= 5; $n++): ?>
                                                        <button type="button" class="eval-nivel-btn" data-nivel-btn="<?php echo $p['id']; ?>" data-valor="<?php echo $n; ?>" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="<?php echo htmlspecialchars($p['niveles'][$n]); ?>"><?php echo $n; ?></button>
                                                    <?php endfor; ?>
                                                </div>
                                                <input type="hidden" id="nivel<?php echo $p['id']; ?>" name="nivel<?php echo $p['id']; ?>" value="">
                                                <button type="button" class="eval-comentario-btn" id="btnComentario<?php echo $p['id']; ?>" data-target="comentario<?php echo $p['id']; ?>" title="<?php echo t('eval.comentario.btn'); ?>" aria-label="<?php echo t('eval.comentario.btn'); ?>">
                                                    <i class="fa-solid fa-comment-dots"></i>
                                                </button>
                                            </div>
                                            <textarea class="form-control eval-comentario-box d-none" id="comentario<?php echo $p['id']; ?>" name="comentario<?php echo $p['id']; ?>" rows="2" placeholder="<?php echo t('eval.comentario.placeholder'); ?>"></textarea>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="eval-submit-row">
                    <button type="submit" class="btn btn-cta btn-lg">
                        <?php echo t('eval.btn.calcular'); ?>
                        <i class="fa-solid fa-chart-line ms-2"></i>
                    </button>
                    <p class="eval-submit-hint"><?php echo t('eval.hint.responder'); ?></p>
                    <p id="eval-guardar-status" class="eval-submit-hint"></p>
                </div>
            </form>
        </div>
    </section>

    <!-- DASHBOARD DE RESULTADOS (oculto hasta calcular) -->
    <section id="eval-dashboard" class="section eval-dashboard" hidden>
        <div class="container">
            <span class="section-eyebrow"><?php echo t('eval.dashboard.eyebrow'); ?></span>
            <h2 class="section-title"><?php echo t('eval.dashboard.title'); ?></h2>

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

            <div class="row g-4 mt-2" id="eval-dimension-cards">
                <!-- Tarjetas C / I / D generadas por JS -->
            </div>

            <div class="row g-4 mt-4">
                <div class="col-md-7">
                    <div class="eval-chart-card">
                        <h6><?php echo t('eval.chart.dimension'); ?></h6>
                        <canvas id="chart-barras" height="220"></canvas>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="eval-chart-card">
                        <h6><?php echo t('eval.chart.global'); ?></h6>
                        <canvas id="chart-circular" height="220"></canvas>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-4">
                <div class="col-12">
                    <div class="eval-chart-card">
                        <h6><?php echo t('eval.chart.preguntas'); ?></h6>
                        <div class="eval-chart-preguntas-wrap">
                            <canvas id="chart-madurez-preguntas"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="eval-export-row mt-4">
                <button type="button" id="btn-export-pdf" class="btn btn-ghost">
                    <i class="fa-solid fa-file-pdf me-2"></i><?php echo t('eval.btn.pdf'); ?>
                </button>
                <button type="button" id="btn-export-excel" class="btn btn-ghost">
                    <i class="fa-solid fa-file-excel me-2"></i><?php echo t('eval.btn.excel'); ?>
                </button>
            </div>

            <div class="eval-recos-card mt-4">
                <h6><i class="fa-solid fa-lightbulb me-2"></i><?php echo t('eval.recos.title'); ?></h6>
                <ul id="eval-recos-list" class="eval-recos-list"></ul>
            </div>

            <div class="eval-weak-card mt-4">
                <h6><i class="fa-solid fa-triangle-exclamation me-2"></i><?php echo t('eval.weak.title'); ?></h6>
                <ul id="eval-weak-list" class="eval-weak-list"></ul>
            </div>
        </div>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.2/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.4/dist/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script id="eval-data" type="application/json"><?php echo json_encode([
    'controles' => $controles,
    'recomendaciones' => $recomendaciones,
    'grupos' => $grupos,
    'nivelesMadurez' => $niveles_madurez,
    'opcionesRespuesta' => $opciones_respuesta,
    'strings' => [
        'alertaIncompleto' => t('js.alert.incompleto'),
        'alertaComentario' => t('js.alert.comentario'),
        'guardando' => t('js.status.guardando'),
        'guardado' => t('js.status.guardado'),
        'errorGuardar' => t('js.status.error'),
        'sinBrechas' => t('js.fallback.sinbrechas'),
        'madurezOk' => t('js.fallback.madurezok'),
        'pctCumplimiento' => t('js.chart.pctcumplimiento'),
        'cumplimiento' => t('js.chart.cumplimiento'),
        'brecha' => t('js.chart.brecha'),
        'nivelMadurezChart' => t('js.chart.nivelmadurez'),
        'madurez' => t('js.madurez.label'),
        'verde' => t('js.semaforo.verde'),
        'amarillo' => t('js.semaforo.amarillo'),
        'rojo' => t('js.semaforo.rojo'),
        'verdeTexto' => t('js.semaforo.verde.texto'),
        'amarilloTexto' => t('js.semaforo.amarillo.texto'),
        'rojoTexto' => t('js.semaforo.rojo.texto'),
        'exportTitulo' => t('js.export.titulo'),
        'exportOrganizacion' => t('js.export.organizacion'),
        'exportEvaluador' => t('js.export.evaluador'),
        'exportFecha' => t('js.export.fecha'),
        'exportGlobal' => t('js.export.global'),
        'exportDimension' => t('js.export.dimension'),
        'exportId' => t('js.export.id'),
        'exportControl' => t('js.export.control'),
        'exportPregunta' => t('js.export.pregunta'),
        'exportRespuesta' => t('js.export.respuesta'),
        'exportNivel' => t('js.export.nivel'),
        'exportNivelAuto' => t('js.export.nivelauto'),
        'exportComentario' => t('js.export.comentario'),
    ],
]); ?></script>
<script src="<?php echo asset_url('assets/js/evaluacion.js'); ?>"></script>

<?php include "includes/footer.php"; ?>
