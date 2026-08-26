<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/includes/auth.php';
requiereAdmin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ClienteRepository.php';

$pageTitleKey = 'clientes.pagetitle';
include 'includes/header.php';
include 'includes/navbar.php';

$repo      = new ClienteRepository(db());
$proyectos = $repo->listarProyectos($LANG);
?>






<main class="flex-grow-1">
  <section class="section">
    <div class="container">

      <span class="section-eyebrow">
        <i class="fa-solid fa-briefcase me-2"></i><?php echo t('clientes.eyebrow'); ?>
      </span>
      <h2 class="section-title"><?php echo t('clientes.title'); ?></h2>
      <p class="section-lead mb-5" style="color:var(--text)"><?php echo t('clientes.lead'); ?></p>
		
      <?php foreach ($proyectos as $p): ?> 
		  <?php $tags = array_filter(array_map('trim', explode(',', $p['etiquetas'] ?? ''))); ?> 
		
		  <?php $slug = $p['cliente_slug'] ?? null; ?>
		
		  <div class="col-md-6 col-lg-4"> 
		    <div class="service-card h-100 d-flex flex-column <?php echo $slug ? 'service-card--link' : ''; ?>"
		         <?php if ($slug): ?>
		             style="cursor:pointer"
		             onclick="window.location='cliente-detalle.php?c=<?php echo htmlspecialchars($slug); ?>'"
		         <?php endif; ?>>

              <i class="fa-solid <?php echo htmlspecialchars($p['icono']); ?> mb-3" style="font-size:1.6rem;color:var(--risk-mid)"></i>

              <?php if ($p['cliente_nombre']): ?>
                <span class="cliente-badge mb-2">
                  <i class="fa-solid fa-building me-1"></i>
                  <?php echo htmlspecialchars($p['cliente_nombre']); ?>
                  <?php if ($p['cliente_sector']): ?>
                    · <?php echo htmlspecialchars($p['cliente_sector']); ?>
                  <?php endif; ?>
                </span>
              <?php endif; ?>

              <h5 class="mb-2"><?php echo htmlspecialchars($p['titulo']); ?></h5>
             <p class="section-lead mb-5" style="color:var(--text)"><?php echo htmlspecialchars($p['descripcion']); ?></p>

              <?php if ($tags): ?>
                <div class="cliente-tags mt-3">
                  <?php foreach ($tags as $tag): ?>
                    <span class="cliente-tag"><?php echo htmlspecialchars($tag); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <?php if ($p['url_demo']): ?>
                <a href="<?php echo htmlspecialchars($p['url_demo']); ?>"
                   class="btn btn-sm btn-ghost mt-3" target="_blank" rel="noopener">
                  <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>
                  <?php echo t('clientes.ver_demo'); ?>
                </a>
              <?php endif; ?>

            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  </section>
</main>

<?php include 'includes/footer.php'; ?>
