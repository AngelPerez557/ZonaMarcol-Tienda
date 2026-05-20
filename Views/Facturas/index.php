<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-file-invoice-dollar me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($ventas) ?> factura<?= count($ventas) !== 1 ? 's' : '' ?> emitida<?= count($ventas) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <?php if (Auth::can('facturacion.configurar')): ?>
        <a href="<?= APP_URL ?>Facturas/config" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-sliders-h me-1"></i>Configuración
        </a>
        <?php endif; ?>
    </div>

    <!-- ─────────────────────────────────────────────
         ESTADO DE FACTURACIÓN
         ───────────────────────────────────────────── -->
    <?php if ($config): ?>
    <div class="card mb-4" style="border-left:4px solid #F5A800;">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <small class="text-muted d-block">CAI</small>
                    <code style="font-size:0.8rem; color:#8C6300;">
                        <?= htmlspecialchars($config['cai'] ?? 'No configurado') ?>
                    </code>
                </div>
                <div class="col-6 col-md-2">
                    <small class="text-muted d-block">Correlativo actual</small>
                    <span class="fw-bold" style="color:#F5A800;">
                        <?= str_pad($config['correlativo'] ?? 1, 8, '0', STR_PAD_LEFT) ?>
                    </span>
                </div>
                <div class="col-6 col-md-2">
                    <small class="text-muted d-block">Fecha límite</small>
                    <span class="<?= isset($config['fecha_limite']) && strtotime($config['fecha_limite']) < time() ? 'text-danger fw-bold' : '' ?>">
                        <?= $config['fecha_limite']
                            ? date('d/m/Y', strtotime($config['fecha_limite']))
                            : '—' ?>
                    </span>
                </div>
                <div class="col-12 col-md-4">
                    <small class="text-muted d-block">Rango autorizado</small>
                    <span style="font-size:0.85rem;">
                        <?= htmlspecialchars($config['rango_desde'] ?? '—') ?>
                        <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:0.7rem;"></i>
                        <?= htmlspecialchars($config['rango_hasta'] ?? '—') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ─────────────────────────────────────────────
         FILTROS
         ───────────────────────────────────────────── -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text"
                               class="form-control border-start-0"
                               id="buscarFactura"
                               placeholder="Buscar por cliente o cajero...">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select class="form-select" id="filtroMetodo">
                        <option value="">Todos los métodos</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <input type="date" class="form-control" id="filtroFecha">
                </div>
                <div class="col-12 col-md-2 text-end">
                    <small class="text-muted" id="contadorVisible">
                        Mostrando <?= count($ventas) ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────
         TABLA
         ───────────────────────────────────────────── -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-4"># Factura</th>
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
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-file-invoice fa-2x mb-3 d-block"
                                   style="color:#F5A800;opacity:0.4;"></i>
                                No hay facturas emitidas.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($ventas as $venta): ?>
                        <tr class="factura-row"
                            data-cliente="<?= strtolower(htmlspecialchars($venta['cliente_nombre'] ?? 'consumidor final')) ?>"
                            data-cajero="<?= strtolower(htmlspecialchars($venta['cajero_nombre'] ?? '')) ?>"
                            data-metodo="<?= htmlspecialchars($venta['metodo_pago'] ?? '') ?>"
                            data-fecha="<?= date('Y-m-d', strtotime($venta['created_at'])) ?>">

                            <td class="ps-4">
                                <span class="fw-bold" style="color:#F5A800;">
                                    #<?= str_pad($venta['id'], 8, '0', STR_PAD_LEFT) ?>
                                </span>
                            </td>
                            <td class="text-muted" style="font-size:0.85rem;">
                                <?= date('d/m/Y H:i', strtotime($venta['created_at'])) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($venta['cliente_nombre'] ?? 'Consumidor final') ?>
                            </td>
                            <td class="text-muted">
                                <?= htmlspecialchars($venta['cajero_nombre'] ?? '—') ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?php
                                    $iconos = [
                                        'Efectivo'       => 'fa-money-bill-wave',
                                        'Tarjeta'        => 'fa-credit-card',
                                        'Transferencia'  => 'fa-mobile-alt',
                                    ];
                                    $icono = $iconos[$venta['metodo_pago']] ?? 'fa-circle';
                                    ?>
                                    <i class="fas <?= $icono ?> me-1"></i>
                                    <?= htmlspecialchars($venta['metodo_pago'] ?? '—') ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold" style="color:#F5A800;">
                                L. <?= number_format((float)$venta['total'], 2) ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= APP_URL ?>Caja/recibo/<?= $venta['id'] ?>"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Ver factura">
                                    <i class="fas fa-print me-1"></i>Imprimir
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const buscar      = document.getElementById('buscarFactura');
    const filtroMet   = document.getElementById('filtroMetodo');
    const filtroFecha = document.getElementById('filtroFecha');
    const contador    = document.getElementById('contadorVisible');
    const filas       = document.querySelectorAll('.factura-row');

    function filtrar() {
        const texto  = buscar.value.toLowerCase();
        const metodo = filtroMet.value;
        const fecha  = filtroFecha.value;
        let visible  = 0;

        filas.forEach(fila => {
            const cliente   = fila.dataset.cliente  || '';
            const cajero    = fila.dataset.cajero   || '';
            const metFila   = fila.dataset.metodo   || '';
            const fechaFila = fila.dataset.fecha    || '';

            const okTexto  = cliente.includes(texto) || cajero.includes(texto);
            const okMetodo = !metodo || metFila === metodo;
            const okFecha  = !fecha  || fechaFila === fecha;

            if (okTexto && okMetodo && okFecha) {
                fila.style.display = '';
                visible++;
            } else {
                fila.style.display = 'none';
            }
        });

        contador.textContent = `Mostrando ${visible}`;
    }

    buscar.addEventListener('input', filtrar);
    filtroMet.addEventListener('change', filtrar);
    filtroFecha.addEventListener('change', filtrar);

});
</script>