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
                       SEPARADOR
                  ================================================== -->

                  <div
                    style="
                      border-top:1px solid var(--border);
                      margin-bottom:3rem;
                    "
                  ></div>


                  <!-- =================================================
                       ARQUITECTURA DE BASE DE DATOS
                  ================================================== -->

                  <span class="section-eyebrow">

                    <i class="fa-solid fa-database me-2"></i>

                    Base de datos diseñada

                  </span>

                  <h3
                    class="section-title"
                    style="font-size:1.6rem"
                  >
                    Arquitectura Oracle 21c XE
                  </h3>

                  <p
                    style="
                      color:var(--text-muted);
                      margin-bottom:2.5rem;
                    "
                  >

                    Base de datos multiesquema desplegada en XEPDB1,
                    con tablespaces separados para datos e índices
                    y un esquema dedicado por cada área de negocio.

                  </p>


                  <!-- =================================================
                       TABLESPACES
                  ================================================== -->

                  <h5
                    style="
                      font-family:var(--font-mono);
                      color:var(--risk-mid);
                      margin-bottom:1rem;
                    "
                  >

                    <i class="fa-solid fa-layer-group me-2"></i>

                    Arquitectura de almacenamiento

                  </h5>


                  <div class="row g-3 mb-5">

                    <?php foreach (
                        $detallesClientes[$slug]['tablespaces']
                        as $ts
                    ): ?>

                      <?php

                      $esApp = $ts['tipo'] === 'Aplicación';

                      ?>

                      <div class="col-md-6 col-lg-4">

                        <div
                          style="
                            background:var(--surface);
                            border:1px solid
                              <?php echo $esApp
                                  ? 'rgba(242,177,52,0.3)'
                                  : 'var(--border)'; ?>;
                            border-radius:10px;
                            padding:1rem 1.25rem;
                          "
                        >

                          <div
                            style="
                              display:flex;
                              align-items:center;
                              gap:0.6rem;
                              margin-bottom:0.5rem;
                            "
                          >

                            <i
                              class="fa-solid <?php echo htmlspecialchars($ts['icono']); ?>"
                              style="
                                color:<?php echo $esApp
                                    ? 'var(--risk-mid)'
                                    : 'var(--text-muted)'; ?>;
                              "
                            ></i>


                            <code
                              style="
                                font-size:0.9rem;
                                color:<?php echo $esApp
                                    ? 'var(--risk-mid)'
                                    : 'var(--text)'; ?>;
                              "
                            >
                              <?php echo htmlspecialchars($ts['nombre']); ?>
                            </code>


                            <span
                              style="
                                margin-left:auto;
                                font-size:0.65rem;
                                font-family:var(--font-mono);
                                color:var(--text-muted);
                                border:1px solid var(--border);
                                border-radius:20px;
                                padding:0.05rem 0.5rem;
                              "
                            >
                              <?php echo htmlspecialchars($ts['tipo']); ?>
                            </span>

                          </div>


                          <p
                            style="
                              font-size:0.82rem;
                              color:var(--text-muted);
                              margin:0;
                              line-height:1.5;
                            "
                          >

                            <?php echo htmlspecialchars($ts['desc']); ?>

                          </p>

                        </div>

                      </div>

                    <?php endforeach; ?>

                  </div>


                  <!-- =================================================
                       ESQUEMAS
                  ================================================== -->

                  <h5
                    style="
                      font-family:var(--font-mono);
                      color:var(--risk-mid);
                      margin-bottom:1rem;
                    "
                  >

                    <i class="fa-solid fa-cubes me-2"></i>

                    Esquemas por área de negocio

                  </h5>


                  <div class="row g-4 mb-5">

                    <?php foreach (
                        $detallesClientes[$slug]['esquemas']
                        as $e
                    ): ?>

                      <div class="col-md-6">

                        <div class="service-card h-100">

                          <!-- Encabezado del esquema -->

                          <div
                            style="
                              display:flex;
                              align-items:center;
                              gap:0.75rem;
                              margin-bottom:0.75rem;
                            "
                          >

                            <i
                              class="fa-solid <?php echo htmlspecialchars($e['icono']); ?>"
                              style="
                                font-size:1.3rem;
                                color:var(--risk-mid);
                              "
                            ></i>


                            <div>

                              <code
                                style="
                                  font-size:0.85rem;
                                  color:var(--risk-mid);
                                "
                              >
                                <?php echo htmlspecialchars($e['usuario']); ?>
                              </code>


                              <div
                                style="
                                  font-size:0.72rem;
                                  color:var(--text-muted);
                                  font-family:var(--font-mono);
                                "
                              >
                                <?php echo htmlspecialchars($e['area']); ?>
                              </div>

                            </div>

                          </div>


                          <!-- Sistemas -->

                          <div style="margin-bottom:0.85rem">

                            <?php foreach ($e['sistemas'] as $s): ?>

                              <div
                                style="
                                  font-size:0.78rem;
                                  font-family:var(--font-mono);
                                  color:var(--text-muted);
                                  padding:0.2rem 0;
                                "
                              >

                                <i
                                  class="fa-solid fa-server me-2"
                                  style="
                                    color:var(--risk-mid);
                                    font-size:0.65rem;
                                  "
                                ></i>

                                <?php echo htmlspecialchars($s); ?>

                              </div>

                            <?php endforeach; ?>

                          </div>


                          <!-- Tablas -->

                          <div
                            style="
                              display:flex;
                              flex-wrap:wrap;
                              gap:0.3rem;
                            "
                          >

                            <?php foreach ($e['tablas'] as $t): ?>

                              <span
                                style="
                                  font-size:0.65rem;
                                  font-family:var(--font-mono);
                                  background:rgba(242,177,52,0.07);
                                  color:var(--risk-mid);
                                  border:1px solid rgba(242,177,52,0.18);
                                  border-radius:4px;
                                  padding:0.1rem 0.45rem;
                                "
                              >

                                <?php echo htmlspecialchars($t); ?>

                              </span>

                            <?php endforeach; ?>

                          </div>

                        </div>

                      </div>

                    <?php endforeach; ?>

                  </div>


                  <!-- =================================================
                       DECISIONES DE DISEÑO
                  ================================================== -->

                  <h5
                    style="
                      font-family:var(--font-mono);
                      color:var(--risk-mid);
                      margin-bottom:1rem;
                    "
                  >

                    <i class="fa-solid fa-lightbulb me-2"></i>

                    Decisiones de diseño

                  </h5>


                  <div class="row g-3">

                    <?php foreach (
                        $detallesClientes[$slug]['decisiones']
                        as $d
                    ): ?>

                      <div class="col-md-6">

                        <div
                          style="
                            background:var(--surface);
                            border:1px solid var(--border);
                            border-radius:10px;
                            padding:1rem 1.25rem;
                          "
                        >

                          <div
                            style="
                              font-size:0.82rem;
                              font-weight:600;
                              color:var(--text);
                              margin-bottom:0.35rem;
                            "
                          >

                            <i
                              class="fa-solid fa-check me-2"
                              style="color:var(--risk-mid)"
                            ></i>

                            <?php echo htmlspecialchars($d['titulo']); ?>

                          </div>


                          <p
                            style="
                              font-size:0.8rem;
                              color:var(--text-muted);
                              margin:0;
                              line-height:1.5;
                            "
                          >

                            <?php echo htmlspecialchars($d['desc']); ?>

                          </p>

                        </div>

                      </div>

                    <?php endforeach; ?>

                  </div>


                  <!-- =================================================
                       CERRAR INFORMACIÓN
                  ================================================== -->

                  <div class="text-center mt-4">

                    <button
                      type="button"
                      class="btn btn-sm btn-ghost"
                      data-bs-toggle="collapse"
                      data-bs-target="#<?php echo $collapseId; ?>"
                      aria-expanded="true"
                    >

                      <i class="fa-solid fa-chevron-up me-1"></i>

                      Ocultar información

                    </button>

                  </div>

                </div>

              </div>

            </div>

          <?php endif; ?>

        <?php endforeach; ?>

      </div>

    </div>

  </section>

</main>

<?php include 'includes/footer.php'; ?>
