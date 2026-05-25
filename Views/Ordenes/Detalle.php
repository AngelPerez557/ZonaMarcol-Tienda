<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-clipboard-check me-2" style="color:#F5A800;"></i>
                Orden <?= htmlspecialchars($orden->codigo) ?>
                <span class="badge <?= $orden->getEstadoBadge() ?> ms-2" style="font-size:0.7rem;vertical-align:middle;">
                    <?= htmlspecialchars($orden->getEstadoLabel()) ?>
                </span>
            </h4>
            <small class="text-muted">
                Recepción: <?= $orden->fecha_recepcion ? date('d/m/Y H:i', strtotime($orden->fecha_recepcion)) : '—' ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <?php if (Auth::can('servicio.recibir') && !$orden->estaCerrada()): ?>
            <a href="<?= APP_URL ?>Ordenes/registry/<?= $orden->id ?>" class="btn btn-outline-primary">
                <i class="fas fa-edit me-2"></i>Editar
            </a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>Ordenes/index" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Cliente y equipo -->
        <div class="col-12 col-lg-7">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-user me-2"></i>Cliente</div>
                <div class="card-body">
                    <p class="mb-1"><strong><?= htmlspecialchars($orden->cliente_nombre ?? '—') ?></strong></p>
                    <p class="mb-0 text-muted">
                        <i class="fas fa-phone me-1"></i>
                        <?= htmlspecialchars($orden->cliente_telefono ?: 'Sin teléfono') ?>
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fas fa-laptop me-2"></i>Equipo</div>
                <div class="card-body">
                    <p class="mb-2"><strong>Descripción:</strong> <?= htmlspecialchars($orden->equipo_descripcion) ?></p>
                    <p class="mb-2"><strong>Serial / IMEI:</strong> <?= htmlspecialchars($orden->serial ?: '—') ?></p>
                    <p class="mb-2"><strong>Accesorios:</strong> <?= htmlspecialchars($orden->accesorios_entregados ?: '—') ?></p>
                    <p class="mb-0"><strong>Falla reportada:</strong>
                        <?= $orden->diagnostico_inicial
                            ? nl2br(htmlspecialchars($orden->diagnostico_inicial))
                            : '<span class="text-muted">—</span>' ?>
                    </p>
                    <?php if (!empty($orden->observaciones)): ?>
                    <hr>
                    <p class="mb-0"><strong>Observaciones internas:</strong><br>
                        <span class="text-muted"><?= nl2br(htmlspecialchars($orden->observaciones)) ?></span>
                    </p>
                    <?php endif; ?>
                    <?php if ($orden->estado === 'Cancelado' && !empty($orden->motivo_cancelacion)): ?>
                    <hr>
                    <p class="mb-0 text-danger"><strong>Motivo de cancelación:</strong>
                        <?= htmlspecialchars($orden->motivo_cancelacion) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Datos de gestión y totales -->
        <div class="col-12 col-lg-5">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-cogs me-2"></i>Gestión</div>
                <div class="card-body">
                    <p class="mb-2"><strong>Recibido por:</strong> <?= htmlspecialchars($orden->recepcion_nombre ?? '—') ?></p>
                    <p class="mb-2"><strong>Técnico:</strong> <?= htmlspecialchars($orden->tecnico_nombre ?: 'Sin asignar') ?></p>
                    <p class="mb-0"><strong>Entrega:</strong>
                        <?= $orden->fecha_entrega
                            ? date('d/m/Y H:i', strtotime($orden->fecha_entrega))
                            : '<span class="text-muted">Pendiente</span>' ?>
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="fas fa-dollar-sign me-2"></i>Totales</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total presupuesto</span>
                        <span class="fw-semibold"><?= htmlspecialchars($orden->getTotalFormateado()) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pagado</span>
                        <span class="fw-semibold text-success">L. <?= number_format((float) $orden->total_pagado, 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Saldo pendiente</span>
                        <span class="fw-bold text-danger"><?= htmlspecialchars($orden->getSaldoFormateado()) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <p class="text-muted mt-4 mb-0" style="font-size:0.85rem;">
        <i class="fas fa-info-circle me-1"></i>
        El presupuesto de ítems, el avance de estados y los pagos se gestionan en las próximas etapas del módulo.
    </p>

</div>
