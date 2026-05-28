<?php
/**
 * Views/Tienda/VerPedidoCamiseta.php — Detalle de un pedido de camiseta
 * del propio cliente. Muestra estado, totales, líneas y avance del proceso.
 * No expone el comprobante (es info interna del admin).
 *
 * Etapas del badge superior — coinciden con el ENUM `pedidos_camiseta.estado`.
 */
$pasos = [
    'Pendiente_pago' => 'Esperando pago',
    'Confirmado'     => 'Pago confirmado',
    'En_proveedor'   => 'En proveedor',
    'Recibido'       => 'En la tienda',
    'Entregado'      => 'Entregado',
];
$pasosOrden = array_keys($pasos);
$indiceActual = array_search($pedido->estado, $pasosOrden, true);
?>
<div class="container py-5">

    <a href="<?= APP_URL ?>Tienda/misPedidosCamiseta"
       style="color:#F5A800;text-decoration:none;font-size:0.9rem;">
        <i class="fas fa-arrow-left me-2"></i>Volver a mis pedidos
    </a>

    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 mb-4 gap-2">
        <div>
            <span style="color:#F5A800;font-size:0.75rem;font-weight:700;letter-spacing:0.5px;
                         text-transform:uppercase;">
                <?= htmlspecialchars($pedido->codigo) ?>
            </span>
            <h2 style="color:#fff;font-weight:800;margin:6px 0 0;">
                <?= htmlspecialchars($pedido->temporada_nombre ?? 'Pedido') ?>
            </h2>
        </div>
        <span class="badge <?= $pedido->getBadgeEstado() ?>" style="font-size:1rem;padding:8px 14px;">
            <?= htmlspecialchars($pedido->getEstadoLabel()) ?>
        </span>
    </div>

    <!-- ─── Línea de tiempo del pedido ─── -->
    <?php if ($pedido->estado !== 'Cancelado'): ?>
    <div style="background:#222;border:1px solid #333;border-radius:14px;padding:20px;margin-bottom:24px;">
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
        Este pedido fue cancelado.
    </div>
    <?php endif; ?>

    <div class="row g-3">

        <!-- ─── Totales ─── -->
        <div class="col-12 col-md-5">
            <div style="background:#222;border:1px solid #333;border-radius:14px;padding:20px;">
                <h6 style="color:#F5A800;text-transform:uppercase;letter-spacing:0.5px;
                           font-size:0.75rem;font-weight:700;">Totales</h6>
                <div class="d-flex justify-content-between mt-3" style="color:#8a8a8a;">
                    <span>Subtotal</span>
                    <span>L. <?= number_format((float) $pedido->subtotal, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mt-1" style="color:#8a8a8a;">
                    <span>Anticipo pagado</span>
                    <span>L. <?= number_format((float) $pedido->anticipo_pagado, 2) ?></span>
                </div>
                <hr style="border-color:#333;">
                <div class="d-flex justify-content-between" style="color:#fff;font-weight:800;">
                    <span>Total</span>
                    <span style="color:#F5A800;font-size:1.25rem;">
                        <?= htmlspecialchars($pedido->getTotalFormateado()) ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mt-1" style="color:#e6e6e6;">
                    <span>Saldo</span>
                    <span>L. <?= number_format((float) $pedido->saldo, 2) ?></span>
                </div>

                <?php if ($pedido->nota): ?>
                <hr style="border-color:#333;">
                <small style="color:#8a8a8a;">
                    <strong style="color:#e6e6e6;">Tu nota:</strong><br>
                    <?= nl2br(htmlspecialchars($pedido->nota)) ?>
                </small>
                <?php endif; ?>
            </div>
        </div>

        <!-- ─── Líneas del pedido ─── -->
        <div class="col-12 col-md-7">
            <div style="background:#222;border:1px solid #333;border-radius:14px;padding:20px;">
                <h6 style="color:#F5A800;text-transform:uppercase;letter-spacing:0.5px;
                           font-size:0.75rem;font-weight:700;">Tu camiseta</h6>

                <?php if (empty($detalle)): ?>
                    <p style="color:#8a8a8a;margin-top:14px;">Sin líneas en el pedido.</p>
                <?php else: foreach ($detalle as $d): ?>
                <div style="border-top:1px solid #333;padding:14px 0;">
                    <div style="color:#e6e6e6;font-weight:700;">
                        <?= htmlspecialchars($d['equipo_nombre'] ?? 'Equipo') ?>
                    </div>
                    <small style="color:#8a8a8a;">
                        <?= htmlspecialchars($d['torneo_nombre'] ?? '') ?>
                        · <?= htmlspecialchars($d['tipo_equipacion'] ?? '') ?>
                        · <?= htmlspecialchars($d['version'] ?? '') ?>
                        <?php if (!empty($d['temporada_nombre'])): ?>
                        · <?= htmlspecialchars($d['temporada_nombre']) ?>
                        <?php endif; ?>
                    </small>

                    <div style="margin-top:8px;font-size:0.85rem;color:#8a8a8a;">
                        <strong style="color:#e6e6e6;">Talla:</strong>
                        <?= htmlspecialchars($d['talla_nombre'] ?? '—') ?>
                        <?php if (!empty($d['talla_tipo'])): ?>
                            (<?= htmlspecialchars($d['talla_tipo']) ?>)
                        <?php endif; ?>
                    </div>

                    <?php
                    $nom = trim((string) ($d['nombre_personalizado'] ?? ''));
                    $num = trim((string) ($d['numero_personalizado'] ?? ''));
                    $par = trim((string) ($d['competicion_nombre'] ?? ''));
                    if ($nom !== '' || $num !== '' || $par !== ''): ?>
                    <div style="margin-top:6px;font-size:0.85rem;color:#8a8a8a;">
                        <strong style="color:#e6e6e6;">Personalización:</strong>
                        <?php if ($nom !== ''): ?><?= htmlspecialchars($nom) ?><?php endif; ?>
                        <?php if ($num !== ''): ?>
                            <span class="badge bg-dark ms-1"><?= htmlspecialchars($num) ?></span>
                        <?php endif; ?>
                        <?php if ($par !== ''): ?>
                            <div><i class="fas fa-shield-halved me-1"></i><?= htmlspecialchars($par) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mt-2">
                        <small style="color:#8a8a8a;">
                            Cant: <?= (int) ($d['cantidad'] ?? 1) ?>
                        </small>
                        <span style="color:#F5A800;font-weight:800;">
                            L. <?= number_format((float) ($d['subtotal'] ?? 0), 2) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>
