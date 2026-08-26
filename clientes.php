<?php

echo '<h1 style="color:red;">PASO 1</h1>';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo '<h1 style="color:red;">PASO 2</h1>';

require_once __DIR__ . '/includes/auth.php';

echo '<h1 style="color:red;">PASO 3</h1>';

requiereAdmin();

echo '<h1 style="color:red;">PASO 4</h1>';

require_once __DIR__ . '/includes/db.php';

echo '<h1 style="color:red;">PASO 5</h1>';

require_once __DIR__ . '/includes/ClienteRepository.php';

echo '<h1 style="color:red;">PASO 6</h1>';

$repo = new ClienteRepository(db());

echo '<h1 style="color:red;">PASO 7</h1>';

$proyectos = $repo->listarProyectos($LANG);

echo '<h1 style="color:red;">PASO 8</h1>';

echo '<pre>';
print_r($proyectos);
echo '</pre>';
