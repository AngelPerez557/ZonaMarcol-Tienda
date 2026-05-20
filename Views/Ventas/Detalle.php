<div class="container-fluid py-4">

    <!-- CABECERA -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-receipt me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <div class="d-flex align-items-center gap-2 mt-1">
                <small class="text-muted">
                    <?= date('d/m/Y H:i', strtotime($venta['created_at'])) ?>
                </small>
                <?php if ((int)($venta['anulada'] ?? 0) === 1): ?>
                <span class="badge bg-danger">
                    <i class="fas fa-ban me-1"></i>ANULADA
                </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <?php if ((int)($venta['anulada'] ?? 0) === 0): ?>
            <a href="<?= APP_URL ?>Caja/recibo/<?= $venta['id'] ?>"
               target="_blank"
               class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-print me-1"></i>Imprimir
            </a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>Ventas/index" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <?php if ((int)($venta['anulada'] ?? 0) === 1): ?>
    <!-- AVISO ANULACIÓN -->
    <div class="alert alert-danger d-flex align-items-start gap-3 mb-4">
        <i class="fas fa-ban fa-2x mt-1"></i>
        <div>
            <div class="fw-bold fs-5">Esta venta fue anulada</div>
            <div class="mt-1">
                <strong>Motivo:</strong> <?= htmlspecialchars($venta['motivo_anulacion'] ?? '—') ?>
            </div>
            <?php if ($venta['anulada_at']): ?>
            <small class="text-muted">
                <?= date('d/m/Y H:i', strtotime($venta['anulada_at'])) ?>
            </small>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Columna izquierda -->
        <div class="col-12 col-lg-4">

            <!-- Datos de la venta -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i>Información
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Cliente</td>
                                <td class="fw-semibold">
                                    <?= htmlspecialchars($venta['cliente_nombre'] ?? 'Consumidor final') ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Cajero</td>
                                <td><?= htmlspecialchars($venta['cajero_nombre'] ?? '—') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Método</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= htmlspecialchars($venta['metodo_pago'] ?? '—') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Fecha</td>
                                <td><?= date('d/m/Y H:i', strtotime($venta['created_at'])) ?></td>
                            </tr>
                            <?php if ($venta['nota']): ?>
                            <tr>
                                <td class="text-muted">Nota</td>
                                <td class="fst-italic"><?= htmlspecialchars($venta['nota']) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totales -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-calculator me-2"></i>Totales
                </div>
                <div class="card-body">
                    <?php
                    $total          = (float) $venta['total'];
                    $subtotalSinIsv = $total / 1.15;
                    $isv            = $total - $subtotalSinIsv;
                    $montoRecibido  = (float) ($venta['monto_recibido'] ?? 0);
                    $cambio         = (float) ($venta['cambio'] ?? 0);
                    ?>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Subtotal sin ISV</span>
                        <span>L. <?= number_format($subtotalSinIsv, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">ISV 15%</span>
                        <span>L. <?= number_format($isv, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold pt-2 border-top"
                         style="font-size:1.15rem;">
                        <span>Total</span>
                        <span style="color:<?= (int)($venta['anulada']??0) ? '#dc3545' : '#F5A800' ?>;">
                            L. <?= number_format($total, 2) ?>
                            <?= (int)($venta['anulada']??0) ? '<small class="text-danger ms-1">(ANULADA)</small>' : '' ?>
                        </span>
                    </div>
                    <?php if ($venta['metodo_pago'] === 'Efectivo' && !(int)($venta['anulada']??0)): ?>
                    <div class="d-flex justify-content-between mt-2 text-muted">
                        <span>Recibido</span>
                        <span>L. <?= number_format($montoRecibido, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-success fw-semibold">
                        <span>Cambio</span>
                        <span>L. <?= number_format($cambio, 2) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($config && $venta['correlativo']): ?>
                    <div class="mt-3 pt-2 border-top">
                        <small class="text-muted d-block">Factura fiscal</small>
                        <code style="color:#8C6300; font-size:0.8rem;">
                            <?= htmlspecialchars(
                                ($config['establecimiento'] ?? '000') . '-' .
                                ($config['punto_emision']   ?? '001') . '-01-' .
                                str_pad($venta['correlativo'], 8, '0', STR_PAD_LEFT)
                            ) ?>
                        </code>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botón anular — solo si tiene permiso y no está anulada -->
            <?php if (Auth::can('ventas.eliminar') && (int)($venta['anulada'] ?? 0) === 0): ?>
            <div class="card border-danger">
                <div class="card-header text-danger" style="background:rgba(220,53,69,0.06);">
                    <i class="fas fa-ban me-2"></i>Zona de anulación
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3" style="font-size:0.85rem;">
                        La anulación no elimina el registro — es obligatorio conservarlo por ley fiscal hondureña.
                        Solo marca la venta como inválida.
                    </p>
                    <div class="mb-3">
                        <label for="motivoAnulacion" class="form-label fw-semibold text-danger">
                            Motivo de anulación <span class="text-danger">*</span>
                        </label>
                        <textarea id="motivoAnulacion"
                                  class="form-control form-control-sm"
                                  rows="3"
                                  maxlength="500"
                                  placeholder="Describe el motivo..."></textarea>
                    </div>
                    <button type="button"
                            class="btn btn-danger w-100"
                            id="btnAnular"
                            data-id="<?= $venta['id'] ?>"
                            data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <i class="fas fa-ban me-2"></i>Anular venta
                    </button>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Columna derecha — Productos -->
        <div class="col-12 col-lg-8">
            <div class="card <?= (int)($venta['anulada']??0) ? 'opacity-75' : '' ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-boxes me-2"></i>Productos vendidos
                    </span>
                    <span class="badge" style="background:#F5A800;">
                        <?= count($detalle) ?> ítem<?= count($detalle) !== 1 ? 's' : '' ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr style="background:rgba(245,168,0,0.08);">
                                <th class="ps-3">Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">P. Unitario</th>
                                <th class="text-end pe-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($detalle)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Sin detalle registrado.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($detalle as $item): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($item['nombre_producto']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        x<?= $item['cantidad'] ?>
                                    </span>
                                </td>
                                <td class="text-end text-muted">
                                    L. <?= number_format((float)$item['precio_unit'], 2) ?>
                                </td>
                                <td class="text-end pe-3 fw-bold" style="color:#F5A800;">
                                    L. <?= number_format((float)$item['subtotal'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:rgba(245,168,0,0.06);">
                                <td colspan="3" class="text-end fw-bold ps-3">Total:</td>
                                <td class="text-end pe-3 fw-bold fs-5" style="color:#F5A800;">
                                    L. <?= number_format($total, 2) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<input type="hidden" id="appUrl" value="<?= APP_URL ?>">

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnAnular = document.getElementById('btnAnular');
    if (!btnAnular) return;

    btnAnular.addEventListener('click', function () {
        const id     = this.dataset.id;
        const csrf   = this.dataset.csrf;
        const motivo = document.getElementById('motivoAnulacion').value.trim();
        const appUrl = document.getElementById('appUrl').value;

        if (!motivo) {
            Swal.fire({ icon:'warning', title:'Campo requerido', text:'Ingresa el motivo de anulación.', confirmButtonColor:'#dc3545' });
            return;
        }

        Swal.fire({
            icon:               'warning',
            title:              '¿Anular esta venta?',
            html:               'Esta acción <strong>no se puede deshacer</strong>.<br>El registro se conserva por ley fiscal.',
            showCancelButton:   true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor:  '#6c757d',
            confirmButtonText:  'Sí, anular',
            cancelButtonText:   'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            btnAnular.disabled    = true;
            btnAnular.innerHTML   = '<i class="fas fa-spinner fa-spin me-2"></i>Anulando...';

            const form = new FormData();
            form.append('csrf_token', csrf);
            form.append('id',         id);
            form.append('motivo',     motivo);

            fetch(`${appUrl}Ventas/anular`, { method:'POST', body:form })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon:'success', title:'Venta anulada',
                        text: data.message,
                        confirmButtonColor:'#F5A800',
                        timer:2000, showConfirmButton:false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({ icon:'error', title:'Error', text:data.message, confirmButtonColor:'#dc3545' });
                    btnAnular.disabled  = false;
                    btnAnular.innerHTML = '<i class="fas fa-ban me-2"></i>Anular venta';
                }
            });
        });
    });
});
</script>