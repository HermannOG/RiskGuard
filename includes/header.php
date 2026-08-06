<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
date_default_timezone_set('America/Costa_Rica');
require_once __DIR__ . '/i18n.php';
$pageTitle = isset($pageTitleKey) ? t($pageTitleKey) : null;
?>
<!DOCTYPE html>
<html lang="<?php echo $LANG; ?>">
<head>
    <meta charset="UTF-8">
    <script>
      (function() {
        var saved = localStorage.getItem('riskguard-theme');
        if (saved === 'light') {
          document.documentElement.setAttribute('data-theme', 'light');
        }
      })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | RiskGuard Consulting' : 'RiskGuard Consulting — Gestión de Riesgos e ISO 27001'; ?></title>
    <meta name="description" content="<?php echo t('meta.description'); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body class="d-flex flex-column min-vh-100">
