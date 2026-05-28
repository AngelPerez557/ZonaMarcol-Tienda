<?php
/**
 * Views/PedidosCamiseta/Detalle.php — Ficha de un pedido de camiseta online.
 * Tres bloques:
 *   - Datos del pedido + cliente + total/anticipo + acciones de workflow
 *   - Comprobante de transferencia (preview)
 *   - Detalle de líneas (equipaciones armadas por el cliente)
 */
?>
<div class="container-fluid py-4">

    <a href="<?= APP_URL ?>PedidosCamiseta/index" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-1"></i>Volver a la bandeja
    </a>

    <div class="d-flex justify-content-between align-items-center mt-2 mb-4">
        <h4 class="fw-bold mb-0">
            <i class="fas fa-tshirt me-2" style="color:#F5A800;"></i>
            <?= htmlspecialchars($pageTitle) ?>
        </h4>
        <span class="badge <?= $pedido->getBadgeEstado() ?> fs-6">
            <?= htmlspecialchars($pedido->getEstadoLabel()) ?>
        </span>
    </div>

    <div class="row g-4">

        <!-- ─── Datos generales + workflow ─── -->
        <div class="col-12 col-lg-5">
            <div class="card mb-4">
                <div class="card-header fw-semibold">
                    <i class="fas fa-user me-2" style="color:#F5A800;"></i>Cliente y totales
                </div>
                <div class="card-body">
                    <dl class="mb-0 small">
                        <dt>Cliente</dt>
                        <dd><?= htmlspecialchars($pedido->cliente_nombre ?? '—') ?></dd>

                        <dt>Email</dt>
                        <dd><?= htmlspecialchars($pedido->cliente_email ?? '—') ?></dd>

                        <dt>Teléfono</dt>
                        <dd><?= htmlspecialchars($pedido->cliente_telefono ?? '—') ?></dd>

                        <dt>Temporada</dt>
                        <dd><?= htmlspecialchars($pedido->temporada_nombre ?? '—') ?></dd>

                        <dt class="mt-2">Subtotal</dt>
                        <dd>L. <?= number_format((float) $pedido->subtotal, 2) ?></dd>

                        <dt>Total</dt>
                        <dd class="fw-bold">L. <?= number_format((float) $pedido->total, 2) ?></dd>

                        <dt>Anticipo pagado</dt>
                        <dd>L. <?= number_format((float) $pedido->anticipo_pagado, 2) ?></dd>

                        <dt>Saldo</dt>
                        <dd>L. <?= number_format((float) $pedido->saldo, 2) ?></dd>

                        <?php if ($pedido->nota): ?>
                        <dt class="mt-2">Nota del cliente</dt>
                        <dd><?= nl2br(htmlspecialchars($pedido->nota)) ?></dd>
                        <?php endif; ?>

                        <dt class="mt-2">Recibido</dt>
                        <dd><?= htmlspecialchars($pedido->created_at ?? '') ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Acciones de workflow -->
            <?php if ($pedido->estado === 'Pendiente_pago'): ?>
            <div class="card mb-4">
                <div class="card-header fw-semibold">
                    <i class="fas fa-check-circle me-2" style="color:#F5A800;"></i>Confirmar pago
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>PedidosCamiseta/confirmarPago">
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="id" value="<?= (int) $pedido->id ?>">
                        <label class="form-label fw-semibold">
                            Monto recibido <span class="text-danger">*</span>
                        </label>
                        <div class="input-group mb-2">
                            <span class="input-group-text">L.</span>
                            <input type="number" step="0.01" min="0.01"
                                   max="<?= (float) $pedido->total ?>"
                                   name="monto" class="form-control"
                                   value="<?= number_format((float) $pedido->getAnticipoMinimo(50), 2, '.', '') ?>"
                                   required>
                        </div>
                        <small class="text-muted">
                            Mínimo sugerido (50%): L.
                            <?= number_format((float) $pedido->getAnticipoMinimo(50), 2) ?>
                        </small>
                        <button type="submit" class="btn btn-success w-100 mt-3">
                            <i class="fas fa-check me-1"></i>Confirmar y registrar anticipo
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($transiciones)): ?>
            <div class="card mb-4">
                <div class="card-header fw-semibold">
                    <i class="fas fa-arrows-rotate me-2" style="color:#F5A800;"></i>Cambiar estado
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>PedidosCamiseta/cambiarEstado">
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="id" value="<?= (int) $pedido->id ?>">
                        <select name="estado" class="form-select mb-2" required>
                            <option value="">— Próximo estado —</option>
                            <?php foreach ($transiciones as $t): ?>
                            <option value="<?= htmlspecialchars($t) ?>">
                                <?= htmlspecialchars(str_replace('_', ' ', $t)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-1"></i>Aplicar transición
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ─── Comprobante + detalle ─── -->
        <div class="col-12 col-lg-7">

            <div class="card mb-4">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-receipt me-2" style="color:#F5A800;"></i>Comprobante de transferencia
                    </span>
                    <?php if ($pedido->comprobante_path): ?>
                    <a href="<?= APP_URL ?>PedidosCamiseta/verComprobante/<?= (int) $pedido->id ?>"
                       target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-up-right-from-square me-1"></i>Abrir en ventana nueva
                    </a>
                    <?php endif; ?>
                </div>
                <div class="card-body text-center">
                    <?php if ($pedido->comprobante_path): ?>
                        <img src="<?= APP_URL ?>PedidosCamiseta/verComprobante/<?= (int) $pedido->id ?>"
                             alt="Comprobante"
                             style="max-width:100%;max-height:480px;border-radius:8px;border:1px solid #333;">
                    <?php else: ?>
                        <div class="text-muted py-4">
                            <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                            El cliente no adjuntó comprobante.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold">
                    <i class="fas fa-list me-2" style="color:#F5A800;"></i>Detalle del pedido
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Equipación</th>
                                    <th>Talla</th>
                                    <th>Personalización</th>
                                    <th class="text-end">P. unit.</th>
                                    <th class="text-end">Extras</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($detalle)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">
                                        Sin líneas en el pedido.
                                    </td>
                                </tr>
                                <?php else: foreach ($detalle as $d): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold">
                                            <?= htmlspecialchars($d['equipo_nombre'] ?? '—') ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($d['torneo_nombre'] ?? '') ?>
                                            · <?= htmlspecialchars($d['tipo_equipacion'] ?? '') ?>
                                            · <?= htmlspecialchars($d['version'] ?? '') ?>
                                            <?php if (!empty($d['temporada_nombre'])): ?>
                                            · <?= htmlspecialchars($d['temporada_nombre']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($d['talla_nombre'] ?? '—') ?>
                                        <?php if (!empty($d['talla_tipo'])): ?>
                                        <small class="text-muted d-block"><?= htmlspecialchars($d['talla_tipo']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $nom = trim((string) ($d['nombre_personalizado'] ?? ''));
                                        $num = trim((string) ($d['numero_personalizado'] ?? ''));
                                        $par = trim((string) ($d['competicion_nombre'] ?? ''));
                                        if ($nom === '' && $num === '' && $par === ''): ?>
                                            <small class="text-muted">Sin personalización</small>
                                        <?php else: ?>
                                            <?php if ($nom !== '' || $num !== ''): ?>
                                            <div>
                                                <?= htmlspecialchars($nom) ?>
                                                <?php if ($num !== ''): ?>
                                                    <span class="badge bg-dark ms-1"><?= htmlspecialchars($num) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($par !== ''): ?>
                                            <small class="text-muted">
                                                <i class="fas fa-shield-halved me-1"></i><?= htmlspecialchars($par) ?>
                                            </small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">L. <?= number_format((float) ($d['precio_unitario'] ?? 0), 2) ?></td>
                                    <td class="text-end">L. <?= number_format((float) ($d['precio_extras'] ?? 0), 2) ?></td>
                                    <td class="text-center"><?= (int) ($d['cantidad'] ?? 1) ?></td>
                                    <td class="text-end fw-semibold">
                                        L. <?= number_format((float) ($d['subtotal'] ?? 0), 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
