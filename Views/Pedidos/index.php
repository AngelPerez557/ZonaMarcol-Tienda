<?php
// Defaults defensivos por si el Controller no inyecta KPIs.
$countHoy = $countHoy ?? 0;
$totalHoy = $totalHoy ?? 0.0;
$pedidos  = $pedidos  ?? [];

// Lista de estados — alineada al ENUM de la tabla `pedidos`.
$estadosOrden = ['Pendiente','En preparacion','Listo','En camino','Entregado','Cancelado'];
?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-shopping-bag me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($pedidos) ?> pedido<?= count($pedidos) !== 1 ? 's' : '' ?> registrado<?= count($pedidos) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Caja/index" class="btn btn-primary btn-sm">
            <i class="fas fa-cash-register me-1"></i>Ir a Caja
        </a>
    </div>

    <!-- Cards resumen -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;background:rgba(245,168,0,0.12);flex-shrink:0;">
                        <i class="fas fa-shopping-cart" style="color:#F5A800;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Pedidos hoy</div>
                        <div class="fw-bold fs-4"><?= (int) $countHoy ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;background:rgba(40,167,69,0.12);flex-shrink:0;">
                        <i class="fas fa-money-bill-wave" style="color:#28a745;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.8rem;">Total hoy</div>
                        <div class="fw-bold fs-5" style="color:#28a745;">L. <?= number_format((float) $totalHoy, 2) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="buscarVenta"
                               placeholder="Buscar por cliente, código o teléfono...">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroMetodo">
                        <option value="">Todos los métodos</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" class="form-control" id="filtroFecha">
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroEstado">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estadosOrden as $e): ?>
                        <option value="<?= htmlspecialchars($e) ?>"><?= htmlspecialchars($e) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex align-items-center justify-content-end gap-2">
                    <small class="text-muted" id="contadorVisible"><?= count($pedidos) ?></small>
                    <select class="form-select form-select-sm" id="porPagina" style="width:auto;">
                        <option value="15">15</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card" id="tablaVentas">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-4">Código</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th class="text-center">Estado</th>
                            <th>Tipo</th>
                            <th>Método</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidos)): ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-shopping-bag fa-2x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
                            No hay pedidos registrados.
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($pedidos as $pedido): ?>
                        <?php
                            $clienteNombre = $pedido->cliente_nombre ?? 'Consumidor final';
                            $cliTel        = $pedido->cliente_telefono ?? '';
                            $codigo        = $pedido->codigo ?? ('#' . $pedido->id);
                            $fechaSrc      = $pedido->created_at ?? '';
                            $fechaTime     = $fechaSrc ? strtotime($fechaSrc) : 0;
                        ?>
                        <tr class="venta-row"
                            data-cliente="<?= strtolower(htmlspecialchars($clienteNombre)) ?>"
                            data-codigo="<?= strtolower(htmlspecialchars($codigo)) ?>"
                            data-telefono="<?= strtolower(htmlspecialchars($cliTel)) ?>"
                            data-metodo="<?= htmlspecialchars($pedido->metodo_pago ?? '') ?>"
                            data-estado="<?= htmlspecialchars($pedido->estado ?? '') ?>"
                            data-fecha="<?= $fechaTime ? date('Y-m-d', $fechaTime) : '' ?>">
                            <td class="ps-4">
                                <span class="fw-bold" style="color:#F5A800;">
                                    <?= htmlspecialchars($codigo) ?>
                                </span>
                            </td>
                            <td class="text-muted" style="font-size:0.85rem;">
                                <?= $fechaTime ? date('d/m/Y H:i', $fechaTime) : '—' ?>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($clienteNombre) ?></div>
                                <?php if ($cliTel): ?>
                                <small class="text-muted">
                                    <i class="fas fa-phone me-1"></i><?= htmlspecialchars($cliTel) ?>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $pedido->getBadgeEstado() ?>">
                                    <?= htmlspecialchars($pedido->estado ?? '—') ?>
                                </span>
                            </td>
                            <td>
                                <small>
                                    <?php if ($pedido->esEnvio()): ?>
                                    <i class="fas fa-truck me-1" style="color:#F5A800;"></i>Envío
                                    <?php elseif ($pedido->esRetiro()): ?>
                                    <i class="fas fa-store me-1" style="color:#F5A800;"></i>Retiro
                                    <?php else: ?>
                                    —
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <?php $iconos = [
                                    'Efectivo'      => 'fa-money-bill-wave text-success',
                                    'Transferencia' => 'fa-mobile-alt text-info',
                                ]; ?>
                                <span class="badge bg-light text-dark border">
                                    <i class="fas <?= $iconos[$pedido->metodo_pago] ?? 'fa-circle' ?> me-1"></i>
                                    <?= htmlspecialchars($pedido->metodo_pago ?? '—') ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold" style="color:#F5A800;">
                                <?= htmlspecialchars($pedido->getTotalFormateado()) ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="<?= APP_URL ?>Pedidos/detalle/<?= (int) $pedido->id ?>"
                                       class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2" id="paginacionWrap">
        <small class="text-muted" id="infoPagina"></small>
        <nav><ul class="pagination pagination-sm mb-0" id="navPagina"></ul></nav>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filas      = [...document.querySelectorAll('.venta-row')];
    const contador   = document.getElementById('contadorVisible');
    const infoPagina = document.getElementById('infoPagina');
    const navPagina  = document.getElementById('navPagina');
    const porPagSel  = document.getElementById('porPagina');

    let porPagina   = 25;
    let pagActual   = 1;
    let filtradas   = filas.map((_, i) => i);

    const buscar       = document.getElementById('buscarVenta');
    const filtroMetodo = document.getElementById('filtroMetodo');
    const filtroFecha  = document.getElementById('filtroFecha');
    const filtroEstado = document.getElementById('filtroEstado');

    function aplicarFiltros() {
        const txt    = buscar.value.toLowerCase();
        const met    = filtroMetodo.value;
        const fecha  = filtroFecha.value;
        const est    = filtroEstado.value;

        filtradas = [];
        filas.forEach((fila, i) => {
            const txtOk = !txt || (
                fila.dataset.cliente.includes(txt) ||
                fila.dataset.codigo.includes(txt)  ||
                (fila.dataset.telefono || '').includes(txt)
            );
            const ok =
                txtOk &&
                (!met   || fila.dataset.metodo === met) &&
                (!fecha || fila.dataset.fecha   === fecha) &&
                (!est   || fila.dataset.estado  === est);
            if (ok) filtradas.push(i);
        });
        pagActual = 1;
        render();
    }

    buscar.addEventListener('input',         aplicarFiltros);
    filtroMetodo.addEventListener('change',  aplicarFiltros);
    filtroFecha.addEventListener('change',   aplicarFiltros);
    filtroEstado.addEventListener('change',  aplicarFiltros);
    porPagSel.addEventListener('change', () => {
        porPagina = parseInt(porPagSel.value); pagActual = 1; render();
    });

    function render() {
        const total   = filtradas.length;
        const paginas = Math.max(1, Math.ceil(total / porPagina));
        if (pagActual > paginas) pagActual = paginas;

        const inicio   = (pagActual - 1) * porPagina;
        const fin      = Math.min(inicio + porPagina, total);
        const visibles = new Set(filtradas.slice(inicio, fin));

        filas.forEach((el, i) => { el.style.display = visibles.has(i) ? '' : 'none'; });

        contador.textContent   = `${total}`;
        infoPagina.textContent = total > 0
            ? `Página ${pagActual} de ${paginas} — ${inicio + 1}–${fin} de ${total}`
            : 'Sin resultados';

        renderNav(paginas);
    }

    function renderNav(paginas) {
        navPagina.innerHTML = '';
        if (paginas <= 1) return;

        const btn = (label, page, disabled, active) => {
            const li = document.createElement('li');
            li.className = `page-item${disabled?' disabled':''}${active?' active':''}`;
            const a = document.createElement('a');
            a.className = 'page-link'; a.href = '#'; a.innerHTML = label;
            if (!disabled && !active) {
                a.addEventListener('click', e => {
                    e.preventDefault();
                    pagActual = page;
                    render();
                    document.getElementById('tablaVentas')
                        .scrollIntoView({behavior:'smooth',block:'start'});
                });
            }
            li.appendChild(a); return li;
        };

        navPagina.appendChild(btn('&laquo;', pagActual - 1, pagActual === 1, false));

        let nums = paginas <= 7 ? Array.from({length:paginas},(_,i)=>i+1) : [1];
        if (paginas > 7) {
            if (pagActual > 3) nums.push('…');
            for (let i = Math.max(2, pagActual-1); i <= Math.min(paginas-1, pagActual+1); i++) nums.push(i);
            if (pagActual < paginas - 2) nums.push('…');
            nums.push(paginas);
        }

        nums.forEach(n => {
            if (n === '…') {
                const li = document.createElement('li');
                li.className='page-item disabled';
                li.innerHTML='<a class="page-link">…</a>';
                navPagina.appendChild(li);
            } else {
                navPagina.appendChild(btn(n, n, false, n === pagActual));
            }
        });

        navPagina.appendChild(btn('&raquo;', pagActual + 1, pagActual === paginas, false));
    }

    filtradas = filas.map((_, i) => i);
    render();
});
</script>
