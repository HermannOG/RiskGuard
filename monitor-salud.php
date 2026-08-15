<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
requiereAdmin();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/includes/db-monitor.php';
require_once __DIR__ . '/includes/monitor-crypto.php';
require_once __DIR__ . '/includes/MonitorAdapterInterface.php';
require_once __DIR__ . '/includes/MariaDBAdapter.php';
require_once __DIR__ . '/includes/MonitorRepository.php';

$pdo = dbMonitor();
$instanciaId = (int) ($_GET['instancia_id'] ?? 0);
$resultado = null;
$error = null;

$stmt = $pdo->prepare("SELECT * FROM monitor_instancias WHERE id = :id");
$stmt->execute(['id' => $instanciaId]);
$instancia = $stmt->fetch();

if (!$instancia) {
    header('Location: monitor-instancias.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['capturar'])) {
    try {
        if ($instancia['tipo_motor'] === 'mariadb') {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $instancia['host'], $instancia['puerto'], $instancia['nombre_bd']);
            $pdoObjetivo = new PDO($dsn, $instancia['usuario'], monitorDecrypt($instancia['password_enc']), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $adapter = new MariaDBAdapter($pdoObjetivo);
        } else {
            throw new RuntimeException('El adaptador para "' . $instancia['tipo_motor'] . '" todavia no esta implementado.');
        }

        $lecturas = $adapter->obtenerLecturas();
        $repo = new MonitorRepository($pdo);
        $capturadoEn = (new DateTime())->format('Y-m-d H:i:s.u');

        foreach ($lecturas as $variableId => $valor) {
            $repo->registrarLectura($instanciaId, $capturadoEn, $variableId, $valor);
        }
        $resultado = $repo->calcularIndices($instanciaId, $capturadoEn);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if (!$resultado) {
    $stmt = $pdo->prepare("SELECT * FROM monitor_indices WHERE instancia_id = :id ORDER BY capturado_en DESC LIMIT 1");
    $stmt->execute(['id' => $instanciaId]);
    $resultado = $stmt->fetch() ?: null;
}

$colores = ['verde' => '#2ecc71', 'amarillo' => '#f1c40f', 'anaranjado' => '#e67e22', 'rojo' => '#e74c3c'];
?>
    <main class="flex-grow-1">
        <section class="section">
            <div class="container">
                <span class="section-eyebrow"><i class="fa-solid fa-heart-pulse me-2"></i>Monitor de Salud</span>
                <h1 class="section-title"><?php echo htmlspecialchars($instancia['nombre']); ?></h1>
                <p class="section-lead"><?php echo htmlspecialchars($instancia['tipo_motor']); ?> · <?php echo htmlspecialchars($instancia['host']); ?></p>

                <form method="post" class="mb-4">
                    <button type="submit" name="capturar" value="1" class="btn btn-cta">
                        <i class="fa-solid fa-rotate me-2"></i>Capturar ahora
                    </button>
                </form>

                <?php if ($error): ?>
                    <div class="alert alert-danger">Error: <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($resultado): ?>
                    <?php $color = $colores[$resultado['estado']] ?? '#999'; ?>
                    <div class="eval-control" style="border-left: 6px solid <?php echo $color; ?>;">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div style="font-size:2rem; font-weight:700; color:<?php echo $color; ?>;">
                                    <?php echo number_format((float) $resultado['indice_salud'], 2); ?>
                                </div>
                                <div>ISBD (<?php echo strtoupper($resultado['estado']); ?>)</div>
                            </div>
                            <div class="col-md-3">
                                <div style="font-size:1.4rem;"><?php echo number_format((float) $resultado['indice_procesos'], 2); ?></div>
                                <div>Procesos (IP)</div>
                            </div>
                            <div class="col-md-3">
                                <div style="font-size:1.4rem;"><?php echo number_format((float) $resultado['indice_memoria'], 2); ?></div>
                                <div>Memoria (IM)</div>
                            </div>
                            <div class="col-md-3">
                                <div style="font-size:1.4rem;"><?php echo number_format((float) $resultado['indice_archivos'], 2); ?></div>
                                <div>Archivos (IA)</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Todavía no hay capturas para esta instancia. Dale clic a "Capturar ahora".</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>