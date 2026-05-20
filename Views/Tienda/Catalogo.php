<?php
// Helper descuento — calcula si aplica y el precio con descuento
if (!function_exists('calcDesc')) {
    function calcDesc(object $p, ?array $d): array {
        if (empty($d)) return ['aplica' => false, 'pct' => 0, 'precio' => null];
        $aplica = $d['aplica_a'] === 'todo' ||
                  ($d['aplica_a'] === 'categoria' && (int)$p->categoria_id === (int)$d['categoria_id']);
        if (!$aplica) return ['aplica' => false, 'pct' => 0, 'precio' => null];
        return [
            'aplica' => true,
            'pct'    => (float)$d['porcentaje'],
            'precio' => round((float)$p->precio_base * (1 - (float)$d['porcentaje'] / 100), 2),
        ];
    }
}
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-box-open me-2" style="color:#F5A800;"></i>Catálogo
        </h3>
        <small class="text-muted" id="contadorProductos">
            <?= count($productos) ?> producto<?= count($productos) !== 1 ? 's' : '' ?>
        </small>
    </div>

    <?php if (!empty($descuentoActivo)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 py-2">
        <i class="fas fa-tag fa-lg"></i>
        <div>
            <strong><?= $descuentoActivo['porcentaje'] ?>% de descuento</strong>
            <?php if ($descuentoActivo['aplica_a'] === 'categoria'): ?>
            en la categoría <strong><?= htmlspecialchars($descuentoActivo['categoria_nombre'] ?? '') ?></strong>
            <?php else: ?>
            en toda la tienda
            <?php endif; ?>
            — hasta <strong><?= date('d/m/Y', strtotime($descuentoActivo['fecha_fin'])) ?></strong>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex gap-2 flex-wrap mb-4">
        <a href="<?= APP_URL ?>Tienda/catalogo"
           class="chip-categoria <?= $categoriaId === 0 ? 'activo' : '' ?>">
            <i class="fas fa-th me-1"></i>Todos
        </a>
        <?php foreach ($categorias as $cat): ?>
        <?php if ($cat->activo): ?>
        <a href="<?= APP_URL ?>Tienda/catalogo/<?= $cat->id ?>"
           class="chip-categoria <?= $categoriaId === $cat->id ? 'activo' : '' ?>">
            <?= htmlspecialchars($cat->nombre) ?>
        </a>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="mb-4">
        <div class="input-group" style="max-width:400px;">
            <span class="input-group-text bg-white">
                <i class="fas fa-search text-muted"></i>
            </span>
            <input type="text" class="form-control border-start-0"
                   id="buscarProducto" placeholder="Buscar producto...">
        </div>
    </div>

    <?php if (empty($productos)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-box-open fa-3x mb-3 d-block" style="opacity:0.3;"></i>
        No hay productos en esta categoría.
    </div>
    <?php else: ?>
    <div class="row g-3" id="gridProductos">
        <?php foreach ($productos as $p):
            $desc = calcDesc($p, $descuentoActivo ?? null);
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 producto-item"
             data-nombre="<?= strtolower(htmlspecialchars($p->nombre)) ?>">
            <div class="producto-card h-100 d-flex flex-column">

                <!-- Imagen + botón favorito + badge descuento -->
                <div style="position:relative;">
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>-<?= slugify($p->nombre) ?>">
                        <div class="producto-img"
                             style="background-image:url('<?= $p->getImageUrl() ?>');">
                        </div>
                    </a>

                    <!-- Badge descuento — esquina superior izquierda -->
                    <?php if ($desc['aplica']): ?>
                    <span style="position:absolute; top:8px; left:8px;
                                 background:#dc3545; color:#fff;
                                 padding:3px 8px; border-radius:20px;
                                 font-size:0.72rem; font-weight:700; z-index:2;">
                        -<?= $desc['pct'] ?>% OFF
                    </span>
                    <?php endif; ?>

                    <!-- Botón favorito — esquina superior derecha -->
                    <button type="button"
                            class="btn-favorito"
                            data-id="<?= $p->id ?>"
                            style="position:absolute; top:8px; right:8px; z-index:2;
                                background:rgba(255,255,255,0.9);
                                border:2px solid <?= in_array($p->id, $favoritosIds) ? '#F5A800' : '#F0E2BC' ?>;
                                border-radius:50%; width:36px; height:36px;
                                display:flex; align-items:center; justify-content:center;
                                cursor:pointer; transition:all 0.2s;
                                box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                        <i class="fas fa-heart" style="color:<?= in_array($p->id, $favoritosIds) ? '#F5A800' : '#ccc' ?>; font-size:0.85rem;"></i>
                    </button>
                </div>

                <div class="p-3 flex-fill d-flex flex-column">
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>-<?= slugify($p->nombre) ?>"
                       style="text-decoration:none; color:inherit;">
                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($p->nombre) ?></h6>
                    </a>
                    <?php if ($p->descripcion): ?>
                    <small class="text-muted mb-2" style="font-size:0.8rem;">
                        <?= htmlspecialchars(substr($p->descripcion, 0, 60)) ?>...
                    </small>
                    <?php endif; ?>

                    <div class="fw-bold mb-3 mt-auto" style="color:#F5A800;">
                        <?php if ($p->tieneVariantes()): ?>
                            <small class="text-muted fw-normal">Desde</small>
                            L. <?= number_format((float)$p->precio_base, 2) ?>
                        <?php elseif ($desc['aplica']): ?>
                            <span class="text-decoration-line-through text-muted fw-normal" style="font-size:0.82rem;">
                                L. <?= number_format((float)$p->precio_base, 2) ?>
                            </span>
                            <span class="ms-1">L. <?= number_format($desc['precio'], 2) ?></span>
                        <?php else: ?>
                            L. <?= number_format((float)$p->precio_base, 2) ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($p->tieneVariantes()): ?>
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>-<?= slugify($p->nombre) ?>"
                       class="btn-rosa d-block text-center text-decoration-none">
                        <i class="fas fa-eye me-1"></i>Ver opciones
                    </a>
                    <?php elseif ($p->stock > 0): ?>
                    <button type="button" class="btn-rosa w-100"
                            onclick="agregarAlCarritoConStock(
                                <?= $p->id ?>, 0,
                                '<?= addslashes(htmlspecialchars($p->nombre)) ?>',
                                <?= $desc['aplica'] ? $desc['precio'] : $p->precio_base ?>,
                                '<?= $p->getImageUrl() ?>')">
                        <i class="fas fa-cart-plus me-1"></i>Agregar al carrito
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn-rosa w-100" disabled
                            style="opacity:0.5; cursor:not-allowed;">
                        <i class="fas fa-ban me-1"></i>No disponible
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

    // ── Buscador ─────────────────────────────────
    const buscar   = document.getElementById('buscarProducto');
    const items    = document.querySelectorAll('.producto-item');
    const contador = document.getElementById('contadorProductos');

    buscar?.addEventListener('input', function () {
        const texto = this.value.toLowerCase();
        let visible = 0;
        items.forEach(item => {
            const nombre = item.dataset.nombre || '';
            if (nombre.includes(texto)) { item.style.display = ''; visible++; }
            else                        { item.style.display = 'none'; }
        });
        contador.textContent = `${visible} producto${visible !== 1 ? 's' : ''}`;
    });

    // ── Favoritos ─────────────────────────────────
    document.querySelectorAll('.btn-favorito').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            <?php if (empty($_SESSION['cliente'])): ?>
            window.location.href = '<?= APP_URL ?>Tienda/login';
            return;
            <?php endif; ?>

            const productoId = this.dataset.id;
            const icon       = this.querySelector('i');
            const self       = this;

            fetch('<?= APP_URL ?>Tienda/toggleFavorito', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `producto_id=${productoId}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.error === 'no_auth') {
                    window.location.href = '<?= APP_URL ?>Tienda/login';
                    return;
                }
                if (data.liked) {
                    icon.style.color   = '#F5A800';
                    self.style.borderColor = '#F5A800';
                    self.style.boxShadow   = '0 2px 8px rgba(245,168,0,0.4)';
                } else {
                    icon.style.color   = '#ccc';
                    self.style.borderColor = '#F0E2BC';
                    self.style.boxShadow   = '0 2px 6px rgba(0,0,0,0.1)';
                }
            })
            .catch(() => {});
        });
    });

});
</script>