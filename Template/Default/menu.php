<?php
$urlActual = strtolower(trim($_GET['url'] ?? '', '/'));

$menu = [
    'Inicio' => ['Id'=>1,'Nombre'=>'Inicio','Url'=>APP_URL.'Dashboard/index','Icono'=>'fas fa-home','Permiso'=>''],

    'Caja' => ['Id'=>12,'Nombre'=>'Caja','Url'=>'#','Icono'=>'fas fa-cash-register','Permiso'=>'ventas.crear','TourId'=>'tour-caja-link','Children'=>[
        ['Id'=>121,'Nombre'=>'Punto de Venta',   'Url'=>APP_URL.'Caja/index',    'Icono'=>'fas fa-cash-register','Permiso'=>'ventas.crear'],
        ...(Auth::can('ventas.crear') ? (function() {
            $cajaSesionMenu = new CajaSesionModel();
            $sesionMenu     = $cajaSesionMenu->getSesionAbierta(Auth::id());
            if ($sesionMenu) {
                return [['Id'=>122,'Nombre'=>'Cerrar Caja','Url'=>APP_URL.'Caja/cierre','Icono'=>'fas fa-store-slash','Permiso'=>'ventas.crear']];
            } else {
                return [['Id'=>122,'Nombre'=>'Abrir Caja','Url'=>APP_URL.'Caja/apertura','Icono'=>'fas fa-lock-open','Permiso'=>'ventas.crear']];
            }
        })() : []),
        ['Id'=>123,'Nombre'=>'Historial de Caja','Url'=>APP_URL.'Caja/historial','Icono'=>'fas fa-book','Permiso'=>'ventas.ver'],
    ]],

    'Pedidos' => ['Id'=>2,'Nombre'=>'Pedidos','Url'=>APP_URL.'Pedidos/index','Icono'=>'fas fa-shopping-bag','Permiso'=>'pedidos.ver','TourId'=>'tour-pedidos-link'],

    'Ventas' => ['Id'=>4,'Nombre'=>'Ventas','Url'=>'#','Icono'=>'fas fa-receipt','Permiso'=>'ventas.ver','Children'=>[
        ['Id'=>41,'Nombre'=>'Historial',     'Url'=>APP_URL.'Ventas/index',   'Icono'=>'fas fa-history',             'Permiso'=>'ventas.ver'],
        ['Id'=>71,'Nombre'=>'Facturas',      'Url'=>APP_URL.'Facturas/index', 'Icono'=>'fas fa-file-invoice-dollar', 'Permiso'=>'facturacion.ver'],
        ['Id'=>72,'Nombre'=>'Configuración', 'Url'=>APP_URL.'Facturas/config','Icono'=>'fas fa-sliders-h',           'Permiso'=>'facturacion.configurar'],
    ]],

    'Catalogo' => ['Id'=>3,'Nombre'=>'Catálogo','Url'=>'#','Icono'=>'fas fa-box-open','Permiso'=>'','Children'=>[
        ['Id'=>31,'Nombre'=>'Productos',  'Url'=>APP_URL.'Productos/index',  'Icono'=>'fas fa-boxes',      'Permiso'=>'productos.ver'],
        ['Id'=>32,'Nombre'=>'Categorías', 'Url'=>APP_URL.'Categorias/index', 'Icono'=>'fas fa-tags',       'Permiso'=>'categorias.ver'],
        ['Id'=>34,'Nombre'=>'Descuentos', 'Url'=>APP_URL.'Descuentos/index', 'Icono'=>'fas fa-percent',    'Permiso'=>'productos.editar'],
    ]],

    // Camisetas — catálogo del módulo de camisas de fútbol.
    // Los hijos se agregan a medida que existe cada Controller (evita 404).
    'Camisetas' => ['Id'=>13,'Nombre'=>'Camisetas','Url'=>'#','Icono'=>'fas fa-tshirt','Permiso'=>'camisetas.catalogo','Children'=>[
        ['Id'=>131,'Nombre'=>'Torneos', 'Url'=>APP_URL.'Torneos/index', 'Icono'=>'fas fa-trophy', 'Permiso'=>'camisetas.catalogo'],
    ]],

    'Clientes' => ['Id'=>6,'Nombre'=>'Clientes','Url'=>APP_URL.'Clientes/index','Icono'=>'fas fa-users','Permiso'=>'clientes.ver'],

    'Tienda' => ['Id'=>8,'Nombre'=>'Tienda','Url'=>'#','Icono'=>'fas fa-store','Permiso'=>'','Children'=>[
        ['Id'=>81,'Nombre'=>'Ver tienda',       'Url'=>APP_URL.'Tienda/index',  'Icono'=>'fas fa-external-link-alt','Permiso'=>''],
        ['Id'=>82,'Nombre'=>'Banners',           'Url'=>APP_URL.'Banners/index', 'Icono'=>'fas fa-image',            'Permiso'=>'tienda.configurar'],
        ['Id'=>83,'Nombre'=>'Zonas de envío',    'Url'=>APP_URL.'Zonas/index',   'Icono'=>'fas fa-map-marker-alt',   'Permiso'=>'tienda.configurar'],
    ]],

    'Administracion' => ['Id'=>10,'Nombre'=>'Administración','Url'=>'#','Icono'=>'fas fa-cogs','Permiso'=>'','Children'=>[
        ['Id'=>101,'Nombre'=>'Usuarios',           'Url'=>APP_URL.'Usuarios/index',       'Icono'=>'fas fa-user-cog',    'Permiso'=>'usuarios.ver'],
        ['Id'=>102,'Nombre'=>'Roles',              'Url'=>APP_URL.'Roles/index',          'Icono'=>'fas fa-user-shield', 'Permiso'=>'roles.ver'],
        ['Id'=>103,'Nombre'=>'Permisos',           'Url'=>APP_URL.'Permisos/index',       'Icono'=>'fas fa-key',         'Permiso'=>'roles.ver'],
        ['Id'=>91, 'Nombre'=>'Reporte Ventas',     'Url'=>APP_URL.'Reportes/ventas',      'Icono'=>'fas fa-chart-line',  'Permiso'=>'reportes.ver'],
        ['Id'=>92, 'Nombre'=>'Reporte Pedidos',    'Url'=>APP_URL.'Reportes/pedidos',     'Icono'=>'fas fa-shopping-bag','Permiso'=>'reportes.ver'],
        ['Id'=>93, 'Nombre'=>'Reporte Inventario', 'Url'=>APP_URL.'Reportes/inventario',  'Icono'=>'fas fa-boxes',       'Permiso'=>'reportes.ver'],
    ]],
];

function isActive(string $itemUrl): bool
{
    global $urlActual;
    if ($itemUrl === '#' || empty($itemUrl)) return false;
    $path = parse_url($itemUrl, PHP_URL_PATH);
    if ($path === null) return false;
    $path = strtolower(trim($path, '/'));
    $base = strtolower(trim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/'));
    if ($base && str_starts_with($path, $base)) $path = ltrim(substr($path, strlen($base)), '/');
    return $path !== '' && str_starts_with($urlActual, $path);
}

function renderMenu(array $menu): void
{
    foreach ($menu as $item) {
        if (!empty($item['Permiso']) && !Auth::can($item['Permiso'])) continue;

        if (!empty($item['Children'])) {
            $hayHijoActivo = false;
            foreach ($item['Children'] as $child) {
                if (isActive($child['Url'])) { $hayHijoActivo = true; break; }
            }
            $submenuId    = 'submenu-' . $item['Id'];
            $parentTourId = !empty($item['TourId']) ? ' data-tour="'.htmlspecialchars($item['TourId']).'"' : '';
            echo '<li class="nav-item">';
            echo '<a class="nav-link accordion-toggle"'.$parentTourId.' href="#" data-bs-toggle="collapse" data-bs-target="#'.$submenuId.'" aria-expanded="'.($hayHijoActivo?'true':'false').'" aria-controls="'.$submenuId.'">';
            echo '<i class="'.htmlspecialchars($item['Icono']).'"></i>';
            echo '<span class="ms-2">'.htmlspecialchars($item['Nombre']).'</span>';
            echo '<i class="fas fa-chevron-down ms-auto chevron-icon"></i></a>';
            echo '<div class="collapse'.($hayHijoActivo?' show':'').'" id="'.$submenuId.'"><ul class="nav flex-column">';
            foreach ($item['Children'] as $child) {
                if (!empty($child['Permiso']) && !Auth::can($child['Permiso'])) continue;
                $activeClass = isActive($child['Url']) ? ' active' : '';
                $childTourId = !empty($child['TourId']) ? ' data-tour="'.htmlspecialchars($child['TourId']).'"' : '';
                echo '<li class="nav-item"><a class="nav-link'.$activeClass.'"'.$childTourId.' href="'.htmlspecialchars($child['Url']).'">';
                echo '<i class="'.htmlspecialchars($child['Icono']).'"></i><span class="ms-2">'.htmlspecialchars($child['Nombre']).'</span></a></li>';
            }
            echo '</ul></div></li>';
        } else {
            $activeClass = isActive($item['Url']) ? ' active' : '';
            $target      = isset($item['Target']) ? ' target="'.htmlspecialchars($item['Target']).'"' : '';
            $tourId      = !empty($item['TourId']) ? ' data-tour="'.htmlspecialchars($item['TourId']).'"' : '';
            echo '<li class="nav-item"><a class="nav-link'.$activeClass.'"'.$tourId.' href="'.htmlspecialchars($item['Url']).'"'.$target.'>';
            echo '<i class="'.htmlspecialchars($item['Icono']).'"></i><span class="ms-2">'.htmlspecialchars($item['Nombre']).'</span></a></li>';
        }
    }
}
?>

<style>
/* ── Logo sidebar — ícono siempre visible, texto se oculta al colapsar ── */
.sidebar-logo-texto {
    opacity: 1;
    width: auto;
    max-width: 180px;
}
/* Colapsado → ocultar texto, solo ícono */
.sidebar.collapsed .sidebar-logo-texto {
    opacity:   0;
    width:     0;
    max-width: 0;
    overflow:  hidden;
}
/* Hover sobre colapsado → texto vuelve */
.sidebar.collapsed:hover .sidebar-logo-texto {
    opacity:   1;
    width:     auto;
    max-width: 180px;
}
/* Móvil abierto → siempre texto visible */
@media (max-width: 991.98px) {
    .sidebar.show .sidebar-logo-texto {
        opacity:   1 !important;
        width:     auto !important;
        max-width: 180px !important;
    }
}

/* ── Acordeón ── */
.accordion-toggle .chevron-icon { transition: transform 0.25s ease; }
.accordion-toggle[aria-expanded="true"] .chevron-icon { transform: rotate(180deg); }

/* ── Notificaciones ── */
.btn-notificaciones {
    position:   relative;
    background: rgba(255,255,255,0.15);
    border:     1px solid rgba(255,255,255,0.3);
    width:      36px; height: 36px;
    border-radius: 50%;
    display:    flex; align-items: center; justify-content: center;
    color:      #fff;
    transition: background 0.2s, border-color 0.2s;
    cursor:     pointer;
}
.btn-notificaciones:hover {
    background:   rgba(255,255,255,0.3);
    border-color: rgba(255,255,255,0.6);
}
.badge-notif {
    position:      absolute; top: 2px; right: 2px;
    background:    #c0392b !important;
    color:         #fff;
    border:        2px solid #fff;
    border-radius: 50%;
    width: 18px; height: 18px;
    font-size:   0.62rem;
    display:     flex; align-items: center; justify-content: center;
    font-weight: 700; line-height: 1;
}

/* ── Dropdown notificaciones ── */
.dropdown-notif { width: 340px; max-height: 420px; overflow-y: auto; padding: 0; }
.notif-item {
    padding: 10px 14px; border-bottom: 1px solid rgba(0,0,0,0.06);
    cursor: pointer; transition: background 0.15s;
    display: flex; gap: 10px; align-items: flex-start;
}
.notif-item:hover         { background: rgba(245,168,0,0.10); }
.notif-item.no-leida      { background: rgba(245,168,0,0.08); }
.notif-item.no-leida .notif-titulo { font-weight: 700; }
.notif-icono {
    width: 36px; height: 36px; flex-shrink: 0;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
}
.notif-icono.cita   { background: rgba(13,202,240,0.15);  color: #0dcaf0; }
.notif-icono.pedido { background: rgba(245,168,0,0.18); color: #F5A800; }
.notif-icono.stock  { background: rgba(255,193,7,0.15);   color: #ffc107; }
.notif-titulo  { font-size: 0.82rem; line-height: 1.3; }
.notif-mensaje { font-size: 0.76rem; color: #888; line-height: 1.3; margin-top: 2px; }
.notif-tiempo  { font-size: 0.7rem; color: #aaa; margin-top: 3px; }
.notif-header  { padding: 10px 14px; border-bottom: 1px solid rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center; }
.notif-footer  { padding: 8px 14px; border-top: 1px solid rgba(0,0,0,0.08); text-align: center; }

/* ── Botón modo oscuro ── */
.btn-theme-toggle {
    border: 1.5px solid rgba(255,255,255,0.6) !important;
}
.btn-theme-toggle:hover {
    background-color: rgba(255,255,255,0.25) !important;
    border-color:     rgba(255,255,255,0.9)  !important;
}

/* ── Logo móvil en topbar — OCULTO, el sidebar ya lo muestra ── */
.navbar-brand-mobile {
    display: none !important;
}
</style>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar" data-tour="tour-menu">
    <div class="sidebar-header">
        <a class="sidebar-brand d-flex align-items-center gap-2"
           href="<?= APP_URL ?>Dashboard/index"
           style="text-decoration:none; overflow:hidden; min-width:0;">

            <!-- Logo Zona Marcol — siempre visible -->
            <img src="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png"
                 alt=""
                 style="height:36px; width:36px; object-fit:contain; flex-shrink:0;
                        background:#fff; border-radius:50%; padding:2px;">

            <!-- Texto — se oculta al colapsar, vuelve al expandir/hover -->
            <span class="sidebar-logo-texto" style="
                display:flex; flex-direction:column; line-height:1.15;
                overflow:hidden; white-space:nowrap;
                transition: opacity 0.25s, width 0.25s;">
                <span style="font-size:1rem; font-weight:800; color:#F5A800; letter-spacing:0.3px;">
                    Zona Marcol
                </span>
                <span style="font-size:0.52rem; font-weight:600; letter-spacing:2.5px;
                             text-transform:uppercase; color:rgba(255,255,255,0.55);">
                    Tienda &amp; Servicio
                </span>
            </span>
        </a>
        <button class="btn-close-sidebar d-lg-none" id="btnCloseSidebar" aria-label="Cerrar menú">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <nav class="sidebar-nav" id="tour-menu">
        <ul class="nav flex-column" id="sidebarMenu"><?php renderMenu($menu); ?></ul>
    </nav>
</aside>

<!-- HEADER SUPERIOR -->
<header class="top-header d-flex align-items-center justify-content-between" id="topHeader">

    <button type="button" class="btn-menu-toggle" id="btnMenuToggle" aria-label="Abrir menú">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Logo centrado visible solo en móvil -->
    <span class="navbar-brand-mobile">
        <img src="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png"
             alt="<?= APP_NAME ?>"
             style="height:32px; width:auto; object-fit:contain; max-width:140px;
                    background:#fff; border-radius:50%; padding:2px;">
    </span>

    <div class="d-flex align-items-center gap-2">

        <div class="dropdown" id="tour-notif">
            <button type="button" class="btn-notificaciones"
                    id="btnNotificaciones" data-bs-toggle="dropdown" data-tour="tour-notif"
                    aria-expanded="false" title="Notificaciones">
                <i class="fas fa-bell"></i>
                <span class="badge-notif d-none" id="badgeNotif">0</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end dropdown-notif shadow"
                 id="dropdownNotif" aria-labelledby="btnNotificaciones">
                <div class="notif-header">
                    <span class="fw-semibold" style="font-size:0.85rem;">
                        <i class="fas fa-bell me-1" style="color:#F5A800;"></i>Notificaciones
                    </span>
                    <button type="button" class="btn btn-link btn-sm p-0 text-muted"
                            id="btnMarcarTodas" style="font-size:0.75rem; text-decoration:none;">
                        Marcar todas como leídas
                    </button>
                </div>
                <div id="listaNotificaciones">
                    <div class="text-center py-4 text-muted" style="font-size:0.85rem;">
                        <i class="fas fa-bell-slash fa-2x mb-2 d-block" style="opacity:0.3;"></i>
                        Sin notificaciones
                    </div>
                </div>
                <div class="notif-footer">
                    <small class="text-muted" id="txtNoLeidas">0 sin leer</small>
                </div>
            </div>
        </div>

        <button type="button" class="btn-theme-toggle" id="themeToggle" aria-label="Cambiar modo">
            <i class="fas <?= (isset($_SESSION['dark_mode']) && $_SESSION['dark_mode']) ? 'fa-sun' : 'fa-moon' ?>" id="themeIcon"></i>
        </button>

        <div class="dropdown">
            <button class="btn btn-profile dropdown-toggle" type="button"
                    id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user me-2"></i>
                <?= htmlspecialchars(Auth::get('nombre') ?? 'Usuario') ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                <li>
                    <span class="dropdown-item-text text-muted" style="font-size:0.8rem;">
                        <i class="fas fa-shield-alt me-1"></i>
                        <?= htmlspecialchars(Auth::get('rol_slug') ?? 'Sin rol') ?>
                    </span>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button class="dropdown-item" type="button"
                            onclick="if (typeof window.amTourIniciar === 'function') { window.amTourIniciar(); } else if (typeof amActivarTour === 'function') { amActivarTour(); }"
                            style="border:none; background:none; cursor:pointer; text-align:left;">
                        <i class="fas fa-graduation-cap me-2" style="color:#F5A800;"></i>Repetir tour
                    </button>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= APP_URL ?>Usuarios/perfil">
                        <i class="fas fa-user-circle me-2"></i>Mi perfil
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item dropdown-item-logout" href="<?= APP_URL ?>Auth/logout?csrf=<?= urlencode(Csrf::token()) ?>">
                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                    </a>
                </li>
            </ul>
        </div>

    </div>
</header>

<script>
(function () {
    'use strict';

    // ── Acordeón sidebar — un submenu abierto a la vez ──────
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebarMenu');
        if (!sidebar) return;
        sidebar.addEventListener('show.bs.collapse', function (e) {
            sidebar.querySelectorAll('.collapse.show').forEach(function (open) {
                if (open !== e.target) {
                    const bsCollapse = bootstrap.Collapse.getInstance(open);
                    if (bsCollapse) bsCollapse.hide();
                    const toggle = sidebar.querySelector('[data-bs-target="#' + open.id + '"]');
                    if (toggle) toggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    });

    // ── Notificaciones ────────────────────────────────────────
    const APP_URL     = '<?= APP_URL ?>';
    const csrf        = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';
    const badge       = document.getElementById('badgeNotif');
    const lista       = document.getElementById('listaNotificaciones');
    const txtNoLeidas = document.getElementById('txtNoLeidas');
    const btnMarcar   = document.getElementById('btnMarcarTodas');

    const iconos = {
        servicio: { icono: 'fas fa-tools',               clase: 'servicio' },
        pedido:   { icono: 'fas fa-shopping-bag',         clase: 'pedido'   },
        stock:    { icono: 'fas fa-exclamation-triangle', clase: 'stock'    },
        camiseta: { icono: 'fas fa-tshirt',               clase: 'camiseta' },
    };

    function tiempoRelativo(fechaStr) {
        const diff = Math.floor((new Date() - new Date(fechaStr.replace(' ', 'T'))) / 1000);
        if (diff < 60)    return 'Hace un momento';
        if (diff < 3600)  return `Hace ${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `Hace ${Math.floor(diff / 3600)}h`;
        return `Hace ${Math.floor(diff / 86400)}d`;
    }

    function renderNotificaciones(notifs, noLeidas) {
        if (noLeidas > 0) {
            badge.textContent = noLeidas > 99 ? '99+' : noLeidas;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
        txtNoLeidas.textContent = `${noLeidas} sin leer`;
        if (!notifs || notifs.length === 0) {
            lista.innerHTML = `<div class="text-center py-4 text-muted" style="font-size:0.85rem;">
                <i class="fas fa-bell-slash fa-2x mb-2 d-block" style="opacity:0.3;"></i>Sin notificaciones</div>`;
            return;
        }
        lista.innerHTML = notifs.map(n => {
            const cfg    = iconos[n.tipo] ?? iconos.pedido;
            const noLeid = parseInt(n.leida) === 0 ? 'no-leida' : '';
            const url    = n.url ? `${APP_URL}${n.url}` : '#';
            return `
            <div class="notif-item ${noLeid}" data-id="${n.id}" data-url="${url}" data-leida="${n.leida}">
                <div class="notif-icono ${cfg.clase}"><i class="${cfg.icono}"></i></div>
                <div class="flex-fill overflow-hidden">
                    <div class="notif-titulo">${n.titulo}</div>
                    <div class="notif-mensaje">${n.mensaje}</div>
                    <div class="notif-tiempo">${tiempoRelativo(n.created_at)}</div>
                </div>
                <button type="button" class="btn-eliminar-notif" data-id="${n.id}" title="Eliminar"
                        style="background:none;border:none;color:#ccc;cursor:pointer;font-size:0.75rem;flex-shrink:0;padding:2px 4px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
        }).join('');
    }

    function cargarNotificaciones() {
        fetch(`${APP_URL}Notificaciones/obtener`)
            .then(r => r.json())
            .then(data => renderNotificaciones(data.notificaciones, data.no_leidas))
            .catch(() => {});
    }

    lista.addEventListener('click', function (e) {
        const btnEl = e.target.closest('.btn-eliminar-notif');
        if (btnEl) {
            e.stopPropagation();
            const fd = new FormData();
            fd.append('csrf_token', csrf);
            fd.append('id', btnEl.dataset.id);
            fetch(`${APP_URL}Notificaciones/eliminar`, { method: 'POST', body: fd })
                .then(() => cargarNotificaciones());
            return;
        }
        const item = e.target.closest('.notif-item');
        if (!item) return;
        if (item.dataset.leida === '0') {
            const fd = new FormData();
            fd.append('csrf_token', csrf);
            fd.append('id', item.dataset.id);
            fetch(`${APP_URL}Notificaciones/marcarLeida`, { method: 'POST', body: fd })
                .then(() => cargarNotificaciones());
        }
        if (item.dataset.url && item.dataset.url !== '#') window.location.href = item.dataset.url;
    });

    btnMarcar?.addEventListener('click', function () {
        const fd = new FormData();
        fd.append('csrf_token', csrf);
        fetch(`${APP_URL}Notificaciones/marcarTodas`, { method: 'POST', body: fd })
            .then(() => cargarNotificaciones());
    });

    document.getElementById('btnNotificaciones')?.addEventListener('click', cargarNotificaciones);
    cargarNotificaciones();
    setInterval(cargarNotificaciones, 30000);
})();
</script>