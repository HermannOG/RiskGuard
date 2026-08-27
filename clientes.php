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
