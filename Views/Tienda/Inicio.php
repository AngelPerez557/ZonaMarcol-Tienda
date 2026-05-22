<?php
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

<!-- ─── ALERTA DESCUENTO ACTIVO ──────────────── -->
<?php if (!empty($descuentoActivo)): ?>
<div style="background:linear-gradient(135deg,#dc3545,#c0392b);color:#fff;text-align:center;padding:10px 16px;font-size:0.9rem;font-weight:600;">
    <i class="fas fa-tag me-2"></i>
    <?= $descuentoActivo['aplica_a'] === 'todo'
        ? "¡{$descuentoActivo['porcentaje']}% de descuento en toda la tienda! Válido hasta " . date('d/m/Y', strtotime($descuentoActivo['fecha_fin']))
        : "¡{$descuentoActivo['porcentaje']}% de descuento en " . htmlspecialchars($descuentoActivo['categoria_nombre'] ?? '') . "! Válido hasta " . date('d/m/Y', strtotime($descuentoActivo['fecha_fin']))
    ?>
</div>
<?php endif; ?>

<!-- ─── SLIDER DE BANNERS ─────────────────────── -->
<?php if (!empty($banners)): ?>
<div id="carouselBanners" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php foreach ($banners as $i => $b): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
            <div style="height:clamp(220px,45vw,420px);background-image:url('<?= APP_URL ?>Content/Demo/img/Banners/<?= htmlspecialchars($b['imagen_url']) ?>');background-size:cover;background-position:center;">
                <?php if ($b['titulo']): ?>
                <div class="carousel-caption d-none d-md-block">
                    <h2 class="fw-bold"><?= htmlspecialchars($b['titulo']) ?></h2>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($banners) > 1): ?>
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselBanners" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselBanners" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
    <?php endif; ?>
</div>
<?php else: ?>
<div style="background:linear-gradient(135deg,#FFF1C8 0%,#FFFBF2 100%);padding:clamp(40px,8vw,80px) 0;text-align:center;">
    <div class="container">
        <h1 class="fw-bold mb-3" style="color:#F5A800;">Zona Marcol</h1>
        <p class="text-muted mb-4">Cosméticos masculinos, tecnología y servicio técnico</p>
        <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa px-4 py-2" style="border-radius:25px;font-size:1rem;">Ver catálogo</a>
    </div>
</div>
<?php endif; ?>

<!-- ─── CATEGORÍAS ────────────────────────────── -->
<div class="container my-5">
    <h3 class="fw-bold mb-4 text-center">Categorías</h3>
    <div class="d-flex gap-2 flex-wrap justify-content-center">
        <a href="<?= APP_URL ?>Tienda/catalogo" class="chip-categoria">
            <i class="fas fa-th me-1"></i>Todos
        </a>
        <?php foreach ($categorias as $cat): ?>
        <?php if ($cat->activo): ?>
        <a href="<?= APP_URL ?>Tienda/catalogo/<?= $cat->id ?>" class="chip-categoria">
            <?= htmlspecialchars($cat->nombre) ?>
        </a>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- ─── PRODUCTOS DESTACADOS ─────────────────── -->
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Productos destacados</h3>
        <a href="<?= APP_URL ?>Tienda/catalogo" class="btn-rosa-outline">
            Ver todos <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>
    <?php if (empty($productosDestacados)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-box-open fa-3x mb-3 d-block" style="opacity:0.3;"></i>
        Próximamente nuevos productos.
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($productosDestacados as $p):
            $desc = calcDesc($p, $descuentoActivo ?? null);
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="producto-card">
                <div style="position:relative;">
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>-<?= slugify($p->nombre) ?>">
                        <div class="producto-img" style="background-image:url('<?= $p->getImageUrl() ?>');"></div>
                    </a>

                    <?php if ($desc['aplica']): ?>
                    <span style="position:absolute;top:8px;left:8px;background:#dc3545;color:#fff;padding:3px 8px;border-radius:20px;font-size:0.72rem;font-weight:700;z-index:2;">
                        -<?= $desc['pct'] ?>% OFF
                    </span>
                    <?php endif; ?>

                    <!-- Botón favorito -->
                    <button type="button"
                            class="btn-favorito"
                            data-id="<?= $p->id ?>"
                            title="Agregar a favoritos"
                            style="position:absolute;top:8px;right:8px;
                                   background:rgba(255,255,255,0.9);border:2px solid #F0E2BC;
                                   border-radius:50%;width:34px;height:34px;
                                   display:flex;align-items:center;justify-content:center;
                                   cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,0.12);
                                   transition:all 0.2s;font-size:0.9rem;z-index:2;">
                        <i class="fas fa-heart" style="color:#ccc;"></i>
                    </button>
                </div>
                <div class="p-3">
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>-<?= slugify($p->nombre) ?>" style="text-decoration:none;color:inherit;">
                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($p->nombre) ?></h6>
                    </a>
                    <div class="fw-bold mb-2" style="color:#F5A800;">
                        <?php if ($p->tieneVariantes()): ?>
                            <small class="text-muted">Desde</small> L. <?= number_format((float)$p->precio_base, 2) ?>
                        <?php elseif ($desc['aplica']): ?>
                            <span class="text-decoration-line-through text-muted fw-normal" style="font-size:0.82rem;">L. <?= number_format((float)$p->precio_base, 2) ?></span>
                            <span class="ms-1">L. <?= number_format($desc['precio'], 2) ?></span>
                        <?php else: ?>
                            L. <?= number_format((float)$p->precio_base, 2) ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($p->tieneVariantes()): ?>
                    <a href="<?= APP_URL ?>Tienda/producto/<?= $p->id ?>-<?= slugify($p->nombre) ?>"
                       class="btn-rosa w-100 d-block text-center" style="border-radius:8px;padding:8px;">
                        <i class="fas fa-eye me-1"></i>Ver opciones
                    </a>
                    <?php elseif ((int)$p->stock <= 0): ?>
                    <button type="button" class="btn-rosa w-100" disabled style="opacity:0.5;cursor:not-allowed;background:#aaa;border:none;">
                        <i class="fas fa-times-circle me-1"></i>Agotado
                    </button>
                    <?php else: ?>
                    <button type="button" class="btn-rosa w-100"
                            onclick="agregarAlCarrito(<?= $p->id ?>,'<?= addslashes(htmlspecialchars($p->nombre)) ?>',<?= $desc['aplica'] ? $desc['precio'] : $p->precio_base ?>,'<?= $p->getImageUrl() ?>',null,null)">
                        <i class="fas fa-cart-plus me-1"></i>Agregar
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ─── CTA TIENDA + SERVICIO TÉCNICO ─────────── -->
<div class="container my-5">
    <div class="card border-0 text-center p-5"
         style="background:linear-gradient(135deg,#F5A800 0%,#C58800 100%);border-radius:16px;">
        <h3 class="fw-bold text-white mb-3">Tu zona de tecnología y mantenimiento</h3>
        <p class="text-white mb-4" style="opacity:0.9;">
            Productos masculinos, accesorios tecnológicos y servicio técnico especializado para tus consolas y PC.
        </p>
        <a href="<?= APP_URL ?>Tienda/catalogo"
           style="background:#fff;color:#3D3D3D;font-weight:700;padding:12px 32px;border-radius:25px;text-decoration:none;display:inline-block;">
            <i class="fas fa-store me-2"></i>Ver catálogo
        </a>
    </div>
</div>

<!-- ─── SERVICIOS TÉCNICOS (SI HAY) ───────────── -->
<?php if (!empty($servicios)): ?>
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Servicios técnicos</h3>
        <a href="<?= APP_URL ?>Servicios/index" class="btn-rosa-outline">Ver todos</a>
    </div>
    <div class="row g-3">
        <?php foreach ($servicios as $s): ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="producto-card p-3" style="background:#fff8ed;">
                <h5 class="mb-2 fw-semibold"><?= htmlspecialchars($s->nombre) ?></h5>
                <p class="text-muted mb-2" style="font-size:0.9rem;"><?= nl2br(htmlspecialchars($s->descripcion)) ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-bold" style="color:var(--rosa);">L. <?= number_format((float)$s->precio,2) ?></div>
                    <a href="<?= APP_URL ?>Servicios/registry/<?= $s->id ?>" class="btn-rosa-outline">Ver</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ─── CAMISETAS / EQUIPACIONES (SI HAY) ─────── -->
<?php if (!empty($equipaciones)): ?>
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Camisetas y equipaciones</h3>
        <a href="<?= APP_URL ?>Equipaciones/index" class="btn-rosa-outline">Ver catálogo</a>
    </div>
    <div class="row g-3">
        <?php foreach ($equipaciones as $eq): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="producto-card">
                <div style="position:relative;">
                    <a href="<?= APP_URL ?>Equipaciones/registry/<?= $eq->id ?>">
                        <div class="producto-img" style="background-image:url('<?= $eq->getImagenUrl() ?>'); height:160px;"></div>
                    </a>
                </div>
                <div class="p-3">
                    <h6 class="fw-semibold mb-1"><?= htmlspecialchars($eq->equipo_nombre ?? 'Equipación') ?> — <?= $eq->getVersionLabel() ?></h6>
                    <div class="fw-bold mb-2" style="color:var(--rosa);"><?= $eq->getPrecioFormateado() ?></div>
                    <a href="<?= APP_URL ?>Equipaciones/registry/<?= $eq->id ?>" class="btn-rosa w-100 d-block text-center">Ver</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>