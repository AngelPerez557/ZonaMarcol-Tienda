<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-sliders-h me-2" style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                Datos fiscales del SAR para la emisión de facturas.
            </small>
        </div>
        <a href="<?= APP_URL ?>Facturas/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <!-- ─────────────────────────────────────────────
         AVISO SI CAI está vencido
         ───────────────────────────────────────────── -->
    <?php if ($config && isset($config['fecha_limite']) && strtotime($config['fecha_limite']) < time()): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
        <i class="fas fa-exclamation-triangle fa-lg"></i>
        <div>
            <strong>¡CAI vencido!</strong> La fecha límite de emisión era
            <?= date('d/m/Y', strtotime($config['fecha_limite'])) ?>.
            Actualiza el CAI con los nuevos datos del SAR.
        </div>
    </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <form method="POST" action="<?= APP_URL ?>Facturas/saveConfig" autocomplete="off">

                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <!-- ── Datos de la empresa ─────────────── -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-building me-2"></i>Datos de la empresa
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="nombre_fiscal" class="form-label fw-semibold">
                                Nombre fiscal
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="nombre_fiscal"
                                   name="nombre_fiscal"
                                   maxlength="150"
                                   placeholder="ZONA MARCOL"
                                   value="<?= htmlspecialchars($config['nombre_fiscal'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="rtn" class="form-label fw-semibold">RTN</label>
                            <input type="text"
                                   class="form-control"
                                   id="rtn"
                                   name="rtn"
                                   maxlength="20"
                                   placeholder="00000000000000"
                                   value="<?= htmlspecialchars($config['rtn'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="direccion_fiscal" class="form-label fw-semibold">
                                Dirección fiscal
                            </label>
                            <textarea class="form-control"
                                      id="direccion_fiscal"
                                      name="direccion_fiscal"
                                      rows="2"
                                      placeholder="Dirección registrada ante el SAR..."><?= htmlspecialchars($config['direccion_fiscal'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- ── Datos del CAI ───────────────────── -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-barcode me-2"></i>Datos del CAI
                        <small class="text-muted ms-2">
                            Código de Autorización de Impresión emitido por el SAR
                        </small>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="cai" class="form-label fw-semibold">CAI</label>
                            <input type="text"
                                   class="form-control font-monospace"
                                   id="cai"
                                   name="cai"
                                   maxlength="50"
                                   placeholder="XXXXXX-XXXXXX-XXXXXX-XXXXXX-XXXXXX-XX"
                                   value="<?= htmlspecialchars($config['cai'] ?? '') ?>">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="rango_desde" class="form-label fw-semibold">
                                    Rango desde
                                </label>
                                <input type="text"
                                       class="form-control font-monospace"
                                       id="rango_desde"
                                       name="rango_desde"
                                       maxlength="20"
                                       placeholder="000-001-01-00000001"
                                       value="<?= htmlspecialchars($config['rango_desde'] ?? '') ?>">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="rango_hasta" class="form-label fw-semibold">
                                    Rango hasta
                                </label>
                                <input type="text"
                                       class="form-control font-monospace"
                                       id="rango_hasta"
                                       name="rango_hasta"
                                       maxlength="20"
                                       placeholder="000-001-01-00009999"
                                       value="<?= htmlspecialchars($config['rango_hasta'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_limite" class="form-label fw-semibold">
                                Fecha límite de emisión
                            </label>
                            <input type="date"
                                   class="form-control"
                                   id="fecha_limite"
                                   name="fecha_limite"
                                   value="<?= htmlspecialchars($config['fecha_limite'] ?? '') ?>">
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label for="establecimiento" class="form-label fw-semibold">
                                    Establecimiento
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="establecimiento"
                                       name="establecimiento"
                                       maxlength="10"
                                       placeholder="000"
                                       value="<?= htmlspecialchars($config['establecimiento'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label for="punto_emision" class="form-label fw-semibold">
                                    Punto de emisión
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="punto_emision"
                                       name="punto_emision"
                                       maxlength="10"
                                       placeholder="001"
                                       value="<?= htmlspecialchars($config['punto_emision'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Correlativo ─────────────────────── -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-hashtag me-2"></i>Correlativo actual
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center g-3">
                            <div class="col-12 col-md-6">
                                <label for="correlativo" class="form-label fw-semibold">
                                    Número de correlativo
                                </label>
                                <input type="number"
                                       class="form-control"
                                       id="correlativo"
                                       name="correlativo"
                                       min="1"
                                       value="<?= htmlspecialchars($config['correlativo'] ?? 1) ?>">
                                <small class="text-muted">
                                    El sistema incrementa este número automáticamente en cada factura.
                                </small>
                            </div>
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block mb-1">Próxima factura:</small>
                                <code class="fs-5" style="color:#F5A800;">
                                    <?= htmlspecialchars(
                                        ($config['establecimiento'] ?? '000') . '-' .
                                        ($config['punto_emision']   ?? '001') . '-01-' .
                                        str_pad($config['correlativo'] ?? 1, 8, '0', STR_PAD_LEFT)
                                    ) ?>
                                </code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Botones ─────────────────────────── -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-save me-2"></i>Guardar configuración
                    </button>
                    <a href="<?= APP_URL ?>Facturas/index" class="btn btn-outline-secondary">
                        Cancelar
                    </a>
                </div>

            </form>
        </div>
    </div>

</div>