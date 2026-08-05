<?php
/**
 * Selección de idioma activo (EN por defecto, cambiable a ES desde el navbar).
 * Debe incluirse después de session_start() y antes de cualquier salida HTML.
 */

$idiomasValidos = ['en', 'es'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $idiomasValidos, true)) {
    $_SESSION['lang'] = $_GET['lang'];

    $query = $_GET;
    unset($query['lang']);
    $queryString = http_build_query($query);
    $destino = strtok($_SERVER['REQUEST_URI'], '?') . ($queryString ? '?' . $queryString : '');

    header('Location: ' . $destino);
    exit;
}

$LANG = $_SESSION['lang'] ?? 'en';
if (!in_array($LANG, $idiomasValidos, true)) {
    $LANG = 'en';
}

$STRINGS = require __DIR__ . '/lang/' . $LANG . '.php';

function t(string $key): string
{
    global $STRINGS;
    return $STRINGS[$key] ?? $key;
}

function langSwitchUrl(string $lang): string
{
    $query = $_GET;
    $query['lang'] = $lang;
    $path = strtok($_SERVER['REQUEST_URI'], '?');
    return $path . '?' . http_build_query($query);
}
