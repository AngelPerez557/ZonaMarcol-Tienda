<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 | Acceso denegado — <?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/Custom/variables.css">
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/Custom/custom-themes.css">
    <style>
        /* Estilos exclusivos de la vista 403 — no afectan otros módulos */
        .error-page {
            min-height:      100vh;
            display:         flex;
            align-items:     center;
            justify-content: center;
            background:      var(--body-bg);
            font-family:     var(--font-family-base);
        }

        .error-code {
            font-size:   8rem;
            font-weight: var(--font-weight-bold);
            line-height: 1;
            color:       #DC3545;
            text-shadow: 0 4px 20px rgba(220,53,69,0.30);
            animation:   shake 0.5s ease-in-out infinite alternate;
        }

        .error-icon {
            font-size: 4rem;
            color:     #DC3545;
            opacity:   0.6;
        }

        .error-title {
            font-size:   1.75rem;
            font-weight: var(--font-weight-bold);
            color:       var(--body-text);
        }

        .error-subtitle {
            color:     var(--body-text-muted);
            font-size: var(--font-size-base);
            max-width: 400px;
            margin:    0 auto;
        }

        /* Animación de shake — refuerza visualmente el "acceso denegado" */
        @keyframes shake {
            0%   { transform: rotate(-2deg); }
            100% { transform: rotate(2deg); }
        }
    </style>
</head>

<!-- Dark mode aplicado desde sesión PHP -->
<body class="<?= (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode']) ? 'dark-mode' : '' ?>">

    <div class="error-page">
        <div class="text-center px-3">

            <!-- Ícono de candado -->
            <div class="error-icon mb-3">
                <i class="fas fa-lock"></i>
            </div>

            <!-- Código de error -->
            <div class="error-code mb-2">403</div>

            <!-- Título -->
            <h1 class="error-title mb-3">Acceso denegado</h1>

            <!-- Descripción -->
            <p class="error-subtitle mb-4">
                No tenés permisos para acceder a esta sección.
                Si creés que esto es un error, contactá al administrador del sistema.
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
                       class="btn btn-danger">
                        <i class="fas fa-home me-2"></i>Ir al Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?= APP_URL ?>Login"
                       class="btn btn-danger">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar sesión
                    </a>
                <?php endif; ?>
            </div>

            <!-- Permisos del usuario — solo en desarrollo -->
            <?php if (APP_ENV === 'development' && isset($_SESSION['user'])): ?>
                <div class="mt-4 p-3 rounded text-start mx-auto"
                     style="max-width:400px;background:var(--card-bg);border:1px solid var(--border-color);">
                    <small class="text-muted d-block mb-2">
                        <i class="fas fa-bug me-1"></i>
                        Debug — Permisos actuales:
                    </small>
                    <?php foreach (Auth::user()['permisos'] ?? [] as $permiso): ?>
                        <span class="badge bg-secondary me-1 mb-1">
                            <?= htmlspecialchars($permiso) ?>
                        </span>
                    <?php endforeach; ?>
                    <?php if (empty(Auth::user()['permisos'] ?? [])): ?>
                        <small class="text-danger">Sin permisos asignados</small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Nombre del sistema -->
            <p class="mt-5 text-muted">
                <small><?= APP_NAME ?> &mdash; <?= date('Y') ?></small>
            </p>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>