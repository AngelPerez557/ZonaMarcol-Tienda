<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA DE BIENVENIDA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Dashboard</h4>
            <small class="text-muted">
                Bienvenida, <strong><?= htmlspecialchars(Auth::get('nombre') ?? 'Usuario') ?></strong>
                &nbsp;|&nbsp;
                <span class="badge" style="background-color:#F5A800;">
                    <?= htmlspecialchars(Auth::get('rol_slug') ?? 'Sin rol') ?>
                </span>
            </small>
        </div>
        <small class="text-muted">
            <i class="fas fa-clock me-1"></i>
            <?= date('d/m/Y H:i') ?>
        </small>
    </div>

    <!-- ─────────────────────────────────────────────
         CARDS DE RESUMEN
         ───────────────────────────────────────────── -->
    <div class="row g-3 mb-4" id="tour-cards">

        <!-- Card Usuarios -->
        <?php if (Auth::can('usuarios.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(245,168,0,0.12);flex-shrink:0;">
                        <i class="fas fa-users fa-lg" style="color:#F5A800;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Usuarios</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalUsuarios ?>
                        </div>
                        <small class="text-muted"><?= $totalActivos ?> activos</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Usuarios/index" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Roles -->
        <?php if (Auth::can('roles.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(40,167,69,0.12);flex-shrink:0;">
                        <i class="fas fa-user-shield fa-lg" style="color:#28a745;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Roles</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalRoles ?>
                        </div>
                        <small class="text-muted">registrados</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Roles/index" class="btn btn-sm btn-success">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Permisos -->
        <?php if (Auth::can('roles.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(111,66,193,0.12);flex-shrink:0;">
                        <i class="fas fa-key fa-lg" style="color:#6f42c1;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Permisos</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalPermisos ?>
                        </div>
                        <small class="text-muted">del sistema</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Permisos/index" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Productos -->
        <?php if (Auth::can('productos.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(253,126,20,0.12);flex-shrink:0;">
                        <i class="fas fa-boxes fa-lg" style="color:#fd7e14;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Productos</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalProductos ?>
                        </div>
                        <small class="text-muted"><?= $totalProductosActivos ?> activos</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Productos/index" class="btn btn-sm btn-warning">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Pedidos pendientes -->
        <?php if (Auth::can('pedidos.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(220,53,69,0.12);flex-shrink:0;">
                        <i class="fas fa-shopping-bag fa-lg" style="color:#dc3545;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Pedidos pendientes</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalPedidosPendientes ?>
                        </div>
                        <small class="text-muted"><?= $totalPedidosHoy ?> hoy</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Pedidos/pendientes" class="btn btn-sm btn-danger">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card Clientes -->
        <?php if (Auth::can('clientes.ver')): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:56px;height:56px;background:rgba(23,162,184,0.12);flex-shrink:0;">
                        <i class="fas fa-users fa-lg" style="color:#17a2b8;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Clientes</div>
                        <div class="fw-bold" style="font-size:1.75rem;line-height:1;">
                            <?= $totalClientes ?>
                        </div>
                        <small class="text-muted">registrados</small>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= APP_URL ?>Clientes/index" class="btn btn-sm btn-info">
                        <i class="fas fa-arrow-right me-1"></i>Ver
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- ─────────────────────────────────────────────
         INFORMACIÓN DEL SISTEMA Y USUARIO
         ───────────────────────────────────────────── -->
    <div class="row g-3">

        <!-- Info del usuario autenticado -->
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-user-circle me-2"></i>Mi información
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">
                        <strong>Usuario:</strong> <?= htmlspecialchars(Auth::get('nombre') ?? 'N/A') ?>
                    </p>
                    <p class="text-muted mb-2">
                        <strong>Email:</strong> <?= htmlspecialchars(Auth::get('email') ?? 'N/A') ?>
                    </p>
                    <p class="text-muted mb-0">
                        <strong>Rol:</strong> <?= htmlspecialchars(Auth::get('rol_slug') ?? 'Sin rol') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

</div><!-- /.container-fluid -->

<!-- ═══ TOUR DASHBOARD ═══════════════════════════
     El tour viejo fue reemplazado por el sistema unificado
     multi-página en Content/Dist/js/am-tour.js.
     Esta View ya no define un tour propio.
═════════════════════════════════════════════════ -->
<script>
// Tour viejo desactivado — usar window.amTourIniciar() para iniciar el nuevo.
if (false) {
    const t = amDriver([
        {
            popover: {
                title: `👋 ¡Bienvenida, ${AM_USER_NOMBRE}!`,
                description: `Hola <strong>${AM_USER_NOMBRE}</strong>, este es el panel de administración de <strong>Zona Marcol</strong>. Te guiaré por los módulos principales para que gestiones tu negocio desde el primer día.`
            }
        },
        {
            element: amEl('tour-menu'),
            popover: {
                title: '📋 Menú principal',
                description: 'Accede a todos los módulos desde aquí: Caja, Pedidos, Catálogo, Clientes, Tienda, Facturación, Reportes y Administración. En móvil toca ☰ para abrirlo.',
                side: 'right',
                align: 'start'
            }
        },
        {
            element: amEl('tour-notif'),
            popover: {
                title: '🔔 Notificaciones en tiempo real',
                description: 'Alertas automáticas de nuevos pedidos y eventos del sistema. El número rojo indica cuántas tienes sin leer. Se actualiza cada 30 segundos automáticamente.',
                side: 'bottom',
                align: 'end'
            }
        },
        {
            element: '#tour-cards',
            popover: {
                title: '📊 Resumen del negocio',
                description: 'Vistazo rápido de tu operación diaria: ventas del día, pedidos pendientes y clientes registrados. El punto de partida cada mañana.',
                side: 'bottom'
            }
        },
        {
            element: amEl('tour-caja-link'),
            popover: {
                title: '💰 Caja / Punto de Venta',
                description: 'Registra ventas presenciales. Busca por nombre o código de barras, elige el método de pago (Efectivo, Tarjeta o Transferencia) y cobra. El stock se descuenta automáticamente y genera el recibo.',
                side: 'right'
            }
        },
        {
            element: amEl('tour-pedidos-link'),
            popover: {
                title: '📦 Pedidos en línea',
                description: 'Los pedidos de la tienda aparecen aquí. Cambia el estado: <strong>Pendiente → En preparación → Listo → En camino → Entregado</strong>. Historial completo por pedido.',
                side: 'right'
            }
        },
        {
            element: amEl('tour-reportes-link'),
            popover: {
                title: '📈 Reportes y estadísticas',
                description: 'Gráficas de ventas por día y mes, métodos de pago, top 10 productos, estados de pedidos e inventario con alertas de stock bajo.',
                side: 'right'
            }
        },
        {
            popover: {
                title: `✅ ¡Todo listo, ${AM_USER_NOMBRE}!`,
                description: `Conoces los módulos principales. <strong>Cambia tu contraseña</strong> desde el ícono de perfil arriba a la derecha. Para dudas o problemas, contacta a DeskCod. ¡Mucho éxito!`
            }
        }
    ]);

    if (t) t.drive();
}
</script>