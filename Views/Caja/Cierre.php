<div class="container py-4" style="max-width:600px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">
                <i class="fas fa-store-slash me-2" style="color:#F5A800;"></i>Cierre de Caja
            </h3>
            <small class="text-muted">
                Turno iniciado: <?= date('d/m/Y H:i', strtotime($sesion['abierta_at'])) ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Caja/index" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    <!-- Resumen del turno -->
    <div class="card mb-4">
        <div class="card-header fw-semibold">
            <i class="fas fa-chart-bar me-2"></i>Resumen del turno
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded" style="background:rgba(245,168,0,0.08);">
                        <div class="fw-bold fs-4" style="color:#F5A800;">
                            <?= $totales['cantidad_ventas'] ?? 0 ?>
                        </div>
                        <small class="text-muted">Ventas</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded" style="background:rgba(40,167,69,0.08);">
                        <div class="fw-bold fs-6" style="color:#28a745;">
                            L. <?= number_format((float)($totales['total_ventas'] ?? 0), 2) ?>
                        </div>
                        <small class="text-muted">Total vendido</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded" style="background:rgba(23,162,184,0.08);">
                        <div class="fw-bold fs-6" style="color:#17a2b8;">
                            L. <?= number_format((float)($totales['total_efectivo'] ?? 0), 2) ?>
                        </div>
                        <small class="text-muted">Efectivo</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center p-3 rounded" style="background:rgba(220,53,69,0.08);">
                        <div class="fw-bold fs-6" style="color:#dc3545;">
                            <?= $totales['cantidad_anuladas'] ?? 0 ?>
                        </div>
                        <small class="text-muted">Anuladas</small>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-3 border-top">
                <div class="row g-2">
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Efectivo:</span>
                            <strong>L. <?= number_format((float)($totales['total_efectivo'] ?? 0), 2) ?></strong>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Tarjeta:</span>
                            <strong>L. <?= number_format((float)($totales['total_tarjeta'] ?? 0), 2) ?></strong>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Transferencia:</span>
                            <strong>L. <?= number_format((float)($totales['total_transferencia'] ?? 0), 2) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteo de efectivo -->
    <div class="card mb-4">
        <div class="card-header fw-semibold">
            <i class="fas fa-calculator me-2"></i>Conteo de efectivo
        </div>
        <div class="card-body">
            <?php
            $fondoInicial  = (float) $sesion['monto_apertura'];
            $totalEfectivo = (float) ($totales['total_efectivo'] ?? 0);
            $montoSistema  = $fondoInicial + $totalEfectivo;
            ?>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Fondo inicial:</span>
                <strong>L. <?= number_format($fondoInicial, 2) ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">+ Cobros en efectivo:</span>
                <strong>L. <?= number_format($totalEfectivo, 2) ?></strong>
            </div>
            <div class="d-flex justify-content-between fw-bold pt-2 border-top" style="font-size:1.1rem;">
                <span>Sistema espera:</span>
                <span style="color:#F5A800;" id="montoSistemaDisplay">
                    L. <?= number_format($montoSistema, 2) ?>
                </span>
            </div>

            <div class="mt-3">
                <label for="montoCierre" class="form-label fw-semibold">
                    Efectivo contado físicamente <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text fw-bold">L.</span>
                    <input type="number" class="form-control" id="montoCierre"
                           min="0" step="0.01" value="<?= number_format($montoSistema, 2, '.', '') ?>"
                           placeholder="0.00">
                </div>
            </div>

            <!-- Diferencia en tiempo real -->
            <div class="mt-3 p-3 rounded" id="panelDiferencia"
                 style="background:rgba(40,167,69,0.08); border:1px solid rgba(40,167,69,0.2);">
                <div class="d-flex justify-content-between fw-semibold">
                    <span>Diferencia:</span>
                    <span id="txtDiferencia" class="text-success">L. 0.00</span>
                </div>
                <small id="lblDiferencia" class="text-success">Sin diferencia</small>
            </div>
        </div>
    </div>

    <!-- Nota de cierre -->
    <div class="card mb-4">
        <div class="card-body">
            <label for="notaCierre" class="form-label fw-semibold">
                Nota de cierre <span class="text-muted fw-normal">(opcional)</span>
            </label>
            <textarea class="form-control" id="notaCierre" rows="2"
                      maxlength="255" placeholder="Observaciones del turno..."></textarea>
        </div>
    </div>

    <button type="button" class="btn btn-danger w-100 fw-bold py-3 fs-5" id="btnCerrar">
        <i class="fas fa-store-slash me-2"></i>Cerrar Caja
    </button>

</div>

<input type="hidden" id="csrfToken"    value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
<input type="hidden" id="appUrl"       value="<?= APP_URL ?>">
<input type="hidden" id="montoSistema" value="<?= $montoSistema ?>">

<script>
document.addEventListener('DOMContentLoaded', function () {
    const appUrl       = document.getElementById('appUrl').value;
    const csrfToken    = document.getElementById('csrfToken').value;
    const montoSistema = parseFloat(document.getElementById('montoSistema').value) || 0;

    const inputCierre   = document.getElementById('montoCierre');
    const panelDif      = document.getElementById('panelDiferencia');
    const txtDif        = document.getElementById('txtDiferencia');
    const lblDif        = document.getElementById('lblDiferencia');

    function calcularDiferencia() {
        const contado    = parseFloat(inputCierre.value) || 0;
        const diferencia = contado - montoSistema;

        txtDif.textContent = `L. ${diferencia >= 0 ? '+' : ''}${diferencia.toFixed(2)}`;

        if (Math.abs(diferencia) < 0.01) {
            panelDif.style.background = 'rgba(40,167,69,0.08)';
            panelDif.style.border     = '1px solid rgba(40,167,69,0.2)';
            txtDif.className          = 'text-success';
            lblDif.className          = 'text-success';
            lblDif.textContent        = 'Sin diferencia ✓';
        } else if (diferencia > 0) {
            panelDif.style.background = 'rgba(23,162,184,0.08)';
            panelDif.style.border     = '1px solid rgba(23,162,184,0.2)';
            txtDif.className          = 'text-info';
            lblDif.className          = 'text-info';
            lblDif.textContent        = 'Sobrante en caja';
        } else {
            panelDif.style.background = 'rgba(220,53,69,0.08)';
            panelDif.style.border     = '1px solid rgba(220,53,69,0.2)';
            txtDif.className          = 'text-danger';
            lblDif.className          = 'text-danger';
            lblDif.textContent        = 'Faltante en caja';
        }
    }

    inputCierre.addEventListener('input', calcularDiferencia);
    calcularDiferencia();

    document.getElementById('btnCerrar').addEventListener('click', function () {
        const montoCierre = parseFloat(inputCierre.value) || 0;
        const diferencia  = montoCierre - montoSistema;
        const notaCierre  = document.getElementById('notaCierre').value.trim();

        const difTexto = diferencia >= 0
            ? `<span class="text-info">Sobrante: L. ${diferencia.toFixed(2)}</span>`
            : `<span class="text-danger">Faltante: L. ${Math.abs(diferencia).toFixed(2)}</span>`;

        Swal.fire({
            icon:               'warning',
            title:              '¿Cerrar caja?',
            html:               `Efectivo contado: <strong>L. ${montoCierre.toFixed(2)}</strong><br>
                                 Sistema espera: <strong>L. ${montoSistema.toFixed(2)}</strong><br>
                                 ${Math.abs(diferencia) > 0.01 ? difTexto : '<span class="text-success">Sin diferencia ✓</span>'}`,
            showCancelButton:   true,
            confirmButtonColor: '#dc3545',
            confirmButtonText:  'Sí, cerrar caja',
            cancelButtonText:   'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            const btn = document.getElementById('btnCerrar');
            btn.disabled  = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Cerrando...';

            const form = new FormData();
            form.append('csrf_token',  csrfToken);
            form.append('monto_cierre', montoCierre);
            form.append('nota_cierre',  notaCierre);

            fetch(`${appUrl}Caja/cerrar`, { method:'POST', body:form })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon:'success', title:'Caja cerrada',
                        text:'El turno fue registrado correctamente.',
                        confirmButtonColor:'#F5A800',
                        confirmButtonText:'Ver resumen'
                    }).then(() => {
                        window.location.href = `${appUrl}Caja/resumen/${data.sesion_id}`;
                    });
                } else {
                    Swal.fire({ icon:'error', title:'Error', text:data.message, confirmButtonColor:'#F5A800' });
                    btn.disabled  = false;
                    btn.innerHTML = '<i class="fas fa-store-slash me-2"></i>Cerrar Caja';
                }
            });
        });
    });
});
</script>