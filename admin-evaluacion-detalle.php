<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
requiereAdmin();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/cuestionario-data.php';

$id = (int) ($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare(
    'SELECT ev.*, e.nombre AS empresa
     FROM evaluaciones ev
     JOIN empresas e ON e.id = ev.empresa_id
     WHERE ev.id = :id'
);
$stmt->execute(['id' => $id]);
$evaluacion = $stmt->fetch();

if (!$evaluacion) {
    http_response_code(404);
}

$respuestas = [];
if ($evaluacion) {
    $stmt = $pdo->prepare('SELECT * FROM evaluacion_respuestas WHERE evaluacion_id = :id ORDER BY pregunta_id');
    $stmt->execute(['id' => $id]);
    $respuestas = $stmt->fetchAll();
}

$preguntasPorId = [];
foreach ($controles as $c) {
    foreach ($c['preguntas'] as $p) {
        $preguntasPorId[$p['id']] = ['texto' => $p['texto'], 'control' => $c['codigo'] . ' — ' . $c['nombre']];
    }
}

$etiquetaRespuesta = ['si' => 'Sí', 'no' => 'No', 'na' => 'N/A'];
?>
<link rel="stylesheet" href="assets/css/evaluacion.css">

<main class="flex-grow-1">
    <section class="section">
        <div class="container">
            <a href="admin-evaluaciones.php" class="btn btn-ghost mb-3"><i class="fa-solid fa-arrow-left me-2"></i>Volver al listado</a>

            <?php if (!$evaluacion): ?>
                <div class="alert alert-danger">Evaluación no encontrada.</div>
            <?php else: ?>
                <span class="section-eyebrow">Evaluación #<?php echo (int) $evaluacion['id']; ?></span>
                <h1 class="section-title"><?php echo htmlspecialchars($evaluacion['empresa']); ?></h1>
                <p class="section-lead">
                    Evaluador: <strong><?php echo htmlspecialchars($evaluacion['evaluador']); ?></strong>
                    · Fecha: <?php echo htmlspecialchars($evaluacion['fecha_evaluacion']); ?>
                    <?php if (!empty($evaluacion['area_evaluada'])): ?> · Área: <?php echo htmlspecialchars($evaluacion['area_evaluada']); ?><?php endif; ?>
                    <?php if (!empty($evaluacion['dba'])): ?> · DBA: <?php echo htmlspecialchars($evaluacion['dba']); ?><?php endif; ?>
                </p>

                <div class="eval-global-card" id="eval-global-card">
                    <div class="eval-global-score">
                        <span><?php echo $evaluacion['pct_global']; ?>%</span>
                        <small>Cumplimiento global</small>
                    </div>
                    <div class="eval-global-meta">
                        <p class="eval-global-text">
                            C: <?php echo $evaluacion['pct_confidencialidad']; ?>% ·
                            I: <?php echo $evaluacion['pct_integridad']; ?>% ·
                            D: <?php echo $evaluacion['pct_disponibilidad']; ?>%
                        </p>
                    </div>
                </div>

                <div class="table-responsive mt-4">
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
                            <?php foreach ($respuestas as $r): ?>
                                <?php $pinfo = $preguntasPorId[$r['pregunta_id']] ?? ['texto' => '—', 'control' => '—']; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pinfo['control']); ?></td>
                                    <td><?php echo htmlspecialchars($pinfo['texto']); ?></td>
                                    <td><?php echo htmlspecialchars($etiquetaRespuesta[$r['respuesta']] ?? $r['respuesta']); ?></td>
                                    <td><?php echo $r['nivel_madurez'] !== null ? (int) $r['nivel_madurez'] : '—'; ?></td>
                                    <td><?php echo $r['comentario'] ? htmlspecialchars($r['comentario']) : '—'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
