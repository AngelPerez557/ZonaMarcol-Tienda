<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">
            <i class="fas fa-map-marker-alt me-2" style="color:#F5A800;"></i>
            <?= htmlspecialchars($pageTitle) ?>
        </h4>
        <a href="<?= APP_URL ?>Zonas/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-map-marker-alt me-2"></i>Datos de la zona</div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>Zonas/save" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <?php if ($zona): ?>
                        <input type="hidden" name="id" value="<?= $zona['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   maxlength="100" placeholder="Ej: Tegucigalpa Centro, Comayagüela..."
                                   value="<?= htmlspecialchars($zona['nombre'] ?? '') ?>"
                                   required autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="costo" class="form-label fw-semibold">Costo de envío (L.)</label>
                            <div class="input-group">
                                <span class="input-group-text">L.</span>
                                <input type="number" class="form-control" id="costo" name="costo"
                                       step="0.01" min="0" placeholder="0.00"
                                       value="<?= $zona['costo'] ?? 0 ?>">
                            </div>
                            <small class="text-muted">Ingresa 0 para envío gratuito.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $zona ? 'Guardar cambios' : 'Crear zona' ?>
                            </button>
                            <a href="<?= APP_URL ?>Zonas/index" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>