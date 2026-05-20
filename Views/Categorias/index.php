<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-tags me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($categorias) ?> categoría<?= count($categorias) !== 1 ? 's' : '' ?> registrada<?= count($categorias) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <?php if (Auth::can('categorias.crear')): ?>
        <a href="<?= APP_URL ?>Categorias/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Categoría
        </a>
        <?php endif; ?>
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
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categorias)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-tags fa-2x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
                                No hay categorías registradas.
                                <?php if (Auth::can('categorias.crear')): ?>
                                <a href="<?= APP_URL ?>Categorias/registry">Crear la primera</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($categorias as $i => $cat): ?>
                        <tr>
                            <td class="ps-4 text-muted"><?= $i + 1 ?></td>
                            <td>
                                <span class="fw-semibold">
                                    <?= htmlspecialchars($cat->nombre) ?>
                                </span>
                            </td>
                            <td class="text-muted">
                                <?= $cat->descripcion
                                    ? htmlspecialchars($cat->descripcion)
                                    : '<em>Sin descripción</em>' ?>
                            </td>
                            <td class="text-center">
                                <?php if (Auth::can('categorias.editar')): ?>
                                <div class="form-check form-switch d-inline-block mb-0">
                                    <input class="form-check-input toggle-activo"
                                           type="checkbox"
                                           role="switch"
                                           id="toggle-<?= $cat->id ?>"
                                           data-id="<?= $cat->id ?>"
                                           data-url="<?= APP_URL ?>Categorias/toggle"
                                           data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                           <?= $cat->isActivo() ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="toggle-<?= $cat->id ?>"></label>
                                </div>
                                <?php else: ?>
                                <span class="badge <?= $cat->isActivo() ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $cat->isActivo() ? 'Activo' : 'Inactivo' ?>
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <?php if (Auth::can('categorias.editar')): ?>
                                    <a href="<?= APP_URL ?>Categorias/registry/<?= $cat->id ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (Auth::can('categorias.eliminar')): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="<?= $cat->id ?>"
                                            data-nombre="<?= htmlspecialchars($cat->nombre) ?>"
                                            data-url="<?= APP_URL ?>Categorias/delete"
                                            data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
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
     JAVASCRIPT
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
                if (data.success) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                    Toast.fire({
                        icon: 'success',
                        title: activo ? 'Categoría activada' : 'Categoría desactivada'
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
                title: '¿Eliminar categoría?',
                text: `"${nombre}" será desactivada.`,
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