<div class="container py-5">
    <div class="d-flex align-items-start justify-content-between mb-4">
        <h2 class="fw-bold mb-0"><?= htmlspecialchars($producto->nombre) ?></h2>
        <button type="button"
                class="btn-favorito ms-3"
                data-id="<?= $producto->id ?>"
                title="Agregar a favoritos"
                style="background:rgba(255,255,255,0.9); border:2px solid #F0E2BC;
                       border-radius:50%; width:42px; height:42px; flex-shrink:0;
                       display:flex; align-items:center; justify-content:center;
                       cursor:pointer; transition:all 0.2s; font-size:1.1rem;">
            <i class="fas fa-heart" style="color:#ccc;"></i>
        </button>
    </div>

    <div class="row g-4">

        <!-- Imagen principal + miniaturas -->
        <div class="col-12 col-md-5">

            <!-- Imagen grande -->
            <div id="imagenProducto"
                 style="
                    height:360px;
                    background-image: url('<?= $producto->getImageUrl() ?>');
                    background-size: contain;
                    background-position: center;
                    background-repeat: no-repeat;
                    background-color: #FFFBF2;
                    border-radius: 16px;
                    border: 1px solid #F0E2BC;
                    transition: all 0.3s ease;">
            </div>

            <!-- Miniaturas — imagen principal + variantes con imagen -->
            <?php
            // Construir lista de miniaturas: primero la imagen del producto, luego variantes con imagen
            $miniaturas = [];
            $miniaturas[] = [
                'url'    => $producto->getImageUrl(),
                'titulo' => $producto->nombre,
                'tipo'   => 'principal',
            ];
            if (!empty($variantes)) {
                foreach ($variantes as $v) {
                    if ($v->activo && !empty($v->image_url)) {
                        $miniaturas[] = [
                            'url'       => $v->getImageUrl(),
                            'titulo'    => $v->nombre,
                            'tipo'      => 'variante',
                            'variante_id' => $v->id,
                        ];
                    }
                }
            }
            ?>

            <?php if (count($miniaturas) > 1): ?>
            <div class="d-flex gap-2 mt-3 flex-wrap" id="galeriaMiniatura">
                <?php foreach ($miniaturas as $idx => $mini): ?>
                <div class="miniatura-item <?= $idx === 0 ? 'activa' : '' ?>"
                     data-url="<?= htmlspecialchars($mini['url']) ?>"
                     data-tipo="<?= $mini['tipo'] ?>"
                     data-variante-id="<?= $mini['variante_id'] ?? '' ?>"
                     title="<?= htmlspecialchars($mini['titulo']) ?>"
                     style="
                        width:64px; height:64px; flex-shrink:0;
                        background-image: url('<?= htmlspecialchars($mini['url']) ?>');
                        background-size: contain;
                        background-position: center;
                        background-repeat: no-repeat;
                        background-color: #FFFBF2;
                        border-radius: 8px;
                        border: 2px solid <?= $idx === 0 ? '#F5A800' : '#F0E2BC' ?>;
                        cursor: pointer;
                        transition: border-color 0.2s, transform 0.2s;">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>

        <!-- Info -->
        <div class="col-12 col-md-7">
            <h2 class="fw-bold mb-2"><?= htmlspecialchars($producto->nombre) ?></h2>

            <?php if ($producto->descripcion): ?>
            <p class="text-muted mb-3"><?= htmlspecialchars($producto->descripcion) ?></p>
            <?php endif; ?>

            <!-- Precio -->
            <div class="mb-4">
                <span class="fw-bold" style="font-size:1.8rem; color:#F5A800;" id="precioMostrado">
                    L. <?= number_format((float)$producto->precio_base, 2) ?>
                </span>
            </div>

            <!-- Variantes -->
            <?php if (!empty($variantes)): ?>
            <div class="mb-4">
                <label class="form-label fw-semibold">Selecciona una opción:</label>
                <div class="d-flex gap-2 flex-wrap" id="contenedorVariantes">
                    <?php foreach ($variantes as $v): ?>
                    <?php if ($v->activo && $v->stock > 0): ?>
                    <button type="button"
                            class="btn-variante"
                            data-id="<?= $v->id ?>"
                            data-nombre="<?= htmlspecialchars($v->nombre) ?>"
                            data-precio="<?= $v->precio ?? $producto->precio_base ?>"
                            data-imagen="<?= htmlspecialchars($v->getImageUrl()) ?>"
                            style="padding:8px 16px; border:2px solid #dee2e6; border-radius:8px;
                                   background:#fff; cursor:pointer; font-weight:500; transition:all 0.2s;">
                        <?= htmlspecialchars($v->nombre) ?>
                        <small style="color:#F5A800; display:block; font-size:0.75rem;">
                            L. <?= number_format((float)($v->precio ?? $producto->precio_base), 2) ?>
                        </small>
                    </button>
                    <?php else: ?>
                    <button type="button" disabled
                            style="padding:8px 16px; border:2px solid #eee; border-radius:8px;
                                   background:#f8f8f8; cursor:not-allowed; color:#aaa; font-weight:500; opacity:0.5;">
                        <?= htmlspecialchars($v->nombre) ?>
                        <small style="display:block; font-size:0.75rem;">No disponible</small>
                    </button>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Disponibilidad -->
            <?php if (!$producto->tieneVariantes()): ?>
            <div class="mb-3">
                <?php if ($producto->stock > 0): ?>
                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Disponible</span>
                <?php else: ?>
                <span class="badge bg-secondary"><i class="fas fa-times me-1"></i>No disponible</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Botones cantidad + carrito -->
            <?php
            $sinStock = false;
            if (!$producto->tieneVariantes() && $producto->stock == 0) $sinStock = true;
            if ($producto->tieneVariantes()) {
                $hayVarianteDisponible = false;
                foreach ($variantes as $v) {
                    if ($v->activo && $v->stock > 0) { $hayVarianteDisponible = true; break; }
                }
                if (!$hayVarianteDisponible) $sinStock = true;
            }
            ?>
            <div class="d-flex gap-2 mt-4">
                <?php if (!$sinStock): ?>
                <div class="d-flex align-items-center gap-2 me-2">
                    <button type="button" id="btnMenos"
                            style="width:36px;height:36px;border-radius:50%;border:2px solid #F5A800;
                                   background:#fff;color:#F5A800;font-size:1.2rem;cursor:pointer;">−</button>
                    <span id="cantidad" style="font-size:1.1rem;font-weight:700;min-width:30px;text-align:center;">1</span>
                    <button type="button" id="btnMas"
                            style="width:36px;height:36px;border-radius:50%;border:2px solid #F5A800;
                                   background:#fff;color:#F5A800;font-size:1.2rem;cursor:pointer;">+</button>
                </div>
                <button type="button" class="btn-rosa flex-fill" id="btnAgregarCarrito">
                    <i class="fas fa-cart-plus me-2"></i>Agregar al carrito
                </button>
                <?php else: ?>
                <button type="button" class="btn-rosa flex-fill" disabled style="opacity:0.5;cursor:not-allowed;">
                    <i class="fas fa-ban me-2"></i>No disponible
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.miniatura-item:hover {
    border-color: #F5A800 !important;
    transform: scale(1.08);
}
.miniatura-item.activa {
    border-color: #F5A800 !important;
    box-shadow: 0 0 0 2px rgba(245,168,0,0.3);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let varianteSeleccionada = null;
    let cantidad = 1;

    const imgDiv         = document.getElementById('imagenProducto');
    const imagenOriginal = <?= json_encode($producto->getImageUrl()) ?>;

    // ── Función para cambiar imagen principal ────────────────
    function cambiarImagenPrincipal(url) {
        imgDiv.style.backgroundImage = `url("${url}")`;
    }

    // ── Función para marcar miniatura activa ─────────────────
    function activarMiniatura(el) {
        document.querySelectorAll('.miniatura-item').forEach(m => {
            m.classList.remove('activa');
            m.style.borderColor = '#F0E2BC';
        });
        if (el) {
            el.classList.add('activa');
            el.style.borderColor = '#F5A800';
        }
    }

    // ── Click en miniatura ───────────────────────────────────
    document.querySelectorAll('.miniatura-item').forEach(mini => {
        mini.addEventListener('click', function () {
            const url        = this.dataset.url;
            const varianteId = this.dataset.varianteId;

            cambiarImagenPrincipal(url);
            activarMiniatura(this);

            // Si la miniatura corresponde a una variante, sincronizar el botón
            if (varianteId) {
                const btnVariante = document.querySelector(`.btn-variante[data-id="${varianteId}"]`);
                if (btnVariante) {
                    // Simular click en el botón de variante para sincronizar precio
                    document.querySelectorAll('.btn-variante').forEach(b => {
                        b.style.borderColor = '#dee2e6';
                        b.style.background  = '#fff';
                        b.style.color       = '#333';
                    });
                    btnVariante.style.borderColor = '#F5A800';
                    btnVariante.style.background  = '#F5A800';
                    btnVariante.style.color       = '#fff';
                    varianteSeleccionada = {
                        id:     btnVariante.dataset.id,
                        nombre: btnVariante.dataset.nombre,
                        precio: parseFloat(btnVariante.dataset.precio),
                    };
                    document.getElementById('precioMostrado').textContent =
                        'L. ' + varianteSeleccionada.precio.toFixed(2);
                }
            } else {
                // Es la imagen principal — deseleccionar variante
                document.querySelectorAll('.btn-variante').forEach(b => {
                    b.style.borderColor = '#dee2e6';
                    b.style.background  = '#fff';
                    b.style.color       = '#333';
                });
                varianteSeleccionada = null;
                document.getElementById('precioMostrado').textContent =
                    'L. ' + (<?= json_encode((float)($producto->precio_base ?? 0)) ?>).toFixed(2);
            }
        });
    });

    // ── Click en botón de variante ───────────────────────────
    document.querySelectorAll('.btn-variante').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-variante').forEach(b => {
                b.style.borderColor = '#dee2e6';
                b.style.background  = '#fff';
                b.style.color       = '#333';
            });
            this.style.borderColor = '#F5A800';
            this.style.background  = '#F5A800';
            this.style.color       = '#fff';

            varianteSeleccionada = {
                id:     this.dataset.id,
                nombre: this.dataset.nombre,
                precio: parseFloat(this.dataset.precio),
            };

            document.getElementById('precioMostrado').textContent =
                'L. ' + varianteSeleccionada.precio.toFixed(2);

            // Cambiar imagen principal
            const imagenVariante = this.dataset.imagen;
            const urlFinal = (imagenVariante && !imagenVariante.includes('default'))
                ? imagenVariante : imagenOriginal;
            cambiarImagenPrincipal(urlFinal);

            // Sincronizar miniatura correspondiente
            const miniatura = document.querySelector(
                `.miniatura-item[data-variante-id="${this.dataset.id}"]`
            );
            activarMiniatura(miniatura);
        });
    });

    // ── Cantidad ─────────────────────────────────────────────
    const btnMenos = document.getElementById('btnMenos');
    const btnMas   = document.getElementById('btnMas');
    if (btnMenos) btnMenos.addEventListener('click', () => {
        if (cantidad > 1) { cantidad--; document.getElementById('cantidad').textContent = cantidad; }
    });
    if (btnMas) btnMas.addEventListener('click', () => {
        cantidad++; document.getElementById('cantidad').textContent = cantidad;
    });

    // ── Agregar al carrito ────────────────────────────────────
    const btnAgregar = document.getElementById('btnAgregarCarrito');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function () {
            const tieneVariantes = <?= $producto->tieneVariantes() ? 'true' : 'false' ?>;
            if (tieneVariantes && !varianteSeleccionada) {
                Swal.fire({
                    icon: 'warning', title: 'Selecciona una opción',
                    text: 'Elige una variante antes de agregar al carrito.',
                    confirmButtonColor: '#F5A800'
                });
                return;
            }
            const precio         = varianteSeleccionada ? varianteSeleccionada.precio : <?= json_encode((float)($producto->precio_base ?? 0)) ?>;
            const varianteId     = varianteSeleccionada ? varianteSeleccionada.id     : null;
            const varianteNombre = varianteSeleccionada ? varianteSeleccionada.nombre : null;
            const bgImg          = imgDiv.style.backgroundImage;
            const imgActual      = bgImg
                ? bgImg.replace(/^url\(["']?/, '').replace(/["']?\)$/, '')
                : imagenOriginal;

            for (let i = 0; i < cantidad; i++) {
                agregarAlCarrito(
                    <?= json_encode((int)$producto->id) ?>,
                    <?= json_encode($producto->nombre) ?>,
                    precio,
                    imgActual || imagenOriginal,
                    varianteId,
                    varianteNombre
                );
            }
        });
    }
});
</script>