<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-shield-alt me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($equipos) ?> equipo<?= count($equipos) !== 1 ? 's' : '' ?> registrado<?= count($equipos) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Equipos/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Equipo
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:rgba(245,168,0,0.08);">
                            <th class="ps-4">#</th>
                            <th>Escudo</th>
                            <th>Nombre</th>
                            <th>Torneo</th>
                            <th class="text-center">Orden</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($equipos)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-shield-alt fa-2x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
                                No hay equipos registrados.
                                <a href="<?= APP_URL ?>Equipos/registry">Crear el primero</a>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($equipos as $i => $equipo): ?>
                        <tr>
                            <td class="ps-4 text-muted"><?= $i + 1 ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($equipo->getEscudoUrl()) ?>"
                                     alt="<?= htmlspecialchars($equipo->nombre) ?>"
                                     style="width:38px;height:38px;object-fit:contain;
                                            background:#fff;border-radius:6px;padding:2px;">
                            </td>
                            <td><span class="fw-semibold"><?= htmlspecialchars($equipo->nombre) ?></span></td>
                            <td class="text-muted">
                                <?= $equipo->torneo_nombre
                                    ? htmlspecialchars($equipo->torneo_nombre)
                                    : '<em>—</em>' ?>
                            </td>
                            <td class="text-center text-muted"><?= (int) $equipo->orden ?></td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block mb-0">
                                    <input class="form-check-input toggle-activo"
                                           type="checkbox" role="switch"
                                           id="toggle-<?= $equipo->id ?>"
                                           data-id="<?= $equipo->id ?>"
                                           data-url="<?= APP_URL ?>Equipos/toggle"
                                           data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                           <?= $equipo->isActivo() ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="toggle-<?= $equipo->id ?>"></label>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="<?= APP_URL ?>Equipos/registry/<?= $equipo->id ?>"
                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="<?= $equipo->id ?>"
                                            data-nombre="<?= htmlspecialchars($equipo->nombre) ?>"
                                            data-url="<?= APP_URL ?>Equipos/delete"
                                            data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
                    Toast.fire({ icon: 'success', title: activo ? 'Equipo activado' : 'Equipo desactivado' });
                } else {
                    self.checked = !self.checked;
                    Swal.fire({ icon: 'warning', title: 'No permitido',
                        text: data.message ?? 'No se pudo cambiar el estado.', confirmButtonColor: '#F5A800' });
                }
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });

    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id, nombre = this.dataset.nombre;
            const url = this.dataset.url, csrf = this.dataset.csrf;
            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar equipo?',
                html: `"<b>${nombre}</b>" y <u>todas sus equipaciones</u> serán eliminadas.`,
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = `
                        <input type="hidden" name="id" value="${id}">
                        <input type="hidden" name="csrf_token" value="${csrf}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

});
</script>
