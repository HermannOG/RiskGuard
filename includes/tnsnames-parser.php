<?php
/**
 * Parser minimo de tnsnames.ora para el Monitor de Salud.
 *
 * Objetivo: en vez de que el admin escriba host/puerto/service_name a
 * mano al registrar una instancia Oracle, elige un alias TNS ya
 * definido en tnsnames.ora, y host/puerto/service_name se leen de ahi
 * -- tanto para conectar (via OCI8, dejando que el cliente Oracle
 * resuelva el alias) como para refrescar la tabla de contexto en cada
 * carga de pantalla, sin depender de lo que haya quedado guardado en
 * la base de datos de RiskGuard.
 *
 * No es un parser completo de la gramatica de Oracle Net (que permite
 * listas de descripciones, failover, IFILE, etc.): cubre el caso
 * comun de un alias con un unico DESCRIPTION -> ADDRESS(HOST, PORT) y
 * CONNECT_DATA(SERVICE_NAME o SID), que es lo que generan Oracle XE,
 * SQL*Plus y los asistentes de instalacion por defecto.
 */

/**
 * Carpeta donde buscar tnsnames.ora, probando en orden (el primero que
 * resuelve gana):
 *   1) La clave 'oracle_tns_admin' de includes/config.php -- explicita,
 *      siempre tiene prioridad porque es intencional.
 *   2) La variable de entorno TNS_ADMIN (la misma que usa el cliente
 *      de Oracle).
 *   3) La variable de entorno ORACLE_HOME + /network/admin.
 *   4) Busqueda automatica en las ubicaciones tipicas donde Oracle se
 *      instala en Windows/Linux/macOS (buscarTnsnamesAutomaticamente).
 * Si ninguna de las 4 encuentra un tnsnames.ora legible, devuelve null
 * y el registro de instancias cae de vuelta al modo manual.
 */
function obtenerCarpetaTnsAdmin(): ?string
{
    static $carpeta = null;
    static $calculada = false;
    if ($calculada) {
        return $carpeta;
    }
    $calculada = true;

    $configPath = __DIR__ . '/config.php';
    $config = file_exists($configPath) ? require $configPath : [];

    if (!empty($config['oracle_tns_admin'])) {
        $carpeta = rtrim($config['oracle_tns_admin'], '/\\');
        return $carpeta;
    }

    $tnsAdminEnv = getenv('TNS_ADMIN');
    if ($tnsAdminEnv) {
        $carpeta = rtrim($tnsAdminEnv, '/\\');
        return $carpeta;
    }

    $oracleHome = getenv('ORACLE_HOME');
    if ($oracleHome) {
        $candidata = rtrim($oracleHome, '/\\') . DIRECTORY_SEPARATOR . 'network' . DIRECTORY_SEPARATOR . 'admin';
        if (is_readable($candidata . DIRECTORY_SEPARATOR . 'tnsnames.ora')) {
            $carpeta = $candidata;
            return $carpeta;
        }
    }

    $carpeta = buscarTnsnamesAutomaticamente();
    return $carpeta;
}

/**
 * Busqueda "mejor esfuerzo" de tnsnames.ora en las ubicaciones donde
 * Oracle instala su cliente por defecto en Windows, Linux y macOS. No
 * hay una unica ubicacion garantizada (depende de si es Instant
 * Client, XE completo, Enterprise...), asi que se revisan varios
 * patrones tipicos y se devuelve la primera carpeta cuyo tnsnames.ora
 * tenga al menos un alias que el parser pueda leer.
 *
 * Solo se llama cuando no hubo config explicita ni TNS_ADMIN/
 * ORACLE_HOME -- esas siempre ganan porque son intencionales.
 */
function buscarTnsnamesAutomaticamente(): ?string
{
    $esWindows = DIRECTORY_SEPARATOR === '\\';

    $patrones = $esWindows
        ? [
            // Oracle Database XE / Enterprise por usuario (18c-23c), como
            // C:\app\<usuario>\product\21c\dbhomeXE\network\admin
            'C:/app/*/product/*/dbhome*/network/admin/tnsnames.ora',
            'C:/app/*/product/*/client*/network/admin/tnsnames.ora',
            // XE clasico (11g/12c/18c en su carpeta propia)
            'C:/oraclexe/app/oracle/product/*/server/network/admin/tnsnames.ora',
            'C:/oraclexe/app/oracle/product/*/client*/network/admin/tnsnames.ora',
            // Instant Client / instalaciones manuales
            'C:/oracle/*/network/admin/tnsnames.ora',
            'C:/Oracle/*/network/admin/tnsnames.ora',
        ]
        : [
            // Linux: instalador RPM estandar y Instant Client manual
            '/opt/oracle/*/network/admin/tnsnames.ora',
            '/u01/app/oracle/product/*/dbhome*/network/admin/tnsnames.ora',
            '/usr/lib/oracle/*/client64/network/admin/tnsnames.ora',
            '/usr/lib/oracle/*/client/network/admin/tnsnames.ora',
            // macOS: casi siempre Instant Client instalado a mano
            (getenv('HOME') ?: '') . '/oracle/*/network/admin/tnsnames.ora',
            '/opt/homebrew/*/network/admin/tnsnames.ora',
        ];

    foreach ($patrones as $patron) {
        $coincidencias = glob($patron, GLOB_NOSORT) ?: [];
        foreach ($coincidencias as $rutaEncontrada) {
            if (is_readable($rutaEncontrada) && count(leerTnsnamesAliases($rutaEncontrada)) > 0) {
                return dirname($rutaEncontrada);
            }
        }
    }

    return null;
}

/**
 * Info de diagnostico: donde se buscó y qué se encontró en cada paso
 * de la cascada. Se usa para mostrarle al admin, cuando no aparece
 * ningun alias, exactamente qué revisó el sistema -- en vez de que
 * tenga que adivinar por qué no lo encontró en una máquina nueva.
 */
function obtenerDiagnosticoTns(): array
{
    $configPath = __DIR__ . '/config.php';
    $config = file_exists($configPath) ? require $configPath : [];

    return [
        'config_oracle_tns_admin' => $config['oracle_tns_admin'] ?? null,
        'env_tns_admin'           => getenv('TNS_ADMIN') ?: null,
        'env_oracle_home'         => getenv('ORACLE_HOME') ?: null,
        'carpeta_resuelta'        => obtenerCarpetaTnsAdmin(),
        'ruta_tnsnames'           => obtenerRutaTnsnames(),
    ];
}

/**
 * Ruta completa a tnsnames.ora, o null si no hay carpeta configurada
 * o el archivo no existe / no se puede leer.
 */
function obtenerRutaTnsnames(): ?string
{
    $carpeta = obtenerCarpetaTnsAdmin();
    if (!$carpeta) {
        return null;
    }
    $ruta = $carpeta . DIRECTORY_SEPARATOR . 'tnsnames.ora';
    return is_readable($ruta) ? $ruta : null;
}

/**
 * Dado el texto de un bloque "(DESCRIPTION = (ADDRESS = ...) (CONNECT_DATA = ...))",
 * extrae host, puerto y service_name/sid con expresiones regulares
 * simples -- no hace falta saber la posicion exacta de cada sub-bloque
 * porque HOST/PORT/SERVICE_NAME/SID son claves unicas dentro del
 * descriptor.
 */
function extraerDescriptorDeBloque(string $bloque): ?array
{
    if (!preg_match('/\bHOST\s*=\s*([^\s\)]+)/i', $bloque, $mHost)) {
        return null;
    }
    if (!preg_match('/\bPORT\s*=\s*([0-9]+)/i', $bloque, $mPuerto)) {
        return null;
    }

    $serviceName = null;
    $sid = null;
    if (preg_match('/\bSERVICE_NAME\s*=\s*([^\s\)]+)/i', $bloque, $mServicio)) {
        $serviceName = $mServicio[1];
    }
    if (preg_match('/\bSID\s*=\s*([^\s\)]+)/i', $bloque, $mSid)) {
        $sid = $mSid[1];
    }
    if (!$serviceName && !$sid) {
        return null;
    }

    return [
        'host'         => $mHost[1],
        'port'         => (int) $mPuerto[1],
        'service_name' => $serviceName,
        'sid'          => $sid,
    ];
}

/**
 * Parsea tnsnames.ora completo y devuelve un mapa alias => descriptor:
 * ['XEPDB1' => ['host' => '127.0.0.1', 'port' => 1521, 'service_name' => 'XEPDB1', 'sid' => null], ...]
 * Los alias se normalizan a MAYUSCULAS (Oracle no distingue mayus/minus
 * en los nombres de alias).
 */
function leerTnsnamesAliases(?string $ruta = null): array
{
    $ruta = $ruta ?? obtenerRutaTnsnames();
    if (!$ruta || !is_readable($ruta)) {
        return [];
    }

    $contenido = file_get_contents($ruta);
    if ($contenido === false) {
        return [];
    }

    // Quitar comentarios (# hasta fin de linea) antes de tokenizar.
    $contenido = preg_replace('/#.*$/m', '', $contenido);

    $aliases = [];
    $len = strlen($contenido);
    $i = 0;

    while ($i < $len) {
        while ($i < $len && ctype_space($contenido[$i])) { $i++; }
        if ($i >= $len) { break; }

        // Leer la lista de nombres (puede haber varios separados por
        // coma apuntando al mismo descriptor) hasta el "=" de nivel
        // superior que abre el bloque.
        $inicioNombres = $i;
        while ($i < $len && $contenido[$i] !== '=') { $i++; }
        if ($i >= $len) { break; }
        $nombres = trim(substr($contenido, $inicioNombres, $i - $inicioNombres));
        $i++; // saltar el "="

        while ($i < $len && ctype_space($contenido[$i])) { $i++; }

        if ($i >= $len || $contenido[$i] !== '(') {
            // Valor suelto sin parentesis (no es un DESCRIPTION, p.ej.
            // una directiva de sqlnet.ora mezclada en el archivo):
            // avanzamos hasta el siguiente espacio para no quedarnos
            // en el mismo punto en la proxima vuelta del while.
            while ($i < $len && !ctype_space($contenido[$i])) { $i++; }
            continue;
        }

        // Capturar el bloque de parentesis balanceado que sigue.
        $inicioBloque = $i;
        $profundidad = 0;
        do {
            if ($contenido[$i] === '(') { $profundidad++; }
            elseif ($contenido[$i] === ')') { $profundidad--; }
            $i++;
        } while ($i < $len && $profundidad > 0);
        $bloque = substr($contenido, $inicioBloque, $i - $inicioBloque);

        if ($nombres === '') { continue; }

        $descriptor = extraerDescriptorDeBloque($bloque);
        if ($descriptor === null) { continue; }

        foreach (explode(',', $nombres) as $alias) {
            $alias = strtoupper(trim($alias));
            if ($alias !== '') {
                $aliases[$alias] = $descriptor;
            }
        }
    }

    return $aliases;
}

/**
 * Descriptor actual de un alias TNS puntual, o null si el archivo no
 * existe / no es legible, o el alias ya no esta definido.
 */
function resolverAliasTns(string $alias, ?string $ruta = null): ?array
{
    $aliases = leerTnsnamesAliases($ruta);
    return $aliases[strtoupper(trim($alias))] ?? null;
}

/**
 * Devuelve una copia de $instancia con host/puerto/nombre_bd
 * refrescados EN VIVO desde tnsnames.ora, si la instancia es Oracle y
 * tiene un tns_alias guardado y ese alias todavia existe en el
 * archivo. Si algo falla (no hay TNS_ADMIN configurado, el archivo no
 * se puede leer, el alias ya no existe...) se devuelve $instancia sin
 * tocar -- con los ultimos valores conocidos que quedaron guardados en
 * monitor_instancias al registrarla -- marcada con 'tns_resuelto' =>
 * false para que la vista pueda avisarlo en vez de fallar.
 */
function refrescarInstanciaDesdeTns(array $instancia): array
{
    $instancia['tns_resuelto'] = false;

    if (($instancia['tipo_motor'] ?? null) !== 'oracle' || empty($instancia['tns_alias'])) {
        return $instancia;
    }

    $descriptor = resolverAliasTns($instancia['tns_alias']);
    if ($descriptor === null) {
        return $instancia;
    }

    $instancia['host']         = $descriptor['host'];
    $instancia['puerto']       = $descriptor['port'];
    $instancia['nombre_bd']    = $descriptor['service_name'] ?? $descriptor['sid'];
    $instancia['tns_resuelto'] = true;

    return $instancia;
}