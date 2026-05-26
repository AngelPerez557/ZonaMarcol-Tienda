<?php
/**
 * Views/Tienda/Servicios.php — Catálogo público de servicios técnicos.
 * Tema oscuro. El cliente consulta los servicios; la orden se abre en
 * recepción (no se crea online), por eso el CTA es por WhatsApp.
 */
$iconos = [
    'limpieza'    => 'fa-broom',
    'reparacion'  => 'fa-screwdriver-wrench',
    'diagnostico' => 'fa-magnifying-glass-chart',
    'otro'        => 'fa-tools',
];
?>
<div class="container py-5">

    <!-- ─── CABECERA ─────────────────────────────── -->
    <div class="text-center mb-5">
        <span style="display:inline-block;font-size:0.75rem;font-weight:700;letter-spacing:0.5px;
                     color:#F5A800;background:rgba(245,168,0,0.12);
                     border:1px solid rgba(245,168,0,0.28);border-radius:20px;padding:4px 14px;">
            <i class="fas fa-wrench me-1"></i>SERVICIO TÉCNICO
        </span>
        <h1 style="color:#fff;font-weight:800;margin:16px 0 8px;font-size:clamp(1.8rem,5vw,2.6rem);">
            Reparamos tus <span style="color:#F5A800;">equipos</span>
        </h1>
        <p style="color:#8a8a8a;max-width:520px;margin:0 auto;">
            Diagnóstico, limpieza y reparación de consolas, PC y laptops.
            Elegí un servicio y solicitalo — coordinamos por WhatsApp.
        </p>
    </div>

    <!-- ─── GRID DE SERVICIOS ────────────────────── -->
    <?php if (empty($servicios)): ?>
        <div style="text-align:center;padding:60px 20px;color:#8a8a8a;">
            <i class="fas fa-tools fa-3x mb-3 d-block" style="color:#F5A800;opacity:0.4;"></i>
            Por el momento no hay servicios disponibles.
        </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($servicios as $s): ?>
        <div class="col-12 col-sm-6 col-lg-4">
            <div style="background:#222222;border:1px solid #333333;border-radius:14px;
                        padding:22px;height:100%;display:flex;flex-direction:column;
                        transition:transform 0.2s,border-color 0.2s;">

                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                    <div style="width:42px;height:42px;flex-shrink:0;border-radius:10px;
                                background:rgba(245,168,0,0.12);display:flex;
                                align-items:center;justify-content:center;">
                        <i class="fas <?= $iconos[$s->categoria] ?? 'fa-tools' ?>"
                           style="color:#F5A800;font-size:1.1rem;"></i>
                    </div>
                    <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.5px;
                                 color:#F5A800;text-transform:uppercase;">
                        <?= htmlspecialchars($s->getCategoriaLabel()) ?>
                    </span>
                </div>

                <h5 style="color:#e6e6e6;font-weight:700;font-size:1.05rem;margin-bottom:8px;">
                    <?= htmlspecialchars($s->nombre) ?>
                </h5>
                <p style="color:#8a8a8a;font-size:0.88rem;flex:1;margin-bottom:16px;line-height:1.5;">
                    <?= $s->descripcion
                        ? htmlspecialchars($s->descripcion)
                        : 'Servicio técnico profesional.' ?>
                </p>

                <div style="display:flex;align-items:center;justify-content:space-between;
                            border-top:1px solid #333333;padding-top:14px;">
                    <span style="color:#F5A800;font-weight:800;font-size:1.25rem;">
                        <?= htmlspecialchars($s->getPrecioFormateado()) ?>
                    </span>
                    <a href="https://wa.me/<?= WA_NUMBER ?>?text=<?= urlencode('Hola! Quiero solicitar el servicio: ' . $s->nombre) ?>"
                       target="_blank" rel="noopener"
                       style="background:#F5A800;color:#1a1a1a;font-weight:700;font-size:0.85rem;
                              padding:8px 16px;border-radius:9px;text-decoration:none;
                              display:inline-flex;align-items:center;gap:6px;">
                        <i class="fab fa-whatsapp"></i>Solicitar
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>
