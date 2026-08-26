<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ClienteRepository.php';

echo '<h1 style="color:red;">PASO 1</h1>';

requiereAdmin();

echo '<h1 style="color:red;">PASO 2</h1>';

$pdo = db();

echo '<h1 style="color:red;">PASO 3</h1>';

echo '<pre style="background:#111;color:#fff;padding:20px;">';

echo "Base de datos:\n";
echo $pdo->query("SELECT DATABASE()")->fetchColumn();

echo "\n\nHost:\n";
echo $pdo->query("SELECT @@hostname")->fetchColumn();

echo "\n\nUsuario MySQL:\n";
echo $pdo->query("SELECT CURRENT_USER()")->fetchColumn();

echo "\n\nProyectos:\n";

$stmt = $pdo->query("
    SELECT
        p.id,
        p.titulo_es,
        p.titulo_en,
        p.activo,
        p.destacado,
        p.orden,
        p.cliente_id,
        c.nombre AS cliente_nombre
    FROM proyectos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    ORDER BY p.id
");

$datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($datos);

echo '</pre>';

echo '<h1 style="color:red;">PASO 4</h1>';

$repo = new ClienteRepository($pdo);

echo '<h1 style="color:red;">PASO 5</h1>';

$proyectos = $repo->listarProyectos('es');

echo '<h1 style="color:red;">PASO 6</h1>';

echo '<pre style="background:#111;color:#fff;padding:20px;">';
print_r($proyectos);
echo '</pre>';

?>
