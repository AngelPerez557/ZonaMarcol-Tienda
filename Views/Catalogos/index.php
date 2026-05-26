<?php
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '');

/** Mini-helper para pintar el switch de activo con sus data-attrs. */
function pintarToggle(int $id, int $activo, string $cat, string $subtipo = '', string $csrf = ''): string {
    $extra = $subtipo ? ' data-subtipo="' . htmlspecialchars($subtipo) . '"' : '';
    $checked = $activo ? 'checked' : '';
    return '<div class="form-check form-switch d-inline-block mb-0">'
         . '<input class="form-check-input toggle-cat" type="checkbox" role="switch"'
         . ' data-id="' . $id . '" data-cat="' . htmlspecialchars($cat) . '"' . $extra
         . ' data-csrf="' . htmlspecialchars($csrf) . '" ' . $checked . '></div>';
}
?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-layer-group me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                Temporadas, tipos de equipación y tallas del módulo Camisetas.
            </small>
        </div>
    </div>

    <!-- ─── Tabs principales ─── -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabTemp">Temporadas</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabTipos">Tipos de equipación</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabTallas">Tallas</a></li>
    </ul>

    <div class="tab-content">

        <!-- ─────────── TEMPORADAS ─────────── -->
        <div class="tab-pane fade show active" id="tabTemp">
            <div class="card mb-3">
                <div class="card-body">
                    <!-- Form alta inline -->
                    <form method="POST" action="<?= APP_URL ?>Catalogos/save" class="row g-2 align-items-end">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="cat" value="temporada">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold mb-1">Nombre</label>
                            <input type="text" class="form-control" name="nombre" maxlength="40"
                                   placeholder="Ej: 2024/25, Mundial 2026" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold mb-1">Año inicio</label>
                            <input type="number" class="form-control" name="anio_inicio" min="1900" max="2100" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold mb-1">Año fin</label>
                            <input type="number" class="form-control" name="anio_fin" min="1900" max="2100" required>
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-1"></i>Agregar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr style="background:rgba(245,168,0,0.08);">
                                <th class="ps-4">Nombre</th>
                                <th class="text-center">Años</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Editar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($temporadas)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No hay temporadas registradas.</td></tr>
                            <?php else: foreach ($temporadas as $t): ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?= htmlspecialchars($t->nombre) ?></td>
                                <td class="text-center text-muted"><?= (int) $t->anio_inicio ?> – <?= (int) $t->anio_fin ?></td>
                                <td class="text-center"><?= pintarToggle((int)$t->id, (int)$t->activo, 'temporada', '', $csrf) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary btn-edit-cat"
                                            data-cat="temporada"
                                            data-id="<?= (int)$t->id ?>"
                                            data-nombre="<?= htmlspecialchars($t->nombre) ?>"
                                            data-anio-inicio="<?= (int)$t->anio_inicio ?>"
                                            data-anio-fin="<?= (int)$t->anio_fin ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ─────────── TIPOS DE EQUIPACIÓN ─────────── -->
        <div class="tab-pane fade" id="tabTipos">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>Catalogos/save" class="row g-2 align-items-end">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="cat" value="tipo">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold mb-1">Nombre</label>
                            <input type="text" class="form-control" name="nombre" maxlength="40"
                                   placeholder="Ej: Local, Visitante, Tercera, Portero" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold mb-1">Orden</label>
                            <input type="number" class="form-control" name="orden" min="0" value="0">
                        </div>
                        <div class="col-6 col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-1"></i>Agregar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr style="background:rgba(245,168,0,0.08);">
                                <th class="ps-4">Nombre</th>
                                <th class="text-center">Orden</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Editar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tipos)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No hay tipos registrados.</td></tr>
                            <?php else: foreach ($tipos as $t): ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?= htmlspecialchars($t['nombre']) ?></td>
                                <td class="text-center text-muted"><?= (int) $t['orden'] ?></td>
                                <td class="text-center"><?= pintarToggle((int)$t['id'], (int)$t['activo'], 'tipo', '', $csrf) ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary btn-edit-cat"
                                            data-cat="tipo"
                                            data-id="<?= (int)$t['id'] ?>"
                                            data-nombre="<?= htmlspecialchars($t['nombre']) ?>"
                                            data-orden="<?= (int)$t['orden'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ─────────── TALLAS ─────────── -->
        <div class="tab-pane fade" id="tabTallas">
            <ul class="nav nav-pills mb-3" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill" href="#tallaH">Hombre</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tallaM">Mujer</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#tallaI">Infantil</a></li>
            </ul>
            <div class="tab-content">
                <?php
                // Render reutilizable de cada sub-tab de talla.
                $renderTallas = function (string $subtipo, array $tallas, string $paneId, bool $active) use ($csrf) {
                    $cls = $active ? 'tab-pane fade show active' : 'tab-pane fade';
                    echo '<div class="' . $cls . '" id="' . $paneId . '">';
                    ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="POST" action="<?= APP_URL ?>Catalogos/save" class="row g-2 align-items-end">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="cat" value="talla">
                                <input type="hidden" name="subtipo" value="<?= htmlspecialchars($subtipo) ?>">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold mb-1">Nombre de la talla</label>
                                    <input type="text" class="form-control" name="nombre" maxlength="10"
                                           placeholder="XS, S, M, L, XL..." required>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label fw-semibold mb-1">Orden</label>
                                    <input type="number" class="form-control" name="orden" min="0" value="0">
                                </div>
                                <div class="col-6 col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-1"></i>Agregar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="background:rgba(245,168,0,0.08);">
                                        <th class="ps-4">Talla</th>
                                        <th class="text-center">Orden</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Editar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tallas)): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">Sin tallas registradas.</td></tr>
                                    <?php else: foreach ($tallas as $t): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold"><?= htmlspecialchars($t['nombre']) ?></td>
                                        <td class="text-center text-muted"><?= (int) $t['orden'] ?></td>
                                        <td class="text-center"><?= pintarToggle((int)$t['id'], (int)$t['activo'], 'talla', $subtipo, $csrf) ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary btn-edit-cat"
                                                    data-cat="talla"
                                                    data-subtipo="<?= htmlspecialchars($subtipo) ?>"
                                                    data-id="<?= (int)$t['id'] ?>"
                                                    data-nombre="<?= htmlspecialchars($t['nombre']) ?>"
                                                    data-orden="<?= (int)$t['orden'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php
                    echo '</div>';
                };
                $renderTallas('hombre',   $tallasHombre,   'tallaH', true);
                $renderTallas('mujer',    $tallasMujer,    'tallaM', false);
                $renderTallas('infantil', $tallasInfantil, 'tallaI', false);
                ?>
            </div>
        </div>

    </div>
</div>

<!-- ─── MODAL DE EDICIÓN ÚNICO (se rellena por JS) ─── -->
<div class="modal fade" id="modalEditCat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?= APP_URL ?>Catalogos/save">
            <div class="modal-header">
                <h5 class="modal-title">Editar <span id="modalCatTitulo">registro</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="cat"     id="modalCat">
                <input type="hidden" name="subtipo" id="modalSubtipo">
                <input type="hidden" name="id"      id="modalId">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre</label>
                    <input type="text" class="form-control" name="nombre" id="modalNombre" maxlength="120" required>
                </div>

                <div class="row g-2" id="modalCamposTemp" style="display:none;">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Año inicio</label>
                        <input type="number" class="form-control" name="anio_inicio" id="modalAnioInicio" min="1900" max="2100">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Año fin</label>
                        <input type="number" class="form-control" name="anio_fin" id="modalAnioFin" min="1900" max="2100">
                    </div>
                </div>

                <div class="mb-3" id="modalCamposOrden" style="display:none;">
                    <label class="form-label fw-semibold">Orden</label>
                    <input type="number" class="form-control" name="orden" id="modalOrden" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Edición: abrir modal pre-rellenado ──
    const modalEl = document.getElementById('modalEditCat');
    const modal   = new bootstrap.Modal(modalEl);
    document.querySelectorAll('.btn-edit-cat').forEach(btn => {
        btn.addEventListener('click', function () {
            const cat = this.dataset.cat;
            document.getElementById('modalCat').value     = cat;
            document.getElementById('modalSubtipo').value = this.dataset.subtipo || '';
            document.getElementById('modalId').value      = this.dataset.id;
            document.getElementById('modalNombre').value  = this.dataset.nombre || '';

            // Mostrar campos según el catálogo
            const esTemp = (cat === 'temporada');
            document.getElementById('modalCamposTemp').style.display  = esTemp ? '' : 'none';
            document.getElementById('modalCamposOrden').style.display = esTemp ? 'none' : '';

            if (esTemp) {
                document.getElementById('modalAnioInicio').value = this.dataset.anioInicio || '';
                document.getElementById('modalAnioFin').value    = this.dataset.anioFin || '';
            } else {
                document.getElementById('modalOrden').value = this.dataset.orden || 0;
            }

            const titulos = { temporada: 'temporada', tipo: 'tipo de equipación', talla: 'talla' };
            document.getElementById('modalCatTitulo').textContent = titulos[cat] || 'registro';
            modal.show();
        });
    });

    // ── Toggle activo (AJAX) ──
    document.querySelectorAll('input.toggle-cat').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const fd = new FormData();
            fd.append('id',      this.dataset.id);
            fd.append('cat',     this.dataset.cat);
            fd.append('activo',  this.checked ? 1 : 0);
            fd.append('csrf_token', this.dataset.csrf);
            if (this.dataset.subtipo) fd.append('subtipo', this.dataset.subtipo);

            const self = this;
            fetch('<?= APP_URL ?>Catalogos/toggle', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    const Toast = Swal.mixin({ toast: true, position: 'top-end',
                        showConfirmButton: false, timer: 1800 });
                    if (data.success) {
                        Toast.fire({ icon: 'success', title: self.checked ? 'Activado' : 'Desactivado' });
                    } else {
                        self.checked = !self.checked;
                        Toast.fire({ icon: 'error', title: 'No se pudo cambiar el estado' });
                    }
                })
                .catch(() => { self.checked = !self.checked; });
        });
    });
});
</script>
