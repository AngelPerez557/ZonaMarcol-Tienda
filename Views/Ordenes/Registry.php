<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $orden->Found ? 'edit' : 'plus-circle' ?> me-2"
                   style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= $orden->Found
                    ? 'Modifica los datos de recepción de la orden ' . htmlspecialchars($orden->codigo) . '.'
                    : 'Registra el equipo que el cliente deja en el taller.' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Ordenes/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Datos de la orden
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>Ordenes/save" autocomplete="off">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <?php if ($orden->Found): ?>
                        <input type="hidden" name="id" value="<?= $orden->id ?>">
                        <?php endif; ?>

                        <div class="row g-3">
                            <!-- Cliente -->
                            <div class="col-12 col-md-6">
                                <label for="cliente_id" class="form-label fw-semibold">
                                    Cliente <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="cliente_id" name="cliente_id" required>
                                    <option value="">— Selecciona un cliente —</option>
                                    <?php foreach ($clientes as $c): ?>
                                    <option value="<?= $c->id ?>"
                                        <?= (int) $orden->cliente_id === (int) $c->id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c->nombre) ?><?= $c->telefono ? ' — ' . htmlspecialchars($c->telefono) : '' ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (empty($clientes)): ?>
                                <small class="text-danger">
                                    No hay clientes. <a href="<?= APP_URL ?>Clientes/index">Registrá uno primero</a>.
                                </small>
                                <?php endif; ?>
                            </div>

                            <!-- Técnico -->
                            <div class="col-12 col-md-6">
                                <label for="tecnico_id" class="form-label fw-semibold">
                                    Técnico asignado
                                    <span class="text-muted fw-normal">(opcional)</span>
                                </label>
                                <select class="form-select" id="tecnico_id" name="tecnico_id">
                                    <option value="">— Sin asignar —</option>
                                    <?php foreach ($usuarios as $u): ?>
                                        <?php if (!$u->activo) continue; ?>
                                    <option value="<?= $u->id ?>"
                                        <?= (int) $orden->tecnico_id === (int) $u->id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u->nombre) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Equipo -->
                            <div class="col-12">
                                <label for="equipo_descripcion" class="form-label fw-semibold">
                                    Descripción del equipo <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="equipo_descripcion"
                                       name="equipo_descripcion" maxlength="255"
                                       placeholder="Ej: PlayStation 5 color blanco, PC de escritorio gamer..."
                                       value="<?= htmlspecialchars($orden->equipo_descripcion ?? '') ?>"
                                       required>
                            </div>

                            <!-- Serial -->
                            <div class="col-12 col-md-6">
                                <label for="serial" class="form-label fw-semibold">
                                    Serial / IMEI
                                    <span class="text-muted fw-normal">(opcional)</span>
                                </label>
                                <input type="text" class="form-control" id="serial" name="serial"
                                       maxlength="120"
                                       value="<?= htmlspecialchars($orden->serial ?? '') ?>">
                            </div>

                            <!-- Accesorios -->
                            <div class="col-12 col-md-6">
                                <label for="accesorios_entregados" class="form-label fw-semibold">
                                    Accesorios entregados
                                    <span class="text-muted fw-normal">(opcional)</span>
                                </label>
                                <input type="text" class="form-control" id="accesorios_entregados"
                                       name="accesorios_entregados" maxlength="255"
                                       placeholder="Ej: cable de poder, control, caja..."
                                       value="<?= htmlspecialchars($orden->accesorios_entregados ?? '') ?>">
                            </div>

                            <!-- Diagnóstico inicial -->
                            <div class="col-12">
                                <label for="diagnostico_inicial" class="form-label fw-semibold">
                                    Falla reportada por el cliente
                                    <span class="text-muted fw-normal">(opcional)</span>
                                </label>
                                <textarea class="form-control" id="diagnostico_inicial"
                                          name="diagnostico_inicial" rows="2"
                                          placeholder="Qué problema reporta el cliente..."><?= htmlspecialchars($orden->diagnostico_inicial ?? '') ?></textarea>
                            </div>

                            <!-- Observaciones -->
                            <div class="col-12">
                                <label for="observaciones" class="form-label fw-semibold">
                                    Observaciones internas
                                    <span class="text-muted fw-normal">(opcional)</span>
                                </label>
                                <textarea class="form-control" id="observaciones"
                                          name="observaciones" rows="2"
                                          placeholder="Estado físico del equipo, rayones, notas internas..."><?= htmlspecialchars($orden->observaciones ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $orden->Found ? 'Guardar cambios' : 'Registrar orden' ?>
                            </button>
                            <a href="<?= APP_URL ?>Ordenes/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
