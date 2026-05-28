<?php
/**
 * Views/Solicitudes/Atender.php — Form para convertir una solicitud
 * pendiente en una orden de servicio. Los datos de la solicitud quedan
 * como contexto (read-only) y los campos editables son los que
 * `OrdenServicioModel::insert` necesita.
 */
?>
<div class="container-fluid py-4">

    <a href="<?= APP_URL ?>Solicitudes/index" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-1"></i>Volver a la bandeja
    </a>

    <h4 class="fw-bold mt-2 mb-4">
        <i class="fas fa-clipboard-check me-2" style="color:#F5A800;"></i>
        <?= htmlspecialchars($pageTitle) ?>
    </h4>

    <div class="row g-4">

        <!-- ─── Datos de la solicitud (read-only) ─── -->
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header fw-semibold">
                    <i class="fas fa-inbox me-2" style="color:#F5A800;"></i>
                    Solicitud original
                </div>
                <div class="card-body">
                    <dl class="mb-0 small">
                        <dt>Cliente</dt>
                        <dd><?= htmlspecialchars($solicitud->cliente_nombre ?? '—') ?></dd>

                        <dt>Email</dt>
                        <dd><?= htmlspecialchars($solicitud->cliente_email ?? '—') ?></dd>

                        <dt>Teléfono</dt>
                        <dd><?= htmlspecialchars($solicitud->telefono_contacto ?: '—') ?></dd>

                        <dt class="mt-2">Equipo declarado</dt>
                        <dd><?= htmlspecialchars($solicitud->equipo_descripcion ?? '') ?></dd>

                        <dt>Falla reportada</dt>
                        <dd>
                            <?= $solicitud->falla_reportada
                                ? nl2br(htmlspecialchars($solicitud->falla_reportada))
                                : '<span class="text-muted">No indicada</span>' ?>
                        </dd>

                        <dt>Recibida</dt>
                        <dd><?= htmlspecialchars($solicitud->created_at ?? '') ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- ─── Form de la nueva orden ─── -->
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header fw-semibold">
                    <i class="fas fa-tools me-2" style="color:#F5A800;"></i>
                    Crear orden de servicio
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>Solicitudes/atenderSave" autocomplete="off">
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="solicitud_id" value="<?= (int) $solicitud->id ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Equipo recibido <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="equipo_descripcion" class="form-control"
                                   maxlength="255" required
                                   value="<?= htmlspecialchars($solicitud->equipo_descripcion ?? '') ?>">
                            <small class="text-muted">
                                Confirmá o corregí lo que el cliente declaró cuando entregue el equipo.
                            </small>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Serial / IMEI</label>
                                <input type="text" name="serial" class="form-control" maxlength="100"
                                       placeholder="Opcional">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Técnico asignado</label>
                                <select name="tecnico_id" class="form-select">
                                    <option value="">— Sin asignar —</option>
                                    <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= (int) $u->id ?>">
                                        <?= htmlspecialchars($u->nombre ?? '') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label fw-semibold">Accesorios entregados</label>
                            <input type="text" name="accesorios_entregados" class="form-control"
                                   maxlength="255" placeholder="Ej: cargador, control, cable HDMI...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Diagnóstico inicial</label>
                            <textarea name="diagnostico_inicial" rows="2" class="form-control"
                                      placeholder="Primer juicio del técnico al recibir."><?=
                                htmlspecialchars($solicitud->falla_reportada ?? '') ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <textarea name="observaciones" rows="2" class="form-control"
                                      placeholder="Notas internas que el cliente no verá."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= APP_URL ?>Solicitudes/index" class="btn btn-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-1"></i>Crear orden y atender
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
