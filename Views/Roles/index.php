<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-user-shield me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= count($roles) ?> rol<?= count($roles) !== 1 ? 'es' : '' ?> registrado<?= count($roles) !== 1 ? 's' : '' ?>
            </small>
        </div>
        <?php if (Auth::can('roles.crear')): ?>
        <a href="<?= APP_URL ?>Roles/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Rol
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
                            <th class="ps-4">Rol</th>
                            <th>Slug</th>
                            <th>Descripción</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($roles)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                No hay roles registrados.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($roles as $rol): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                         style="width:36px; height:36px;
                                                background:rgba(245,168,0,0.12); flex-shrink:0;">
                                        <i class="fas fa-shield-alt" style="color:#F5A800; font-size:0.85rem;"></i>
                                    </div>
                                    <span class="fw-semibold"><?= htmlspecialchars($rol->nombre) ?></span>
                                </div>
                            </td>
                            <td>
                                <code class="px-2 py-1 rounded"
                                      style="background:rgba(245,168,0,0.08); color:#8C6300;">
                                    <?= htmlspecialchars($rol->slug) ?>
                                </code>
                            </td>
                            <td class="text-muted">
                                <?= $rol->descripcion
                                    ? htmlspecialchars($rol->descripcion)
                                    : '<em>Sin descripción</em>' ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <?php if (Auth::can('roles.editar')): ?>
                                    <a href="<?= APP_URL ?>Roles/registry/<?= $rol->id ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Editar y asignar permisos">
                                        <i class="fas fa-edit me-1"></i>Editar
                                    </a>
                                    <?php endif; ?>

                                    <?php if (Auth::can('roles.eliminar') && $rol->slug !== 'admin'): ?>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-delete"
                                            data-id="<?= $rol->id ?>"
                                            data-nombre="<?= htmlspecialchars($rol->nombre) ?>"
                                            data-url="<?= APP_URL ?>Roles/delete"
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;
            const nombre = this.dataset.nombre;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;

            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar rol?',
                text: `"${nombre}" será eliminado. Los permisos asignados se revocarán.`,
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