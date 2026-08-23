<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
requiereAdmin();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/includes/db-monitor.php';
require_once __DIR__ . '/includes/MonitorAdapterInterface.php';
require_once __DIR__ . '/includes/MariaDBAdapter.php';
require_once __DIR__ . '/includes/OracleAdapter.php';
require_once __DIR__ . '/includes/MonitorRepository.php';
require_once __DIR__ . '/includes/monitor-render.php';

$pdo = dbMonitor();
$instanciaId = (int) ($_GET['instancia_id'] ?? 0);
$lang = $LANG ?? 'es';

$stmt = $pdo->prepare("SELECT * FROM monitor_instancias WHERE id = :id");
$stmt->execute(['id' => $instanciaId]);
$instancia = $stmt->fetch();

if (!$instancia) {
    header('Location: monitor-instancias.php');
    exit;
}

$repo = new MonitorRepository($pdo);

// --- Paginación de la tabla ---
$porPagina = 15;
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$totalCapturas = $repo->contarCapturas($instanciaId);
$totalPaginas = max(1, (int) ceil($totalCapturas / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$capturas = $repo->obtenerHistorialIndices($instanciaId, $porPagina, $offset);

// --- Serie para el gráfico: hasta 40 puntos más recientes, en orden
// cronológico ascendente (izquierda = más antigua, derecha = más reciente),
// que es como se lee naturalmente una línea de tiempo. ---
$puntosGrafico = array_reverse($repo->obtenerHistorialIndices($instanciaId, 40, 0));

$colores = monitorColores();
$nombresComponente = monitorNombresComponente();
$siglaComponente = monitorSiglaComponente();

/**
 * Gráfico de línea de tiempo del ISBD: un punto por captura, sobre bandas
 * de fondo que marcan las mismas zonas de riesgo que el resto del
 * dashboard (verde 0-30, amarillo 30-50, anaranjado 50-70, rojo 70-100).
 * Cada punto es un link a monitor-captura-detalle.php para esa captura.
 */
function renderGraficoLineaTiempo(array $puntos, array $colores, int $instanciaId, string $lang): string
{
    $n = count($puntos);
    if ($n === 0) {
        return '';
    }
    if ($n === 1) {
        $puntos[] = $puntos[0];
        $n = 2;
    }

    $margenIzq = 42; $margenDer = 20; $margenArriba = 16; $margenAbajo = 56;
    $altoPlot = 200;
    $anchoPlot = max(560, ($n - 1) * 52);
    $anchoTotal = $margenIzq + $anchoPlot + $margenDer;
    $altoTotal = $margenArriba + $altoPlot + $margenAbajo;

    $xAt = function (int $i) use ($n, $margenIzq, $anchoPlot) {
        return $n <= 1 ? $margenIzq + $anchoPlot / 2 : $margenIzq + ($i * ($anchoPlot / ($n - 1)));
    };
    $yAt = function (float $valor) use ($margenArriba, $altoPlot) {
        $v = max(0, min(100, $valor));
        return $margenArriba + $altoPlot - ($v / 100 * $altoPlot);
    };

    // Bandas de fondo (mismas zonas de riesgo que los gauges y barras).
    $svgBandas = '';
    foreach ([['rojo', 70, 100], ['anaranjado', 50, 70], ['amarillo', 30, 50], ['verde', 0, 30]] as [$clave, $lo, $hi]) {
        $y = $yAt($hi);
        $alto = $yAt($lo) - $yAt($hi);
        $svgBandas .= '<rect x="' . $margenIzq . '" y="' . round($y, 2) . '" width="' . $anchoPlot . '" height="' . round($alto, 2) . '" fill="' . $colores[$clave] . '" fill-opacity="0.08"/>';
    }

    // Guías horizontales + etiquetas del eje Y.
    $svgEjeY = '';
    foreach ([0, 30, 50, 70, 100] as $marca) {
        $y = round($yAt($marca), 2);
        $svgEjeY .= '<line x1="' . $margenIzq . '" y1="' . $y . '" x2="' . ($margenIzq + $anchoPlot) . '" y2="' . $y . '" stroke="var(--border)" stroke-width="1" stroke-dasharray="3,4"/>'
            . '<text x="' . ($margenIzq - 8) . '" y="' . ($y + 4) . '" text-anchor="end" class="tl-eje-label">' . $marca . '</text>';
    }

    // Etiquetas del eje X (fecha corta), sin amontonarlas si hay muchos puntos.
    $pasoLabel = max(1, (int) ceil($n / 8));
    $svgEjeX = '';
    for ($i = 0; $i < $n; $i++) {
        if ($i % $pasoLabel !== 0 && $i !== $n - 1) { continue; }
        $x = round($xAt($i), 2);
        $yLabel = $margenArriba + $altoPlot + 18;
        $svgEjeX .= '<text x="' . $x . '" y="' . $yLabel . '" text-anchor="end" transform="rotate(-40 ' . $x . ' ' . $yLabel . ')" class="tl-eje-label">'
            . htmlspecialchars(formatearFechaCorta($puntos[$i]['capturado_en'], $lang)) . '</text>';
    }

    // Línea que conecta los puntos.
    $coordenadas = [];
    foreach ($puntos as $i => $p) {
        $coordenadas[] = round($xAt($i), 2) . ',' . round($yAt((float) $p['indice_salud']), 2);
    }
    $svgLinea = '<polyline points="' . implode(' ', $coordenadas) . '" fill="none" stroke="var(--text-muted)" stroke-width="2" opacity="0.55"/>';

    // Puntos clicables (cada uno navega al detalle de esa captura).
    $svgPuntos = '';
    foreach ($puntos as $i => $p) {
        $x = round($xAt($i), 2);
        $y = round($yAt((float) $p['indice_salud']), 2);
        $color = $colores[$p['estado']] ?? '#999';
        $urlDetalle = 'monitor-captura-detalle.php?instancia_id=' . $instanciaId . '&capturado_en=' . urlencode($p['capturado_en']);
        $titulo = htmlspecialchars(formatearFecha($p['capturado_en'], $lang) . ' · ISBD ' . number_format((float) $p['indice_salud'], 2));
        $svgPuntos .= '<a href="' . htmlspecialchars($urlDetalle) . '" class="tl-punto">'
            . '<title>' . $titulo . '</title>'
            . '<circle cx="' . $x . '" cy="' . $y . '" r="4.5" fill="' . $color . '" stroke="var(--surface)" stroke-width="2"/>'
            . '</a>';
    }

    return '
        <div class="tl-grafico-wrap">
            <svg viewBox="0 0 ' . $anchoTotal . ' ' . $altoTotal . '" width="' . $anchoTotal . '" height="' . $altoTotal . '">
                ' . $svgBandas . $svgEjeY . $svgLinea . $svgPuntos . $svgEjeX . '
            </svg>
        </div>
    ';
}
?>
<?php echo renderEstilosMonitor(); ?>
    <main class="flex-grow-1">
        <section class="section">
            <div class="container">
                <span class="section-eyebrow"><i class="fa-solid fa-chart-line me-2"></i><?php echo t('monitor.nav.item'); ?></span>
                <h1 class="section-title"><?php echo htmlspecialchars($instancia['nombre']); ?></h1>
                <p class="section-lead">
                    <?php echo $lang === 'en'
                        ? 'Health progress over time · ' . $totalCapturas . ' captures total'
                        : 'Progreso de la salud en el tiempo · ' . $totalCapturas . ' capturas en total'; ?>
                </p>

                <div class="tl-toolbar">
                    <a href="monitor-salud.php?instancia_id=<?php echo $instanciaId; ?>" class="btn btn-ghost">
                        <i class="fa-solid fa-arrow-left me-2"></i><?php echo $lang === 'en' ? 'Back to panel' : 'Volver al panel'; ?>
                    </a>
                </div>

                <?php if (empty($puntosGrafico)): ?>
                    <p><?php echo t('monitor.salud.sincapturas'); ?></p>
                <?php else: ?>

                    <?php echo renderGraficoLineaTiempo($puntosGrafico, $colores, $instanciaId, $lang); ?>

                    <div class="tl-tabla-wrap">
                        <table class="tl-tabla">
                            <thead>
                            <tr>
                                <th><?php echo $lang === 'en' ? 'Date' : 'Fecha'; ?></th>
                                <th>ISBD</th>
                                <th><?php echo $siglaComponente['procesos']; ?></th>
                                <th><?php echo $siglaComponente['memoria']; ?></th>
                                <th><?php echo $siglaComponente['archivos']; ?></th>
                                <th><?php echo $lang === 'en' ? 'Status' : 'Estado'; ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($capturas as $c): ?>
                                <?php $colorC = $colores[$c['estado']] ?? '#999'; ?>
                                <tr>
                                    <td class="tl-tabla-fecha"><?php echo formatearFecha($c['capturado_en'], $lang); ?></td>
                                    <td class="tl-tabla-valor" style="color:<?php echo $colorC; ?>;"><?php echo number_format((float) $c['indice_salud'], 2); ?></td>
                                    <td><?php echo number_format((float) $c['indice_procesos'], 2); ?></td>
                                    <td><?php echo number_format((float) $c['indice_memoria'], 2); ?></td>
                                    <td><?php echo number_format((float) $c['indice_archivos'], 2); ?></td>
                                    <td>
                                            <span class="tl-estado-chip" style="color:<?php echo $colorC; ?>;">
                                                <span class="tl-estado-punto" style="background:<?php echo $colorC; ?>;"></span>
                                                <?php echo strtoupper(htmlspecialchars($c['estado'])); ?>
                                            </span>
                                    </td>
                                    <td>
                                        <a href="monitor-captura-detalle.php?instancia_id=<?php echo $instanciaId; ?>&capturado_en=<?php echo urlencode($c['capturado_en']); ?>" class="btn btn-ghost btn-sm">
                                            <?php echo $lang === 'en' ? 'View detail' : 'Ver detalle'; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPaginas > 1): ?>
                        <div class="tl-paginacion">
                            <?php if ($pagina > 1): ?>
                                <a href="?instancia_id=<?php echo $instanciaId; ?>&pagina=<?php echo $pagina - 1; ?>" class="btn btn-ghost btn-sm">
                                    <i class="fa-solid fa-chevron-left me-1"></i><?php echo $lang === 'en' ? 'Newer' : 'Más recientes'; ?>
                                </a>
                            <?php endif; ?>
                            <span><?php echo $lang === 'en' ? 'Page' : 'Página'; ?> <?php echo $pagina; ?> / <?php echo $totalPaginas; ?></span>
                            <?php if ($pagina < $totalPaginas): ?>
                                <a href="?instancia_id=<?php echo $instanciaId; ?>&pagina=<?php echo $pagina + 1; ?>" class="btn btn-ghost btn-sm">
                                    <?php echo $lang === 'en' ? 'Older' : 'Más antiguas'; ?><i class="fa-solid fa-chevron-right ms-1"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </section>
    </main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>