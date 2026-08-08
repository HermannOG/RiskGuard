<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
requiereAdmin();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/includes/db.php';

$pdo = db();

$empresas = $pdo->query("SELECT id, nombre FROM empresas ORDER BY nombre")->fetchAll();

$empresaFiltro = isset($_GET['empresa_id']) && $_GET['empresa_id'] !== '' ? (int) $_GET['empresa_id'] : null;

$sql = "SELECT ev.id, e.nombre AS empresa, ev.evaluador, ev.area_evaluada, ev.dba,
               ev.fecha_evaluacion, ev.pct_confidencialidad, ev.pct_integridad,
               ev.pct_disponibilidad, ev.pct_global, ev.creado_en
        FROM evaluaciones ev
        JOIN empresas e ON e.id = ev.empresa_id";
$params = [];
if ($empresaFiltro) {
    $sql .= " WHERE ev.empresa_id = :empresa_id";
    $params['empresa_id'] = $empresaFiltro;
}
$sql .= " ORDER BY ev.creado_en DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$evaluaciones = $stmt->fetchAll();
?>
    <link rel="stylesheet" href="assets/css/evaluacion.css">

    <main class="flex-grow-1">
        <section class="section">
            <div class="container">
                <span class="section-eyebrow"><i class="fa-solid fa-user-shield me-2"></i>Panel de administración</span>
                <h1 class="section-title">Todas las evaluaciones</h1>
                <p class="section-lead">
                    Sesión: <strong><?php echo htmlspecialchars(usuarioActual()['nombre_usuario']); ?></strong> (admin) ·
                    <a href="logout.php">Cerrar sesión</a>
                </p>
                <form method="get" class="eval-meta-field mb-3" style="max-width:320px;">
                    <label for="empresa_id">Filtrar por empresa</label>
                    <select id="empresa_id" name="empresa_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Todas las empresas</option>
                        <?php foreach ($empresas as $e): ?>
                            <option value="<?php echo (int) $e['id']; ?>" <?php echo $empresaFiltro === (int) $e['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($e['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <p>Total registradas: <strong><?php echo count($evaluaciones); ?></strong></p>

                <div class="table-responsive mt-4">
                    <table class="table table-striped align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Empresa</th>
                            <th>Evaluador</th>
                            <th>Área</th>
                            <th>DBA</th>
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
                            <tr><td colspan="11">Aún no hay evaluaciones registradas.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($evaluaciones as $ev): ?>
                            <tr>
                                <td><?php echo (int) $ev['id']; ?></td>
                                <td><?php echo htmlspecialchars($ev['empresa']); ?></td>
                                <td><?php echo htmlspecialchars($ev['evaluador']); ?></td>
                                <td><?php echo htmlspecialchars($ev['area_evaluada'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($ev['dba'] ?? '—'); ?></td>
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