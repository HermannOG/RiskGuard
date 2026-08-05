<?php
/**
 * Helpers de sesión y control de acceso.
 * Requiere que session_start() ya se haya llamado (lo hace includes/header.php).
 */

function usuarioActual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function esAdmin(): bool
{
    $u = usuarioActual();
    return $u !== null && $u['rol'] === 'admin';
}

function esEmpresa(): bool
{
    $u = usuarioActual();
    return $u !== null && $u['rol'] === 'empresa';
}

/** Exige que haya alguna sesión iniciada (empresa o admin). */
function requiereLogin(): void
{
    if (!usuarioActual()) {
        $destino = $_SERVER['REQUEST_URI'] ?? 'login.php';
        header('Location: login.php?redirect=' . urlencode($destino));
        exit;
    }
}

/** Exige sesión de administrador. */
function requiereAdmin(): void
{
    requiereLogin();
    if (!esAdmin()) {
        http_response_code(403);
        die('Acceso restringido a administradores.');
    }
}

/** Exige sesión de empresa (para responder la encuesta). */
function requiereEmpresa(): void
{
    requiereLogin();
    if (!esEmpresa()) {
        http_response_code(403);
        die('Esta sección es solo para cuentas de empresa.');
    }
}
