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
require_once __DIR__ . '/includes/tnsnames-parser.php';

$pdo = dbMonitor();
$instanciaId = (int) ($_GET['instancia_id'] ?? 0);
$capturadoEn = $_GET['capturado_en'] ?? '';
$lang = $LANG ?? 'es';

$stmt = $pdo->prepare("SELECT * FROM monitor_instancias WHERE id = :id");
$stmt->execute(['id' => $instanciaId]);
$instancia = $stmt->fetch();

if (!$instancia) {
    header('Location: monitor-instancias.php');
    exit;
}

$instancia = refrescarInstanciaDesdeTns($instancia);

$repo = new MonitorRepository($pdo);
$resultado = $capturadoEn !== '' ? $repo->obtenerIndicePorFecha($instanciaId, $capturadoEn) : null;

if (!$resultado) {
    // Captura inexistente o timestamp inválido: volvemos a la línea de tiempo
    // en vez de mostrar una página rota.
    header('Location: monitor-linea-tiempo.php?instancia_id=' . $instanciaId);
    exit;
}

$detalle = $repo->obtenerDetalleLecturas($instanciaId, $capturadoEn, $lang);
$detallePorComponente = ['procesos' => [], 'memoria' => [], 'archivos' => []];
foreach ($detalle as $d) {
    $detallePorComponente[$d['componente']][] = $d;
}

$capturaAnterior  = $repo->obtenerCapturaAdyacente($instanciaId, $capturadoEn, 'anterior');
$capturaSiguiente = $repo->obtenerCapturaAdyacente($instanciaId, $capturadoEn, 'siguiente');

$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total_capturas, MIN(capturado_en) AS primera, MAX(capturado_en) AS ultima
    FROM monitor_indices WHERE instancia_id = :id
");
$stmt->execute(['id' => $instanciaId]);
$ctx = $stmt->fetch() ?: ['total_capturas' => 0, 'primera' => null, 'ultima' => null];

$colores = monitorColores();
$nombresComponente = monitorNombresComponente();
$siglaComponente = monitorSiglaComponente();
$iconoComponente = monitorIconoComponente();
$subtituloDetalle = monitorSubtituloDetalle($lang);

$estadoIP = $repo->determinarEstado($instanciaId, (float) $resultado['indice_procesos']);
$estadoIM = $repo->determinarEstado($instanciaId, (float) $resultado['indice_memoria']);
$estadoIA = $repo->determinarEstado($instanciaId, (float) $resultado['indice_archivos']);

$colorGeneral = $colores[$resultado['estado']] ?? '#999';
$esMasReciente = $ctx['ultima'] && $resultado['capturado_en'] === $ctx['ultima'];
?>
<?php echo renderEstilosMonitor(); ?>
    <main class="flex-grow-1">
        <section class="section">
            <div class="container">
                <span class="section-eyebrow"><i class="fa-solid fa-clock-rotate-left me-2"></i><?php echo t('monitor.nav.item'); ?></span>
                <h1 class="section-title"><?php echo htmlspecialchars($instancia['nombre']); ?></h1>
                <p class="section-lead">
                    <?php echo formatearFecha($resultado['capturado_en'], $lang); ?>
                    <?php if ($esMasReciente): ?>
                        · <span style="color:var(--text-muted);"><?php echo $lang === 'en' ? 'most recent capture' : 'captura más reciente'; ?></span>
                    <?php endif; ?>
                </p>

                <div class="captura-nav">
                    <a href="monitor-linea-tiempo.php?instancia_id=<?php echo $instanciaId; ?>" class="btn btn-ghost">
                        <i class="fa-solid fa-list me-2"></i><?php echo $lang === 'en' ? 'Back to timeline' : 'Volver a la línea de tiempo'; ?>
                    </a>
                    <div class="d-flex gap-2">
                        <a href="<?php echo $capturaAnterior ? 'monitor-captura-detalle.php?instancia_id=' . $instanciaId . '&capturado_en=' . urlencode($capturaAnterior) : '#'; ?>"
                           class="btn btn-ghost btn-sm" <?php echo $capturaAnterior ? '' : 'disabled'; ?>>
                            <i class="fa-solid fa-chevron-left me-1"></i><?php echo $lang === 'en' ? 'Previous' : 'Anterior'; ?>
                        </a>
                        <a href="<?php echo $capturaSiguiente ? 'monitor-captura-detalle.php?instancia_id=' . $instanciaId . '&capturado_en=' . urlencode($capturaSiguiente) : '#'; ?>"
                           class="btn btn-ghost btn-sm" <?php echo $capturaSiguiente ? '' : 'disabled'; ?>>
                            <?php echo $lang === 'en' ? 'Next' : 'Siguiente'; ?><i class="fa-solid fa-chevron-right ms-1"></i>
                        </a>
                        <?php if (!$esMasReciente): ?>
                            <a href="monitor-salud.php?instancia_id=<?php echo $instanciaId; ?>" class="btn btn-cta btn-sm">
                                <?php echo $lang === 'en' ? 'Go to current panel' : 'Ir al panel actual'; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="eval-control mb-4">
                    <div class="isbd-row">
                        <?php echo renderTablaContexto($instancia, $resultado, $detalle, $ctx, $colores); ?>
                        <div class="dash-isbd">
                            <?php echo renderRoscaGrande((float) $resultado['indice_salud'], $colorGeneral); ?>
                        </div>
                    </div>

                    <div class="dash-mini-row mt-4">
                        <?php foreach (['procesos' => $estadoIP, 'memoria' => $estadoIM, 'archivos' => $estadoIA] as $comp => $estadoComp): ?>
                            <?php $colorComp = $colores[$estadoComp]; $valorComp = (float) $resultado['indice_' . $comp]; ?>
                            <div class="mini-gauge-h-wrap">
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
            </div>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[role="button"][data-bs-toggle="collapse"]').forEach((card) => {
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