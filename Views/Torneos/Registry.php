<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $torneo->Found ? 'edit' : 'plus-circle' ?> me-2"
                   style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= $torneo->Found
                    ? 'Modifica los datos del torneo.'
                    : 'Completa el formulario para registrar un nuevo torneo.' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Torneos/index" class="btn btn-outline-secondary">
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
                    <i class="fas fa-trophy me-2"></i>
                    Datos del torneo
                </div>
                <div class="card-body">
                    <!-- enctype multipart — el formulario sube el logo -->
                    <form method="POST"
                          action="<?= APP_URL ?>Torneos/save"
                          enctype="multipart/form-data"
                          autocomplete="off">

                        <!-- CSRF -->
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <!-- ID oculto en edición -->
                        <?php if ($torneo->Found): ?>
                        <input type="hidden" name="id" value="<?= $torneo->id ?>">
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
                                   maxlength="120"
                                   placeholder="Ej: LaLiga, Premier League, Mundial 2026..."
                                   value="<?= htmlspecialchars($torneo->nombre ?? '') ?>"
                                   required
                                   autofocus>
                        </div>

                        <!-- Tipo -->
                        <div class="mb-3">
                            <label for="tipo" class="form-label fw-semibold">
                                Tipo <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <?php
                                $tipos = [
                                    'liga_club'        => 'Liga de clubes',
                                    'seleccion'        => 'Selecciones',
                                    'copa_continental' => 'Copa continental',
                                    'otro'             => 'Otro',
                                ];
                                $tipoActual = $torneo->tipo ?? 'liga_club';
                                foreach ($tipos as $valor => $etiqueta):
                                ?>
                                <option value="<?= $valor ?>" <?= $tipoActual === $valor ? 'selected' : '' ?>>
                                    <?= $etiqueta ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- País -->
                        <div class="mb-3">
                            <label for="pais" class="form-label fw-semibold">
                                País
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="pais"
                                   name="pais"
                                   maxlength="80"
                                   placeholder="Ej: España, Inglaterra..."
                                   value="<?= htmlspecialchars($torneo->pais ?? '') ?>">
                        </div>

                        <!-- Orden -->
                        <div class="mb-3">
                            <label for="orden" class="form-label fw-semibold">
                                Orden de aparición
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="orden"
                                   name="orden"
                                   min="0"
                                   value="<?= (int) ($torneo->orden ?? 0) ?>">
                            <small class="text-muted">Menor número aparece primero en el configurador.</small>
                        </div>

                        <!-- Logo -->
                        <div class="mb-4">
                            <label for="logo" class="form-label fw-semibold">
                                Logo
                                <?php if (!$torneo->Found): ?>
                                <span class="text-danger">*</span>
                                <?php else: ?>
                                <span class="text-muted fw-normal">(dejar vacío para conservar el actual)</span>
                                <?php endif; ?>
                            </label>

                            <?php if ($torneo->Found && !empty($torneo->logo_path)): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($torneo->getLogoUrl()) ?>"
                                     alt="Logo actual"
                                     style="width:64px;height:64px;object-fit:contain;
                                            background:#fff;border-radius:8px;padding:4px;border:1px solid #dee2e6;">
                            </div>
                            <?php endif; ?>

                            <input type="file"
                                   class="form-control"
                                   id="logo"
                                   name="logo"
                                   accept="image/jpeg,image/png,image/webp"
                                   <?= $torneo->Found ? '' : 'required' ?>>
                            <small class="text-muted">JPG, PNG o WEBP. Se optimiza automáticamente a WebP.</small>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $torneo->Found ? 'Guardar cambios' : 'Crear torneo' ?>
                            </button>
                            <a href="<?= APP_URL ?>Torneos/index"
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
