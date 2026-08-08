<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
requiereEmpresa();
$usuarioSesion = usuarioActual();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/EmpresaRepository.php';

$pageTitleKey = null;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$empresaRepo = new EmpresaRepository(db());
$evaluaciones = $usuarioSesion['empresa_id']
    ? $empresaRepo->historial((int) $usuarioSesion['empresa_id'])
    : [];
?>
    <link rel="stylesheet" href="assets/css/evaluacion.css">

    <main class="flex-grow-1">
        <section class="section">
            <div class="container">
                <span class="section-eyebrow"><i class="fa-solid fa-building me-2"></i>Panel de empresa</span>
                <h1 class="section-title"><?php echo htmlspecialchars($usuarioSesion['empresa_nombre'] ?? ''); ?></h1>
                <p class="section-lead">
                    Sesión: <strong><?php echo htmlspecialchars($usuarioSesion['nombre_usuario']); ?></strong> ·
                    <a href="logout.php">Cerrar sesión</a>
                </p>

                <div class="eval-submit-row mb-4" style="justify-content:flex-start;">
                    <a href="evaluacion-riesgos.php" class="btn btn-cta">
                        <i class="fa-solid fa-plus me-2"></i>Nueva evaluación
                    </a>
                </div>

                <p>Evaluaciones registradas: <strong><?php echo count($evaluaciones); ?></strong></p>

                <div class="table-responsive mt-3">
                    <table class="table table-striped align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Evaluador</th>
                            <th>Área</th>
                            <th>Fecha</th>
                            <th>C%</th>
                            <th>I%</th>
                            <th>D%</th>
                            <th>Global%</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($evaluaciones)): ?>
                            <tr><td colspan="9">Todavía no ha realizado ninguna evaluación. ¡Empiece con la primera!</td></tr>
                        <?php endif; ?>
                        <?php foreach ($evaluaciones as $ev): ?>
                            <tr>
                                <td><?php echo (int) $ev['id']; ?></td>
                                <td><?php echo htmlspecialchars($ev['evaluador']); ?></td>
                                <td><?php echo htmlspecialchars($ev['area_evaluada'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($ev['fecha_evaluacion']); ?></td>
                                <td><?php echo $ev['pct_confidencialidad']; ?>%</td>
                                <td><?php echo $ev['pct_integridad']; ?>%</td>
                                <td><?php echo $ev['pct_disponibilidad']; ?>%</td>
                                <td><strong><?php echo $ev['pct_global']; ?>%</strong></td>
                                <td><a href="ver-evaluacion.php?id=<?php echo (int) $ev['id']; ?>" class="btn btn-ghost btn-sm">Ver estadísticas</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>