    <footer class="site-footer">
        <div class="container">

            <div class="footer-grid">

                <div class="footer-about">
                    <a class="brand-text footer-brand" href="index.php">RiskGuard<span class="brand-text-accent">.</span></a>
                    <p class="footer-tagline">
                        <?php echo t('footer.tagline'); ?>
                    </p>
                </div>

                <div class="footer-col">
                    <h6><?php echo t('footer.nav'); ?></h6>
                    <ul>
                        <li><a href="index.php#servicios"><?php echo t('nav.servicios'); ?></a></li>
                        <li><a href="index.php#metodologia"><?php echo t('nav.metodologia'); ?></a></li>
                        <li><a href="index.php#normas"><?php echo t('nav.normas'); ?></a></li>
                        <li><a href="index.php#equipo"><?php echo t('nav.equipo'); ?></a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h6><?php echo t('footer.equipo'); ?></h6>
                    <ul class="footer-team">
                        <li>Julissa Solano Valverde</li>
                        <li>Isaac Naranjo Cerdas</li>
                        <li>Nicolás Zárate Hernández</li>
                        <li>Hermann Hidalgo Araya</li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h6><?php echo t('footer.contacto'); ?></h6>
                    <ul>
                        <li><a href="mailto:contacto@riskguard.cr">contacto@riskguard.cr</a></li>
                        <li><a href="tel:+50600000000">+506 0000-0000</a></li>
                        <li>Costa Rica</li>
                    </ul>
                </div>

            </div>

            <div class="footer-bottom">
                <small>&copy; <?php echo date("Y"); ?> <?php echo t('footer.copyright'); ?></small>
            </div>

        </div>
    </footer>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
