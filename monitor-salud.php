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

$ayudaComponente = [
    'isbd' => [
        'titulo' => 'ISBD por color',
        'verde' => 'Base de datos saludable en general, sin problemas relevantes.',
        'amarillo' => 'Advertencia leve, hay al menos un area que conviene vigilar.',
        'anaranjado' => 'Salud degradada, uno o mas componentes necesitan atencion pronto.',
        'rojo' => 'Estado critico, se recomienda revisar de inmediato.',
    ],
    'procesos' => [
        'titulo' => 'Procesos (IP) por color',
        'verde' => 'Carga de procesos normal, sin senales de saturacion.',
        'amarillo' => 'Actividad por encima de lo usual, vigilar tendencia.',
        'anaranjado' => 'Riesgo de saturacion de procesos o sesiones bloqueadas.',
        'rojo' => 'Saturacion real: conexiones rechazadas o bloqueos serios.',
    ],
    'memoria' => [
        'titulo' => 'Memoria (IM) por color',
        'verde' => 'Uso de memoria y cache saludable, con margen disponible.',
        'amarillo' => 'Uso de memoria por encima de lo ideal.',
        'anaranjado' => 'Presion de memoria notable, rendimiento puede empezar a bajar.',
        'rojo' => 'Memoria al limite, alto riesgo de lentitud o fallos.',
    ],
    'archivos' => [
        'titulo' => 'Archivos (IA) por color',
        'verde' => 'Espacio y archivos en buen estado, sin riesgo inmediato.',
        'amarillo' => 'Espacio libre o archivos que conviene empezar a vigilar.',
        'anaranjado' => 'Espacio bajo o archivos con problemas, planificar accion.',
        'rojo' => 'Riesgo alto de quedarse sin espacio o de archivos inaccesibles.',
    ],
];

function renderRoscaGrande(float $valor, string $color): string
{
    $pct = max(0, min(100, $valor));
    return '
        <div class="rosca-css" style="background: conic-gradient(' . $color . ' 0% ' . $pct . '%, var(--border) ' . $pct . '% 100%);">
            <div class="rosca-centro"><div class="rosca-valor" style="color:' . $color . ';">' . number_format($valor, 2) . '</div></div>
        </div>
    ';
}

function renderGaugeChico(float $valor): string
{
    $pct = max(0, min(100, $valor));
    $anguloAguja = 180 - ($pct / 100 * 180);
    $rad = deg2rad($anguloAguja);
    $cx = 60; $cy = 55; $rAguja = 27;
    $xa = $cx + $rAguja * cos($rad);
    $ya = $cy - $rAguja * sin($rad);
    return '
        <svg viewBox="0 0 120 65" class="mini-gauge-svg">
            <path d="M 15.00 55.00 A 45 45 0 0 1 33.55 18.59" stroke="#3FB950" stroke-width="12" fill="none"/>
            <path d="M 33.55 18.59 A 45 45 0 0 1 60.00 10.00" stroke="#F2B134" stroke-width="12" fill="none"/>
            <path d="M 60.00 10.00 A 45 45 0 0 1 86.45 18.59" stroke="#F0724A" stroke-width="12" fill="none"/>
            <path d="M 86.45 18.59 A 45 45 0 0 1 105.00 55.00" stroke="#E5484D" stroke-width="12" fill="none"/>
            <line x1="' . $cx . '" y1="' . $cy . '" x2="' . round($xa, 2) . '" y2="' . round($ya, 2) . '" stroke="#E7ECF6" stroke-width="2.5" stroke-linecap="round"/>
            <circle cx="' . $cx . '" cy="' . $cy . '" r="4" fill="#E7ECF6"/>
        </svg>
    ';
}

function renderPopover(string $id, array $ayuda, array $colores): string
{
    $html = '<div class="popover-simple" id="pop-' . $id . '"><strong>' . htmlspecialchars($ayuda['titulo']) . '</strong><br>';
    foreach (['verde', 'amarillo', 'anaranjado', 'rojo'] as $estado) {
        $html .= '<span style="color:' . $colores[$estado] . ';">' . ucfirst($estado) . ':</span> ' . htmlspecialchars($ayuda[$estado]) . '<br>';
    }
    $html .= '</div>';
    return $html;
}

function renderBarraRango(array $d, array $colores): string
{
    $pct = max(0, min(100, $d['valor_normalizado']));
    $color = $colores[$d['estado']];
    $idPop = 'var-' . $d['variable_id'];

    $popContenido = '<strong>' . htmlspecialchars($d['nombre']) . '</strong><br>' . htmlspecialchars($d['descripcion']) . '<br><br>'
        . '<span style="color:' . $color . ';">' . strtoupper($d['estado']) . ':</span> ' . htmlspecialchars($d['banda_' . $d['estado']] ?? '');

    return '
        <div class="rango-fila">
            <div style="position:relative;">
                ' . htmlspecialchars($d['nombre']) . ' <small style="color:var(--text-muted); font-family:var(--font-mono);">(' . htmlspecialchars($d['variable_id']) . ')</small>
                <button type="button" class="btn-ayuda btn-ayuda-var" data-popover-target="' . $idPop . '">?</button>
                <div class="popover-simple popover-var" id="pop-' . $idPop . '">' . $popContenido . '</div>
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
        .rosca-css{ width:230px; height:230px; border-radius: 50%; margin: 0 auto; display:flex; align-items:center; justify-content:center; position:relative; }
        .rosca-css::before{ content:''; position:absolute; inset:16px; border-radius:50%; background:var(--surface); }
        .rosca-centro{ position:relative; z-index:1; }
        .rosca-valor{ font-family: var(--font-mono); font-weight: 700; font-size: 3rem; }

        .dash-row{ display:flex; gap:1.5rem; align-items:center; flex-wrap:wrap; }
        .dash-isbd{ flex: 0 0 260px; text-align:center; position:relative; }
        .dash-mini-col{ flex:1; min-width:280px; display:flex; flex-direction:column; gap:0.75rem; }
        .mini-gauge{ display:flex; align-items:center; gap:0.75rem; background:var(--bg); border-radius:10px; padding:0.6rem 0.9rem; }
        .mini-gauge-svg{ width:80px; flex-shrink:0; }
        .mini-gauge-valor{ font-family:var(--font-mono); font-weight:700; font-size:1.3rem; }
        .mini-gauge-label{ font-size:0.85rem; color:var(--text-muted); display:flex; align-items:center; gap:0.4rem; position:relative; }

        .btn-ayuda{ background: transparent; border: 1px solid var(--border); color: var(--text-muted); width:24px; height:24px; border-radius:50%; cursor:pointer; font-size:0.78rem; line-height:1; }
        .btn-ayuda:hover{ border-color: var(--risk-mid); color: var(--text); }
        .ayuda-isbd{ position:absolute; top:0; right:15px; width:30px; height:30px; font-size:0.9rem; }

        .popover-simple{ position:absolute; z-index:30; background:var(--surface); border:1px solid var(--border); border-radius:8px; padding:0.9rem 1rem; width:260px; font-size:0.83rem; line-height:1.5; box-shadow:0 8px 24px rgba(0,0,0,0.4); display:none; text-align:left; top:110%; left:50%; transform:translateX(-50%); }
        .popover-simple.show{ display:block; }
        .popover-var{ top:100%; left:0; transform:none; margin-top:6px; }

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
                        <div class="dash-row">
                            <div class="dash-isbd">
                                <button type="button" class="btn-ayuda ayuda-isbd" data-popover-target="isbd">?</button>
                                <?php echo renderPopover('isbd', $ayudaComponente['isbd'], $colores); ?>
                                <?php echo renderRoscaGrande((float) $resultado['indice_salud'], $colorGeneral); ?>
                            </div>
                            <div class="dash-mini-col">
                                <?php foreach (['procesos' => $estadoIP, 'memoria' => $estadoIM, 'archivos' => $estadoIA] as $comp => $estadoComp): ?>
                                    <?php $colorComp = $colores[$estadoComp]; $valorComp = (float) $resultado['indice_' . $comp]; ?>
                                    <div class="mini-gauge componente-card" role="button" data-bs-toggle="collapse" data-bs-target="#detalle-<?php echo $comp; ?>" aria-expanded="false">
                                        <?php echo renderGaugeChico($valorComp); ?>
                                        <div>
                                            <div class="mini-gauge-valor" style="color:<?php echo $colorComp; ?>;"><?php echo number_format($valorComp, 2); ?></div>
                                            <div class="mini-gauge-label">
                                                <?php echo $nombresComponente[$comp]; ?> (<?php echo $siglaComponente[$comp]; ?>)
                                                <button type="button" class="btn-ayuda" data-popover-target="<?php echo $comp; ?>" onclick="event.stopPropagation();">?</button>
                                                <?php echo renderPopover($comp, $ayudaComponente[$comp], $colores); ?>
                                                <i class="fa-solid fa-chevron-down chevron" style="font-size:0.65rem; color:var(--text-muted);"></i>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
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
        document.querySelectorAll('.componente-card').forEach((card) => {
            const target = document.querySelector(card.dataset.bsTarget);
            if (!target) return;
            target.addEventListener('show.bs.collapse', () => card.setAttribute('aria-expanded', 'true'));
            target.addEventListener('hide.bs.collapse', () => card.setAttribute('aria-expanded', 'false'));
        });

        document.querySelectorAll('[data-popover-target]').forEach((btn) => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const id = 'pop-' + this.dataset.popoverTarget;
                const pop = document.getElementById(id);
                const yaAbierto = pop.classList.contains('show');
                document.querySelectorAll('.popover-simple.show').forEach((p) => p.classList.remove('show'));
                if (!yaAbierto) pop.classList.add('show');
            });
        });

        document.addEventListener('click', function () {
            document.querySelectorAll('.popover-simple.show').forEach((p) => p.classList.remove('show'));
        });
    });
    </script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>