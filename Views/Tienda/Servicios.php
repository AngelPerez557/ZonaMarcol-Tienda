<?php if (empty($servicios)): ?>
<div class="container py-5 text-center text-muted">
    <i class="fas fa-tools fa-3x mb-3" style="opacity:0.3"></i>
    <h4 class="fw-bold">No hay servicios disponibles</h4>
    <p class="mb-0">Estamos preparando nuestros servicios. Volvé pronto.</p>
</div>
<?php else: ?>
<div class="container my-5">
    <h3 class="fw-bold mb-4">Servicios técnicos</h3>
    <div class="row g-3">
        <?php foreach ($servicios as $s): ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="producto-card p-3" style="background:#fff8ed;">
                <h5 class="mb-2 fw-semibold"><?= htmlspecialchars($s->nombre) ?></h5>
                <p class="text-muted mb-2" style="font-size:0.95rem;"><?= nl2br(htmlspecialchars($s->descripcion)) ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-bold" style="color:var(--rosa);">L. <?= number_format((float)$s->precio,2) ?></div>
                    <a href="<?= APP_URL ?>Tienda/servicio/<?= $s->id ?>" class="btn-rosa-outline">Ver</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
