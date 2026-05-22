<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $equipacion->Found ? 'edit' : 'plus-circle' ?> me-2"
                   style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= $equipacion->Found
                    ? 'Modifica los datos de la equipación.'
                    : 'Registra una camisa concreta: equipo + temporada + tipo + versión.' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Equipaciones/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tshirt me-2"></i>
                    Datos de la equipación
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Equipaciones/save"
                          enctype="multipart/form-data"
                          autocomplete="off">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <?php if ($equipacion->Found): ?>
                        <input type="hidden" name="id" value="<?= $equipacion->id ?>">
                        <?php endif; ?>

                        <!-- Equipo -->
                        <div class="mb-3">
                            <label for="equipo_id" class="form-label fw-semibold">
                                Equipo <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="equipo_id" name="equipo_id" required>
                                <option value="">— Selecciona un equipo —</option>
                                <?php foreach ($equipos as $e): ?>
                                <option value="<?= $e->id ?>"
                                    <?= (int) $equipacion->equipo_id === (int) $e->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($e->nombre) ?><?= $e->torneo_nombre ? ' — ' . htmlspecialchars($e->torneo_nombre) : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($equipos)): ?>
                            <small class="text-danger">
                                No hay equipos. <a href="<?= APP_URL ?>Equipos/registry">Crea uno primero</a>.
                            </small>
                            <?php endif; ?>
                        </div>

                        <!-- Temporada -->
                        <div class="mb-3">
                            <label for="temporada_id" class="form-label fw-semibold">
                                Temporada <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="temporada_id" name="temporada_id" required>
                                <option value="">— Selecciona una temporada —</option>
                                <?php foreach ($temporadas as $tmp): ?>
                                <option value="<?= $tmp->id ?>"
                                    <?= (int) $equipacion->temporada_id === (int) $tmp->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tmp->nombre) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tipo de equipación -->
                        <div class="mb-3">
                            <label for="tipo_equipacion_id" class="form-label fw-semibold">
                                Tipo <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="tipo_equipacion_id" name="tipo_equipacion_id" required>
                                <option value="">— Local / Visitante / Tercera... —</option>
                                <?php foreach ($tipos as $tp): ?>
                                <option value="<?= (int) $tp['id'] ?>"
                                    <?= (int) $equipacion->tipo_equipacion_id === (int) $tp['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tp['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Versión -->
                        <div class="mb-3">
                            <label for="version" class="form-label fw-semibold">
                                Versión <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="version" name="version" required>
                                <?php
                                $versiones  = ['hombre' => 'Hombre', 'mujer' => 'Mujer', 'infantil' => 'Infantil'];
                                $verActual  = $equipacion->version ?? '';
                                ?>
                                <option value="">— Selecciona —</option>
                                <?php foreach ($versiones as $valor => $etiqueta): ?>
                                <option value="<?= $valor ?>" <?= $verActual === $valor ? 'selected' : '' ?>>
                                    <?= $etiqueta ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Precio base -->
                        <div class="mb-3">
                            <label for="precio_base" class="form-label fw-semibold">
                                Precio base (L.) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="precio_base" name="precio_base"
                                   min="0.01" step="0.01"
                                   value="<?= number_format((float) ($equipacion->precio_base ?? 0), 2, '.', '') ?>"
                                   required>
                        </div>

                        <!-- Imagen -->
                        <div class="mb-4">
                            <label for="imagen" class="form-label fw-semibold">
                                Imagen de la camisa
                                <?php if (!$equipacion->Found): ?>
                                <span class="text-danger">*</span>
                                <?php else: ?>
                                <span class="text-muted fw-normal">(dejar vacío para conservar la actual)</span>
                                <?php endif; ?>
                            </label>

                            <?php if ($equipacion->Found && !empty($equipacion->imagen_path)): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($equipacion->getImagenUrl()) ?>"
                                     alt="Imagen actual"
                                     style="width:80px;height:80px;object-fit:contain;
                                            background:#fff;border-radius:8px;padding:4px;border:1px solid #dee2e6;">
                            </div>
                            <?php endif; ?>

                            <input type="file" class="form-control" id="imagen" name="imagen"
                                   accept="image/jpeg,image/png,image/webp"
                                   <?= $equipacion->Found ? '' : 'required' ?>>
                            <small class="text-muted">JPG, PNG o WEBP. Se optimiza automáticamente a WebP.</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $equipacion->Found ? 'Guardar cambios' : 'Crear equipación' ?>
                            </button>
                            <a href="<?= APP_URL ?>Equipaciones/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
