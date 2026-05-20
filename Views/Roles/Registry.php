<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $rol->Found ? 'edit' : 'plus-circle' ?> me-2"
                   style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= $rol->Found
                    ? 'Modifica el rol y sus permisos.'
                    : 'Crea un nuevo rol y asigna sus permisos.' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Roles/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <form method="POST" action="<?= APP_URL ?>Roles/save" autocomplete="off">

        <!-- CSRF -->
        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

        <!-- ID oculto en edición -->
        <?php if ($rol->Found): ?>
        <input type="hidden" name="id" value="<?= $rol->id ?>">
        <?php endif; ?>

        <div class="row g-4">

            <!-- ─────────────────────────────────────────────
                 COLUMNA IZQUIERDA — Datos del rol
                 ───────────────────────────────────────────── -->
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-shield-alt me-2"></i>Datos del rol
                    </div>
                    <div class="card-body">

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="nombre"
                                   name="nombre"
                                   maxlength="60"
                                   placeholder="Ej: Vendedor, Cajero..."
                                   value="<?= htmlspecialchars($rol->nombre ?? '') ?>"
                                   required
                                   autofocus>
                        </div>

                        <!-- Slug -->
                        <input type="hidden" id="slug" name="slug" value="<?= htmlspecialchars($rol->slug ?? '') ?>">

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label for="descripcion" class="form-label fw-semibold">
                                Descripción
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <textarea class="form-control"
                                      id="descripcion"
                                      name="descripcion"
                                      rows="3"
                                      placeholder="Describe las responsabilidades de este rol..."><?= htmlspecialchars($rol->descripcion ?? '') ?></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $rol->Found ? 'Guardar cambios' : 'Crear rol' ?>
                            </button>
                            <a href="<?= APP_URL ?>Roles/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ─────────────────────────────────────────────
                 COLUMNA DERECHA — Permisos agrupados por módulo
                 ───────────────────────────────────────────── -->
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-key me-2"></i>Permisos
                        </span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnSeleccionarTodos">
                                <i class="fas fa-check-double me-1"></i>Todos
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeseleccionarTodos">
                                <i class="fas fa-times me-1"></i>Ninguno
                            </button>
                        </div>
                    </div>
                    <div class="card-body">

                        <?php if (empty($permisosAgrupados)): ?>
                        <p class="text-muted text-center py-3 mb-0">
                            No hay permisos registrados en el sistema.
                        </p>
                        <?php else: ?>

                        <div class="row g-3">
                            <?php foreach ($permisosAgrupados as $modulo => $permisos): ?>
                            <div class="col-12 col-md-6">
                                <div class="card h-100" style="border:1px solid rgba(245,168,0,0.2);">
                                    <div class="card-header py-2 d-flex justify-content-between align-items-center"
                                         style="background:rgba(245,168,0,0.06);">
                                        <span class="fw-semibold text-capitalize" style="font-size:0.9rem;">
                                            <i class="fas fa-cube me-2" style="color:#F5A800;"></i>
                                            <?= htmlspecialchars($modulo) ?>
                                        </span>
                                        <!-- Selector de todo el módulo -->
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input toggle-modulo"
                                                   type="checkbox"
                                                   data-modulo="<?= htmlspecialchars($modulo) ?>"
                                                   title="Seleccionar todos los permisos de <?= htmlspecialchars($modulo) ?>">
                                        </div>
                                    </div>
                                    <div class="card-body py-2 px-3">
                                        <?php foreach ($permisos as $permiso): ?>
                                        <div class="form-check mb-1">
                                            <input class="form-check-input permiso-check"
                                                   type="checkbox"
                                                   name="permisos[]"
                                                   value="<?= $permiso->id ?>"
                                                   id="perm-<?= $permiso->id ?>"
                                                   data-modulo="<?= htmlspecialchars($modulo) ?>"
                                                   <?= in_array($permiso->id, $permisosAsignados) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="perm-<?= $permiso->id ?>"
                                                   style="font-size:0.85rem;">
                                                <?= htmlspecialchars($permiso->nombre) ?>
                                                <small class="text-muted d-block">
                                                    <?= htmlspecialchars($permiso->slug) ?>
                                                </small>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Auto-generar slug desde el nombre
    const inputNombre = document.getElementById('nombre');
    const inputSlug   = document.getElementById('slug');

    if (inputNombre && inputSlug && inputSlug.readOnly === false) {
        inputNombre.addEventListener('input', function () {
            inputSlug.value = this.value
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // quitar acentos
                .replace(/[^a-z0-9\s\-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        });
    }

    // Toggle de todos los permisos de un módulo
    document.querySelectorAll('.toggle-modulo').forEach(function (toggle) {
        const modulo = toggle.dataset.modulo;
        const checks = document.querySelectorAll(`.permiso-check[data-modulo="${modulo}"]`);

        // Estado inicial del toggle de módulo
        const todosChecked = Array.from(checks).every(c => c.checked);
        toggle.checked = todosChecked;

        toggle.addEventListener('change', function () {
            checks.forEach(c => c.checked = this.checked);
        });

        // Sincronizar toggle de módulo cuando cambia un permiso individual
        checks.forEach(function (check) {
            check.addEventListener('change', function () {
                const todosCheckedNow = Array.from(checks).every(c => c.checked);
                toggle.checked = todosCheckedNow;
            });
        });
    });

    // Seleccionar todos los permisos
    document.getElementById('btnSeleccionarTodos').addEventListener('click', function () {
        document.querySelectorAll('.permiso-check, .toggle-modulo').forEach(c => c.checked = true);
    });

    // Deseleccionar todos los permisos
    document.getElementById('btnDeseleccionarTodos').addEventListener('click', function () {
        document.querySelectorAll('.permiso-check, .toggle-modulo').forEach(c => c.checked = false);
    });

});
</script>