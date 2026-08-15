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
$siglaComponente = ['procesos' => 'IP', 'memoria' => 'IM', 'archivos' => 'IA'];

$estadoIP = $resultado ? $repo->determinarEstado($instanciaId, (float) $resultado['indice_procesos']) : null;
$estadoIM = $resultado ? $repo->determinarEstado($instanciaId, (float) $resultado['indice_memoria']) : null;
$estadoIA = $resultado ? $repo->determinarEstado($instanciaId, (float) $resultado['indice_archivos']) : null;

$detallePorComponente = ['procesos' => [], 'memoria' => [], 'archivos' => []];
foreach ($detalle as $d) {
    $detallePorComponente[$d['componente']][] = $d;
}

function renderRosca(string $idHtml, float $valor, string $color, string $tamano = 'grande'): string
{
    $pct = max(0, min(100, $valor));
    $tam = $tamano === 'grande' ? '150px' : '100px';
    $fuenteValor = $tamano === 'grande' ? '1.7rem' : '1rem';
    return '
        <div class="rosca-css" style="width:' . $tam . '; height:' . $tam . '; background: conic-gradient(' . $color . ' 0% ' . $pct . '%, var(--border) ' . $pct . '% 100%);">
            <div class="rosca-centro">
                <div class="rosca-valor" style="font-size:' . $fuenteValor . '; color:' . $color . ';">' . number_format($valor, 2) . '</div>
            </div>
        </div>
    ';
}

function renderBarraRango(array $d, array $colores): string
{
    $pct = max(0, min(100, $d['valor_normalizado']));
    $color = $colores[$d['estado']];
    $popoverContenido = htmlspecialchars($d['descripcion'] . "\n\n" . strtoupper($d['estado']) . ': ' . ($d['banda_' . $d['estado']] ?? ''));

    return '
        <div class="rango-fila">
            <div>
                ' . htmlspecialchars($d['nombre']) . ' <small style="color:var(--text-muted); font-family:var(--font-mono);">(' . htmlspecialchars($d['variable_id']) . ')</small>
                <button type="button" class="btn-ayuda-var" data-bs-toggle="popover" data-bs-trigger="focus click" data-bs-placement="top" data-bs-content="' . $popoverContenido . '">
                    <i class="fa-solid fa-circle-info"></i>
                </button>
            </div>
            <div>
                <div class="rango-track">
                    <div class="rango-zona verde"></div>
                    <div class="rango-zona amarillo"></div>
                    <div class="rango-zona anaranjado"></div>
                    <div class="rango-zona rojo"></div>
                    <div class="rango-marcador" style="left:' . $pct . '%;"></div>
                </div>
                <div class="rango-ticks"><span>0</span><span>30</span><span>50</span><span>70</span><span>100</span></div>
            </div>
            <div class="rango-valor" style="color:' . $color . ';">' . number_format($d['valor_normalizado'], 2) . '</div>
        </div>
    ';
}
?>
    <style>
        .rosca-css{ border-radius: 50%; margin: 0 auto; display:flex; align-items:center; justify-content:center; position:relative; }
        .rosca-css::before{ content:''; position:absolute; inset:12px; border-radius:50%; background:var(--surface); }
        .rosca-centro{ position:relative; text-align:center; z-index:1; }
        .rosca-valor{ font-family: var(--font-mono); font-weight: 700; }
        .rosca-label{ color: var(--text-muted); font-size: 0.78rem; }

        .componente-card{ cursor: pointer; transition: border-color 0.15s ease; }
        .componente-card:hover{ border-color: var(--risk-mid); }
        .componente-card .chevron{ transition: transform 0.2s ease; }
        .componente-card[aria-expanded="true"] .chevron{ transform: rotate(180deg); }

        .rango-fila{ display: grid; grid-template-columns: 240px 1fr 70px; align-items: center; gap: 1rem; padding: 0.6rem 0; border-bottom: 1px solid var(--border); }
        .rango-fila:last-child{ border-bottom: none; }
        .rango-track{ position: relative; height: 10px; border-radius: 6px; display: flex; }
        .rango-zona{ height: 100%; }
        .rango-zona:first-child{ border-radius: 6px 0 0 6px; }
        .rango-zona:last-child{ border-radius: 0 6px 6px 0; }
        .rango-zona.verde{ background: #3FB950; width: 30%; }
        .rango-zona.amarillo{ background: #F2B134; width: 20%; }
        .rango-zona.anaranjado{ background: #F0724A; width: 20%; }
        .rango-zona.rojo{ background: #E5484D; width: 30%; }
        .rango-marcador{ position: absolute; top: -5px; width: 3px; height: 20px; background: var(--text); border-radius: 2px; transform: translateX(-50%); box-shadow: 0 0 0 2px var(--surface); }
        .rango-ticks{ display:flex; font-family: var(--font-mono); font-size: 0.65rem; color: var(--text-muted); margin-top: 2px; }
        .rango-ticks span{ width: 30%; }
        .rango-ticks span:nth-child(2), .rango-ticks span:nth-child(3){ width: 20%; }
        .rango-valor{ text-align: right; font-family: var(--font-mono); font-weight: 600; font-size: 0.95rem; }

        .btn-ayuda-var{ background: transparent; border: none; color: var(--text-muted); padding: 0 0.2rem; cursor: pointer; }
        .btn-ayuda-var:hover{ color: var(--risk-mid); }
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

                    <div class="text-center mb-4">
                        <?php echo renderRosca('isbd', (float) $resultado['indice_salud'], $colorGeneral, 'grande'); ?>
                        <div class="rosca-label mt-2">ISBD — <?php echo strtoupper($resultado['estado']); ?></div>
                    </div>

                    <div class="row g-3 mb-2">
                        <?php foreach (['procesos' => $estadoIP, 'memoria' => $estadoIM, 'archivos' => $estadoIA] as $comp => $estadoComp): ?>
                            <?php $colorComp = $colores[$estadoComp]; $valorComp = (float) $resultado['indice_' . $comp]; ?>
                            <div class="col-4">
                                <div class="eval-control componente-card text-center" role="button" data-bs-toggle="collapse" data-bs-target="#detalle-<?php echo $comp; ?>" aria-expanded="false">
                                    <?php echo renderRosca($comp, $valorComp, $colorComp, 'chica'); ?>
                                    <div style="font-size:0.85rem; margin-top:0.6rem;">
                                        <?php echo $nombresComponente[$comp]; ?> (<?php echo $siglaComponente[$comp]; ?>)
                                        <i class="fa-solid fa-chevron-down chevron ms-1" style="font-size:0.7rem; color:var(--text-muted);"></i>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php foreach (['procesos', 'memoria', 'archivos'] as $comp): ?>
                        <div class="collapse mb-3" id="detalle-<?php echo $comp; ?>">
                            <div class="eval-control">
                                <h6 class="mb-3">Por qué <?php echo $nombresComponente[$comp]; ?> (<?php echo $siglaComponente[$comp]; ?>) está así</h6>
                                <?php foreach ($detallePorComponente[$comp] as $d): ?>
                                    <?php echo renderBarraRango($d, $colores); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php else: ?>
                    <p>Todavía no hay capturas para esta instancia. Dale clic a "Capturar ahora".</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="popover"]').forEach((el) => new bootstrap.Popover(el));

        document.querySelectorAll('.componente-card').forEach((card) => {
            const target = document.querySelector(card.dataset.bsTarget);
            if (!target) return;
            target.addEventListener('show.bs.collapse', () => card.setAttribute('aria-expanded', 'true'));
            target.addEventListener('hide.bs.collapse', () => card.setAttribute('aria-expanded', 'false'));
        });
    });
    </script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>