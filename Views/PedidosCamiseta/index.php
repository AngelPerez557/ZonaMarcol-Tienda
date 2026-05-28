<?php
/**
 * Views/PedidosCamiseta/index.php — Bandeja de pedidos de camisetas online.
 * Filtro por estado en JS sobre el mismo dataset.
 */
$estadosOrden = ['Pendiente_pago','Confirmado','En_proveedor','Recibido','Entregado','Cancelado'];
?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-tshirt me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($pedidos) ?> pedido<?= count($pedidos) !== 1 ? 's' : '' ?>
                · <?= (int) $conteos['Pendiente_pago'] ?> pendiente<?= $conteos['Pendiente_pago'] !== 1 ? 's' : '' ?> de pago
            </small>
        </div>
    </div>

    <!-- Filtros por estado -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-sm btn-dark filtro-estado on" data-estado="">
            Todos (<?= count($pedidos) ?>)
        </button>
        <?php foreach ($estadosOrden as $e): ?>
        <button type="button" class="btn btn-sm btn-outline-secondary filtro-estado"
                data-estado="<?= htmlspecialchars($e) ?>">
            <?= htmlspecialchars(str_replace('_', ' ', $e)) ?> (<?= (int) $conteos[$e] ?>)
        </button>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-4">Código</th>
                            <th>Cliente</th>
                            <th>Temporada</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Anticipo</th>
                            <th class="text-center">Comprob.</th>
                            <th class="text-center">Estado</th>
                            <th>Creado</th>
                            <th class="text-center">Acc.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidos)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Todavía no hay pedidos online de camisetas.
                            </td>
                        </tr>
                        <?php else: foreach ($pedidos as $p): ?>
                        <tr class="fila-pedido" data-estado="<?= htmlspecialchars($p->estado) ?>">
                            <td class="ps-4 fw-semibold"><?= htmlspecialchars($p->codigo) ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($p->cliente_nombre ?? '—') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($p->cliente_telefono ?? '') ?></small>
                            </td>
                            <td><small><?= htmlspecialchars($p->temporada_nombre ?? '—') ?></small></td>
                            <td class="text-end fw-semibold">L. <?= number_format((float) $p->total, 2) ?></td>
                            <td class="text-end">L. <?= number_format((float) $p->anticipo_pagado, 2) ?></td>
                            <td class="text-center">
                                <?php if (!empty($p->comprobante_path)): ?>
                                    <i class="fas fa-receipt" style="color:#F5A800;" title="Comprobante adjunto"></i>
                                <?php else: ?>
                                    <i class="fas fa-minus text-muted"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $p->getBadgeEstado() ?>">
                                    <?= htmlspecialchars($p->getEstadoLabel()) ?>
                                </span>
                            </td>
                            <td><small><?= htmlspecialchars($p->created_at ?? '') ?></small></td>
                            <td class="text-center">
                                <a href="<?= APP_URL ?>PedidosCamiseta/detalle/<?= (int) $p->id ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.filtro-estado.on{background:#F5A800 !important;color:#1a1a1a !important;border-color:#F5A800 !important;font-weight:700;}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filas = document.querySelectorAll('.fila-pedido');
    document.querySelectorAll('.filtro-estado').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filtro-estado').forEach(b => b.classList.remove('on'));
            this.classList.add('on');
            const est = this.dataset.estado;
            filas.forEach(function (fila) {
                fila.style.display = (est === '' || fila.dataset.estado === est) ? '' : 'none';
            });
        });
    });
});
</script>
