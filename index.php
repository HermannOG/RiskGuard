<?php $pageTitleKey = "home.pagetitle"; include "includes/header.php"; ?>
<?php include "includes/navbar.php"; ?>

<main class="flex-grow-1">

    <!-- HERO -->
    <section class="hero">
        <div class="container hero-grid">

            <div class="hero-copy">
                <span class="eyebrow"><i class="fa-solid fa-shield-halved me-2"></i><?php echo t('home.eyebrow'); ?></span>
                <h1 class="hero-title">
                    <?php echo t('home.hero.title1'); ?><br>
                    <?php echo t('home.hero.title2'); ?><br>
                    <span class="hero-title-accent"><?php echo t('home.hero.title3'); ?></span>
                </h1>
                <p class="hero-lead">
                    <?php echo t('home.hero.lead'); ?>
                </p>
                <div class="hero-actions">
                    <a href="#contacto" class="btn btn-cta btn-lg"><?php echo t('nav.cta'); ?></a>
                    <a href="evaluacion-riesgos.php" class="btn btn-ghost btn-lg"><?php echo t('home.hero.btn.evaluar'); ?></a>
                </div>
            </div>

            <div class="hero-visual" aria-hidden="true">
                <div class="matrix-card">
                    <div class="matrix-card-head">
                        <span><?php echo t('home.matrix.title'); ?></span>
                        <span class="matrix-card-head-sub"><?php echo t('home.matrix.subtitle'); ?></span>
                    </div>
                    <div class="risk-matrix">
                        <div class="cell low"></div><div class="cell low"></div><div class="cell mid"></div><div class="cell high"></div><div class="cell high"></div>
                        <div class="cell low"></div><div class="cell mid"></div><div class="cell mid"></div><div class="cell high"></div><div class="cell high"></div>
                        <div class="cell low"></div><div class="cell mid"></div><div class="cell high"></div><div class="cell high"></div><div class="cell crit"></div>
                        <div class="cell mid"></div><div class="cell mid"></div><div class="cell high"></div><div class="cell crit"></div><div class="cell crit"></div>
                        <div class="cell mid"></div><div class="cell high"></div><div class="cell crit"></div><div class="cell crit"></div><div class="cell crit"></div>
                    </div>
                    <div class="matrix-card-legend">
                        <span><i class="dot low"></i><?php echo t('home.matrix.low'); ?></span>
                        <span><i class="dot mid"></i><?php echo t('home.matrix.mid'); ?></span>
                        <span><i class="dot high"></i><?php echo t('home.matrix.high'); ?></span>
                        <span><i class="dot crit"></i><?php echo t('home.matrix.crit'); ?></span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- SERVICIOS -->
    <section id="servicios" class="section">
        <div class="container">
            <span class="section-eyebrow"><?php echo t('home.servicios.eyebrow'); ?></span>
            <h2 class="section-title"><?php echo t('home.servicios.title'); ?></h2>

            <div class="row g-4 mt-2">

                <div class="col-md-4">
                    <div class="service-card">
                        <i class="fa-solid fa-magnifying-glass-chart"></i>
                        <h5><?php echo t('home.servicios.s1.title'); ?></h5>
                        <p><?php echo t('home.servicios.s1.desc'); ?></p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card">
                        <i class="fa-solid fa-list-check"></i>
                        <h5><?php echo t('home.servicios.s2.title'); ?></h5>
                        <p><?php echo t('home.servicios.s2.desc'); ?></p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="service-card">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <h5><?php echo t('home.servicios.s3.title'); ?></h5>
                        <p><?php echo t('home.servicios.s3.desc'); ?></p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- METODOLOGIA -->
    <section id="metodologia" class="section section-alt">
        <div class="container">
            <span class="section-eyebrow"><?php echo t('home.metodologia.eyebrow'); ?></span>
            <h2 class="section-title"><?php echo t('home.metodologia.title'); ?></h2>

            <div class="cycle">
                <div class="cycle-step">
                    <span class="cycle-tag">01</span>
                    <h6><?php echo t('home.metodologia.c1.title'); ?></h6>
                    <p><?php echo t('home.metodologia.c1.desc'); ?></p>
                </div>
                <div class="cycle-step">
                    <span class="cycle-tag">02</span>
                    <h6><?php echo t('home.metodologia.c2.title'); ?></h6>
                    <p><?php echo t('home.metodologia.c2.desc'); ?></p>
                </div>
                <div class="cycle-step">
                    <span class="cycle-tag">03</span>
                    <h6><?php echo t('home.metodologia.c3.title'); ?></h6>
                    <p><?php echo t('home.metodologia.c3.desc'); ?></p>
                </div>
                <div class="cycle-step">
                    <span class="cycle-tag">04</span>
                    <h6><?php echo t('home.metodologia.c4.title'); ?></h6>
                    <p><?php echo t('home.metodologia.c4.desc'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- NORMAS -->
    <section id="normas" class="section">
        <div class="container">
            <span class="section-eyebrow"><?php echo t('home.normas.eyebrow'); ?></span>
            <h2 class="section-title"><?php echo t('home.normas.title'); ?></h2>

            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <div class="norm-card">
                        <span class="norm-code">ISO/IEC 27001</span>
                        <p><?php echo t('home.normas.n1.desc'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="norm-card">
                        <span class="norm-code">ISO/IEC 27002</span>
                        <p><?php echo t('home.normas.n2.desc'); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="norm-card">
                        <span class="norm-code">ISO/IEC 27007</span>
                        <p><?php echo t('home.normas.n3.desc'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- EQUIPO -->
    <section id="equipo" class="section section-alt">
        <div class="container">
            <span class="section-eyebrow"><?php echo t('home.equipo.eyebrow'); ?></span>
            <h2 class="section-title"><?php echo t('home.equipo.title'); ?></h2>

            <div class="row g-4 mt-2">
                <div class="col-6 col-md-3">
                    <div class="team-card">
                        <div class="team-avatar">JS</div>
                        <h6>Julissa Solano Valverde</h6>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="team-card">
                        <div class="team-avatar">IN</div>
                        <h6>Isaac Naranjo Cerdas</h6>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="team-card">
                        <div class="team-avatar">NZ</div>
                        <h6>Nicolás Zárate Hernández</h6>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="team-card">
                        <div class="team-avatar">HH</div>
                        <h6>Hermann Hidalgo Araya</h6>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACTO -->
    <section id="contacto" class="section">
        <div class="container">
            <div class="contact-box">
                <div>
                    <span class="section-eyebrow"><?php echo t('home.contacto.eyebrow'); ?></span>
                    <h2 class="section-title"><?php echo t('home.contacto.title'); ?></h2>
                    <p class="section-lead"><?php echo t('home.contacto.lead'); ?></p>
                </div>

                <form class="contact-form" method="post" action="#">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" placeholder="<?php echo t('home.contacto.ph.nombre'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="form-control" placeholder="<?php echo t('home.contacto.ph.email'); ?>" required>
                        </div>
                        <div class="col-12">
                            <input type="text" class="form-control" placeholder="<?php echo t('home.contacto.ph.org'); ?>">
                        </div>
                        <div class="col-12">
                            <textarea class="form-control" rows="4" placeholder="<?php echo t('home.contacto.ph.mensaje'); ?>"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-cta btn-lg w-100 w-md-auto"><?php echo t('home.contacto.btn'); ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

</main>

<?php include "includes/footer.php"; ?>
