<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-heart me-2" style="color:#F5A800;"></i>Mis favoritos
        </h3>
        <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa-outline">
            <i class="fas fa-box-open me-1"></i>Ver catálogo
        </a>
    </div>

    <?php if (empty($favoritos)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-heart fa-3x mb-3 d-block" style="opacity:0.2; color:#F5A800;"></i>
        <h5>Aún no tienes favoritos</h5>
        <p style="font-size:0.9rem;">Guarda los productos que más te gusten para encontrarlos rápido.</p>
        <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa mt-2 d-inline-block px-4 py-2">
            Explorar productos
        </a>
    </div>
    <?php else: ?>
    <div class="row g-3" id="gridFavoritos">
        <?php foreach ($favoritos as $p): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3" data-producto-id="<?= $p['id'] ?>">
            <div class="producto-card h-100 d-flex flex-column">
                <div style="position:relative;">
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p['id'] ?>-<?= slugify($p['nombre']) ?>">
                        <div class="producto-img"
                             style="background-image:url('<?= APP_URL ?>Content/Demo/img/Productos/<?= htmlspecialchars($p['image_url'] ?? '') ?>');">
                        </div>
                    </a>
                    <!-- Clase btn-quitar-favorito (distinta de btn-favorito del template) -->
                    <button type="button"
                            class="btn-quitar-favorito"
                            data-id="<?= $p['id'] ?>"
                            data-url="<?= APP_URL ?>Tienda/toggleFavorito"
                            title="Quitar de favoritos"
                            style="position:absolute; top:8px; right:8px;
                                   background:rgba(255,255,255,0.9); border:none;
                                   border-radius:50%; width:34px; height:34px;
                                   display:flex; align-items:center; justify-content:center;
                                   cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,0.15);
                                   transition:all 0.2s; font-size:1rem;">
                        <i class="fas fa-heart" style="color:#F5A800;"></i>
                    </button>
                </div>
                <div class="p-3 flex-fill d-flex flex-column">
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p['id'] ?>-<?= slugify($p['nombre']) ?>"
                       style="text-decoration:none; color:inherit;">
                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($p['nombre']) ?></h6>
                    </a>
                    <small class="text-muted mb-2" style="font-size:0.8rem;">
                        <?= htmlspecialchars($p['categoria_nombre']) ?>
                    </small>
                    <div class="fw-bold mb-3 mt-auto" style="color:#F5A800;">
                        L. <?= number_format((float)$p['precio_base'], 2) ?>
                    </div>
                    <?php if ($p['tiene_variantes']): ?>
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p['id'] ?>-<?= slugify($p['nombre']) ?>"
                       class="btn-rosa d-block text-center text-decoration-none">
                        <i class="fas fa-eye me-1"></i>Ver opciones
                    </a>
                    <?php elseif ($p['stock'] > 0): ?>
                    <button type="button"
                            class="btn-rosa w-100"
                            onclick="agregarAlCarrito(
                                <?= $p['id'] ?>,
                                '<?= addslashes(htmlspecialchars($p['nombre'])) ?>',
                                <?= $p['precio_base'] ?>,
                                '<?= APP_URL ?>Content/Demo/img/Productos/<?= htmlspecialchars($p['image_url'] ?? '') ?>',
                                null, null)">
                        <i class="fas fa-cart-plus me-1"></i>Agregar
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn-rosa w-100" disabled
                            style="opacity:0.5; cursor:not-allowed; background:#aaa; border:none;">
                        <i class="fas fa-times-circle me-1"></i>Agotado
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Clase específica para esta vista — no colisiona con btn-favorito del template
    document.querySelectorAll('.btn-quitar-favorito').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const productoId = this.dataset.id;
            const url        = this.dataset.url;
            const card       = this.closest('[data-producto-id]');
            const self       = this;

            self.style.opacity = '0.5';
            self.disabled      = true;

            fetch(url, {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    `producto_id=${productoId}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.error === 'no_auth') {
                    window.location.href = '<?= APP_URL ?>Tienda/login';
                    return;
                }

                if (card) {
                    card.style.transition = 'opacity 0.3s, transform 0.3s';
                    card.style.opacity    = '0';
                    card.style.transform  = 'scale(0.9)';
                    setTimeout(function () {
                        card.remove();
                        if (!document.querySelector('[data-producto-id]')) {
                            location.reload();
                        }
                    }, 300);
                }
            })
            .catch(function () {
                self.style.opacity = '1';
                self.disabled      = false;
            });
        });
    });

});
</script>