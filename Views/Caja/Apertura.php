<div class="container py-5" style="max-width:480px;">

    <div class="text-center mb-4">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
             style="width:72px;height:72px;background:rgba(245,168,0,0.12);">
            <i class="fas fa-cash-register fa-2x" style="color:#F5A800;"></i>
        </div>
        <h3 class="fw-bold mb-1">Apertura de Caja</h3>
        <p class="text-muted">Ingresa el fondo inicial para comenzar el turno.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">

            <div class="mb-3 p-3 rounded" style="background:rgba(245,168,0,0.06);border:1px solid rgba(245,168,0,0.2);">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-user" style="color:#F5A800;"></i>
                    <div>
                        <div class="fw-semibold"><?= htmlspecialchars(Auth::get('nombre')) ?></div>
                        <small class="text-muted"><?= date('d/m/Y H:i') ?></small>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="montoApertura" class="form-label fw-semibold">
                    Fondo inicial (efectivo en caja) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text fw-bold">L.</span>
                    <input type="number"
                           class="form-control form-control-lg"
                           id="montoApertura"
                           min="0" step="0.01" value="0.00"
                           placeholder="0.00">
                </div>
                <small class="text-muted">Dinero físico con el que inicias el turno.</small>
            </div>

            <div class="mb-4">
                <label for="notaApertura" class="form-label fw-semibold">
                    Nota <span class="text-muted fw-normal">(opcional)</span>
                </label>
                <textarea class="form-control" id="notaApertura" rows="2"
                          maxlength="255" placeholder="Observaciones..."></textarea>
            </div>

            <button type="button" class="btn btn-primary w-100 fw-bold py-3 fs-5" id="btnAbrir">
                <i class="fas fa-lock-open me-2"></i>Abrir Caja
            </button>

        </div>
    </div>

</div>

<input type="hidden" id="csrfToken" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
<input type="hidden" id="appUrl"    value="<?= APP_URL ?>">

<script>
document.addEventListener('DOMContentLoaded', function () {
    const appUrl    = document.getElementById('appUrl').value;
    const csrfToken = document.getElementById('csrfToken').value;

    document.getElementById('btnAbrir').addEventListener('click', function () {
        const monto = parseFloat(document.getElementById('montoApertura').value) || 0;
        const nota  = document.getElementById('notaApertura').value.trim();

        if (monto < 0) {
            Swal.fire({ icon:'warning', title:'Monto inválido', text:'El fondo inicial no puede ser negativo.', confirmButtonColor:'#F5A800' });
            return;
        }

        Swal.fire({
            icon:               'question',
            title:              '¿Abrir caja?',
            html:               `Fondo inicial: <strong>L. ${monto.toFixed(2)}</strong>`,
            showCancelButton:   true,
            confirmButtonColor: '#F5A800',
            confirmButtonText:  'Sí, abrir',
            cancelButtonText:   'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            const btn = document.getElementById('btnAbrir');
            btn.disabled   = true;
            btn.innerHTML  = '<i class="fas fa-spinner fa-spin me-2"></i>Abriendo...';

            const form = new FormData();
            form.append('csrf_token',    csrfToken);
            form.append('monto_apertura', monto);
            form.append('nota',           nota);

            fetch(`${appUrl}Caja/abrir`, { method:'POST', body:form })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon:'success', title:'¡Caja abierta!',
                        text:'Puedes comenzar a registrar ventas.',
                        confirmButtonColor:'#F5A800',
                        timer:1800, showConfirmButton:false
                    }).then(() => { window.location.href = `${appUrl}Caja/index`; });
                } else {
                    Swal.fire({ icon:'error', title:'Error', text:data.message, confirmButtonColor:'#F5A800' });
                    btn.disabled  = false;
                    btn.innerHTML = '<i class="fas fa-lock-open me-2"></i>Abrir Caja';
                }
            });
        });
    });
});
</script>