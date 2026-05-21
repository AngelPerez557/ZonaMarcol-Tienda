<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-trophy me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($torneos) ?> torneo<?= count($torneos) !== 1 ? 's' : '' ?> registrado<?= count($torneos) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Torneos/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Torneo
        </a>
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
                            <th class="ps-4">#</th>
                            <th>Logo</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>País</th>
                            <th class="text-center">Orden</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($torneos)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-trophy fa-2x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
                                No hay torneos registrados.
                                <a href="<?= APP_URL ?>Torneos/registry">Crear el primero</a>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($torneos as $i => $torneo): ?>
                        <tr>
                            <td class="ps-4 text-muted"><?= $i + 1 ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($torneo->getLogoUrl()) ?>"
                                     alt="<?= htmlspecialchars($torneo->nombre) ?>"
                                     style="width:38px;height:38px;object-fit:contain;
                                            background:#fff;border-radius:6px;padding:2px;">
                            </td>
                            <td>
                                <span class="fw-semibold"><?= htmlspecialchars($torneo->nombre) ?></span>
                            </td>
                            <td class="text-muted"><?= htmlspecialchars($torneo->getTipoLabel()) ?></td>
                            <td class="text-muted">
                                <?= $torneo->pais
                                    ? htmlspecialchars($torneo->pais)
                                    : '<em>—</em>' ?>
                            </td>
                            <td class="text-center text-muted"><?= (int) $torneo->orden ?></td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block mb-0">
                                    <input class="form-check-input toggle-activo"
                                           type="checkbox"
                                           role="switch"
                                           id="toggle-<?= $torneo->id ?>"
                                           data-id="<?= $torneo->id ?>"
                                           data-url="<?= APP_URL ?>Torneos/toggle"
                                           data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                           <?= $torneo->isActivo() ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="toggle-<?= $torneo->id ?>"></label>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="<?= APP_URL ?>Torneos/registry/<?= $torneo->id ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="<?= $torneo->id ?>"
                                            data-nombre="<?= htmlspecialchars($torneo->nombre) ?>"
                                            data-url="<?= APP_URL ?>Torneos/delete"
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

<!-- ─────────────────────────────────────────────
     JAVASCRIPT — toggle activo / eliminar
     ───────────────────────────────────────────── -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Toggle activo ────────────────────────────
    document.querySelectorAll('input.toggle-activo[type="checkbox"]').forEach(function (toggle) {
        toggle.addEventListener('change', function (e) {
            e.stopPropagation();

            const id     = this.dataset.id;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;
            const activo = this.checked ? 1 : 0;
            const self   = this;

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${id}&activo=${activo}&csrf_token=${csrf}`
            })
            .then(r => r.json())
            .then(data => {
                const Toast = Swal.mixin({
                    toast: true, position: 'top-end',
                    showConfirmButton: false, timer: 2000, timerProgressBar: true,
                });
                if (data.success) {
                    Toast.fire({
                        icon: 'success',
                        title: activo ? 'Torneo activado' : 'Torneo desactivado'
                    });
                } else {
                    self.checked = !self.checked;
                    Swal.fire({
                        icon: 'warning',
                        title: 'No permitido',
                        text: data.message ?? 'No se pudo cambiar el estado.',
                        confirmButtonColor: '#F5A800'
                    });
                }
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });

    // ── Eliminar ─────────────────────────────────
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;
            const nombre = this.dataset.nombre;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;

            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar torneo?',
                html: `"<b>${nombre}</b>" y <u>todos sus equipos</u> serán eliminados.`,
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
