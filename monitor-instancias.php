<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
requiereAdmin();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/includes/db-monitor.php';
require_once __DIR__ . '/includes/monitor-crypto.php';

$pdo = dbMonitor();
$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre     = trim($_POST['nombre'] ?? '');
    $tipoMotor  = $_POST['tipo_motor'] ?? '';
    $host       = trim($_POST['host'] ?? '');
    $puerto     = (int) ($_POST['puerto'] ?? 0);
    $nombreBd   = trim($_POST['nombre_bd'] ?? '');
    $usuario    = trim($_POST['usuario'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($nombre && $tipoMotor && $host && $puerto && $nombreBd && $usuario) {
        $stmt = $pdo->prepare("
            INSERT INTO monitor_instancias (nombre, tipo_motor, host, puerto, nombre_bd, usuario, password_enc, activo)
            VALUES (:nombre, :tipo_motor, :host, :puerto, :nombre_bd, :usuario, :password_enc, 1)
        ");
        $stmt->execute([
            'nombre'       => $nombre,
            'tipo_motor'   => $tipoMotor,
            'host'         => $host,
            'puerto'       => $puerto,
            'nombre_bd'    => $nombreBd,
            'usuario'      => $usuario,
            'password_enc' => monitorEncrypt($password),
        ]);
        $mensaje = 'Instancia agregada correctamente.';
    } else {
        $mensaje = 'Faltan campos obligatorios.';
    }
}

$instancias = $pdo->query("SELECT id, nombre, tipo_motor, host, puerto, nombre_bd, activo FROM monitor_instancias ORDER BY nombre")->fetchAll();
?>
    <main class="flex-grow-1">
        <section class="section">
            <div class="container">
                <span class="section-eyebrow"><i class="fa-solid fa-server me-2"></i>Monitor de Salud</span>
                <h1 class="section-title">Instancias monitoreadas</h1>
                <p class="section-lead">Registra las bases de datos que quieres vigilar.</p>

                <?php if ($mensaje): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($mensaje); ?></div>
                <?php endif; ?>

                <div class="row g-4 mb-5">
                    <div class="col-lg-6">
                        <form method="post" class="eval-control">
                            <div class="mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Motor</label>
                                <select name="tipo_motor" class="form-select" required>
                                    <option value="mariadb">MariaDB / MySQL</option>
                                    <option value="oracle">Oracle</option>
                                    <option value="postgres">PostgreSQL</option>
                                    <option value="sqlserver">SQL Server</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Host</label>
                                <input type="text" name="host" class="form-control" placeholder="127.0.0.1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Puerto</label>
                                <input type="number" name="puerto" class="form-control" value="3306" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Base de datos</label>
                                <input type="text" name="nombre_bd" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Usuario</label>
                                <input type="text" name="usuario" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-cta">Agregar instancia</button>
                        </form>
                    </div>

                    <div class="col-lg-6">
                        <table class="table">
                            <thead><tr><th>Nombre</th><th>Motor</th><th>Host</th><th></th></tr></thead>
                            <tbody>
                                <?php foreach ($instancias as $inst): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($inst['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($inst['tipo_motor']); ?></td>
                                        <td><?php echo htmlspecialchars($inst['host']); ?>:<?php echo (int) $inst['puerto']; ?></td>
                                        <td><a href="monitor-salud.php?instancia_id=<?php echo (int) $inst['id']; ?>" class="btn btn-sm btn-ghost">Ver salud</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>