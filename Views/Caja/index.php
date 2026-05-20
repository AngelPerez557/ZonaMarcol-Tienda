<div class="container-fluid py-3 cash-register">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-cash-register me-2" style="color:#F5A800;"></i>
                Caja / Punto de Venta
            </h4>
            <small class="text-muted">
                <i class="fas fa-user me-1"></i><?= htmlspecialchars(Auth::get('nombre')) ?>
                &nbsp;|&nbsp;
                <i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i') ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Ventas/index" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-history me-1"></i>Historial
        </a>
    </div>

    <!-- Alerta descuento activo -->
    <?php if (!empty($descuentoActivo)): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-3 py-2">
        <i class="fas fa-tag fa-lg"></i>
        <div>
            <strong>Descuento activo: <?= $descuentoActivo['porcentaje'] ?>% — <?= htmlspecialchars($descuentoActivo['nombre']) ?></strong>
            <span class="ms-2 text-muted" style="font-size:0.85rem;">
                Válido hasta <?= date('d/m/Y', strtotime($descuentoActivo['fecha_fin'])) ?>
                <?php if ($descuentoActivo['aplica_a'] === 'categoria'): ?>
                · Solo categoría: <strong><?= htmlspecialchars($descuentoActivo['categoria_nombre'] ?? '') ?></strong>
                <?php endif; ?>
            </span>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-3">

        <!-- ═══════════════════════════════════════════
             COLUMNA IZQUIERDA — Productos
             ═══════════════════════════════════════════ -->
        <div class="col-12 col-lg-7" id="tour-grid-caja">

            <!-- Barra de búsqueda y controles -->
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-5" id="tour-buscador-caja">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text"
                                       class="form-control border-start-0"
                                       id="buscarProducto"
                                       placeholder="Buscar producto o escanear código...">
                                <!-- Scanner cámara para caja -->
                                <button type="button"
                                        class="btn btn-outline-primary"
                                        onclick="window.amBarcodeScanner && window.amBarcodeScanner.open(document.getElementById('buscarProducto'))"
                                        title="Escanear con la cámara">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <select class="form-select form-select-sm" id="filtroCategoria">
                                <option value="">Todas las categorías</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat->nombre) ?>">
                                    <?= htmlspecialchars($cat->nombre) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-4 d-flex justify-content-end gap-2">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary active" id="btnCards" title="Vista cards">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnLista" title="Vista lista">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                            <small class="text-muted align-self-center" id="contadorProductos">
                                <?= count($productos) ?> productos
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── VISTA CARDS ── -->
            <div id="vistaCards">
                <?php if (empty($productos)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-boxes fa-3x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
                    No hay productos activos.
                </div>
                <?php else: ?>
                <div class="row g-2" id="gridCards">
                    <?php foreach ($productos as $producto): ?>
                    <div class="col-6 col-sm-4 col-xl-3 producto-item"
                         data-nombre="<?= strtolower(htmlspecialchars($producto->nombre)) ?>"
                         data-categoria="<?= htmlspecialchars($producto->categoria_nombre) ?>"
                         data-categoria-id="<?= $producto->categoria_id ?>">
                        <div class="card h-100 producto-card-caja"
                             data-id="<?= $producto->id ?>"
                             data-nombre="<?= htmlspecialchars($producto->nombre) ?>"
                             data-precio="<?= $producto->precio_base ?? 0 ?>"
                             data-tiene-variantes="<?= $producto->tieneVariantes() ? '1' : '0' ?>"
                             data-stock="<?= $producto->stock ?>"
                             data-categoria-id="<?= $producto->categoria_id ?>"
                             style="cursor:pointer; transition: transform 0.15s, box-shadow 0.15s;">
                            <div style="height:100px; overflow:hidden; border-radius:8px 8px 0 0;">
                                <div style="
                                    width:100%; height:100%;
                                    background-image: url('<?= $producto->getImageUrl() ?>');
                                    background-size: contain;
                                    background-position: center;
                                    background-repeat: no-repeat;
                                    background-color: #FFFBF2;">
                                </div>
                            </div>
                            <div class="card-body p-2">
                                <div class="fw-semibold" style="font-size:0.8rem; line-height:1.2;">
                                    <?= htmlspecialchars($producto->nombre) ?>
                                </div>
                                <div class="mt-1 d-flex justify-content-between align-items-center">
                                    <span style="color:#F5A800; font-weight:700; font-size:0.85rem;">
                                        <?php if ($producto->tieneVariantes()): ?>
                                            <small class="text-muted">Ver variantes</small>
                                        <?php else: ?>
                                            L. <?= number_format((float)$producto->precio_base, 2) ?>
                                        <?php endif; ?>
                                    </span>
                                    <?php if (!$producto->tieneVariantes()): ?>
                                    <span class="badge <?= $producto->stock > 0 ? 'bg-success' : 'bg-danger' ?>" style="font-size:0.65rem;">
                                        <?= $producto->stock ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- ── VISTA LISTA ── -->
            <div id="vistaLista" style="display:none;">
                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0" id="tablaProductos">
                            <thead>
                                <tr style="background:rgba(245,168,0,0.08);">
                                    <th class="ps-3">Producto</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center">Agregar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                <tr class="producto-item"
                                    data-nombre="<?= strtolower(htmlspecialchars($producto->nombre)) ?>"
                                    data-categoria="<?= htmlspecialchars($producto->categoria_nombre) ?>"
                                    data-categoria-id="<?= $producto->categoria_id ?>">
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:44px;height:44px;flex-shrink:0;border-radius:6px;background-image:url('<?= $producto->getImageUrl() ?>');background-size:contain;background-position:center;background-repeat:no-repeat;background-color:#FFFBF2;border:1px solid #dee2e6;"></div>
                                            <div>
                                                <div class="fw-semibold" style="font-size:0.88rem;"><?= htmlspecialchars($producto->nombre) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($producto->categoria_nombre) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold" style="color:#F5A800;">
                                        <?php if ($producto->tieneVariantes()): ?>
                                            <small class="text-muted">Ver var.</small>
                                        <?php else: ?>
                                            L. <?= number_format((float)$producto->precio_base, 2) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!$producto->tieneVariantes()): ?>
                                        <span class="badge <?= $producto->stock > 0 ? 'bg-success' : 'bg-danger' ?>"><?= $producto->stock ?></span>
                                        <?php else: ?>
                                        <span class="badge bg-info text-dark" style="font-size:0.7rem;">Variantes</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-primary producto-card-caja"
                                                data-id="<?= $producto->id ?>"
                                                data-nombre="<?= htmlspecialchars($producto->nombre) ?>"
                                                data-precio="<?= $producto->precio_base ?? 0 ?>"
                                                data-tiene-variantes="<?= $producto->tieneVariantes() ? '1' : '0' ?>"
                                                data-stock="<?= $producto->stock ?>"
                                                data-categoria-id="<?= $producto->categoria_id ?>">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- ═══════════════════════════════════════════
             COLUMNA DERECHA — Carrito
             ═══════════════════════════════════════════ -->
        <div class="col-12 col-lg-5" id="tour-carrito-caja">
            <div class="card" style="position:sticky; top:80px;">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-shopping-cart me-2"></i>
                        Carrito
                        <span class="badge ms-1" style="background:#F5A800;" id="badgeItems">0</span>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnLimpiarCarrito">
                        <i class="fas fa-trash me-1"></i>Limpiar
                    </button>
                </div>

                <div class="card-body p-0">

                    <!-- Cliente opcional -->
                    <div class="p-3 border-bottom">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent">
                                <i class="fas fa-user text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="buscarCliente"
                                   placeholder="Cliente (opcional)..." autocomplete="off">
                        </div>
                        <div id="resultadosCliente" class="list-group mt-1" style="display:none;"></div>
                        <input type="hidden" id="clienteId" value="">
                        <div id="clienteSeleccionado" style="display:none;" class="mt-1">
                            <span class="badge" style="background:#F5A800;">
                                <i class="fas fa-user me-1"></i>
                                <span id="clienteNombre"></span>
                                <button type="button" class="btn-close btn-close-white btn-sm ms-1"
                                        id="btnQuitarCliente" style="font-size:0.6rem;"></button>
                            </span>
                        </div>
                    </div>

                    <!-- Items del carrito -->
                    <div id="listaCarrito" style="max-height:280px; overflow-y:auto;">
                        <div id="carritoVacio" class="text-center py-4 text-muted">
                            <i class="fas fa-shopping-cart fa-2x mb-2 d-block" style="opacity:0.3;"></i>
                            <small>Agrega productos al carrito</small>
                        </div>
                        <table class="table table-sm mb-0 d-none" id="tablaCarrito">
                            <thead>
                                <tr style="background:rgba(245,168,0,0.06);">
                                    <th class="ps-3">Producto</th>
                                    <th class="text-center" style="width:90px;">Cant.</th>
                                    <th class="text-end">Total</th>
                                    <th style="width:30px;"></th>
                                </tr>
                            </thead>
                            <tbody id="bodyCarrito"></tbody>
                        </table>
                    </div>

                    <!-- Totales -->
                    <div class="p-3 border-top border-bottom">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Subtotal (sin ISV)</span>
                            <span id="txtSubtotalSinIsv">L. 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">ISV 15%</span>
                            <span id="txtIsv">L. 0.00</span>
                        </div>
                        <!-- Descuento activo -->
                        <?php if (!empty($descuentoActivo)): ?>
                        <input type="hidden" id="descuentoPct"      value="<?= $descuentoActivo['porcentaje'] ?>">
                        <input type="hidden" id="descuentoCatId"    value="<?= $descuentoActivo['categoria_id'] ?? '' ?>">
                        <input type="hidden" id="descuentoAplicaA"  value="<?= $descuentoActivo['aplica_a'] ?>">
                        <div class="d-flex justify-content-between mb-1 align-items-center">
                            <span class="text-success fw-semibold" style="font-size:0.88rem;">
                                <i class="fas fa-tag me-1"></i>
                                Descuento (<?= $descuentoActivo['porcentaje'] ?>%)
                                <small class="text-muted fw-normal d-block" style="font-size:0.75rem;">
                                    <?= htmlspecialchars($descuentoActivo['nombre']) ?>
                                </small>
                            </span>
                            <span class="text-success fw-bold" id="txtDescuento">- L. 0.00</span>
                        </div>
                        <?php else: ?>
                        <input type="hidden" id="descuentoPct"     value="0">
                        <input type="hidden" id="descuentoCatId"   value="">
                        <input type="hidden" id="descuentoAplicaA" value="todo">
                        <?php endif; ?>

                        <div class="d-flex justify-content-between fw-bold"
                             style="font-size:1.2rem; border-top:2px solid #F5A800; padding-top:0.5rem; margin-top:0.5rem;">
                            <span>Total</span>
                            <span style="color:#F5A800;" id="txtTotal">L. 0.00</span>
                        </div>
                    </div>

                    <!-- Método de pago -->
                    <div class="p-3 border-bottom" id="tour-metodo-pago">
                        <label class="form-label fw-semibold mb-2" style="font-size:0.85rem;">Método de pago</label>
                        <div class="d-flex gap-2 mb-2">
                            <button type="button" class="btn btn-primary btn-sm flex-fill btn-pago active" data-metodo="Efectivo">
                                <i class="fas fa-money-bill-wave me-1"></i>Efectivo
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill btn-pago" data-metodo="Tarjeta">
                                <i class="fas fa-credit-card me-1"></i>Tarjeta
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill btn-pago" data-metodo="Transferencia">
                                <i class="fas fa-mobile-alt me-1"></i>Transfer.
                            </button>
                        </div>
                        <input type="hidden" id="metodoPago" value="Efectivo">
                        <div id="seccionEfectivo">
                            <div class="input-group input-group-sm mb-1">
                                <span class="input-group-text">L.</span>
                                <input type="number" class="form-control" id="montoRecibido"
                                       placeholder="Monto recibido" min="0" step="0.01">
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted" style="font-size:0.85rem;">Cambio:</span>
                                <span class="fw-bold text-success" id="txtCambio">L. 0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Nota -->
                    <div class="p-3 border-bottom">
                        <input type="text" class="form-control form-control-sm" id="notaVenta"
                               placeholder="Nota (opcional)..." maxlength="255">
                    </div>

                    <!-- Botón cobrar -->
                    <div class="p-3" id="tour-btn-cobrar">
                        <button type="button" class="btn btn-primary w-100 fw-bold"
                                id="btnCobrar" style="font-size:1.1rem; padding:0.75rem;">
                            <i class="fas fa-check-circle me-2"></i>COBRAR
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- MODAL — Seleccionar variante -->
<div class="modal fade" id="modalVariantes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-layer-group me-2" style="color:#F5A800;"></i>Seleccionar variante
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bodyVariantes"></div>
        </div>
    </div>
</div>

<!-- MODAL — Confirmación de cobro -->
<div class="modal fade" id="modalConfirmar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2 text-success"></i>Confirmar venta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bodyConfirmar"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarVenta">
                    <i class="fas fa-check me-1"></i>Confirmar y cobrar
                </button>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
<input type="hidden" id="appUrl"    value="<?= APP_URL ?>">

<script>
document.addEventListener('DOMContentLoaded', function () {

    const APP_URL   = document.getElementById('appUrl').value;
    const csrfToken = document.getElementById('csrfToken').value;

    let carrito = [];

    // ── Descuento activo ─────────────────────────
    const descuentoPct    = parseFloat(document.getElementById('descuentoPct').value)    || 0;
    const descuentoCatId  = document.getElementById('descuentoCatId').value              || '';
    const descuentoAplicaA = document.getElementById('descuentoAplicaA').value           || 'todo';

    // ── Toggle vista cards / lista ───────────────
    document.getElementById('btnCards').addEventListener('click', function () {
        document.getElementById('vistaCards').style.display = '';
        document.getElementById('vistaLista').style.display = 'none';
        this.classList.add('active','btn-primary'); this.classList.remove('btn-outline-primary');
        const b = document.getElementById('btnLista');
        b.classList.remove('active','btn-primary'); b.classList.add('btn-outline-primary');
    });
    document.getElementById('btnLista').addEventListener('click', function () {
        document.getElementById('vistaCards').style.display = 'none';
        document.getElementById('vistaLista').style.display = '';
        this.classList.add('active','btn-primary'); this.classList.remove('btn-outline-primary');
        const b = document.getElementById('btnCards');
        b.classList.remove('active','btn-primary'); b.classList.add('btn-outline-primary');
    });

    // ── Filtros ──────────────────────────────────
    const buscar    = document.getElementById('buscarProducto');
    const filtroCat = document.getElementById('filtroCategoria');
    const contador  = document.getElementById('contadorProductos');

    function filtrarProductos() {
        const texto = buscar.value.toLowerCase();
        const cat   = filtroCat.value;
        let visible = 0;
        document.querySelectorAll('.producto-item').forEach(item => {
            const ok = (!texto || item.dataset.nombre.includes(texto)) &&
                       (!cat   || item.dataset.categoria === cat);
            item.style.display = ok ? '' : 'none';
            if (ok) visible++;
        });
        contador.textContent = `${visible} productos`;
    }

    buscar.addEventListener('input', filtrarProductos);
    filtroCat.addEventListener('change', filtrarProductos);
    buscar.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); buscarPorBarras(this.value.trim()); }
    });

    // ── Código de barras ─────────────────────────
    function buscarPorBarras(codigo) {
        if (!codigo) return;
        fetch(`${APP_URL}Caja/barras?codigo=${encodeURIComponent(codigo)}`)
            .then(r => r.json())
            .then(data => {
                if (data.found) {
                    const p = data.producto;
                    agregarAlCarrito({
                        producto_id:  p.id,
                        variante_id:  p.variante_id || null,
                        nombre:       p.variante_id ? p.nombre + ' — ' + p.variante_nombre : p.nombre,
                        precio:       parseFloat(p.precio),
                        stock:        parseInt(p.stock),
                        categoria_id: p.categoria_id || '',
                    });
                    buscar.value = '';
                    filtrarProductos();
                } else {
                    Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2500 })
                        .fire({ icon:'warning', title:'Código no encontrado' });
                }
            });
    }

    // ── Click en producto ────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.producto-card-caja');
        if (!btn) return;
        const id             = btn.dataset.id;
        const nombre         = btn.dataset.nombre;
        const precio         = parseFloat(btn.dataset.precio) || 0;
        const tieneVariantes = btn.dataset.tieneVariantes === '1';
        const stock          = parseInt(btn.dataset.stock)   || 0;
        const categoriaId    = btn.dataset.categoriaId       || '';

        if (tieneVariantes) {
            mostrarModalVariantes(id, nombre);
        } else {
            if (stock <= 0) {
                Swal.fire({ icon:'warning', title:'Sin stock', text:`"${nombre}" no tiene stock disponible.`, confirmButtonColor:'#F5A800' });
                return;
            }
            agregarAlCarrito({ producto_id:id, variante_id:null, nombre, precio, stock, categoria_id: categoriaId });
        }
    });

    // ── Modal variantes ──────────────────────────
    function mostrarModalVariantes(productoId, nombreProducto) {
        const body = document.getElementById('bodyVariantes');
        body.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin fa-2x" style="color:#F5A800;"></i></div>';
        new bootstrap.Modal(document.getElementById('modalVariantes')).show();

        fetch(`${APP_URL}Caja/buscar?q=${encodeURIComponent(nombreProducto)}`)
            .then(r => r.json())
            .then(data => {
                const producto = data.find(p => p.id == productoId);
                if (!producto || !producto.variantes.length) {
                    body.innerHTML = '<p class="text-muted text-center">No hay variantes disponibles.</p>';
                    return;
                }
                let html = `<p class="fw-semibold mb-3">${nombreProducto}</p><div class="row g-2">`;
                producto.variantes.forEach(v => {
                    const sinStock = v.stock <= 0;
                    html += `
                        <div class="col-6">
                            <button type="button"
                                    class="btn w-100 ${sinStock ? 'btn-outline-secondary' : 'btn-outline-primary'} btn-variante-select"
                                    data-producto-id="${productoId}"
                                    data-variante-id="${v.id}"
                                    data-nombre="${nombreProducto} — ${v.nombre}"
                                    data-precio="${v.precio}"
                                    data-stock="${v.stock}"
                                    data-categoria-id="${producto.categoria_id || ''}"
                                    ${sinStock ? 'disabled' : ''}>
                                <div class="fw-semibold">${v.nombre}</div>
                                <div style="color:#F5A800;font-size:0.85rem;">L. ${parseFloat(v.precio).toFixed(2)}</div>
                                <div class="badge ${sinStock ? 'bg-danger' : 'bg-success'}" style="font-size:0.65rem;">
                                    ${sinStock ? 'Agotado' : 'Stock: ' + v.stock}
                                </div>
                            </button>
                        </div>`;
                });
                html += '</div>';
                body.innerHTML = html;
            });
    }

    document.getElementById('modalVariantes').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-variante-select');
        if (!btn) return;
        agregarAlCarrito({
            producto_id:  btn.dataset.productoId,
            variante_id:  btn.dataset.varianteId,
            nombre:       btn.dataset.nombre,
            precio:       parseFloat(btn.dataset.precio),
            stock:        parseInt(btn.dataset.stock),
            categoria_id: btn.dataset.categoriaId || '',
        });
        bootstrap.Modal.getInstance(document.getElementById('modalVariantes')).hide();
    });

    // ── Descuento por item ───────────────────────
    // Si aplica_a = 'categoria' solo descuenta si el producto es de esa categoría
    function getPrecioConDescuento(item) {
        if (descuentoPct <= 0) return item.precio;
        if (descuentoAplicaA === 'todo') {
            return item.precio * (1 - descuentoPct / 100);
        }
        if (descuentoAplicaA === 'categoria' && descuentoCatId &&
            String(item.categoria_id) === String(descuentoCatId)) {
            return item.precio * (1 - descuentoPct / 100);
        }
        return item.precio;
    }

    // ── Carrito ──────────────────────────────────
    function agregarAlCarrito(item) {
        const key = item.variante_id ? `v${item.variante_id}` : `p${item.producto_id}`;
        const existente = carrito.find(c => c.key === key);
        if (existente) {
            if (existente.cantidad >= item.stock) {
                Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2500 })
                    .fire({ icon:'warning', title:`Stock máximo para "${item.nombre}"` });
                return;
            }
            existente.cantidad++;
        } else {
            carrito.push({
                key,
                producto_id:  item.producto_id,
                variante_id:  item.variante_id || null,
                nombre:       item.nombre,
                precio:       item.precio,
                categoria_id: item.categoria_id || '',
                stock:        item.stock,
                cantidad:     1,
            });
        }
        renderCarrito();
        Swal.mixin({ toast:true, position:'bottom-end', showConfirmButton:false, timer:1000 })
            .fire({ icon:'success', title:`"${item.nombre}" agregado` });
    }

    function quitarDelCarrito(key) {
        carrito = carrito.filter(c => c.key !== key);
        renderCarrito();
    }

    function actualizarCantidad(key, cantidad) {
        const item = carrito.find(c => c.key === key);
        if (!item) return;
        cantidad = parseInt(cantidad);
        if (isNaN(cantidad) || cantidad < 1) { quitarDelCarrito(key); return; }
        if (cantidad > item.stock) {
            cantidad = item.stock;
            Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2000 })
                .fire({ icon:'warning', title:'Stock máximo alcanzado' });
        }
        item.cantidad = cantidad;
        renderCarrito();
    }

    function renderCarrito() {
        const body  = document.getElementById('bodyCarrito');
        const tabla = document.getElementById('tablaCarrito');
        const vacio = document.getElementById('carritoVacio');
        const badge = document.getElementById('badgeItems');

        if (carrito.length === 0) {
            tabla.classList.add('d-none');
            vacio.style.display = '';
            badge.textContent = '0';
            actualizarTotales();
            return;
        }

        tabla.classList.remove('d-none');
        vacio.style.display = 'none';
        badge.textContent = carrito.reduce((s, c) => s + c.cantidad, 0);

        body.innerHTML = carrito.map(item => {
            const precioDesc = getPrecioConDescuento(item);
            const tieneDesc  = precioDesc < item.precio;
            return `
            <tr>
                <td class="ps-3" style="font-size:0.82rem;">
                    <div class="fw-semibold">${item.nombre}</div>
                    <small class="text-muted">
                        ${tieneDesc
                            ? `<span class="text-decoration-line-through">L. ${item.precio.toFixed(2)}</span>
                               <span class="text-success ms-1">L. ${precioDesc.toFixed(2)}</span>`
                            : `L. ${item.precio.toFixed(2)}`
                        } c/u
                    </small>
                </td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-1 py-0"
                                onclick="window._cajaCantidad('${item.key}', ${item.cantidad - 1})"
                                style="min-width:24px;">−</button>
                        <input type="number" class="form-control form-control-sm text-center p-0"
                               value="${item.cantidad}" min="1" max="${item.stock}" style="width:40px;"
                               onchange="window._cajaCantidad('${item.key}', this.value)">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-1 py-0"
                                onclick="window._cajaCantidad('${item.key}', ${item.cantidad + 1})"
                                style="min-width:24px;">+</button>
                    </div>
                </td>
                <td class="text-end fw-bold" style="color:#F5A800;">
                    L. ${(precioDesc * item.cantidad).toFixed(2)}
                </td>
                <td>
                    <button type="button" class="btn btn-sm text-danger p-0"
                            onclick="window._cajaQuitar('${item.key}')">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');

        actualizarTotales();
    }

    window._cajaCantidad = actualizarCantidad;
    window._cajaQuitar   = quitarDelCarrito;

    // ── Totales ──────────────────────────────────
    function actualizarTotales() {
        // Calcular subtotal con precios con descuento aplicado por item
        const subtotalConDesc = carrito.reduce((s, c) => s + (getPrecioConDescuento(c) * c.cantidad), 0);
        const subtotalSinDesc = carrito.reduce((s, c) => s + (c.precio * c.cantidad), 0);
        const descuentoMonto  = subtotalSinDesc - subtotalConDesc;
        const subtotalSinIsv  = subtotalConDesc / 1.15;
        const isv             = subtotalConDesc - subtotalSinIsv;

        document.getElementById('txtSubtotalSinIsv').textContent = `L. ${subtotalSinIsv.toFixed(2)}`;
        document.getElementById('txtIsv').textContent            = `L. ${isv.toFixed(2)}`;
        document.getElementById('txtTotal').textContent          = `L. ${subtotalConDesc.toFixed(2)}`;

        const txtDesc = document.getElementById('txtDescuento');
        if (txtDesc) txtDesc.textContent = `- L. ${descuentoMonto.toFixed(2)}`;

        calcularCambio();
    }

    function calcularCambio() {
        const total    = carrito.reduce((s, c) => s + (getPrecioConDescuento(c) * c.cantidad), 0);
        const recibido = parseFloat(document.getElementById('montoRecibido').value) || 0;
        const cambio   = recibido - total;
        const txt      = document.getElementById('txtCambio');
        txt.textContent = `L. ${Math.max(cambio, 0).toFixed(2)}`;
        txt.style.color = cambio >= 0 ? '#28a745' : '#dc3545';
    }

    document.getElementById('montoRecibido').addEventListener('input', calcularCambio);

    // ── Método de pago ───────────────────────────
    document.querySelectorAll('.btn-pago').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-pago').forEach(b => {
                b.classList.remove('active','btn-primary');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.add('active','btn-primary');
            this.classList.remove('btn-outline-secondary');
            document.getElementById('metodoPago').value = this.dataset.metodo;
            document.getElementById('seccionEfectivo').style.display =
                this.dataset.metodo === 'Efectivo' ? '' : 'none';
        });
    });

    // ── Limpiar carrito ──────────────────────────
    document.getElementById('btnLimpiarCarrito').addEventListener('click', function () {
        if (!carrito.length) return;
        Swal.fire({
            icon:'warning', title:'¿Limpiar carrito?', text:'Se eliminarán todos los productos.',
            showCancelButton:true, confirmButtonColor:'#dc3545',
            confirmButtonText:'Sí, limpiar', cancelButtonText:'Cancelar'
        }).then(r => { if (r.isConfirmed) { carrito = []; renderCarrito(); } });
    });

    // ── Buscar cliente ───────────────────────────
    let clienteTimer = null;
    document.getElementById('buscarCliente').addEventListener('input', function () {
        clearTimeout(clienteTimer);
        const q = this.value.trim();
        if (q.length < 2) { document.getElementById('resultadosCliente').style.display = 'none'; return; }
        clienteTimer = setTimeout(() => {
            fetch(`${APP_URL}Clientes/search?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    const lista = document.getElementById('resultadosCliente');
                    if (!data.length) { lista.style.display = 'none'; return; }
                    lista.innerHTML = data.map(c => `
                        <button type="button" class="list-group-item list-group-item-action py-1 btn-cliente"
                                data-id="${c.id}" data-nombre="${c.nombre}">
                            <i class="fas fa-user me-2 text-muted"></i>
                            <strong>${c.nombre}</strong>
                            <small class="text-muted ms-2">${c.telefono || c.email || ''}</small>
                        </button>`).join('');
                    lista.style.display = '';
                });
        }, 300);
    });

    document.getElementById('resultadosCliente').addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-cliente');
        if (!btn) return;
        document.getElementById('clienteId').value           = btn.dataset.id;
        document.getElementById('clienteNombre').textContent = btn.dataset.nombre;
        document.getElementById('clienteSeleccionado').style.display = '';
        document.getElementById('buscarCliente').value = '';
        this.style.display = 'none';
    });

    document.getElementById('btnQuitarCliente').addEventListener('click', function () {
        document.getElementById('clienteId').value = '';
        document.getElementById('clienteSeleccionado').style.display = 'none';
        document.getElementById('buscarCliente').value = '';
    });

    // ── Cobrar ───────────────────────────────────
    document.getElementById('btnCobrar').addEventListener('click', function () {
        if (!carrito.length) {
            Swal.fire({ icon:'warning', title:'Carrito vacío', text:'Agrega productos antes de cobrar.', confirmButtonColor:'#F5A800' });
            return;
        }

        const metodo          = document.getElementById('metodoPago').value;
        const recibido        = parseFloat(document.getElementById('montoRecibido').value) || 0;
        const subtotalSinDesc = carrito.reduce((s, c) => s + (c.precio * c.cantidad), 0);
        const total           = carrito.reduce((s, c) => s + (getPrecioConDescuento(c) * c.cantidad), 0);
        const descuentoMonto  = subtotalSinDesc - total;
        const isv             = total - (total / 1.15);
        const cambio          = recibido - total;

        if (metodo === 'Efectivo' && recibido < total) {
            Swal.fire({ icon:'warning', title:'Monto insuficiente', text:'El monto recibido es menor al total.', confirmButtonColor:'#F5A800' });
            return;
        }

        document.getElementById('bodyConfirmar').innerHTML = `
            <div class="table-responsive">
                <table class="table table-sm mb-3">
                    <tbody>
                        ${carrito.map(c => {
                            const pd = getPrecioConDescuento(c);
                            return `<tr>
                                <td>${c.nombre}</td>
                                <td class="text-center">x${c.cantidad}</td>
                                <td class="text-end fw-bold">L. ${(pd * c.cantidad).toFixed(2)}</td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            </div>
            <div class="border-top pt-2">
                <div class="d-flex justify-content-between">
                    <span>Subtotal sin ISV:</span><strong>L. ${(total/1.15).toFixed(2)}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>ISV 15%:</span><strong>L. ${isv.toFixed(2)}</strong>
                </div>
                ${descuentoMonto > 0 ? `
                <div class="d-flex justify-content-between text-success">
                    <span>Descuento (${descuentoPct}%):</span>
                    <strong>- L. ${descuentoMonto.toFixed(2)}</strong>
                </div>` : ''}
                <div class="d-flex justify-content-between fs-5">
                    <span>Total:</span><strong style="color:#F5A800;">L. ${total.toFixed(2)}</strong>
                </div>
                ${metodo === 'Efectivo' ? `
                <div class="d-flex justify-content-between">
                    <span>Recibido:</span><strong>L. ${recibido.toFixed(2)}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Cambio:</span><strong class="text-success">L. ${cambio.toFixed(2)}</strong>
                </div>` : ''}
                <div class="mt-2">
                    <span class="badge" style="background:#F5A800;font-size:0.9rem;">
                        <i class="fas fa-credit-card me-1"></i>${metodo}
                    </span>
                </div>
            </div>`;

        new bootstrap.Modal(document.getElementById('modalConfirmar')).show();
    });

    document.getElementById('btnConfirmarVenta').addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Procesando...';

        const total          = carrito.reduce((s, c) => s + (getPrecioConDescuento(c) * c.cantidad), 0);
        const subtotalSinDesc = carrito.reduce((s, c) => s + (c.precio * c.cantidad), 0);
        const descuentoMonto = subtotalSinDesc - total;

        const formData = new FormData();
        formData.append('csrf_token',     csrfToken);
        formData.append('cliente_id',     document.getElementById('clienteId').value);
        formData.append('metodo_pago',    document.getElementById('metodoPago').value);
        formData.append('monto_recibido', document.getElementById('montoRecibido').value);
        formData.append('nota',           document.getElementById('notaVenta').value);
        formData.append('descuento_pct',  descuentoPct);
        formData.append('items',          JSON.stringify(carrito));

        fetch(`${APP_URL}Caja/cobrar`, { method:'POST', body:formData })
        .then(r => r.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmar')).hide();
            if (data.success) {
                carrito = [];
                renderCarrito();
                document.getElementById('montoRecibido').value = '';
                document.getElementById('notaVenta').value     = '';
                document.getElementById('clienteId').value     = '';
                document.getElementById('clienteSeleccionado').style.display = 'none';

                Swal.fire({
                    icon:'success', title:'¡Venta registrada!',
                    html:`<div class="fs-4 fw-bold" style="color:#F5A800;">Total: L. ${parseFloat(data.total).toFixed(2)}</div>
                          ${data.cambio !== null ? `<div class="text-success">Cambio: L. ${parseFloat(data.cambio).toFixed(2)}</div>` : ''}
                          ${data.descuento > 0 ? `<div class="text-success">Descuento aplicado: L. ${parseFloat(data.descuento).toFixed(2)}</div>` : ''}`,
                    confirmButtonColor:'#F5A800',
                    confirmButtonText:'<i class="fas fa-print me-1"></i>Imprimir recibo',
                    showCancelButton:true, cancelButtonText:'Nueva venta'
                }).then(result => {
                    if (result.isConfirmed) window.open(`${APP_URL}Caja/recibo/${data.venta_id}`, '_blank');
                });
            } else {
                Swal.fire({ icon:'error', title:'Error', text: data.message || 'No se pudo procesar.', confirmButtonColor:'#F5A800' });
            }
        })
        .catch(() => {
            Swal.fire({ icon:'error', title:'Error de conexión', text:'No se pudo conectar.', confirmButtonColor:'#F5A800' });
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-check me-1"></i>Confirmar y cobrar';
        });
    });

});
</script>

<!-- Tour viejo de Caja desactivado.
     Ahora gestionado por el sistema unificado en Content/Dist/js/am-tour.js
     que incluye los pasos de Caja como parte del flujo completo. -->