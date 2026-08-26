<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
requiereAdmin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ClienteRepository.php';

$slug = trim($_GET['c'] ?? '');
if (!$slug) { header('Location: clientes.php'); exit; }

$repo     = new ClienteRepository(db());
$cliente  = $repo->obtenerPorSlug($slug);
if (!$cliente) { header('Location: clientes.php'); exit; }

$proyectos = $repo->listarPorCliente($cliente['id'], $LANG);

$pageTitleKey = 'clientes.pagetitle';
include 'includes/header.php';
include 'includes/navbar.php';
?>




<main class="flex-grow-1">
  <section class="section">
    <div class="container">

      <!-- Breadcrumb -->
      <nav class="mb-4" style="font-family:var(--font-mono);font-size:0.8rem;color:var(--text-muted)">
        <a href="clientes.php" style="color:var(--text-muted);text-decoration:none">
          <i class="fa-solid fa-briefcase me-1"></i>Clientes
        </a>
        <span class="mx-2">›</span>
        <span style="color:var(--text)"><?php echo htmlspecialchars($cliente['nombre']); ?></span>
      </nav>

      <!-- Encabezado del cliente -->
      <div class="row align-items-start g-5 mb-5">
        <div class="col-lg-7">
          <span class="section-eyebrow">
            <i class="fa-solid fa-building me-2"></i>
            <?php echo htmlspecialchars($cliente['sector'] ?? ''); ?>
          </span>
          <h2 class="section-title"><?php echo htmlspecialchars($cliente['nombre']); ?></h2>
          <p style="color:var(--text);line-height:1.75;font-size:1.02rem">
            <?php echo nl2br(htmlspecialchars($cliente['descripcion'] ?? '')); ?>
          </p>
        </div>

        <?php if ($cliente['organigrama']): ?>
        <div class="col-lg-5">
          <div style="border:1px solid var(--border);border-radius:14px;overflow:hidden;background:var(--surface)">
            <div style="padding:0.6rem 1rem;border-bottom:1px solid var(--border);font-family:var(--font-mono);font-size:0.72rem;color:var(--text-muted)">
              <i class="fa-solid fa-sitemap me-2"></i>Organigrama institucional 2026
            </div>
            <img src="<?php echo htmlspecialchars($cliente['organigrama']); ?>"
                 alt="Organigrama <?php echo htmlspecialchars($cliente['nombre']); ?>"
                 style="width:100%;display:block">
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Separador -->
      <div style="border-top:1px solid var(--border);margin-bottom:3rem"></div>

      <!-- Arquitectura de BD -->
      <span class="section-eyebrow">
        <i class="fa-solid fa-database me-2"></i>Base de datos diseñada
      </span>
      <h3 class="section-title" style="font-size:1.6rem">Arquitectura Oracle 21c XE</h3>
      <p style="color:var(--text-muted);margin-bottom:2.5rem">
        Base de datos multiesquema desplegada en XEPDB1, con tablespaces separados para datos e índices
        y un esquema dedicado por cada área de negocio.
      </p>

      <!-- Tablespaces -->
      <h5 style="font-family:var(--font-mono);color:var(--risk-mid);margin-bottom:1rem">
        <i class="fa-solid fa-layer-group me-2"></i>Arquitectura de almacenamiento
      </h5>
      <div class="row g-3 mb-5">
        <?php
        $tablespaces = [
            ['nombre'=>'SYSTEM',    'desc'=>'Estructuras fundamentales de Oracle. Reservado, no modificado.',          'icono'=>'fa-gear',          'tipo'=>'Oracle'],
            ['nombre'=>'SYSAUX',    'desc'=>'Componentes auxiliares de Oracle. Reservado, no modificado.',             'icono'=>'fa-gear',          'tipo'=>'Oracle'],
            ['nombre'=>'UNDO',      'desc'=>'Información de rollback y transacciones. Gestionado automáticamente.',    'icono'=>'fa-rotate-left',   'tipo'=>'Oracle'],
            ['nombre'=>'TEMP',      'desc'=>'Operaciones temporales, sorts y joins. Compartido entre esquemas.',       'icono'=>'fa-clock',         'tipo'=>'Oracle'],
            ['nombre'=>'APP_DATA',  'desc'=>'Datos empresariales. 50 MB iniciales, AUTOEXTEND hasta 500 MB.',         'icono'=>'fa-table',         'tipo'=>'Aplicación'],
            ['nombre'=>'APP_INDEX', 'desc'=>'Índices empresariales. 50 MB iniciales, AUTOEXTEND hasta 500 MB.',       'icono'=>'fa-magnifying-glass','tipo'=>'Aplicación'],
        ];
        foreach ($tablespaces as $ts):
            $esApp = $ts['tipo'] === 'Aplicación';
        ?>
        <div class="col-md-6 col-lg-4">
          <div style="background:var(--surface);border:1px solid <?php echo $esApp ? 'rgba(242,177,52,0.3)' : 'var(--border)'; ?>;border-radius:10px;padding:1rem 1.25rem">
            <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.5rem">
              <i class="fa-solid <?php echo $ts['icono']; ?>" style="color:<?php echo $esApp ? 'var(--risk-mid)' : 'var(--text-muted)'; ?>"></i>
              <code style="font-size:0.9rem;color:<?php echo $esApp ? 'var(--risk-mid)' : 'var(--text)'; ?>"><?php echo $ts['nombre']; ?></code>
              <span style="margin-left:auto;font-size:0.65rem;font-family:var(--font-mono);color:var(--text-muted);border:1px solid var(--border);border-radius:20px;padding:0.05rem 0.5rem"><?php echo $ts['tipo']; ?></span>
            </div>
            <p style="font-size:0.82rem;color:var(--text-muted);margin:0;line-height:1.5"><?php echo $ts['desc']; ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Esquemas / Áreas de negocio -->
      <h5 style="font-family:var(--font-mono);color:var(--risk-mid);margin-bottom:1rem">
        <i class="fa-solid fa-cubes me-2"></i>Esquemas por área de negocio
      </h5>
      <div class="row g-4 mb-5">
        <?php
        $esquemas = [
          [
            'usuario' => 'ESPH_RESIDUOS',
            'sistemas' => ['SGA — Sistema de Gestión de Acopio', 'SRR — Sistema de Rutas y Recolección'],
            'tablas'  => ['centro_acopio','tipo_residuo','ingreso_residuo','ruta','vehiculo','ejecucion_ruta'],
            'icono'   => 'fa-recycle',
            'area'    => 'Negocio de Residuos',
          ],
          [
            'usuario' => 'ESPH_ENERGIA',
            'sistemas' => ['SRED — Sistema de Red Eléctrica y Distribución', 'SALP — Sistema de Alumbrado Público'],
            'tablas'  => ['subestacion','circuito','medidor','lectura_medidor','luminaria','orden_mant_luminaria'],
            'icono'   => 'fa-bolt',
            'area'    => 'Negocio de Energía Eléctrica y Alumbrado Público',
          ],
          [
            'usuario' => 'ESPH_AGUA',
            'sistemas' => ['SCDA — Sistema de Control y Distribución de Agua', 'SHH — Sistema de Hidrantes y Presión Hídrica'],
            'tablas'  => ['planta_potabilizadora','tanque','zona_distribucion','conexion','lectura_agua','hidrante','inspeccion_hidrante'],
            'icono'   => 'fa-droplet',
            'area'    => 'Negocio Agua Potable e Hidrantes',
          ],
          [
            'usuario' => 'ESPH_TIC',
            'sistemas' => ['SGTI — Sistema de Gestión de Infraestructura TI', 'SAMS — Sistema de Atención y Mesa de Servicio'],
            'tablas'  => ['categoria_activo','activo_ti','licencia_software','categoria_ticket','usuario_interno','ticket'],
            'icono'   => 'fa-microchip',
            'area'    => 'Negocio de Tecnologías e Infocomunicaciones',
          ],
        ];
        foreach ($esquemas as $e):
        ?>
        <div class="col-md-6">
          <div class="service-card h-100">
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem">
              <i class="fa-solid <?php echo $e['icono']; ?>" style="font-size:1.3rem;color:var(--risk-mid)"></i>
              <div>
                <code style="font-size:0.85rem;color:var(--risk-mid)"><?php echo $e['usuario']; ?></code>
                <div style="font-size:0.72rem;color:var(--text-muted);font-family:var(--font-mono)"><?php echo $e['area']; ?></div>
              </div>
            </div>

            <!-- Sistemas -->
            <div style="margin-bottom:0.85rem">
              <?php foreach ($e['sistemas'] as $s): ?>
                <div style="font-size:0.78rem;font-family:var(--font-mono);color:var(--text-muted);padding:0.2rem 0">
                  <i class="fa-solid fa-server me-2" style="color:var(--risk-mid);font-size:0.65rem"></i><?php echo $s; ?>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Tablas -->
            <div style="display:flex;flex-wrap:wrap;gap:0.3rem">
              <?php foreach ($e['tablas'] as $t): ?>
                <span style="font-size:0.65rem;font-family:var(--font-mono);background:rgba(242,177,52,0.07);color:var(--risk-mid);border:1px solid rgba(242,177,52,0.18);border-radius:4px;padding:0.1rem 0.45rem">
                  <?php echo $t; ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Decisiones de diseño -->
      <h5 style="font-family:var(--font-mono);color:var(--risk-mid);margin-bottom:1rem">
        <i class="fa-solid fa-lightbulb me-2"></i>Decisiones de diseño
      </h5>
      <div class="row g-3">
        <?php
        $decisiones = [
          ['titulo'=>'Separación datos / índices',   'desc'=>'APP_DATA para segmentos de tabla y APP_INDEX para todos los índices, incluyendo PKs con USING INDEX TABLESPACE APP_INDEX.'],
          ['titulo'=>'Un esquema por negocio',       'desc'=>'Aislamiento de responsabilidades y cuotas individuales (200 MB datos, 100 MB índices) por área, evitando contaminación entre negocios.'],
          ['titulo'=>'IDENTITY en lugar de secuencias', 'desc'=>'PKs con GENERATED ALWAYS AS IDENTITY. Solo se creó una secuencia externa (seq_folio_ingreso) para numeración de folios de negocio.'],
          ['titulo'=>'INSERT con SELECT para FKs',   'desc'=>'Los inserts de datos de prueba usan SELECT para obtener el ID padre por nombre, evitando dependencia de que IDENTITY comience en 1.'],
          ['titulo'=>'Oracle Managed Files',         'desc'=>'El script detecta automáticamente si DB_CREATE_FILE_DEST está configurado y ajusta el CREATE TABLESPACE correspondientemente.'],
          ['titulo'=>'Contraseñas en tiempo de ejecución', 'desc'=>'Los passwords se solicitan con ACCEPT ... HIDE al ejecutar el script, nunca quedan almacenados en texto plano.'],
        ];
        foreach ($decisiones as $d):
        ?>
        <div class="col-md-6">
          <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:1rem 1.25rem">
            <div style="font-size:0.82rem;font-weight:600;color:var(--text);margin-bottom:0.35rem">
              <i class="fa-solid fa-check me-2" style="color:var(--risk-mid)"></i><?php echo $d['titulo']; ?>
            </div>
            <p style="font-size:0.8rem;color:var(--text-muted);margin:0;line-height:1.5"><?php echo $d['desc']; ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
