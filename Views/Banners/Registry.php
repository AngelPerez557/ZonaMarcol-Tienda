<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">
            <i class="fas fa-image me-2" style="color:#F5A800;"></i>
            <?= htmlspecialchars($pageTitle) ?>
        </h4>
        <a href="<?= APP_URL ?>Banners/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="card">
                <div class="card-header"><i class="fas fa-image me-2"></i>Datos del banner</div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>Banners/save" enctype="multipart/form-data" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <?php if ($banner): ?>
                        <input type="hidden" name="id" value="<?= $banner['id'] ?>">
                        <?php endif; ?>

                        <!-- Preview -->
                        <?php if ($banner && $banner['imagen_url']): ?>
                        <div class="mb-3">
                            <div style="height:120px; background-image:url('<?= APP_URL ?>Content/Demo/img/Banners/<?= htmlspecialchars($banner['imagen_url']) ?>');
                                background-size:cover; background-position:center; border-radius:8px;"></div>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="imagen" class="form-label fw-semibold">
                                Imagen <?= !$banner ? '<span class="text-danger">*</span>' : '' ?>
                            </label>
                            <input type="file" class="form-control" id="imagen" name="imagen"
                                   accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">JPG, PNG o WEBP. Máx. 2MB. Recomendado: 1200x400px.</small>
                        </div>

                        <div class="mb-3">
                            <label for="titulo" class="form-label fw-semibold">Título <span class="text-muted fw-normal">(opcional)</span></label>
                            <input type="text" class="form-control" id="titulo" name="titulo"
                                   maxlength="150" placeholder="Título del banner"
                                   value="<?= htmlspecialchars($banner['titulo'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="enlace" class="form-label fw-semibold">Enlace <span class="text-muted fw-normal">(opcional)</span></label>
                            <input type="text" class="form-control" id="enlace" name="enlace"
                                   maxlength="255" placeholder="https://..."
                                   value="<?= htmlspecialchars($banner['enlace'] ?? '') ?>">
                        </div>

                        <div class="mb-4">
                            <label for="orden" class="form-label fw-semibold">Orden</label>
                            <input type="number" class="form-control" id="orden" name="orden"
                                   min="0" value="<?= $banner['orden'] ?? 0 ?>">
                            <small class="text-muted">Los banners se muestran de menor a mayor orden.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $banner ? 'Guardar cambios' : 'Crear banner' ?>
                            </button>
                            <a href="<?= APP_URL ?>Banners/index" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>