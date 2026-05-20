<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-map-marker-alt me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted"><?= count($zonas) ?> zona<?= count($zonas) !== 1 ? 's' : '' ?></small>
        </div>
        <?php if (Auth::can('zonas.gestionar')): ?>
        <a href="<?= APP_URL ?>Zonas/registry" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Zona
        </a>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr style="background:rgba(245,168,0,0.08);">
                        <th class="ps-4">Zona</th>
                        <th class="text-end">Costo de envío</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($zonas)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fas fa-map-marker-alt fa-2x mb-3 d-block" style="opacity:0.3;"></i>
                            No hay zonas de envío registradas.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($zonas as $zona): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= htmlspecialchars($zona['nombre']) ?></td>
                        <td class="text-end fw-bold" style="color:#F5A800;">
                            <?= (float)$zona['costo'] === 0.0
                                ? '<span class="badge bg-success">Gratis</span>'
                                : 'L. ' . number_format((float)$zona['costo'], 2) ?>
                        </td>
                        <td class="text-center">
                            <?php if (Auth::can('zonas.gestionar')): ?>
                            <div class="form-check form-switch d-inline-block mb-0">
                                <input class="form-check-input toggle-activo" type="checkbox" role="switch"
                                       id="toggle-<?= $zona['id'] ?>"
                                       data-id="<?= $zona['id'] ?>"
                                       data-url="<?= APP_URL ?>Zonas/toggle"
                                       data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                       <?= $zona['activo'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="toggle-<?= $zona['id'] ?>"></label>
                            </div>
                            <?php else: ?>
                            <span class="badge <?= $zona['activo'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $zona['activo'] ? 'Activa' : 'Inactiva' ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <?php if (Auth::can('zonas.gestionar')): ?>
                                <a href="<?= APP_URL ?>Zonas/registry/<?= $zona['id'] ?>"
                                   class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                        data-id="<?= $zona['id'] ?>"
                                        data-nombre="<?= htmlspecialchars($zona['nombre']) ?>"
                                        data-url="<?= APP_URL ?>Zonas/delete"
                                        data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input.toggle-activo').forEach(function (toggle) {
        toggle.addEventListener('change', function (e) {
            e.stopPropagation();
            const self = this;
            fetch(this.dataset.url, {
                method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:`id=${this.dataset.id}&activo=${this.checked?1:0}&csrf_token=${this.dataset.csrf}`
            }).then(r=>r.json()).then(data=>{
                if(!data.success) self.checked=!self.checked;
                else Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:2000})
                    .fire({icon:'success',title:self.checked?'Zona activada':'Zona desactivada'});
            }).catch(()=>{self.checked=!self.checked;});
        });
    });

    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            Swal.fire({icon:'warning',title:'¿Eliminar zona?',
                text:`"${this.dataset.nombre}" será eliminada.`,
                showCancelButton:true,confirmButtonColor:'#dc3545',
                confirmButtonText:'Sí',cancelButtonText:'Cancelar'})
            .then(r=>{
                if(r.isConfirmed){
                    const form=document.createElement('form');
                    form.method='POST'; form.action=this.dataset.url;
                    form.innerHTML=`<input type="hidden" name="id" value="${this.dataset.id}">
                                    <input type="hidden" name="csrf_token" value="${this.dataset.csrf}">`;
                    document.body.appendChild(form); form.submit();
                }
            },this.bind(this));
        });
    });
});
</script>