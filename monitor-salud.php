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
$detalle = [];
$error = null;

$stmt = $pdo->prepare("SELECT * FROM monitor_instancias WHERE id = :id");
$stmt->execute(['id' => $instanciaId]);
$instancia = $stmt->fetch();

if (!$instancia) {
    header('Location: monitor-instancias.php');
    exit;
}

$repo = new MonitorRepository($pdo);

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
        $capturadoEn = (new DateTime())->format('Y-m-d H:i:s.u');

        foreach ($lecturas as $variableId => $valor) {
            $repo->registrarLectura($instanciaId, $capturadoEn, $variableId, $valor);
        }
        $resultado = $repo->calcularIndices($instanciaId, $capturadoEn);
        $detalle = $repo->obtenerDetalleLecturas($instanciaId, $capturadoEn);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if (!$resultado) {
    $stmt = $pdo->prepare("SELECT * FROM monitor_indices WHERE instancia_id = :id ORDER BY capturado_en DESC LIMIT 1");
    $stmt->execute(['id' => $instanciaId]);
    $resultado = $stmt->fetch() ?: null;
    if ($resultado) {
        $detalle = $repo->obtenerDetalleLecturas($instanciaId, $resultado['capturado_en']);
    }
}

$colores = ['verde' => '#2ecc71', 'amarillo' => '#f1c40f', 'anaranjado' => '#e67e22', 'rojo' => '#e74c3c'];
$nombresComponente = ['procesos' => 'Procesos', 'memoria' => 'Memoria', 'archivos' => 'Archivos'];

$estadoIP = $resultado ? $repo->determinarEstado($instanciaId, (float) $resultado['indice_procesos']) : null;
$estadoIM = $resultado ? $repo->determinarEstado($instanciaId, (float) $resultado['indice_memoria']) : null;
$estadoIA = $resultado ? $repo->determinarEstado($instanciaId, (float) $resultado['indice_archivos']) : null;
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
                    <?php $colorGeneral = $colores[$resultado['estado']] ?? '#999'; ?>

                    <div class="eval-control mb-4" style="border-left: 6px solid <?php echo $colorGeneral; ?>;">
                        <div style="text-align:center;">
                            <div style="font-size:2.5rem; font-weight:700; color:<?php echo $colorGeneral; ?>;">
                                <?php echo number_format((float) $resultado['indice_salud'], 2); ?>
                            </div>
                            <div>Índice de Salud de la Base de Datos (ISBD) — <?php echo strtoupper($resultado['estado']); ?></div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="eval-control text-center" style="border-left: 6px solid <?php echo $colores[$estadoIP]; ?>;">
                                <div style="font-size:1.6rem; font-weight:700; color:<?php echo $colores[$estadoIP]; ?>;">
                                    <?php echo number_format((float) $resultado['indice_procesos'], 2); ?>
                                </div>
                                <div>Procesos (IP) — <?php echo strtoupper($estadoIP); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="eval-control text-center" style="border-left: 6px solid <?php echo $colores[$estadoIM]; ?>;">
                                <div style="font-size:1.6rem; font-weight:700; color:<?php echo $colores[$estadoIM]; ?>;">
                                    <?php echo number_format((float) $resultado['indice_memoria'], 2); ?>
                                </div>
                                <div>Memoria (IM) — <?php echo strtoupper($estadoIM); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="eval-control text-center" style="border-left: 6px solid <?php echo $colores[$estadoIA]; ?>;">
                                <div style="font-size:1.6rem; font-weight:700; color:<?php echo $colores[$estadoIA]; ?>;">
                                    <?php echo number_format((float) $resultado['indice_archivos'], 2); ?>
                                </div>
                                <div>Archivos (IA) — <?php echo strtoupper($estadoIA); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="eval-control mb-4">
                        <h5 class="mb-3">Detalle por variable</h5>
                        <canvas id="chartVariables" height="90"></canvas>
                    </div>

                    <div class="eval-control">
                        <table class="table mb-0">
                            <thead><tr><th>Variable</th><th>Componente</th><th>Valor crudo</th><th>Normalizado</th><th>Estado</th></tr></thead>
                            <tbody>
                                <?php foreach ($detalle as $d): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($d['nombre']); ?> <small class="text-muted">(<?php echo htmlspecialchars($d['variable_id']); ?>)</small></td>
                                        <td><?php echo $nombresComponente[$d['componente']] ?? $d['componente']; ?></td>
                                        <td><?php echo number_format($d['valor_crudo'], 2); ?></td>
                                        <td><?php echo number_format($d['valor_normalizado'], 2); ?></td>
                                        <td>
                                            <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:<?php echo $colores[$d['estado']]; ?>; margin-right:6px;"></span>
                                            <?php echo strtoupper($d['estado']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
                    <script>
                    (function () {
                        const datos = <?php echo json_encode($detalle); ?>;
                        const coloresEstado = <?php echo json_encode($colores); ?>;

                        new Chart(document.getElementById('chartVariables'), {
                            type: 'bar',
                            data: {
                                labels: datos.map(d => d.variable_id + ' - ' + d.nombre),
                                datasets: [{
                                    label: 'Valor normalizado (0-100, mas alto = peor)',
                                    data: datos.map(d => d.valor_normalizado),
                                    backgroundColor: datos.map(d => coloresEstado[d.estado])
                                }]
                            },
                            options: {
                                indexAxis: 'y',
                                scales: { x: { min: 0, max: 100 } },
                                plugins: { legend: { display: false } }
                            }
                        });
                    })();
                    </script>
                <?php else: ?>
                    <p>Todavía no hay capturas para esta instancia. Dale clic a "Capturar ahora".</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>