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
require_once __DIR__ . '/includes/OracleStress.php';
require_once __DIR__ . '/includes/MonitorRepository.php';
require_once __DIR__ . '/includes/monitor-render.php';
require_once __DIR__ . '/includes/tnsnames-parser.php';

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

// Si es Oracle y tiene un alias TNS guardado, refrescamos host/puerto/
// nombre_bd leyendo tnsnames.ora en este mismo momento -- en vez de
// confiar en lo que haya quedado guardado en monitor_instancias.
$instancia = refrescarInstanciaDesdeTns($instancia);

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
            $instancia['password_plano'] = monitorDecrypt($instancia['password_enc']);
            $ociConn = conectarOracleInstancia($instancia);
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

// --- Prueba de estres (solo Oracle) ---------------------------------
// Estado que la vista necesita para pintar el panel de estres.
$esOracle       = $instancia['tipo_motor'] === 'oracle';
$estresActivo   = ['total' => 0, 'corriendo' => 0];
$dbLinks        = [];
$estresMensaje  = null;   // aviso verde (exito)
$estresError    = null;   // aviso rojo (fallo del estres, separado de $error de captura)

if ($esOracle) {
    // Acciones POST del estres: iniciar, detener, o refrescar la lista de
    // links. Cada una abre su propia conexion OCI8 y la usa para operar.
    $accionEstres = $_POST['estres'] ?? null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accionEstres !== null) {
        try {
            $instancia['password_plano'] = monitorDecrypt($instancia['password_enc']);
            $ociEstres = conectarOracleInstancia($instancia);
            $stress = new OracleStress($ociEstres);

            if ($accionEstres === 'iniciar') {
                $manual   = trim($_POST['db_link_manual'] ?? '');
                $sel      = trim($_POST['db_link'] ?? '');
                $sesiones = (int) ($_POST['sesiones'] ?? 5);
                $segundos = (int) ($_POST['segundos'] ?? 60);
                // Local si eligen "__local__" o dejan todo vacio; por link si
                // escriben uno a mano o seleccionan un link real.
                if ($manual !== '') {
                    $dbLink = $manual;
                } elseif ($sel === '' || $sel === '__local__') {
                    $dbLink = null; // carga LOCAL sobre la base monitoreada
                } else {
                    $dbLink = $sel;
                }
                $creados = $stress->iniciar($dbLink, $sesiones, $segundos);
                $destino = $dbLink === null
                    ? ($lang === 'en' ? 'this database (local load)' : 'esta base (carga local)')
                    : '"' . $dbLink . '"';
                $estresMensaje = $lang === 'en'
                    ? sprintf('Stress started: %d session(s) loading %s for %ds.', $creados, $destino, $segundos)
                    : sprintf('Estrés iniciado: %d sesión(es) generando carga en %s durante %ds.', $creados, $destino, $segundos);
            } elseif ($accionEstres === 'detener') {
                $restantes = $stress->detenerYLimpiar();
                $estresMensaje = $restantes === 0
                    ? ($lang === 'en' ? 'Base restored: 0 stress jobs remaining.' : 'Base restaurada: 0 jobs de estrés restantes.')
                    : ($lang === 'en' ? sprintf('%d job(s) could not be removed.', $restantes) : sprintf('%d job(s) no se pudieron eliminar.', $restantes));
            }
            // Para cualquier accion (incluye 'refrescar') releemos estado y links.
            $estresActivo = $stress->estado();
            $dbLinks = $stress->listarDbLinks();
        } catch (Throwable $e) {
            $estresError = $e->getMessage();
        }
    } else {
        // GET normal: consultamos estado y links para pintar el panel,
        // pero sin abortar la pagina si Oracle no responde.
        try {
            $instancia['password_plano'] = monitorDecrypt($instancia['password_enc']);
            $ociEstres = conectarOracleInstancia($instancia);
            $stress = new OracleStress($ociEstres);
            $estresActivo = $stress->estado();
            $dbLinks = $stress->listarDbLinks();
        } catch (Throwable $e) {
            $estresError = $e->getMessage();
        }
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

                <?php if ($esOracle): ?>
                    <?php $hayEstres = ($estresActivo['total'] ?? 0) > 0; ?>
                    <div class="estres-card mb-4" style="border-left:4px solid <?php echo $hayEstres ? '#F2B134' : 'var(--border)'; ?>;">

                        <?php /* ── Cabecera con botón que despliega el panel ── */ ?>
                        <div class="estres-card-header">
                            <div class="estres-card-titulo">
                                <i class="fa-solid fa-gauge-high"></i>
                                <span><?php echo t('monitor.estres.titulo'); ?></span>
                                <?php if ($hayEstres): ?>
                                    <span class="estres-badge-activo"><i class="fa-solid fa-circle fa-beat" style="font-size:0.5rem;"></i> <?php echo $lang === 'en' ? 'RUNNING' : 'EN CURSO'; ?></span>
                                <?php else: ?>
                                    <span class="estres-badge-listo"><?php echo $lang === 'en' ? 'READY' : 'LISTO PARA PRUEBA'; ?></span>
                                <?php endif; ?>
                            </div>

                            <?php /* Info de la BD bajo análisis */ ?>
                            <div class="estres-bd-info">
                                <div class="estres-bd-icono"><i class="fa-solid fa-database"></i></div>
                                <div>
                                    <div class="estres-bd-eyebrow"><?php echo $lang === 'en' ? 'DATABASE UNDER ANALYSIS' : 'BASE DE DATOS BAJO ANÁLISIS'; ?></div>
                                    <div class="estres-bd-nombre"><?php echo htmlspecialchars($instancia['nombre']); ?></div>
                                    <div class="estres-bd-meta">
                                        <?php echo strtoupper(htmlspecialchars($instancia['tipo_motor'])); ?>
                                        &nbsp;·&nbsp;
                                        <?php echo htmlspecialchars($instancia['host']); ?>
                                    </div>
                                </div>
                            </div>

                            <?php /* Mensajes de estado (inician, errores, restauración) */ ?>
                            <?php if ($estresMensaje): ?>
                                <div class="estres-msg ok"><i class="fa-solid fa-circle-check"></i><span><?php echo htmlspecialchars($estresMensaje); ?></span></div>
                            <?php endif; ?>
                            <?php if ($estresError): ?>
                                <div class="estres-msg err"><i class="fa-solid fa-circle-exclamation"></i><span><?php echo t('monitor.estres.error'); ?>: <?php echo htmlspecialchars($estresError); ?></span></div>
                            <?php endif; ?>
                            <?php if ($hayEstres): ?>
                                <div class="estres-msg warn" id="estres-aviso">
                                    <i class="fa-solid fa-bolt"></i>
                                    <span><?php echo sprintf(t('monitor.estres.activo'), (int) $estresActivo['corriendo'], (int) $estresActivo['total']); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php /* Botón que despliega el panel de configuración */ ?>
                            <?php if (!$hayEstres): ?>
                                <button type="button" class="btn btn-ghost mt-2" data-bs-toggle="collapse" data-bs-target="#estres-panel" aria-expanded="<?php echo $estresMensaje ? 'true' : 'false'; ?>">
                                    <i class="fa-solid fa-sliders me-2"></i><?php echo $lang === 'en' ? 'Configure and run' : 'Configurar e iniciar'; ?> <i class="fa-solid fa-chevron-down ms-1" style="font-size:0.7rem;"></i>
                                </button>
                            <?php else: ?>
                                <div class="d-flex gap-2 flex-wrap mt-2">
                                    <form method="post">
                                        <input type="hidden" name="estres" value="detener">
                                        <button type="submit" class="btn btn-cta"><i class="fa-solid fa-stop me-2"></i><?php echo t('monitor.estres.detener'); ?></button>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="estres" value="refrescar">
                                        <button type="submit" class="btn btn-ghost"><i class="fa-solid fa-rotate me-2"></i><?php echo t('monitor.estres.refrescar'); ?></button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php /* ── Panel colapsable: configuración del estrés ── */ ?>
                        <?php if (!$hayEstres): ?>
                        <div class="collapse <?php echo $estresMensaje ? 'show' : ''; ?>" id="estres-panel">
                            <div class="estres-form-wrap">
                                <form method="post">
                                    <input type="hidden" name="estres" value="iniciar">
                                    <div class="estres-form-grid">
                                        <div class="estres-form-origen">
                                            <label class="estres-label"><?php echo t('monitor.estres.origen'); ?></label>
                                            <select name="db_link" id="estres-link-select" class="form-select">
                                                <option value="__local__"><?php echo t('monitor.estres.origen.local'); ?></option>
                                                <?php foreach ($dbLinks as $lk): ?>
                                                    <option value="<?php echo htmlspecialchars($lk); ?>"><?php echo t('monitor.estres.origen.porlink'); ?> <?php echo htmlspecialchars($lk); ?></option>
                                                <?php endforeach; ?>
                                                <option value=""><?php echo t('monitor.estres.link.otro'); ?></option>
                                            </select>
                                            <input type="text" name="db_link_manual" id="estres-link-manual"
                                                   class="form-control mt-2 d-none"
                                                   placeholder="<?php echo t('monitor.estres.link.manual'); ?>"
                                                   pattern="[A-Za-z0-9_$#.]+" maxlength="128">
                                        </div>
                                        <div>
                                            <label class="estres-label"><?php echo t('monitor.estres.sesiones'); ?></label>
                                            <input type="number" name="sesiones" class="form-control" value="5" min="1" max="50">
                                            <div class="estres-sublabel"><?php echo $lang === 'en' ? 'Simultaneous connections' : 'Conexiones simultáneas'; ?></div>
                                        </div>
                                        <div>
                                            <label class="estres-label"><?php echo t('monitor.estres.duracion'); ?></label>
                                            <input type="number" name="segundos" class="form-control" value="60" min="1" max="600">
                                            <div class="estres-sublabel"><?php echo $lang === 'en' ? 'Maximum load time' : 'Tiempo máximo de carga'; ?></div>
                                        </div>
                                        <div class="estres-form-btn">
                                            <button type="submit" class="btn btn-cta w-100">
                                                <i class="fa-solid fa-play me-2"></i><?php echo $lang === 'en' ? 'Start stress' : 'Iniciar estrés'; ?>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="estres-nota"><i class="fa-solid fa-shield-halved me-1"></i><?php echo t('monitor.estres.nota'); ?></p>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php /* Toggle en vivo — oculto visualmente, funcional en JS */ ?>
                        <?php if ($resultado): ?>
                            <input type="checkbox" id="vivo-toggle" style="display:none;">
                            <span id="vivo-indicador" style="display:none;"></span>
                        <?php endif; ?>

                    </div>
                    <script>
                        (function () {
                            var sel = document.getElementById('estres-link-select');
                            var man = document.getElementById('estres-link-manual');
                            if (!sel || !man) return;
                            sel.addEventListener('change', function () {
                                if (sel.value === '') { man.classList.remove('d-none'); man.focus(); }
                                else { man.classList.add('d-none'); man.value = ''; }
                            });
                        })();
                    </script>
                    <?php if ($resultado): ?>
                    <script>
                        // Modo "en vivo": refresca las MISMAS roscas/gauges/barras cada 3 s
                        // pidiendo una captura efímera (no se guarda en historial). Es
                        // INDEPENDIENTE de la deteccion de jobs: se controla con el
                        // interruptor, y se enciende solo al iniciar una prueba de estres.
                        // Nunca recarga la pagina, asi que no cierra los paneles abiertos.
                        (function () {
                            var instanciaId = <?php echo (int) $instanciaId; ?>;
                            var key = 'monitorVivo_' + instanciaId;
                            var toggle = document.getElementById('vivo-toggle');
                            var indic = document.getElementById('vivo-indicador');
                            var rosca = document.getElementById('vivo-rosca');
                            var timer = null;

                            function aplicar(d) {
                                if (!d || !d.ok) return;
                                if (rosca && d.rosca_html) rosca.innerHTML = d.rosca_html;
                                if (d.gauges) {
                                    Object.keys(d.gauges).forEach(function (comp) {
                                        var g = d.gauges[comp];
                                        var svg = document.querySelector('[data-vivo-gauge="' + comp + '"]');
                                        if (svg) svg.innerHTML = g.svg;
                                        var val = document.querySelector('[data-vivo-valor="' + comp + '"]');
                                        if (val) { val.textContent = g.valor; val.style.color = g.color; }
                                        // No regeneramos el detalle si hay un popover "?" abierto
                                        // dentro, para no cerrarlo mientras el usuario lo lee.
                                        var det = document.querySelector('[data-vivo-detalle="' + comp + '"]');
                                        if (det && typeof g.detalle === 'string' && !det.querySelector('.popover-simple.show')) {
                                            det.innerHTML = g.detalle;
                                        }
                                    });
                                }
                            }
                            function tick() {
                                fetch('api/monitor-vivo.php?instancia_id=' + instanciaId)
                                    .then(function (r) { return r.json(); })
                                    .then(aplicar)
                                    .catch(function () {});
                            }
                            function start() {
                                if (timer) return;
                                if (indic) indic.style.display = 'inline';
                                tick();
                                timer = setInterval(tick, 3000);
                            }
                            function stop() {
                                if (timer) { clearInterval(timer); timer = null; }
                                if (indic) indic.style.display = 'none';
                            }

                            if (toggle) {
                                var on = false;
                                try { on = localStorage.getItem(key) === '1'; } catch (e) {}
                                toggle.checked = on;
                                toggle.addEventListener('change', function () {
                                    try { localStorage.setItem(key, toggle.checked ? '1' : '0'); } catch (e) {}
                                    if (toggle.checked) start(); else stop();
                                });
                                if (on) start();
                            }

                            // Al iniciar una prueba de estres, dejamos encendido el modo en
                            // vivo para que, tras el POST, la pagina siga actualizando sola.
                            var iniciarInput = document.querySelector('input[name="estres"][value="iniciar"]');
                            if (iniciarInput && iniciarInput.form) {
                                iniciarInput.form.addEventListener('submit', function () {
                                    try { localStorage.setItem(key, '1'); } catch (e) {}
                                });
                            }
                        })();
                    </script>
                    <?php endif; ?>
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
                            <div id="vivo-rosca"><?php echo renderRoscaGrande((float) $resultado['indice_salud'], $colorGeneral); ?></div>
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
                                        <span data-vivo-gauge="<?php echo $comp; ?>"><?php echo renderGaugeChico($valorComp); ?></span>
                                        <div class="mini-gauge-h-valor" data-vivo-valor="<?php echo $comp; ?>" style="color:<?php echo $colorComp; ?>;"><?php echo number_format($valorComp, 2); ?></div>
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
                                    <div data-vivo-detalle="<?php echo $comp; ?>">
                                        <?php foreach ($detallePorComponente[$comp] as $d): ?>
                                            <?php echo renderBarraRango($d, $colores); ?>
                                        <?php endforeach; ?>
                                    </div>
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

            // Delegacion de eventos: funciona tambien para los botones "?"
            // que el modo en vivo regenera cada 3 s al refrescar el detalle.
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-popover-target]');
                if (btn) {
                    e.stopPropagation();
                    const pop = document.getElementById('pop-' + btn.dataset.popoverTarget);
                    if (!pop) return;
                    const yaAbierto = pop.classList.contains('show');
                    document.querySelectorAll('.popover-simple.show').forEach((p) => p.classList.remove('show'));
                    if (!yaAbierto) pop.classList.add('show');
                    return;
                }
                // Clic fuera de un boton: cierra cualquier popover abierto.
                document.querySelectorAll('.popover-simple.show').forEach((p) => p.classList.remove('show'));
            });
        });
    </script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>