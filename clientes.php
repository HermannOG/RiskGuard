<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/auth.php';
requiereAdmin();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ClienteRepository.php';

$pageTitleKey = 'clientes.pagetitle';

include 'includes/header.php';
include 'includes/navbar.php';

$repo      = new ClienteRepository(db());
$proyectos = $repo->listarProyectos($LANG);

/*
|--------------------------------------------------------------------------
| Información adicional de los clientes
|--------------------------------------------------------------------------
| Esta información queda hardcodeada intencionalmente.
| El contenido se muestra mediante Bootstrap Collapse.
|
| La llave debe coincidir con cliente_slug de la base de datos.
|--------------------------------------------------------------------------
*/

$detallesClientes = [

    'esph' => [

        'descripcion' => 'Empresa de Servicios Públicos de Heredia (ESPH), organización dedicada a la prestación de servicios públicos y al desarrollo de soluciones en áreas como agua potable, energía eléctrica, alumbrado público, residuos y tecnologías de información.',

        'organigrama' => null,

        'tablespaces' => [

            [
                'nombre' => 'SYSTEM',
                'desc'   => 'Estructuras fundamentales de Oracle. Reservado, no modificado.',
                'icono'  => 'fa-gear',
                'tipo'   => 'Oracle'
            ],

            [
                'nombre' => 'SYSAUX',
                'desc'   => 'Componentes auxiliares de Oracle. Reservado, no modificado.',
                'icono'  => 'fa-gear',
                'tipo'   => 'Oracle'
            ],

            [
                'nombre' => 'UNDO',
                'desc'   => 'Información de rollback y transacciones. Gestionado automáticamente.',
                'icono'  => 'fa-rotate-left',
                'tipo'   => 'Oracle'
            ],

            [
                'nombre' => 'TEMP',
                'desc'   => 'Operaciones temporales, sorts y joins. Compartido entre esquemas.',
                'icono'  => 'fa-clock',
                'tipo'   => 'Oracle'
            ],

            [
                'nombre' => 'APP_DATA',
                'desc'   => 'Datos empresariales. 50 MB iniciales, AUTOEXTEND hasta 500 MB.',
                'icono'  => 'fa-table',
                'tipo'   => 'Aplicación'
            ],

            [
                'nombre' => 'APP_INDEX',
                'desc'   => 'Índices empresariales. 50 MB iniciales, AUTOEXTEND hasta 500 MB.',
                'icono'  => 'fa-magnifying-glass',
                'tipo'   => 'Aplicación'
            ],

        ],

        'esquemas' => [

            [
                'usuario' => 'ESPH_RESIDUOS',

                'sistemas' => [
                    'SGA — Sistema de Gestión de Acopio',
                    'SRR — Sistema de Rutas y Recolección'
                ],

                'tablas' => [
                    'centro_acopio',
                    'tipo_residuo',
                    'ingreso_residuo',
                    'ruta',
                    'vehiculo',
                    'ejecucion_ruta'
                ],

                'icono' => 'fa-recycle',

                'area' => 'Negocio de Residuos'
            ],

            [
                'usuario' => 'ESPH_ENERGIA',

                'sistemas' => [
                    'SRED — Sistema de Red Eléctrica y Distribución',
                    'SALP — Sistema de Alumbrado Público'
                ],

                'tablas' => [
                    'subestacion',
                    'circuito',
                    'medidor',
                    'lectura_medidor',
                    'luminaria',
                    'orden_mant_luminaria'
                ],

                'icono' => 'fa-bolt',

                'area' => 'Negocio de Energía Eléctrica y Alumbrado Público'
            ],

            [
                'usuario' => 'ESPH_AGUA',

                'sistemas' => [
                    'SCDA — Sistema de Control y Distribución de Agua',
                    'SHH — Sistema de Hidrantes y Presión Hídrica'
                ],

                'tablas' => [
                    'planta_potabilizadora',
                    'tanque',
                    'zona_distribucion',
                    'conexion',
                    'lectura_agua',
                    'hidrante',
                    'inspeccion_hidrante'
                ],

                'icono' => 'fa-droplet',

                'area' => 'Negocio Agua Potable e Hidrantes'
            ],

            [
                'usuario' => 'ESPH_TIC',

                'sistemas' => [
                    'SGTI — Sistema de Gestión de Infraestructura TI',
                    'SAMS — Sistema de Atención y Mesa de Servicio'
                ],

                'tablas' => [
                    'categoria_activo',
                    'activo_ti',
                    'licencia_software',
                    'categoria_ticket',
                    'usuario_interno',
                    'ticket'
                ],

                'icono' => 'fa-microchip',

                'area' => 'Negocio de Tecnologías e Infocomunicaciones'
            ],

        ],

        'decisiones' => [

            [
                'titulo' => 'Separación datos / índices',

                'desc' => 'APP_DATA para segmentos de tabla y APP_INDEX para todos los índices, incluyendo PKs con USING INDEX TABLESPACE APP_INDEX.'
            ],

            [
                'titulo' => 'Un esquema por negocio',

                'desc' => 'Aislamiento de responsabilidades y cuotas individuales (200 MB datos, 100 MB índices) por área, evitando contaminación entre negocios.'
            ],

            [
                'titulo' => 'IDENTITY en lugar de secuencias',

                'desc' => 'PKs con GENERATED ALWAYS AS IDENTITY. Solo se creó una secuencia externa (seq_folio_ingreso) para numeración de folios de negocio.'
            ],

            [
                'titulo' => 'INSERT con SELECT para FKs',

                'desc' => 'Los inserts de datos de prueba usan SELECT para obtener el ID padre por nombre, evitando dependencia de que IDENTITY comience en 1.'
            ],

            [
                'titulo' => 'Oracle Managed Files',

                'desc' => 'El script detecta automáticamente si DB_CREATE_FILE_DEST está configurado y ajusta el CREATE TABLESPACE correspondientemente.'
            ],

            [
                'titulo' => 'Contraseñas en tiempo de ejecución',

                'desc' => 'Los passwords se solicitan con ACCEPT ... HIDE al ejecutar el script, nunca quedan almacenados en texto plano.'
            ],

        ]
    ]

];

// Función auxiliar para formatear código SQL en las secciones del accordion
function renderSqlBlock($sql, $title = 'Script SQL') {
    $sql = trim($sql);
    if (empty($sql)) return '';
    
    $html = '
    <div style="position:relative;margin-top:1.5rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0.75rem;background:rgba(0,0,0,0.2);border:1px solid var(--border);border-radius:8px 8px 0 0;border-bottom:1px solid var(--border);">
            <span style="font-size:0.85rem;color:var(--text-muted);">
                <i class="fa-solid fa-code" style="color:var(--risk-mid);margin-right:0.5rem;"></i>
                ' . htmlspecialchars($title) . '
            </span>
            <button onclick="copiarCodigo(this)" style="background:transparent;border:1px solid var(--border);border-radius:6px;padding:0.2rem 0.75rem;font-size:0.75rem;color:var(--text-muted);cursor:pointer;transition:all 0.2s;">
                <i class="fa-regular fa-copy" style="margin-right:0.4rem;"></i>
                Copiar
            </button>
        </div>
        <pre style="margin:0;background:rgba(0,0,0,0.25);border:1px solid var(--border);border-top:none;border-radius:0 0 8px 8px;overflow-x:auto;padding:1rem;font-family:var(--font-mono);font-size:0.75rem;line-height:1.5;color:var(--text);"><code style="font-family:var(--font-mono);font-size:0.75rem;color:var(--text);">' . htmlspecialchars($sql) . '</code></pre>
    </div>
    <script>
    function copiarCodigo(btn) {
        var pre = btn.closest("div").nextElementSibling;
        var code = pre.querySelector("code");
        var texto = code.textContent;
        navigator.clipboard.writeText(texto).then(function() {
            btn.innerHTML = \'<i class="fa-regular fa-check" style="margin-right:0.4rem;"></i> Copiado\';
            setTimeout(function() {
                btn.innerHTML = \'<i class="fa-regular fa-copy" style="margin-right:0.4rem;"></i> Copiar\';
            }, 2000);
        }).catch(function() {
            // Fallback para navegadores que no soportan clipboard API
            var area = document.createElement("textarea");
            area.value = texto;
            document.body.appendChild(area);
            area.select();
            document.execCommand("copy");
            document.body.removeChild(area);
            btn.innerHTML = \'<i class="fa-regular fa-check" style="margin-right:0.4rem;"></i> Copiado\';
            setTimeout(function() {
                btn.innerHTML = \'<i class="fa-regular fa-copy" style="margin-right:0.4rem;"></i> Copiar\';
            }, 2000);
        });
    }
    </script>
    ';
    return $html;
}
?>

<main class="flex-grow-1">

  <section class="section">

    <div class="container">

      <!-- =========================================================
           ENCABEZADO
      ========================================================== -->

      <span class="section-eyebrow">
        <i class="fa-solid fa-briefcase me-2"></i>
        <?php echo t('clientes.eyebrow'); ?>
      </span>

      <h2 class="section-title">
        <?php echo t('clientes.title'); ?>
      </h2>

      <p class="section-lead mb-5" style="color:var(--text)">
        <?php echo t('clientes.lead'); ?>
      </p>


      <!-- =========================================================
           TARJETAS DE CLIENTES
      ========================================================== -->

      <div class="row g-4">

        <?php foreach ($proyectos as $p): ?>

          <?php

          $tags = array_filter(
              array_map(
                  'trim',
                  explode(',', $p['etiquetas'] ?? '')
              )
          );

          $slug = $p['cliente_slug'] ?? null;

          /*
           * Determinamos si este cliente tiene información
           * adicional hardcodeada.
           */
          $tieneDetalle = $slug && isset($detallesClientes[$slug]);

          /*
           * Creamos un ID único para el Collapse.
           */
          $collapseId = 'detalle-' . preg_replace(
              '/[^a-zA-Z0-9_-]/',
              '',
              $slug ?? uniqid()
          );

          ?>

          <div class="col-md-6 col-lg-4">

            <div class="service-card h-100 d-flex flex-column">

              <!-- Icono -->
              <i
                class="fa-solid <?php echo htmlspecialchars($p['icono']); ?> mb-3"
                style="font-size:1.6rem;color:var(--risk-mid)"
              ></i>


              <!-- Cliente -->
              <?php if (!empty($p['cliente_nombre'])): ?>

                <span class="cliente-badge mb-2">

                  <i class="fa-solid fa-building me-1"></i>

                  <?php echo htmlspecialchars($p['cliente_nombre']); ?>

                  <?php if (!empty($p['cliente_sector'])): ?>

                    · <?php echo htmlspecialchars($p['cliente_sector']); ?>

                  <?php endif; ?>

                </span>

              <?php endif; ?>


              <!-- Título -->
              <h5 class="mb-2">
                <?php echo htmlspecialchars($p['titulo']); ?>
              </h5>


              <!-- Descripción -->
              <p
                class="section-lead mb-3"
                style="color:var(--text)"
              >
                <?php echo htmlspecialchars($p['descripcion']); ?>
              </p>


              <!-- Tags -->
              <?php if ($tags): ?>

                <div class="cliente-tags mt-3">

                  <?php foreach ($tags as $tag): ?>

                    <span class="cliente-tag">
                      <?php echo htmlspecialchars($tag); ?>
                    </span>

                  <?php endforeach; ?>

                </div>

              <?php endif; ?>


              <!-- =================================================
                   BOTONES
              ================================================== -->

              <div class="mt-auto pt-3 d-flex flex-wrap gap-2">

                <?php if ($tieneDetalle): ?>

                  <button
                    type="button"
                    class="btn btn-sm btn-ghost"
                    data-bs-toggle="collapse"
                    data-bs-target="#<?php echo $collapseId; ?>"
                    aria-expanded="false"
                    aria-controls="<?php echo $collapseId; ?>"
                  >

                    <i class="fa-solid fa-chevron-down me-1"></i>

                    Ver información

                  </button>

                <?php endif; ?>


                <?php if (!empty($p['url_demo'])): ?>

                  <a
                    href="<?php echo htmlspecialchars($p['url_demo']); ?>"
                    class="btn btn-sm btn-ghost"
                    target="_blank"
                    rel="noopener"
                  >

                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>

                    <?php echo t('clientes.ver_demo'); ?>

                  </a>

                <?php endif; ?>

              </div>

            </div>

          </div>


          <!-- =====================================================
               INFORMACIÓN DESPLEGABLE
          ====================================================== -->

          <?php if ($tieneDetalle): ?>

            <div class="col-12">

              <div
                id="<?php echo $collapseId; ?>"
                class="collapse"
              >

                <div
                  class="mt-2 mb-4"
                  style="
                    background:var(--surface);
                    border:1px solid var(--border);
                    border-radius:14px;
                    padding:2rem;
                  "
                >

                  <!-- =================================================
                       INFORMACIÓN GENERAL
                  ================================================== -->

                  <div class="mb-5">

                    <span class="section-eyebrow">

                      <i class="fa-solid fa-building me-2"></i>

                      Información del cliente

                    </span>

                    <h3
                      class="section-title"
                      style="font-size:1.6rem"
                    >

                      <?php echo htmlspecialchars($p['cliente_nombre']); ?>

                    </h3>

                    <p
                      style="
                        color:var(--text);
                        line-height:1.75;
                        font-size:1.02rem;
                        margin-bottom:0;
                      "
                    >

                      <?php echo htmlspecialchars(
                          $detallesClientes[$slug]['descripcion']
                      ); ?>

                    </p>

                  </div>

                    <!-- =================================================
                             ORGANIGRAMA INSTITUCIONAL
                        ================================================== -->
                        
                        <div class="mb-5">
                        
                          <span class="section-eyebrow">
                        
                            <i class="fa-solid fa-sitemap me-2"></i>
                        
                            Organigrama institucional
                        
                          </span>
                        
                          <h4
                            class="section-title"
                            style="font-size:1.4rem"
                          >
                            ESPH S.A.
                          </h4>
                        
                          <div
                            style="
                              background:var(--surface);
                              border:1px solid var(--border);
                              border-radius:14px;
                              overflow:hidden;
                              margin-top:1.5rem;
                            "
                          >
                        
                            <div
                              style="
                                padding:0.75rem 1rem;
                                border-bottom:1px solid var(--border);
                                font-family:var(--font-mono);
                                font-size:0.75rem;
                                color:var(--text-muted);
                              "
                            >
                        
                              <i class="fa-solid fa-sitemap me-2"></i>
                        
                              Organigrama Institucional 2026
                        
                            </div>
                        
                            <div
                              style="
                                padding:1rem;
                                overflow-x:auto;
                                text-align:center;
                              "
                            >
                        
                              <img
                                src="assets/img/ESPH%20Organigrama%20Institucional%202026.png"
                                alt="Organigrama Institucional 2026 de ESPH S.A."
                                style="
                                  width:100%;
                                  max-width:1400px;
                                  height:auto;
                                  display:block;
                                  margin:0 auto;
                                "
                              >
                        
                            </div>
                        
                          </div>
                        
                        </div>


                  <!-- =================================================
                       SEPARADOR
                  ================================================== -->

                  <div
                    style="
                      border-top:1px solid var(--border);
                      margin-bottom:3rem;
                    "
                  ></div>


<!-- =========================================================
     INFORMACIÓN DE LA BASE DE DATOS
     Adaptado al sistema de diseño de RiskGuard:
     - Font Awesome en lugar de Bootstrap Icons
     - Variables CSS del tema (var(--surface), var(--border), var(--risk-mid), var(--text), var(--text-muted))
     - Sin bg-light, card, alert-*, table-light (no respetan modo oscuro)
========================================================== -->

<div style="margin-top:2rem">

    <!-- Encabezado -->
    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem">
        <div style="width:2.8rem;height:2.8rem;border-radius:50%;background:rgba(242,177,52,0.12);border:1px solid rgba(242,177,52,0.3);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fa-solid fa-database" style="color:var(--risk-mid)"></i>
        </div>
        <div>
            <h4 style="margin:0;font-size:1.15rem">Arquitectura y configuración de la base de datos</h4>
            <p style="margin:0;font-size:0.82rem;color:var(--text-muted)">
                Descripción técnica de las modificaciones realizadas sobre Oracle Database para representar el entorno empresarial de ESPH S.A.
            </p>
        </div>
    </div>

    <!-- Alerta introductoria -->
    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:1rem 1.25rem;margin-bottom:1.5rem;font-size:0.88rem;color:var(--text)">
        <strong style="color:var(--risk-mid)">¿Qué se realizó?</strong><br>
        Se diseñó y configuró una estructura de almacenamiento empresarial sobre <strong>Oracle Database 21c XE</strong>,
        separando los datos empresariales, los índices, los esquemas por área de negocio y los privilegios de acceso.
        El objetivo es proporcionar una estructura organizada, controlada y escalable para representar diferentes dominios operativos de ESPH S.A.
    </div>

    <!-- Accordion -->
    <div class="accordion" id="accordionBD">


        <!-- 1. ENTORNO -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingEntorno">
                <button class="accordion-button" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseEntorno"
                        aria-expanded="true"
                        style="background:var(--surface);color:var(--text);box-shadow:none;border-bottom:1px solid var(--border)">
                    <i class="fa-solid fa-server me-2" style="color:var(--risk-mid)"></i>
                    1. Preparación y verificación del entorno Oracle
                </button>
            </h2>
            <div id="collapseEntorno" class="accordion-collapse collapse show" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Qué se hizo?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Antes de realizar cualquier modificación estructural se verificó la versión de Oracle,
                        el contenedor activo, el usuario conectado, los tablespaces existentes y la configuración de almacenamiento.
                    </p>

                    <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:1rem;font-size:0.85rem">
                        <strong>Oracle utilizado:</strong>
                        <ul style="margin:0.5rem 0 0 0;padding-left:1.25rem;color:var(--text-muted)">
                            <li>Oracle Database 21c XE</li>
                            <li>PDB: <code>XEPDB1</code></li>
                            <li>Ejecución administrativa: <code>SYS AS SYSDBA</code></li>
                        </ul>
                    </div>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Por qué se hizo?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Esta verificación evita ejecutar accidentalmente operaciones administrativas sobre un contenedor incorrecto.
                        En una arquitectura Oracle Multitenant, trabajar en el PDB equivocado podría provocar que los usuarios,
                        tablespaces u objetos fueran creados en un entorno diferente al esperado.
                    </p>

                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text)">
                        <strong style="color:var(--risk-mid)">Justificación:</strong> el script incluye una validación mediante
                        <code>SYS_CONTEXT('USERENV', 'CON_NAME')</code> que detiene la ejecución si el contenedor no corresponde a <code>XEPDB1</code>.
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- PARTE 0 - VERIFICACION DEL ENTORNO
-- ============================================================

SET SERVEROUTPUT ON
SET VERIFY OFF
SET FEEDBACK ON

WHENEVER SQLERROR EXIT SQL.SQLCODE ROLLBACK;

SELECT banner
FROM v$version
WHERE banner LIKE \'Oracle Database%\';

SHOW CON_NAME;
SHOW USER;

ALTER SESSION SET CONTAINER = XEPDB1;

SHOW CON_NAME;

DECLARE
    v_con_name VARCHAR2(128);
BEGIN
    v_con_name := SYS_CONTEXT(\'USERENV\', \'CON_NAME\');

    IF UPPER(v_con_name) <> \'XEPDB1\' THEN
        RAISE_APPLICATION_ERROR(-20001, \'ERROR: El script debe ejecutarse dentro de XEPDB1.\');
    END IF;

    DBMS_OUTPUT.PUT_LINE(\'OK: El script se ejecutara en el PDB \' || v_con_name);
END;
/
'); ?>

                </div>
            </div>
        </div>


        <!-- 2. TABLESPACES -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingTablespaces">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseTablespaces"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-layer-group me-2" style="color:var(--risk-mid)"></i>
                    2. Organización física mediante Tablespaces
                </button>
            </h2>
            <div id="collapseTablespaces" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Qué se hizo?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Se inspeccionaron los tablespaces existentes de Oracle y posteriormente se crearon dos tablespaces
                        destinados exclusivamente a los objetos de la aplicación:
                    </p>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(242,177,52,0.3);border-radius:10px;padding:1.25rem;height:100%">
                                <h5 style="font-size:0.95rem;color:var(--risk-mid);margin-bottom:0.75rem">
                                    <i class="fa-solid fa-table me-2"></i>APP_DATA
                                </h5>
                                <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:0.75rem">
                                    Destinado al almacenamiento de las <strong style="color:var(--text)">tablas y datos empresariales</strong>.
                                </p>
                                <ul style="font-size:0.82rem;color:var(--text-muted);padding-left:1.25rem;margin:0">
                                    <li>Tamaño inicial: 50 MB</li>
                                    <li>Incremento automático: 10 MB</li>
                                    <li>Tamaño máximo: 500 MB</li>
                                    <li>Extents administrados localmente</li>
                                    <li>Segment Space Management automático</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(242,177,52,0.3);border-radius:10px;padding:1.25rem;height:100%">
                                <h5 style="font-size:0.95rem;color:var(--risk-mid);margin-bottom:0.75rem">
                                    <i class="fa-solid fa-table-columns me-2"></i>APP_INDEX
                                </h5>
                                <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:0.75rem">
                                    Destinado exclusivamente al almacenamiento de los <strong style="color:var(--text)">índices</strong>.
                                </p>
                                <ul style="font-size:0.82rem;color:var(--text-muted);padding-left:1.25rem;margin:0">
                                    <li>Tamaño inicial: 50 MB</li>
                                    <li>Incremento automático: 10 MB</li>
                                    <li>Tamaño máximo: 500 MB</li>
                                    <li>Administración local de extents</li>
                                    <li>Segment Space Management automático</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Por qué se separaron los datos y los índices?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        La separación permite administrar de forma independiente el espacio utilizado por los datos y por las
                        estructuras de indexación. Esto facilita tareas de administración, monitoreo y crecimiento de almacenamiento.
                    </p>

                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text)">
                        <strong style="color:var(--risk-mid)">Decisión de diseño:</strong><br>
                        <code>APP_DATA</code> → tablas y datos &nbsp;·&nbsp; <code>APP_INDEX</code> → índices<br><br>
                        De esta forma se evita concentrar todos los objetos de aplicación en los tablespaces internos de Oracle
                        como <code>SYSTEM</code> y <code>SYSAUX</code>.
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- PARTE 1 - TABLESPACES EXISTENTES
-- ============================================================

SELECT
    tablespace_name,
    status,
    contents,
    extent_management,
    segment_space_management
FROM dba_tablespaces
ORDER BY tablespace_name;

SELECT
    tablespace_name,
    file_name,
    ROUND(bytes / 1024 / 1024, 2) AS size_mb,
    autoextensible,
    ROUND(maxbytes / 1024 / 1024, 2) AS max_size_mb
FROM dba_data_files
ORDER BY tablespace_name, file_name;

-- ============================================================
-- PARTE 7 - CREANDO APP_DATA
-- ============================================================

DECLARE
    v_omf      VARCHAR2(1000);
    v_sql      VARCHAR2(4000);
BEGIN
    SELECT value INTO v_omf FROM v$parameter WHERE name = \'db_create_file_dest\';

    IF v_omf IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE(\'Oracle Managed Files detectado: \' || v_omf);
        v_sql := \'CREATE TABLESPACE APP_DATA SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    ELSE
        DBMS_OUTPUT.PUT_LINE(\'OMF no esta configurado.\');
        v_sql := \'CREATE TABLESPACE APP_DATA DATAFILE \'\'&DATA_DIR\\app_data01.dbf\'\' SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    END IF;

    EXECUTE IMMEDIATE v_sql;
    DBMS_OUTPUT.PUT_LINE(\'APP_DATA creado correctamente.\');
END;
/

-- ============================================================
-- PARTE 8 - CREANDO APP_INDEX
-- ============================================================

DECLARE
    v_omf      VARCHAR2(1000);
    v_sql      VARCHAR2(4000);
BEGIN
    SELECT value INTO v_omf FROM v$parameter WHERE name = \'db_create_file_dest\';

    IF v_omf IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE(\'Oracle Managed Files detectado: \' || v_omf);
        v_sql := \'CREATE TABLESPACE APP_INDEX SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    ELSE
        DBMS_OUTPUT.PUT_LINE(\'OMF no esta configurado.\');
        v_sql := \'CREATE TABLESPACE APP_INDEX DATAFILE \'\'&DATA_DIR\\app_index01.dbf\'\' SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    END IF;

    EXECUTE IMMEDIATE v_sql;
    DBMS_OUTPUT.PUT_LINE(\'APP_INDEX creado correctamente.\');
END;
/
'); ?>

                </div>
            </div>
        </div>


        <!-- 3. OMF -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingOMF">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseOMF"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-folder-open me-2" style="color:var(--risk-mid)"></i>
                    3. Administración de los archivos físicos
                </button>
            </h2>
            <div id="collapseOMF" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Qué se hizo?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        El script comprueba si Oracle tiene configurado <code>DB_CREATE_FILE_DEST</code>.
                        Si está disponible, se utiliza <strong style="color:var(--text)">Oracle Managed Files (OMF)</strong>.
                        En caso contrario, se utiliza una ruta definida manualmente.
                    </p>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Por qué?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Esto permite que la creación de los tablespaces no dependa completamente de una ruta física específica del servidor.
                        Cuando OMF está disponible, Oracle administra la ubicación y nombres de los archivos físicos.
                    </p>

                    <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text-muted)">
                        <strong style="color:var(--text)">Resultado:</strong><br>
                        El script puede adaptarse a diferentes instalaciones de Oracle sin modificar necesariamente toda la definición de almacenamiento.
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- PARTE 6 - ORACLE MANAGED FILES
-- ============================================================

SELECT
    name,
    value
FROM v$parameter
WHERE name = \'db_create_file_dest\';

-- ============================================================
-- PARTE 7 y 8 - CREACIÓN DE TABLESPACES CON OMF
-- ============================================================

DEFINE DATA_DIR = \'C:\oracle\data\'

-- El bloque DECLARE de APP_DATA (PARTE 7)
DECLARE
    v_omf      VARCHAR2(1000);
    v_sql      VARCHAR2(4000);
BEGIN
    SELECT value INTO v_omf FROM v$parameter WHERE name = \'db_create_file_dest\';

    IF v_omf IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE(\'Oracle Managed Files detectado: \' || v_omf);
        v_sql := \'CREATE TABLESPACE APP_DATA SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    ELSE
        DBMS_OUTPUT.PUT_LINE(\'OMF no esta configurado.\');
        v_sql := \'CREATE TABLESPACE APP_DATA DATAFILE \'\'&DATA_DIR\\app_data01.dbf\'\' SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    END IF;

    EXECUTE IMMEDIATE v_sql;
    DBMS_OUTPUT.PUT_LINE(\'APP_DATA creado correctamente.\');
END;
/

-- El bloque DECLARE de APP_INDEX (PARTE 8)
DECLARE
    v_omf      VARCHAR2(1000);
    v_sql      VARCHAR2(4000);
BEGIN
    SELECT value INTO v_omf FROM v$parameter WHERE name = \'db_create_file_dest\';

    IF v_omf IS NOT NULL THEN
        DBMS_OUTPUT.PUT_LINE(\'Oracle Managed Files detectado: \' || v_omf);
        v_sql := \'CREATE TABLESPACE APP_INDEX SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    ELSE
        DBMS_OUTPUT.PUT_LINE(\'OMF no esta configurado.\');
        v_sql := \'CREATE TABLESPACE APP_INDEX DATAFILE \'\'&DATA_DIR\\app_index01.dbf\'\' SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    END IF;

    EXECUTE IMMEDIATE v_sql;
    DBMS_OUTPUT.PUT_LINE(\'APP_INDEX creado correctamente.\');
END;
/
'); ?>

                </div>
            </div>
        </div>


        <!-- 4. USUARIOS -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingUsuarios">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseUsuarios"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-users me-2" style="color:var(--risk-mid)"></i>
                    4. Creación de esquemas y separación por áreas
                </button>
            </h2>
            <div id="collapseUsuarios" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Qué se hizo?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Se crearon cuatro usuarios de Oracle que funcionan también como <strong style="color:var(--text)">esquemas independientes</strong>:
                    </p>

                    <div class="row g-3 mb-3">
                        <?php
                        $esquemas_info = [
                            ['nombre'=>'ESPH_RESIDUOS','desc'=>'Sistemas relacionados con gestión de residuos.','sistemas'=>'SGA y SRR','icono'=>'fa-recycle'],
                            ['nombre'=>'ESPH_ENERGIA', 'desc'=>'Sistemas relacionados con energía eléctrica.',  'sistemas'=>'SRED y SALP','icono'=>'fa-bolt'],
                            ['nombre'=>'ESPH_AGUA',    'desc'=>'Sistemas relacionados con agua potable.',       'sistemas'=>'SCDA y SHH','icono'=>'fa-droplet'],
                            ['nombre'=>'ESPH_TIC',     'desc'=>'Sistemas relacionados con tecnología de información.','sistemas'=>'SGTI y SAMS','icono'=>'fa-microchip'],
                        ];
                        foreach ($esquemas_info as $ei):
                        ?>
                        <div class="col-md-6">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:8px;padding:1rem;height:100%">
                                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem">
                                    <i class="fa-solid <?php echo $ei['icono']; ?>" style="color:var(--risk-mid);font-size:0.85rem"></i>
                                    <strong style="font-size:0.85rem;font-family:var(--font-mono)"><?php echo $ei['nombre']; ?></strong>
                                </div>
                                <p style="font-size:0.82rem;color:var(--text-muted);margin:0 0 0.25rem 0"><?php echo $ei['desc']; ?></p>
                                <span style="font-size:0.72rem;font-family:var(--font-mono);color:var(--risk-mid)"><?php echo $ei['sistemas']; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="border-top:1px solid var(--border);margin:1rem 0"></div>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Por qué utilizar esquemas separados?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        La separación por esquemas permite aplicar una división lógica de responsabilidades sobre los datos.
                        Cada área posee sus propias tablas y objetos, evitando concentrar toda la información empresarial en un único esquema.
                    </p>

                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text)">
                        <strong style="color:var(--risk-mid)">Principio aplicado: separación de responsabilidades.</strong><br>
                        Cada dominio empresarial mantiene sus propios objetos de datos, mientras Oracle conserva el control administrativo general de la instancia.
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- PARTE 10 - CREACION DE USUARIOS
-- ============================================================

ACCEPT PWD_RESIDUOS CHAR PROMPT \'Password ESPH_RESIDUOS: \' HIDE
ACCEPT PWD_ENERGIA  CHAR PROMPT \'Password ESPH_ENERGIA : \' HIDE
ACCEPT PWD_AGUA     CHAR PROMPT \'Password ESPH_AGUA    : \' HIDE
ACCEPT PWD_TIC      CHAR PROMPT \'Password ESPH_TIC     : \' HIDE

CREATE USER esph_residuos
IDENTIFIED BY "&PWD_RESIDUOS"
DEFAULT TABLESPACE APP_DATA
TEMPORARY TABLESPACE TEMP
QUOTA 200M ON APP_DATA
QUOTA 100M ON APP_INDEX;

CREATE USER esph_energia
IDENTIFIED BY "&PWD_ENERGIA"
DEFAULT TABLESPACE APP_DATA
TEMPORARY TABLESPACE TEMP
QUOTA 200M ON APP_DATA
QUOTA 100M ON APP_INDEX;

CREATE USER esph_agua
IDENTIFIED BY "&PWD_AGUA"
DEFAULT TABLESPACE APP_DATA
TEMPORARY TABLESPACE TEMP
QUOTA 200M ON APP_DATA
QUOTA 100M ON APP_INDEX;

CREATE USER esph_tic
IDENTIFIED BY "&PWD_TIC"
DEFAULT TABLESPACE APP_DATA
TEMPORARY TABLESPACE TEMP
QUOTA 200M ON APP_DATA
QUOTA 100M ON APP_INDEX;
'); ?>

                </div>
            </div>
        </div>


        <!-- 5. PRIVILEGIOS -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingPrivilegios">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapsePrivilegios"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-shield-halved me-2" style="color:var(--risk-mid)"></i>
                    5. Control de privilegios
                </button>
            </h2>
            <div id="collapsePrivilegios" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Qué se hizo?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        En lugar de otorgar privilegios administrativos generales, cada esquema recibió únicamente
                        privilegios explícitos para las operaciones necesarias dentro de Oracle.
                    </p>

                    <div class="table-responsive">
                        <table style="width:100%;border-collapse:collapse;font-size:0.83rem">
                            <thead>
                                <tr style="border-bottom:1px solid var(--border)">
                                    <th style="padding:0.6rem 0.75rem;text-align:left;color:var(--text-muted);font-weight:600">Privilegio</th>
                                    <th style="padding:0.6rem 0.75rem;text-align:left;color:var(--text-muted);font-weight:600">Propósito</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $privs = [
                                    ['CREATE SESSION',  'Permite al esquema establecer una sesión con Oracle.'],
                                    ['CREATE TABLE',    'Permite crear tablas propias del esquema.'],
                                    ['CREATE INDEX',    'Permite crear índices necesarios para optimizar consultas.'],
                                    ['CREATE VIEW',     'Permite definir vistas para representar información mediante consultas.'],
                                    ['CREATE SEQUENCE', 'Permite utilizar secuencias para generación controlada de identificadores.'],
                                    ['CREATE SYNONYM',  'Permite crear nombres alternativos para objetos cuando sea necesario.'],
                                ];
                                foreach ($privs as $priv):
                                ?>
                                <tr style="border-bottom:1px solid var(--border)">
                                    <td style="padding:0.6rem 0.75rem"><code><?php echo $priv[0]; ?></code></td>
                                    <td style="padding:0.6rem 0.75rem;color:var(--text-muted)"><?php echo $priv[1]; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <h5 style="font-size:0.95rem;margin:1rem 0 0.5rem 0">¿Por qué?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Esta configuración evita otorgar privilegios excesivos como <code>DBA</code> o permisos administrativos completos.
                        La intención es aplicar el principio de <strong style="color:var(--text)">mínimo privilegio</strong>.
                    </p>

                    <div style="background:rgba(255,200,50,0.07);border:1px solid rgba(255,200,50,0.25);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text)">
                        <strong style="color:var(--risk-mid)">Importante:</strong>
                        Los privilegios otorgados permiten administrar objetos propios del esquema, pero no convierten a estos usuarios en administradores de toda la base de datos.
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- PARTE 11 - PRIVILEGIOS
-- ============================================================

-- ESPH_RESIDUOS
GRANT CREATE SESSION TO esph_residuos;
GRANT CREATE TABLE TO esph_residuos;
GRANT CREATE INDEX TO esph_residuos;
GRANT CREATE VIEW TO esph_residuos;
GRANT CREATE SEQUENCE TO esph_residuos;
GRANT CREATE SYNONYM TO esph_residuos;

-- ESPH_ENERGIA
GRANT CREATE SESSION TO esph_energia;
GRANT CREATE TABLE TO esph_energia;
GRANT CREATE INDEX TO esph_energia;
GRANT CREATE VIEW TO esph_energia;
GRANT CREATE SEQUENCE TO esph_energia;
GRANT CREATE SYNONYM TO esph_energia;

-- ESPH_AGUA
GRANT CREATE SESSION TO esph_agua;
GRANT CREATE TABLE TO esph_agua;
GRANT CREATE INDEX TO esph_agua;
GRANT CREATE VIEW TO esph_agua;
GRANT CREATE SEQUENCE TO esph_agua;
GRANT CREATE SYNONYM TO esph_agua;

-- ESPH_TIC
GRANT CREATE SESSION TO esph_tic;
GRANT CREATE TABLE TO esph_tic;
GRANT CREATE INDEX TO esph_tic;
GRANT CREATE VIEW TO esph_tic;
GRANT CREATE SEQUENCE TO esph_tic;
GRANT CREATE SYNONYM TO esph_tic;
'); ?>

                </div>
            </div>
        </div>


        <!-- 6–9. MODELOS DE DATOS -->
        <?php
        $modelos = [
            [
                'id'    => 'Residuos',
                'num'   => '6',
                'icono' => 'fa-recycle',
                'titulo'=> 'Modelo de datos — Gestión de Residuos',
                'esquema'=> 'ESPH_RESIDUOS',
                'intro' => 'El esquema <code>ESPH_RESIDUOS</code> representa dos dominios: gestión de centros de acopio y operación de rutas de recolección.',
                'cols'  => [
                    ['Tablas principales',   ['centro_acopio','tipo_residuo','ingreso_residuo','ruta','vehiculo','ejecucion_ruta']],
                    ['Relaciones relevantes',['Centro → Ingresos','Tipo de residuo → Ingresos','Ruta → Ejecuciones','Vehículo → Ejecuciones']],
                ],
                'justificacion' => '¿Por qué se utilizaron claves foráneas?',
                'justificacion_desc' => 'Las claves foráneas impiden registrar relaciones hacia registros inexistentes. Por ejemplo, un ingreso de residuos debe asociarse con un centro de acopio y un tipo de residuo válidos. Esto protege la <strong>integridad referencial</strong> de los datos.',
                'alerta' => null,
                'sql' => '
-- ============================================================
-- PARTE 12 - ESPH_RESIDUOS
-- ============================================================

CREATE TABLE esph_residuos.centro_acopio (
    id_centro NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(100) NOT NULL,
    direccion VARCHAR2(200) NOT NULL,
    capacidad_ton NUMBER(8,2) NOT NULL,
    activo CHAR(1) DEFAULT \'S\' NOT NULL,
    fecha_registro DATE DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_centro_acopio PRIMARY KEY (id_centro) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT ck_ca_activo CHECK (activo IN (\'S\',\'N\'))
) TABLESPACE APP_DATA;

CREATE TABLE esph_residuos.tipo_residuo (
    id_tipo NUMBER GENERATED ALWAYS AS IDENTITY,
    descripcion VARCHAR2(100) NOT NULL,
    categoria VARCHAR2(50) NOT NULL,
    unidad_medida VARCHAR2(20) DEFAULT \'KG\' NOT NULL,
    CONSTRAINT pk_tipo_residuo PRIMARY KEY (id_tipo) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT ck_tr_cat CHECK (categoria IN (\'ORGANICO\',\'INORGANICO\',\'PELIGROSO\',\'ESPECIAL\'))
) TABLESPACE APP_DATA;

CREATE TABLE esph_residuos.ingreso_residuo (
    id_ingreso NUMBER GENERATED ALWAYS AS IDENTITY,
    id_centro NUMBER NOT NULL,
    id_tipo NUMBER NOT NULL,
    cantidad NUMBER(10,3) NOT NULL,
    fecha_ingreso DATE DEFAULT SYSDATE NOT NULL,
    origen VARCHAR2(150),
    observaciones VARCHAR2(500),
    CONSTRAINT pk_ingreso_residuo PRIMARY KEY (id_ingreso) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT fk_ir_centro FOREIGN KEY (id_centro) REFERENCES esph_residuos.centro_acopio(id_centro),
    CONSTRAINT fk_ir_tipo FOREIGN KEY (id_tipo) REFERENCES esph_residuos.tipo_residuo(id_tipo)
) TABLESPACE APP_DATA;

CREATE TABLE esph_residuos.ruta (
    id_ruta NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(100) NOT NULL,
    zona VARCHAR2(80) NOT NULL,
    dia_semana VARCHAR2(10) NOT NULL,
    hora_inicio VARCHAR2(5) NOT NULL,
    activa CHAR(1) DEFAULT \'S\' NOT NULL,
    CONSTRAINT pk_ruta PRIMARY KEY (id_ruta) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT ck_ruta_dia CHECK (dia_semana IN (\'LUNES\',\'MARTES\',\'MIERCOLES\',\'JUEVES\',\'VIERNES\',\'SABADO\',\'DOMINGO\')),
    CONSTRAINT ck_ruta_activa CHECK (activa IN (\'S\',\'N\'))
) TABLESPACE APP_DATA;

CREATE TABLE esph_residuos.vehiculo (
    id_vehiculo NUMBER GENERATED ALWAYS AS IDENTITY,
    placa VARCHAR2(10) NOT NULL,
    tipo VARCHAR2(50) NOT NULL,
    capacidad_ton NUMBER(6,2) NOT NULL,
    en_servicio CHAR(1) DEFAULT \'S\' NOT NULL,
    CONSTRAINT pk_vehiculo PRIMARY KEY (id_vehiculo) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT uk_vehiculo_placa UNIQUE (placa) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT ck_veh_servicio CHECK (en_servicio IN (\'S\',\'N\'))
) TABLESPACE APP_DATA;

CREATE TABLE esph_residuos.ejecucion_ruta (
    id_ejecucion NUMBER GENERATED ALWAYS AS IDENTITY,
    id_ruta NUMBER NOT NULL,
    id_vehiculo NUMBER NOT NULL,
    fecha DATE DEFAULT SYSDATE NOT NULL,
    kg_recolectados NUMBER(10,3),
    incidencias VARCHAR2(500),
    CONSTRAINT pk_ejecucion_ruta PRIMARY KEY (id_ejecucion) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT fk_er_ruta FOREIGN KEY (id_ruta) REFERENCES esph_residuos.ruta(id_ruta),
    CONSTRAINT fk_er_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES esph_residuos.vehiculo(id_vehiculo)
) TABLESPACE APP_DATA;

CREATE INDEX esph_residuos.idx_ir_centro ON esph_residuos.ingreso_residuo(id_centro) TABLESPACE APP_INDEX;
CREATE INDEX esph_residuos.idx_ir_fecha ON esph_residuos.ingreso_residuo(fecha_ingreso) TABLESPACE APP_INDEX;
CREATE INDEX esph_residuos.idx_er_ruta ON esph_residuos.ejecucion_ruta(id_ruta) TABLESPACE APP_INDEX;
CREATE INDEX esph_residuos.idx_er_fecha ON esph_residuos.ejecucion_ruta(fecha) TABLESPACE APP_INDEX;

CREATE SEQUENCE esph_residuos.seq_folio_ingreso START WITH 1000 INCREMENT BY 1 NOCACHE NOCYCLE;
',
            ],
            [
                'id'    => 'Energia',
                'num'   => '7',
                'icono' => 'fa-bolt',
                'titulo'=> 'Modelo de datos — Energía Eléctrica',
                'esquema'=> 'ESPH_ENERGIA',
                'intro' => 'El esquema <code>ESPH_ENERGIA</code> representa elementos relacionados con la distribución eléctrica y el alumbrado público.',
                'cols'  => [
                    ['Distribución eléctrica', ['subestacion','circuito','medidor','lectura_medidor']],
                    ['Alumbrado público',       ['luminaria','orden_mant_luminaria']],
                ],
                'justificacion' => 'Justificación',
                'justificacion_desc' => 'Las relaciones entre subestaciones, circuitos y medidores permiten representar la estructura jerárquica de la distribución eléctrica. De forma similar, las órdenes de mantenimiento se relacionan directamente con las luminarias.',
                'alerta' => 'Esta estructura evita almacenar repetidamente información de una subestación o circuito en cada registro de medición y permite mantener una relación normalizada entre las entidades.',
                'sql' => '
-- ============================================================
-- PARTE 13 - ESPH_ENERGIA
-- ============================================================

CREATE TABLE esph_energia.subestacion (
    id_subestacion NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(100) NOT NULL,
    ubicacion VARCHAR2(200) NOT NULL,
    voltaje_kv NUMBER(6,2) NOT NULL,
    capacidad_mva NUMBER(8,2) NOT NULL,
    operativa CHAR(1) DEFAULT \'S\' NOT NULL,
    CONSTRAINT pk_subestacion PRIMARY KEY (id_subestacion) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT ck_sub_op CHECK (operativa IN (\'S\',\'N\'))
) TABLESPACE APP_DATA;

CREATE TABLE esph_energia.circuito (
    id_circuito NUMBER GENERATED ALWAYS AS IDENTITY,
    id_subestacion NUMBER NOT NULL,
    nombre VARCHAR2(100) NOT NULL,
    zona_cobertura VARCHAR2(150) NOT NULL,
    clientes_aprox NUMBER(8) NOT NULL,
    CONSTRAINT pk_circuito PRIMARY KEY (id_circuito) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT fk_cir_sub FOREIGN KEY (id_subestacion) REFERENCES esph_energia.subestacion(id_subestacion)
) TABLESPACE APP_DATA;

CREATE TABLE esph_energia.medidor (
    id_medidor NUMBER GENERATED ALWAYS AS IDENTITY,
    id_circuito NUMBER NOT NULL,
    numero_serie VARCHAR2(30) NOT NULL,
    tipo VARCHAR2(30) NOT NULL,
    fecha_instalacion DATE DEFAULT SYSDATE NOT NULL,
    activo CHAR(1) DEFAULT \'S\' NOT NULL,
    CONSTRAINT pk_medidor PRIMARY KEY (id_medidor) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT uk_medidor_serie UNIQUE (numero_serie) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT ck_med_tipo CHECK (tipo IN (\'RESIDENCIAL\',\'COMERCIAL\',\'INDUSTRIAL\')),
    CONSTRAINT ck_med_activo CHECK (activo IN (\'S\',\'N\')),
    CONSTRAINT fk_med_cir FOREIGN KEY (id_circuito) REFERENCES esph_energia.circuito(id_circuito)
) TABLESPACE APP_DATA;

CREATE TABLE esph_energia.lectura_medidor (
    id_lectura NUMBER GENERATED ALWAYS AS IDENTITY,
    id_medidor NUMBER NOT NULL,
    fecha_lectura DATE DEFAULT SYSDATE NOT NULL,
    kwh_acumulado NUMBER(12,3) NOT NULL,
    kwh_consumo NUMBER(10,3) NOT NULL,
    lector VARCHAR2(80),
    CONSTRAINT pk_lectura_medidor PRIMARY KEY (id_lectura) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT fk_lec_med FOREIGN KEY (id_medidor) REFERENCES esph_energia.medidor(id_medidor)
) TABLESPACE APP_DATA;

CREATE TABLE esph_energia.luminaria (
    id_luminaria NUMBER GENERATED ALWAYS AS IDENTITY,
    codigo VARCHAR2(20) NOT NULL,
    ubicacion VARCHAR2(200) NOT NULL,
    tipo_lampara VARCHAR2(50) NOT NULL,
    potencia_w NUMBER(6,2) NOT NULL,
    en_operacion CHAR(1) DEFAULT \'S\' NOT NULL,
    fecha_instalacion DATE DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_luminaria PRIMARY KEY (id_luminaria) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT uk_luminaria_codigo UNIQUE (codigo) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT ck_lum_tipo CHECK (tipo_lampara IN (\'LED\',\'SODIO\',\'MERCURIO\',\'HALURO\')),
    CONSTRAINT ck_lum_op CHECK (en_operacion IN (\'S\',\'N\'))
) TABLESPACE APP_DATA;

CREATE TABLE esph_energia.orden_mant_luminaria (
    id_orden NUMBER GENERATED ALWAYS AS IDENTITY,
    id_luminaria NUMBER NOT NULL,
    tipo_trabajo VARCHAR2(80) NOT NULL,
    fecha_reporte DATE DEFAULT SYSDATE NOT NULL,
    fecha_atencion DATE,
    estado VARCHAR2(20) DEFAULT \'PENDIENTE\' NOT NULL,
    observaciones VARCHAR2(500),
    CONSTRAINT pk_orden_mant_lum PRIMARY KEY (id_orden) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT fk_oml_lum FOREIGN KEY (id_luminaria) REFERENCES esph_energia.luminaria(id_luminaria),
    CONSTRAINT ck_oml_estado CHECK (estado IN (\'PENDIENTE\',\'EN_PROCESO\',\'COMPLETADA\',\'CANCELADA\'))
) TABLESPACE APP_DATA;

CREATE INDEX esph_energia.idx_cir_sub ON esph_energia.circuito(id_subestacion) TABLESPACE APP_INDEX;
CREATE INDEX esph_energia.idx_med_cir ON esph_energia.medidor(id_circuito) TABLESPACE APP_INDEX;
CREATE INDEX esph_energia.idx_lec_med ON esph_energia.lectura_medidor(id_medidor) TABLESPACE APP_INDEX;
CREATE INDEX esph_energia.idx_lec_fecha ON esph_energia.lectura_medidor(fecha_lectura) TABLESPACE APP_INDEX;
CREATE INDEX esph_energia.idx_oml_lum ON esph_energia.orden_mant_luminaria(id_luminaria) TABLESPACE APP_INDEX;
CREATE INDEX esph_energia.idx_oml_estado ON esph_energia.orden_mant_luminaria(estado) TABLESPACE APP_INDEX;
',
            ],
            [
                'id'    => 'Agua',
                'num'   => '8',
                'icono' => 'fa-droplet',
                'titulo'=> 'Modelo de datos — Agua Potable',
                'esquema'=> 'ESPH_AGUA',
                'intro' => 'El esquema <code>ESPH_AGUA</code> representa infraestructura de potabilización, almacenamiento, distribución y control de hidrantes.',
                'cols'  => [
                    ['Infraestructura',      ['planta_potabilizadora','tanque','zona_distribucion']],
                    ['Operación y medición', ['conexion','lectura_agua','hidrante','inspeccion_hidrante']],
                ],
                'justificacion' => 'Justificación del modelo',
                'justificacion_desc' => 'Las entidades se separan para evitar duplicidad de información. Por ejemplo, una zona de distribución puede contener múltiples conexiones e hidrantes sin necesidad de repetir los datos de la zona en cada registro. Las inspecciones también se mantienen como registros históricos relacionados con el hidrante correspondiente.',
                'alerta' => null,
                'sql' => '
-- ============================================================
-- PARTE 14 - ESPH_AGUA
-- ============================================================

CREATE TABLE esph_agua.planta_potabilizadora (
    id_planta NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(100) NOT NULL,
    ubicacion VARCHAR2(200) NOT NULL,
    capacidad_lps NUMBER(8,2) NOT NULL,
    operativa CHAR(1) DEFAULT \'S\' NOT NULL,
    CONSTRAINT pk_planta_potabilizadora PRIMARY KEY (id_planta) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT ck_pp_op CHECK (operativa IN (\'S\',\'N\'))
) TABLESPACE APP_DATA;

CREATE TABLE esph_agua.tanque (
    id_tanque NUMBER GENERATED ALWAYS AS IDENTITY,
    id_planta NUMBER,
    nombre VARCHAR2(100) NOT NULL,
    capacidad_m3 NUMBER(10,2) NOT NULL,
    ubicacion VARCHAR2(200) NOT NULL,
    cota_msnm NUMBER(7,2) NOT NULL,
    CONSTRAINT pk_tanque PRIMARY KEY (id_tanque) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT fk_tan_planta FOREIGN KEY (id_planta) REFERENCES esph_agua.planta_potabilizadora(id_planta)
) TABLESPACE APP_DATA;

CREATE TABLE esph_agua.zona_distribucion (
    id_zona NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(100) NOT NULL,
    poblacion_est NUMBER(8) NOT NULL,
    conexiones_act NUMBER(8) NOT NULL,
    CONSTRAINT pk_zona_distribucion PRIMARY KEY (id_zona) USING INDEX TABLESPACE APP_INDEX
) TABLESPACE APP_DATA;

CREATE TABLE esph_agua.conexion (
    id_conexion NUMBER GENERATED ALWAYS AS IDENTITY,
    id_zona NUMBER NOT NULL,
    numero_medidor VARCHAR2(20) NOT NULL,
    tipo VARCHAR2(20) NOT NULL,
    diametro_mm NUMBER(5,1) NOT NULL,
    activa CHAR(1) DEFAULT \'S\' NOT NULL,
    fecha_alta DATE DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_conexion PRIMARY KEY (id_conexion) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT uk_conexion_medidor UNIQUE (numero_medidor) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT fk_con_zona FOREIGN KEY (id_zona) REFERENCES esph_agua.zona_distribucion(id_zona),
    CONSTRAINT ck_con_tipo CHECK (tipo IN (\'RESIDENCIAL\',\'COMERCIAL\',\'INDUSTRIAL\',\'MUNICIPAL\')),
    CONSTRAINT ck_con_activa CHECK (activa IN (\'S\',\'N\'))
) TABLESPACE APP_DATA;

CREATE TABLE esph_agua.lectura_agua (
    id_lectura NUMBER GENERATED ALWAYS AS IDENTITY,
    id_conexion NUMBER NOT NULL,
    fecha_lectura DATE DEFAULT SYSDATE NOT NULL,
    m3_acumulado NUMBER(12,3) NOT NULL,
    m3_consumo NUMBER(10,3) NOT NULL,
    lector VARCHAR2(80),
    CONSTRAINT pk_lectura_agua PRIMARY KEY (id_lectura) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT fk_la_con FOREIGN KEY (id_conexion) REFERENCES esph_agua.conexion(id_conexion)
) TABLESPACE APP_DATA;

CREATE TABLE esph_agua.hidrante (
    id_hidrante NUMBER GENERATED ALWAYS AS IDENTITY,
    id_zona NUMBER NOT NULL,
    codigo VARCHAR2(20) NOT NULL,
    ubicacion VARCHAR2(200) NOT NULL,
    tipo VARCHAR2(30) NOT NULL,
    presion_psi NUMBER(6,2) NOT NULL,
    operativo CHAR(1) DEFAULT \'S\' NOT NULL,
    ultima_inspeccion DATE,
    CONSTRAINT pk_hidrante PRIMARY KEY (id_hidrante) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT uk_hidrante_codigo UNIQUE (codigo) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT fk_hid_zona FOREIGN KEY (id_zona) REFERENCES esph_agua.zona_distribucion(id_zona),
    CONSTRAINT ck_hid_tipo CHECK (tipo IN (\'COLUMNA\',\'BAJO_NIVEL\',\'MURAL\')),
    CONSTRAINT ck_hid_op CHECK (operativo IN (\'S\',\'N\'))
) TABLESPACE APP_DATA;

CREATE TABLE esph_agua.inspeccion_hidrante (
    id_inspeccion NUMBER GENERATED ALWAYS AS IDENTITY,
    id_hidrante NUMBER NOT NULL,
    fecha DATE DEFAULT SYSDATE NOT NULL,
    presion_medida NUMBER(6,2) NOT NULL,
    caudal_lps NUMBER(6,2),
    resultado VARCHAR2(20) NOT NULL,
    inspector VARCHAR2(100),
    observaciones VARCHAR2(500),
    CONSTRAINT pk_inspeccion_hidrante PRIMARY KEY (id_inspeccion) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT fk_ih_hid FOREIGN KEY (id_hidrante) REFERENCES esph_agua.hidrante(id_hidrante),
    CONSTRAINT ck_ih_res CHECK (resultado IN (\'APROBADO\',\'REPARACION\',\'FUERA_SERVICIO\'))
) TABLESPACE APP_DATA;

CREATE INDEX esph_agua.idx_con_zona ON esph_agua.conexion(id_zona) TABLESPACE APP_INDEX;
CREATE INDEX esph_agua.idx_la_con ON esph_agua.lectura_agua(id_conexion) TABLESPACE APP_INDEX;
CREATE INDEX esph_agua.idx_la_fecha ON esph_agua.lectura_agua(fecha_lectura) TABLESPACE APP_INDEX;
CREATE INDEX esph_agua.idx_hid_zona ON esph_agua.hidrante(id_zona) TABLESPACE APP_INDEX;
CREATE INDEX esph_agua.idx_ih_hid ON esph_agua.inspeccion_hidrante(id_hidrante) TABLESPACE APP_INDEX;
CREATE INDEX esph_agua.idx_ih_fecha ON esph_agua.inspeccion_hidrante(fecha) TABLESPACE APP_INDEX;
',
            ],
            [
                'id'    => 'TIC',
                'num'   => '9',
                'icono' => 'fa-desktop',
                'titulo'=> 'Modelo de datos — Tecnologías de Información',
                'esquema'=> 'ESPH_TIC',
                'intro' => 'El esquema <code>ESPH_TIC</code> representa activos tecnológicos, licenciamiento, usuarios internos y atención de incidentes mediante tickets.',
                'cols'  => [
                    ['Gestión de activos', ['categoria_activo','activo_ti','licencia_software']],
                    ['Mesa de servicio',   ['categoria_ticket','usuario_interno','ticket']],
                ],
                'justificacion' => '¿Por qué se relacionan los tickets con activos?',
                'justificacion_desc' => 'Un incidente puede estar asociado con un activo tecnológico específico. La relación permite determinar qué equipo, servidor o componente de infraestructura está involucrado en una solicitud. También se relaciona cada ticket con el usuario que lo solicita y con su categoría correspondiente, permitiendo mantener trazabilidad de la atención.',
                'alerta' => null,
                'sql' => '
-- ============================================================
-- PARTE 15 - ESPH_TIC
-- ============================================================

CREATE TABLE esph_tic.categoria_activo (
    id_categoria NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(80) NOT NULL,
    descripcion VARCHAR2(200),
    CONSTRAINT pk_categoria_activo PRIMARY KEY (id_categoria) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT uk_categoria_activo UNIQUE (nombre) USING INDEX TABLESPACE APP_INDEX
) TABLESPACE APP_DATA;

CREATE TABLE esph_tic.activo_ti (
    id_activo NUMBER GENERATED ALWAYS AS IDENTITY,
    id_categoria NUMBER NOT NULL,
    codigo VARCHAR2(30) NOT NULL,
    nombre VARCHAR2(150) NOT NULL,
    marca VARCHAR2(80),
    modelo VARCHAR2(80),
    numero_serie VARCHAR2(80),
    ip_asignada VARCHAR2(15),
    ubicacion VARCHAR2(150) NOT NULL,
    estado VARCHAR2(20) DEFAULT \'ACTIVO\' NOT NULL,
    fecha_adquisicion DATE,
    garantia_hasta DATE,
    CONSTRAINT pk_activo_ti PRIMARY KEY (id_activo) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT uk_activo_codigo UNIQUE (codigo) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT fk_ati_cat FOREIGN KEY (id_categoria) REFERENCES esph_tic.categoria_activo(id_categoria),
    CONSTRAINT ck_ati_estado CHECK (estado IN (\'ACTIVO\',\'EN_MANTENIMIENTO\',\'DADO_DE_BAJA\',\'BODEGA\'))
) TABLESPACE APP_DATA;

CREATE TABLE esph_tic.licencia_software (
    id_licencia NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre_producto VARCHAR2(150) NOT NULL,
    proveedor VARCHAR2(100) NOT NULL,
    tipo_licencia VARCHAR2(50) NOT NULL,
    cantidad_usuarios NUMBER(6),
    fecha_vencimiento DATE,
    costo_anual NUMBER(12,2),
    observaciones VARCHAR2(300),
    CONSTRAINT pk_licencia_software PRIMARY KEY (id_licencia) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT ck_lic_tipo CHECK (tipo_licencia IN (\'PERPETUA\',\'SUSCRIPCION\',\'OPEN_SOURCE\',\'FREEWARE\'))
) TABLESPACE APP_DATA;

CREATE TABLE esph_tic.categoria_ticket (
    id_categoria NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(80) NOT NULL,
    nivel_sla_horas NUMBER(4) NOT NULL,
    CONSTRAINT pk_categoria_ticket PRIMARY KEY (id_categoria) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT uk_categoria_ticket UNIQUE (nombre) USING INDEX TABLESPACE APP_INDEX
) TABLESPACE APP_DATA;

CREATE TABLE esph_tic.usuario_interno (
    id_usuario NUMBER GENERATED ALWAYS AS IDENTITY,
    cedula VARCHAR2(12) NOT NULL,
    nombre VARCHAR2(150) NOT NULL,
    area VARCHAR2(100) NOT NULL,
    correo VARCHAR2(150) NOT NULL,
    activo CHAR(1) DEFAULT \'S\' NOT NULL,
    CONSTRAINT pk_usuario_interno PRIMARY KEY (id_usuario) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT uk_usuario_cedula UNIQUE (cedula) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT uk_usuario_correo UNIQUE (correo) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT ck_ui_activo CHECK (activo IN (\'S\',\'N\'))
) TABLESPACE APP_DATA;

CREATE TABLE esph_tic.ticket (
    id_ticket NUMBER GENERATED ALWAYS AS IDENTITY,
    id_categoria NUMBER NOT NULL,
    id_solicitante NUMBER NOT NULL,
    id_activo NUMBER,
    asunto VARCHAR2(200) NOT NULL,
    descripcion VARCHAR2(1000) NOT NULL,
    prioridad VARCHAR2(10) DEFAULT \'MEDIA\' NOT NULL,
    estado VARCHAR2(20) DEFAULT \'ABIERTO\' NOT NULL,
    fecha_apertura DATE DEFAULT SYSDATE NOT NULL,
    fecha_cierre DATE,
    tecnico_asig VARCHAR2(100),
    resolucion VARCHAR2(1000),
    CONSTRAINT pk_ticket PRIMARY KEY (id_ticket) USING INDEX TABLESPACE APP_INDEX,
    CONSTRAINT fk_tkt_cat FOREIGN KEY (id_categoria) REFERENCES esph_tic.categoria_ticket(id_categoria),
    CONSTRAINT fk_tkt_sol FOREIGN KEY (id_solicitante) REFERENCES esph_tic.usuario_interno(id_usuario),
    CONSTRAINT fk_tkt_ati FOREIGN KEY (id_activo) REFERENCES esph_tic.activo_ti(id_activo),
    CONSTRAINT ck_tkt_pri CHECK (prioridad IN (\'BAJA\',\'MEDIA\',\'ALTA\',\'CRITICA\')),
    CONSTRAINT ck_tkt_est CHECK (estado IN (\'ABIERTO\',\'EN_PROCESO\',\'RESUELTO\',\'CERRADO\',\'CANCELADO\'))
) TABLESPACE APP_DATA;

CREATE INDEX esph_tic.idx_ati_cat ON esph_tic.activo_ti(id_categoria) TABLESPACE APP_INDEX;
CREATE INDEX esph_tic.idx_ati_estado ON esph_tic.activo_ti(estado) TABLESPACE APP_INDEX;
CREATE INDEX esph_tic.idx_tkt_cat ON esph_tic.ticket(id_categoria) TABLESPACE APP_INDEX;
CREATE INDEX esph_tic.idx_tkt_sol ON esph_tic.ticket(id_solicitante) TABLESPACE APP_INDEX;
CREATE INDEX esph_tic.idx_tkt_estado ON esph_tic.ticket(estado) TABLESPACE APP_INDEX;
CREATE INDEX esph_tic.idx_tkt_fecha ON esph_tic.ticket(fecha_apertura) TABLESPACE APP_INDEX;
',
            ],
        ];
        foreach ($modelos as $m):
        ?>
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="heading<?php echo $m['id']; ?>">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse<?php echo $m['id']; ?>"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid <?php echo $m['icono']; ?> me-2" style="color:var(--risk-mid)"></i>
                    <?php echo $m['num']; ?>. <?php echo $m['titulo']; ?>
                </button>
            </h2>
            <div id="collapse<?php echo $m['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <p style="font-size:0.88rem;color:var(--text-muted)"><?php echo $m['intro']; ?></p>

                    <div class="row g-3 mb-3">
                        <?php foreach ($m['cols'] as $col): ?>
                        <div class="col-md-6">
                            <h6 style="font-size:0.82rem;font-family:var(--font-mono);color:var(--risk-mid);margin-bottom:0.5rem"><?php echo $col[0]; ?></h6>
                            <ul style="font-size:0.82rem;color:var(--text-muted);padding-left:1.25rem;margin:0">
                                <?php foreach ($col[1] as $item): ?>
                                <li><code style="font-size:0.78rem"><?php echo $item; ?></code></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="border-top:1px solid var(--border);margin:1rem 0"></div>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem"><?php echo $m['justificacion']; ?></h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)"><?php echo $m['justificacion_desc']; ?></p>

                    <?php if ($m['alerta']): ?>
                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text)">
                        <?php echo $m['alerta']; ?>
                    </div>
                    <?php endif; ?>

                    <?php echo renderSqlBlock($m['sql']); ?>

                </div>
            </div>
        </div>
        <?php endforeach; ?>


        <!-- 10. INTEGRIDAD -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingIntegridad">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseIntegridad"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-shield-check me-2" style="color:var(--risk-mid)"></i>
                    10. Integridad y validación de los datos
                </button>
            </h2>
            <div id="collapseIntegridad" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Qué se hizo?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        El modelo utiliza diferentes mecanismos nativos de Oracle para restringir los valores que pueden almacenarse.
                    </p>

                    <div class="table-responsive">
                        <table style="width:100%;border-collapse:collapse;font-size:0.83rem">
                            <thead>
                                <tr style="border-bottom:1px solid var(--border)">
                                    <th style="padding:0.6rem 0.75rem;color:var(--text-muted);font-weight:600;text-align:left">Mecanismo</th>
                                    <th style="padding:0.6rem 0.75rem;color:var(--text-muted);font-weight:600;text-align:left">Uso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $mecanismos = [
                                    ['PRIMARY KEY', 'Identifica de forma única cada registro.'],
                                    ['FOREIGN KEY', 'Mantiene relaciones válidas entre tablas.'],
                                    ['UNIQUE',      'Evita duplicidad en valores que deben ser únicos.'],
                                    ['NOT NULL',    'Obliga a proporcionar información requerida.'],
                                    ['CHECK',       'Restringe valores a dominios válidos.'],
                                    ['IDENTITY',    'Genera automáticamente identificadores.'],
                                ];
                                foreach ($mecanismos as $mec):
                                ?>
                                <tr style="border-bottom:1px solid var(--border)">
                                    <td style="padding:0.6rem 0.75rem"><code><?php echo $mec[0]; ?></code></td>
                                    <td style="padding:0.6rem 0.75rem;color:var(--text-muted)"><?php echo $mec[1]; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <h5 style="font-size:0.95rem;margin:1rem 0 0.5rem 0">¿Por qué?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        La validación directamente en el modelo de datos proporciona una segunda barrera de protección frente a información inconsistente.
                    </p>

                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text)">
                        <strong style="color:var(--risk-mid)">Ejemplo:</strong><br>
                        La tabla <code>vehiculo</code> utiliza una restricción <code>UNIQUE</code> sobre la placa.
                        Por lo tanto, Oracle impide registrar dos vehículos con la misma placa.
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- EJEMPLOS DE CONSTRAINTS REPRESENTATIVOS
-- ============================================================

-- PRIMARY KEY con USING INDEX TABLESPACE APP_INDEX
CONSTRAINT pk_centro_acopio
    PRIMARY KEY (id_centro)
    USING INDEX TABLESPACE APP_INDEX

-- FOREIGN KEY (relación)
CONSTRAINT fk_ir_centro
    FOREIGN KEY (id_centro)
    REFERENCES esph_residuos.centro_acopio(id_centro)

-- UNIQUE (evita duplicidad)
CONSTRAINT uk_vehiculo_placa
    UNIQUE (placa)
    USING INDEX TABLESPACE APP_INDEX

-- CHECK (valores permitidos)
CONSTRAINT ck_med_tipo
    CHECK (tipo IN (\'RESIDENCIAL\',\'COMERCIAL\',\'INDUSTRIAL\'))

-- GENERATED ALWAYS AS IDENTITY
id_centro NUMBER GENERATED ALWAYS AS IDENTITY
'); ?>

                </div>
            </div>
        </div>


        <!-- 11. ÍNDICES -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingIndices">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseIndices"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-gauge-high me-2" style="color:var(--risk-mid)"></i>
                    11. Optimización mediante índices
                </button>
            </h2>
            <div id="collapseIndices" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Además de los índices generados por las claves primarias y restricciones <code>UNIQUE</code>,
                        se crearon índices adicionales sobre columnas utilizadas frecuentemente para búsquedas y relaciones.
                    </p>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">Ejemplos</h5>
                    <ul style="font-size:0.85rem;color:var(--text-muted);padding-left:1.25rem">
                        <li><code>idx_ir_centro</code> → búsquedas de ingresos por centro de acopio.</li>
                        <li><code>idx_ir_fecha</code> → consultas de ingresos por fecha.</li>
                        <li><code>idx_lec_med</code> → búsquedas de lecturas por medidor.</li>
                        <li><code>idx_lec_fecha</code> → consultas históricas por fecha.</li>
                        <li><code>idx_tkt_estado</code> → filtrado de tickets según estado.</li>
                        <li><code>idx_tkt_fecha</code> → consultas de tickets por fecha.</li>
                    </ul>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Por qué?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Los índices permiten que Oracle encuentre registros mediante estructuras auxiliares sin tener que recorrer necesariamente toda la tabla.
                    </p>

                    <div style="background:rgba(255,200,50,0.07);border:1px solid rgba(255,200,50,0.25);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text)">
                        <strong style="color:var(--risk-mid)">Decisión:</strong>
                        No se indexaron indiscriminadamente todas las columnas. Se seleccionaron principalmente columnas utilizadas en relaciones, búsquedas, filtros y consultas temporales.
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- ÍNDICES DE RENDIMIENTO POR ESQUEMA
-- ============================================================

-- ESPH_RESIDUOS
CREATE INDEX esph_residuos.idx_ir_centro ON esph_residuos.ingreso_residuo(id_centro) TABLESPACE APP_INDEX;
CREATE INDEX esph_residuos.idx_ir_fecha ON esph_residuos.ingreso_residuo(fecha_ingreso) TABLESPACE APP_INDEX;
CREATE INDEX esph_residuos.idx_er_ruta ON esph_residuos.ejecucion_ruta(id_ruta) TABLESPACE APP_INDEX;
CREATE INDEX esph_residuos.idx_er_fecha ON esph_residuos.ejecucion_ruta(fecha) TABLESPACE APP_INDEX;

-- ESPH_ENERGIA
CREATE INDEX esph_energia.idx_cir_sub ON esph_energia.circuito(id_subestacion) TABLESPACE APP_INDEX;
CREATE INDEX esph_energia.idx_med_cir ON esph_energia.medidor(id_circuito) TABLESPACE APP_INDEX;
CREATE INDEX esph_energia.idx_lec_med ON esph_energia.lectura_medidor(id_medidor) TABLESPACE APP_INDEX;
CREATE INDEX esph_energia.idx_lec_fecha ON esph_energia.lectura_medidor(fecha_lectura) TABLESPACE APP_INDEX;
CREATE INDEX esph_energia.idx_oml_lum ON esph_energia.orden_mant_luminaria(id_luminaria) TABLESPACE APP_INDEX;
CREATE INDEX esph_energia.idx_oml_estado ON esph_energia.orden_mant_luminaria(estado) TABLESPACE APP_INDEX;

-- ESPH_AGUA
CREATE INDEX esph_agua.idx_con_zona ON esph_agua.conexion(id_zona) TABLESPACE APP_INDEX;
CREATE INDEX esph_agua.idx_la_con ON esph_agua.lectura_agua(id_conexion) TABLESPACE APP_INDEX;
CREATE INDEX esph_agua.idx_la_fecha ON esph_agua.lectura_agua(fecha_lectura) TABLESPACE APP_INDEX;
CREATE INDEX esph_agua.idx_hid_zona ON esph_agua.hidrante(id_zona) TABLESPACE APP_INDEX;
CREATE INDEX esph_agua.idx_ih_hid ON esph_agua.inspeccion_hidrante(id_hidrante) TABLESPACE APP_INDEX;
CREATE INDEX esph_agua.idx_ih_fecha ON esph_agua.inspeccion_hidrante(fecha) TABLESPACE APP_INDEX;

-- ESPH_TIC
CREATE INDEX esph_tic.idx_ati_cat ON esph_tic.activo_ti(id_categoria) TABLESPACE APP_INDEX;
CREATE INDEX esph_tic.idx_ati_estado ON esph_tic.activo_ti(estado) TABLESPACE APP_INDEX;
CREATE INDEX esph_tic.idx_tkt_cat ON esph_tic.ticket(id_categoria) TABLESPACE APP_INDEX;
CREATE INDEX esph_tic.idx_tkt_sol ON esph_tic.ticket(id_solicitante) TABLESPACE APP_INDEX;
CREATE INDEX esph_tic.idx_tkt_estado ON esph_tic.ticket(estado) TABLESPACE APP_INDEX;
CREATE INDEX esph_tic.idx_tkt_fecha ON esph_tic.ticket(fecha_apertura) TABLESPACE APP_INDEX;
'); ?>

                </div>
            </div>
        </div>


        <!-- 12. DATOS DE PRUEBA -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingDatos">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseDatos"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-database me-2" style="color:var(--risk-mid)"></i>
                    12. Carga de datos de prueba
                </button>
            </h2>
            <div id="collapseDatos" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Qué se hizo?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">Después de crear la estructura se introdujeron datos de prueba representativos de las diferentes áreas empresariales. Por ejemplo:</p>
                    <ul style="font-size:0.85rem;color:var(--text-muted);padding-left:1.25rem">
                        <li>Centros de acopio.</li>
                        <li>Tipos de residuos.</li>
                        <li>Vehículos y rutas.</li>
                        <li>Subestaciones y circuitos.</li>
                        <li>Luminarias.</li>
                        <li>Plantas potabilizadoras.</li>
                        <li>Zonas de distribución.</li>
                        <li>Hidrantes.</li>
                        <li>Activos tecnológicos.</li>
                        <li>Usuarios internos.</li>
                        <li>Categorías y tickets.</li>
                    </ul>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Por qué?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Los datos de prueba permiten verificar que las tablas, relaciones, restricciones e índices funcionen correctamente antes de utilizar información real.
                    </p>

                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text)">
                        <strong style="color:var(--risk-mid)">Importante:</strong>
                        Los datos incluidos en el script son datos demostrativos. No deben interpretarse como información real de ESPH S.A.
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- DATOS DE PRUEBA - ESPH_RESIDUOS
-- ============================================================

INSERT INTO esph_residuos.tipo_residuo (descripcion, categoria)
VALUES (\'Residuos organicos domiciliarios\', \'ORGANICO\');

INSERT INTO esph_residuos.tipo_residuo (descripcion, categoria)
VALUES (\'Plastico PET\', \'INORGANICO\');

INSERT INTO esph_residuos.tipo_residuo (descripcion, categoria)
VALUES (\'Carton y papel\', \'INORGANICO\');

INSERT INTO esph_residuos.tipo_residuo (descripcion, categoria)
VALUES (\'Aceites y lubricantes usados\', \'PELIGROSO\');

INSERT INTO esph_residuos.centro_acopio (nombre, direccion, capacidad_ton)
VALUES (\'Centro Acopio Heredia Norte\', \'Barrio Corazon de Jesus, Heredia\', 150);

INSERT INTO esph_residuos.centro_acopio (nombre, direccion, capacidad_ton)
VALUES (\'Centro Acopio Heredia Sur\', \'Barrio Los Angeles, Heredia\', 200);

INSERT INTO esph_residuos.vehiculo (placa, tipo, capacidad_ton)
VALUES (\'HRD-001\', \'Compactador\', 8);

INSERT INTO esph_residuos.vehiculo (placa, tipo, capacidad_ton)
VALUES (\'HRD-002\', \'Volteo\', 12);

INSERT INTO esph_residuos.ruta (nombre, zona, dia_semana, hora_inicio)
VALUES (\'Ruta Norte A\', \'Heredia Centro\', \'LUNES\', \'06:00\');

INSERT INTO esph_residuos.ruta (nombre, zona, dia_semana, hora_inicio)
VALUES (\'Ruta Sur B\', \'Heredia Sur\', \'MIERCOLES\', \'07:00\');

-- ============================================================
-- DATOS DE PRUEBA - ESPH_ENERGIA
-- ============================================================

INSERT INTO esph_energia.subestacion (nombre, ubicacion, voltaje_kv, capacidad_mva)
VALUES (\'Subestacion Heredia\', \'Heredia Centro\', 34.5, 20);

INSERT INTO esph_energia.subestacion (nombre, ubicacion, voltaje_kv, capacidad_mva)
VALUES (\'Subestacion Mercedes\', \'Mercedes, Heredia\', 34.5, 15);

INSERT INTO esph_energia.circuito (id_subestacion, nombre, zona_cobertura, clientes_aprox)
SELECT id_subestacion, \'Circuito H-01\', \'Heredia Centro\', 4500
FROM esph_energia.subestacion WHERE nombre = \'Subestacion Heredia\';

INSERT INTO esph_energia.circuito (id_subestacion, nombre, zona_cobertura, clientes_aprox)
SELECT id_subestacion, \'Circuito H-02\', \'Barrio Fatima\', 2800
FROM esph_energia.subestacion WHERE nombre = \'Subestacion Heredia\';

INSERT INTO esph_energia.circuito (id_subestacion, nombre, zona_cobertura, clientes_aprox)
SELECT id_subestacion, \'Circuito M-01\', \'Mercedes Norte\', 3200
FROM esph_energia.subestacion WHERE nombre = \'Subestacion Mercedes\';

INSERT INTO esph_energia.luminaria (codigo, ubicacion, tipo_lampara, potencia_w)
VALUES (\'LUM-0001\', \'Av. Central, Heredia\', \'LED\', 150);

INSERT INTO esph_energia.luminaria (codigo, ubicacion, tipo_lampara, potencia_w)
VALUES (\'LUM-0002\', \'Calle 4, Barrio Fatima\', \'LED\', 100);

INSERT INTO esph_energia.luminaria (codigo, ubicacion, tipo_lampara, potencia_w)
VALUES (\'LUM-0003\', \'Ruta 3, Mercedes\', \'SODIO\', 250);

-- ============================================================
-- DATOS DE PRUEBA - ESPH_AGUA
-- ============================================================

INSERT INTO esph_agua.planta_potabilizadora (nombre, ubicacion, capacidad_lps)
VALUES (\'Planta La Ribera\', \'La Ribera, Belen\', 120);

INSERT INTO esph_agua.planta_potabilizadora (nombre, ubicacion, capacidad_lps)
VALUES (\'Planta El Cedral\', \'El Cedral, Heredia\', 80);

INSERT INTO esph_agua.zona_distribucion (nombre, poblacion_est, conexiones_act)
VALUES (\'Zona Heredia Centro\', 45000, 12500);

INSERT INTO esph_agua.zona_distribucion (nombre, poblacion_est, conexiones_act)
VALUES (\'Zona Mercedes\', 28000, 7800);

INSERT INTO esph_agua.zona_distribucion (nombre, poblacion_est, conexiones_act)
VALUES (\'Zona Santo Domingo\', 31000, 8900);

INSERT INTO esph_agua.tanque (id_planta, nombre, capacidad_m3, ubicacion, cota_msnm)
SELECT id_planta, \'Tanque Alto Heredia\', 3000, \'Alto de Heredia\', 1200
FROM esph_agua.planta_potabilizadora WHERE nombre = \'Planta La Ribera\';

INSERT INTO esph_agua.tanque (id_planta, nombre, capacidad_m3, ubicacion, cota_msnm)
SELECT id_planta, \'Tanque El Cedral\', 1500, \'El Cedral\', 1050
FROM esph_agua.planta_potabilizadora WHERE nombre = \'Planta El Cedral\';

INSERT INTO esph_agua.hidrante (id_zona, codigo, ubicacion, tipo, presion_psi)
SELECT id_zona, \'HID-001\', \'Frente Mercado Central, Heredia\', \'COLUMNA\', 65
FROM esph_agua.zona_distribucion WHERE nombre = \'Zona Heredia Centro\';

INSERT INTO esph_agua.hidrante (id_zona, codigo, ubicacion, tipo, presion_psi)
SELECT id_zona, \'HID-002\', \'Av. 4, Heredia Centro\', \'BAJO_NIVEL\', 60
FROM esph_agua.zona_distribucion WHERE nombre = \'Zona Heredia Centro\';

INSERT INTO esph_agua.hidrante (id_zona, codigo, ubicacion, tipo, presion_psi)
SELECT id_zona, \'HID-003\', \'Mercedes Sur, Heredia\', \'COLUMNA\', 58
FROM esph_agua.zona_distribucion WHERE nombre = \'Zona Mercedes\';

-- ============================================================
-- DATOS DE PRUEBA - ESPH_TIC
-- ============================================================

INSERT INTO esph_tic.categoria_activo (nombre, descripcion)
VALUES (\'Servidor\', \'Servidores fisicos y virtuales\');

INSERT INTO esph_tic.categoria_activo (nombre, descripcion)
VALUES (\'Computadora\', \'Equipos de escritorio y laptops\');

INSERT INTO esph_tic.categoria_activo (nombre, descripcion)
VALUES (\'Red\', \'Switches, routers, access points\');

INSERT INTO esph_tic.categoria_activo (nombre, descripcion)
VALUES (\'Impresora\', \'Impresoras y multifuncionales\');

INSERT INTO esph_tic.categoria_ticket (nombre, nivel_sla_horas)
VALUES (\'Falla de hardware\', 4);

INSERT INTO esph_tic.categoria_ticket (nombre, nivel_sla_horas)
VALUES (\'Problema de software\', 8);

INSERT INTO esph_tic.categoria_ticket (nombre, nivel_sla_horas)
VALUES (\'Accesos y permisos\', 24);

INSERT INTO esph_tic.categoria_ticket (nombre, nivel_sla_horas)
VALUES (\'Conectividad de red\', 2);

INSERT INTO esph_tic.categoria_ticket (nombre, nivel_sla_horas)
VALUES (\'Solicitud de servicio\', 48);

INSERT INTO esph_tic.usuario_interno (cedula, nombre, area, correo)
VALUES (\'101110001\', \'Ana Vargas Mora\', \'Gestion Financiera\', \'avargas@esph.cr\');

INSERT INTO esph_tic.usuario_interno (cedula, nombre, area, correo)
VALUES (\'201220002\', \'Luis Brenes Soto\', \'Negocio de Residuos\', \'lbrenes@esph.cr\');

INSERT INTO esph_tic.usuario_interno (cedula, nombre, area, correo)
VALUES (\'301330003\', \'Maria Quesada Ruiz\', \'Negocio Agua Potable\', \'mquesada@esph.cr\');

INSERT INTO esph_tic.activo_ti (id_categoria, codigo, nombre, marca, modelo, ip_asignada, ubicacion, fecha_adquisicion)
SELECT id_categoria, \'SRV-001\', \'Servidor de Aplicaciones Principal\', \'Dell\', \'PowerEdge R750\', \'192.168.1.10\', \'Data Center ESPH\', DATE \'2022-03-15\'
FROM esph_tic.categoria_activo WHERE nombre = \'Servidor\';

INSERT INTO esph_tic.activo_ti (id_categoria, codigo, nombre, marca, modelo, ip_asignada, ubicacion, fecha_adquisicion)
SELECT id_categoria, \'NET-001\', \'Switch Core\', \'Cisco\', \'Catalyst 9300\', \'192.168.1.1\', \'Data Center ESPH\', DATE \'2021-07-20\'
FROM esph_tic.categoria_activo WHERE nombre = \'Red\';

COMMIT;
'); ?>

                </div>
            </div>
        </div>


        <!-- 13. VERIFICACIÓN -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingVerificacion">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseVerificacion"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-clipboard-check me-2" style="color:var(--risk-mid)"></i>
                    13. Verificación posterior a la implementación
                </button>
            </h2>
            <div id="collapseVerificacion" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <p style="font-size:0.88rem;color:var(--text-muted)">El script no se limita a crear los objetos. También realiza consultas de verificación después de la instalación.</p>

                    <div class="row g-3 mb-3">
                        <?php
                        $verificaciones = [
                            ['fa-user-check','Usuarios',   'Se comprueba el estado de las cuentas, tablespaces y fecha de creación.'],
                            ['fa-key',       'Privilegios','Se consultan los privilegios otorgados a cada esquema.'],
                            ['fa-table',     'Tablas',     'Se verifica la cantidad de tablas existentes por área de negocio.'],
                            ['fa-chart-bar', 'Espacio',    'Se revisa el espacio utilizado y disponible en APP_DATA y APP_INDEX.'],
                        ];
                        foreach ($verificaciones as $v):
                        ?>
                        <div class="col-md-6">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:8px;padding:1rem;height:100%">
                                <h6 style="font-size:0.82rem;margin-bottom:0.35rem">
                                    <i class="fa-solid <?php echo $v[0]; ?> me-2" style="color:var(--risk-mid)"></i>
                                    <?php echo $v[1]; ?>
                                </h6>
                                <p style="font-size:0.8rem;color:var(--text-muted);margin:0"><?php echo $v[2]; ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="border-top:1px solid var(--border);margin:1rem 0"></div>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Por qué verificar después de crear?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Porque una ejecución exitosa del script no garantiza por sí sola que todos los objetos hayan quedado configurados
                        como se esperaba. Las consultas finales permiten comprobar el estado real de Oracle.
                    </p>

                    <?php echo renderSqlBlock('
-- ============================================================
-- PARTE 16 - VERIFICACION DE USUARIOS
-- ============================================================

SELECT
    username,
    account_status,
    default_tablespace,
    temporary_tablespace,
    created
FROM dba_users
WHERE username IN (\'ESPH_RESIDUOS\', \'ESPH_ENERGIA\', \'ESPH_AGUA\', \'ESPH_TIC\')
ORDER BY username;

-- ============================================================
-- PARTE 17 - CUOTAS
-- ============================================================

SELECT
    username,
    tablespace_name,
    CASE
        WHEN max_bytes = -1 THEN \'UNLIMITED\'
        ELSE TO_CHAR(ROUND(max_bytes / 1024 / 1024, 2))
    END AS quota_mb
FROM dba_ts_quotas
WHERE username IN (\'ESPH_RESIDUOS\', \'ESPH_ENERGIA\', \'ESPH_AGUA\', \'ESPH_TIC\')
ORDER BY username, tablespace_name;

-- ============================================================
-- PARTE 18 - PRIVILEGIOS
-- ============================================================

SELECT
    grantee,
    privilege
FROM dba_sys_privs
WHERE grantee IN (\'ESPH_RESIDUOS\', \'ESPH_ENERGIA\', \'ESPH_AGUA\', \'ESPH_TIC\')
ORDER BY grantee, privilege;
'); ?>

                </div>
            </div>
        </div>


        <!-- 14. RESUMEN FINAL -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingResumen">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseResumen"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-sitemap me-2" style="color:var(--risk-mid)"></i>
                    14. Resultado final de la arquitectura
                </button>
            </h2>
            <div id="collapseResumen" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <h5 style="font-size:0.95rem;text-align:center;margin-bottom:1.25rem">Arquitectura de almacenamiento</h5>

                    <div class="row text-center g-3 mb-3">
                        <?php
                        $arq = [
                            ['Oracle','Motor de base de datos'],
                            ['APP_DATA','Datos empresariales'],
                            ['APP_INDEX','Índices'],
                        ];
                        foreach ($arq as $a):
                        ?>
                        <div class="col-md-4">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:8px;padding:1rem">
                                <strong style="font-family:var(--font-mono);font-size:0.85rem;color:var(--risk-mid)"><?php echo $a[0]; ?></strong><br>
                                <span style="font-size:0.78rem;color:var(--text-muted)"><?php echo $a[1]; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="border-top:1px solid var(--border);margin:1rem 0"></div>

                    <div class="row text-center g-3 mb-4">
                        <?php
                        $esquemas_res = [
                            ['ESPH_RESIDUOS','Residuos'],
                            ['ESPH_ENERGIA','Energía'],
                            ['ESPH_AGUA','Agua'],
                            ['ESPH_TIC','Tecnologías de Información'],
                        ];
                        foreach ($esquemas_res as $er):
                        ?>
                        <div class="col-md-3">
                            <div style="background:rgba(242,177,52,0.06);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem">
                                <strong style="font-family:var(--font-mono);font-size:0.75rem;color:var(--risk-mid)"><?php echo $er[0]; ?></strong><br>
                                <small style="color:var(--text-muted)"><?php echo $er[1]; ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:1rem 1.25rem;font-size:0.88rem;color:var(--text)">
                        <strong style="color:var(--risk-mid)">En resumen:</strong>
                        La base de datos fue estructurada para separar la información empresarial por dominios, controlar el almacenamiento,
                        restringir los privilegios, mantener la integridad referencial, optimizar las consultas y facilitar la administración
                        futura del entorno Oracle.
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- PARTE 19 - TABLAS POR AREA DE NEGOCIO
-- ============================================================

SELECT
    owner,
    COUNT(*) AS cantidad_tablas
FROM dba_tables
WHERE owner IN (\'ESPH_RESIDUOS\', \'ESPH_ENERGIA\', \'ESPH_AGUA\', \'ESPH_TIC\')
GROUP BY owner
ORDER BY owner;

-- ============================================================
-- PARTE 20 - TABLAS EN APP_DATA
-- ============================================================

SELECT
    owner,
    table_name,
    tablespace_name
FROM dba_tables
WHERE owner IN (\'ESPH_RESIDUOS\', \'ESPH_ENERGIA\', \'ESPH_AGUA\', \'ESPH_TIC\')
ORDER BY owner, table_name;

-- ============================================================
-- PARTE 21 - INDICES EN APP_INDEX
-- ============================================================

SELECT
    owner,
    index_name,
    table_name,
    index_type,
    uniqueness,
    tablespace_name
FROM dba_indexes
WHERE owner IN (\'ESPH_RESIDUOS\', \'ESPH_ENERGIA\', \'ESPH_AGUA\', \'ESPH_TIC\')
ORDER BY owner, table_name, index_name;

-- ============================================================
-- PARTE 22 - SEGMENTOS
-- ============================================================

SELECT
    owner,
    segment_name,
    segment_type,
    tablespace_name,
    ROUND(bytes / 1024 / 1024, 2) AS size_mb
FROM dba_segments
WHERE owner IN (\'ESPH_RESIDUOS\', \'ESPH_ENERGIA\', \'ESPH_AGUA\', \'ESPH_TIC\')
ORDER BY owner, tablespace_name, segment_type, segment_name;

-- ============================================================
-- PARTE 23 - SEGMENTOS POR TABLESPACE
-- ============================================================

SELECT
    tablespace_name,
    segment_type,
    COUNT(*) AS cantidad_segmentos,
    ROUND(SUM(bytes) / 1024 / 1024, 2) AS total_mb
FROM dba_segments
WHERE tablespace_name IN (\'APP_DATA\', \'APP_INDEX\')
GROUP BY tablespace_name, segment_type
ORDER BY tablespace_name, segment_type;

-- ============================================================
-- PARTE 24 - EXTENTS
-- ============================================================

SELECT
    owner,
    segment_name,
    segment_type,
    tablespace_name,
    COUNT(*) AS cantidad_extents,
    ROUND(SUM(bytes) / 1024 / 1024, 2) AS total_mb
FROM dba_extents
WHERE owner IN (\'ESPH_RESIDUOS\', \'ESPH_ENERGIA\', \'ESPH_AGUA\', \'ESPH_TIC\')
GROUP BY owner, segment_name, segment_type, tablespace_name
ORDER BY owner, tablespace_name, segment_name;

-- ============================================================
-- PARTE 25 - ESPACIO UTILIZADO
-- ============================================================

SELECT
    df.tablespace_name,
    ROUND(df.total_mb, 2) AS total_mb,
    ROUND(df.total_mb - NVL(fs.free_mb, 0), 2) AS used_mb,
    ROUND(NVL(fs.free_mb, 0), 2) AS free_mb,
    ROUND(((df.total_mb - NVL(fs.free_mb, 0)) / df.total_mb) * 100, 2) AS pct_used
FROM
(
    SELECT tablespace_name, SUM(bytes) / 1024 / 1024 AS total_mb
    FROM dba_data_files
    WHERE tablespace_name IN (\'APP_DATA\', \'APP_INDEX\')
    GROUP BY tablespace_name
) df
LEFT JOIN
(
    SELECT tablespace_name, SUM(bytes) / 1024 / 1024 AS free_mb
    FROM dba_free_space
    WHERE tablespace_name IN (\'APP_DATA\', \'APP_INDEX\')
    GROUP BY tablespace_name
) fs
ON df.tablespace_name = fs.tablespace_name
ORDER BY df.tablespace_name;

-- ============================================================
-- PARTE 26 - DATAFILES
-- ============================================================

SELECT
    tablespace_name,
    file_name,
    ROUND(bytes / 1024 / 1024, 2) AS size_mb,
    autoextensible,
    ROUND(maxbytes / 1024 / 1024, 2) AS max_size_mb,
    ROUND(increment_by * 8192 / 1024 / 1024, 2) AS autoextend_next_mb
FROM dba_data_files
WHERE tablespace_name IN (\'APP_DATA\', \'APP_INDEX\')
ORDER BY tablespace_name, file_name;

-- ============================================================
-- PARTE 27 - RESUMEN FINAL
-- ============================================================

SELECT
    tablespace_name,
    status,
    contents,
    extent_management,
    segment_space_management
FROM dba_tablespaces
WHERE tablespace_name IN (\'SYSTEM\', \'SYSAUX\', \'TEMP\', \'APP_DATA\', \'APP_INDEX\')
OR contents = \'UNDO\'
ORDER BY
    CASE tablespace_name
        WHEN \'SYSTEM\' THEN 1
        WHEN \'SYSAUX\' THEN 2
        WHEN \'UNDO\' THEN 3
        WHEN \'TEMP\' THEN 4
        WHEN \'APP_DATA\' THEN 5
        WHEN \'APP_INDEX\' THEN 6
        ELSE 7
    END;
'); ?>

                </div>
            </div>
        </div>


    </div><!-- /accordionBD -->
</div><!-- /wrapper -->


                  

          <?php endif; ?>

        <?php endforeach; ?>

      </div>

    </div>

  </section>

</main>

<?php include 'includes/footer.php'; ?>
