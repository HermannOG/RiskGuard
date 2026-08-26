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
========================================================= -->

<section class="container my-5">

    <div class="card shadow-sm border-0">

        <div class="card-body p-4">

            <div class="d-flex align-items-center mb-3">
                <div class="bg-primary text-white rounded-circle p-3 me-3">
                    <i class="bi bi-database-fill"></i>
                </div>

                <div>
                    <h2 class="mb-1">Arquitectura y configuración de la base de datos</h2>
                    <p class="text-muted mb-0">
                        Descripción técnica de las modificaciones realizadas sobre Oracle Database
                        para representar el entorno empresarial de ESPH S.A.
                    </p>
                </div>
            </div>

            <div class="alert alert-info border-0">
                <strong>¿Qué se realizó?</strong><br>
                Se diseñó y configuró una estructura de almacenamiento empresarial sobre
                <strong>Oracle Database 21c XE</strong>, separando los datos empresariales,
                los índices, los esquemas por área de negocio y los privilegios de acceso.
                El objetivo es proporcionar una estructura organizada, controlada y escalable
                para representar diferentes dominios operativos de ESPH S.A.
            </div>


            <!-- =================================================
                 1. ENTORNO
            ================================================== -->

            <div class="accordion" id="accordionBD">

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingEntorno">

                        <button class="accordion-button"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseEntorno"
                                aria-expanded="true"
                                aria-controls="collapseEntorno">

                            <i class="bi bi-server me-2"></i>
                            1. Preparación y verificación del entorno Oracle

                        </button>

                    </h2>

                    <div id="collapseEntorno"
                         class="accordion-collapse collapse show"
                         aria-labelledby="headingEntorno"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <h5>¿Qué se hizo?</h5>

                            <p>
                                Antes de realizar cualquier modificación estructural se verificó
                                la versión de Oracle, el contenedor activo, el usuario conectado,
                                los tablespaces existentes y la configuración de almacenamiento.
                            </p>

                            <div class="bg-light rounded p-3 mb-3">

                                <strong>Oracle utilizado:</strong>

                                <ul class="mb-0 mt-2">
                                    <li>Oracle Database 21c XE</li>
                                    <li>PDB: <code>XEPDB1</code></li>
                                    <li>Ejecución administrativa: <code>SYS AS SYSDBA</code></li>
                                </ul>

                            </div>

                            <h5>¿Por qué se hizo?</h5>

                            <p>
                                Esta verificación evita ejecutar accidentalmente operaciones
                                administrativas sobre un contenedor incorrecto. En una arquitectura
                                Oracle Multitenant, trabajar en el PDB equivocado podría provocar
                                que los usuarios, tablespaces u objetos fueran creados en un
                                entorno diferente al esperado.
                            </p>

                            <div class="alert alert-warning">
                                <strong>Justificación:</strong>
                                el script incluye una validación mediante
                                <code>SYS_CONTEXT('USERENV', 'CON_NAME')</code> que detiene
                                la ejecución si el contenedor no corresponde a
                                <code>XEPDB1</code>.
                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     2. TABLESPACES
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingTablespaces">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseTablespaces"
                                aria-expanded="false"
                                aria-controls="collapseTablespaces">

                            <i class="bi bi-hdd-stack-fill me-2"></i>
                            2. Organización física mediante Tablespaces

                        </button>

                    </h2>

                    <div id="collapseTablespaces"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingTablespaces"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <h5>¿Qué se hizo?</h5>

                            <p>
                                Se inspeccionaron los tablespaces existentes de Oracle y posteriormente
                                se crearon dos tablespaces destinados exclusivamente a los objetos
                                de la aplicación:

                            </p>

                            <div class="row g-3 mb-4">

                                <div class="col-md-6">

                                    <div class="card h-100 border-primary">

                                        <div class="card-body">

                                            <h5 class="text-primary">
                                                <i class="bi bi-database me-2"></i>
                                                APP_DATA
                                            </h5>

                                            <p>
                                                Destinado al almacenamiento de las
                                                <strong>tablas y datos empresariales</strong>.
                                            </p>

                                            <ul>
                                                <li>Tamaño inicial: 50 MB</li>
                                                <li>Incremento automático: 10 MB</li>
                                                <li>Tamaño máximo: 500 MB</li>
                                                <li>Extents administrados localmente</li>
                                                <li>Segment Space Management automático</li>
                                            </ul>

                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-6">

                                    <div class="card h-100 border-success">

                                        <div class="card-body">

                                            <h5 class="text-success">
                                                <i class="bi bi-list-columns-reverse me-2"></i>
                                                APP_INDEX
                                            </h5>

                                            <p>
                                                Destinado exclusivamente al almacenamiento
                                                de los <strong>índices</strong>.
                                            </p>

                                            <ul>
                                                <li>Tamaño inicial: 50 MB</li>
                                                <li>Incremento automático: 10 MB</li>
                                                <li>Tamaño máximo: 500 MB</li>
                                                <li>Administración local de extents</li>
                                                <li>Segment Space Management automático</li>
                                            </ul>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <h5>¿Por qué se separaron los datos y los índices?</h5>

                            <p>
                                La separación permite administrar de forma independiente el espacio
                                utilizado por los datos y por las estructuras de indexación.
                                Esto facilita tareas de administración, monitoreo y crecimiento
                                de almacenamiento.
                            </p>

                            <div class="alert alert-success">

                                <strong>Decisión de diseño:</strong>

                                <br>

                                <code>APP_DATA</code> → tablas y datos

                                <br>

                                <code>APP_INDEX</code> → índices

                                <br><br>

                                De esta forma se evita concentrar todos los objetos de aplicación
                                en los tablespaces internos de Oracle como <code>SYSTEM</code> y
                                <code>SYSAUX</code>.
                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     3. OMF
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingOMF">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseOMF"
                                aria-expanded="false">

                            <i class="bi bi-folder2-open me-2"></i>
                            3. Administración de los archivos físicos

                        </button>

                    </h2>

                    <div id="collapseOMF"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingOMF"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <h5>¿Qué se hizo?</h5>

                            <p>
                                El script comprueba si Oracle tiene configurado
                                <code>DB_CREATE_FILE_DEST</code>.
                                Si está disponible, se utiliza
                                <strong>Oracle Managed Files (OMF)</strong>.
                                En caso contrario, se utiliza una ruta definida manualmente.
                            </p>

                            <h5>¿Por qué?</h5>

                            <p>
                                Esto permite que la creación de los tablespaces no dependa
                                completamente de una ruta física específica del servidor.
                                Cuando OMF está disponible, Oracle administra la ubicación
                                y nombres de los archivos físicos.
                            </p>

                            <div class="alert alert-secondary">

                                <strong>Resultado:</strong>

                                <br>

                                El script puede adaptarse a diferentes instalaciones de Oracle
                                sin modificar necesariamente toda la definición de almacenamiento.

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     4. USUARIOS
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingUsuarios">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseUsuarios"
                                aria-expanded="false">

                            <i class="bi bi-people-fill me-2"></i>
                            4. Creación de esquemas y separación por áreas

                        </button>

                    </h2>

                    <div id="collapseUsuarios"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingUsuarios"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <h5>¿Qué se hizo?</h5>

                            <p>
                                Se crearon cuatro usuarios de Oracle que funcionan también como
                                <strong>esquemas independientes</strong>:

                            </p>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <strong>ESPH_RESIDUOS</strong>
                                        <p class="mb-0">
                                            Sistemas relacionados con gestión de residuos.
                                        </p>
                                        <small class="text-muted">
                                            SGA y SRR
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <strong>ESPH_ENERGIA</strong>
                                        <p class="mb-0">
                                            Sistemas relacionados con energía eléctrica.
                                        </p>
                                        <small class="text-muted">
                                            SRED y SALP
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <strong>ESPH_AGUA</strong>
                                        <p class="mb-0">
                                            Sistemas relacionados con agua potable.
                                        </p>
                                        <small class="text-muted">
                                            SCDA y SHH
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100">
                                        <strong>ESPH_TIC</strong>
                                        <p class="mb-0">
                                            Sistemas relacionados con tecnología de información.
                                        </p>
                                        <small class="text-muted">
                                            SGTI y SAMS
                                        </small>
                                    </div>
                                </div>

                            </div>

                            <hr>

                            <h5>¿Por qué utilizar esquemas separados?</h5>

                            <p>
                                La separación por esquemas permite aplicar una división lógica
                                de responsabilidades sobre los datos. Cada área posee sus propias
                                tablas y objetos, evitando concentrar toda la información empresarial
                                en un único esquema.
                            </p>

                            <div class="alert alert-info">

                                <strong>Principio aplicado: separación de responsabilidades.</strong>

                                <br>

                                Cada dominio empresarial mantiene sus propios objetos de datos,
                                mientras Oracle conserva el control administrativo general
                                de la instancia.
                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     5. PRIVILEGIOS
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingPrivilegios">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapsePrivilegios"
                                aria-expanded="false">

                            <i class="bi bi-shield-lock-fill me-2"></i>
                            5. Control de privilegios

                        </button>

                    </h2>

                    <div id="collapsePrivilegios"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingPrivilegios"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <h5>¿Qué se hizo?</h5>

                            <p>
                                En lugar de otorgar privilegios administrativos generales,
                                cada esquema recibió únicamente privilegios explícitos para
                                las operaciones necesarias dentro de Oracle.
                            </p>

                            <div class="table-responsive">

                                <table class="table table-bordered align-middle">

                                    <thead class="table-light">

                                        <tr>
                                            <th>Privilegio</th>
                                            <th>Propósito</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <td><code>CREATE SESSION</code></td>
                                            <td>
                                                Permite al esquema establecer una sesión
                                                con Oracle.
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><code>CREATE TABLE</code></td>
                                            <td>
                                                Permite crear tablas propias del esquema.
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><code>CREATE INDEX</code></td>
                                            <td>
                                                Permite crear índices necesarios para
                                                optimizar consultas.
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><code>CREATE VIEW</code></td>
                                            <td>
                                                Permite definir vistas para representar
                                                información mediante consultas.
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><code>CREATE SEQUENCE</code></td>
                                            <td>
                                                Permite utilizar secuencias para generación
                                                controlada de identificadores.
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><code>CREATE SYNONYM</code></td>
                                            <td>
                                                Permite crear nombres alternativos para
                                                objetos cuando sea necesario.
                                            </td>
                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                            <h5>¿Por qué?</h5>

                            <p>
                                Esta configuración evita otorgar privilegios excesivos como
                                <code>DBA</code> o permisos administrativos completos.
                                La intención es aplicar el principio de
                                <strong>mínimo privilegio</strong>.
                            </p>

                            <div class="alert alert-warning">

                                <strong>Importante:</strong>

                                Los privilegios otorgados permiten administrar objetos propios
                                del esquema, pero no convierten a estos usuarios en
                                administradores de toda la base de datos.

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     6. MODELO RESIDUOS
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingResiduos">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseResiduos">

                            <i class="bi bi-recycle me-2"></i>
                            6. Modelo de datos — Gestión de Residuos

                        </button>

                    </h2>

                    <div id="collapseResiduos"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingResiduos"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <p>
                                El esquema <code>ESPH_RESIDUOS</code> representa dos dominios:
                                gestión de centros de acopio y operación de rutas de recolección.
                            </p>

                            <div class="row">

                                <div class="col-md-6">

                                    <h6>Tablas principales</h6>

                                    <ul>
                                        <li>centro_acopio</li>
                                        <li>tipo_residuo</li>
                                        <li>ingreso_residuo</li>
                                        <li>ruta</li>
                                        <li>vehiculo</li>
                                        <li>ejecucion_ruta</li>
                                    </ul>

                                </div>

                                <div class="col-md-6">

                                    <h6>Relaciones relevantes</h6>

                                    <ul>
                                        <li>Centro → Ingresos</li>
                                        <li>Tipo de residuo → Ingresos</li>
                                        <li>Ruta → Ejecuciones</li>
                                        <li>Vehículo → Ejecuciones</li>
                                    </ul>

                                </div>

                            </div>

                            <hr>

                            <h5>¿Por qué se utilizaron claves foráneas?</h5>

                            <p>
                                Las claves foráneas impiden registrar relaciones hacia registros
                                inexistentes. Por ejemplo, un ingreso de residuos debe asociarse
                                con un centro de acopio y un tipo de residuo válidos.
                            </p>

                            <p>
                                Esto protege la <strong>integridad referencial</strong> de los datos.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     7. ENERGIA
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingEnergia">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseEnergia">

                            <i class="bi bi-lightning-charge-fill me-2"></i>
                            7. Modelo de datos — Energía Eléctrica

                        </button>

                    </h2>

                    <div id="collapseEnergia"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingEnergia"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <p>
                                El esquema <code>ESPH_ENERGIA</code> representa elementos
                                relacionados con la distribución eléctrica y el alumbrado público.
                            </p>

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <h6>Distribución eléctrica</h6>

                                    <ul>
                                        <li>subestacion</li>
                                        <li>circuito</li>
                                        <li>medidor</li>
                                        <li>lectura_medidor</li>
                                    </ul>

                                </div>

                                <div class="col-md-6">

                                    <h6>Alumbrado público</h6>

                                    <ul>
                                        <li>luminaria</li>
                                        <li>orden_mant_luminaria</li>
                                    </ul>

                                </div>

                            </div>

                            <hr>

                            <p>
                                Las relaciones entre subestaciones, circuitos y medidores permiten
                                representar la estructura jerárquica de la distribución eléctrica.
                                De forma similar, las órdenes de mantenimiento se relacionan
                                directamente con las luminarias.
                            </p>

                            <div class="alert alert-success">

                                <strong>Justificación:</strong>

                                Esta estructura evita almacenar repetidamente información de una
                                subestación o circuito en cada registro de medición y permite
                                mantener una relación normalizada entre las entidades.

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     8. AGUA
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingAgua">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseAgua">

                            <i class="bi bi-droplet-fill me-2"></i>
                            8. Modelo de datos — Agua Potable

                        </button>

                    </h2>

                    <div id="collapseAgua"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingAgua"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <p>
                                El esquema <code>ESPH_AGUA</code> representa infraestructura
                                de potabilización, almacenamiento, distribución y control
                                de hidrantes.
                            </p>

                            <div class="row">

                                <div class="col-md-6">

                                    <h6>Infraestructura</h6>

                                    <ul>
                                        <li>planta_potabilizadora</li>
                                        <li>tanque</li>
                                        <li>zona_distribucion</li>
                                    </ul>

                                </div>

                                <div class="col-md-6">

                                    <h6>Operación y medición</h6>

                                    <ul>
                                        <li>conexion</li>
                                        <li>lectura_agua</li>
                                        <li>hidrante</li>
                                        <li>inspeccion_hidrante</li>
                                    </ul>

                                </div>

                            </div>

                            <hr>

                            <h5>Justificación del modelo</h5>

                            <p>
                                Las entidades se separan para evitar duplicidad de información.
                                Por ejemplo, una zona de distribución puede contener múltiples
                                conexiones e hidrantes sin necesidad de repetir los datos de
                                la zona en cada registro.
                            </p>

                            <p>
                                Las inspecciones también se mantienen como registros históricos
                                relacionados con el hidrante correspondiente.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     9. TIC
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingTIC">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseTIC">

                            <i class="bi bi-pc-display-horizontal me-2"></i>
                            9. Modelo de datos — Tecnologías de Información

                        </button>

                    </h2>

                    <div id="collapseTIC"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingTIC"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <p>
                                El esquema <code>ESPH_TIC</code> representa activos tecnológicos,
                                licenciamiento, usuarios internos y atención de incidentes
                                mediante tickets.
                            </p>

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <h6>Gestión de activos</h6>

                                    <ul>
                                        <li>categoria_activo</li>
                                        <li>activo_ti</li>
                                        <li>licencia_software</li>
                                    </ul>

                                </div>

                                <div class="col-md-6">

                                    <h6>Mesa de servicio</h6>

                                    <ul>
                                        <li>categoria_ticket</li>
                                        <li>usuario_interno</li>
                                        <li>ticket</li>
                                    </ul>

                                </div>

                            </div>

                            <hr>

                            <h5>¿Por qué se relacionan los tickets con activos?</h5>

                            <p>
                                Un incidente puede estar asociado con un activo tecnológico
                                específico. La relación permite determinar qué equipo,
                                servidor o componente de infraestructura está involucrado
                                en una solicitud.
                            </p>

                            <p>
                                También se relaciona cada ticket con el usuario que lo solicita
                                y con su categoría correspondiente, permitiendo mantener
                                trazabilidad de la atención.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     10. INTEGRIDAD
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingIntegridad">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseIntegridad">

                            <i class="bi bi-shield-check me-2"></i>
                            10. Integridad y validación de los datos

                        </button>

                    </h2>

                    <div id="collapseIntegridad"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingIntegridad"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <h5>¿Qué se hizo?</h5>

                            <p>
                                El modelo utiliza diferentes mecanismos nativos de Oracle
                                para restringir los valores que pueden almacenarse.
                            </p>

                            <div class="table-responsive">

                                <table class="table table-bordered">

                                    <thead class="table-light">

                                        <tr>
                                            <th>Mecanismo</th>
                                            <th>Uso</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <tr>
                                            <td>PRIMARY KEY</td>
                                            <td>
                                                Identifica de forma única cada registro.
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>FOREIGN KEY</td>
                                            <td>
                                                Mantiene relaciones válidas entre tablas.
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>UNIQUE</td>
                                            <td>
                                                Evita duplicidad en valores que deben ser únicos.
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>NOT NULL</td>
                                            <td>
                                                Obliga a proporcionar información requerida.
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>CHECK</td>
                                            <td>
                                                Restringe valores a dominios válidos.
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>IDENTITY</td>
                                            <td>
                                                Genera automáticamente identificadores.
                                            </td>
                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                            <h5>¿Por qué?</h5>

                            <p>
                                La validación directamente en el modelo de datos proporciona
                                una segunda barrera de protección frente a información
                                inconsistente.
                            </p>

                            <div class="alert alert-info">

                                <strong>Ejemplo:</strong>

                                <br>

                                La tabla <code>vehiculo</code> utiliza una restricción
                                <code>UNIQUE</code> sobre la placa. Por lo tanto, Oracle
                                impide registrar dos vehículos con la misma placa.

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     11. INDICES
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingIndices">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseIndices">

                            <i class="bi bi-speedometer2 me-2"></i>
                            11. Optimización mediante índices

                        </button>

                    </h2>

                    <div id="collapseIndices"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingIndices"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <p>
                                Además de los índices generados por las claves primarias y
                                restricciones <code>UNIQUE</code>, se crearon índices adicionales
                                sobre columnas utilizadas frecuentemente para búsquedas y
                                relaciones.
                            </p>

                            <h5>Ejemplos</h5>

                            <ul>

                                <li>
                                    <code>idx_ir_centro</code> →
                                    búsquedas de ingresos por centro de acopio.
                                </li>

                                <li>
                                    <code>idx_ir_fecha</code> →
                                    consultas de ingresos por fecha.
                                </li>

                                <li>
                                    <code>idx_lec_med</code> →
                                    búsquedas de lecturas por medidor.
                                </li>

                                <li>
                                    <code>idx_lec_fecha</code> →
                                    consultas históricas por fecha.
                                </li>

                                <li>
                                    <code>idx_tkt_estado</code> →
                                    filtrado de tickets según estado.
                                </li>

                                <li>
                                    <code>idx_tkt_fecha</code> →
                                    consultas de tickets por fecha.
                                </li>

                            </ul>

                            <h5>¿Por qué?</h5>

                            <p>
                                Los índices permiten que Oracle encuentre registros mediante
                                estructuras auxiliares sin tener que recorrer necesariamente
                                toda la tabla.
                            </p>

                            <div class="alert alert-warning">

                                <strong>Decisión:</strong>

                                No se indexaron indiscriminadamente todas las columnas.
                                Se seleccionaron principalmente columnas utilizadas en
                                relaciones, búsquedas, filtros y consultas temporales.

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     12. DATOS DE PRUEBA
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingDatos">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseDatos">

                            <i class="bi bi-database-fill-add me-2"></i>
                            12. Carga de datos de prueba

                        </button>

                    </h2>

                    <div id="collapseDatos"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingDatos"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <h5>¿Qué se hizo?</h5>

                            <p>
                                Después de crear la estructura se introdujeron datos de prueba
                                representativos de las diferentes áreas empresariales.
                            </p>

                            <p>
                                Por ejemplo:
                            </p>

                            <ul>
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

                            <h5>¿Por qué?</h5>

                            <p>
                                Los datos de prueba permiten verificar que las tablas,
                                relaciones, restricciones e índices funcionen correctamente
                                antes de utilizar información real.
                            </p>

                            <div class="alert alert-success">

                                <strong>Importante:</strong>

                                Los datos incluidos en el script son datos demostrativos.
                                No deben interpretarse como información real de ESPH S.A.

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     13. VERIFICACION
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingVerificacion">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseVerificacion">

                            <i class="bi bi-clipboard2-check-fill me-2"></i>
                            13. Verificación posterior a la implementación

                        </button>

                    </h2>

                    <div id="collapseVerificacion"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingVerificacion"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <p>
                                El script no se limita a crear los objetos. También realiza
                                consultas de verificación después de la instalación.
                            </p>

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <div class="border rounded p-3 h-100">

                                        <h6>
                                            <i class="bi bi-person-check me-2"></i>
                                            Usuarios
                                        </h6>

                                        <p class="mb-0">
                                            Se comprueba el estado de las cuentas,
                                            tablespaces y fecha de creación.
                                        </p>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="border rounded p-3 h-100">

                                        <h6>
                                            <i class="bi bi-key me-2"></i>
                                            Privilegios
                                        </h6>

                                        <p class="mb-0">
                                            Se consultan los privilegios otorgados
                                            a cada esquema.
                                        </p>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="border rounded p-3 h-100">

                                        <h6>
                                            <i class="bi bi-table me-2"></i>
                                            Tablas
                                        </h6>

                                        <p class="mb-0">
                                            Se verifica la cantidad de tablas existentes
                                            por área de negocio.
                                        </p>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="border rounded p-3 h-100">

                                        <h6>
                                            <i class="bi bi-bar-chart-line me-2"></i>
                                            Espacio
                                        </h6>

                                        <p class="mb-0">
                                            Se revisa el espacio utilizado y disponible
                                            en APP_DATA y APP_INDEX.
                                        </p>

                                    </div>

                                </div>

                            </div>

                            <hr>

                            <h5>¿Por qué verificar después de crear?</h5>

                            <p>
                                Porque una ejecución exitosa del script no garantiza por sí sola
                                que todos los objetos hayan quedado configurados como se esperaba.
                                Las consultas finales permiten comprobar el estado real de Oracle.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     14. RESUMEN
                ================================================== -->

                <div class="accordion-item">

                    <h2 class="accordion-header" id="headingResumen">

                        <button class="accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseResumen">

                            <i class="bi bi-diagram-3-fill me-2"></i>
                            14. Resultado final de la arquitectura

                        </button>

                    </h2>

                    <div id="collapseResumen"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingResumen"
                         data-bs-parent="#accordionBD">

                        <div class="accordion-body">

                            <div class="text-center mb-4">

                                <h4>Arquitectura de almacenamiento</h4>

                            </div>

                            <div class="row text-center g-3">

                                <div class="col-md-4">
                                    <div class="p-3 border rounded bg-light">
                                        <strong>Oracle</strong>
                                        <br>
                                        Motor de base de datos
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 border rounded bg-light">
                                        <strong>APP_DATA</strong>
                                        <br>
                                        Datos empresariales
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 border rounded bg-light">
                                        <strong>APP_INDEX</strong>
                                        <br>
                                        Índices
                                    </div>
                                </div>

                            </div>

                            <hr>

                            <div class="row text-center g-3">

                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <strong>ESPH_RESIDUOS</strong>
                                        <br>
                                        <small>Residuos</small>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <strong>ESPH_ENERGIA</strong>
                                        <br>
                                        <small>Energía</small>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <strong>ESPH_AGUA</strong>
                                        <br>
                                        <small>Agua</small>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <strong>ESPH_TIC</strong>
                                        <br>
                                        <small>Tecnologías de Información</small>
                                    </div>
                                </div>

                            </div>

                            <div class="alert alert-primary mt-4 mb-0">

                                <strong>En resumen:</strong>

                                La base de datos fue estructurada para separar la información
                                empresarial por dominios, controlar el almacenamiento,
                                restringir los privilegios, mantener la integridad referencial,
                                optimizar las consultas y facilitar la administración futura
                                del entorno Oracle.

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


                  

          <?php endif; ?>

        <?php endforeach; ?>

      </div>

    </div>

  </section>

</main>

<?php include 'includes/footer.php'; ?>
