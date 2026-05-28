<?php
/**
 * Views/Tienda/MisPedidosCamiseta.php — Lista de pedidos online de
 * camisetas del cliente autenticado. Cada fila lleva al detalle propio
 * (verPedidoCamiseta/{id}). Sin enlaces al panel admin.
 */
?>
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color:#fff;font-weight:800;margin:0;font-size:clamp(1.4rem,4vw,2rem);">
            <i class="fas fa-tshirt me-2" style="color:#F5A800;"></i>
            Mis Pedidos de <span style="color:#F5A800;">Camisetas</span>
        </h1>
        <a href="<?= APP_URL ?>Tienda/camisetas"
           style="color:#F5A800;text-decoration:none;font-size:0.9rem;">
            <i class="fas fa-plus me-1"></i>Nuevo pedido
        </a>
    </div>

    <?php if (empty($pedidos)): ?>
        <div style="background:#222;border:1px solid #333;border-radius:14px;
                    padding:50px 20px;text-align:center;color:#8a8a8a;">
            <i class="fas fa-receipt fa-3x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
            Todavía no hiciste ningún pedido de camiseta.<br>
            <a href="<?= APP_URL ?>Tienda/camisetas"
               style="color:#F5A800;text-decoration:none;font-weight:700;">
                Ver el catálogo →
            </a>
        </div>
    <?php else: ?>

    <div class="row g-3">
        <?php foreach ($pedidos as $p): ?>
        <div class="col-12">
            <a href="<?= APP_URL ?>Tienda/verPedidoCamiseta/<?= (int) $p->id ?>"
               style="display:block;text-decoration:none;color:inherit;">
                <div style="background:#222;border:1px solid #333;border-radius:14px;
                            padding:18px;transition:border-color 0.2s,transform 0.2s;"
                     onmouseover="this.style.borderColor='#F5A800';this.style.transform='translateY(-2px)';"
                     onmouseout="this.style.borderColor='#333';this.style.transform='none';">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <div style="color:#F5A800;font-size:0.72rem;font-weight:700;
                                        letter-spacing:0.5px;text-transform:uppercase;">
                                <?= htmlspecialchars($p->codigo) ?>
                            </div>
                            <div style="color:#e6e6e6;font-weight:700;margin-top:4px;">
                                <?= htmlspecialchars($p->temporada_nombre ?? '—') ?>
                            </div>
                            <small style="color:#8a8a8a;">
                                Pedido el <?= htmlspecialchars($p->created_at ?? '') ?>
                            </small>
                        </div>

                        <div class="text-end">
                            <div>
                                <span class="badge <?= $p->getBadgeEstado() ?>" style="font-size:0.85rem;">
                                    <?= htmlspecialchars($p->getEstadoLabel()) ?>
                                </span>
                            </div>
                            <div style="color:#F5A800;font-weight:800;font-size:1.15rem;margin-top:4px;">
                                <?= htmlspecialchars($p->getTotalFormateado()) ?>
                            </div>
                            <?php if ((float) $p->anticipo_pagado > 0): ?>
                            <small style="color:#8a8a8a;">
                                Anticipo: L. <?= number_format((float) $p->anticipo_pagado, 2) ?>
                                · Saldo: L. <?= number_format((float) $p->saldo, 2) ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>
