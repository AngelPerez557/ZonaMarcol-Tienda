<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-image me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted"><?= count($banners) ?> banner<?= count($banners) !== 1 ? 's' : '' ?></small>
        </div>
        <?php if (Auth::can('banners.gestionar')): ?>
        <a href="<?= APP_URL ?>Banners/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nuevo Banner
        </a>
        <?php endif; ?>
    </div>

    <?php if (empty($banners)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-image fa-3x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
        No hay banners registrados.
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($banners as $b): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 <?= !$b['activo'] ? 'opacity-50' : '' ?>">
                <div style="height:160px; overflow:hidden; border-radius:8px 8px 0 0;">
                    <div style="
                        width:100%; height:100%;
                        background-image: url('<?= APP_URL ?>Content/Demo/img/Banners/<?= htmlspecialchars($b['imagen_url']) ?>');
                        background-size: cover;
                        background-position: center;
                        background-color: #FFFBF2;">
                    </div>
                </div>
                <div class="card-body py-2">
                    <div class="fw-semibold"><?= htmlspecialchars($b['titulo'] ?? 'Sin título') ?></div>
                    <small class="text-muted">Orden: <?= $b['orden'] ?></small>
                    <?php if ($b['enlace']): ?>
                    <br><small class="text-muted"><i class="fas fa-link me-1"></i><?= htmlspecialchars($b['enlace']) ?></small>
                    <?php endif; ?>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <?php if (Auth::can('banners.gestionar')): ?>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input toggle-activo" type="checkbox" role="switch"
                               id="toggle-<?= $b['id'] ?>"
                               data-id="<?= $b['id'] ?>"
                               data-url="<?= APP_URL ?>Banners/toggle"
                               data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                               <?= $b['activo'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="toggle-<?= $b['id'] ?>"></label>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= APP_URL ?>Banners/registry/<?= $b['id'] ?>"
                           class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                data-id="<?= $b['id'] ?>"
                                data-nombre="<?= htmlspecialchars($b['titulo'] ?? 'este banner') ?>"
                                data-url="<?= APP_URL ?>Banners/delete"
                                data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Toggle activo/inactivo ────────────────────
    document.querySelectorAll('input.toggle-activo').forEach(function (toggle) {
        toggle.addEventListener('change', function (e) {
            e.stopPropagation();
            const self   = this;
            const id     = this.dataset.id;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;
            const activo = this.checked ? 1 : 0;

            fetch(url, {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    `id=${id}&activo=${activo}&csrf_token=${csrf}`
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    self.checked = !self.checked;
                } else {
                    Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
                        .fire({ icon: 'success', title: activo ? 'Activado' : 'Desactivado' });
                }
            })
            .catch(() => { self.checked = !self.checked; });
        });
    });

    // ── Eliminar ──────────────────────────────────
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            // Guardar datos ANTES del .then() — this no es confiable dentro del callback
            const id     = this.dataset.id;
            const url    = this.dataset.url;
            const csrf   = this.dataset.csrf;
            const nombre = this.dataset.nombre;

            Swal.fire({
                icon:               'warning',
                title:              '¿Eliminar?',
                text:               `"${nombre}" será eliminado permanentemente.`,
                showCancelButton:   true,
                confirmButtonColor: '#dc3545',
                confirmButtonText:  'Sí, eliminar',
                cancelButtonText:   'Cancelar'
            }).then(r => {
                if (r.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;
                    form.innerHTML = `
                        <input type="hidden" name="id"         value="${id}">
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