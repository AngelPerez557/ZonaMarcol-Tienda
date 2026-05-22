<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-tshirt me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($equipaciones) ?> equipaci<?= count($equipaciones) !== 1 ? 'ones' : 'ón' ?> registrada<?= count($equipaciones) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Equipaciones/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Equipación
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-4">#</th>
                            <th>Camisa</th>
                            <th>Equipo</th>
                            <th>Temporada</th>
                            <th>Tipo</th>
                            <th>Versión</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($equipaciones)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-tshirt fa-2x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
                                No hay equipaciones registradas.
                                <a href="<?= APP_URL ?>Equipaciones/registry">Crear la primera</a>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($equipaciones as $i => $eqp): ?>
                        <tr>
                            <td class="ps-4 text-muted"><?= $i + 1 ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($eqp->getImagenUrl()) ?>"
                                     alt="Camisa"
                                     style="width:42px;height:42px;object-fit:contain;
                                            background:#fff;border-radius:6px;padding:2px;">
                            </td>
                            <td>
                                <span class="fw-semibold"><?= htmlspecialchars($eqp->equipo_nombre ?? '—') ?></span>
                                <?php if (!empty($eqp->torneo_nombre)): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($eqp->torneo_nombre) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($eqp->temporada_nombre ?? '—') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($eqp->tipo_nombre ?? '—') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($eqp->getVersionLabel()) ?></td>
                            <td class="text-end text-muted"><?= htmlspecialchars($eqp->getPrecioFormateado()) ?></td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block mb-0">
                                    <input class="form-check-input toggle-activo"
                                           type="checkbox" role="switch"
                                           id="toggle-<?= $eqp->id ?>"
                                           data-id="<?= $eqp->id ?>"
                                           data-url="<?= APP_URL ?>Equipaciones/toggle"
                                           data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                           <?= $eqp->isActivo() ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="toggle-<?= $eqp->id ?>"></label>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="<?= APP_URL ?>Equipaciones/registry/<?= $eqp->id ?>"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
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
    document.querySelectorAll('input.toggle-activo[type="checkbox"]').forEach(function (toggle) {
        toggle.addEventListener('change', function (e) {
            e.stopPropagation();
            const id = this.dataset.id, url = this.dataset.url, csrf = this.dataset.csrf;
            const activo = this.checked ? 1 : 0, self = this;
            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&activo=${activo}&csrf_token=${csrf}`
            })
            .then(r => r.json())
            .then(data => {
                const Toast = Swal.mixin({ toast: true, position: 'top-end',
                    showConfirmButton: false, timer: 2000, timerProgressBar: true });
                if (data.success) {
                    Toast.fire({ icon: 'success', title: activo ? 'Equipación activada' : 'Equipación desactivada' });
                } else {
                    self.checked = !self.checked;
                    Swal.fire({ icon: 'warning', title: 'No permitido',
                        text: data.message ?? 'No se pudo cambiar el estado.', confirmButtonColor: '#F5A800' });
                }
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });
});
</script>
