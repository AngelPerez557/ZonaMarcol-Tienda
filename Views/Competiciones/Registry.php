<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $competicion->Found ? 'edit' : 'plus-circle' ?> me-2"
                   style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= $competicion->Found
                    ? 'Modifica los datos de la competición.'
                    : 'Completa el formulario para registrar una nueva competición.' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Competiciones/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-medal me-2"></i>
                    Datos de la competición
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Competiciones/save"
                          enctype="multipart/form-data"
                          autocomplete="off">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <?php if ($competicion->Found): ?>
                        <input type="hidden" name="id" value="<?= $competicion->id ?>">
                        <?php endif; ?>

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   maxlength="120"
                                   placeholder="Ej: UEFA Champions League, LaLiga..."
                                   value="<?= htmlspecialchars($competicion->nombre ?? '') ?>"
                                   required autofocus>
                        </div>

                        <!-- Precio extra -->
                        <div class="mb-3">
                            <label for="precio_extra" class="form-label fw-semibold">
                                Precio del parche (L.)
                            </label>
                            <input type="number" class="form-control" id="precio_extra" name="precio_extra"
                                   min="0" step="0.01"
                                   value="<?= number_format((float) ($competicion->precio_extra ?? 0), 2, '.', '') ?>">
                            <small class="text-muted">Se suma al pedido cuando el cliente elige este parche.</small>
                        </div>

                        <!-- Parche -->
                        <div class="mb-4">
                            <label for="parche" class="form-label fw-semibold">
                                Parche
                                <?php if (!$competicion->Found): ?>
                                <span class="text-danger">*</span>
                                <?php else: ?>
                                <span class="text-muted fw-normal">(dejar vacío para conservar el actual)</span>
                                <?php endif; ?>
                            </label>

                            <?php if ($competicion->Found && !empty($competicion->parche_path)): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($competicion->getParcheUrl()) ?>"
                                     alt="Parche actual"
                                     style="width:64px;height:64px;object-fit:contain;
                                            background:#fff;border-radius:8px;padding:4px;border:1px solid #dee2e6;">
                            </div>
                            <?php endif; ?>

                            <input type="file" class="form-control" id="parche" name="parche"
                                   accept="image/jpeg,image/png,image/webp"
                                   <?= $competicion->Found ? '' : 'required' ?>>
                            <small class="text-muted">JPG, PNG o WEBP. Se optimiza automáticamente a WebP.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $competicion->Found ? 'Guardar cambios' : 'Crear competición' ?>
                            </button>
                            <a href="<?= APP_URL ?>Competiciones/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
