<?php if (empty($equipaciones)): ?>
<div class="container py-5 text-center text-muted">
    <i class="fas fa-tshirt fa-3x mb-3" style="opacity:0.3"></i>
    <h4 class="fw-bold">No hay equipaciones disponibles</h4>
    <p class="mb-0">Se están cargando las camisas. Volvé más tarde.</p>
</div>
<?php else: ?>
<div class="container my-5">
    <h3 class="fw-bold mb-4">Camisetas y equipaciones</h3>
    <div class="row g-3">
        <?php foreach ($equipaciones as $eq): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="producto-card">
                <div style="position:relative;">
                    <a href="<?= APP_URL ?>Tienda/equipacion/<?= $eq->id ?>">
                        <div class="producto-img" style="background-image:url('<?= $eq->getImagenUrl() ?>'); height:160px;"></div>
                    </a>
                </div>
                <div class="p-3">
                    <h6 class="fw-semibold mb-1"><?= htmlspecialchars($eq->equipo_nombre ?? 'Equipación') ?> — <?= $eq->getVersionLabel() ?></h6>
                    <div class="fw-bold mb-2" style="color:var(--rosa);"><?= $eq->getPrecioFormateado() ?></div>
                    <a href="<?= APP_URL ?>Tienda/equipacion/<?= $eq->id ?>" class="btn-rosa w-100 d-block text-center">Ver</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
