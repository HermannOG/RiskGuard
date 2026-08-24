<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
requiereAdmin();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
require_once __DIR__ . '/includes/db-monitor.php';
require_once __DIR__ . '/includes/monitor-crypto.php';
require_once __DIR__ . '/includes/tnsnames-parser.php';

$pdo = dbMonitor();
$mensaje = null;
$lang = $LANG ?? 'es';

$rutaTns = obtenerRutaTnsnames();
$tnsAliases = leerTnsnamesAliases($rutaTns);
$diagTns = obtenerDiagnosticoTns();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre       = trim($_POST['nombre'] ?? '');
    $tipoMotor    = $_POST['tipo_motor'] ?? '';
    $usuario      = trim($_POST['usuario'] ?? '');
    $password     = $_POST['password'] ?? '';
    $tnsAliasPost = trim($_POST['tns_alias'] ?? '');

    $host = $puerto = $nombreBd = null;
    $tnsAlias = null;

    if ($tipoMotor === 'oracle' && $tnsAliasPost !== '') {
        // Nunca confiamos en host/puerto que pudiera venir en el POST
        // para Oracle: se resuelven SIEMPRE desde tnsnames.ora, para
        // que lo que se guarda coincida con lo que el archivo dice hoy.
        $descriptor = resolverAliasTns($tnsAliasPost, $rutaTns);
        if ($descriptor === null) {
            $mensaje = $lang === 'en'
                    ? 'That TNS alias is no longer in tnsnames.ora. Reload the page and pick another one.'
                    : 'Ese alias TNS ya no está en tnsnames.ora. Recargá la página y elegí otro.';
        } else {
            $host     = $descriptor['host'];
            $puerto   = $descriptor['port'];
            $nombreBd = $descriptor['service_name'] ?? $descriptor['sid'];
            $tnsAlias = strtoupper($tnsAliasPost);
        }
    } else {
        $host     = trim($_POST['host'] ?? '');
        $puerto   = (int) ($_POST['puerto'] ?? 0);
        $nombreBd = trim($_POST['nombre_bd'] ?? '');
    }

    if (!$mensaje && $nombre && $tipoMotor && $host && $puerto && $nombreBd && $usuario) {
        $stmt = $pdo->prepare("
            INSERT INTO monitor_instancias (nombre, tipo_motor, host, puerto, nombre_bd, tns_alias, usuario, password_enc, activo)
            VALUES (:nombre, :tipo_motor, :host, :puerto, :nombre_bd, :tns_alias, :usuario, :password_enc, 1)
        ");
        $stmt->execute([
                'nombre'       => $nombre,
                'tipo_motor'   => $tipoMotor,
                'host'         => $host,
                'puerto'       => $puerto,
                'nombre_bd'    => $nombreBd,
                'tns_alias'    => $tnsAlias,
                'usuario'      => $usuario,
                'password_enc' => monitorEncrypt($password),
        ]);
        $mensaje = t('monitor.instancias.ok');
    } elseif (!$mensaje) {
        $mensaje = t('monitor.instancias.faltan');
    }
}

$instancias = $pdo->query("SELECT id, nombre, tipo_motor, host, puerto, nombre_bd, tns_alias, activo FROM monitor_instancias ORDER BY nombre")->fetchAll();
?>
    <link rel="stylesheet" href="assets/css/evaluacion.css">
    <main class="flex-grow-1">
        <section class="section">
            <div class="container">
                <span class="section-eyebrow"><i class="fa-solid fa-server me-2"></i><?php echo t('monitor.nav.item'); ?></span>
                <h1 class="section-title"><?php echo t('monitor.instancias.titulo'); ?></h1>
                <p class="section-lead"><?php echo t('monitor.instancias.lead'); ?></p>

                <?php if ($mensaje): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($mensaje); ?></div>
                <?php endif; ?>

                <div class="row g-4 mb-5">
                    <div class="col-lg-6">
                        <form method="post" class="eval-control">
                            <div class="mb-3">
                                <label class="form-label"><?php echo t('monitor.instancias.nombre'); ?></label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="d-flex flex-wrap gap-3 mb-3">
                                <div style="flex:1; min-width:220px;">
                                    <label class="form-label"><?php echo t('monitor.instancias.motor'); ?></label>
                                    <select name="tipo_motor" class="form-select" required>
                                        <option value="mariadb">MariaDB / MySQL</option>
                                        <option value="oracle">Oracle</option>
                                        <option value="postgres">PostgreSQL</option>
                                        <option value="sqlserver">SQL Server</option>
                                    </select>
                                </div>
                                <div style="flex:1; min-width:220px; display:none;" id="modo-oracle-wrap">
                                    <label class="form-label"><?php echo $lang === 'en' ? 'Connection source' : 'Origen de la conexión'; ?></label>
                                    <div class="d-flex flex-column gap-2 pt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="modo_conexion" id="modo-tns" value="tns" checked>
                                            <label class="form-check-label" for="modo-tns">
                                                <?php echo $lang === 'en' ? 'Use tnsnames.ora alias' : 'Usar alias de tnsnames.ora'; ?>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="modo_conexion" id="modo-manual" value="manual">
                                            <label class="form-check-label" for="modo-manual">
                                                <?php echo $lang === 'en' ? 'Enter manually' : 'Ingresar manualmente'; ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="mb-3" id="campo-tns" style="display:none;">
                                <label class="form-label"><?php echo $lang === 'en' ? 'TNS alias (tnsnames.ora)' : 'Alias TNS (tnsnames.ora)'; ?></label>
                                <select name="tns_alias" class="form-select">
                                    <option value=""><?php echo $lang === 'en' ? '— Select an alias —' : '— Elegí un alias —'; ?></option>
                                    <?php foreach ($tnsAliases as $alias => $d): ?>
                                        <option value="<?php echo htmlspecialchars($alias); ?>">
                                            <?php echo htmlspecialchars($alias); ?> (<?php echo htmlspecialchars($d['host']); ?>:<?php echo (int) $d['port']; ?>/<?php echo htmlspecialchars($d['service_name'] ?? $d['sid']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    <?php echo $lang === 'en'
                                            ? 'Host, port and database are read directly from tnsnames.ora for this alias.'
                                            : 'Host, puerto y base de datos se leen directamente de tnsnames.ora para este alias.'; ?>
                                </div>
                            </div>

                            <div class="alert alert-warning" id="aviso-tns" style="display:none;">
                                <?php echo $lang === 'en'
                                        ? 'No tnsnames.ora with aliases was found. Fill in the connection manually below, or check where the system looked:'
                                        : 'No se encontró tnsnames.ora con alias. Completá la conexión manualmente abajo, o revisá dónde buscó el sistema:'; ?>
                                <ul class="mb-0 mt-2" style="font-family:var(--font-mono); font-size:0.8rem;">
                                    <li>config.php → oracle_tns_admin: <?php echo $diagTns['config_oracle_tns_admin'] ? htmlspecialchars($diagTns['config_oracle_tns_admin']) : '—'; ?></li>
                                    <li>env TNS_ADMIN: <?php echo $diagTns['env_tns_admin'] ? htmlspecialchars($diagTns['env_tns_admin']) : '—'; ?></li>
                                    <li>env ORACLE_HOME: <?php echo $diagTns['env_oracle_home'] ? htmlspecialchars($diagTns['env_oracle_home']) : '—'; ?></li>
                                    <li><?php echo $lang === 'en' ? 'Common install paths' : 'Rutas típicas de instalación'; ?>: <?php echo $lang === 'en' ? 'checked, none found' : 'revisadas, ninguna encontrada'; ?></li>
                                </ul>
                            </div>

                            <div id="campos-manual">
                                <div class="mb-3">
                                    <label class="form-label"><?php echo t('monitor.instancias.host'); ?></label>
                                    <input type="text" name="host" class="form-control" placeholder="127.0.0.1" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo t('monitor.instancias.puerto'); ?></label>
                                    <input type="number" name="puerto" class="form-control" value="3306" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><?php echo t('monitor.instancias.bd'); ?></label>
                                    <input type="text" name="nombre_bd" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo t('monitor.instancias.usuario'); ?></label>
                                <input type="text" name="usuario" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo t('monitor.instancias.password'); ?></label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-cta"><?php echo t('monitor.instancias.agregar'); ?></button>
                        </form>
                    </div>

                    <div class="col-lg-6">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead><tr><th><?php echo t('monitor.instancias.nombre'); ?></th><th><?php echo t('monitor.instancias.motor'); ?></th><th><?php echo t('monitor.instancias.host'); ?></th><th></th></tr></thead>
                                <tbody>
                                <?php foreach ($instancias as $inst): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($inst['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($inst['tipo_motor']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($inst['host']); ?>:<?php echo (int) $inst['puerto']; ?>
                                            <?php if (!empty($inst['tns_alias'])): ?>
                                                <br><small style="color:var(--text-muted);">TNS: <?php echo htmlspecialchars($inst['tns_alias']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><a href="monitor-salud.php?instancia_id=<?php echo (int) $inst['id']; ?>" class="btn btn-sm btn-cta"><?php echo t('monitor.instancias.ver'); ?></a></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var motorSelect = document.querySelector('select[name="tipo_motor"]');
            var camposManual = document.getElementById('campos-manual');
            var campoTns = document.getElementById('campo-tns');
            var modoOracleWrap = document.getElementById('modo-oracle-wrap');
            var avisoTns = document.getElementById('aviso-tns');
            var selectTns = campoTns.querySelector('select[name="tns_alias"]');
            var radiosModo = document.querySelectorAll('input[name="modo_conexion"]');
            var hayAliases = <?php echo !empty($tnsAliases) ? 'true' : 'false'; ?>;

            function modoElegido() {
                var marcado = document.querySelector('input[name="modo_conexion"]:checked');
                return marcado ? marcado.value : 'tns';
            }

            function actualizarVisibilidad() {
                var esOracle = motorSelect.value === 'oracle';
                var usarTns = esOracle && hayAliases && modoElegido() === 'tns';

                modoOracleWrap.style.display = (esOracle && hayAliases) ? '' : 'none';
                campoTns.style.display = usarTns ? '' : 'none';
                avisoTns.style.display = (esOracle && !hayAliases) ? '' : 'none';
                camposManual.style.display = usarTns ? 'none' : '';

                camposManual.querySelectorAll('input').forEach(function (input) {
                    input.disabled = usarTns;
                });
                selectTns.disabled = !usarTns;
            }

            motorSelect.addEventListener('change', actualizarVisibilidad);
            radiosModo.forEach(function (radio) {
                radio.addEventListener('change', actualizarVisibilidad);
            });
            actualizarVisibilidad();
        });
    </script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>