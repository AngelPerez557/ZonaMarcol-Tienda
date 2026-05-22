<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $equipo->Found ? 'edit' : 'plus-circle' ?> me-2"
                   style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= $equipo->Found
                    ? 'Modifica los datos del equipo.'
                    : 'Completa el formulario para registrar un nuevo equipo.' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Equipos/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-shield-alt me-2"></i>
                    Datos del equipo
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Equipos/save"
                          enctype="multipart/form-data"
                          autocomplete="off">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <?php if ($equipo->Found): ?>
                        <input type="hidden" name="id" value="<?= $equipo->id ?>">
                        <?php endif; ?>

                        <!-- Torneo -->
                        <div class="mb-3">
                            <label for="torneo_id" class="form-label fw-semibold">
                                Torneo <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="torneo_id" name="torneo_id" required>
                                <option value="">— Selecciona un torneo —</option>
                                <?php foreach ($torneos as $t): ?>
                                <option value="<?= $t->id ?>"
                                    <?= (int) $equipo->torneo_id === (int) $t->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t->nombre) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($torneos)): ?>
                            <small class="text-danger">
                                No hay torneos. <a href="<?= APP_URL ?>Torneos/registry">Crea uno primero</a>.
                            </small>
                            <?php endif; ?>
                        </div>

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                   maxlength="120"
                                   placeholder="Ej: Real Madrid, Honduras..."
                                   value="<?= htmlspecialchars($equipo->nombre ?? '') ?>"
                                   required autofocus>
                        </div>

                        <!-- Orden -->
                        <div class="mb-3">
                            <label for="orden" class="form-label fw-semibold">Orden de aparición</label>
                            <input type="number" class="form-control" id="orden" name="orden"
                                   min="0" value="<?= (int) ($equipo->orden ?? 0) ?>">
                        </div>

                        <!-- Escudo -->
                        <div class="mb-3">
                            <label for="escudo" class="form-label fw-semibold">
                                Escudo
                                <?php if (!$equipo->Found): ?>
                                <span class="text-danger">*</span>
                                <?php else: ?>
                                <span class="text-muted fw-normal">(dejar vacío para conservar el actual)</span>
                                <?php endif; ?>
                            </label>

                            <?php if ($equipo->Found && !empty($equipo->escudo_path)): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($equipo->getEscudoUrl()) ?>"
                                     alt="Escudo actual"
                                     style="width:64px;height:64px;object-fit:contain;
                                            background:#fff;border-radius:8px;padding:4px;border:1px solid #dee2e6;">
                            </div>
                            <?php endif; ?>

                            <input type="file" class="form-control" id="escudo" name="escudo"
                                   accept="image/jpeg,image/png,image/webp"
                                   <?= $equipo->Found ? '' : 'required' ?>>
                            <small class="text-muted">JPG, PNG o WEBP. Se optimiza automáticamente a WebP.</small>
                        </div>

                        <!-- Competiciones (N:M) -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Competiciones que juega
                                <span class="text-muted fw-normal">(parches disponibles para sus camisas)</span>
                            </label>
                            <?php if (empty($competiciones)): ?>
                            <div class="text-muted small">
                                No hay competiciones registradas.
                                <a href="<?= APP_URL ?>Competiciones/registry">Crear una</a>.
                            </div>
                            <?php else: ?>
                            <div class="row g-2">
                                <?php foreach ($competiciones as $c): ?>
                                <div class="col-12 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="competiciones[]"
                                               value="<?= $c->id ?>"
                                               id="comp-<?= $c->id ?>"
                                               <?= in_array((int) $c->id, $competicionesEquipo, true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="comp-<?= $c->id ?>">
                                            <?= htmlspecialchars($c->nombre) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $equipo->Found ? 'Guardar cambios' : 'Crear equipo' ?>
                            </button>
                            <a href="<?= APP_URL ?>Equipos/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
