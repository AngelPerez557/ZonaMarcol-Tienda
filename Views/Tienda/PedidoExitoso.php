<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">

            <!-- ── Confirmación ─────────────────────────────── -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-5 text-center">
                    <div class="mb-3" style="font-size:4rem;">🎉</div>
                    <h3 class="fw-bold mb-2" style="color:#F5A800;">¡Pedido confirmado!</h3>
                    <p class="text-muted mb-1">Tu pedido fue recibido exitosamente.</p>

                    <?php if ($pedido->Found): ?>
                    <div class="my-3 p-3 rounded" style="background:rgba(245,168,0,0.08);">
                        <div class="fw-bold" style="color:#F5A800; font-size:1.3rem;">
                            #<?= htmlspecialchars($pedido->codigo) ?>
                        </div>
                        <small class="text-muted">Código de seguimiento</small>
                    </div>
                    <p class="text-muted" style="font-size:0.85rem;">
                        Te notificaremos por WhatsApp cuando tu pedido esté listo.
                    </p>
                    <?php if (!empty($pedido->wa_numero) || !empty($pedido->cliente_telefono)): ?>
                    <a href="<?= $pedido->getWhatsAppUrl($detalle) ?>"
                       target="_blank"
                       class="btn-rosa d-block mb-3 py-2 text-decoration-none">
                        <i class="fab fa-whatsapp me-2"></i>Ver confirmación en WhatsApp
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <a href="<?= APP_URL ?>Tienda/catalogo"
                       class="btn-rosa-outline d-block py-2">
                        <i class="fas fa-arrow-left me-1"></i>Seguir comprando
                    </a>
                </div>
            </div>

            <!-- ── FACTURA / RECIBO ─────────────────────────── -->
            <?php if ($pedido->Found && !empty($detalle) && !empty($factConfig)): ?>

            <?php
                /*
                 * Separación fiscal:
                 *   $subtotalFiscal  → base imponible (solo productos) — va en la factura CAI
                 *   $costoEnvio      → servicio logístico — se muestra FUERA del cuerpo fiscal
                 *   $totalRecibo     → lo que el cliente paga en total (referencia en el recibo)
                 *
                 * PedidoModel almacena subtotal, costo_envio y total correctamente;
                 * aquí solo controlamos QUÉ entra en la sección fiscal imprimible.
                 */
                $subtotalFiscal = (float)$pedido->subtotal;
                $costoEnvio     = (float)$pedido->costo_envio;
                $totalRecibo    = (float)$pedido->total; // subtotal + envío
            ?>

            <div class="card shadow-sm" id="facturaRecibo">
                <div class="card-body p-4">

                    <!-- Cabecera factura -->
                    <div class="text-center mb-3 pb-3 border-bottom">
                        <img src="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png"
                             alt="Zona Marcol"
                             style="height:48px; object-fit:contain; display:block; margin:0 auto 8px;">
                        <h5 class="fw-bold mt-2 mb-0">
                            <?= htmlspecialchars($factConfig['nombre_fiscal'] ?? 'ZONA MARCOL') ?>
                        </h5>
                        <small class="text-muted d-block">
                            RTN: <?= htmlspecialchars($factConfig['rtn'] ?? '') ?>
                        </small>
                        <small class="text-muted d-block">
                            <?= htmlspecialchars($factConfig['direccion_fiscal'] ?? '') ?>
                        </small>
                    </div>

                    <!-- Datos del documento -->
                    <div class="row mb-3" style="font-size:0.82rem;">
                        <div class="col-6">
                            <strong>Fecha:</strong>
                            <?= date('d/m/Y H:i') ?>
                        </div>
                        <div class="col-6 text-end">
                            <strong>Pedido:</strong>
                            #<?= htmlspecialchars($pedido->codigo) ?>
                        </div>
                        <?php if (!empty($factConfig['cai'])): ?>
                        <div class="col-12 mt-1">
                            <strong>CAI:</strong>
                            <?= htmlspecialchars($factConfig['cai']) ?>
                        </div>
                        <div class="col-12">
                            <strong>Rango:</strong>
                            <?= htmlspecialchars($factConfig['rango_desde'] ?? '') ?>
                            —
                            <?= htmlspecialchars($factConfig['rango_hasta'] ?? '') ?>
                        </div>
                        <div class="col-12">
                            <strong>Fecha límite:</strong>
                            <?= !empty($factConfig['fecha_limite'])
                                ? date('d/m/Y', strtotime($factConfig['fecha_limite']))
                                : '' ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Cliente -->
                    <?php if (!empty($_SESSION['cliente'])): ?>
                    <div class="mb-3 p-2 rounded" style="background:#FFFBF2; font-size:0.82rem;">
                        <strong>Cliente:</strong>
                        <?= htmlspecialchars($_SESSION['cliente']['nombre']) ?>
                        <?php if (!empty($_SESSION['cliente']['telefono'])): ?>
                        — <?= htmlspecialchars($_SESSION['cliente']['telefono']) ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- ── Detalle de productos (cuerpo fiscal) ── -->
                    <table class="table table-sm mb-0" style="font-size:0.82rem;">
                        <thead>
                            <tr style="background:rgba(245,168,0,0.1);">
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalle as $d): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($d['nombre_producto']) ?>
                                    <?php if (!empty($d['variante_nombre'])): ?>
                                    <small class="text-muted d-block">
                                        <?= htmlspecialchars($d['variante_nombre']) ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= (int)$d['cantidad'] ?></td>
                                <td class="text-end">
                                    L. <?= number_format((float)$d['precio_unit'], 2) ?>
                                </td>
                                <td class="text-end">
                                    L. <?= number_format((float)$d['subtotal'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>

                        <tfoot>
                            <!-- Total fiscal: solo productos -->
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end" style="color:#F5A800;">
                                    TOTAL FACTURA
                                </td>
                                <td class="text-end" style="color:#F5A800;">
                                    L. <?= number_format($subtotalFiscal, 2) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- ── Costo de envío — FUERA del cuerpo fiscal ── -->
                    <?php if ($costoEnvio > 0): ?>
                    <div class="mt-2 pt-2 border-top" style="font-size:0.82rem;">
                        <!--
                            El envío es un servicio logístico separado.
                            No forma parte de la base imponible de la factura CAI.
                        -->
                        <div class="d-flex justify-content-between text-muted">
                            <span>
                                <i class="fas fa-truck me-1"></i>
                                Servicio de envío
                                <small class="d-block" style="font-size:0.72rem;">
                                    (no incluido en factura)
                                </small>
                            </span>
                            <span>L. <?= number_format($costoEnvio, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between fw-bold pt-2 border-top mt-2"
                             style="font-size:0.9rem;">
                            <span>Total a pagar</span>
                            <span style="color:#F5A800;">
                                L. <?= number_format($totalRecibo, 2) ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Pie de factura -->
                    <div class="text-center text-muted mt-3"
                         style="font-size:0.75rem; border-top:1px dashed #ddd; padding-top:10px;">
                        <p class="mb-1">¡Gracias por tu compra!</p>
                        <p class="mb-0">
                            Este documento es un comprobante de tu pedido en línea.
                        </p>
                    </div>

                    <!-- Botón imprimir -->
                    <button onclick="window.print()"
                            class="btn-rosa w-100 mt-3">
                        <i class="fas fa-print me-2"></i>Imprimir recibo
                    </button>

                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<style>
@media print {
    /* Ocultar navbar, footer y botones al imprimir */
    .tienda-navbar, .tienda-footer,
    .btn-rosa-outline, .btn-rosa:not(#facturaRecibo .btn-rosa),
    .toast-carrito { display: none !important; }
    /* Mostrar solo la factura */
    body { background: #fff !important; }
    #facturaRecibo { box-shadow: none !important; }
}
</style>