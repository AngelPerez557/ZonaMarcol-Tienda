<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-history me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                Historial de compras del cliente
            </small>
        </div>
        <a href="<?= APP_URL ?>Clientes/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <!-- ─────────────────────────────────────────────
         INFO DEL CLIENTE
         ───────────────────────────────────────────── -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:56px; height:56px;
                            background:rgba(245,168,0,0.12); flex-shrink:0;">
                    <i class="fas fa-user fa-lg" style="color:#F5A800;"></i>
                </div>
                <div class="flex-fill">
                    <div class="fw-bold fs-5"><?= htmlspecialchars($cliente->nombre) ?></div>
                    <div class="text-muted" style="font-size:0.85rem;">
                        <?php if ($cliente->email): ?>
                        <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($cliente->email) ?>
                        <?php endif; ?>
                        <?php if ($cliente->telefono): ?>
                        &nbsp;|&nbsp;
                        <i class="fab fa-whatsapp me-1 text-success"></i><?= htmlspecialchars($cliente->telefono) ?>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">
                        Cliente desde <?= date('d/m/Y', strtotime($cliente->created_at)) ?>
                    </small>
                </div>
                <span class="badge <?= $cliente->isActivo() ? 'bg-success' : 'bg-secondary' ?>">
                    <?= $cliente->isActivo() ? 'Activo' : 'Inactivo' ?>
                </span>
            </div>
        </div>
    </div>

    <!-- ─────────────────────────────────────────────
         HISTORIAL DE COMPRAS
         ───────────────────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-shopping-bag me-2"></i>Compras registradas
        </div>
        <div class="card-body p-0">
            <?php if (empty($ventas)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-shopping-bag fa-2x mb-3 d-block"
                   style="color:#F5A800;opacity:0.4;"></i>
                Este cliente aún no tiene compras registradas.
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-4">#Venta</th>
                            <th>Fecha</th>
                            <th>Método pago</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Recibo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">
                                #<?= str_pad($venta['id'], 6, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($venta['created_at'])) ?></td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($venta['metodo_pago']) ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold" style="color:#F5A800;">
                                L. <?= number_format((float)$venta['total'], 2) ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= APP_URL ?>Caja/recibo/<?= $venta['id'] ?>"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Ver recibo">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>