<?php $usuarioSesionNav = $_SESSION['usuario'] ?? null; ?>
<header class="site-nav">
    <nav class="navbar navbar-expand-lg">
        <div class="container">

            <a class="navbar-brand" href="index.php">
                <span class="brand-mark" aria-hidden="true">
                    <span class="brand-cell c-low"></span>
                    <span class="brand-cell c-mid"></span>
                    <span class="brand-cell c-high"></span>
                    <span class="brand-cell c-mid"></span>
                </span>
                <span class="brand-text">RiskGuard<span class="brand-text-accent">.</span></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-label="<?php echo t('nav.toggler'); ?>">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php#servicios"><?php echo t('nav.servicios'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#metodologia"><?php echo t('nav.metodologia'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#normas"><?php echo t('nav.normas'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#equipo"><?php echo t('nav.equipo'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contacto"><?php echo t('nav.contacto'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="evaluacion-riesgos.php"><?php echo t('nav.evaluacion'); ?></a></li>
                    <?php if ($usuarioSesionNav && $usuarioSesionNav['rol'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="monitor-instancias.php">Monitor de Salud</a></li>
                    <?php endif; ?>
                </ul>
                <button id="theme-toggle" type="button" class="theme-toggle" aria-label="Cambiar modo día/noche">
                    <i class="fa-solid fa-sun theme-icon-light"></i>
                    <i class="fa-solid fa-moon theme-icon-dark"></i>
                </button>
                <div class="lang-switch" role="group" aria-label="Language / Idioma">
                    <a href="<?php echo htmlspecialchars(langSwitchUrl('en')); ?>" class="lang-switch-option<?php echo $LANG === 'en' ? ' active' : ''; ?>">EN</a>
                    <span class="lang-switch-sep">/</span>
                    <a href="<?php echo htmlspecialchars(langSwitchUrl('es')); ?>" class="lang-switch-option<?php echo $LANG === 'es' ? ' active' : ''; ?>">ES</a>
                </div>

                <?php if ($usuarioSesionNav): ?>
                    <?php if ($usuarioSesionNav['rol'] === 'admin'): ?>
                        <a href="admin-evaluaciones.php" class="btn btn-ghost">
                            <i class="fa-solid fa-user-shield me-2"></i>Panel admin
                        </a>
                    <?php else: ?>
                        <a href="panel-empresa.php" class="btn btn-ghost">
                            <i class="fa-solid fa-building me-2"></i><?php echo htmlspecialchars($usuarioSesionNav['empresa_nombre'] ?? $usuarioSesionNav['nombre_usuario']); ?>
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-cta">
                        <?php echo t('nav.salir') !== 'nav.salir' ? t('nav.salir') : 'Cerrar sesión'; ?>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-ghost">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Iniciar sesión
                    </a>
                    <a href="index.php#contacto" class="btn btn-cta">
                        <?php echo t('nav.cta'); ?>
                        <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </nav>
</header>