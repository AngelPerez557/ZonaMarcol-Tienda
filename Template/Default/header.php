<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?><?= APP_NAME ?></title>

    <link rel="icon" type="image/png" href="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- CSS propio -->
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/Custom/variables.css">
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/Custom/custom-themes.css">

    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= APP_URL . htmlspecialchars($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Driver.js CSS local + estilos del tour Zona Marcol (rediseño 2026) -->
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Vendor/driverjs/driver.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/am-tour.css">

    <!-- PWA Panel Admin -->
    <link rel="manifest" href="<?= APP_URL ?>manifest-admin.json">
    <meta name="theme-color" content="#F5A800">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="AM Admin">
    <link rel="apple-touch-icon"
          href="<?= APP_URL ?>Content/Demo/img/icons/icon-admin-192.png">

    <?php $darkMode = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] === true; ?>
</head>
<body id="appBody"<?= $darkMode ? ' class="dark-mode"' : '' ?>>
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>