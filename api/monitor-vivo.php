<?php
/**
 * Endpoint AJAX de captura EN VIVO para el modo "estres en tiempo real"
 * del Monitor de Salud. Lee las variables crudas de la instancia,
 * calcula los indices (IP/IM/IA/ISBD) y devuelve el HTML de las mismas
 * roscas/gauges que ya usa monitor-salud.php -- pero SIN guardar nada en
 * el historial (usa MonitorRepository::calcularIndicesEnVivo).
 *
 * Lo consume el poller de JavaScript de monitor-salud.php cada pocos
 * segundos mientras hay una prueba de estres corriendo, para ver como
 * cambian los graficos en tiempo real.
 */
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/auth.php';

if (!esAdmin()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Solo un administrador puede usar el monitor.']);
    exit;
}

require_once __DIR__ . '/../includes/db-monitor.php';
require_once __DIR__ . '/../includes/monitor-crypto.php';
require_once __DIR__ . '/../includes/MonitorAdapterInterface.php';
require_once __DIR__ . '/../includes/MariaDBAdapter.php';
require_once __DIR__ . '/../includes/OracleAdapter.php';
require_once __DIR__ . '/../includes/OracleStress.php';
require_once __DIR__ . '/../includes/MonitorRepository.php';
require_once __DIR__ . '/../includes/monitor-render.php';
require_once __DIR__ . '/../includes/tnsnames-parser.php';

try {
    $pdo = dbMonitor();
    $instanciaId = (int) ($_GET['instancia_id'] ?? 0);

    $stmt = $pdo->prepare("SELECT * FROM monitor_instancias WHERE id = :id");
    $stmt->execute(['id' => $instanciaId]);
    $instancia = $stmt->fetch();
    if (!$instancia) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Instancia no encontrada.']);
        exit;
    }

    $instancia = refrescarInstanciaDesdeTns($instancia);
    $instancia['password_plano'] = monitorDecrypt($instancia['password_enc']);

    // Conecta al motor objetivo y, en Oracle, reutiliza la MISMA conexion
    // para consultar si la prueba de estres sigue activa.
    $estresActivo = false;
    if ($instancia['tipo_motor'] === 'mariadb') {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $instancia['host'], $instancia['puerto'], $instancia['nombre_bd']);
        $pdoObjetivo = new PDO($dsn, $instancia['usuario'], $instancia['password_plano'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $adapter = new MariaDBAdapter($pdoObjetivo);
    } elseif ($instancia['tipo_motor'] === 'oracle') {
        $ociConn = conectarOracleInstancia($instancia);
        $adapter = new OracleAdapter($ociConn);
        $estado = (new OracleStress($ociConn))->estado();
        $estresActivo = ($estado['total'] ?? 0) > 0;
    } else {
        throw new RuntimeException('Motor no soportado: ' . $instancia['tipo_motor']);
    }

    $lecturas = $adapter->obtenerLecturas();

    // MEMORIA POR DELTA (solo Oracle, solo modo en vivo): m2 y m3 son
    // contadores acumulados desde que arranco la instancia, asi que de por
    // vida casi no se mueven. Aqui los recalculamos como la DIFERENCIA
    // respecto a la lectura anterior (guardada en sesion), para que reflejen
    // la actividad del intervalo y el ISBD general responda al estres.
    // No afecta las capturas guardadas en el historial.
    if ($instancia['tipo_motor'] === 'oracle') {
        $raw  = $adapter->obtenerCrudosMemoria();
        $prev = $_SESSION['vivo_mem'][$instanciaId] ?? null;
        if ($prev) {
            $dLog = $raw['logical'] - $prev['logical'];
            $dPhy = $raw['fisica']  - $prev['fisica'];
            if ($dLog > 0) {
                $lecturas['m3'] = round(max(0, min(100, (1 - $dPhy / $dLog) * 100)), 2);
            }
            $dMem = $raw['mem_waits']   - $prev['mem_waits'];
            $dTot = $raw['total_waits'] - $prev['total_waits'];
            $lecturas['m2'] = $dTot > 0 ? round(max(0, min(100, $dMem / $dTot * 100)), 2) : 0.0;
        }
        $_SESSION['vivo_mem'][$instanciaId] = $raw;
    }

    $repo = new MonitorRepository($pdo);
    $r = $repo->calcularIndicesEnVivo($instanciaId, $lecturas);

    $lang = $_SESSION['lang'] ?? 'es';
    $colores = monitorColores();
    $colorGeneral = $colores[$r['estado']] ?? '#999';

    // Detalle por variable (mismas barras que la pagina), calculado en vivo.
    $detalle = $repo->obtenerDetalleEnVivo($instanciaId, $lecturas, $lang);
    $detallePorComp = ['procesos' => '', 'memoria' => '', 'archivos' => ''];
    foreach ($detalle as $d) {
        if (isset($detallePorComp[$d['componente']])) {
            $detallePorComp[$d['componente']] .= renderBarraRango($d, $colores);
        }
    }

    // Fragmentos con las MISMAS funciones de render de la pagina.
    $componentes = ['procesos', 'memoria', 'archivos'];
    $gauges = [];
    foreach ($componentes as $comp) {
        $valor = (float) $r['indice_' . $comp];
        $gauges[$comp] = [
            'svg'     => renderGaugeChico($valor),
            'valor'   => number_format($valor, 2),
            'color'   => $colores[$r['estado_' . $comp]] ?? '#999',
            'detalle' => $detallePorComp[$comp],
        ];
    }

    echo json_encode([
        'ok'            => true,
        'estres_activo' => $estresActivo,
        'isbd'          => number_format((float) $r['indice_salud'], 2),
        'rosca_html'    => renderRoscaGrande((float) $r['indice_salud'], $colorGeneral),
        'gauges'        => $gauges,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
