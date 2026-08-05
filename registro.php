<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/UsuarioRepository.php';

if (usuarioActual()) {
    header('Location: ' . (esAdmin() ? 'admin-evaluaciones.php' : 'evaluacion-riesgos.php'));
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresa   = trim($_POST['empresa'] ?? '');
    $usuario   = trim($_POST['usuario'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($empresa === '' || $usuario === '' || $password === '') {
        $error = 'Complete todos los campos.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $usuario)) {
        $error = 'El usuario debe tener entre 3 y 50 caracteres (letras, números, punto, guion).';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        $repo = new UsuarioRepository(db());
        if ($repo->existeUsuario($usuario)) {
            $error = 'Ese nombre de usuario ya existe. Elija otro.';
        } else {
            $repo->crearEmpresaUsuario($empresa, $usuario, $password);
            header('Location: login.php?registrado=1');
            exit;
        }
    }
}

$pageTitleKey = null;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<link rel="stylesheet" href="assets/css/evaluacion.css">

<main class="flex-grow-1">
    <section class="section">
        <div class="container" style="max-width:480px;">
            <span class="section-eyebrow"><i class="fa-solid fa-building me-2"></i>Cuenta de empresa</span>
            <h1 class="section-title">Registro</h1>
            <p class="section-lead">Cree la cuenta con la que su empresa iniciará sesión para responder las evaluaciones de riesgo.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" class="eval-meta-card">
                <div class="eval-meta-field mb-3">
                    <label for="empresa">Nombre de la empresa</label>
                    <input type="text" id="empresa" name="empresa" class="form-control" required
                           value="<?php echo htmlspecialchars($_POST['empresa'] ?? ''); ?>">
                </div>
                <div class="eval-meta-field mb-3">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" class="form-control" required
                           value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>">
                </div>
                <div class="eval-meta-field mb-3">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="eval-meta-field mb-3">
                    <label for="password2">Confirmar contraseña</label>
                    <input type="password" id="password2" name="password2" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-cta btn-lg w-100">Crear cuenta</button>
                <p class="mt-3 text-center">
                    ¿Ya tiene cuenta? <a href="login.php">Inicie sesión</a>
                </p>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
