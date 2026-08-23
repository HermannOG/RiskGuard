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
require_once __DIR__ . '/includes/OracleAdapter.php';
require_once __DIR__ . '/includes/MonitorRepository.php';
require_once __DIR__ . '/includes/monitor-render.php';

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
                    PDO::ATTR_TIMEOUT => 5,
            ]);
            $adapter = new MariaDBAdapter($pdoObjetivo);
        } elseif ($instancia['tipo_motor'] === 'oracle') {
            if (!extension_loaded('oci8')) {
                throw new RuntimeException('La extension oci8 de PHP no esta habilitada en este servidor.');
            }

            // 'nombre_bd' se usa como SERVICE_NAME de Oracle (ej. XEPDB1, ORCLPDB1).
            // Formato estandar de connection string via listener:
            //   host:puerto/service_name
            $connString = sprintf('%s:%s/%s', $instancia['host'], $instancia['puerto'], $instancia['nombre_bd']);

            $ociConn = @oci_connect(
                    $instancia['usuario'],
                    monitorDecrypt($instancia['password_enc']),
                    $connString,
                    'AL32UTF8'
            );

            if (!$ociConn) {
                $e = oci_error();
                throw new RuntimeException('No se pudo conectar a Oracle: ' . ($e['message'] ?? 'error desconocido'));
            }

            $adapter = new OracleAdapter($ociConn);
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
    $historial = array_values(array_filter($historial, fn($h) => ($h['capturado_en'] ?? null) !== ($resultado['capturado_en'] ?? null)));
}

$colores = monitorColores();
$nombresComponente = monitorNombresComponente();
$siglaComponente = monitorSiglaComponente();
$iconoComponente = monitorIconoComponente();
$subtituloDetalle = monitorSubtituloDetalle($lang);

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

$justificacionComponente = [
        'procesos' => [
                'titulo' => 'Procesos (IP) — por qué estas variables',
                'intro'  => 'El IP mide la carga viva del motor: cuántas operaciones compiten en este instante por CPU y por bloqueos. Es el componente que se degrada primero y el que el usuario percibe como "lentitud".',
                'vars'   => [
                        'Procesos actuales (p1)'      => 'Es el primer síntoma de saturación: si el número de hilos en ejecución crece, se acerca el agotamiento de max_connections y el rechazo de nuevas conexiones.',
                        'Sesiones activas (p2)'       => 'Separa las conexiones que realmente ejecutan trabajo de las que están abiertas pero ociosas, lo que permite distinguir carga real de conexiones abandonadas por la aplicación.',
                        'Sesiones bloqueadas (p3)'    => 'Las esperas por bloqueo son la causa más común de que la base "se sienta caída" sin estarlo, y anticipan interbloqueos antes de que se conviertan en incidente.',
                        'Operaciones prolongadas (p4)'=> 'Delatan consultas sin índice o transacciones abiertas que retienen recursos, que es donde nace la mayoría de los problemas de rendimiento evitables.',
                ],
                'norma'  => 'Respaldo normativo: ISO/IEC 27002 controles 8.6 (gestión de capacidad) y 8.16 (actividades de seguimiento); COBIT DSS01 (gestionar operaciones) y BAI04 (gestionar disponibilidad y capacidad).',
        ],
        'memoria' => [
                'titulo' => 'Memoria (IM) — por qué estas variables',
                'intro'  => 'Es el componente con mayor peso (60%) porque en MariaDB la memoria determina el rendimiento más que cualquier otro recurso: si la caché no retiene los datos de uso frecuente, toda la operación se traduce en lecturas a disco.',
                'vars'   => [
                        'Uso de buffer/caché (m1)' => 'Muestra si la memoria asignada al motor alcanza para el conjunto de datos que se trabaja a diario, o si está sobredimensionada y se desperdicia.',
                        'Presión de memoria (m2)'  => 'Detecta cuándo el motor se ve obligado a expulsar páginas para hacer espacio; una presión sostenida anticipa la degradación antes de que el usuario la note.',
                        'Cache hit ratio (m3)'     => 'Es el indicador clásico de eficiencia de la caché y el más comparable entre motores, lo que permite que el índice siga siendo válido si mañana se monitorea Oracle o PostgreSQL.',
                ],
                'norma'  => 'Respaldo normativo: ISO/IEC 27002 control 8.6 (gestión de capacidad); COBIT BAI04. El peso de 60% responde al criterio definido para este proyecto.',
        ],
        'archivos' => [
                'titulo' => 'Archivos (IA) — por qué estas variables',
                'intro'  => 'El IA vigila el soporte físico de la información. Sus fallas son las únicas de este tablero capaces de producir pérdida de datos irreversible, por lo que se midieron disponibilidad e integridad del almacenamiento.',
                'vars'   => [
                        'Archivos fuera de línea (a1)' => 'Un archivo o tablespace inaccesible golpea directamente la disponibilidad, uno de los tres pilares que protege el SGSI.',
                        'Espacio libre (a2)'           => 'Quedarse sin espacio es la causa de caída más frecuente y a la vez la más evitable, porque avisa con antelación si se vigila de forma continua.',
                        'Archivos con problemas (a3)'  => 'Las tablas marcadas como corruptas o que requieren reparación afectan la integridad de la información, y detectarlas a tiempo evita restaurar respaldos completos.',
                ],
                'norma'  => 'Respaldo normativo: ISO/IEC 27002 controles 8.6, 8.13 (copia de seguridad) y 8.14 (redundancia de instalaciones de tratamiento de información); COBIT DSS04 (gestionar la continuidad).',
        ],
];

?>
<?php echo renderEstilosMonitor(); ?>
    <main class="flex-grow-1">
        <section class="section">
            <div class="container">
                <span class="section-eyebrow"><i class="fa-solid fa-heart-pulse me-2"></i><?php echo t('monitor.nav.item'); ?></span>
                <h1 class="section-title"><?php echo htmlspecialchars($instancia['nombre']); ?></h1>
                <p class="section-lead"><?php echo htmlspecialchars($instancia['tipo_motor']); ?> · <?php echo htmlspecialchars($instancia['host']); ?></p>

                <div class="d-flex gap-2 flex-wrap mb-4">
                    <form method="post">
                        <button type="submit" name="capturar" value="1" class="btn btn-cta">
                            <i class="fa-solid fa-rotate me-2"></i><?php echo t('monitor.salud.capturar'); ?>
                        </button>
                    </form>
                    <a href="monitor-linea-tiempo.php?instancia_id=<?php echo $instanciaId; ?>" class="btn btn-ghost">
                        <i class="fa-solid fa-chart-line me-2"></i><?php echo $lang === 'en' ? 'View timeline' : 'Ver línea de tiempo'; ?>
                    </a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">Error: <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($resultado): ?>
                <?php $colorGeneral = $colores[$resultado['estado']] ?? '#999'; ?>

                <div class="eval-control mb-4">
                    <h2 class="section-title" style="font-size:1.3rem; margin-top:0;"><?php echo t('monitor.isbd.titulo'); ?></h2>
                    <p class="section-lead" style="max-width:none;"><?php echo t('monitor.isbd.intro'); ?></p>

                    <div class="isbd-row">
                        <?php echo renderTablaContexto($instancia, $resultado, $detalle, $ctx, $colores); ?>
                        <div class="dash-isbd" style="position:relative;">
                            <div class="ayuda-isbd-wrap">
                                <button type="button" class="btn-ayuda" data-popover-target="isbd">?</button>
                                <?php echo renderPopover('isbd', $ayudaComponente['isbd'], $colores); ?>
                            </div>
                            <?php echo renderRoscaGrande((float) $resultado['indice_salud'], $colorGeneral); ?>
                        </div>
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
                                        <div class="just-wrap">
                                            <button type="button" class="btn-ayuda btn-justif" data-popover-target="just-<?php echo $comp; ?>" title="¿Por qué estas variables?">
                                                <i class="fa-solid fa-lightbulb"></i>
                                            </button>
                                            <?php echo renderPopoverJustificacion($comp, $justificacionComponente[$comp]); ?>
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
                                    <?php
                                    $idHist = 'hist-' . $i;
                                    $colorH = $colores[$h['estado']];

                                    // Detalle completo (todas las variables) de ESTA captura puntual del historial.
                                    // No requiere cambios en la BD: monitor_lecturas ya guarda cada lectura
                                    // cruda por capturado_en, y obtenerDetalleLecturas() ya sabe reconstruirlo.
                                    $detalleH = $repo->obtenerDetalleLecturas($instanciaId, $h['capturado_en'], $lang);
                                    $detallePorComponenteH = ['procesos' => [], 'memoria' => [], 'archivos' => []];
                                    foreach ($detalleH as $d) {
                                        $detallePorComponenteH[$d['componente']][] = $d;
                                    }
                                    $estadoComponenteH = [
                                            'procesos' => $repo->determinarEstado($instanciaId, (float) $h['indice_procesos']),
                                            'memoria'  => $repo->determinarEstado($instanciaId, (float) $h['indice_memoria']),
                                            'archivos' => $repo->determinarEstado($instanciaId, (float) $h['indice_archivos']),
                                    ];
                                    ?>
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
                                                    <?php $colorCompH = $colores[$estadoComponenteH[$comp]]; $idComp = $idHist . '-' . $comp; ?>
                                                    <div class="mini-gauge-h-wrap" style="flex:1; min-width:150px;">
                                                        <div class="mini-gauge-h componente-card" role="button" data-bs-toggle="collapse" data-bs-target="#detalle-<?php echo $idComp; ?>" aria-expanded="false">
                                                            <?php echo renderGaugeChico((float) $h['indice_' . $comp]); ?>
                                                            <div class="mini-gauge-h-valor" style="color:<?php echo $colorCompH; ?>;"><?php echo number_format((float) $h['indice_' . $comp], 2); ?></div>
                                                            <div class="mini-gauge-h-label">
                                                                <?php echo $nombresComponente[$comp]; ?> (<?php echo $siglaComponente[$comp]; ?>)
                                                                <i class="fa-solid fa-chevron-down chevron" style="font-size:0.65rem; color:var(--text-muted);"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                            <?php foreach (['procesos', 'memoria', 'archivos'] as $comp): ?>
                                                <?php $idComp = $idHist . '-' . $comp; $colorCompH = $colores[$estadoComponenteH[$comp]]; ?>
                                                <div class="collapse mt-3" id="detalle-<?php echo $idComp; ?>">
                                                    <div class="comp-block" style="border-left-color:<?php echo $colorCompH; ?>;">
                                                        <div class="comp-block-header">
                                                            <div class="comp-block-icon" style="color:<?php echo $colorCompH; ?>;"><i class="fa-solid <?php echo $iconoComponente[$comp]; ?>"></i></div>
                                                            <div>
                                                                <div class="comp-block-title"><?php echo $nombresComponente[$comp]; ?> <span style="color:var(--text-muted); font-weight:400;">(<?php echo $siglaComponente[$comp]; ?>)</span></div>
                                                                <div class="comp-block-sub"><?php echo $subtituloDetalle; ?></div>
                                                            </div>
                                                        </div>
                                                        <?php foreach ($detallePorComponenteH[$comp] as $d): ?>
                                                            <?php echo renderBarraRango($d, $colores, $idComp); ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
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