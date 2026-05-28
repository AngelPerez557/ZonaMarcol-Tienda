<?php
/**
 * Views/Solicitudes/index.php — Bandeja de solicitudes de servicio online.
 * Filtro por estado del lado cliente. Cada fila ofrece atender o rechazar.
 */
$estados = ['Pendiente', 'Atendida', 'Rechazada'];
$conteos = ['Pendiente'=>0, 'Atendida'=>0, 'Rechazada'=>0];
foreach ($solicitudes as $s) {
    if (isset($conteos[$s->estado])) {
        $conteos[$s->estado]++;
    }
}
?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-inbox me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($solicitudes) ?> solicitud<?= count($solicitudes) !== 1 ? 'es' : '' ?>
                · <?= (int) $pendientes ?> pendiente<?= $pendientes !== 1 ? 's' : '' ?> de atender
            </small>
        </div>
    </div>

    <!-- Filtros por estado (JS) -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-sm btn-dark filtro-estado on" data-estado="">
            Todas (<?= count($solicitudes) ?>)
        </button>
        <?php foreach ($estados as $e): ?>
        <button type="button" class="btn btn-sm btn-outline-secondary filtro-estado"
                data-estado="<?= htmlspecialchars($e) ?>">
            <?= htmlspecialchars($e) ?> (<?= (int) $conteos[$e] ?>)
        </button>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-4">#</th>
                            <th>Cliente</th>
                            <th>Equipo</th>
                            <th>Falla reportada</th>
                            <th>Contacto</th>
                            <th class="text-center">Estado</th>
                            <th>Recibida</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($solicitudes)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Aún no hay solicitudes registradas.
                            </td>
                        </tr>
                        <?php else: foreach ($solicitudes as $s): ?>
                        <tr class="fila-solicitud" data-estado="<?= htmlspecialchars($s->estado) ?>">
                            <td class="ps-4 fw-semibold">#<?= (int) $s->id ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($s->cliente_nombre ?? '—') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($s->cliente_email ?? '') ?></small>
                            </td>
                            <td><?= htmlspecialchars($s->equipo_descripcion ?? '') ?></td>
                            <td>
                                <?php if ($s->falla_reportada): ?>
                                    <small><?= nl2br(htmlspecialchars($s->falla_reportada)) ?></small>
                                <?php else: ?>
                                    <small class="text-muted">—</small>
                                <?php endif; ?>
                            </td>
                            <td><small><?= htmlspecialchars($s->telefono_contacto ?: '—') ?></small></td>
                            <td class="text-center">
                                <span class="badge <?= $s->getEstadoBadge() ?>">
                                    <?= htmlspecialchars($s->estado) ?>
                                </span>
                                <?php if ($s->isAtendida() && $s->codigo_orden): ?>
                                    <div><small class="text-muted">→ <?= htmlspecialchars($s->codigo_orden) ?></small></div>
                                <?php elseif ($s->isRechazada() && $s->motivo_rechazo): ?>
                                    <div><small class="text-danger"><?= htmlspecialchars($s->motivo_rechazo) ?></small></div>
                                <?php endif; ?>
                            </td>
                            <td><small><?= htmlspecialchars($s->created_at ?? '') ?></small></td>
                            <td class="text-center">
                                <?php if ($s->isPendiente()): ?>
                                <a href="<?= APP_URL ?>Solicitudes/atender/<?= (int) $s->id ?>"
                                   class="btn btn-sm btn-success" title="Crear orden">
                                    <i class="fas fa-check"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-rechazar"
                                        data-id="<?= (int) $s->id ?>"
                                        data-cliente="<?= htmlspecialchars($s->cliente_nombre ?? '') ?>"
                                        title="Rechazar">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php elseif ($s->isAtendida() && $s->orden_servicio_id): ?>
                                <a href="<?= APP_URL ?>Ordenes/detalle/<?= (int) $s->orden_servicio_id ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Ver orden">
                                    <i class="fas fa-clipboard-check"></i>
                                </a>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rechazar -->
<div class="modal fade" id="modalRechazar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>Solicitudes/rechazar" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <input type="hidden" name="id" id="rechId">
            <div class="modal-header">
                <h5 class="modal-title">Rechazar solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    Vas a rechazar la solicitud de <strong id="rechCliente">—</strong>.
                </p>
                <label class="form-label fw-semibold">Motivo <span class="text-danger">*</span></label>
                <textarea name="motivo" rows="3" class="form-control" required
                          placeholder="Ej: equipo fuera de garantía, no se reparan ese modelo..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times me-1"></i>Rechazar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.filtro-estado.on{background:#F5A800 !important;color:#1a1a1a !important;border-color:#F5A800 !important;font-weight:700;}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Filtro por estado.
    const filas = document.querySelectorAll('.fila-solicitud');
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

    // Abrir modal de rechazo con datos cargados.
    const modal = new bootstrap.Modal(document.getElementById('modalRechazar'));
    document.querySelectorAll('.btn-rechazar').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('rechId').value      = this.dataset.id;
            document.getElementById('rechCliente').textContent = this.dataset.cliente || 'el cliente';
            modal.show();
        });
    });
});
</script>
