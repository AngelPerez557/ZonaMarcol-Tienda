<?php
/**
 * Views/Tienda/SolicitudServicio.php — Form para que el cliente envíe una
 * solicitud de servicio técnico online. Queda como `solicitudes_servicio`
 * pendiente; un empleado en recepción la convierte en orden real cuando
 * el cliente lleva el equipo.
 */
$cliente = $_SESSION['cliente'] ?? [];
$telPre  = $cliente['telefono'] ?? '';

// Si vino con ?servicio=ID, prellena la descripción con el nombre del servicio.
$descripcionPre = '';
if (!empty($servicioPre)) {
    foreach ($servicios as $s) {
        if ((int) $s->id === (int) $servicioPre) {
            $descripcionPre = 'Solicito: ' . $s->nombre;
            break;
        }
    }
}
?>
<div class="container py-5">
    <a href="<?= APP_URL ?>Tienda/servicios" style="color:#F5A800;text-decoration:none;font-size:0.9rem;">
        <i class="fas fa-arrow-left me-2"></i>Volver a servicios
    </a>

    <div class="row justify-content-center mt-3">
        <div class="col-12 col-md-8 col-lg-6">

            <div class="text-center mb-4">
                <span style="display:inline-block;font-size:0.75rem;font-weight:700;
                             color:#F5A800;background:rgba(245,168,0,0.12);
                             border:1px solid rgba(245,168,0,0.28);border-radius:20px;padding:4px 14px;">
                    <i class="fas fa-wrench me-1"></i>SERVICIO TÉCNICO
                </span>
                <h2 style="color:#fff;font-weight:800;margin:16px 0 8px;">
                    Solicitar <span style="color:#F5A800;">servicio</span>
                </h2>
                <p style="color:#8a8a8a;font-size:0.9rem;">
                    Contanos qué equipo tenés y qué falla reporta. Te contactamos para coordinar la recepción.
                </p>
            </div>

            <div style="background:#222;border:1px solid #333;border-radius:14px;padding:24px;">
                <form method="POST" action="<?= APP_URL ?>Tienda/guardarSolicitudServicio">
                    <input type="hidden" name="csrf_token"
                           value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#e6e6e6;">
                            Equipo <span style="color:#dc3545;">*</span>
                        </label>
                        <input type="text" class="form-control" name="equipo_descripcion"
                               maxlength="255" required
                               placeholder="Ej: PlayStation 5 blanco, PC gamer, laptop HP..."
                               value="<?= htmlspecialchars($descripcionPre) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#e6e6e6;">
                            Falla reportada
                            <small style="color:#8a8a8a;font-weight:400;">(opcional)</small>
                        </label>
                        <textarea class="form-control" name="falla_reportada" rows="3"
                                  placeholder="¿Qué le pasa al equipo? Síntomas, cuándo empezó..."></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:#e6e6e6;">
                            Teléfono de contacto
                            <small style="color:#8a8a8a;font-weight:400;">(opcional)</small>
                        </label>
                        <input type="text" class="form-control" name="telefono_contacto"
                               maxlength="30" placeholder="9999-9999"
                               value="<?= htmlspecialchars($telPre) ?>">
                    </div>

                    <button type="submit"
                            style="background:#F5A800;color:#1a1a1a;border:none;width:100%;
                                   padding:12px;border-radius:10px;font-weight:800;font-size:1rem;">
                        <i class="fas fa-paper-plane me-2"></i>Enviar solicitud
                    </button>
                </form>
            </div>

            <p class="text-center mt-3" style="color:#8a8a8a;font-size:0.8rem;">
                <i class="fas fa-info-circle me-1"></i>
                Recibimos tu solicitud y un técnico te contacta para coordinar el día y hora de recepción.
            </p>
        </div>
    </div>
</div>
