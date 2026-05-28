<?php
/**
 * Views/Tienda/Configurador.php — Form para que el cliente arme su
 * camiseta y haga el pedido online subiendo el comprobante de transferencia.
 * Reemplaza el envío por WhatsApp. Crea pedido_camiseta + detalle + notif.
 */
// Map de precios extras (concepto => precio) para el cálculo en JS.
$mapExtras = [];
foreach ($extras as $e) {
    $mapExtras[$e['concepto']] = (float) $e['precio'];
}
$base = (float) $equipacion->precio_base;
?>
<div class="container py-5">

    <a href="<?= APP_URL ?>Tienda/camisetas" style="color:#F5A800;text-decoration:none;font-size:0.9rem;">
        <i class="fas fa-arrow-left me-2"></i>Volver a camisetas
    </a>

    <div class="row g-4 mt-2">
        <!-- ─── Imagen + info ─── -->
        <div class="col-12 col-md-5">
            <div style="background:#222;border:1px solid #333;border-radius:14px;overflow:hidden;">
                <div style="height:320px;background:#1a1a1a;
                            background-image:url('<?= htmlspecialchars($equipacion->getImagenUrl()) ?>');
                            background-size:contain;background-position:center;
                            background-repeat:no-repeat;"></div>
            </div>
            <div class="mt-3" style="color:#e6e6e6;">
                <div style="font-size:0.72rem;font-weight:700;color:#F5A800;letter-spacing:0.4px;
                            text-transform:uppercase;">
                    <?= htmlspecialchars($equipacion->torneo_nombre ?? 'Liga') ?>
                </div>
                <h3 style="color:#fff;font-weight:800;margin:6px 0 4px;">
                    <?= htmlspecialchars($equipacion->equipo_nombre ?? 'Equipo') ?>
                </h3>
                <div style="color:#8a8a8a;font-size:0.9rem;">
                    <?= htmlspecialchars($equipacion->tipo_nombre ?? '') ?>
                    · <?= htmlspecialchars($equipacion->getVersionLabel()) ?>
                    <?php if ($equipacion->temporada_nombre): ?>
                    · <?= htmlspecialchars($equipacion->temporada_nombre) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ─── Formulario ─── -->
        <div class="col-12 col-md-7">
            <div style="background:#222;border:1px solid #333;border-radius:14px;padding:24px;">
                <h4 style="color:#fff;font-weight:800;margin-bottom:16px;">Configurar tu camiseta</h4>

                <form method="POST"
                      action="<?= APP_URL ?>Tienda/configuradorSave"
                      enctype="multipart/form-data"
                      autocomplete="off">
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="equipacion_id" value="<?= (int) $equipacion->id ?>">

                    <!-- Talla -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#e6e6e6;">
                            Talla <span style="color:#dc3545;">*</span>
                        </label>
                        <select class="form-select" name="talla_id" required>
                            <option value="">— Elegí tu talla —</option>
                            <?php foreach ($tallas as $t): ?>
                                <?php if (!(int) $t['activo']) continue; ?>
                            <option value="<?= (int) $t['id'] ?>">
                                <?= htmlspecialchars($t['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Personalización -->
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label fw-semibold" style="color:#e6e6e6;">
                                Nombre en la camiseta
                                <small style="color:#8a8a8a;font-weight:400;">(opcional)</small>
                            </label>
                            <input type="text" class="form-control" id="nombre_personalizado"
                                   name="nombre_personalizado" maxlength="40"
                                   placeholder="Ej: MESSI">
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-semibold" style="color:#e6e6e6;">
                                Número
                                <small style="color:#8a8a8a;font-weight:400;">(opcional)</small>
                            </label>
                            <input type="text" class="form-control" id="numero_personalizado"
                                   name="numero_personalizado" maxlength="5" placeholder="10">
                        </div>
                    </div>

                    <!-- Parche / competición -->
                    <?php if (!empty($competiciones)): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#e6e6e6;">
                            Parche de competición
                            <small style="color:#8a8a8a;font-weight:400;">(opcional)</small>
                        </label>
                        <select class="form-select" id="competicion_id" name="competicion_id">
                            <option value="">— Sin parche —</option>
                            <?php foreach ($competiciones as $c): ?>
                            <option value="<?= (int) ($c['id'] ?? $c['competicion_id'] ?? 0) ?>">
                                <?= htmlspecialchars($c['nombre'] ?? '') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Comprobante -->
                    <div class="mb-3 p-3" style="background:#1a1a1a;border:1px solid #4a4a4a;border-radius:10px;">
                        <label class="form-label fw-semibold" style="color:#e6e6e6;">
                            <i class="fas fa-receipt me-2" style="color:#F5A800;"></i>
                            Comprobante de transferencia <span style="color:#dc3545;">*</span>
                        </label>
                        <input type="file" class="form-control" name="comprobante"
                               accept="image/jpeg,image/png,image/webp" required>
                        <small style="color:#8a8a8a;">
                            JPG, PNG o WEBP. Subí el comprobante y validamos el pago.
                        </small>
                    </div>

                    <!-- Nota -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#e6e6e6;">
                            Comentarios <small style="color:#8a8a8a;font-weight:400;">(opcional)</small>
                        </label>
                        <textarea class="form-control" name="nota" rows="2" maxlength="500"
                                  placeholder="Algo que debamos saber..."></textarea>
                    </div>

                    <!-- Resumen -->
                    <div class="p-3 mb-3" style="background:#1a1a1a;border-radius:10px;border:1px solid #333;">
                        <div class="d-flex justify-content-between" style="color:#8a8a8a;">
                            <span>Precio camiseta</span>
                            <span>L. <?= number_format($base, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mt-1" style="color:#8a8a8a;">
                            <span>Extras</span>
                            <span id="cfgExtras">L. 0.00</span>
                        </div>
                        <hr style="border-color:#333;">
                        <div class="d-flex justify-content-between" style="color:#fff;font-weight:800;">
                            <span>Total</span>
                            <span style="color:#F5A800;font-size:1.2rem;" id="cfgTotal">
                                L. <?= number_format($base, 2) ?>
                            </span>
                        </div>
                    </div>

                    <button type="submit"
                            style="background:#F5A800;color:#1a1a1a;border:none;width:100%;
                                   padding:12px;border-radius:10px;font-weight:800;font-size:1rem;">
                        <i class="fas fa-paper-plane me-2"></i>Confirmar pedido
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const EXTRAS = <?= json_encode($mapExtras, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const BASE   = <?= json_encode($base) ?>;
    const nombre = document.getElementById('nombre_personalizado');
    const numero = document.getElementById('numero_personalizado');
    const parche = document.getElementById('competicion_id');
    const elExt  = document.getElementById('cfgExtras');
    const elTot  = document.getElementById('cfgTotal');

    function calc() {
        const n = (nombre && nombre.value.trim() !== '');
        const m = (numero && numero.value.trim() !== '');
        const p = (parche && parche.value !== '');
        let e = 0;
        if (n && m)      e += +(EXTRAS['nombre_y_numero'] || 0);
        else if (n)      e += +(EXTRAS['nombre'] || 0);
        else if (m)      e += +(EXTRAS['numero'] || 0);
        if (p)           e += +(EXTRAS['parche'] || 0);
        elExt.textContent = 'L. ' + e.toFixed(2);
        elTot.textContent = 'L. ' + (BASE + e).toFixed(2);
    }
    [nombre, numero, parche].forEach(el => el && el.addEventListener('input',  calc));
    [nombre, numero, parche].forEach(el => el && el.addEventListener('change', calc));
})();
</script>
