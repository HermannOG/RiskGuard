<?php
/**
 * Crea la PRIMERA cuenta de administrador. Se autodeshabilita en cuanto
 * ya existe un admin en la base de datos. Por seguridad, borre este
 * archivo del servidor después de usarlo.
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/db.php';

$pdo = db();
$existeAdmin = (bool) $pdo->query("SELECT id FROM usuarios WHERE rol = 'admin' LIMIT 1")->fetch();

$mensaje = null;
$error = null;

if (!$existeAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $usuario)) {
        $error = 'El usuario debe tener entre 3 y 50 caracteres (letras, números, punto, guion).';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO usuarios (empresa_id, nombre_usuario, password_hash, rol)
             VALUES (NULL, :u, :h, "admin")'
        );
        $stmt->execute([
            'u' => $usuario,
            'h' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        $existeAdmin = true;
        $mensaje = 'Cuenta de administrador creada correctamente. Ahora borre crear-admin.php del servidor e inicie sesión en login.php.';
    }
}

$pageTitleKey = null;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<link rel="stylesheet" href="assets/css/evaluacion.css">

<main class="flex-grow-1">
    <section class="section">
        <div class="container" style="max-width:440px;">
            <h1 class="section-title">Crear cuenta de administrador</h1>

            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php elseif ($existeAdmin): ?>
                <div class="alert alert-warning">
                    Ya existe una cuenta de administrador. Por seguridad este formulario está deshabilitado.
                    Borre el archivo <code>crear-admin.php</code> del servidor.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!$existeAdmin): ?>
                <form method="post" class="eval-meta-card">
                    <div class="eval-meta-field mb-3">
                        <label for="usuario">Usuario</label>
                        <input type="text" id="usuario" name="usuario" class="form-control" required>
                    </div>
                    <div class="eval-meta-field mb-3">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-cta btn-lg w-100">Crear administrador</button>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
