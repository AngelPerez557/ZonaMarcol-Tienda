<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? APP_NAME ?> | Zona Marcol</title>
    <link rel="icon" type="image/png" href="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png">
    <link rel="icon" type="image/png" href="<?= APP_URL ?>Content/Demo/img/zonamarcol_Letras.png">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Tienda -->
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/tienda.css">
    <link rel="stylesheet" href="<?= APP_URL ?>Content/Dist/css/Custom/custom-themes.css">

    <!-- PWA Tienda -->
    <link rel="manifest" href="<?= APP_URL ?>manifest-tienda.json">
    <meta name="theme-color" content="#F5A800">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Zona Marcol">
    <link rel="apple-touch-icon" href="<?= APP_URL ?>Content/Demo/img/icon/icon-tienda-192.png">

    <style>
        :root {
            --rosa:       #F5A800;
            --rosa-hover: #C58800;
            --rosa-dark:  #8C6300;
            --rosa-soft:  #FFF1C8;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #FFFBF2;
            color: #333;
        }

        /* ── NAVBAR ── */
        .tienda-navbar {
            background: #fff;
            border-bottom: 2px solid var(--rosa-soft);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(245,168,0,0.1);
        }

        .tienda-brand {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--rosa);
            text-decoration: none;
        }
        .tienda-brand:hover { color: var(--rosa-hover); }

        .nav-tienda .nav-link {
            color: #555;
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 20px;
            transition: all 0.2s;
        }
        .nav-tienda .nav-link:hover,
        .nav-tienda .nav-link.active {
            background: var(--rosa-soft);
            color: var(--rosa);
        }

        .btn-carrito {
            position: relative;
            background: var(--rosa);
            color: #fff;
            border: none;
            border-radius: 20px;
            padding: 8px 16px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-carrito:hover { background: var(--rosa-hover); color: #fff; }

        .badge-carrito {
            position: absolute;
            top: -6px; right: -6px;
            background: #dc3545;
            color: #fff;
            border-radius: 50%;
            width: 20px; height: 20px;
            font-size: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        /* ── FIX 1 — Menú móvil ── */
        #menuMobile .nav-link {
            color: #555555;
            font-weight: 500;
            padding: 8px 14px;
            border-radius: 8px;
            transition: background-color 0.2s, color 0.2s;
        }
        #menuMobile .nav-link:hover { color: var(--rosa); background-color: var(--rosa-soft); }
        #menuMobile .nav-link.active { color: var(--rosa); background-color: var(--rosa-soft); }

        /* ── FIX 4 — Botón hamburguesa ── */
        .tienda-navbar [data-bs-target="#menuMobile"] { border-color: var(--rosa); color: var(--rosa); }
        .tienda-navbar [data-bs-target="#menuMobile"]:hover { background-color: var(--rosa-soft); }

        /* ── CARDS PRODUCTO ── */
        .producto-card {
            border: 1px solid #F0E2BC;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            background: #fff;
        }
        .producto-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(245,168,0,0.2);
        }
        .producto-img {
            height: 200px;
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #FFFBF2;
        }

        .btn-rosa {
            background: var(--rosa);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
            transition: background 0.2s;
            cursor: pointer;
        }
        .btn-rosa:hover { background: var(--rosa-hover); color: #fff; }

        .btn-rosa-outline {
            background: transparent;
            color: var(--rosa);
            border: 2px solid var(--rosa);
            border-radius: 8px;
            padding: 6px 14px;
            font-weight: 600;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-rosa-outline:hover { background: var(--rosa); color: #fff; }

        /* ── FOOTER ── */
        .tienda-footer {
            background: #3d3d3d;
            color: #ccc;
            padding: 40px 0 20px;
            margin-top: 60px;
        }
        .tienda-footer a { color: #F5A800; text-decoration: none; }
        .tienda-footer a:hover { color: #FFC75A; }

        /* ── FIX 2 — Footer móvil ── */
        @media (max-width: 767px) {
            .tienda-footer .row > div { flex: 0 0 100%; max-width: 100%; }
            .tienda-footer .col-6 a { word-break: break-word; }
        }

        /* ── TOAST CARRITO ── */
        .toast-carrito {
            position: fixed;
            bottom: 90px; right: 20px; /* subido para no tapar el botón flotante */
            z-index: 9998;
            background: #333;
            color: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            display: none;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        /* ── CHIPS CATEGORÍAS ── */
        .chip-categoria {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            border: 2px solid var(--rosa-soft);
            background: #fff;
            color: #555;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
        }
        .chip-categoria:hover,
        .chip-categoria.activo { background: var(--rosa); border-color: var(--rosa); color: #fff; }

        /* ── Responsive móvil navbar ── */
        @media (max-width: 767px) {
            .tienda-navbar .container { padding-left: 12px; padding-right: 12px; }
            .tienda-brand img { height: 36px !important; max-width: 140px !important; }
            .btn-carrito { padding: 6px 12px !important; font-size: 0.85rem; }
            .btn-rosa-outline { padding: 5px 10px !important; font-size: 0.78rem !important; }
            .d-flex.align-items-center.gap-3 { gap: 8px !important; }
            .tienda-footer { padding: 30px 0 15px; margin-top: 30px; }
            /* Espacio extra al fondo para que el botón flotante no tape contenido */
            body { padding-bottom: 80px; }
        }

        /* ── DISPONIBILIDAD CITAS ── */
        .dia-disponible  { background: rgba(40,167,69,0.15) !important; border-color: #28a745 !important; cursor: pointer; }
        .dia-ocupado     { background: rgba(220,53,69,0.08) !important; color: #aaa !important; cursor: not-allowed; }
        .dia-no-laboral  { background: rgba(0,0,0,0.03) !important; color: #ccc !important; cursor: not-allowed; }

        /* ── DROPDOWN CLIENTE ── */
        .dropdown-tienda .dropdown-toggle {
            background: transparent;
            border: 1.5px solid var(--rosa);
            color: var(--rosa);
            border-radius: 20px;
            padding: 6px 14px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .dropdown-tienda .dropdown-toggle:hover,
        .dropdown-tienda .dropdown-toggle:focus,
        .dropdown-tienda .dropdown-toggle:active,
        .dropdown-tienda .show > .dropdown-toggle {
            background: var(--rosa) !important;
            color: #fff !important;
            border-color: var(--rosa) !important;
            box-shadow: none !important;
        }
        .dropdown-tienda .dropdown-menu {
            background: #fff;
            border: 1.5px solid var(--rosa-soft);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(245,168,0,0.18);
            padding: 6px;
            min-width: 180px;
            margin-top: 6px;
        }
        .dropdown-tienda .dropdown-item {
            border-radius: 8px;
            color: #555;
            font-weight: 500;
            padding: 8px 14px;
            transition: all 0.15s;
        }
        .dropdown-tienda .dropdown-item:hover { background: var(--rosa-soft); color: var(--rosa); }
        .dropdown-tienda .dropdown-item i { color: var(--rosa); }
        .dropdown-tienda .dropdown-divider { border-color: var(--rosa-soft); margin: 4px 0; }
        .dropdown-tienda .dropdown-item.text-danger:hover { background: #fdecea; color: #dc3545 !important; }

        /* ── CARRITO FLOTANTE MÓVIL ── */
        .btn-carrito-flotante {
            display: none; /* oculto en desktop */
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: var(--rosa);
            color: #fff;
            border: none;
            box-shadow: 0 4px 16px rgba(245,168,0,0.5);
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            text-decoration: none;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-carrito-flotante:hover {
            background: var(--rosa-hover);
            color: #fff;
            transform: scale(1.08);
        }
        .btn-carrito-flotante .badge-flotante {
            position: absolute;
            top: -2px; right: -2px;
            background: #dc3545;
            color: #fff;
            border-radius: 50%;
            width: 22px; height: 22px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border: 2px solid #fff;
        }

        /* Solo visible en móvil */
        @media (max-width: 767px) {
            .btn-carrito-flotante {
                display: flex;
            }
            /* Ocultar el botón carrito del navbar en móvil para no duplicar */
            .btn-carrito {
                display: none !important;
            }
        }

        /* ── F-39 Banner WebView (Instagram, Facebook, etc.) ── */
        .webview-banner {
            background: #fff8e6;
            color: #6b4d00;
            border-bottom: 1px solid #f0d779;
            padding: 10px 14px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
            line-height: 1.35;
        }
        .webview-banner i.fa-info-circle { color: #d49e00; flex-shrink: 0; }
        .webview-banner .wb-action {
            background: var(--rosa);
            color: #fff;
            padding: 5px 12px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.78rem;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .webview-banner .wb-action:hover { background: var(--rosa-hover); color: #fff; }
        .webview-banner .wb-close {
            background: transparent;
            border: none;
            color: #6b4d00;
            cursor: pointer;
            padding: 2px 6px;
            flex-shrink: 0;
        }
        .webview-banner .wb-text { flex: 1; min-width: 0; }
        @media (max-width: 480px) {
            .webview-banner { font-size: 0.78rem; padding: 8px 10px; gap: 6px; }
            .webview-banner .wb-action { font-size: 0.72rem; padding: 4px 10px; }
        }
    </style>
    <?php $darkMode = isset($_SESSION['dark_mode']) && $_SESSION['dark_mode'] === true; ?>
</head>
<body<?= $darkMode ? ' class="dark-mode"' : '' ?>>

    <?php
    // F-39 — Banner solo si entra desde Instagram / Facebook / TikTok / etc.
    // No bloquea la navegación, solo sugiere abrir en navegador real.
    if (class_exists('WebViewDetector') && WebViewDetector::isInAppBrowser()):
        $urlExterna = WebViewDetector::openInBrowserUrl(APP_URL . 'Tienda');
        $instruccion = WebViewDetector::instruccion();
        $appNombre   = WebViewDetector::isInstagram() ? 'Instagram' : 'la app';
    ?>
    <div class="webview-banner" id="webviewBanner">
        <i class="fas fa-info-circle"></i>
        <div class="wb-text">
            Para una mejor experiencia (especialmente al iniciar sesión o pagar),
            abrí esta tienda en tu navegador. <?= htmlspecialchars($instruccion) ?>
        </div>
        <a href="<?= htmlspecialchars($urlExterna) ?>" class="wb-action"
           target="_blank" rel="noopener noreferrer">Abrir</a>
        <button type="button" class="wb-close" id="webviewBannerClose"
                aria-label="Cerrar banner">×</button>
    </div>
    <script>
        // Cierra el banner y recuerda la decisión por esta sesión
        document.addEventListener('DOMContentLoaded', function () {
            const banner = document.getElementById('webviewBanner');
            const closeBtn = document.getElementById('webviewBannerClose');
            if (sessionStorage.getItem('webview_banner_cerrado') === '1') {
                banner.style.display = 'none';
            }
            closeBtn.addEventListener('click', function () {
                banner.style.display = 'none';
                sessionStorage.setItem('webview_banner_cerrado', '1');
            });
        });
    </script>
    <?php endif; ?>

    <!-- ─── NAVBAR ─────────────────────────────────── -->
    <nav class="tienda-navbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="<?= APP_URL ?>Tienda" class="tienda-brand">
                    <img src="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png"
                         alt="<?= APP_NAME ?>"
                         style="height:40px; width:auto; object-fit:contain; max-width:160px;">
                </a>

                <?php
                $urlActual = strtolower(trim($_GET['url'] ?? '', '/'));
                $esInicio   = $urlActual === 'tienda' || $urlActual === 'tienda/index' || $urlActual === '';
                $esCatalogo = str_starts_with($urlActual, 'tienda/catalogo') || str_starts_with($urlActual, 'tienda/producto');
                ?>
                <ul class="nav nav-tienda d-none d-md-flex align-items-center">
                    <li class="nav-item">
                        <a href="<?= APP_URL ?>Tienda" class="nav-link <?= $esInicio ? 'active' : '' ?>">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= APP_URL ?>Tienda/catalogo" class="nav-link <?= $esCatalogo ? 'active' : '' ?>">Catálogo</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <!-- Carrito desktop — oculto en móvil via CSS -->
                    <a href="<?= APP_URL ?>Tienda/carrito" class="btn-carrito">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge-carrito" id="badgeCarrito">0</span>
                    </a>

                    <?php if (!empty($_SESSION['cliente'])): ?>
                    <div class="dropdown dropdown-tienda">
                        <button class="dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i>
                            <?= htmlspecialchars($_SESSION['cliente']['nombre']) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= APP_URL ?>Tienda/miPerfil"><i class="fas fa-user-edit me-2"></i>Mi perfil</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>Tienda/misPedidos"><i class="fas fa-box me-2"></i>Mis pedidos</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>Tienda/misFavoritos"><i class="fas fa-heart me-2" style="color:#F5A800;"></i>Mis favoritos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>Tienda/logout?csrf=<?= urlencode(Csrf::token()) ?>"><i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión</a></li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <a href="<?= APP_URL ?>Tienda/login" class="btn-rosa-outline" style="font-size:0.85rem;">
                        <i class="fas fa-sign-in-alt me-1"></i>Ingresar
                    </a>
                    <?php endif; ?>

                    <button class="btn btn-outline-secondary btn-sm d-md-none" type="button"
                            data-bs-toggle="collapse" data-bs-target="#menuMobile"
                            aria-expanded="false" aria-controls="menuMobile">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>

            <div class="collapse d-md-none mt-2" id="menuMobile">
                <ul class="nav flex-column">
                    <li><a href="<?= APP_URL ?>Tienda/index"    class="nav-link <?= $esInicio   ? 'active' : '' ?>">Inicio</a></li>
                    <li><a href="<?= APP_URL ?>Tienda/catalogo" class="nav-link <?= $esCatalogo ? 'active' : '' ?>">Catálogo</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ─── CONTENIDO ──────────────────────────────── -->
    <main>
        {JBODY}
    </main>

    <!-- ─── FOOTER ─────────────────────────────────── -->
    <footer class="tienda-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-md-4">
                    <h5 class="text-white mb-3">
                        <i class="fas fa-store me-2" style="color:#F5A800;"></i>
                        Zona Marcol
                    </h5>
                    <p style="font-size:0.85rem;">
                        Cosméticos masculinos, tecnología y servicio técnico de consolas y PC.
                    </p>
                </div>
                <div class="col-6 col-md-2">
                    <h6 class="text-white mb-3">Tienda</h6>
                    <ul class="list-unstyled" style="font-size:0.85rem;">
                        <li><a href="<?= APP_URL ?>Tienda/catalogo">Catálogo</a></li>
                        <li><a href="<?= APP_URL ?>Tienda/carrito">Carrito</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-3">
                    <h6 class="text-white mb-3">Contacto</h6>
                    <ul class="list-unstyled" style="font-size:0.85rem;">
                        <li>
                            <a href="https://wa.me/50499873125" target="_blank" style="color:#ccc; text-decoration:none;">
                                <i class="fab fa-whatsapp me-2" style="color:#25d366;"></i>+(504) 9987-3125
                            </a>
                        </li>
                        <li class="mt-2">
                            <a href="https://www.instagram.com/zonamarcol" target="_blank"
                               style="color:#ccc; text-decoration:none; word-break:break-word;">
                                <i class="fab fa-instagram me-2" style="color:#F5A800;"></i>@zonamarcol
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-md-3">
                    <h6 class="text-white mb-3">Síguenos</h6>
                    <div class="d-flex gap-3" style="font-size:1.5rem;">
                        <a href="https://www.instagram.com/zonamarcol" target="_blank" title="Instagram">
                            <i class="fab fa-instagram" style="color:#F5A800;"></i>
                        </a>
                        <a href="https://wa.me/50499873125" target="_blank" title="WhatsApp">
                            <i class="fab fa-whatsapp" style="color:#25d366;"></i>
                        </a>
                    </div>
                    <p class="mt-3" style="font-size:0.8rem;">
                        Desarrollado por
                        <a href="https://www.instagram.com/deskcod_" target="_blank" style="color:#F5A800;">DeskCod</a>
                        <br>
                        <a href="https://wa.me/50493429640" target="_blank" style="color:#ccc; text-decoration:none; font-size:0.78rem;">
                            <i class="fab fa-whatsapp me-1" style="color:#25d366;"></i>+(504) 9342-9640
                        </a>
                        <br>
                        <a href="https://www.instagram.com/deskcod_" target="_blank" style="color:#ccc; text-decoration:none; font-size:0.78rem;">
                            <i class="fab fa-instagram me-1" style="color:#F5A800;"></i>@deskcod_
                        </a>
                    </p>
                </div>
            </div>
            <hr style="border-color:#555; margin: 20px 0;">
            <div class="text-center" style="font-size:0.8rem;">
                &copy; <?= date('Y') ?> Zona Marcol. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    <!-- ─── CARRITO FLOTANTE — solo visible en móvil ── -->
    <a href="<?= APP_URL ?>Tienda/carrito" class="btn-carrito-flotante" id="btnCarritoFlotante" title="Ver carrito">
        <i class="fas fa-shopping-cart"></i>
        <span class="badge-flotante" id="badgeFlotante">0</span>
    </a>

    <!-- Toast carrito — subido para no tapar el botón flotante -->
    <div class="toast-carrito" id="toastCarrito">
        <i class="fas fa-check-circle text-success"></i>
        <span id="toastCarritoMsg">Producto agregado</span>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Dark mode toggle script -->
    <script src="<?= APP_URL ?>Content/Dist/js/theme-switcher.js"></script>

    <script>
    const APP_URL = '<?= APP_URL ?>';

    function getCarrito() {
        return JSON.parse(localStorage.getItem('carrito_zonamarcol') || '[]');
    }
    function saveCarrito(carrito) {
        localStorage.setItem('carrito_zonamarcol', JSON.stringify(carrito));
        actualizarBadge();
    }
    function actualizarBadge() {
        const carrito = getCarrito();
        const total   = carrito.reduce((sum, item) => sum + item.cantidad, 0);

        // Badge navbar desktop
        const badge = document.getElementById('badgeCarrito');
        if (badge) badge.textContent = total;

        // Badge flotante móvil
        const badgeFlotante = document.getElementById('badgeFlotante');
        if (badgeFlotante) {
            badgeFlotante.textContent = total > 99 ? '99+' : total;
            // Ocultar badge si es 0
            badgeFlotante.style.display = total > 0 ? 'flex' : 'none';
        }
    }
    function agregarAlCarrito(id, nombre, precio, imagen, varianteId, varianteNombre) {
        const carrito = getCarrito();
        const key     = `${id}-${varianteId || ''}`;
        const existe  = carrito.find(i => i.key === key);
        if (existe) {
            existe.cantidad++;
        } else {
            carrito.push({ key, id, nombre, precio, imagen,
                varianteId:     varianteId     || null,
                varianteNombre: varianteNombre || null,
                cantidad: 1 });
        }
        saveCarrito(carrito);
        mostrarToast(`"${nombre}" agregado al carrito`);
    }
    // Wrapper con verificación de stock — usado en Catalogo e Inicio
    function agregarAlCarritoConStock(id, varianteId, nombre, precio, imagen) {
        fetch(`${APP_URL}Tienda/verificarStock`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `producto_id=${id}&variante_id=${varianteId}`
        })
        .then(r => r.json())
        .then(data => {
            if (!data.disponible) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin stock',
                    text: 'Este producto ya no está disponible.',
                    confirmButtonColor: '#F5A800'
                });
                return;
            }
            agregarAlCarrito(id, nombre, precio, imagen, varianteId || null, null);
        })
        .catch(() => {
            // Si falla la verificación igual agrega — falla silenciosa
            agregarAlCarrito(id, nombre, precio, imagen, varianteId || null, null);
        });
    }
    function mostrarToast(msg) {
        const toast = document.getElementById('toastCarrito');
        const texto = document.getElementById('toastCarritoMsg');
        if (!toast) return;
        texto.textContent = msg;
        toast.style.display = 'flex';
        setTimeout(() => { toast.style.display = 'none'; }, 2500);
    }
    document.addEventListener('DOMContentLoaded', actualizarBadge);

    // ── Favoritos ─────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-favorito').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                <?php if (empty($_SESSION['cliente'])): ?>
                window.location.href = '<?= APP_URL ?>Tienda/login';
                return;
                <?php endif; ?>

                const productoId = this.dataset.id;
                const icon       = this.querySelector('i');
                const self       = this;

                fetch('<?= APP_URL ?>Tienda/toggleFavorito', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `producto_id=${productoId}`
                })
                .then(r => r.json())
                .then(data => {
                    if (data.error === 'no_auth') {
                        window.location.href = '<?= APP_URL ?>Tienda/login';
                        return;
                    }
                    if (data.liked) {
                        icon.style.color = '#F5A800';
                        self.style.boxShadow = '0 2px 8px rgba(245,168,0,0.4)';
                    } else {
                        icon.style.color = '#ccc';
                        self.style.boxShadow = '0 2px 6px rgba(0,0,0,0.15)';
                    }
                })
                .catch(() => {});
            });
        });
    });
    </script>

    {JSCRIPTS}
</body>
</html>                                  