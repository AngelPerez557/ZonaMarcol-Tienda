<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $categoria->Found ? 'edit' : 'plus-circle' ?> me-2"
                   style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= $categoria->Found ? 'Modifica los datos de la categoría.' : 'Completa el formulario para crear una nueva categoría.' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Categorias/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <!-- ─────────────────────────────────────────────
         FORMULARIO
         ───────────────────────────────────────────── -->
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tags me-2"></i>
                    Datos de la categoría
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Categorias/save"
                          autocomplete="off">

                        <!-- CSRF -->
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <!-- ID oculto en edición -->
                        <?php if ($categoria->Found): ?>
                        <input type="hidden" name="id" value="<?= $categoria->id ?>">
                        <?php endif; ?>

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="nombre"
                                   name="nombre"
                                   maxlength="100"
                                   placeholder="Ej: Labiales, Bases, Sombras..."
                                   value="<?= htmlspecialchars($categoria->nombre ?? '') ?>"
                                   required
                                   autofocus>
                        </div>

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
                                      maxlength="255"
                                      placeholder="Descripción breve de la categoría..."><?= htmlspecialchars($categoria->descripcion ?? '') ?></textarea>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $categoria->Found ? 'Guardar cambios' : 'Crear categoría' ?>
                            </button>
                            <a href="<?= APP_URL ?>Categorias/index"
                               class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>