<div class="container-fluid py-4">

    <!-- CABECERA -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-boxes me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($productos) ?> producto<?= count($productos) !== 1 ? 's' : '' ?> registrado<?= count($productos) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <?php if (Auth::can('productos.crear')): ?>
        <a href="<?= APP_URL ?>Productos/registry" class="btn btn-primary" id="tour-btn-nuevo-prod">
            <i class="fas fa-plus me-2"></i>Nuevo Producto
        </a>
        <?php endif; ?>
    </div>

    <!-- FILTROS -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0"
                               id="buscarProducto" placeholder="Buscar por nombre...">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroCategoria">
                        <option value="">Todas las categorías</option>
                        <?php
                        $cats = array_unique(array_filter(array_map(fn($p) => $p->categoria_nombre, $productos)));
                        sort($cats);
                        foreach ($cats as $cat):
                        ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroEstado">
                        <option value="">Activos e inactivos</option>
                        <option value="1">Solo activos</option>
                        <option value="0">Solo inactivos</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroImagen">
                        <option value="">Con y sin imagen</option>
                        <option value="1">Con imagen</option>
                        <option value="0">Sin imagen</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroVisible">
                        <option value="">Visibilidad tienda</option>
                        <option value="1">Visibles en tienda</option>
                        <option value="0">Ocultos en tienda</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnVistaTarjeta" title="Vista tarjeta">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnVistaLista" title="Vista lista">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <small class="text-muted" id="contadorVisible">
                        Mostrando <?= count($productos) ?>
                    </small>
                    <!-- Items por página -->
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted">Por página:</small>
                        <select class="form-select form-select-sm" id="porPagina" style="width:auto;">
                            <option value="12">12</option>
                            <option value="24" selected>24</option>
                            <option value="48">48</option>
                            <option value="96">96</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($productos)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-boxes fa-3x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
        No hay productos registrados.
        <?php if (Auth::can('productos.crear')): ?>
        <br><a href="<?= APP_URL ?>Productos/registry" class="btn btn-primary mt-3">
            <i class="fas fa-plus me-2"></i>Crear el primero
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>

    <div id="tour-tabla-productos">

    <!-- ── VISTA TARJETA ── -->
    <div class="row g-3 d-none" id="gridProductos">
        <?php $primerToggle = true; foreach ($productos as $producto): ?>
        <div class="col-12 col-sm-6 col-md-4 col-xl-3 producto-item"
             data-nombre="<?= strtolower(htmlspecialchars($producto->nombre)) ?>"
             data-categoria="<?= htmlspecialchars($producto->categoria_nombre ?? '') ?>"
             data-activo="<?= $producto->activo ?>"
             data-imagen="<?= empty($producto->image_url) ? '0' : '1' ?>"
             data-visible="<?= $producto->visible_tienda ?>">
            <div class="card h-100 <?= !$producto->isActivo() ? 'opacity-50' : '' ?>">
                <div class="position-relative overflow-hidden" style="height:180px; flex-shrink:0;">
                    <div style="width:100%;height:100%;
                        background-image:url('<?= $producto->getImageUrl() ?>');
                        background-size:contain;background-position:center;
                        background-repeat:no-repeat;background-color:#FFFBF2;">
                    </div>
                    <?php if ($producto->tieneVariantes()): ?>
                    <span class="position-absolute top-0 start-0 m-2 badge" style="background:#F5A800;">
                        <i class="fas fa-layer-group me-1"></i>Variantes
                    </span>
                    <?php endif; ?>
                    <span class="position-absolute top-0 end-0 m-2 badge <?= $producto->isActivo() ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $producto->isActivo() ? 'Activo' : 'Inactivo' ?>
                    </span>
                    <?php if (!$producto->isVisibleTienda()): ?>
                    <span class="position-absolute bottom-0 start-0 m-2 badge bg-warning text-dark">
                        <i class="fas fa-eye-slash me-1"></i>Oculto tienda
                    </span>
                    <?php endif; ?>
                </div>
                <div class="card-body d-flex flex-column">
                    <small class="text-muted mb-1">
                        <i class="fas fa-tag me-1" style="color:#F5A800;"></i>
                        <?= htmlspecialchars($producto->categoria_nombre ?? '—') ?>
                    </small>
                    <h6 class="card-title fw-bold mb-2"><?= htmlspecialchars($producto->nombre) ?></h6>
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="fw-bold" style="color:#F5A800;font-size:1.1rem;">
                            <?= $producto->getPrecioFormateado() ?>
                        </span>
                        <?php if (!$producto->tieneVariantes()): ?>
                        <span class="badge <?= $producto->stock > 0 ? 'bg-success' : 'bg-danger' ?>">
                            <i class="fas fa-cubes me-1"></i><?= $producto->stock ?> uds.
                        </span>
                        <?php else: ?>
                        <span class="badge bg-info text-dark">
                            <i class="fas fa-layer-group me-1"></i>Ver variantes
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <?php if (Auth::can('productos.editar')): ?>
                        <div class="form-check form-switch mb-0" <?= $primerToggle ? 'id="tour-toggle-activo"' : '' ?>>
                            <?php $primerToggle = false; ?>
                            <input class="form-check-input toggle-activo" type="checkbox" role="switch"
                                   id="toggle-<?= $producto->id ?>"
                                   data-id="<?= $producto->id ?>"
                                   data-url="<?= APP_URL ?>Productos/toggle"
                                   data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                   <?= $producto->isActivo() ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="toggle-<?= $producto->id ?>">Activo</label>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex gap-2">
                            <?php if (Auth::can('productos.editar')): ?>
                            <a href="<?= APP_URL ?>Productos/registry/<?= $producto->id ?>"
                               class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (Auth::can('productos.eliminar')): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                    data-id="<?= $producto->id ?>"
                                    data-nombre="<?= htmlspecialchars($producto->nombre) ?>"
                                    data-url="<?= APP_URL ?>Productos/delete"
                                    data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                    title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (Auth::can('productos.editar')): ?>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input toggle-visible" type="checkbox" role="switch"
                               id="visible-<?= $producto->id ?>"
                               data-id="<?= $producto->id ?>"
                               data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                               <?= $producto->isVisibleTienda() ? 'checked' : '' ?>>
                        <label class="form-check-label small text-muted" for="visible-<?= $producto->id ?>">
                            <i class="fas fa-store me-1"></i>Visible en tienda
                        </label>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── VISTA LISTA ── -->
    <div class="card d-none" id="tablaProductos">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-3">Producto</th>
                            <th>Categoría</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Activo</th>
                            <th class="text-center">Tienda</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyLista">
                        <?php foreach ($productos as $producto): ?>
                        <tr class="producto-item"
                            data-nombre="<?= strtolower(htmlspecialchars($producto->nombre)) ?>"
                            data-categoria="<?= htmlspecialchars($producto->categoria_nombre ?? '') ?>"
                            data-activo="<?= $producto->activo ?>"
                            data-imagen="<?= empty($producto->image_url) ? '0' : '1' ?>"
                            data-visible="<?= $producto->visible_tienda ?>">
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= $producto->getImageUrl() ?>"
                                         style="width:40px;height:40px;object-fit:contain;border-radius:6px;background:#FFFBF2;">
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($producto->nombre) ?></div>
                                        <?php if ($producto->tieneVariantes()): ?>
                                        <small class="badge" style="background:#F5A800;font-size:0.65rem;">Variantes</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($producto->categoria_nombre ?? '—') ?></td>
                            <td class="text-end fw-bold" style="color:#F5A800;"><?= $producto->getPrecioFormateado() ?></td>
                            <td class="text-center">
                                <?php if (!$producto->tieneVariantes()): ?>
                                <span class="badge <?= $producto->stock > 0 ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $producto->stock ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (Auth::can('productos.editar')): ?>
                                <div class="form-check form-switch d-inline-block mb-0">
                                    <input class="form-check-input toggle-activo" type="checkbox" role="switch"
                                           data-id="<?= $producto->id ?>"
                                           data-url="<?= APP_URL ?>Productos/toggle"
                                           data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                           <?= $producto->isActivo() ? 'checked' : '' ?>>
                                </div>
                                <?php else: ?>
                                <span class="badge <?= $producto->isActivo() ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $producto->isActivo() ? 'Sí' : 'No' ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if (Auth::can('productos.editar')): ?>
                                <div class="form-check form-switch d-inline-block mb-0">
                                    <input class="form-check-input toggle-visible" type="checkbox" role="switch"
                                           data-id="<?= $producto->id ?>"
                                           data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                           <?= $producto->isVisibleTienda() ? 'checked' : '' ?>>
                                </div>
                                <?php else: ?>
                                <span class="badge <?= $producto->isVisibleTienda() ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= $producto->isVisibleTienda() ? 'Sí' : 'No' ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <?php if (Auth::can('productos.editar')): ?>
                                    <a href="<?= APP_URL ?>Productos/registry/<?= $producto->id ?>"
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (Auth::can('productos.eliminar')): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="<?= $producto->id ?>"
                                            data-nombre="<?= htmlspecialchars($producto->nombre) ?>"
                                            data-url="<?= APP_URL ?>Productos/delete"
                                            data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── PAGINACIÓN ── -->
    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2" id="paginacionWrap">
        <small class="text-muted" id="infoPagina"></small>
        <nav>
            <ul class="pagination pagination-sm mb-0" id="paginacion"></ul>
        </nav>
    </div>

    </div><!-- /#tour-tabla-productos -->
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const APP_URL = '<?= APP_URL ?>';
    const csrf    = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';

    // ── Estado global ────────────────────────────
    let paginaActual  = 1;
    let porPagina     = 24;
    let vistaActual   = localStorage.getItem('productos_vista') || 'tarjeta';
    let itemsFiltrados = []; // todos los items visibles después de filtros

    const gridProductos  = document.getElementById('gridProductos');
    const tablaProductos = document.getElementById('tablaProductos');
    const btnTarjeta     = document.getElementById('btnVistaTarjeta');
    const btnLista       = document.getElementById('btnVistaLista');
    const contador       = document.getElementById('contadorVisible');
    const infoPagina     = document.getElementById('infoPagina');
    const paginacion     = document.getElementById('paginacion');
    const selectPorPag   = document.getElementById('porPagina');

    // Todos los items (tarjeta + fila tabla — referencia paralela)
    const todosCards = [...document.querySelectorAll('#gridProductos .producto-item')];
    const todasFilas = [...document.querySelectorAll('#tbodyLista .producto-item')];

    // ── Vista ────────────────────────────────────
    aplicarVista(vistaActual);

    btnTarjeta?.addEventListener('click', () => { vistaActual = 'tarjeta'; localStorage.setItem('productos_vista','tarjeta'); aplicarVista('tarjeta'); renderPagina(); });
    btnLista?.addEventListener('click',   () => { vistaActual = 'lista';   localStorage.setItem('productos_vista','lista');   aplicarVista('lista');   renderPagina(); });

    function aplicarVista(vista) {
        if (vista === 'lista') {
            gridProductos?.classList.add('d-none');
            tablaProductos?.classList.remove('d-none');
            btnLista?.classList.replace('btn-outline-secondary','btn-secondary');
            btnTarjeta?.classList.replace('btn-secondary','btn-outline-secondary');
        } else {
            gridProductos?.classList.remove('d-none');
            tablaProductos?.classList.add('d-none');
            btnTarjeta?.classList.replace('btn-outline-secondary','btn-secondary');
            btnLista?.classList.replace('btn-secondary','btn-outline-secondary');
        }
    }

    // ── Filtros ──────────────────────────────────
    const buscar    = document.getElementById('buscarProducto');
    const filtroCat = document.getElementById('filtroCategoria');
    const filtroEst = document.getElementById('filtroEstado');
    const filtroImg = document.getElementById('filtroImagen');
    const filtroVis = document.getElementById('filtroVisible');

    function getItems() {
        // Referencia siempre en los cards — los índices son paralelos con filas
        return todosCards;
    }

    function filtrar() {
        const texto   = buscar.value.toLowerCase().trim();
        const cat     = filtroCat.value;
        const estado  = filtroEst.value;
        const imagen  = filtroImg.value;
        const visible = filtroVis.value;

        // Filtra por datos y construye lista de índices visibles
        itemsFiltrados = [];
        todosCards.forEach((card, i) => {
            const ok =
                (!texto   || card.dataset.nombre.includes(texto)) &&
                (!cat     || card.dataset.categoria === cat) &&
                (!estado  || card.dataset.activo    === estado) &&
                (!imagen  || card.dataset.imagen    === imagen) &&
                (!visible || card.dataset.visible   === visible);
            if (ok) itemsFiltrados.push(i);
        });

        paginaActual = 1;
        renderPagina();
    }

    // ── Render página ────────────────────────────
    function renderPagina() {
        const totalItems   = itemsFiltrados.length;
        const totalPaginas = Math.max(1, Math.ceil(totalItems / porPagina));
        if (paginaActual > totalPaginas) paginaActual = totalPaginas;

        const inicio = (paginaActual - 1) * porPagina;
        const fin    = Math.min(inicio + porPagina, totalItems);
        const visibles = new Set(itemsFiltrados.slice(inicio, fin));

        // Mostrar/ocultar cards
        todosCards.forEach((card, i) => {
            card.style.display = visibles.has(i) ? '' : 'none';
        });
        // Mostrar/ocultar filas tabla (índice paralelo)
        todasFilas.forEach((fila, i) => {
            fila.style.display = visibles.has(i) ? '' : 'none';
        });

        // Contador
        contador.textContent = `Mostrando ${totalItems} producto${totalItems !== 1 ? 's' : ''}`;
        infoPagina.textContent = totalItems > 0
            ? `Página ${paginaActual} de ${totalPaginas} — ${inicio + 1}–${fin} de ${totalItems}`
            : 'Sin resultados';

        renderControlesPaginacion(totalPaginas);
    }

    // ── Controles de paginación ──────────────────
    function renderControlesPaginacion(totalPaginas) {
        paginacion.innerHTML = '';
        if (totalPaginas <= 1) return;

        const crear = (label, page, disabled, active) => {
            const li  = document.createElement('li');
            li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
            const a   = document.createElement('a');
            a.className = 'page-link';
            a.href      = '#';
            a.innerHTML = label;
            if (!disabled && !active) {
                a.addEventListener('click', e => {
                    e.preventDefault();
                    paginaActual = page;
                    renderPagina();
                    // Scroll suave al top de la tabla
                    document.getElementById('tour-tabla-productos')?.scrollIntoView({ behavior:'smooth', block:'start' });
                });
            }
            li.appendChild(a);
            return li;
        };

        // Anterior
        paginacion.appendChild(crear('&laquo;', paginaActual - 1, paginaActual === 1, false));

        // Páginas — máximo 5 visibles con elipsis
        let paginas = [];
        if (totalPaginas <= 7) {
            for (let i = 1; i <= totalPaginas; i++) paginas.push(i);
        } else {
            paginas = [1];
            if (paginaActual > 3) paginas.push('...');
            for (let i = Math.max(2, paginaActual - 1); i <= Math.min(totalPaginas - 1, paginaActual + 1); i++) {
                paginas.push(i);
            }
            if (paginaActual < totalPaginas - 2) paginas.push('...');
            paginas.push(totalPaginas);
        }

        paginas.forEach(p => {
            if (p === '...') {
                const li = document.createElement('li');
                li.className = 'page-item disabled';
                li.innerHTML = '<a class="page-link">…</a>';
                paginacion.appendChild(li);
            } else {
                paginacion.appendChild(crear(p, p, false, p === paginaActual));
            }
        });

        // Siguiente
        paginacion.appendChild(crear('&raquo;', paginaActual + 1, paginaActual === totalPaginas, false));
    }

    // ── Por página ───────────────────────────────
    selectPorPag?.addEventListener('change', function () {
        porPagina    = parseInt(this.value);
        paginaActual = 1;
        renderPagina();
    });

    // ── Listeners filtros ────────────────────────
    buscar.addEventListener('input',     filtrar);
    filtroCat.addEventListener('change', filtrar);
    filtroEst.addEventListener('change', filtrar);
    filtroImg.addEventListener('change', filtrar);
    filtroVis.addEventListener('change', filtrar);

    // ── Init ─────────────────────────────────────
    // Inicializar itemsFiltrados con todos los índices
    itemsFiltrados = todosCards.map((_, i) => i);
    renderPagina();

    // ── Toggle activo ────────────────────────────
    document.querySelectorAll('.toggle-activo').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const id     = this.dataset.id;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;
            const activo = this.checked ? 1 : 0;
            const self   = this;
            const card   = this.closest('.producto-item');

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&activo=${activo}&csrf_token=${csrf}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (card) {
                        card.dataset.activo = activo;
                        const cardEl = card.querySelector('.card');
                        cardEl?.classList.toggle('opacity-50', activo === 0);
                        const badge = card.querySelector('.badge.bg-success, .badge.bg-secondary');
                        if (badge) {
                            badge.className = activo
                                ? 'position-absolute top-0 end-0 m-2 badge bg-success'
                                : 'position-absolute top-0 end-0 m-2 badge bg-secondary';
                            badge.textContent = activo ? 'Activo' : 'Inactivo';
                        }
                    }
                    Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2000 })
                        .fire({ icon:'success', title: activo ? 'Producto activado' : 'Producto desactivado' });
                } else {
                    self.checked = !self.checked;
                    Swal.fire({ icon:'warning', title:'No permitido', text: data.message ?? 'Error', confirmButtonColor:'#F5A800' });
                }
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });

    // ── Toggle visible tienda ────────────────────
    document.querySelectorAll('.toggle-visible').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const id      = this.dataset.id;
            const csrf    = this.dataset.csrf;
            const visible = this.checked ? 1 : 0;
            const self    = this;
            const card    = this.closest('.producto-item');

            fetch(`${APP_URL}Productos/toggleVisible`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&visible=${visible}&csrf_token=${csrf}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (card) card.dataset.visible = visible;
                    Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:2000 })
                        .fire({ icon:'success', title: visible ? 'Visible en tienda' : 'Oculto en tienda' });
                } else {
                    self.checked = !self.checked;
                    Swal.fire({ icon:'error', title:'Error', text:'No se pudo actualizar', confirmButtonColor:'#F5A800' });
                }
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });

    // ── Eliminar ─────────────────────────────────
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;
            const nombre = this.dataset.nombre;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;

            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar producto?',
                text: `"${nombre}" será desactivado.`,
                showCancelButton:   true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor:  '#6c757d',
                confirmButtonText:  'Sí, eliminar',
                cancelButtonText:   'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = `
                        <input type="hidden" name="id" value="${id}">
                        <input type="hidden" name="csrf_token" value="${csrf}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

});
</script>