<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 | Página no encontrada — <?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/Custom/variables.css">
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/Custom/custom-themes.css">
    <style>
        /* Estilos exclusivos de la vista 404 — no afectan otros módulos */
        .error-page {
            min-height:      100vh;
            display:         flex;
            align-items:     center;
            justify-content: center;
            background:      var(--body-bg);
            font-family:     var(--font-family-base);
        }

        .error-code {
            font-size:      8rem;
            font-weight:    var(--font-weight-bold);
            line-height:    1;
            color:          var(--btn-primary-bg);
            text-shadow:    0 4px 20px rgba(52,152,219,0.30);
            animation:      pulse 2s ease-in-out infinite;
        }

        .error-icon {
            font-size:   4rem;
            color:       var(--btn-primary-bg);
            opacity:     0.6;
            animation:   bounce 2s infinite;
        }

        .error-title {
            font-size:   1.75rem;
            font-weight: var(--font-weight-bold);
            color:       var(--body-text);
        }

        .error-subtitle {
            color:       var(--body-text-muted);
            font-size:   var(--font-size-base);
            max-width:   400px;
            margin:      0 auto;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50%       { transform: scale(1.05); }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40%  { transform: translateY(-20px); }
            60%  { transform: translateY(-10px); }
        }
    </style>
</head>

<!-- Dark mode aplicado desde sesión PHP -->
<body class="<?= (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode']) ? 'dark-mode' : '' ?>">

    <div class="error-page">
        <div class="text-center px-3">

            <!-- Ícono animado -->
            <div class="error-icon mb-3">
                <i class="fas fa-map-signs"></i>
            </div>

            <!-- Código de error -->
            <div class="error-code mb-2">404</div>

            <!-- Título -->
            <h1 class="error-title mb-3">Página no encontrada</h1>

            <!-- Descripción -->
            <p class="error-subtitle mb-4">
                La página que buscás no existe o fue movida a otra ubicación.
                Verificá la URL o volvé al inicio.
            </p>

            <!-- Botones de acción -->
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <!-- Volver atrás -->
                <button onclick="history.back()"
                        class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver atrás
                </button>

                <!-- Ir al Dashboard -->
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="<?= APP_URL ?>Dashboard/index"
                       class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Ir al Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?= APP_URL ?>Login"
                       class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar sesión
                    </a>
                <?php endif; ?>
            </div>

            <!-- Nombre del sistema -->
            <p class="mt-5 text-muted">
                <small><?= APP_NAME ?> &mdash; <?= date('Y') ?></small>
            </p>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>