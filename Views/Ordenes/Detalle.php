<?php
/**
 * Views/Ordenes/Detalle.php — Ficha completa de una orden de servicio (Etapa 3).
 *
 * Layout: tabs Bootstrap
 *   1. Info general (cliente + equipo + gestión + totales)
 *   2. Diagnóstico técnico (textarea editable)
 *   3. Presupuesto (items + alta + aprobación + eliminar)
 *   4. Historial (servicio_historial DESC)
 *
 * Workflow: card lateral con botones por transición legal + modal cancelar
 *           (motivo obligatorio).
 */
$cerrada = $orden->estaCerrada();
$totalNoAprobados = (float) $orden->total_actual - (float) $totalAprob;
$csrfTok = htmlspecialchars($_SESSION['csrf_token'] ?? '');
?>
<div class="container-fluid py-4">

    <!-- ─── Encabezado ─── -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-clipboard-check me-2" style="color:#F5A800;"></i>
                Orden <?= htmlspecialchars($orden->codigo) ?>
                <span class="badge <?= $orden->getEstadoBadge() ?> ms-2"
                      style="font-size:0.75rem;vertical-align:middle;">
                    <?= htmlspecialchars($orden->getEstadoLabel()) ?>
                </span>
            </h4>
            <small class="text-muted">
                Recepción:
                <?= $orden->fecha_recepcion ? date('d/m/Y H:i', strtotime($orden->fecha_recepcion)) : '—' ?>
                <?php if ($orden->fecha_entrega): ?>
                · Entrega: <?= date('d/m/Y H:i', strtotime($orden->fecha_entrega)) ?>
                <?php endif; ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <?php if (Auth::can('servicio.recibir') && !$cerrada): ?>
            <a href="<?= APP_URL ?>Ordenes/registry/<?= $orden->id ?>" class="btn btn-outline-primary">
                <i class="fas fa-edit me-2"></i>Editar datos
            </a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>Ordenes/index" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <div class="row g-4">

        <!-- ─── Columna principal — tabs ─── -->
        <div class="col-12 col-xl-8">
            <div class="card">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs card-header-tabs px-2 pt-2" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabInfo">
                            <i class="fas fa-info-circle me-1"></i>Información
                        </a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabDiag">
                            <i class="fas fa-stethoscope me-1"></i>Diagnóstico
                        </a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabItems">
                            <i class="fas fa-list-check me-1"></i>Presupuesto
                            <span class="badge bg-secondary ms-1"><?= count($items) ?></span>
                        </a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPagos">
                            <i class="fas fa-dollar-sign me-1"></i>Pagos
                            <span class="badge bg-secondary ms-1"><?= count($pagos) ?></span>
                        </a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabHist">
                            <i class="fas fa-clock-rotate-left me-1"></i>Historial
                            <span class="badge bg-secondary ms-1"><?= count($historial) ?></span>
                        </a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">

                        <!-- ─── Tab 1: Info ─── -->
                        <div class="tab-pane fade show active" id="tabInfo">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted text-uppercase small">
                                        <i class="fas fa-user me-1"></i>Cliente
                                    </h6>
                                    <p class="mb-1"><strong><?= htmlspecialchars($orden->cliente_nombre ?? '—') ?></strong></p>
                                    <p class="mb-0 text-muted small">
                                        <i class="fas fa-phone me-1"></i>
                                        <?= htmlspecialchars($orden->cliente_telefono ?: 'Sin teléfono') ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted text-uppercase small">
                                        <i class="fas fa-cogs me-1"></i>Gestión
                                    </h6>
                                    <p class="mb-1 small">
                                        <strong>Recibido por:</strong>
                                        <?= htmlspecialchars($orden->recepcion_nombre ?? '—') ?>
                                    </p>
                                    <p class="mb-0 small">
                                        <strong>Técnico:</strong>
                                        <?= htmlspecialchars($orden->tecnico_nombre ?: 'Sin asignar') ?>
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <h6 class="text-muted text-uppercase small">
                                <i class="fas fa-laptop me-1"></i>Equipo recibido
                            </h6>
                            <p class="mb-1"><strong>Descripción:</strong> <?= htmlspecialchars($orden->equipo_descripcion) ?></p>
                            <p class="mb-1"><strong>Serial / IMEI:</strong> <?= htmlspecialchars($orden->serial ?: '—') ?></p>
                            <p class="mb-1"><strong>Accesorios:</strong> <?= htmlspecialchars($orden->accesorios_entregados ?: '—') ?></p>
                            <?php if (!empty($orden->observaciones)): ?>
                            <hr>
                            <p class="mb-0 small">
                                <strong>Observaciones internas:</strong><br>
                                <span class="text-muted"><?= nl2br(htmlspecialchars($orden->observaciones)) ?></span>
                            </p>
                            <?php endif; ?>
                            <?php if ($orden->estado === 'Cancelado' && !empty($orden->motivo_cancelacion)): ?>
                            <hr>
                            <p class="mb-0 text-danger">
                                <strong>Motivo de cancelación:</strong>
                                <?= htmlspecialchars($orden->motivo_cancelacion) ?>
                            </p>
                            <?php endif; ?>
                        </div>

                        <!-- ─── Tab 2: Diagnóstico ─── -->
                        <div class="tab-pane fade" id="tabDiag">
                            <?php if (Auth::can('servicio.diagnosticar') && !$cerrada): ?>
                            <form method="POST" action="<?= APP_URL ?>Ordenes/guardarDiagnostico">
                                <input type="hidden" name="csrf_token" value="<?= $csrfTok ?>">
                                <input type="hidden" name="orden_id"   value="<?= (int) $orden->id ?>">
                                <label class="form-label fw-semibold">
                                    Diagnóstico técnico
                                </label>
                                <textarea name="diagnostico" rows="6" class="form-control mb-3"
                                          placeholder="Falla observada, componentes afectados, prueba realizada..."><?=
                                    htmlspecialchars($orden->diagnostico_inicial ?? '') ?></textarea>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Guardar diagnóstico
                                </button>
                            </form>
                            <?php else: ?>
                            <h6 class="text-muted text-uppercase small">Diagnóstico técnico</h6>
                            <p class="mb-0">
                                <?= $orden->diagnostico_inicial
                                    ? nl2br(htmlspecialchars($orden->diagnostico_inicial))
                                    : '<span class="text-muted">Sin diagnóstico cargado.</span>' ?>
                            </p>
                            <?php endif; ?>
                        </div>

                        <!-- ─── Tab 3: Presupuesto (items) ─── -->
                        <div class="tab-pane fade" id="tabItems">
                            <!-- Tabla de items -->
                            <form method="POST" action="<?= APP_URL ?>Ordenes/aprobarItems">
                                <input type="hidden" name="csrf_token" value="<?= $csrfTok ?>">
                                <input type="hidden" name="orden_id"   value="<?= (int) $orden->id ?>">

                                <div class="table-responsive mb-3">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr style="background:rgba(245,168,0,0.08);">
                                                <th style="width:30px;"></th>
                                                <th>Descripción</th>
                                                <th>Tipo</th>
                                                <th class="text-center">Cant.</th>
                                                <th class="text-end">P. unit.</th>
                                                <th class="text-end">Subtotal</th>
                                                <th class="text-center">Gar.</th>
                                                <th class="text-center">Aprob.</th>
                                                <th class="text-center"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($items)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-3">
                                                    Sin ítems en el presupuesto.
                                                </td>
                                            </tr>
                                            <?php else: foreach ($items as $it): ?>
                                            <tr>
                                                <td>
                                                    <?php if (Auth::can('servicio.aprobar') && !$it->isAprobado() && !$cerrada): ?>
                                                    <input type="checkbox" name="item_ids[]"
                                                           value="<?= (int) $it->id ?>" class="form-check-input">
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold"><?= htmlspecialchars($it->descripcion) ?></div>
                                                    <?php if ($it->catalogo_nombre): ?>
                                                    <small class="text-muted">Catálogo: <?= htmlspecialchars($it->catalogo_nombre) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $it->getTipoBadge() ?>">
                                                        <?= htmlspecialchars($it->getTipoLabel()) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center"><?= (int) $it->cantidad ?></td>
                                                <td class="text-end">L. <?= number_format((float) $it->precio_unitario, 2) ?></td>
                                                <td class="text-end fw-semibold"><?= htmlspecialchars($it->getSubtotalFormateado()) ?></td>
                                                <td class="text-center"><small><?= (int) $it->dias_garantia ?>d</small></td>
                                                <td class="text-center">
                                                    <?php if ($it->isAprobado()): ?>
                                                        <i class="fas fa-check-circle text-success" title="Aprobado por cliente"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-clock text-warning" title="Pendiente de aprobación"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (Auth::can('servicio.diagnosticar') && !$it->isAprobado() && !$cerrada): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-item"
                                                            data-id="<?= (int) $it->id ?>"
                                                            data-desc="<?= htmlspecialchars($it->descripcion) ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; endif; ?>
                                        </tbody>
                                        <?php if (!empty($items)): ?>
                                        <tfoot>
                                            <tr>
                                                <td colspan="5" class="text-end fw-bold">Total presupuesto:</td>
                                                <td class="text-end fw-bold" style="color:#F5A800;">
                                                    L. <?= number_format((float) $orden->total_actual, 2) ?>
                                                </td>
                                                <td colspan="3"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="text-end text-muted small">
                                                    Aprobado por cliente:
                                                </td>
                                                <td class="text-end text-success small">
                                                    L. <?= number_format((float) $totalAprob, 2) ?>
                                                </td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                        <?php endif; ?>
                                    </table>
                                </div>

                                <?php if (Auth::can('servicio.aprobar') && !$cerrada && !empty($items)): ?>
                                <button type="submit" class="btn btn-sm btn-success mb-3">
                                    <i class="fas fa-check me-1"></i>Aprobar seleccionados
                                </button>
                                <?php endif; ?>
                            </form>

                            <!-- Form alta de item -->
                            <?php if (Auth::can('servicio.diagnosticar') && !$cerrada): ?>
                            <hr>
                            <h6 class="text-muted text-uppercase small mb-3">
                                <i class="fas fa-plus-circle me-1"></i>Agregar ítem al presupuesto
                            </h6>
                            <form method="POST" action="<?= APP_URL ?>Ordenes/agregarItem" id="formItem">
                                <input type="hidden" name="csrf_token" value="<?= $csrfTok ?>">
                                <input type="hidden" name="orden_id"   value="<?= (int) $orden->id ?>">

                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label class="form-label small">Tipo</label>
                                        <select name="tipo" id="itemTipo" class="form-select form-select-sm" required>
                                            <option value="servicio_catalogo">Servicio de catálogo</option>
                                            <option value="repuesto_libre">Repuesto</option>
                                            <option value="mano_obra_adicional">Mano de obra</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5" id="filaCatalogo">
                                        <label class="form-label small">Servicio del catálogo</label>
                                        <select name="servicio_catalogo_id" id="itemCatalogo" class="form-select form-select-sm">
                                            <option value="">— Elegir —</option>
                                            <?php foreach ($catalogo as $sc): ?>
                                            <option value="<?= (int) $sc->id ?>"
                                                    data-precio="<?= (float) $sc->precio_base ?>">
                                                <?= htmlspecialchars($sc->nombre) ?>
                                                — L. <?= number_format((float) $sc->precio_base, 2) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-5 d-none" id="filaDesc">
                                        <label class="form-label small">Descripción</label>
                                        <input type="text" name="descripcion" class="form-control form-control-sm"
                                               maxlength="255" placeholder="Ej: Memoria RAM 8GB DDR4">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Cantidad</label>
                                        <input type="number" name="cantidad" min="1" value="1"
                                               class="form-control form-control-sm" required>
                                    </div>
                                </div>

                                <div class="row g-2 mt-2">
                                    <div class="col-md-3" id="filaPrecio">
                                        <label class="form-label small">Precio unitario (L.)</label>
                                        <input type="number" name="precio_unitario" step="0.01" min="0"
                                               id="itemPrecio" class="form-control form-control-sm" value="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Garantía (días)</label>
                                        <input type="number" name="dias_garantia" min="0" value="30"
                                               class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-plus me-1"></i>Agregar ítem
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>

                        <!-- ─── Tab Pagos ─── -->
                        <div class="tab-pane fade" id="tabPagos">
                            <?php if (empty($pagos)): ?>
                            <p class="text-muted">Sin pagos registrados.</p>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr style="background:rgba(245,168,0,0.08);">
                                            <th>Recibo</th>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Método</th>
                                            <th>Cobrado por</th>
                                            <th class="text-end">Monto</th>
                                            <?php if (Auth::can('servicio.aprobar') && !$orden->estaCerrada()): ?>
                                            <th></th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pagos as $pg): ?>
                                        <tr>
                                            <td><code><?= htmlspecialchars($pg->recibo_numero ?: '—') ?></code></td>
                                            <td><small><?= htmlspecialchars($pg->fecha) ?></small></td>
                                            <td>
                                                <span class="badge <?= $pg->getTipoBadge() ?>">
                                                    <?= htmlspecialchars($pg->getTipoLabel()) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas <?= $pg->getMetodoIcon() ?> me-1"></i>
                                                <?= htmlspecialchars($pg->metodo) ?>
                                            </td>
                                            <td><small><?= htmlspecialchars($pg->user_nombre ?? '—') ?></small></td>
                                            <td class="text-end fw-semibold">
                                                <?= htmlspecialchars($pg->getMontoFormateado()) ?>
                                            </td>
                                            <?php if (Auth::can('servicio.aprobar') && !$orden->estaCerrada()): ?>
                                            <td class="text-end">
                                                <form method="POST" action="<?= APP_URL ?>Ordenes/anularPago"
                                                      onsubmit="return confirm('¿Anular este pago?');" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrfTok ?>">
                                                    <input type="hidden" name="pago_id" value="<?= (int) $pg->id ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            title="Anular pago">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold">Total pagado:</td>
                                            <td class="text-end fw-bold text-success">
                                                L. <?= number_format((float) $orden->total_pagado, 2) ?>
                                            </td>
                                            <?php if (Auth::can('servicio.aprobar') && !$orden->estaCerrada()): ?>
                                            <td></td>
                                            <?php endif; ?>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <?php endif; ?>

                            <?php if (Auth::can('servicio.entregar') && !$orden->estaCerrada() && (float) $orden->saldo > 0.009): ?>
                            <button type="button" class="btn btn-primary btn-sm mt-3"
                                    data-bs-toggle="modal" data-bs-target="#modalPago">
                                <i class="fas fa-plus me-1"></i>Registrar pago
                            </button>
                            <?php elseif ((float) $orden->saldo <= 0.009 && !empty($pagos)): ?>
                            <div class="alert alert-success mt-3 mb-0 py-2">
                                <i class="fas fa-check-circle me-1"></i>
                                Orden saldada — sin saldo pendiente.
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- ─── Tab Historial ─── -->
                        <div class="tab-pane fade" id="tabHist">
                            <?php if (empty($historial)): ?>
                            <p class="text-muted">Sin movimientos registrados.</p>
                            <?php else: ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($historial as $h): ?>
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>
                                                <?= htmlspecialchars($h['estado_anterior'] ?: '(inicial)') ?>
                                                <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                                <?= htmlspecialchars($h['estado_nuevo']) ?>
                                            </strong>
                                            <?php if (!empty($h['motivo'])): ?>
                                            <div class="small text-muted">
                                                <?= htmlspecialchars($h['motivo']) ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end small text-muted">
                                            <?= htmlspecialchars($h['user_nombre'] ?? '—') ?><br>
                                            <?= date('d/m/Y H:i', strtotime($h['fecha'])) ?>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Columna lateral — workflow y totales ─── -->
        <div class="col-12 col-xl-4">

            <!-- Totales -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-dollar-sign me-2"></i>Totales</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span class="text-muted">Total presupuesto</span>
                        <span class="fw-semibold"><?= htmlspecialchars($orden->getTotalFormateado()) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span class="text-muted">Aprobado por cliente</span>
                        <span class="text-success">L. <?= number_format((float) $totalAprob, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span class="text-muted">Sin aprobar</span>
                        <span class="text-warning">L. <?= number_format((float) $totalNoAprobados, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span class="text-muted">Pagado</span>
                        <span>L. <?= number_format((float) $orden->total_pagado, 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Saldo</span>
                        <span class="fw-bold text-danger"><?= htmlspecialchars($orden->getSaldoFormateado()) ?></span>
                    </div>
                </div>
            </div>

            <!-- Asignar técnico -->
            <?php if (Auth::can('servicio.recibir') && !$cerrada): ?>
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-user-cog me-2"></i>Técnico asignado</div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>Ordenes/asignarTecnico" class="d-flex gap-2">
                        <input type="hidden" name="csrf_token" value="<?= $csrfTok ?>">
                        <input type="hidden" name="orden_id"   value="<?= (int) $orden->id ?>">
                        <select name="tecnico_id" class="form-select form-select-sm">
                            <option value="">— Sin asignar —</option>
                            <?php foreach ($tecnicos as $u): ?>
                            <option value="<?= (int) $u->id ?>"
                                    <?= (int) $u->id === (int) $orden->tecnico_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u->nombre ?? '') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-save"></i>
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Workflow -->
            <?php if (!empty($transiciones)): ?>
            <div class="card">
                <div class="card-header"><i class="fas fa-arrows-rotate me-2"></i>Avanzar estado</div>
                <div class="card-body">
                    <p class="small text-muted">
                        Estado actual: <strong><?= htmlspecialchars($orden->getEstadoLabel()) ?></strong>
                    </p>
                    <div class="d-grid gap-2">
                        <?php foreach ($transiciones as $t): ?>
                            <?php if ($t === 'Cancelado'): ?>
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalCancelar">
                                <i class="fas fa-times-circle me-1"></i>Cancelar orden
                            </button>
                            <?php else: ?>
                                <?php
                                    // Guard de Etapa 4: no permitir "Entregado" si hay saldo > 0.
                                    // El bloqueo también se enforza en OrdenServicioModel::cambiarEstado.
                                    $bloqueado = ($t === 'Entregado' && (float) $orden->saldo > 0.009);
                                ?>
                            <form method="POST" action="<?= APP_URL ?>Ordenes/cambiarEstado" class="d-grid">
                                <input type="hidden" name="csrf_token" value="<?= $csrfTok ?>">
                                <input type="hidden" name="orden_id"   value="<?= (int) $orden->id ?>">
                                <input type="hidden" name="estado"     value="<?= htmlspecialchars($t) ?>">
                                <button type="submit" class="btn btn-success btn-sm"
                                        <?= $bloqueado ? 'disabled' : '' ?>
                                        <?= $bloqueado ? 'title="Hay saldo pendiente — registrá el pago antes de entregar"' : '' ?>>
                                    <i class="fas fa-arrow-right me-1"></i>
                                    Mover a "<?= htmlspecialchars($t) ?>"
                                    <?php if ($bloqueado): ?>
                                    <small class="d-block mt-1" style="font-weight:normal;opacity:0.85;">
                                        <i class="fas fa-lock me-1"></i>Saldo pendiente
                                    </small>
                                    <?php endif; ?>
                                </button>
                            </form>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal cancelar — pide motivo -->
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>Ordenes/cambiarEstado" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= $csrfTok ?>">
            <input type="hidden" name="orden_id"   value="<?= (int) $orden->id ?>">
            <input type="hidden" name="estado"     value="Cancelado">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar orden <?= htmlspecialchars($orden->codigo) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-semibold">Motivo <span class="text-danger">*</span></label>
                <textarea name="motivo" rows="3" class="form-control" required
                          placeholder="Ej: cliente no aprobó presupuesto, equipo irreparable..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times me-1"></i>Cancelar orden
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Form oculto para eliminar item — submit por JS -->
<form method="POST" action="<?= APP_URL ?>Ordenes/eliminarItem" id="formEliminarItem" class="d-none">
    <input type="hidden" name="csrf_token" value="<?= $csrfTok ?>">
    <input type="hidden" name="item_id"    id="elimItemId" value="">
</form>

<!-- Modal registrar pago — Etapa 4 -->
<?php if (Auth::can('servicio.entregar') && !$orden->estaCerrada() && (float) $orden->saldo > 0.009): ?>
<div class="modal fade" id="modalPago" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?= APP_URL ?>Ordenes/registrarPago" class="modal-content">
            <input type="hidden" name="csrf_token" value="<?= $csrfTok ?>">
            <input type="hidden" name="orden_id"   value="<?= (int) $orden->id ?>">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-cash-register me-2" style="color:#F5A800;"></i>
                    Registrar pago — <?= htmlspecialchars($orden->codigo) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <?php if (empty($cajaAbierta)): ?>
                <div class="alert alert-warning small mb-3">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    No tenés caja abierta. El pago se registra igual, pero no
                    quedará vinculado a una sesión de caja.
                </div>
                <?php else: ?>
                <div class="alert alert-info small mb-3 py-2">
                    <i class="fas fa-cash-register me-1"></i>
                    Caja activa: #<?= (int) $cajaAbierta['id'] ?>
                </div>
                <?php endif; ?>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fw-semibold">
                            Tipo <span class="text-danger">*</span>
                        </label>
                        <select name="tipo" class="form-select" required>
                            <?php if ((float) $orden->total_pagado < 0.01): ?>
                            <option value="anticipo">Anticipo</option>
                            <?php endif; ?>
                            <option value="abono">Abono</option>
                            <option value="saldo">Saldo final</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">
                            Método <span class="text-danger">*</span>
                        </label>
                        <select name="metodo" class="form-select" required>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Transferencia">Transferencia</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-semibold">
                        Monto a cobrar <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">L.</span>
                        <input type="number" name="monto" id="pagoMonto" class="form-control"
                               step="0.01" min="0.01"
                               max="<?= number_format((float) $orden->saldo, 2, '.', '') ?>"
                               value="<?= number_format((float) $orden->saldo, 2, '.', '') ?>"
                               required>
                    </div>
                    <small class="text-muted">
                        Saldo pendiente: L. <?= number_format((float) $orden->saldo, 2) ?>.
                        El pago no puede excederlo.
                    </small>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary flex-fill"
                            onclick="document.getElementById('pagoMonto').value=(<?= (float) $orden->saldo ?>/2).toFixed(2)">
                        50% del saldo
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary flex-fill"
                            onclick="document.getElementById('pagoMonto').value=<?= number_format((float) $orden->saldo, 2, '.', '') ?>">
                        Saldo total
                    </button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-1"></i>Registrar pago
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Form de alta de ítem — alterna campos según tipo.
    const tipo     = document.getElementById('itemTipo');
    const fCatal   = document.getElementById('filaCatalogo');
    const fDesc    = document.getElementById('filaDesc');
    const selCatal = document.getElementById('itemCatalogo');
    const precio   = document.getElementById('itemPrecio');
    const filaPrecio = document.getElementById('filaPrecio');

    function aplicarTipo() {
        if (!tipo) return;
        if (tipo.value === 'servicio_catalogo') {
            fCatal.classList.remove('d-none');
            fDesc.classList.add('d-none');
            filaPrecio.classList.add('d-none');
            if (selCatal.selectedOptions[0]) {
                precio.value = selCatal.selectedOptions[0].dataset.precio || 0;
            }
        } else {
            fCatal.classList.add('d-none');
            fDesc.classList.remove('d-none');
            filaPrecio.classList.remove('d-none');
        }
    }
    if (tipo) {
        tipo.addEventListener('change', aplicarTipo);
        selCatal && selCatal.addEventListener('change', function () {
            const opt = this.selectedOptions[0];
            if (opt) precio.value = opt.dataset.precio || 0;
        });
        aplicarTipo();
    }

    // Eliminar ítem (confirm + submit)
    document.querySelectorAll('.btn-eliminar-item').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const desc = this.dataset.desc || 'este ítem';
            if (!confirm('¿Eliminar "' + desc + '" del presupuesto?')) return;
            document.getElementById('elimItemId').value = this.dataset.id;
            document.getElementById('formEliminarItem').submit();
        });
    });
});
</script>
