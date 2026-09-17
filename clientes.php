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
                'nombre' => 'ESPH_RESIDUOS_DATA',
                'desc'   => 'Datos del área de Residuos. 50 MB iniciales, AUTOEXTEND hasta 500 MB.',
                'icono'  => 'fa-recycle',
                'tipo'   => 'Aplicación'
            ],

            [
                'nombre' => 'ESPH_RESIDUOS_IDX',
                'desc'   => 'Índices del área de Residuos. 50 MB iniciales, AUTOEXTEND hasta 500 MB.',
                'icono'  => 'fa-recycle',
                'tipo'   => 'Aplicación'
            ],

            [
                'nombre' => 'ESPH_ENERGIA_DATA',
                'desc'   => 'Datos del área de Energía Eléctrica. 50 MB iniciales, AUTOEXTEND hasta 500 MB.',
                'icono'  => 'fa-bolt',
                'tipo'   => 'Aplicación'
            ],

            [
                'nombre' => 'ESPH_ENERGIA_IDX',
                'desc'   => 'Índices del área de Energía Eléctrica. 50 MB iniciales, AUTOEXTEND hasta 500 MB.',
                'icono'  => 'fa-bolt',
                'tipo'   => 'Aplicación'
            ],

            [
                'nombre' => 'ESPH_AGUA_DATA',
                'desc'   => 'Datos del área de Agua Potable. 50 MB iniciales, AUTOEXTEND hasta 500 MB.',
                'icono'  => 'fa-droplet',
                'tipo'   => 'Aplicación'
            ],

            [
                'nombre' => 'ESPH_AGUA_IDX',
                'desc'   => 'Índices del área de Agua Potable. 50 MB iniciales, AUTOEXTEND hasta 500 MB.',
                'icono'  => 'fa-droplet',
                'tipo'   => 'Aplicación'
            ],

            [
                'nombre' => 'ESPH_TIC_DATA',
                'desc'   => 'Datos del área de Tecnologías de Información. 50 MB iniciales, AUTOEXTEND hasta 500 MB.',
                'icono'  => 'fa-microchip',
                'tipo'   => 'Aplicación'
            ],

            [
                'nombre' => 'ESPH_TIC_IDX',
                'desc'   => 'Índices del área de Tecnologías de Información. 50 MB iniciales, AUTOEXTEND hasta 500 MB.',
                'icono'  => 'fa-microchip',
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
                'titulo' => 'Un tablespace por dominio',

                'desc' => 'Cada área de negocio posee su propio par de tablespaces (_DATA e _IDX), permitiendo administrar, monitorear y crecer el almacenamiento de forma completamente independiente por esquema.'
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

// Función auxiliar para formatear código SQL en las secciones del accordion.
// La función copiarCodigo() se emite UNA SOLA VEZ antes del accordion (ver más abajo).
function renderSqlBlock($sql, $title = 'Script SQL') {
    static $counter = 0;
    $counter++;
    $blockId = 'sql-block-' . $counter;
    $collapseId = 'sql-collapse-' . $counter;

    $sql = trim($sql);
    if (empty($sql)) return '';

    $html = '
    <div class="cliente-sql-block">
        <div class="cliente-sql-head">
            <div>
                <span class="cliente-sql-kicker"><i class="fa-solid fa-code"></i> SQL</span>
                <strong>' . htmlspecialchars($title) . '</strong>
            </div>
            <div class="cliente-sql-actions">
                <button type="button" class="cliente-sql-toggle" data-bs-toggle="collapse" data-bs-target="#' . $collapseId . '" aria-expanded="false" aria-controls="' . $collapseId . '">
                    <i class="fa-solid fa-chevron-down"></i><span>Ver código</span>
                </button>
                <button type="button" onclick="copiarCodigo(this)" class="cliente-copy-btn">
                    <i class="fa-regular fa-copy"></i> Copiar
                </button>
            </div>
        </div>
        <div id="' . $collapseId . '" class="collapse cliente-sql-collapse">
            <pre id="' . $blockId . '"><code>' . htmlspecialchars($sql) . '</code></pre>
        </div>
    </div>';
    return $html;
}
?>

<main class="flex-grow-1">

  <!-- =========================================================
       copiarCodigo() — declarada UNA SOLA VEZ aquí.
       Usa el <pre> hermano del botón mediante closest().
  ========================================================== -->
  <script>
  function copiarCodigo(btn) {
      var wrapper = btn.closest('div[style*="position:relative"]');
      var code    = wrapper.querySelector('pre code');
      var texto   = code.textContent;

      navigator.clipboard.writeText(texto).then(function () {
          btn.innerHTML = '<i class="fa-regular fa-check" style="margin-right:0.4rem;"></i> Copiado';
          setTimeout(function () {
              btn.innerHTML = '<i class="fa-regular fa-copy" style="margin-right:0.4rem;"></i> Copiar';
          }, 2000);
      }).catch(function () {
          var area = document.createElement('textarea');
          area.value = texto;
          document.body.appendChild(area);
          area.select();
          document.execCommand('copy');
          document.body.removeChild(area);
          btn.innerHTML = '<i class="fa-regular fa-check" style="margin-right:0.4rem;"></i> Copiado';
          setTimeout(function () {
              btn.innerHTML = '<i class="fa-regular fa-copy" style="margin-right:0.4rem;"></i> Copiar';
          }, 2000);
      });
  }
  </script>

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

      <div class="row g-4 clientes-grid">

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

          <div class="col-12">

            <div class="service-card cliente-project-card h-100 d-flex flex-column">

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
                  class="mt-2 mb-4 cliente-detail-shell"
                  style="
                    background:var(--surface);
                    border:1px solid var(--border);
                    border-radius:14px;
                    padding:2rem;
                  "
                >

                  <div class="cliente-detail-toolbar">
                    <span><i class="fa-solid fa-circle-info"></i> Detalle técnico del proyecto</span>
                    <button type="button" class="cliente-close-detail" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-controls="<?php echo $collapseId; ?>">
                      <i class="fa-solid fa-xmark"></i> Cerrar información
                    </button>
                  </div>

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

<div class="cliente-db-architecture" style="margin-top:2rem">

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
        asignando un par de tablespaces exclusivos (<code>_DATA</code> e <code>_IDX</code>) a cada área de negocio,
        junto con sus esquemas, privilegios y modelos de datos.
        El objetivo es proporcionar una estructura organizada, controlada y escalable para representar diferentes dominios operativos de ESPH S.A.
    </div>

    <!-- Accordion -->
    <div class="accordion cliente-tech-accordion" id="accordionBD">


        <!-- 1. ENTORNO -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingEntorno">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseEntorno"
                        aria-expanded="false"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-server me-2" style="color:var(--risk-mid)"></i>
                    1. Preparación y verificación del entorno Oracle
                </button>
            </h2>
            <div id="collapseEntorno" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
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
                        Se inspeccionaron los tablespaces existentes de Oracle y posteriormente se crearon
                        <strong style="color:var(--text)">ocho tablespaces de aplicación</strong>,
                        asignando un par exclusivo (<code>_DATA</code> e <code>_IDX</code>) a cada área de negocio:
                    </p>

                    <div class="row g-3 mb-4">
                        <?php
                        $tsGroups = [
                            ['icono'=>'fa-recycle','color'=>'var(--risk-mid)','data'=>'ESPH_RESIDUOS_DATA','idx'=>'ESPH_RESIDUOS_IDX','area'=>'Residuos'],
                            ['icono'=>'fa-bolt',   'color'=>'var(--risk-mid)','data'=>'ESPH_ENERGIA_DATA', 'idx'=>'ESPH_ENERGIA_IDX', 'area'=>'Energía Eléctrica'],
                            ['icono'=>'fa-droplet','color'=>'var(--risk-mid)','data'=>'ESPH_AGUA_DATA',    'idx'=>'ESPH_AGUA_IDX',    'area'=>'Agua Potable'],
                            ['icono'=>'fa-microchip','color'=>'var(--risk-mid)','data'=>'ESPH_TIC_DATA',  'idx'=>'ESPH_TIC_IDX',     'area'=>'TIC'],
                        ];
                        foreach ($tsGroups as $tg):
                        ?>
                        <div class="col-md-6">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(242,177,52,0.3);border-radius:10px;padding:1.25rem;height:100%">
                                <h5 style="font-size:0.9rem;color:var(--risk-mid);margin-bottom:0.75rem">
                                    <i class="fa-solid <?php echo $tg['icono']; ?> me-2"></i>
                                    <?php echo $tg['area']; ?>
                                </h5>
                                <div style="display:flex;flex-direction:column;gap:0.5rem">
                                    <div style="background:rgba(0,0,0,0.15);border-radius:6px;padding:0.5rem 0.75rem;font-size:0.8rem">
                                        <code style="color:var(--risk-mid)"><?php echo $tg['data']; ?></code>
                                        <span style="color:var(--text-muted);margin-left:0.5rem">→ tablas y datos</span>
                                    </div>
                                    <div style="background:rgba(0,0,0,0.15);border-radius:6px;padding:0.5rem 0.75rem;font-size:0.8rem">
                                        <code style="color:var(--risk-mid)"><?php echo $tg['idx']; ?></code>
                                        <span style="color:var(--text-muted);margin-left:0.5rem">→ índices</span>
                                    </div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);padding-left:0.25rem">
                                        50 MB iniciales · AUTOEXTEND 10 MB · máx 500 MB
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Por qué un par de tablespaces por dominio?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Asignar tablespaces exclusivos por área de negocio permite administrar, monitorear y escalar
                        el almacenamiento de forma completamente independiente. Si el área de Residuos crece,
                        su tablespace puede ampliarse sin afectar al resto. Además, facilita tareas de respaldo y
                        auditoría por dominio.
                    </p>

                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text)">
                        <strong style="color:var(--risk-mid)">Decisión de diseño:</strong><br>
                        <code>*_DATA</code> → tablas y datos del dominio &nbsp;·&nbsp; <code>*_IDX</code> → índices del dominio<br><br>
                        De esta forma se evita concentrar todos los objetos de aplicación en los tablespaces internos de Oracle
                        como <code>SYSTEM</code> y <code>SYSAUX</code>, y además cada área mantiene su propio espacio aislado.
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
-- PARTE 7 - CREANDO TABLESPACES POR DOMINIO
-- ============================================================

DEFINE DATA_DIR = \'C:\oracle\data\'

-- ESPH_RESIDUOS_DATA
DECLARE
    v_omf VARCHAR2(1000);
    v_sql VARCHAR2(4000);
BEGIN
    SELECT value INTO v_omf FROM v$parameter WHERE name = \'db_create_file_dest\';
    IF v_omf IS NOT NULL THEN
        v_sql := \'CREATE TABLESPACE ESPH_RESIDUOS_DATA SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    ELSE
        v_sql := \'CREATE TABLESPACE ESPH_RESIDUOS_DATA DATAFILE \'\'&DATA_DIR\\esph_residuos_data01.dbf\'\' SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    END IF;
    EXECUTE IMMEDIATE v_sql;
    DBMS_OUTPUT.PUT_LINE(\'ESPH_RESIDUOS_DATA creado correctamente.\');
END;
/

-- ESPH_RESIDUOS_IDX
DECLARE
    v_omf VARCHAR2(1000);
    v_sql VARCHAR2(4000);
BEGIN
    SELECT value INTO v_omf FROM v$parameter WHERE name = \'db_create_file_dest\';
    IF v_omf IS NOT NULL THEN
        v_sql := \'CREATE TABLESPACE ESPH_RESIDUOS_IDX SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    ELSE
        v_sql := \'CREATE TABLESPACE ESPH_RESIDUOS_IDX DATAFILE \'\'&DATA_DIR\\esph_residuos_idx01.dbf\'\' SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    END IF;
    EXECUTE IMMEDIATE v_sql;
    DBMS_OUTPUT.PUT_LINE(\'ESPH_RESIDUOS_IDX creado correctamente.\');
END;
/

-- ESPH_ENERGIA_DATA
DECLARE
    v_omf VARCHAR2(1000);
    v_sql VARCHAR2(4000);
BEGIN
    SELECT value INTO v_omf FROM v$parameter WHERE name = \'db_create_file_dest\';
    IF v_omf IS NOT NULL THEN
        v_sql := \'CREATE TABLESPACE ESPH_ENERGIA_DATA SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    ELSE
        v_sql := \'CREATE TABLESPACE ESPH_ENERGIA_DATA DATAFILE \'\'&DATA_DIR\\esph_energia_data01.dbf\'\' SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    END IF;
    EXECUTE IMMEDIATE v_sql;
    DBMS_OUTPUT.PUT_LINE(\'ESPH_ENERGIA_DATA creado correctamente.\');
END;
/

-- ESPH_ENERGIA_IDX
DECLARE
    v_omf VARCHAR2(1000);
    v_sql VARCHAR2(4000);
BEGIN
    SELECT value INTO v_omf FROM v$parameter WHERE name = \'db_create_file_dest\';
    IF v_omf IS NOT NULL THEN
        v_sql := \'CREATE TABLESPACE ESPH_ENERGIA_IDX SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    ELSE
        v_sql := \'CREATE TABLESPACE ESPH_ENERGIA_IDX DATAFILE \'\'&DATA_DIR\\esph_energia_idx01.dbf\'\' SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    END IF;
    EXECUTE IMMEDIATE v_sql;
    DBMS_OUTPUT.PUT_LINE(\'ESPH_ENERGIA_IDX creado correctamente.\');
END;
/

-- ESPH_AGUA_DATA
DECLARE
    v_omf VARCHAR2(1000);
    v_sql VARCHAR2(4000);
BEGIN
    SELECT value INTO v_omf FROM v$parameter WHERE name = \'db_create_file_dest\';
    IF v_omf IS NOT NULL THEN
        v_sql := \'CREATE TABLESPACE ESPH_AGUA_DATA SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    ELSE
        v_sql := \'CREATE TABLESPACE ESPH_AGUA_DATA DATAFILE \'\'&DATA_DIR\\esph_agua_data01.dbf\'\' SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    END IF;
    EXECUTE IMMEDIATE v_sql;
    DBMS_OUTPUT.PUT_LINE(\'ESPH_AGUA_DATA creado correctamente.\');
END;
/

-- ESPH_AGUA_IDX
DECLARE
    v_omf VARCHAR2(1000);
    v_sql VARCHAR2(4000);
BEGIN
    SELECT value INTO v_omf FROM v$parameter WHERE name = \'db_create_file_dest\';
    IF v_omf IS NOT NULL THEN
        v_sql := \'CREATE TABLESPACE ESPH_AGUA_IDX SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    ELSE
        v_sql := \'CREATE TABLESPACE ESPH_AGUA_IDX DATAFILE \'\'&DATA_DIR\\esph_agua_idx01.dbf\'\' SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    END IF;
    EXECUTE IMMEDIATE v_sql;
    DBMS_OUTPUT.PUT_LINE(\'ESPH_AGUA_IDX creado correctamente.\');
END;
/

-- ESPH_TIC_DATA
DECLARE
    v_omf VARCHAR2(1000);
    v_sql VARCHAR2(4000);
BEGIN
    SELECT value INTO v_omf FROM v$parameter WHERE name = \'db_create_file_dest\';
    IF v_omf IS NOT NULL THEN
        v_sql := \'CREATE TABLESPACE ESPH_TIC_DATA SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    ELSE
        v_sql := \'CREATE TABLESPACE ESPH_TIC_DATA DATAFILE \'\'&DATA_DIR\\esph_tic_data01.dbf\'\' SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    END IF;
    EXECUTE IMMEDIATE v_sql;
    DBMS_OUTPUT.PUT_LINE(\'ESPH_TIC_DATA creado correctamente.\');
END;
/

-- ESPH_TIC_IDX
DECLARE
    v_omf VARCHAR2(1000);
    v_sql VARCHAR2(4000);
BEGIN
    SELECT value INTO v_omf FROM v$parameter WHERE name = \'db_create_file_dest\';
    IF v_omf IS NOT NULL THEN
        v_sql := \'CREATE TABLESPACE ESPH_TIC_IDX SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    ELSE
        v_sql := \'CREATE TABLESPACE ESPH_TIC_IDX DATAFILE \'\'&DATA_DIR\\esph_tic_idx01.dbf\'\' SIZE 50M AUTOEXTEND ON NEXT 10M MAXSIZE 500M EXTENT MANAGEMENT LOCAL AUTOALLOCATE SEGMENT SPACE MANAGEMENT AUTO\';
    END IF;
    EXECUTE IMMEDIATE v_sql;
    DBMS_OUTPUT.PUT_LINE(\'ESPH_TIC_IDX creado correctamente.\');
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
                        Esta lógica se aplica a cada uno de los ocho tablespaces de dominio.
                    </p>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Por qué?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Esto permite que la creación de los tablespaces no dependa completamente de una ruta física específica del servidor.
                        Cuando OMF está disponible, Oracle administra la ubicación y nombres de los archivos físicos.
                    </p>

                    <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text-muted)">
                        <strong style="color:var(--text)">Resultado:</strong><br>
                        El script puede adaptarse a diferentes instalaciones de Oracle sin modificar necesariamente toda la definición de almacenamiento.
                        Cada tablespace tiene su propio datafile nombrado con el prefijo <code>ESPH_</code> para facilitar la identificación en disco.
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

-- Cuando OMF está activo, Oracle asigna nombres y rutas automáticamente.
-- Cuando no lo está, cada tablespace recibe un datafile con nombre propio:
--
--   ESPH_RESIDUOS_DATA  →  esph_residuos_data01.dbf
--   ESPH_RESIDUOS_IDX   →  esph_residuos_idx01.dbf
--   ESPH_ENERGIA_DATA   →  esph_energia_data01.dbf
--   ESPH_ENERGIA_IDX    →  esph_energia_idx01.dbf
--   ESPH_AGUA_DATA      →  esph_agua_data01.dbf
--   ESPH_AGUA_IDX       →  esph_agua_idx01.dbf
--   ESPH_TIC_DATA       →  esph_tic_data01.dbf
--   ESPH_TIC_IDX        →  esph_tic_idx01.dbf
--
-- Ver la creación completa en la sección 2.
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
                        Se crearon cuatro usuarios de Oracle que funcionan también como <strong style="color:var(--text)">esquemas independientes</strong>.
                        Cada usuario tiene como tablespace por defecto su propio <code>_DATA</code> y cuota independiente:
                    </p>

                    <div class="row g-3 mb-3">
                        <?php
                        $esquemas_info = [
                            ['nombre'=>'ESPH_RESIDUOS','desc'=>'Sistemas relacionados con gestión de residuos.','sistemas'=>'SGA y SRR','icono'=>'fa-recycle','data'=>'ESPH_RESIDUOS_DATA','idx'=>'ESPH_RESIDUOS_IDX'],
                            ['nombre'=>'ESPH_ENERGIA', 'desc'=>'Sistemas relacionados con energía eléctrica.',  'sistemas'=>'SRED y SALP','icono'=>'fa-bolt','data'=>'ESPH_ENERGIA_DATA','idx'=>'ESPH_ENERGIA_IDX'],
                            ['nombre'=>'ESPH_AGUA',    'desc'=>'Sistemas relacionados con agua potable.',       'sistemas'=>'SCDA y SHH','icono'=>'fa-droplet','data'=>'ESPH_AGUA_DATA','idx'=>'ESPH_AGUA_IDX'],
                            ['nombre'=>'ESPH_TIC',     'desc'=>'Sistemas relacionados con tecnología de información.','sistemas'=>'SGTI y SAMS','icono'=>'fa-microchip','data'=>'ESPH_TIC_DATA','idx'=>'ESPH_TIC_IDX'],
                        ];
                        foreach ($esquemas_info as $ei):
                        ?>
                        <div class="col-md-6">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:8px;padding:1rem;height:100%">
                                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.35rem">
                                    <i class="fa-solid <?php echo $ei['icono']; ?>" style="color:var(--risk-mid);font-size:0.85rem"></i>
                                    <strong style="font-size:0.85rem;font-family:var(--font-mono)"><?php echo $ei['nombre']; ?></strong>
                                </div>
                                <p style="font-size:0.82rem;color:var(--text-muted);margin:0 0 0.4rem 0"><?php echo $ei['desc']; ?></p>
                                <div style="font-size:0.72rem;font-family:var(--font-mono);color:var(--text-muted);line-height:1.6">
                                    <span style="color:var(--risk-mid)"><?php echo $ei['data']; ?></span> · 200 MB<br>
                                    <span style="color:var(--risk-mid)"><?php echo $ei['idx']; ?></span> · 100 MB
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="border-top:1px solid var(--border);margin:1rem 0"></div>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Por qué utilizar esquemas separados con tablespaces propios?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        La combinación de esquema exclusivo + tablespace exclusivo garantiza que cada dominio no pueda
                        consumir espacio de otro. Las cuotas por tablespace limitan el crecimiento individual
                        sin afectar al resto de las áreas.
                    </p>

                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text)">
                        <strong style="color:var(--risk-mid)">Principio aplicado: separación de responsabilidades.</strong><br>
                        Cada dominio empresarial mantiene sus propios objetos de datos en su propio espacio de almacenamiento,
                        mientras Oracle conserva el control administrativo general de la instancia.
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
DEFAULT TABLESPACE ESPH_RESIDUOS_DATA
TEMPORARY TABLESPACE TEMP
QUOTA 200M ON ESPH_RESIDUOS_DATA
QUOTA 100M ON ESPH_RESIDUOS_IDX;

CREATE USER esph_energia
IDENTIFIED BY "&PWD_ENERGIA"
DEFAULT TABLESPACE ESPH_ENERGIA_DATA
TEMPORARY TABLESPACE TEMP
QUOTA 200M ON ESPH_ENERGIA_DATA
QUOTA 100M ON ESPH_ENERGIA_IDX;

CREATE USER esph_agua
IDENTIFIED BY "&PWD_AGUA"
DEFAULT TABLESPACE ESPH_AGUA_DATA
TEMPORARY TABLESPACE TEMP
QUOTA 200M ON ESPH_AGUA_DATA
QUOTA 100M ON ESPH_AGUA_IDX;

CREATE USER esph_tic
IDENTIFIED BY "&PWD_TIC"
DEFAULT TABLESPACE ESPH_TIC_DATA
TEMPORARY TABLESPACE TEMP
QUOTA 200M ON ESPH_TIC_DATA
QUOTA 100M ON ESPH_TIC_IDX;
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
                'ts_data'=> 'ESPH_RESIDUOS_DATA',
                'ts_idx' => 'ESPH_RESIDUOS_IDX',
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
-- Tablespace datos : ESPH_RESIDUOS_DATA
-- Tablespace índices: ESPH_RESIDUOS_IDX
-- ============================================================

CREATE TABLE esph_residuos.centro_acopio (
    id_centro NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(100) NOT NULL,
    direccion VARCHAR2(200) NOT NULL,
    capacidad_ton NUMBER(8,2) NOT NULL,
    activo CHAR(1) DEFAULT \'S\' NOT NULL,
    fecha_registro DATE DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_centro_acopio PRIMARY KEY (id_centro) USING INDEX TABLESPACE ESPH_RESIDUOS_IDX,
    CONSTRAINT ck_ca_activo CHECK (activo IN (\'S\',\'N\'))
) TABLESPACE ESPH_RESIDUOS_DATA;

CREATE TABLE esph_residuos.tipo_residuo (
    id_tipo NUMBER GENERATED ALWAYS AS IDENTITY,
    descripcion VARCHAR2(100) NOT NULL,
    categoria VARCHAR2(50) NOT NULL,
    unidad_medida VARCHAR2(20) DEFAULT \'KG\' NOT NULL,
    CONSTRAINT pk_tipo_residuo PRIMARY KEY (id_tipo) USING INDEX TABLESPACE ESPH_RESIDUOS_IDX,
    CONSTRAINT ck_tr_cat CHECK (categoria IN (\'ORGANICO\',\'INORGANICO\',\'PELIGROSO\',\'ESPECIAL\'))
) TABLESPACE ESPH_RESIDUOS_DATA;

CREATE TABLE esph_residuos.ingreso_residuo (
    id_ingreso NUMBER GENERATED ALWAYS AS IDENTITY,
    id_centro NUMBER NOT NULL,
    id_tipo NUMBER NOT NULL,
    cantidad NUMBER(10,3) NOT NULL,
    fecha_ingreso DATE DEFAULT SYSDATE NOT NULL,
    origen VARCHAR2(150),
    observaciones VARCHAR2(500),
    CONSTRAINT pk_ingreso_residuo PRIMARY KEY (id_ingreso) USING INDEX TABLESPACE ESPH_RESIDUOS_IDX,
    CONSTRAINT fk_ir_centro FOREIGN KEY (id_centro) REFERENCES esph_residuos.centro_acopio(id_centro),
    CONSTRAINT fk_ir_tipo FOREIGN KEY (id_tipo) REFERENCES esph_residuos.tipo_residuo(id_tipo)
) TABLESPACE ESPH_RESIDUOS_DATA;

CREATE TABLE esph_residuos.ruta (
    id_ruta NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(100) NOT NULL,
    zona VARCHAR2(80) NOT NULL,
    dia_semana VARCHAR2(10) NOT NULL,
    hora_inicio VARCHAR2(5) NOT NULL,
    activa CHAR(1) DEFAULT \'S\' NOT NULL,
    CONSTRAINT pk_ruta PRIMARY KEY (id_ruta) USING INDEX TABLESPACE ESPH_RESIDUOS_IDX,
    CONSTRAINT ck_ruta_dia CHECK (dia_semana IN (\'LUNES\',\'MARTES\',\'MIERCOLES\',\'JUEVES\',\'VIERNES\',\'SABADO\',\'DOMINGO\')),
    CONSTRAINT ck_ruta_activa CHECK (activa IN (\'S\',\'N\'))
) TABLESPACE ESPH_RESIDUOS_DATA;

CREATE TABLE esph_residuos.vehiculo (
    id_vehiculo NUMBER GENERATED ALWAYS AS IDENTITY,
    placa VARCHAR2(10) NOT NULL,
    tipo VARCHAR2(50) NOT NULL,
    capacidad_ton NUMBER(6,2) NOT NULL,
    en_servicio CHAR(1) DEFAULT \'S\' NOT NULL,
    CONSTRAINT pk_vehiculo PRIMARY KEY (id_vehiculo) USING INDEX TABLESPACE ESPH_RESIDUOS_IDX,
    CONSTRAINT uk_vehiculo_placa UNIQUE (placa) USING INDEX TABLESPACE ESPH_RESIDUOS_IDX,
    CONSTRAINT ck_veh_servicio CHECK (en_servicio IN (\'S\',\'N\'))
) TABLESPACE ESPH_RESIDUOS_DATA;

CREATE TABLE esph_residuos.ejecucion_ruta (
    id_ejecucion NUMBER GENERATED ALWAYS AS IDENTITY,
    id_ruta NUMBER NOT NULL,
    id_vehiculo NUMBER NOT NULL,
    fecha DATE DEFAULT SYSDATE NOT NULL,
    kg_recolectados NUMBER(10,3),
    incidencias VARCHAR2(500),
    CONSTRAINT pk_ejecucion_ruta PRIMARY KEY (id_ejecucion) USING INDEX TABLESPACE ESPH_RESIDUOS_IDX,
    CONSTRAINT fk_er_ruta FOREIGN KEY (id_ruta) REFERENCES esph_residuos.ruta(id_ruta),
    CONSTRAINT fk_er_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES esph_residuos.vehiculo(id_vehiculo)
) TABLESPACE ESPH_RESIDUOS_DATA;

CREATE INDEX esph_residuos.idx_ir_centro ON esph_residuos.ingreso_residuo(id_centro) TABLESPACE ESPH_RESIDUOS_IDX;
CREATE INDEX esph_residuos.idx_ir_fecha ON esph_residuos.ingreso_residuo(fecha_ingreso) TABLESPACE ESPH_RESIDUOS_IDX;
CREATE INDEX esph_residuos.idx_er_ruta ON esph_residuos.ejecucion_ruta(id_ruta) TABLESPACE ESPH_RESIDUOS_IDX;
CREATE INDEX esph_residuos.idx_er_fecha ON esph_residuos.ejecucion_ruta(fecha) TABLESPACE ESPH_RESIDUOS_IDX;

CREATE SEQUENCE esph_residuos.seq_folio_ingreso START WITH 1000 INCREMENT BY 1 NOCACHE NOCYCLE;
',
            ],
            [
                'id'    => 'Energia',
                'num'   => '7',
                'icono' => 'fa-bolt',
                'titulo'=> 'Modelo de datos — Energía Eléctrica',
                'esquema'=> 'ESPH_ENERGIA',
                'ts_data'=> 'ESPH_ENERGIA_DATA',
                'ts_idx' => 'ESPH_ENERGIA_IDX',
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
-- Tablespace datos : ESPH_ENERGIA_DATA
-- Tablespace índices: ESPH_ENERGIA_IDX
-- ============================================================

CREATE TABLE esph_energia.subestacion (
    id_subestacion NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(100) NOT NULL,
    ubicacion VARCHAR2(200) NOT NULL,
    voltaje_kv NUMBER(6,2) NOT NULL,
    capacidad_mva NUMBER(8,2) NOT NULL,
    operativa CHAR(1) DEFAULT \'S\' NOT NULL,
    CONSTRAINT pk_subestacion PRIMARY KEY (id_subestacion) USING INDEX TABLESPACE ESPH_ENERGIA_IDX,
    CONSTRAINT ck_sub_op CHECK (operativa IN (\'S\',\'N\'))
) TABLESPACE ESPH_ENERGIA_DATA;

CREATE TABLE esph_energia.circuito (
    id_circuito NUMBER GENERATED ALWAYS AS IDENTITY,
    id_subestacion NUMBER NOT NULL,
    nombre VARCHAR2(100) NOT NULL,
    zona_cobertura VARCHAR2(150) NOT NULL,
    clientes_aprox NUMBER(8) NOT NULL,
    CONSTRAINT pk_circuito PRIMARY KEY (id_circuito) USING INDEX TABLESPACE ESPH_ENERGIA_IDX,
    CONSTRAINT fk_cir_sub FOREIGN KEY (id_subestacion) REFERENCES esph_energia.subestacion(id_subestacion)
) TABLESPACE ESPH_ENERGIA_DATA;

CREATE TABLE esph_energia.medidor (
    id_medidor NUMBER GENERATED ALWAYS AS IDENTITY,
    id_circuito NUMBER NOT NULL,
    numero_serie VARCHAR2(30) NOT NULL,
    tipo VARCHAR2(30) NOT NULL,
    fecha_instalacion DATE DEFAULT SYSDATE NOT NULL,
    activo CHAR(1) DEFAULT \'S\' NOT NULL,
    CONSTRAINT pk_medidor PRIMARY KEY (id_medidor) USING INDEX TABLESPACE ESPH_ENERGIA_IDX,
    CONSTRAINT uk_medidor_serie UNIQUE (numero_serie) USING INDEX TABLESPACE ESPH_ENERGIA_IDX,
    CONSTRAINT ck_med_tipo CHECK (tipo IN (\'RESIDENCIAL\',\'COMERCIAL\',\'INDUSTRIAL\')),
    CONSTRAINT ck_med_activo CHECK (activo IN (\'S\',\'N\')),
    CONSTRAINT fk_med_cir FOREIGN KEY (id_circuito) REFERENCES esph_energia.circuito(id_circuito)
) TABLESPACE ESPH_ENERGIA_DATA;

CREATE TABLE esph_energia.lectura_medidor (
    id_lectura NUMBER GENERATED ALWAYS AS IDENTITY,
    id_medidor NUMBER NOT NULL,
    fecha_lectura DATE DEFAULT SYSDATE NOT NULL,
    kwh_acumulado NUMBER(12,3) NOT NULL,
    kwh_consumo NUMBER(10,3) NOT NULL,
    lector VARCHAR2(80),
    CONSTRAINT pk_lectura_medidor PRIMARY KEY (id_lectura) USING INDEX TABLESPACE ESPH_ENERGIA_IDX,
    CONSTRAINT fk_lec_med FOREIGN KEY (id_medidor) REFERENCES esph_energia.medidor(id_medidor)
) TABLESPACE ESPH_ENERGIA_DATA;

CREATE TABLE esph_energia.luminaria (
    id_luminaria NUMBER GENERATED ALWAYS AS IDENTITY,
    codigo VARCHAR2(20) NOT NULL,
    ubicacion VARCHAR2(200) NOT NULL,
    tipo_lampara VARCHAR2(50) NOT NULL,
    potencia_w NUMBER(6,2) NOT NULL,
    en_operacion CHAR(1) DEFAULT \'S\' NOT NULL,
    fecha_instalacion DATE DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_luminaria PRIMARY KEY (id_luminaria) USING INDEX TABLESPACE ESPH_ENERGIA_IDX,
    CONSTRAINT uk_luminaria_codigo UNIQUE (codigo) USING INDEX TABLESPACE ESPH_ENERGIA_IDX,
    CONSTRAINT ck_lum_tipo CHECK (tipo_lampara IN (\'LED\',\'SODIO\',\'MERCURIO\',\'HALURO\')),
    CONSTRAINT ck_lum_op CHECK (en_operacion IN (\'S\',\'N\'))
) TABLESPACE ESPH_ENERGIA_DATA;

CREATE TABLE esph_energia.orden_mant_luminaria (
    id_orden NUMBER GENERATED ALWAYS AS IDENTITY,
    id_luminaria NUMBER NOT NULL,
    tipo_trabajo VARCHAR2(80) NOT NULL,
    fecha_reporte DATE DEFAULT SYSDATE NOT NULL,
    fecha_atencion DATE,
    estado VARCHAR2(20) DEFAULT \'PENDIENTE\' NOT NULL,
    observaciones VARCHAR2(500),
    CONSTRAINT pk_orden_mant_lum PRIMARY KEY (id_orden) USING INDEX TABLESPACE ESPH_ENERGIA_IDX,
    CONSTRAINT fk_oml_lum FOREIGN KEY (id_luminaria) REFERENCES esph_energia.luminaria(id_luminaria),
    CONSTRAINT ck_oml_estado CHECK (estado IN (\'PENDIENTE\',\'EN_PROCESO\',\'COMPLETADA\',\'CANCELADA\'))
) TABLESPACE ESPH_ENERGIA_DATA;

CREATE INDEX esph_energia.idx_cir_sub ON esph_energia.circuito(id_subestacion) TABLESPACE ESPH_ENERGIA_IDX;
CREATE INDEX esph_energia.idx_med_cir ON esph_energia.medidor(id_circuito) TABLESPACE ESPH_ENERGIA_IDX;
CREATE INDEX esph_energia.idx_lec_med ON esph_energia.lectura_medidor(id_medidor) TABLESPACE ESPH_ENERGIA_IDX;
CREATE INDEX esph_energia.idx_lec_fecha ON esph_energia.lectura_medidor(fecha_lectura) TABLESPACE ESPH_ENERGIA_IDX;
CREATE INDEX esph_energia.idx_oml_lum ON esph_energia.orden_mant_luminaria(id_luminaria) TABLESPACE ESPH_ENERGIA_IDX;
CREATE INDEX esph_energia.idx_oml_estado ON esph_energia.orden_mant_luminaria(estado) TABLESPACE ESPH_ENERGIA_IDX;
',
            ],
            [
                'id'    => 'Agua',
                'num'   => '8',
                'icono' => 'fa-droplet',
                'titulo'=> 'Modelo de datos — Agua Potable',
                'esquema'=> 'ESPH_AGUA',
                'ts_data'=> 'ESPH_AGUA_DATA',
                'ts_idx' => 'ESPH_AGUA_IDX',
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
-- Tablespace datos : ESPH_AGUA_DATA
-- Tablespace índices: ESPH_AGUA_IDX
-- ============================================================

CREATE TABLE esph_agua.planta_potabilizadora (
    id_planta NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(100) NOT NULL,
    ubicacion VARCHAR2(200) NOT NULL,
    capacidad_lps NUMBER(8,2) NOT NULL,
    operativa CHAR(1) DEFAULT \'S\' NOT NULL,
    CONSTRAINT pk_planta_potabilizadora PRIMARY KEY (id_planta) USING INDEX TABLESPACE ESPH_AGUA_IDX,
    CONSTRAINT ck_pp_op CHECK (operativa IN (\'S\',\'N\'))
) TABLESPACE ESPH_AGUA_DATA;

CREATE TABLE esph_agua.tanque (
    id_tanque NUMBER GENERATED ALWAYS AS IDENTITY,
    id_planta NUMBER,
    nombre VARCHAR2(100) NOT NULL,
    capacidad_m3 NUMBER(10,2) NOT NULL,
    ubicacion VARCHAR2(200) NOT NULL,
    cota_msnm NUMBER(7,2) NOT NULL,
    CONSTRAINT pk_tanque PRIMARY KEY (id_tanque) USING INDEX TABLESPACE ESPH_AGUA_IDX,
    CONSTRAINT fk_tan_planta FOREIGN KEY (id_planta) REFERENCES esph_agua.planta_potabilizadora(id_planta)
) TABLESPACE ESPH_AGUA_DATA;

CREATE TABLE esph_agua.zona_distribucion (
    id_zona NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(100) NOT NULL,
    poblacion_est NUMBER(8) NOT NULL,
    conexiones_act NUMBER(8) NOT NULL,
    CONSTRAINT pk_zona_distribucion PRIMARY KEY (id_zona) USING INDEX TABLESPACE ESPH_AGUA_IDX
) TABLESPACE ESPH_AGUA_DATA;

CREATE TABLE esph_agua.conexion (
    id_conexion NUMBER GENERATED ALWAYS AS IDENTITY,
    id_zona NUMBER NOT NULL,
    numero_medidor VARCHAR2(20) NOT NULL,
    tipo VARCHAR2(20) NOT NULL,
    diametro_mm NUMBER(5,1) NOT NULL,
    activa CHAR(1) DEFAULT \'S\' NOT NULL,
    fecha_alta DATE DEFAULT SYSDATE NOT NULL,
    CONSTRAINT pk_conexion PRIMARY KEY (id_conexion) USING INDEX TABLESPACE ESPH_AGUA_IDX,
    CONSTRAINT uk_conexion_medidor UNIQUE (numero_medidor) USING INDEX TABLESPACE ESPH_AGUA_IDX,
    CONSTRAINT fk_con_zona FOREIGN KEY (id_zona) REFERENCES esph_agua.zona_distribucion(id_zona),
    CONSTRAINT ck_con_tipo CHECK (tipo IN (\'RESIDENCIAL\',\'COMERCIAL\',\'INDUSTRIAL\',\'MUNICIPAL\')),
    CONSTRAINT ck_con_activa CHECK (activa IN (\'S\',\'N\'))
) TABLESPACE ESPH_AGUA_DATA;

CREATE TABLE esph_agua.lectura_agua (
    id_lectura NUMBER GENERATED ALWAYS AS IDENTITY,
    id_conexion NUMBER NOT NULL,
    fecha_lectura DATE DEFAULT SYSDATE NOT NULL,
    m3_acumulado NUMBER(12,3) NOT NULL,
    m3_consumo NUMBER(10,3) NOT NULL,
    lector VARCHAR2(80),
    CONSTRAINT pk_lectura_agua PRIMARY KEY (id_lectura) USING INDEX TABLESPACE ESPH_AGUA_IDX,
    CONSTRAINT fk_la_con FOREIGN KEY (id_conexion) REFERENCES esph_agua.conexion(id_conexion)
) TABLESPACE ESPH_AGUA_DATA;

CREATE TABLE esph_agua.hidrante (
    id_hidrante NUMBER GENERATED ALWAYS AS IDENTITY,
    id_zona NUMBER NOT NULL,
    codigo VARCHAR2(20) NOT NULL,
    ubicacion VARCHAR2(200) NOT NULL,
    tipo VARCHAR2(30) NOT NULL,
    presion_psi NUMBER(6,2) NOT NULL,
    operativo CHAR(1) DEFAULT \'S\' NOT NULL,
    ultima_inspeccion DATE,
    CONSTRAINT pk_hidrante PRIMARY KEY (id_hidrante) USING INDEX TABLESPACE ESPH_AGUA_IDX,
    CONSTRAINT uk_hidrante_codigo UNIQUE (codigo) USING INDEX TABLESPACE ESPH_AGUA_IDX,
    CONSTRAINT fk_hid_zona FOREIGN KEY (id_zona) REFERENCES esph_agua.zona_distribucion(id_zona),
    CONSTRAINT ck_hid_tipo CHECK (tipo IN (\'COLUMNA\',\'BAJO_NIVEL\',\'MURAL\')),
    CONSTRAINT ck_hid_op CHECK (operativo IN (\'S\',\'N\'))
) TABLESPACE ESPH_AGUA_DATA;

CREATE TABLE esph_agua.inspeccion_hidrante (
    id_inspeccion NUMBER GENERATED ALWAYS AS IDENTITY,
    id_hidrante NUMBER NOT NULL,
    fecha DATE DEFAULT SYSDATE NOT NULL,
    presion_medida NUMBER(6,2) NOT NULL,
    caudal_lps NUMBER(6,2),
    resultado VARCHAR2(20) NOT NULL,
    inspector VARCHAR2(100),
    observaciones VARCHAR2(500),
    CONSTRAINT pk_inspeccion_hidrante PRIMARY KEY (id_inspeccion) USING INDEX TABLESPACE ESPH_AGUA_IDX,
    CONSTRAINT fk_ih_hid FOREIGN KEY (id_hidrante) REFERENCES esph_agua.hidrante(id_hidrante),
    CONSTRAINT ck_ih_res CHECK (resultado IN (\'APROBADO\',\'REPARACION\',\'FUERA_SERVICIO\'))
) TABLESPACE ESPH_AGUA_DATA;

CREATE INDEX esph_agua.idx_con_zona ON esph_agua.conexion(id_zona) TABLESPACE ESPH_AGUA_IDX;
CREATE INDEX esph_agua.idx_la_con ON esph_agua.lectura_agua(id_conexion) TABLESPACE ESPH_AGUA_IDX;
CREATE INDEX esph_agua.idx_la_fecha ON esph_agua.lectura_agua(fecha_lectura) TABLESPACE ESPH_AGUA_IDX;
CREATE INDEX esph_agua.idx_hid_zona ON esph_agua.hidrante(id_zona) TABLESPACE ESPH_AGUA_IDX;
CREATE INDEX esph_agua.idx_ih_hid ON esph_agua.inspeccion_hidrante(id_hidrante) TABLESPACE ESPH_AGUA_IDX;
CREATE INDEX esph_agua.idx_ih_fecha ON esph_agua.inspeccion_hidrante(fecha) TABLESPACE ESPH_AGUA_IDX;
',
            ],
            [
                'id'    => 'TIC',
                'num'   => '9',
                'icono' => 'fa-desktop',
                'titulo'=> 'Modelo de datos — Tecnologías de Información',
                'esquema'=> 'ESPH_TIC',
                'ts_data'=> 'ESPH_TIC_DATA',
                'ts_idx' => 'ESPH_TIC_IDX',
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
-- Tablespace datos : ESPH_TIC_DATA
-- Tablespace índices: ESPH_TIC_IDX
-- ============================================================

CREATE TABLE esph_tic.categoria_activo (
    id_categoria NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(80) NOT NULL,
    descripcion VARCHAR2(200),
    CONSTRAINT pk_categoria_activo PRIMARY KEY (id_categoria) USING INDEX TABLESPACE ESPH_TIC_IDX,
    CONSTRAINT uk_categoria_activo UNIQUE (nombre) USING INDEX TABLESPACE ESPH_TIC_IDX
) TABLESPACE ESPH_TIC_DATA;

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
    CONSTRAINT pk_activo_ti PRIMARY KEY (id_activo) USING INDEX TABLESPACE ESPH_TIC_IDX,
    CONSTRAINT uk_activo_codigo UNIQUE (codigo) USING INDEX TABLESPACE ESPH_TIC_IDX,
    CONSTRAINT fk_ati_cat FOREIGN KEY (id_categoria) REFERENCES esph_tic.categoria_activo(id_categoria),
    CONSTRAINT ck_ati_estado CHECK (estado IN (\'ACTIVO\',\'EN_MANTENIMIENTO\',\'DADO_DE_BAJA\',\'BODEGA\'))
) TABLESPACE ESPH_TIC_DATA;

CREATE TABLE esph_tic.licencia_software (
    id_licencia NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre_producto VARCHAR2(150) NOT NULL,
    proveedor VARCHAR2(100) NOT NULL,
    tipo_licencia VARCHAR2(50) NOT NULL,
    cantidad_usuarios NUMBER(6),
    fecha_vencimiento DATE,
    costo_anual NUMBER(12,2),
    observaciones VARCHAR2(300),
    CONSTRAINT pk_licencia_software PRIMARY KEY (id_licencia) USING INDEX TABLESPACE ESPH_TIC_IDX,
    CONSTRAINT ck_lic_tipo CHECK (tipo_licencia IN (\'PERPETUA\',\'SUSCRIPCION\',\'OPEN_SOURCE\',\'FREEWARE\'))
) TABLESPACE ESPH_TIC_DATA;

CREATE TABLE esph_tic.categoria_ticket (
    id_categoria NUMBER GENERATED ALWAYS AS IDENTITY,
    nombre VARCHAR2(80) NOT NULL,
    nivel_sla_horas NUMBER(4) NOT NULL,
    CONSTRAINT pk_categoria_ticket PRIMARY KEY (id_categoria) USING INDEX TABLESPACE ESPH_TIC_IDX,
    CONSTRAINT uk_categoria_ticket UNIQUE (nombre) USING INDEX TABLESPACE ESPH_TIC_IDX
) TABLESPACE ESPH_TIC_DATA;

CREATE TABLE esph_tic.usuario_interno (
    id_usuario NUMBER GENERATED ALWAYS AS IDENTITY,
    cedula VARCHAR2(12) NOT NULL,
    nombre VARCHAR2(150) NOT NULL,
    area VARCHAR2(100) NOT NULL,
    correo VARCHAR2(150) NOT NULL,
    activo CHAR(1) DEFAULT \'S\' NOT NULL,
    CONSTRAINT pk_usuario_interno PRIMARY KEY (id_usuario) USING INDEX TABLESPACE ESPH_TIC_IDX,
    CONSTRAINT uk_usuario_cedula UNIQUE (cedula) USING INDEX TABLESPACE ESPH_TIC_IDX,
    CONSTRAINT uk_usuario_correo UNIQUE (correo) USING INDEX TABLESPACE ESPH_TIC_IDX,
    CONSTRAINT ck_ui_activo CHECK (activo IN (\'S\',\'N\'))
) TABLESPACE ESPH_TIC_DATA;

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
    CONSTRAINT pk_ticket PRIMARY KEY (id_ticket) USING INDEX TABLESPACE ESPH_TIC_IDX,
    CONSTRAINT fk_tkt_cat FOREIGN KEY (id_categoria) REFERENCES esph_tic.categoria_ticket(id_categoria),
    CONSTRAINT fk_tkt_sol FOREIGN KEY (id_solicitante) REFERENCES esph_tic.usuario_interno(id_usuario),
    CONSTRAINT fk_tkt_ati FOREIGN KEY (id_activo) REFERENCES esph_tic.activo_ti(id_activo),
    CONSTRAINT ck_tkt_pri CHECK (prioridad IN (\'BAJA\',\'MEDIA\',\'ALTA\',\'CRITICA\')),
    CONSTRAINT ck_tkt_est CHECK (estado IN (\'ABIERTO\',\'EN_PROCESO\',\'RESUELTO\',\'CERRADO\',\'CANCELADO\'))
) TABLESPACE ESPH_TIC_DATA;

CREATE INDEX esph_tic.idx_ati_cat ON esph_tic.activo_ti(id_categoria) TABLESPACE ESPH_TIC_IDX;
CREATE INDEX esph_tic.idx_ati_estado ON esph_tic.activo_ti(estado) TABLESPACE ESPH_TIC_IDX;
CREATE INDEX esph_tic.idx_tkt_cat ON esph_tic.ticket(id_categoria) TABLESPACE ESPH_TIC_IDX;
CREATE INDEX esph_tic.idx_tkt_sol ON esph_tic.ticket(id_solicitante) TABLESPACE ESPH_TIC_IDX;
CREATE INDEX esph_tic.idx_tkt_estado ON esph_tic.ticket(estado) TABLESPACE ESPH_TIC_IDX;
CREATE INDEX esph_tic.idx_tkt_fecha ON esph_tic.ticket(fecha_apertura) TABLESPACE ESPH_TIC_IDX;
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

                    <!-- Tablespaces del dominio -->
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem">
                        <div style="background:rgba(0,0,0,0.15);border-radius:6px;padding:0.35rem 0.75rem;font-size:0.75rem">
                            <i class="fa-solid fa-table me-1" style="color:var(--risk-mid)"></i>
                            <code style="color:var(--risk-mid)"><?php echo $m['ts_data']; ?></code>
                            <span style="color:var(--text-muted);margin-left:0.35rem">datos</span>
                        </div>
                        <div style="background:rgba(0,0,0,0.15);border-radius:6px;padding:0.35rem 0.75rem;font-size:0.75rem">
                            <i class="fa-solid fa-magnifying-glass me-1" style="color:var(--risk-mid)"></i>
                            <code style="color:var(--risk-mid)"><?php echo $m['ts_idx']; ?></code>
                            <span style="color:var(--text-muted);margin-left:0.35rem">índices</span>
                        </div>
                    </div>

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

-- PRIMARY KEY con USING INDEX TABLESPACE propio del dominio
CONSTRAINT pk_centro_acopio
    PRIMARY KEY (id_centro)
    USING INDEX TABLESPACE ESPH_RESIDUOS_IDX

-- FOREIGN KEY (relación)
CONSTRAINT fk_ir_centro
    FOREIGN KEY (id_centro)
    REFERENCES esph_residuos.centro_acopio(id_centro)

-- UNIQUE (evita duplicidad)
CONSTRAINT uk_vehiculo_placa
    UNIQUE (placa)
    USING INDEX TABLESPACE ESPH_RESIDUOS_IDX

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
                        Todos los índices residen en el tablespace <code>_IDX</code> correspondiente al dominio.
                    </p>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">Ejemplos</h5>
                    <ul style="font-size:0.85rem;color:var(--text-muted);padding-left:1.25rem">
                        <li><code>idx_ir_centro</code> → búsquedas de ingresos por centro de acopio. <span style="font-family:var(--font-mono);font-size:0.75rem;color:var(--risk-mid)">[ESPH_RESIDUOS_IDX]</span></li>
                        <li><code>idx_ir_fecha</code> → consultas de ingresos por fecha. <span style="font-family:var(--font-mono);font-size:0.75rem;color:var(--risk-mid)">[ESPH_RESIDUOS_IDX]</span></li>
                        <li><code>idx_lec_med</code> → búsquedas de lecturas por medidor. <span style="font-family:var(--font-mono);font-size:0.75rem;color:var(--risk-mid)">[ESPH_ENERGIA_IDX]</span></li>
                        <li><code>idx_lec_fecha</code> → consultas históricas por fecha. <span style="font-family:var(--font-mono);font-size:0.75rem;color:var(--risk-mid)">[ESPH_ENERGIA_IDX]</span></li>
                        <li><code>idx_tkt_estado</code> → filtrado de tickets según estado. <span style="font-family:var(--font-mono);font-size:0.75rem;color:var(--risk-mid)">[ESPH_TIC_IDX]</span></li>
                        <li><code>idx_tkt_fecha</code> → consultas de tickets por fecha. <span style="font-family:var(--font-mono);font-size:0.75rem;color:var(--risk-mid)">[ESPH_TIC_IDX]</span></li>
                    </ul>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Por qué?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Los índices permiten que Oracle encuentre registros mediante estructuras auxiliares sin tener que recorrer necesariamente toda la tabla.
                    </p>

                    <div style="background:rgba(255,200,50,0.07);border:1px solid rgba(255,200,50,0.25);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text)">
                        <strong style="color:var(--risk-mid)">Decisión:</strong>
                        No se indexaron indiscriminadamente todas las columnas. Se seleccionaron principalmente columnas utilizadas en relaciones, búsquedas, filtros y consultas temporales.
                        Cada índice queda físicamente aislado en el tablespace <code>_IDX</code> de su propio dominio.
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- ÍNDICES DE RENDIMIENTO POR ESQUEMA Y TABLESPACE
-- ============================================================

-- ESPH_RESIDUOS → ESPH_RESIDUOS_IDX
CREATE INDEX esph_residuos.idx_ir_centro ON esph_residuos.ingreso_residuo(id_centro) TABLESPACE ESPH_RESIDUOS_IDX;
CREATE INDEX esph_residuos.idx_ir_fecha ON esph_residuos.ingreso_residuo(fecha_ingreso) TABLESPACE ESPH_RESIDUOS_IDX;
CREATE INDEX esph_residuos.idx_er_ruta ON esph_residuos.ejecucion_ruta(id_ruta) TABLESPACE ESPH_RESIDUOS_IDX;
CREATE INDEX esph_residuos.idx_er_fecha ON esph_residuos.ejecucion_ruta(fecha) TABLESPACE ESPH_RESIDUOS_IDX;

-- ESPH_ENERGIA → ESPH_ENERGIA_IDX
CREATE INDEX esph_energia.idx_cir_sub ON esph_energia.circuito(id_subestacion) TABLESPACE ESPH_ENERGIA_IDX;
CREATE INDEX esph_energia.idx_med_cir ON esph_energia.medidor(id_circuito) TABLESPACE ESPH_ENERGIA_IDX;
CREATE INDEX esph_energia.idx_lec_med ON esph_energia.lectura_medidor(id_medidor) TABLESPACE ESPH_ENERGIA_IDX;
CREATE INDEX esph_energia.idx_lec_fecha ON esph_energia.lectura_medidor(fecha_lectura) TABLESPACE ESPH_ENERGIA_IDX;
CREATE INDEX esph_energia.idx_oml_lum ON esph_energia.orden_mant_luminaria(id_luminaria) TABLESPACE ESPH_ENERGIA_IDX;
CREATE INDEX esph_energia.idx_oml_estado ON esph_energia.orden_mant_luminaria(estado) TABLESPACE ESPH_ENERGIA_IDX;

-- ESPH_AGUA → ESPH_AGUA_IDX
CREATE INDEX esph_agua.idx_con_zona ON esph_agua.conexion(id_zona) TABLESPACE ESPH_AGUA_IDX;
CREATE INDEX esph_agua.idx_la_con ON esph_agua.lectura_agua(id_conexion) TABLESPACE ESPH_AGUA_IDX;
CREATE INDEX esph_agua.idx_la_fecha ON esph_agua.lectura_agua(fecha_lectura) TABLESPACE ESPH_AGUA_IDX;
CREATE INDEX esph_agua.idx_hid_zona ON esph_agua.hidrante(id_zona) TABLESPACE ESPH_AGUA_IDX;
CREATE INDEX esph_agua.idx_ih_hid ON esph_agua.inspeccion_hidrante(id_hidrante) TABLESPACE ESPH_AGUA_IDX;
CREATE INDEX esph_agua.idx_ih_fecha ON esph_agua.inspeccion_hidrante(fecha) TABLESPACE ESPH_AGUA_IDX;

-- ESPH_TIC → ESPH_TIC_IDX
CREATE INDEX esph_tic.idx_ati_cat ON esph_tic.activo_ti(id_categoria) TABLESPACE ESPH_TIC_IDX;
CREATE INDEX esph_tic.idx_ati_estado ON esph_tic.activo_ti(estado) TABLESPACE ESPH_TIC_IDX;
CREATE INDEX esph_tic.idx_tkt_cat ON esph_tic.ticket(id_categoria) TABLESPACE ESPH_TIC_IDX;
CREATE INDEX esph_tic.idx_tkt_sol ON esph_tic.ticket(id_solicitante) TABLESPACE ESPH_TIC_IDX;
CREATE INDEX esph_tic.idx_tkt_estado ON esph_tic.ticket(estado) TABLESPACE ESPH_TIC_IDX;
CREATE INDEX esph_tic.idx_tkt_fecha ON esph_tic.ticket(fecha_apertura) TABLESPACE ESPH_TIC_IDX;
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
                            ['fa-chart-bar', 'Espacio',    'Se revisa el espacio utilizado y disponible en cada par de tablespaces.'],
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
-- PARTE 17 - CUOTAS POR TABLESPACE DE DOMINIO
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

                    <!-- Capa Oracle -->
                    <div class="row text-center g-3 mb-3">
                        <?php
                        $arq = [
                            ['Oracle Database 21c XE','Motor de base de datos · PDB: XEPDB1'],
                            ['TEMP',                  'Operaciones temporales compartidas'],
                            ['UNDO',                  'Rollback y gestión de transacciones'],
                        ];
                        foreach ($arq as $a):
                        ?>
                        <div class="col-md-4">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:8px;padding:1rem">
                                <strong style="font-family:var(--font-mono);font-size:0.8rem;color:var(--risk-mid)"><?php echo $a[0]; ?></strong><br>
                                <span style="font-size:0.75rem;color:var(--text-muted)"><?php echo $a[1]; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="border-top:1px solid var(--border);margin:1rem 0"></div>

                    <!-- Capa dominios -->
                    <div class="row g-3 mb-4">
                        <?php
                        $dominios = [
                            ['icono'=>'fa-recycle', 'esquema'=>'ESPH_RESIDUOS', 'data'=>'ESPH_RESIDUOS_DATA', 'idx'=>'ESPH_RESIDUOS_IDX'],
                            ['icono'=>'fa-bolt',    'esquema'=>'ESPH_ENERGIA',  'data'=>'ESPH_ENERGIA_DATA',  'idx'=>'ESPH_ENERGIA_IDX'],
                            ['icono'=>'fa-droplet', 'esquema'=>'ESPH_AGUA',     'data'=>'ESPH_AGUA_DATA',     'idx'=>'ESPH_AGUA_IDX'],
                            ['icono'=>'fa-microchip','esquema'=>'ESPH_TIC',     'data'=>'ESPH_TIC_DATA',      'idx'=>'ESPH_TIC_IDX'],
                        ];
                        foreach ($dominios as $d):
                        ?>
                        <div class="col-md-6">
                            <div style="background:rgba(242,177,52,0.06);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem 1rem">
                                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem">
                                    <i class="fa-solid <?php echo $d['icono']; ?>" style="color:var(--risk-mid)"></i>
                                    <strong style="font-family:var(--font-mono);font-size:0.8rem;color:var(--risk-mid)"><?php echo $d['esquema']; ?></strong>
                                </div>
                                <div style="font-size:0.72rem;font-family:var(--font-mono);color:var(--text-muted);line-height:1.8">
                                    <i class="fa-solid fa-table me-1"></i><?php echo $d['data']; ?> · 200 MB<br>
                                    <i class="fa-solid fa-magnifying-glass me-1"></i><?php echo $d['idx']; ?> · 100 MB
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:1rem 1.25rem;font-size:0.88rem;color:var(--text)">
                        <strong style="color:var(--risk-mid)">En resumen:</strong>
                        La base de datos fue estructurada para separar la información empresarial por dominios con tablespaces exclusivos,
                        controlar el almacenamiento mediante cuotas individuales, restringir los privilegios, mantener la integridad referencial,
                        optimizar las consultas y facilitar la administración futura del entorno Oracle.
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
-- PARTE 20 - TABLAS POR TABLESPACE DE DOMINIO
-- ============================================================

SELECT
    owner,
    table_name,
    tablespace_name
FROM dba_tables
WHERE owner IN (\'ESPH_RESIDUOS\', \'ESPH_ENERGIA\', \'ESPH_AGUA\', \'ESPH_TIC\')
ORDER BY owner, table_name;

-- ============================================================
-- PARTE 21 - INDICES POR TABLESPACE DE DOMINIO
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
-- PARTE 22 - SEGMENTOS POR DOMINIO
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
-- PARTE 23 - SEGMENTOS POR TABLESPACE DE DOMINIO
-- ============================================================

SELECT
    tablespace_name,
    segment_type,
    COUNT(*) AS cantidad_segmentos,
    ROUND(SUM(bytes) / 1024 / 1024, 2) AS total_mb
FROM dba_segments
WHERE tablespace_name IN (
    \'ESPH_RESIDUOS_DATA\', \'ESPH_RESIDUOS_IDX\',
    \'ESPH_ENERGIA_DATA\',  \'ESPH_ENERGIA_IDX\',
    \'ESPH_AGUA_DATA\',     \'ESPH_AGUA_IDX\',
    \'ESPH_TIC_DATA\',      \'ESPH_TIC_IDX\'
)
GROUP BY tablespace_name, segment_type
ORDER BY tablespace_name, segment_type;

-- ============================================================
-- PARTE 24 - EXTENTS POR DOMINIO
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
-- PARTE 25 - ESPACIO UTILIZADO POR TABLESPACE DE DOMINIO
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
    WHERE tablespace_name IN (
        \'ESPH_RESIDUOS_DATA\', \'ESPH_RESIDUOS_IDX\',
        \'ESPH_ENERGIA_DATA\',  \'ESPH_ENERGIA_IDX\',
        \'ESPH_AGUA_DATA\',     \'ESPH_AGUA_IDX\',
        \'ESPH_TIC_DATA\',      \'ESPH_TIC_IDX\'
    )
    GROUP BY tablespace_name
) df
LEFT JOIN
(
    SELECT tablespace_name, SUM(bytes) / 1024 / 1024 AS free_mb
    FROM dba_free_space
    WHERE tablespace_name IN (
        \'ESPH_RESIDUOS_DATA\', \'ESPH_RESIDUOS_IDX\',
        \'ESPH_ENERGIA_DATA\',  \'ESPH_ENERGIA_IDX\',
        \'ESPH_AGUA_DATA\',     \'ESPH_AGUA_IDX\',
        \'ESPH_TIC_DATA\',      \'ESPH_TIC_IDX\'
    )
    GROUP BY tablespace_name
) fs
ON df.tablespace_name = fs.tablespace_name
ORDER BY df.tablespace_name;

-- ============================================================
-- PARTE 26 - DATAFILES POR TABLESPACE DE DOMINIO
-- ============================================================

SELECT
    tablespace_name,
    file_name,
    ROUND(bytes / 1024 / 1024, 2) AS size_mb,
    autoextensible,
    ROUND(maxbytes / 1024 / 1024, 2) AS max_size_mb,
    ROUND(increment_by * 8192 / 1024 / 1024, 2) AS autoextend_next_mb
FROM dba_data_files
WHERE tablespace_name IN (
    \'ESPH_RESIDUOS_DATA\', \'ESPH_RESIDUOS_IDX\',
    \'ESPH_ENERGIA_DATA\',  \'ESPH_ENERGIA_IDX\',
    \'ESPH_AGUA_DATA\',     \'ESPH_AGUA_IDX\',
    \'ESPH_TIC_DATA\',      \'ESPH_TIC_IDX\'
)
ORDER BY tablespace_name, file_name;

-- ============================================================
-- PARTE 27 - RESUMEN FINAL DE TABLESPACES
-- ============================================================

SELECT
    tablespace_name,
    status,
    contents,
    extent_management,
    segment_space_management
FROM dba_tablespaces
WHERE tablespace_name IN (
    \'SYSTEM\', \'SYSAUX\', \'TEMP\',
    \'ESPH_RESIDUOS_DATA\', \'ESPH_RESIDUOS_IDX\',
    \'ESPH_ENERGIA_DATA\',  \'ESPH_ENERGIA_IDX\',
    \'ESPH_AGUA_DATA\',     \'ESPH_AGUA_IDX\',
    \'ESPH_TIC_DATA\',      \'ESPH_TIC_IDX\'
)
OR contents = \'UNDO\'
ORDER BY
    CASE tablespace_name
        WHEN \'SYSTEM\'              THEN 1
        WHEN \'SYSAUX\'             THEN 2
        WHEN \'UNDO\'               THEN 3  -- nombre real varía
        WHEN \'TEMP\'               THEN 4
        WHEN \'ESPH_RESIDUOS_DATA\' THEN 5
        WHEN \'ESPH_RESIDUOS_IDX\'  THEN 6
        WHEN \'ESPH_ENERGIA_DATA\'  THEN 7
        WHEN \'ESPH_ENERGIA_IDX\'   THEN 8
        WHEN \'ESPH_AGUA_DATA\'     THEN 9
        WHEN \'ESPH_AGUA_IDX\'      THEN 10
        WHEN \'ESPH_TIC_DATA\'      THEN 11
        WHEN \'ESPH_TIC_IDX\'       THEN 12
        ELSE 13
    END;
'); ?>

                </div>
            </div>
        </div>



        <!-- 15. ARCHIVELOG -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingArchivelog">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseArchivelog"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-box-archive me-2" style="color:var(--risk-mid)"></i>
                    15. Modo de archivado — ARCHIVELOG
                </button>
            </h2>
            <div id="collapseArchivelog" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Qué son los Redo Logs?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Oracle escribe cada cambio en la base de datos (INSERT, UPDATE, DELETE) en archivos circulares
                        llamados <strong style="color:var(--text)">Online Redo Logs</strong> antes de aplicarlos a los datafiles.
                        Son la primera línea de defensa ante fallos: si Oracle cae en medio de una operación,
                        los redo logs permiten reconstruir los cambios al reiniciar.
                    </p>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:10px;padding:1.25rem;height:100%">
                                <h5 style="font-size:0.9rem;margin-bottom:0.75rem">
                                    <i class="fa-solid fa-circle-xmark me-2" style="color:#e05c5c"></i>
                                    NOARCHIVELOG
                                </h5>
                                <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:0.5rem">
                                    Modo por defecto en XE. Los grupos de redo se reutilizan en ciclo
                                    y el contenido anterior se sobreescribe permanentemente.
                                </p>
                                <ul style="font-size:0.8rem;color:var(--text-muted);padding-left:1.25rem;margin:0">
                                    <li>Sin historial de cambios</li>
                                    <li>Recuperación solo al último backup completo</li>
                                    <li>Pérdida de datos entre backups</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(242,177,52,0.3);border-radius:10px;padding:1.25rem;height:100%">
                                <h5 style="font-size:0.9rem;color:var(--risk-mid);margin-bottom:0.75rem">
                                    <i class="fa-solid fa-circle-check me-2"></i>
                                    ARCHIVELOG
                                </h5>
                                <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:0.5rem">
                                    Antes de reutilizar un grupo de redo, Oracle lo copia como archivo histórico
                                    (archive log). Esto habilita recuperación point-in-time.
                                </p>
                                <ul style="font-size:0.8rem;color:var(--text-muted);padding-left:1.25rem;margin:0">
                                    <li>Historial completo de cambios</li>
                                    <li>Recuperación a cualquier punto en el tiempo</li>
                                    <li>Sin pérdida de datos ante fallos de disco</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Cómo se activa?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        El cambio de modo no puede hacerse con la base abierta. Requiere un ciclo controlado:
                        bajar la instancia, subirla en modo <code>MOUNT</code>, ejecutar el cambio y volver a abrirla.
                        El script verifica el modo actual antes de proceder para evitar ejecutar el ciclo innecesariamente.
                    </p>

                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text);margin-bottom:1rem">
                        <strong style="color:var(--risk-mid)">Importante:</strong>
                        Este procedimiento reinicia la base de datos. Debe ejecutarse en una ventana de mantenimiento
                        cuando no haya sesiones activas. En el entorno de ESPH S.A. sobre Oracle XE local,
                        el impacto es inmediato y controlado.
                    </div>

                    <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:8px;padding:1rem;margin-bottom:1rem;font-size:0.85rem">
                        <strong>Secuencia de operaciones:</strong>
                        <ol style="margin:0.5rem 0 0 0;padding-left:1.25rem;color:var(--text-muted);line-height:2">
                            <li>Verificar modo actual con <code>v$database</code></li>
                            <li>Si ya es ARCHIVELOG, detener — no se necesita hacer nada</li>
                            <li><code>SHUTDOWN IMMEDIATE</code> — cierre limpio</li>
                            <li><code>STARTUP MOUNT</code> — instancia arriba, base sin abrir</li>
                            <li><code>ALTER DATABASE ARCHIVELOG</code> — cambio de modo</li>
                            <li><code>ALTER DATABASE OPEN</code> — base disponible</li>
                            <li>Verificar con <code>SELECT log_mode FROM v$database</code></li>
                        </ol>
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- PARTE 28 - VERIFICACION Y ACTIVACION DE ARCHIVELOG
-- ============================================================

-- Paso 1: Verificar modo actual
SELECT
    name,
    log_mode,
    db_unique_name
FROM v$database;

-- Paso 2: Verificar grupos de redo actuales
SELECT
    l.group#,
    l.members,
    l.status,
    ROUND(l.bytes / 1024 / 1024, 2) AS size_mb,
    l.archived
FROM v$log l
ORDER BY l.group#;

-- Paso 3: Verificar archivos de redo
SELECT
    lf.group#,
    lf.member,
    lf.status
FROM v$logfile lf
ORDER BY lf.group#, lf.member;

-- ============================================================
-- Ejecutar solo si log_mode = \'NOARCHIVELOG\'
-- ============================================================

-- Paso 4: Bajar la instancia de forma limpia
SHUTDOWN IMMEDIATE;

-- Paso 5: Subir en modo MOUNT (instancia activa, base sin abrir)
STARTUP MOUNT;

-- Paso 6: Activar modo ARCHIVELOG
ALTER DATABASE ARCHIVELOG;

-- Paso 7: Abrir la base de datos
ALTER DATABASE OPEN;

-- ============================================================
-- PARTE 29 - VERIFICACION POST-ACTIVACION
-- ============================================================

-- Confirmar que el modo cambió
SELECT
    name,
    log_mode,
    db_unique_name
FROM v$database;

-- Confirmar que el archivado está activo
SELECT
    dest_id,
    dest_name,
    status,
    target,
    archiver,
    destination
FROM v$archive_dest
WHERE status = \'VALID\'
  AND target = \'PRIMARY\';

-- Ver archive logs generados
SELECT
    sequence#,
    name,
    ROUND(blocks * block_size / 1024 / 1024, 2) AS size_mb,
    archived,
    status,
    completion_time
FROM v$archived_log
ORDER BY sequence# DESC
FETCH FIRST 10 ROWS ONLY;
', 'Activación de ARCHIVELOG'); ?>

                </div>
            </div>
        </div>


        <!-- 16. FAST RECOVERY AREA -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingFRA">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseFRA"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-hard-drive me-2" style="color:var(--risk-mid)"></i>
                    16. Fast Recovery Area — control del espacio de archivado
                </button>
            </h2>
            <div id="collapseFRA" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Qué es la Fast Recovery Area?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        La <strong style="color:var(--text)">Fast Recovery Area (FRA)</strong> es una ubicación en disco
                        administrada por Oracle donde se almacenan los archive logs, backups y otros archivos de recuperación.
                        Oracle gestiona automáticamente el espacio dentro de ella: cuando se acerca al límite,
                        elimina los archive logs que ya están cubiertos por un backup, sin intervención manual.
                    </p>

                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text);margin-bottom:1.5rem">
                        <strong style="color:var(--risk-mid)">El riesgo crítico sin FRA configurada correctamente:</strong><br>
                        Si el destino de archive logs se llena, Oracle <strong>detiene completamente todas las escrituras</strong>
                        hasta que se libere espacio. La base queda inaccesible aunque el motor esté activo.
                        La FRA con un tamaño definido evita este escenario gestionando el espacio de forma autónoma.
                    </div>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Cómo se configura?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        El script detecta automáticamente si XE ya tiene una FRA configurada consultando
                        <code>DB_RECOVERY_FILE_DEST</code> en <code>v$parameter</code>, igual que se hizo con OMF
                        para los tablespaces. Si ya existe, solo se ajusta el tamaño. Si no existe, se define la ruta
                        estándar de XE y el tamaño máximo.
                    </p>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:8px;padding:1rem;height:100%">
                                <h6 style="font-size:0.82rem;color:var(--risk-mid);margin-bottom:0.4rem">
                                    <i class="fa-solid fa-folder me-1"></i>DB_RECOVERY_FILE_DEST
                                </h6>
                                <p style="font-size:0.78rem;color:var(--text-muted);margin:0">
                                    Ruta en disco donde Oracle almacena los archive logs y backups.
                                    Se detecta automáticamente de la instalación existente de XE.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:8px;padding:1rem;height:100%">
                                <h6 style="font-size:0.82rem;color:var(--risk-mid);margin-bottom:0.4rem">
                                    <i class="fa-solid fa-weight-hanging me-1"></i>DB_RECOVERY_FILE_DEST_SIZE
                                </h6>
                                <p style="font-size:0.78rem;color:var(--text-muted);margin:0">
                                    Límite máximo de espacio que Oracle puede usar en la FRA.
                                    Al acercarse al límite, Oracle purga archive logs obsoletos automáticamente.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:8px;padding:1rem;height:100%">
                                <h6 style="font-size:0.82rem;color:var(--risk-mid);margin-bottom:0.4rem">
                                    <i class="fa-solid fa-rotate me-1"></i>LOG_ARCHIVE_DEST_1
                                </h6>
                                <p style="font-size:0.78rem;color:var(--text-muted);margin:0">
                                    Apunta los archive logs hacia la FRA usando
                                    <code>USE_DB_RECOVERY_FILE_DEST</code> para que Oracle los gestione centralizadamente.
                                </p>
                            </div>
                        </div>
                    </div>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Qué pasa cuando la FRA se llena?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Oracle aplica una política de limpieza automática en tres pasos antes de llegar al límite:
                    </p>

                    <ol style="font-size:0.85rem;color:var(--text-muted);padding-left:1.25rem;line-height:2">
                        <li>Elimina archive logs ya aplicados y cubiertos por un backup.</li>
                        <li>Elimina backups obsoletos según la política de retención configurada.</li>
                        <li>Si aun así no hay espacio, emite alertas en el <code>alert.log</code> y eventualmente detiene las escrituras.</li>
                    </ol>

                    <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem">
                        <strong style="color:var(--text)">Por eso el tamaño importa:</strong><br>
                        En el entorno de ESPH S.A. sobre XE local, se configura la FRA en <strong>10 GB</strong> como punto de partida razonable.
                        Este valor puede ajustarse en línea con <code>ALTER SYSTEM</code> sin reiniciar la base.
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- PARTE 30 - VERIFICACION Y CONFIGURACION DE LA FRA
-- ============================================================

-- Paso 1: Detectar si ya existe FRA configurada en XE
SELECT
    name,
    value,
    description
FROM v$parameter
WHERE name IN (
    \'db_recovery_file_dest\',
    \'db_recovery_file_dest_size\',
    \'log_archive_dest_1\'
)
ORDER BY name;

-- Paso 2: Ver uso actual de la FRA
SELECT
    space_limit,
    space_used,
    space_reclaimable,
    number_of_files,
    ROUND(space_used / space_limit * 100, 2) AS pct_usado
FROM v$recovery_file_dest;

-- ============================================================
-- PARTE 31 - CONFIGURACION AUTOMATICA DE LA FRA
-- Detecta la ruta existente de XE y ajusta el tamaño.
-- Si no existe FRA, usa el directorio de recuperación
-- estándar de Oracle XE.
-- ============================================================

DECLARE
    v_fra_dest      VARCHAR2(512);
    v_fra_size      VARCHAR2(100);
BEGIN
    -- Detectar FRA existente
    BEGIN
        SELECT value INTO v_fra_dest
        FROM v$parameter
        WHERE name = \'db_recovery_file_dest\';
    EXCEPTION
        WHEN NO_DATA_FOUND THEN v_fra_dest := NULL;
    END;

    IF v_fra_dest IS NOT NULL AND v_fra_dest != \'\' THEN
        DBMS_OUTPUT.PUT_LINE(\'FRA existente detectada: \' || v_fra_dest);
        DBMS_OUTPUT.PUT_LINE(\'Solo se ajustara el tamaño maximo.\');
    ELSE
        -- FRA no configurada: usar ruta por defecto de XE
        DBMS_OUTPUT.PUT_LINE(\'FRA no configurada. Se usara el directorio de recuperacion de XE.\');
        EXECUTE IMMEDIATE
            \'ALTER SYSTEM SET db_recovery_file_dest = \'\'C:\app\oracle\fast_recovery_area\'\' SCOPE=BOTH\';
        DBMS_OUTPUT.PUT_LINE(\'db_recovery_file_dest configurado.\');
    END IF;

    -- Definir tamaño máximo de la FRA: 10 GB
    EXECUTE IMMEDIATE
        \'ALTER SYSTEM SET db_recovery_file_dest_size = 10G SCOPE=BOTH\';
    DBMS_OUTPUT.PUT_LINE(\'db_recovery_file_dest_size = 10G configurado.\');

    -- Apuntar archive logs a la FRA
    EXECUTE IMMEDIATE
        \'ALTER SYSTEM SET log_archive_dest_1 = \'\'LOCATION=USE_DB_RECOVERY_FILE_DEST\'\' SCOPE=BOTH\';
    DBMS_OUTPUT.PUT_LINE(\'log_archive_dest_1 apuntado a la FRA.\');

END;
/

-- ============================================================
-- PARTE 32 - VERIFICACION POST-CONFIGURACION
-- ============================================================

-- Confirmar parámetros activos
SELECT
    name,
    value
FROM v$parameter
WHERE name IN (
    \'db_recovery_file_dest\',
    \'db_recovery_file_dest_size\',
    \'log_archive_dest_1\'
)
ORDER BY name;

-- Estado actualizado de la FRA
SELECT
    ROUND(space_limit / 1024 / 1024 / 1024, 2)       AS limite_gb,
    ROUND(space_used  / 1024 / 1024 / 1024, 2)       AS usado_gb,
    ROUND(space_reclaimable / 1024 / 1024 / 1024, 2) AS recuperable_gb,
    number_of_files,
    ROUND(space_used / space_limit * 100, 2)          AS pct_usado
FROM v$recovery_file_dest;

-- Archivos dentro de la FRA por tipo
SELECT
    file_type,
    COUNT(*)                                          AS cantidad,
    ROUND(SUM(space_used) / 1024 / 1024, 2)          AS mb_usados,
    ROUND(SUM(space_reclaimable) / 1024 / 1024, 2)   AS mb_recuperables
FROM v$recovery_area_usage
GROUP BY file_type
ORDER BY mb_usados DESC;
', 'Configuración de la Fast Recovery Area'); ?>

                </div>
            </div>
        </div>


        <!-- 17. REDO LOG GROUPS -->
        <div class="accordion-item" style="background:var(--surface);border:1px solid var(--border);border-radius:10px;margin-bottom:0.5rem;overflow:hidden">
            <h2 class="accordion-header" id="headingRedoGroups">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseRedoGroups"
                        style="background:var(--surface);color:var(--text);box-shadow:none">
                    <i class="fa-solid fa-layer-group me-2" style="color:var(--risk-mid)"></i>
                    17. Redo Log Groups — dimensionamiento y protección
                </button>
            </h2>
            <div id="collapseRedoGroups" class="accordion-collapse collapse" data-bs-parent="#accordionBD">
                <div class="accordion-body" style="background:var(--surface);color:var(--text)">

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Qué son los Redo Log Groups?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Oracle organiza los redo logs en <strong style="color:var(--text)">grupos</strong>.
                        Cada grupo contiene uno o más archivos físicos llamados <strong style="color:var(--text)">miembros</strong>.
                        Oracle escribe en un grupo a la vez de forma circular: cuando un grupo se llena ocurre un
                        <strong style="color:var(--text)">log switch</strong> y Oracle pasa al siguiente grupo.
                        Si está en modo ARCHIVELOG, antes de reutilizar un grupo lo archiva.
                    </p>

                    <div style="background:rgba(242,177,52,0.07);border:1px solid rgba(242,177,52,0.2);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text);margin-bottom:1.5rem">
                        <strong style="color:var(--risk-mid)">El problema con la configuración por defecto de XE:</strong><br>
                        XE viene con 3 grupos de ~50 MB cada uno. En una base con actividad media,
                        esto provoca log switches cada pocos minutos, lo que genera carga de I/O innecesaria
                        y, en modo ARCHIVELOG, presión constante sobre la FRA.
                        Con grupos más grandes y más cantidad, los switches ocurren con mucha menor frecuencia.
                    </div>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Qué se hace?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Se agregan tres grupos nuevos (4, 5 y 6) con miembros de <strong style="color:var(--text)">200 MB</strong> cada uno.
                        Los grupos originales de XE (1, 2 y 3) no se pueden eliminar mientras estén activos,
                        pero al agregar los nuevos Oracle los irá dejando inactivos en su ciclo normal
                        y eventualmente podrán eliminarse. El script detecta automáticamente qué grupos
                        ya existen para no intentar crear duplicados.
                    </p>

                    <div class="row g-3 mb-4">
                        <?php
                        $redoInfo = [
                            ['fa-circle-exclamation','XE por defecto','3 grupos · ~50 MB c/u · log switches frecuentes','#e05c5c'],
                            ['fa-circle-check',      'Con mejora',    '6 grupos · 200 MB c/u · switches reducidos notablemente','var(--risk-mid)'],
                            ['fa-rotate',            'Log switch',    'Evento en que Oracle pasa al siguiente grupo. Menos frecuencia = mejor rendimiento','var(--text-muted)'],
                        ];
                        foreach ($redoInfo as $ri):
                        ?>
                        <div class="col-md-4">
                            <div style="background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:8px;padding:1rem;height:100%">
                                <h6 style="font-size:0.82rem;margin-bottom:0.4rem">
                                    <i class="fa-solid <?php echo $ri[0]; ?> me-1" style="color:<?php echo $ri[3]; ?>"></i>
                                    <?php echo $ri[1]; ?>
                                </h6>
                                <p style="font-size:0.78rem;color:var(--text-muted);margin:0"><?php echo $ri[2]; ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <h5 style="font-size:0.95rem;margin-bottom:0.5rem">¿Por qué no simplemente ampliar los grupos existentes?</h5>
                    <p style="font-size:0.88rem;color:var(--text-muted)">
                        Un grupo de redo no puede redimensionarse en línea. La única forma de cambiar el tamaño
                        de un grupo existente es eliminarlo y recrearlo, lo que requiere que el grupo esté
                        en estado <code>INACTIVE</code>. Agregar grupos nuevos con el tamaño correcto
                        es la vía segura que no interrumpe la operación.
                    </p>

                    <div style="background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:8px;padding:0.85rem 1rem;font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem">
                        <strong style="color:var(--text)">Nota sobre miembros múltiples:</strong><br>
                        Cada grupo puede tener más de un miembro (copia del mismo archivo en rutas distintas).
                        Oracle escribe en todos los miembros del grupo simultáneamente. En este entorno XE local
                        se define un solo miembro por grupo, ya que el objetivo es académico y no hay múltiples
                        discos físicos para separar las copias.
                    </div>

                    <?php echo renderSqlBlock('
-- ============================================================
-- PARTE 33 - ESTADO ACTUAL DE LOS REDO LOG GROUPS
-- ============================================================

-- Ver grupos actuales
SELECT
    l.group#,
    l.members,
    l.status,
    ROUND(l.bytes / 1024 / 1024, 2) AS size_mb,
    l.archived,
    l.sequence#
FROM v$log l
ORDER BY l.group#;

-- Ver archivos físicos de cada grupo
SELECT
    lf.group#,
    lf.member,
    lf.type,
    lf.status
FROM v$logfile lf
ORDER BY lf.group#, lf.member;

-- Frecuencia actual de log switches (por hora)
SELECT
    TO_CHAR(first_time, \'YYYY-MM-DD HH24\') AS hora,
    COUNT(*)                                 AS switches
FROM v$log_history
WHERE first_time >= SYSDATE - 1
GROUP BY TO_CHAR(first_time, \'YYYY-MM-DD HH24\')
ORDER BY hora;

-- ============================================================
-- PARTE 34 - AGREGAR GRUPOS CON DETECCION AUTOMATICA
-- Detecta si la FRA está disponible para usar OMF.
-- Si no, los miembros se crean en la ruta estándar de XE.
-- ============================================================

DECLARE
    v_fra      VARCHAR2(512);
    v_omf      VARCHAR2(512);
    v_usa_omf  BOOLEAN := FALSE;
    v_max_grp  NUMBER;
BEGIN
    -- Detectar FRA (configurada en PARTE 31)
    SELECT value INTO v_fra
    FROM v$parameter
    WHERE name = \'db_recovery_file_dest\';

    -- Detectar OMF
    SELECT value INTO v_omf
    FROM v$parameter
    WHERE name = \'db_create_file_dest\';

    v_usa_omf := (v_omf IS NOT NULL AND v_omf != \'\')
              OR (v_fra IS NOT NULL AND v_fra != \'\');

    DBMS_OUTPUT.PUT_LINE(
        CASE WHEN v_usa_omf
             THEN \'OMF/FRA disponible: Oracle gestionara los miembros automaticamente.\'
             ELSE \'OMF/FRA no detectado: se usara ruta manual.\'
        END
    );

    -- Saber cuántos grupos existen actualmente
    SELECT MAX(group#) INTO v_max_grp FROM v$log;
    DBMS_OUTPUT.PUT_LINE(\'Grupos actuales: \' || v_max_grp);

    -- Agregar grupos 4, 5 y 6 si no existen
    FOR i IN 4..6 LOOP
        DECLARE
            v_existe NUMBER;
        BEGIN
            SELECT COUNT(*) INTO v_existe FROM v$log WHERE group# = i;

            IF v_existe = 0 THEN
                IF v_usa_omf THEN
                    EXECUTE IMMEDIATE
                        \'ALTER DATABASE ADD LOGFILE GROUP \' || i ||
                        \' SIZE 200M\';
                ELSE
                    EXECUTE IMMEDIATE
                        \'ALTER DATABASE ADD LOGFILE GROUP \' || i ||
                        \' (\'\'C:\app\oracle\oradata\XE\redo0\' || i || \'.log\'\') SIZE 200M\';
                END IF;
                DBMS_OUTPUT.PUT_LINE(\'Grupo \' || i || \' agregado correctamente (200 MB).\');
            ELSE
                DBMS_OUTPUT.PUT_LINE(\'Grupo \' || i || \' ya existe. Se omite.\');
            END IF;
        END;
    END LOOP;

END;
/

-- ============================================================
-- PARTE 35 - VERIFICACION POST-CONFIGURACION
-- ============================================================

-- Estado final de todos los grupos
SELECT
    l.group#,
    l.members,
    l.status,
    ROUND(l.bytes / 1024 / 1024, 2) AS size_mb,
    l.archived,
    l.sequence#
FROM v$log l
ORDER BY l.group#;

-- Archivos físicos resultantes
SELECT
    lf.group#,
    lf.member,
    lf.status
FROM v$logfile lf
ORDER BY lf.group#, lf.member;

-- Resumen: tamaño total de redo configurado
SELECT
    COUNT(*)                                    AS total_grupos,
    ROUND(SUM(bytes) / 1024 / 1024, 2)         AS total_mb_redo,
    ROUND(AVG(bytes) / 1024 / 1024, 2)         AS promedio_mb_por_grupo
FROM v$log;
', 'Dimensionamiento de Redo Log Groups'); ?>

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

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Sincroniza los botones del SQL: Ver código / Ocultar código.
    document.querySelectorAll('.cliente-sql-collapse').forEach(function (panel) {
        var trigger = document.querySelector('[data-bs-target="#' + panel.id + '"]');
        if (!trigger) return;
        var label = trigger.querySelector('span');
        var icon = trigger.querySelector('i');
        panel.addEventListener('shown.bs.collapse', function () {
            if (label) label.textContent = 'Ocultar código';
            if (icon) icon.className = 'fa-solid fa-chevron-up';
        });
        panel.addEventListener('hidden.bs.collapse', function () {
            if (label) label.textContent = 'Ver código';
            if (icon) icon.className = 'fa-solid fa-chevron-down';
        });
    });

    // Mejora accesibilidad/estado visual del acordeón técnico.
    document.querySelectorAll('#accordionBD .accordion-collapse').forEach(function (panel) {
        panel.addEventListener('shown.bs.collapse', function () {
            var button = document.querySelector('[data-bs-target="#' + panel.id + '"]');
            if (button) button.closest('.accordion-item')?.classList.add('is-open');
        });
        panel.addEventListener('hidden.bs.collapse', function () {
            var button = document.querySelector('[data-bs-target="#' + panel.id + '"]');
            if (button) button.closest('.accordion-item')?.classList.remove('is-open');
        });
    });
});
</script>

</main>

<?php include 'includes/footer.php'; ?>
