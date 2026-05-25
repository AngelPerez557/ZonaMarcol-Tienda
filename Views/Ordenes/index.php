<?php
$estados = ['Recibido','Diagnostico','Esperando aprobacion','En reparacion','Listo','Entregado','Cancelado'];
?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-clipboard-check me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($ordenes) ?> orden<?= count($ordenes) !== 1 ? 'es' : '' ?> registrada<?= count($ordenes) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <?php if (Auth::can('servicio.recibir')): ?>
        <a href="<?= APP_URL ?>Ordenes/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Orden
        </a>
        <?php endif; ?>
    </div>

    <!-- Filtros por estado (lado cliente) -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-sm btn-dark filtro-estado" data-estado="">
            Todas (<?= count($ordenes) ?>)
        </button>
        <?php foreach ($estados as $e): ?>
        <button type="button" class="btn btn-sm btn-outline-secondary filtro-estado" data-estado="<?= htmlspecialchars($e) ?>">
            <?= htmlspecialchars($e) ?> (<?= (int) ($conteos[$e] ?? 0) ?>)
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
                            <th>Equipo</th>
                            <th>Técnico</th>
                            <th class="text-center">Estado</th>
                            <th>Recepción</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ordenes)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-check fa-2x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
                                No hay órdenes de servicio registradas.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($ordenes as $orden): ?>
                        <tr class="fila-orden" data-estado="<?= htmlspecialchars($orden->estado) ?>">
                            <td class="ps-4 fw-semibold"><?= htmlspecialchars($orden->codigo) ?></td>
                            <td><?= htmlspecialchars($orden->cliente_nombre ?? '—') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($orden->equipo_descripcion) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($orden->tecnico_nombre ?? 'Sin asignar') ?></td>
                            <td class="text-center">
                                <span class="badge <?= $orden->getEstadoBadge() ?>">
                                    <?= htmlspecialchars($orden->getEstadoLabel()) ?>
                                </span>
                            </td>
                            <td class="text-muted">
                                <?= $orden->fecha_recepcion
                                    ? date('d/m/Y', strtotime($orden->fecha_recepcion))
                                    : '—' ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= APP_URL ?>Ordenes/detalle/<?= $orden->id ?>"
                                   class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
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
    const botones = document.querySelectorAll('.filtro-estado');
    const filas   = document.querySelectorAll('.fila-orden');

    botones.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const estado = this.dataset.estado;

            // Resaltar el botón activo
            botones.forEach(b => {
                b.classList.remove('btn-dark');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-dark');

            // Mostrar / ocultar filas
            filas.forEach(function (fila) {
                fila.style.display = (estado === '' || fila.dataset.estado === estado) ? '' : 'none';
            });
        });
    });
});
</script>
