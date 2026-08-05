<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/UsuarioRepository.php';

// Si ya hay sesión, no tiene sentido mostrar el login de nuevo.
if (usuarioActual()) {
    header('Location: ' . (esAdmin() ? 'admin-evaluaciones.php' : 'evaluacion-riesgos.php'));
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $error = 'Complete usuario y contraseña.';
    } else {
        $repo = new UsuarioRepository(db());
        $fila = $repo->buscarPorUsuario($usuario);

        if ($fila && password_verify($password, $fila['password_hash'])) {
            $_SESSION['usuario'] = [
                'id'             => (int) $fila['id'],
                'nombre_usuario' => $fila['nombre_usuario'],
                'rol'            => $fila['rol'],
                'empresa_id'     => $fila['empresa_id'] !== null ? (int) $fila['empresa_id'] : null,
                'empresa_nombre' => $fila['empresa_nombre'],
            ];

            $redirect = $_GET['redirect'] ?? null;
            if (!$redirect || str_contains($redirect, 'login.php')) {
                $redirect = $fila['rol'] === 'admin' ? 'admin-evaluaciones.php' : 'evaluacion-riesgos.php';
            }
            header('Location: ' . $redirect);
            exit;
        }

        $error = 'Usuario o contraseña incorrectos.';
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
            <span class="section-eyebrow"><i class="fa-solid fa-lock me-2"></i>Acceso</span>
            <h1 class="section-title">Iniciar sesión</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" class="eval-meta-card">
                <div class="eval-meta-field mb-3">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" class="form-control" required autofocus
                           value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>">
                </div>
                <div class="eval-meta-field mb-3">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-cta btn-lg w-100">Entrar</button>
                <p class="mt-3 text-center">
                    ¿Su empresa aún no tiene cuenta? <a href="registro.php">Regístrese aquí</a>
                </p>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
