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
$lang = $LANG ?? 'es';

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
        $detalle = $repo->obtenerDetalleLecturas($instanciaId, $capturadoEn, $lang);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if (!$resultado) {
    $stmt = $pdo->prepare("SELECT * FROM monitor_indices WHERE instancia_id = :id ORDER BY capturado_en DESC LIMIT 1");
    $stmt->execute(['id' => $instanciaId]);
    $resultado = $stmt->fetch() ?: null;
    if ($resultado) {
        $detalle = $repo->obtenerDetalleLecturas($instanciaId, $resultado['capturado_en'], $lang);
    }
}

$historial = $repo->obtenerHistorialIndices($instanciaId, 10);
// Quitamos de la lista la que ya se muestra arriba como "actual"
if ($resultado) {
    $historial = array_values(array_filter($historial, fn($h) => $h['capturado_en'] !== $resultado['capturado_en']));
}

$colores = ['verde' => '#3FB950', 'amarillo' => '#F2B134', 'anaranjado' => '#F0724A', 'rojo' => '#E5484D'];
$nombresComponente = [
    'procesos' => t('monitor.componente.procesos'),
    'memoria'  => t('monitor.componente.memoria'),
    'archivos' => t('monitor.componente.archivos'),
];
$siglaComponente = ['procesos' => 'IP', 'memoria' => 'IM', 'archivos' => 'IA'];

$estadoIP = $resultado ? $repo->determinarEstado($instanciaId, (float) $resultado['indice_procesos']) : null;
$estadoIM = $resultado ? $repo->determinarEstado($instanciaId, (float) $resultado['indice_memoria']) : null;
$estadoIA = $resultado ? $repo->determinarEstado($instanciaId, (float) $resultado['indice_archivos']) : null;

$detallePorComponente = ['procesos' => [], 'memoria' => [], 'archivos' => []];
foreach ($detalle as $d) {
    $detallePorComponente[$d['componente']][] = $d;
}
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total_capturas,
           MIN(capturado_en) AS primera,
           MAX(capturado_en) AS ultima
    FROM monitor_indices
    WHERE instancia_id = :id
");
$stmt->execute(['id' => $instanciaId]);
$ctx = $stmt->fetch() ?: ['total_capturas' => 0, 'primera' => null, 'ultima' => null];


$ayudaComponente = [
    'isbd' => [
        'titulo' => t('monitor.ayuda.isbd.titulo'),
        'verde' => t('monitor.ayuda.isbd.verde'),
        'amarillo' => t('monitor.ayuda.isbd.amarillo'),
        'anaranjado' => t('monitor.ayuda.isbd.anaranjado'),
        'rojo' => t('monitor.ayuda.isbd.rojo'),
    ],
    'procesos' => [
        'titulo' => t('monitor.ayuda.procesos.titulo'),
        'verde' => t('monitor.ayuda.procesos.verde'),
        'amarillo' => t('monitor.ayuda.procesos.amarillo'),
        'anaranjado' => t('monitor.ayuda.procesos.anaranjado'),
        'rojo' => t('monitor.ayuda.procesos.rojo'),
    ],
    'memoria' => [
        'titulo' => t('monitor.ayuda.memoria.titulo'),
        'verde' => t('monitor.ayuda.memoria.verde'),
        'amarillo' => t('monitor.ayuda.memoria.amarillo'),
        'anaranjado' => t('monitor.ayuda.memoria.anaranjado'),
        'rojo' => t('monitor.ayuda.memoria.rojo'),
    ],
    'archivos' => [
        'titulo' => t('monitor.ayuda.archivos.titulo'),
        'verde' => t('monitor.ayuda.archivos.verde'),
        'amarillo' => t('monitor.ayuda.archivos.amarillo'),
        'anaranjado' => t('monitor.ayuda.archivos.anaranjado'),
        'rojo' => t('monitor.ayuda.archivos.rojo'),
    ],
];

function renderRoscaGrande(float $valor, string $color, int $tam = 230): string
{
    $pct = max(0, min(100, $valor));
    $fuente = round($tam / 230 * 3, 2);
    return '
        <div class="rosca-css" style="width:' . $tam . 'px; height:' . $tam . 'px; background: conic-gradient(' . $color . ' 0% ' . $pct . '%, var(--border) ' . $pct . '% 100%);">
            <div class="rosca-centro"><div class="rosca-valor" style="color:' . $color . '; font-size:' . $fuente . 'rem;">' . number_format($valor, 2) . '</div></div>
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

    $popContenido = '<strong>' . htmlspecialchars($d['nombre']) . '</strong><br>' . htmlspecialchars($d['descripcion']) . '<br><br>';
    foreach (['verde', 'amarillo', 'anaranjado', 'rojo'] as $estadoBanda) {
        $popContenido .= '<span style="color:' . $colores[$estadoBanda] . ';">' . ucfirst($estadoBanda) . ':</span> ' . htmlspecialchars($d['banda_' . $estadoBanda] ?? '') . '<br>';
    }

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
                <div class="rango-ticks"><span style="left:0%;">0</span><span style="left:30%;">30</span><span style="left:50%;">50</span><span style="left:70%;">70</span><span style="left:100%;">100</span></div>
            </div>
            <div class="rango-valor" style="color:' . $color . ';">' . number_format($d['valor_normalizado'], 2) . '</div>
        </div>
    ';
}

function renderTablaContexto(array $instancia, ?array $resultado, array $detalle, array $ctx, array $colores): string
{
    $fecha = static function (?string $ts): string {
        if (empty($ts)) { return '—'; }
        try {
            return (new DateTime($ts))->format('d/m/Y H:i:s');
        } catch (Throwable $e) {
            return htmlspecialchars($ts);
        }
    };

    $estado = $resultado['estado'] ?? null;
    $color  = $estado ? ($colores[$estado] ?? 'var(--text-muted)') : 'var(--text-muted)';

    $filas = [
        ['Instancia',        htmlspecialchars($instancia['nombre'])],
        ['Motor',            strtoupper(htmlspecialchars($instancia['tipo_motor']))],
        ['Servidor',         htmlspecialchars($instancia['host'] . ':' . $instancia['puerto'])],
        ['Base de datos',    htmlspecialchars($instancia['nombre_bd'])],
        ['Usuario',          htmlspecialchars($instancia['usuario'])],
        ['Variables leídas', count($detalle) . ' variables'],
        ['Última captura',   $fecha($ctx['ultima'] ?? null)],
        ['Primera captura',  $fecha($ctx['primera'] ?? null)],
        ['Capturas acumuladas', (string) ((int) ($ctx['total_capturas'] ?? 0))],
        ['Ponderación ISBD', 'IP 25% · IM 60% · IA 15%'],
    ];

    $html = '<div class="tabla-contexto-wrap">'
          . '<div class="tabla-contexto-titulo">Contexto de la base de datos monitoreada</div>'
          . '<table class="tabla-contexto"><tbody>';

    foreach ($filas as [$etiqueta, $valor]) {
        $html .= '<tr><th>' . $etiqueta . '</th><td>' . $valor . '</td></tr>';
    }

    $html .= '<tr><th>Estado actual</th><td>'
           . '<span class="ctx-punto" style="background:' . $color . ';"></span>'
           . ($estado ? strtoupper(htmlspecialchars($estado)) : 'SIN DATOS')
           . '</td></tr>';

    $html .= '</tbody></table></div>';

    return $html;
}

function formatearFecha(string $capturadoEn, string $lang): string
{
    $meses_es = [1=>'ene',2=>'feb',3=>'mar',4=>'abr',5=>'may',6=>'jun',7=>'jul',8=>'ago',9=>'sep',10=>'oct',11=>'nov',12=>'dic'];
    $meses_en = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
    $meses = $lang === 'en' ? $meses_en : $meses_es;

    $dt = new DateTime($capturadoEn);
    $hora24 = (int) $dt->format('H');
    $ampm = $hora24 >= 12 ? 'PM' : 'AM';
    $hora12 = $hora24 % 12; if ($hora12 === 0) $hora12 = 12;

    if ($lang === 'en') {
        return $meses[(int) $dt->format('n')] . ' ' . $dt->format('j') . ', ' . $dt->format('Y') . ', ' . $hora12 . ':' . $dt->format('i') . ' ' . $ampm;
    }
    return $dt->format('j') . ' ' . $meses[(int) $dt->format('n')] . ' ' . $dt->format('Y') . ', ' . $hora12 . ':' . $dt->format('i') . ' ' . $ampm;
}
?>
    <style>
        .rosca-css{ border-radius: 50%; margin: 0 auto; display:flex; align-items:center; justify-content:center; position:relative; }
        .rosca-css::before{ content:''; position:absolute; inset:16px; border-radius:50%; background:var(--surface); }
        .rosca-centro{ position:relative; z-index:1; }
        .rosca-valor{ font-family: var(--font-mono); font-weight: 700; }

        .dash-row{ display:flex; gap:1.5rem; align-items:center; flex-wrap:wrap; }
        .dash-isbd{ flex: 0 0 260px; text-align:center; position:relative; }
        .dash-mini-col{ flex:1; min-width:280px; display:flex; flex-direction:column; gap:0.75rem; }
        .mini-gauge{ display:flex; align-items:center; gap:0.75rem; background:var(--bg); border-radius:10px; padding:0.6rem 0.9rem; }
        .mini-gauge-svg{ width:80px; flex-shrink:0; }
        .mini-gauge-valor{ font-family:var(--font-mono); font-weight:700; font-size:1.3rem; }
        .mini-gauge-label{ font-size:0.85rem; color:var(--text-muted); display:flex; align-items:center; gap:0.4rem; position:relative; }

        .btn-ayuda{ background: transparent; border: 1px solid var(--border); color: var(--text-muted); width:24px; height:24px; border-radius:50%; cursor:pointer; font-size:0.78rem; line-height:1; }
        .btn-ayuda:hover{ border-color: var(--risk-mid); color: var(--text); }
        .ayuda-isbd-wrap{ position:absolute; top:0; right:15px; }

        .popover-simple{ position:absolute; z-index:30; background:var(--surface); border:1px solid var(--border); border-radius:8px; padding:0.9rem 1rem; width:260px; font-size:0.83rem; line-height:1.5; box-shadow:0 8px 24px rgba(0,0,0,0.4); display:none; text-align:left; top:calc(100% + 10px); right:0; }
        .popover-simple.show{ display:block; }
        .popover-simple::before{ content:''; position:absolute; top:-6px; right:10px; width:12px; height:12px; background:var(--surface); border-left:1px solid var(--border); border-top:1px solid var(--border); transform:rotate(45deg); }
        .popover-var{ top:100%; left:0; right:auto; margin-top:6px; }
        .popover-var::before{ display:none; }

        .dash-mini-row{ display:flex; gap:1rem; margin-top:1.5rem; flex-wrap:wrap; }
        .mini-gauge-h{background:var(--bg); border-radius:12px; padding:1.2rem; text-align:center; cursor:pointer; transition:border-color 0.15s ease; border:1px solid transparent; }
        .mini-gauge-h:hover{ border-color:var(--risk-mid); }
        .mini-gauge-h svg{ width:130px; }
        .mini-gauge-h-valor{ font-family:var(--font-mono); font-weight:700; font-size:1.8rem; margin-top:0.3rem; }
        .mini-gauge-h-label{ font-size:0.95rem; color:var(--text-muted); margin-top:0.3rem; display:flex; align-items:center; justify-content:center; gap:0.4rem; position:relative; }
        .mini-gauge-h-wrap{ position:relative; flex:1; min-width:180px; }
        .mini-ayuda-btn{ position:absolute; top:10px; right:10px; z-index:5; }
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
        .rango-ticks{ position:relative; height:14px; font-family: var(--font-mono); font-size: 0.65rem; color: var(--text-muted); margin-top: 2px; }
        .rango-ticks span{ position:absolute; top:0; transform:translateX(-50%); }
        .rango-ticks span:first-child{ transform:translateX(0); }
        .rango-ticks span:last-child{ transform:translateX(-100%); }
        .rango-valor{ text-align: right; font-family: var(--font-mono); font-weight: 600; font-size: 0.95rem; }

        .hist-titulo{ margin-bottom:1rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; font-size:0.78rem; font-family:var(--font-mono); }
        .hist-fila{ display:flex; align-items:center; gap:1rem; padding:0.8rem 1rem; border-radius:8px; cursor:pointer; transition:background 0.15s ease; background:var(--bg); margin-bottom:0.6rem; border:none; width:100%; text-align:left; }
        .hist-fila:hover{ background:var(--surface-alt); }
        .hist-dot{ width:12px; height:12px; border-radius:50%; flex-shrink:0; }
        .hist-fecha{ flex:1; font-size:0.88rem; color:var(--text-muted); }
        .hist-valor{ font-family:var(--font-mono); font-weight:700; font-size:1.1rem; }
        .hist-chevron{ color:var(--text-muted); font-size:0.75rem; transition:transform 0.2s ease; }
        .hist-fila[aria-expanded="true"] .hist-chevron{ transform:rotate(180deg); }
        .hist-detalle{ padding: 1rem; margin-bottom: 0.6rem; }
        .hist-mini-row{ display:flex; gap:1rem; align-items:center; flex-wrap:wrap; }

        .comp-block{ background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.25rem; border-left: 4px solid var(--border); }
        .comp-block-header{ display:flex; align-items:center; gap:0.7rem; margin-bottom:1rem; }
        .comp-block-icon{ width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.06); font-size:1rem; flex-shrink:0; }
        .comp-block-title{ font-size:1.05rem; font-weight:600; }
        .comp-block-sub{ font-size:0.8rem; color:var(--text-muted); }

        .isbd-row{ display:flex; gap:2rem; align-items:center; justify-content:center; flex-wrap:wrap; margin-top:1rem; }
        .tabla-contexto-wrap{ flex:1 1 340px; max-width:460px; background:var(--bg); border:1px solid var(--border); border-radius:12px; padding:1rem 1.2rem; }                .tabla-contexto-titulo{ font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); margin-bottom:0.7rem; font-family:var(--font-mono); }
        .tabla-contexto{ width:100%; border-collapse:collapse; font-size:0.85rem; }
        .tabla-contexto th{ text-align:left; font-weight:500; color:var(--text-muted); padding:0.3rem 0.6rem 0.3rem 0; white-space:nowrap; }
        .tabla-contexto td{ text-align:right; font-family:var(--font-mono); padding:0.3rem 0; word-break:break-all; }
        .tabla-contexto tr + tr th, .tabla-contexto tr + tr td{ border-top:1px solid var(--border); }
        .ctx-punto{ display:inline-block; width:9px; height:9px; border-radius:50%; margin-right:0.4rem; vertical-align:middle; }

    </style>
    <main class="flex-grow-1">
        <section class="section">
            <div class="container">
                <span class="section-eyebrow"><i class="fa-solid fa-heart-pulse me-2"></i><?php echo t('monitor.nav.item'); ?></span>
                <h1 class="section-title"><?php echo htmlspecialchars($instancia['nombre']); ?></h1>
                <p class="section-lead"><?php echo htmlspecialchars($instancia['tipo_motor']); ?> · <?php echo htmlspecialchars($instancia['host']); ?></p>

                <form method="post" class="mb-4">
                    <button type="submit" name="capturar" value="1" class="btn btn-cta">
                        <i class="fa-solid fa-rotate me-2"></i><?php echo t('monitor.salud.capturar'); ?>
                    </button>
                </form>

                <?php if ($error): ?>
                    <div class="alert alert-danger">Error: <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($resultado): ?>
                    <?php $colorGeneral = $colores[$resultado['estado']] ?? '#999'; ?>

                    <div class="eval-control mb-4">
                        <h2 class="section-title" style="font-size:1.3rem; margin-top:0;"><?php echo t('monitor.isbd.titulo'); ?></h2>
                        <p class="section-lead" style="max-width:60ch;"><?php echo t('monitor.isbd.intro'); ?></p>

                        <div class="isbd-row">
                            <div class="dash-isbd" style="position:relative;">
                                <div class="ayuda-isbd-wrap">
                                    <button type="button" class="btn-ayuda" data-popover-target="isbd">?</button>
                                    <?php echo renderPopover('isbd', $ayudaComponente['isbd'], $colores); ?>
                                </div>
                                <?php echo renderRoscaGrande((float) $resultado['indice_salud'], $colorGeneral); ?>
                            </div>
                            <?php echo renderTablaContexto($instancia, $resultado, $detalle, $ctx, $colores); ?>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-ghost" data-bs-toggle="collapse" data-bs-target="#isbd-panel" aria-expanded="false">
                                <?php echo t('monitor.isbd.ver'); ?> <i class="fa-solid fa-chevron-down ms-1"></i>
                            </button>
                        </div>

                        <div class="collapse mt-4" id="isbd-panel">
                            <div class="dash-mini-row">
                                <?php foreach (['procesos' => $estadoIP, 'memoria' => $estadoIM, 'archivos' => $estadoIA] as $comp => $estadoComp): ?>
                                    <?php $colorComp = $colores[$estadoComp]; $valorComp = (float) $resultado['indice_' . $comp]; ?>
                                    <div class="mini-gauge-h-wrap">
                                        <button type="button" class="btn-ayuda mini-ayuda-btn" data-popover-target="<?php echo $comp; ?>">?</button>
                                        <?php echo renderPopover($comp, $ayudaComponente[$comp], $colores); ?>
                                        <div class="mini-gauge-h componente-card" role="button" data-bs-toggle="collapse" data-bs-target="#detalle-<?php echo $comp; ?>" aria-expanded="false">
                                            <?php echo renderGaugeChico($valorComp); ?>
                                            <div class="mini-gauge-h-valor" style="color:<?php echo $colorComp; ?>;"><?php echo number_format($valorComp, 2); ?></div>
                                            <div class="mini-gauge-h-label">
                                                <?php echo $nombresComponente[$comp]; ?> (<?php echo $siglaComponente[$comp]; ?>)
                                                <i class="fa-solid fa-chevron-down chevron" style="font-size:0.65rem; color:var(--text-muted);"></i>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php
                            $iconoComponente = ['procesos' => 'fa-microchip', 'memoria' => 'fa-memory', 'archivos' => 'fa-folder-open'];
                            $subtituloDetalle = $lang === 'en' ? 'Variables that make up this index' : 'Variables que forman este índice';
                            ?>
                            <?php foreach (['procesos' => $estadoIP, 'memoria' => $estadoIM, 'archivos' => $estadoIA] as $comp => $estadoComp): ?>
                                <?php $colorComp = $colores[$estadoComp]; ?>
                                <div class="collapse mt-3" id="detalle-<?php echo $comp; ?>">
                                    <div class="comp-block" style="border-left-color:<?php echo $colorComp; ?>;">
                                        <div class="comp-block-header">
                                            <div class="comp-block-icon" style="color:<?php echo $colorComp; ?>;"><i class="fa-solid <?php echo $iconoComponente[$comp]; ?>"></i></div>
                                            <div>
                                                <div class="comp-block-title"><?php echo $nombresComponente[$comp]; ?> <span style="color:var(--text-muted); font-weight:400;">(<?php echo $siglaComponente[$comp]; ?>)</span></div>
                                                <div class="comp-block-sub"><?php echo $subtituloDetalle; ?></div>
                                            </div>
                                        </div>
                                        <?php foreach ($detallePorComponente[$comp] as $d): ?>
                                            <?php echo renderBarraRango($d, $colores); ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                <?php else: ?>
                    <p><?php echo t('monitor.salud.sincapturas'); ?></p>
                <?php endif; ?>

                <?php if (!empty($historial)): ?>
                    <div class="eval-control mt-4">
                            <button type="button" class="btn btn-ghost mb-3" data-bs-toggle="collapse" data-bs-target="#historial-panel" aria-expanded="false">
                            <?php echo $lang === 'en' ? 'View history' : 'Ver historial'; ?> <i class="fa-solid fa-chevron-down ms-1"></i>
                        </button>
                        <div class="collapse" id="historial-panel">
                        <?php foreach ($historial as $i => $h): ?>
                            <?php $idHist = 'hist-' . $i; $colorH = $colores[$h['estado']]; ?>
                            <button type="button" class="hist-fila" data-bs-toggle="collapse" data-bs-target="#<?php echo $idHist; ?>" aria-expanded="false">
                                <span class="hist-dot" style="background:<?php echo $colorH; ?>;"></span>
                                <span class="hist-fecha"><?php echo formatearFecha($h['capturado_en'], $lang); ?></span>
                                <span class="hist-valor" style="color:<?php echo $colorH; ?>;"><?php echo number_format((float) $h['indice_salud'], 2); ?></span>
                                <i class="fa-solid fa-chevron-down hist-chevron"></i>
                            </button>
                            <div class="collapse" id="<?php echo $idHist; ?>">
                                <div class="hist-detalle">
                                    <div class="hist-mini-row">
                                        <?php echo renderRoscaGrande((float) $h['indice_salud'], $colorH, 110); ?>
                                        <?php foreach (['procesos', 'memoria', 'archivos'] as $comp): ?>
                                            <?php $estadoH = $repo->determinarEstado($instanciaId, (float) $h['indice_' . $comp]); $colorCompH = $colores[$estadoH]; ?>
                                            <div class="mini-gauge" style="flex:1; min-width:150px;">
                                                <?php echo renderGaugeChico((float) $h['indice_' . $comp]); ?>
                                                <div>
                                                    <div class="mini-gauge-valor" style="color:<?php echo $colorCompH; ?>;"><?php echo number_format((float) $h['indice_' . $comp], 2); ?></div>
                                                    <div class="mini-gauge-label"><?php echo $nombresComponente[$comp]; ?> (<?php echo $siglaComponente[$comp]; ?>)</div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </section>
    </main>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[role="button"][data-bs-toggle="collapse"], .hist-fila').forEach((card) => {
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