<?php
/**
 * Views/Tienda/VerOrdenServicio.php — Detalle de una orden de servicio
 * del propio cliente. Tres bloques: timeline visual del estado, equipo +
 * datos, presupuesto aprobado, historial de pagos.
 *
 * Importante: solo se muestran items APROBADOS (filtrado en el controller).
 * Lo que el técnico todavía está cotizando no se expone al cliente.
 */
$pasos = [
    'Recibido'             => 'Recibido',
    'Diagnostico'          => 'Diagnóstico',
    'Esperando aprobacion' => 'Esperando aprobación',
    'En reparacion'        => 'En reparación',
    'Listo'                => 'Listo para retirar',
    'Entregado'            => 'Entregado',
];
$pasosOrden    = array_keys($pasos);
$indiceActual  = array_search($orden->estado, $pasosOrden, true);
$cancelada     = ($orden->estado === 'Cancelado');
$totalPagos    = (float) $orden->total_pagado;
?>
<div class="container py-5">

    <a href="<?= APP_URL ?>Tienda/misOrdenesServicio"
       style="color:#F5A800;text-decoration:none;font-size:0.9rem;">
        <i class="fas fa-arrow-left me-2"></i>Volver a mis órdenes
    </a>

    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 mb-4 gap-2">
        <div>
            <span style="color:#F5A800;font-size:0.75rem;font-weight:700;letter-spacing:0.5px;
                         text-transform:uppercase;">
                <?= htmlspecialchars($orden->codigo) ?>
            </span>
            <h2 style="color:#fff;font-weight:800;margin:6px 0 0;">
                <?= htmlspecialchars($orden->equipo_descripcion) ?>
            </h2>
            <?php if ($orden->serial): ?>
            <small style="color:#8a8a8a;">
                Serial: <?= htmlspecialchars($orden->serial) ?>
            </small>
            <?php endif; ?>
        </div>
        <span class="badge <?= $orden->getEstadoBadge() ?>" style="font-size:1rem;padding:8px 14px;">
            <?= htmlspecialchars($orden->getEstadoLabel()) ?>
        </span>
    </div>

    <!-- ─── Timeline ─── -->
    <?php if (!$cancelada): ?>
    <div style="background:#222;border:1px solid #333;border-radius:14px;padding:20px;margin-bottom:24px;">
        <h6 style="color:#F5A800;text-transform:uppercase;letter-spacing:0.5px;
                   font-size:0.75rem;font-weight:700;margin-bottom:18px;">
            Progreso de tu equipo
        </h6>
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <?php $i = 0; foreach ($pasos as $code => $label): ?>
            <?php
                $hecho   = ($indiceActual !== false && $i <= $indiceActual);
                $color   = $hecho ? '#F5A800' : '#3d3d3d';
                $colorTx = $hecho ? '#e6e6e6' : '#666';
            ?>
            <div style="text-align:center;flex:1;min-width:90px;">
                <div style="width:34px;height:34px;border-radius:50%;background:<?= $color ?>;
                            color:#1a1a1a;display:flex;align-items:center;justify-content:center;
                            margin:0 auto;font-weight:800;font-size:0.85rem;">
                    <?= $hecho ? '<i class="fas fa-check"></i>' : ($i + 1) ?>
                </div>
                <small style="display:block;margin-top:6px;color:<?= $colorTx ?>;font-size:0.72rem;">
                    <?= htmlspecialchars($label) ?>
                </small>
            </div>
            <?php $i++; endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div style="background:rgba(220,53,69,0.1);border:1px solid #dc3545;border-radius:10px;
                padding:14px;color:#ff9099;margin-bottom:24px;">
        <i class="fas fa-times-circle me-2"></i>
        Esta orden fue cancelada.
        <?php if (!empty($orden->motivo_cancelacion)): ?>
        <div class="mt-2"><strong>Motivo:</strong> <?= htmlspecialchars($orden->motivo_cancelacion) ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="row g-3">

        <!-- ─── Equipo + datos ─── -->
        <div class="col-12 col-md-5">
            <div style="background:#222;border:1px solid #333;border-radius:14px;padding:20px;height:100%;">
                <h6 style="color:#F5A800;text-transform:uppercase;letter-spacing:0.5px;
                           font-size:0.75rem;font-weight:700;">Tu equipo</h6>

                <div style="color:#e6e6e6;margin-top:14px;">
                    <div style="font-weight:700;"><?= htmlspecialchars($orden->equipo_descripcion) ?></div>
                    <?php if ($orden->accesorios_entregados): ?>
                    <small style="color:#8a8a8a;display:block;margin-top:4px;">
                        <strong style="color:#e6e6e6;">Accesorios:</strong>
                        <?= htmlspecialchars($orden->accesorios_entregados) ?>
                    </small>
                    <?php endif; ?>
                </div>

                <hr style="border-color:#333;">

                <dl class="mb-0 small" style="color:#8a8a8a;">
                    <dt>Recibido el</dt>
                    <dd style="color:#e6e6e6;">
                        <?= $orden->fecha_recepcion
                            ? date('d/m/Y H:i', strtotime($orden->fecha_recepcion))
                            : '—' ?>
                    </dd>

                    <?php if ($orden->fecha_entrega): ?>
                    <dt>Entregado el</dt>
                    <dd style="color:#e6e6e6;">
                        <?= date('d/m/Y H:i', strtotime($orden->fecha_entrega)) ?>
                    </dd>
                    <?php endif; ?>

                    <?php if ($orden->tecnico_nombre): ?>
                    <dt>Técnico asignado</dt>
                    <dd style="color:#e6e6e6;"><?= htmlspecialchars($orden->tecnico_nombre) ?></dd>
                    <?php endif; ?>
                </dl>

                <?php if ((float) $orden->total_actual > 0): ?>
                <hr style="border-color:#333;">
                <div class="d-flex justify-content-between mt-2" style="color:#8a8a8a;">
                    <span>Presupuesto</span>
                    <span>L. <?= number_format((float) $orden->total_actual, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mt-1" style="color:#8a8a8a;">
                    <span>Pagado</span>
                    <span style="color:#28a745;">L. <?= number_format($totalPagos, 2) ?></span>
                </div>
                <hr style="border-color:#333;">
                <div class="d-flex justify-content-between" style="color:#fff;font-weight:800;">
                    <span>Saldo</span>
                    <span style="color:<?= (float) $orden->saldo > 0.009 ? '#ff9099' : '#28a745' ?>;font-size:1.15rem;">
                        L. <?= number_format((float) $orden->saldo, 2) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ─── Items aprobados ─── -->
        <div class="col-12 col-md-7">
            <div style="background:#222;border:1px solid #333;border-radius:14px;padding:20px;">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 style="color:#F5A800;text-transform:uppercase;letter-spacing:0.5px;
                               font-size:0.75rem;font-weight:700;margin:0;">
                        Presupuesto aprobado
                    </h6>
                    <span class="badge bg-dark"><?= count($items) ?></span>
                </div>

                <?php if (empty($items)): ?>
                <p style="color:#8a8a8a;margin-top:14px;font-size:0.9rem;">
                    <i class="fas fa-info-circle me-1" style="color:#F5A800;"></i>
                    Todavía no hay ítems aprobados. Cuando el técnico termine de
                    diagnosticar te avisamos para que apruebes el presupuesto.
                </p>
                <?php else: foreach ($items as $it): ?>
                <div style="border-top:1px solid #333;padding:14px 0;">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div style="flex:1;">
                            <div style="color:#e6e6e6;font-weight:700;">
                                <?= htmlspecialchars($it->descripcion) ?>
                            </div>
                            <small style="color:#8a8a8a;">
                                <?= htmlspecialchars($it->getTipoLabel()) ?>
                                · Garantía <?= (int) $it->dias_garantia ?> días
                            </small>
                        </div>
                        <div class="text-end">
                            <small style="color:#8a8a8a;">
                                <?= (int) $it->cantidad ?> × L. <?= number_format((float) $it->precio_unitario, 2) ?>
                            </small>
                            <div style="color:#F5A800;font-weight:800;">
                                <?= htmlspecialchars($it->getSubtotalFormateado()) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- ─── Pagos ─── -->
        <?php if (!empty($pagos)): ?>
        <div class="col-12">
            <div style="background:#222;border:1px solid #333;border-radius:14px;padding:20px;">
                <h6 style="color:#F5A800;text-transform:uppercase;letter-spacing:0.5px;
                           font-size:0.75rem;font-weight:700;margin-bottom:14px;">
                    Tus pagos
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="color:#e6e6e6;">
                        <thead>
                            <tr style="color:#8a8a8a;font-size:0.75rem;text-transform:uppercase;">
                                <th>Recibo</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Método</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $pg): ?>
                            <tr style="border-color:#333;">
                                <td><code style="color:#F5A800;"><?= htmlspecialchars($pg->recibo_numero ?: '—') ?></code></td>
                                <td><small style="color:#8a8a8a;"><?= htmlspecialchars($pg->fecha) ?></small></td>
                                <td><?= htmlspecialchars($pg->getTipoLabel()) ?></td>
                                <td>
                                    <i class="fas <?= $pg->getMetodoIcon() ?> me-1" style="color:#F5A800;"></i>
                                    <?= htmlspecialchars($pg->metodo) ?>
                                </td>
                                <td class="text-end fw-semibold">
                                    <?= htmlspecialchars($pg->getMontoFormateado()) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total pagado:</td>
                                <td class="text-end fw-bold" style="color:#28a745;">
                                    L. <?= number_format($totalPagos, 2) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
