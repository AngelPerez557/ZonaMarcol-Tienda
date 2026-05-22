<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $servicio->Found ? 'edit' : 'plus-circle' ?> me-2"
                   style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= $servicio->Found
                    ? 'Modifica los datos del servicio.'
                    : 'Completa el formulario para registrar un nuevo servicio.' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Servicios/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tools me-2"></i>
                    Datos del servicio
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Servicios/save"
                          autocomplete="off">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <?php if ($servicio->Found): ?>
                        <input type="hidden" name="id" value="<?= $servicio->id ?>">
                        <?php endif; ?>

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   maxlength="120"
                                   placeholder="Ej: Limpieza interna de consola, Cambio de pasta térmica..."
                                   value="<?= htmlspecialchars($servicio->nombre ?? '') ?>"
                                   required autofocus>
                        </div>

                        <!-- Categoría -->
                        <div class="mb-3">
                            <label for="categoria" class="form-label fw-semibold">
                                Categoría <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="categoria" name="categoria" required>
                                <?php
                                $cats = [
                                    'limpieza'    => 'Limpieza',
                                    'reparacion'  => 'Reparación',
                                    'diagnostico' => 'Diagnóstico',
                                    'otro'        => 'Otro',
                                ];
                                $catActual = $servicio->categoria ?? 'limpieza';
                                foreach ($cats as $valor => $etiqueta):
                                ?>
                                <option value="<?= $valor ?>" <?= $catActual === $valor ? 'selected' : '' ?>>
                                    <?= $etiqueta ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Precio -->
                        <div class="mb-3">
                            <label for="precio" class="form-label fw-semibold">
                                Precio (L.) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="precio" name="precio"
                                   min="0" step="0.01"
                                   value="<?= number_format((float) ($servicio->precio ?? 0), 2, '.', '') ?>"
                                   required>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-4">
                            <label for="descripcion" class="form-label fw-semibold">
                                Descripción
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <textarea class="form-control" id="descripcion" name="descripcion"
                                      rows="3" maxlength="500"
                                      placeholder="Detalle breve de lo que incluye el servicio..."><?= htmlspecialchars($servicio->descripcion ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $servicio->Found ? 'Guardar cambios' : 'Crear servicio' ?>
                            </button>
                            <a href="<?= APP_URL ?>Servicios/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
