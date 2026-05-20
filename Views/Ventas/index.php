<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-history me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($ventas) ?> venta<?= count($ventas) !== 1 ? 's' : '' ?> registrada<?= count($ventas) !== 1 ? 's' : '' ?>
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
                        <div class="text-muted" style="font-size:0.8rem;">Ventas hoy</div>
                        <div class="fw-bold fs-4"><?= $countHoy ?></div>
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
                        <div class="fw-bold fs-5" style="color:#28a745;">L. <?= number_format($totalHoy, 2) ?></div>
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
                        <input type="text" class="form-control border-start-0" id="buscarVenta" placeholder="Buscar por cliente o cajero...">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroMetodo">
                        <option value="">Todos los métodos</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" class="form-control" id="filtroFecha">
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select" id="filtroAnulada">
                        <option value="">Todas</option>
                        <option value="0">Solo activas</option>
                        <option value="1">Solo anuladas</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex align-items-center justify-content-end gap-2">
                    <small class="text-muted" id="contadorVisible"><?= count($ventas) ?></small>
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
                            <th class="ps-4"># Venta</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Cajero</th>
                            <th>Método</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ventas)): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-history fa-2x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
                            No hay ventas registradas.
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($ventas as $venta): ?>
                        <tr class="venta-row"
                            data-cliente="<?= strtolower(htmlspecialchars($venta['cliente_nombre'] ?? 'consumidor final')) ?>"
                            data-cajero="<?= strtolower(htmlspecialchars($venta['cajero_nombre'] ?? '')) ?>"
                            data-metodo="<?= htmlspecialchars($venta['metodo_pago'] ?? '') ?>"
                            data-fecha="<?= date('Y-m-d', strtotime($venta['created_at'])) ?>"
                            data-anulada="<?= (int)($venta['anulada'] ?? 0) ?>">
                            <td class="ps-4">
                                <span class="fw-bold" style="color:#F5A800;">#<?= str_pad($venta['id'], 8, '0', STR_PAD_LEFT) ?></span>
                                <?php if ((int)($venta['anulada'] ?? 0)): ?>
                                <span class="badge bg-danger ms-1" style="font-size:0.65rem;">ANULADA</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted" style="font-size:0.85rem;"><?= date('d/m/Y H:i', strtotime($venta['created_at'])) ?></td>
                            <td><div class="fw-semibold"><?= htmlspecialchars($venta['cliente_nombre'] ?? 'Consumidor final') ?></div></td>
                            <td class="text-muted"><?= htmlspecialchars($venta['cajero_nombre'] ?? '—') ?></td>
                            <td>
                                <?php $iconos = ['Efectivo'=>'fa-money-bill-wave text-success','Tarjeta'=>'fa-credit-card text-primary','Transferencia'=>'fa-mobile-alt text-info']; ?>
                                <span class="badge bg-light text-dark border">
                                    <i class="fas <?= $iconos[$venta['metodo_pago']] ?? 'fa-circle' ?> me-1"></i>
                                    <?= htmlspecialchars($venta['metodo_pago'] ?? '—') ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold" style="color:#F5A800;">L. <?= number_format((float)$venta['total'], 2) ?></td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="<?= APP_URL ?>Ventas/detalle/<?= $venta['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver detalle"><i class="fas fa-eye"></i></a>
                                    <a href="<?= APP_URL ?>Caja/recibo/<?= $venta['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Imprimir"><i class="fas fa-print"></i></a>
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

    // ── Filtros ──────────────────────────────────
    const buscar       = document.getElementById('buscarVenta');
    const filtroMetodo = document.getElementById('filtroMetodo');
    const filtroFecha  = document.getElementById('filtroFecha');
    const filtroAnul   = document.getElementById('filtroAnulada');

    function aplicarFiltros() {
        const txt    = buscar.value.toLowerCase();
        const met    = filtroMetodo.value;
        const fecha  = filtroFecha.value;
        const anul   = filtroAnul.value;

        filtradas = [];
        filas.forEach((fila, i) => {
            const ok =
                (!txt   || fila.dataset.cliente.includes(txt) || fila.dataset.cajero.includes(txt)) &&
                (!met   || fila.dataset.metodo   === met) &&
                (!fecha || fila.dataset.fecha     === fecha) &&
                (!anul  || fila.dataset.anulada   === anul);
            if (ok) filtradas.push(i);
        });
        pagActual = 1;
        render();
    }

    buscar.addEventListener('input',           aplicarFiltros);
    filtroMetodo.addEventListener('change',    aplicarFiltros);
    filtroFecha.addEventListener('change',     aplicarFiltros);
    filtroAnul.addEventListener('change',      aplicarFiltros);
    porPagSel.addEventListener('change', () => { porPagina = parseInt(porPagSel.value); pagActual = 1; render(); });

    // ── Render ───────────────────────────────────
    function render() {
        const total   = filtradas.length;
        const paginas = Math.max(1, Math.ceil(total / porPagina));
        if (pagActual > paginas) pagActual = paginas;

        const inicio   = (pagActual - 1) * porPagina;
        const fin      = Math.min(inicio + porPagina, total);
        const visibles = new Set(filtradas.slice(inicio, fin));

        filas.forEach((el, i) => { el.style.display = visibles.has(i) ? '' : 'none'; });

        contador.textContent  = `${total}`;
        infoPagina.textContent = total > 0 ? `Página ${pagActual} de ${paginas} — ${inicio + 1}–${fin} de ${total}` : 'Sin resultados';

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
            if (!disabled && !active) a.addEventListener('click', e => { e.preventDefault(); pagActual = page; render(); document.getElementById('tablaVentas').scrollIntoView({behavior:'smooth',block:'start'}); });
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
            if (n === '…') { const li = document.createElement('li'); li.className='page-item disabled'; li.innerHTML='<a class="page-link">…</a>'; navPagina.appendChild(li); }
            else navPagina.appendChild(btn(n, n, false, n === pagActual));
        });

        navPagina.appendChild(btn('&raquo;', pagActual + 1, pagActual === paginas, false));
    }

    filtradas = filas.map((_, i) => i);
    render();
});
</script>