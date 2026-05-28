<?php
/**
 * Views/Tienda/MisSolicitudes.php — Lista de solicitudes de servicio
 * técnico online del cliente autenticado. Muestra estado y, si la
 * solicitud fue atendida, el código de la orden creada por recepción.
 */
?>
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color:#fff;font-weight:800;margin:0;font-size:clamp(1.4rem,4vw,2rem);">
            <i class="fas fa-wrench me-2" style="color:#F5A800;"></i>
            Mis Solicitudes de <span style="color:#F5A800;">Servicio</span>
        </h1>
        <a href="<?= APP_URL ?>Tienda/solicitarServicio"
           style="color:#F5A800;text-decoration:none;font-size:0.9rem;">
            <i class="fas fa-plus me-1"></i>Nueva solicitud
        </a>
    </div>

    <?php if (empty($solicitudes)): ?>
        <div style="background:#222;border:1px solid #333;border-radius:14px;
                    padding:50px 20px;text-align:center;color:#8a8a8a;">
            <i class="fas fa-tools fa-3x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
            Todavía no enviaste ninguna solicitud de servicio.<br>
            <a href="<?= APP_URL ?>Tienda/servicios"
               style="color:#F5A800;text-decoration:none;font-weight:700;">
                Ver los servicios disponibles →
            </a>
        </div>
    <?php else: ?>

    <div class="row g-3">
        <?php foreach ($solicitudes as $s): ?>
        <div class="col-12">
            <div style="background:#222;border:1px solid #333;border-radius:14px;padding:18px;">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div style="flex:1;min-width:240px;">
                        <div style="color:#F5A800;font-size:0.72rem;font-weight:700;
                                    letter-spacing:0.5px;text-transform:uppercase;">
                            Solicitud #<?= (int) $s->id ?>
                        </div>
                        <div style="color:#e6e6e6;font-weight:700;margin-top:4px;">
                            <?= htmlspecialchars($s->equipo_descripcion ?? '') ?>
                        </div>
                        <?php if ($s->falla_reportada): ?>
                        <small style="color:#8a8a8a;display:block;margin-top:4px;">
                            <?= nl2br(htmlspecialchars($s->falla_reportada)) ?>
                        </small>
                        <?php endif; ?>
                        <small style="color:#8a8a8a;display:block;margin-top:6px;">
                            Enviada el <?= htmlspecialchars($s->created_at ?? '') ?>
                        </small>
                    </div>

                    <div class="text-end">
                        <span class="badge <?= $s->getEstadoBadge() ?>" style="font-size:0.85rem;">
                            <?= htmlspecialchars($s->estado) ?>
                        </span>

                        <?php if ($s->isAtendida() && $s->codigo_orden): ?>
                        <div style="color:#e6e6e6;font-size:0.8rem;margin-top:6px;">
                            Orden: <strong style="color:#F5A800;">
                                <?= htmlspecialchars($s->codigo_orden) ?>
                            </strong>
                        </div>
                        <small style="color:#8a8a8a;">
                            Coordinamos contigo la recepción del equipo.
                        </small>
                        <?php elseif ($s->isRechazada()): ?>
                        <div style="color:#ff9099;font-size:0.8rem;margin-top:6px;max-width:240px;">
                            <strong>Motivo:</strong>
                            <?= htmlspecialchars($s->motivo_rechazo ?: 'No procede.') ?>
                        </div>
                        <?php else: ?>
                        <small style="color:#8a8a8a;display:block;margin-top:6px;">
                            Te contactamos para coordinar la recepción.
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>
