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

$colores = ['verde' => '#3FB950', 'amarillo' => '#F2B134', 'anaranjado' => '#F0724A', 'rojo' => '#E5484D'];
$nombresComponente = ['procesos' => 'Procesos', 'memoria' => 'Memoria', 'archivos' => 'Archivos'];

$estadoIP = $resultado ? $repo->determinarEstado($instanciaId, (float) $resultado['indice_procesos']) : null;
$estadoIM = $resultado ? $repo->determinarEstado($instanciaId, (float) $resultado['indice_memoria']) : null;
$estadoIA = $resultado ? $repo->determinarEstado($instanciaId, (float) $resultado['indice_archivos']) : null;

$conteoEstados = ['verde' => 0, 'amarillo' => 0, 'anaranjado' => 0, 'rojo' => 0];
foreach ($detalle as $d) {
    $conteoEstados[$d['estado']]++;
}

function renderBarraRango(string $etiqueta, string $codigo, float $valor, string $estado, array $colores): string
{
    $pct = max(0, min(100, $valor));
    $color = $colores[$estado];
    return '
        <div class="rango-fila">
            <div class="rango-nombre">' . htmlspecialchars($etiqueta) . ' <small>(' . htmlspecialchars($codigo) . ')</small></div>
            <div class="rango-track">
                <div class="rango-zona verde"></div>
                <div class="rango-zona amarillo"></div>
                <div class="rango-zona anaranjado"></div>
                <div class="rango-zona rojo"></div>
                <div class="rango-marcador" style="left:' . $pct . '%;"></div>
            </div>
            <div class="rango-valor" style="color:' . $color . ';">' . number_format($valor, 2) . '</div>
        </div>
    ';
}
?>
    <style>
        .rango-fila{ display: grid; grid-template-columns: 220px 1fr 70px; align-items: center; gap: 1rem; padding: 0.7rem 0; border-bottom: 1px solid var(--border); }
        .rango-nombre{ font-size: 0.88rem; }
        .rango-nombre small{ color: var(--text-muted); font-family: var(--font-mono); }
        .rango-track{ position: relative; height: 10px; border-radius: 6px; display: flex; }
        .rango-zona{ height: 100%; }
        .rango-zona:first-child{ border-radius: 6px 0 0 6px; }
        .rango-zona:last-child{ border-radius: 0 6px 6px 0; }
        .rango-zona.verde{ background: #3FB950; width: 30%; }
        .rango-zona.amarillo{ background: #F2B134; width: 20%; }
        .rango-zona.anaranjado{ background: #F0724A; width: 20%; }
        .rango-zona.rojo{ background: #E5484D; width: 30%; }
        .rango-marcador{ position: absolute; top: -5px; width: 3px; height: 20px; background: var(--text); border-radius: 2px; transform: translateX(-50%); box-shadow: 0 0 0 2px var(--surface); }
        .rango-valor{ text-align: right; font-family: var(--font-mono); font-weight: 600; font-size: 0.95rem; }
        .heatmap-resumen{ display: flex; gap: 1.5rem; align-items: center; }
        .heatmap-dot{ display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:6px; }
    </style>
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

                    <div class="eval-control mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <div style="font-size:2.5rem; font-weight:700; color:<?php echo $colorGeneral; ?>;">
                                    <?php echo number_format((float) $resultado['indice_salud'], 2); ?>
                                </div>
                                <div>ISBD — <?php echo strtoupper($resultado['estado']); ?></div>
                            </div>
                            <div class="col-md-9">
                                <div class="heatmap-resumen">
                                    <span><span class="heatmap-dot" style="background:<?php echo $colores['verde']; ?>;"></span><?php echo $conteoEstados['verde']; ?> verde</span>
                                    <span><span class="heatmap-dot" style="background:<?php echo $colores['amarillo']; ?>;"></span><?php echo $conteoEstados['amarillo']; ?> amarillo</span>
                                    <span><span class="heatmap-dot" style="background:<?php echo $colores['anaranjado']; ?>;"></span><?php echo $conteoEstados['anaranjado']; ?> anaranjado</span>
                                    <span><span class="heatmap-dot" style="background:<?php echo $colores['rojo']; ?>;"></span><?php echo $conteoEstados['rojo']; ?> rojo</span>
                                </div>
                                <p class="text-muted mt-2 mb-0" style="font-size:0.85rem;">de las 10 variables monitoreadas en esta captura</p>
                            </div>
                        </div>
                    </div>

                    <div class="eval-control mb-4">
                        <h5 class="mb-3">Índices por componente</h5>
                        <?php echo renderBarraRango('Procesos', 'IP', (float) $resultado['indice_procesos'], $estadoIP, $colores); ?>
                        <?php echo renderBarraRango('Memoria', 'IM', (float) $resultado['indice_memoria'], $estadoIM, $colores); ?>
                        <?php echo renderBarraRango('Archivos', 'IA', (float) $resultado['indice_archivos'], $estadoIA, $colores); ?>
                    </div>

                    <div class="eval-control">
                        <h5 class="mb-3">Detalle por variable</h5>
                        <?php foreach ($detalle as $d): ?>
                            <?php echo renderBarraRango($d['nombre'], $d['variable_id'], $d['valor_normalizado'], $d['estado'], $colores); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Todavía no hay capturas para esta instancia. Dale clic a "Capturar ahora".</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>