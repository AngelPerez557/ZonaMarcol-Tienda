<?php
/**
 * Views/Tienda/MisOrdenesServicio.php — Lista de órdenes de servicio
 * técnico abiertas físicamente para el cliente autenticado. Distinta de
 * `Mis solicitudes` (esas son las que el cliente envió online y aún no
 * llegaron al taller).
 */
?>
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color:#fff;font-weight:800;margin:0;font-size:clamp(1.4rem,4vw,2rem);">
            <i class="fas fa-clipboard-check me-2" style="color:#F5A800;"></i>
            Mis Órdenes de <span style="color:#F5A800;">Servicio</span>
        </h1>
        <a href="<?= APP_URL ?>Tienda/solicitarServicio"
           style="color:#F5A800;text-decoration:none;font-size:0.9rem;">
            <i class="fas fa-plus me-1"></i>Nueva solicitud
        </a>
    </div>

    <?php if (empty($ordenes)): ?>
        <div style="background:#222;border:1px solid #333;border-radius:14px;
                    padding:50px 20px;text-align:center;color:#8a8a8a;">
            <i class="fas fa-clipboard-check fa-3x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
            Todavía no tenés órdenes de servicio abiertas.<br>
            <small>Cuando entregues un equipo en el taller, podrás seguirlo desde acá.</small>
        </div>
    <?php else: ?>

    <div class="row g-3">
        <?php foreach ($ordenes as $o): ?>
        <div class="col-12">
            <a href="<?= APP_URL ?>Tienda/verOrdenServicio/<?= (int) $o->id ?>"
               style="display:block;text-decoration:none;color:inherit;">
                <div style="background:#222;border:1px solid #333;border-radius:14px;
                            padding:18px;transition:border-color 0.2s,transform 0.2s;"
                     onmouseover="this.style.borderColor='#F5A800';this.style.transform='translateY(-2px)';"
                     onmouseout="this.style.borderColor='#333';this.style.transform='none';">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div style="flex:1;min-width:240px;">
                            <div style="color:#F5A800;font-size:0.72rem;font-weight:700;
                                        letter-spacing:0.5px;text-transform:uppercase;">
                                <?= htmlspecialchars($o->codigo) ?>
                            </div>
                            <div style="color:#e6e6e6;font-weight:700;margin-top:4px;">
                                <?= htmlspecialchars($o->equipo_descripcion) ?>
                            </div>
                            <?php if ($o->serial): ?>
                            <small style="color:#8a8a8a;display:block;margin-top:4px;">
                                Serial: <?= htmlspecialchars($o->serial) ?>
                            </small>
                            <?php endif; ?>
                            <small style="color:#8a8a8a;display:block;margin-top:6px;">
                                Recibida el
                                <?= $o->fecha_recepcion
                                    ? date('d/m/Y H:i', strtotime($o->fecha_recepcion))
                                    : '—' ?>
                            </small>
                        </div>

                        <div class="text-end">
                            <span class="badge <?= $o->getEstadoBadge() ?>" style="font-size:0.85rem;">
                                <?= htmlspecialchars($o->getEstadoLabel()) ?>
                            </span>
                            <?php if ((float) $o->total_actual > 0): ?>
                            <div style="color:#F5A800;font-weight:800;font-size:1.1rem;margin-top:6px;">
                                <?= htmlspecialchars($o->getTotalFormateado()) ?>
                            </div>
                            <?php if ((float) $o->saldo > 0.009): ?>
                            <small style="color:#ff9099;">
                                Saldo: L. <?= number_format((float) $o->saldo, 2) ?>
                            </small>
                            <?php else: ?>
                            <small style="color:#28a745;">
                                <i class="fas fa-check me-1"></i>Pagada
                            </small>
                            <?php endif; ?>
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
