<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $producto->Found ? 'edit' : 'plus-circle' ?> me-2"
                   style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= $producto->Found ? 'Modifica los datos del producto.' : 'Completa el formulario para crear un nuevo producto.' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Productos/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row g-4">

        <!-- ─────────────────────────────────────────────
             COLUMNA IZQUIERDA — Datos del producto
             ───────────────────────────────────────────── -->
        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-box-open me-2"></i>Datos del producto
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Productos/save"
                          enctype="multipart/form-data"
                          autocomplete="off"
                          id="formProducto">

                        <!-- CSRF -->
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <!-- ID oculto en edición -->
                        <?php if ($producto->Found): ?>
                        <input type="hidden" name="id" value="<?= $producto->id ?>">
                        <?php endif; ?>

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="nombre"
                                   name="nombre"
                                   maxlength="150"
                                   placeholder="Nombre del producto"
                                   value="<?= htmlspecialchars($producto->nombre ?? '') ?>"
                                   required
                                   autofocus>
                        </div>

                        <!-- Categoría -->
                        <div class="mb-3">
                            <label for="categoria_id" class="form-label fw-semibold">
                                Categoría <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="categoria_id" name="categoria_id" required>
                                <option value="">Seleccionar categoría...</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat->id ?>"
                                        <?= (int)($producto->categoria_id ?? 0) === $cat->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat->nombre) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label fw-semibold">
                                Descripción
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <textarea class="form-control"
                                      id="descripcion"
                                      name="descripcion"
                                      rows="3"
                                      placeholder="Descripción del producto..."><?= htmlspecialchars($producto->descripcion ?? '') ?></textarea>
                        </div>

                        <!-- ¿Tiene variantes? -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       role="switch"
                                       id="tiene_variantes"
                                       name="tiene_variantes"
                                       <?= $producto->tieneVariantes() ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="tiene_variantes">
                                    Este producto tiene variantes
                                    <small class="text-muted fw-normal d-block">
                                        Ej: colores, tallas, presentaciones con stock independiente
                                    </small>
                                </label>
                            </div>
                        </div>
                        <!-- Visible en tienda -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="visible_tienda"
                                    name="visible_tienda"
                                    <?= $producto->isVisibleTienda() ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="visible_tienda">
                                    Mostrar en tienda en línea
                                    <small class="text-muted fw-normal d-block">
                                        Desactiva si es un producto exclusivo para venta presencial
                                    </small>
                                </label>
                            </div>
                        </div>
                        <!-- ─────────────────────────────────────────────
                             SECCIÓN SIMPLE — se oculta si tiene variantes
                             ───────────────────────────────────────────── -->
                        <div id="seccionSimple" class="<?= $producto->tieneVariantes() ? 'd-none' : '' ?>">
                            <div class="row g-3 mb-3">
                                <!-- Precio base -->
                                <div class="col-6">
                                    <label for="precio_base" class="form-label fw-semibold">
                                        Precio base (L.)
                                    </label>
                                    <input type="number"
                                           class="form-control"
                                           id="precio_base"
                                           name="precio_base"
                                           step="0.01"
                                           min="0"
                                           placeholder="0.00"
                                           value="<?= $producto->precio_base ?? '' ?>">
                                </div>
                                <!-- Stock -->
                                <div class="col-6">
                                    <label for="stock" class="form-label fw-semibold">Stock</label>
                                    <input type="number"
                                           class="form-control"
                                           id="stock"
                                           name="stock"
                                           min="0"
                                           placeholder="0"
                                           value="<?= $producto->stock ?? 0 ?>">
                                </div>
                            </div>

                            <!-- Código de barras — producto simple -->
                            <div class="mb-3">
                                <label for="codigo_barras" class="form-label fw-semibold">
                                    Código de barras
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-barcode"></i>
                                    </span>
                                    <input type="text"
                                           class="form-control"
                                           id="codigo_barras"
                                           name="codigo_barras"
                                           placeholder="Escanear o ingresar manualmente..."
                                           maxlength="100"
                                           value="<?= htmlspecialchars($producto->codigo_barras ?? '') ?>">
                                    <!-- Botón scanner con cámara -->
                                    <button type="button"
                                            class="btn btn-outline-primary"
                                            onclick="window.amBarcodeScanner && window.amBarcodeScanner.open(document.getElementById('codigo_barras'))"
                                            title="Escanear con la cámara">
                                        <i class="fas fa-camera"></i>
                                        <span class="d-none d-md-inline ms-1">Escanear</span>
                                    </button>
                                </div>
                                <small class="text-muted">
                                    Conectá un lector USB, o usá el botón <i class="fas fa-camera"></i> para escanear con la cámara del dispositivo.
                                </small>
                            </div>
                        </div>

                        <!-- Imagen principal -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Imagen principal</label>
                            <div class="d-flex gap-3 align-items-start">
                                <div id="previewContainer">
                                    <img id="previewImagen"
                                         src="<?= $producto->getImageUrl() ?>"
                                         alt="Preview"
                                         style="width:100px; height:100px; object-fit:contain; border-radius:8px; border:2px solid #dee2e6; background:#FFFBF2;">
                                </div>
                                <div class="flex-fill">
                                    <input type="file"
                                           class="form-control"
                                           id="imagen"
                                           name="imagen"
                                           accept="image/jpeg,image/png,image/webp">
                                    <small class="text-muted">
                                        JPG, PNG o WEBP. Máximo 2MB.
                                        <?= $producto->Found ? 'Deja vacío para mantener la imagen actual.' : '' ?>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $producto->Found ? 'Guardar cambios' : 'Crear producto' ?>
                            </button>
                            <a href="<?= APP_URL ?>Productos/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- ─────────────────────────────────────────────
             COLUMNA DERECHA — Variantes
             Solo visible si el producto tiene variantes Y ya fue guardado
             ───────────────────────────────────────────── -->
        <div class="col-12 col-lg-5" id="columnaVariantes"
             style="<?= (!$producto->Found || !$producto->tieneVariantes()) ? 'display:none;' : '' ?>">

            <?php if ($producto->Found && $producto->tieneVariantes()): ?>

            <!-- Listado de variantes -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-layer-group me-2"></i>Variantes</span>
                    <span class="badge" style="background:#F5A800;">
                        <?= count($variantes) ?> variante<?= count($variantes) !== 1 ? 's' : '' ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($variantes)): ?>
                    <p class="text-muted text-center py-3 mb-0">
                        No hay variantes. Agrega la primera abajo.
                    </p>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($variantes as $v): ?>
                        <div class="list-group-item">
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= $v->getImageUrl() ?>"
                                     alt="<?= htmlspecialchars($v->nombre) ?>"
                                     style="width:48px; height:48px; object-fit:contain; border-radius:6px; background:#FFFBF2;">
                                <div class="flex-fill">
                                    <div class="fw-semibold"><?= htmlspecialchars($v->nombre) ?></div>
                                    <small class="text-muted">
                                        <?= $v->getPrecioFormateado() ?> &bull;
                                        Stock: <?= $v->stock ?>
                                        <?php if ($v->codigo_barras): ?>
                                        &bull; <i class="fas fa-barcode me-1"></i><?= htmlspecialchars($v->codigo_barras) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="d-flex gap-1">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary btn-edit-variante"
                                            data-variante='<?= json_encode([
                                                "id"            => $v->id,
                                                "nombre"        => $v->nombre,
                                                "precio"        => $v->precio,
                                                "stock"         => $v->stock,
                                                "codigo_barras" => $v->codigo_barras,
                                                "orden"         => $v->orden,
                                            ]) ?>'
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-delete-variante"
                                            data-id="<?= $v->id ?>"
                                            data-nombre="<?= htmlspecialchars($v->nombre) ?>"
                                            data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"
                                            title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Formulario nueva/editar variante -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus-circle me-2"></i>
                    <span id="tituloFormVariante">Nueva variante</span>
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Productos/saveVariante"
                          enctype="multipart/form-data"
                          id="formVariante">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="producto_id" value="<?= $producto->id ?>">
                        <input type="hidden" name="variante_id" id="varianteId" value="">

                        <div class="mb-3">
                            <label for="varNombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="varNombre" name="nombre"
                                   placeholder="Ej: Nude, Rojo, Coral..." maxlength="100" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="varPrecio" class="form-label fw-semibold">Precio (L.)</label>
                                <input type="number" class="form-control" id="varPrecio" name="precio"
                                       step="0.01" min="0" placeholder="Hereda precio base">
                                <small class="text-muted">Vacío = precio del producto</small>
                            </div>
                            <div class="col-6">
                                <label for="varStock" class="form-label fw-semibold">Stock</label>
                                <input type="number" class="form-control" id="varStock" name="stock"
                                       min="0" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="varBarras" class="form-label fw-semibold">Código de barras</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" class="form-control" id="varBarras"
                                       name="codigo_barras"
                                       placeholder="Escanear o ingresar manualmente..."
                                       maxlength="100">
                                <button type="button"
                                        class="btn btn-outline-primary"
                                        onclick="window.amBarcodeScanner && window.amBarcodeScanner.open(document.getElementById('varBarras'))"
                                        title="Escanear con la cámara">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="varImagen" class="form-label fw-semibold">Imagen de la variante</label>
                            <input type="file" class="form-control" id="varImagen" name="imagen"
                                   accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">JPG, PNG o WEBP. Máximo 2MB.</small>
                        </div>

                        <input type="hidden" id="varOrden" name="orden" value="<?= count($variantes) ?>">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <span id="btnVarianteTexto">Agregar variante</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary d-none"
                                    id="btnCancelarVariante">Cancelar</button>
                        </div>

                    </form>
                </div>
            </div>

            <?php endif; ?>

        </div>

        <!-- Mensaje informativo — solo en creación o cuando no tiene variantes -->
        <?php if (!$producto->Found): ?>
        <div class="col-12 col-lg-5" id="mensajeVariantes">
            <div class="card border-0" style="background:rgba(245,168,0,0.06);">
                <div class="card-body text-center py-4">
                    <i class="fas fa-info-circle fa-2x mb-3 d-block" style="color:#F5A800;"></i>
                    <p class="mb-0 text-muted">
                        Las variantes se podrán agregar después de crear el producto.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Toggle variantes — muestra/oculta secciones ──
    const toggleVariantes  = document.getElementById('tiene_variantes');
    const seccionSimple    = document.getElementById('seccionSimple');
    const columnaVariantes = document.getElementById('columnaVariantes');
    const mensajeVariantes = document.getElementById('mensajeVariantes');

    if (toggleVariantes) {
        toggleVariantes.addEventListener('change', function () {
            const tieneVariantes = this.checked;

            // Oculta/muestra la sección de precio, stock y código de barras
            seccionSimple.classList.toggle('d-none', tieneVariantes);

            // Muestra/oculta la columna de variantes (solo en edición)
            if (columnaVariantes) {
                columnaVariantes.style.display = tieneVariantes ? '' : 'none';
            }

            // Muestra/oculta el mensaje informativo (solo en creación)
            if (mensajeVariantes) {
                mensajeVariantes.style.display = tieneVariantes ? 'none' : '';
            }
        });
    }

    // ── Preview imagen producto ──────────────────
    const inputImagen   = document.getElementById('imagen');
    const previewImagen = document.getElementById('previewImagen');

    if (inputImagen && previewImagen) {
        inputImagen.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => previewImagen.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // ── Editar variante ──────────────────────────
    document.querySelectorAll('.btn-edit-variante').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const data = JSON.parse(this.dataset.variante);

            document.getElementById('varianteId').value = data.id;
            document.getElementById('varNombre').value  = data.nombre;
            document.getElementById('varPrecio').value  = data.precio ?? '';
            document.getElementById('varStock').value   = data.stock;
            document.getElementById('varBarras').value  = data.codigo_barras ?? '';
            document.getElementById('varOrden').value   = data.orden;

            document.getElementById('tituloFormVariante').textContent = 'Editar variante';
            document.getElementById('btnVarianteTexto').textContent   = 'Guardar cambios';
            document.getElementById('btnCancelarVariante').classList.remove('d-none');

            document.getElementById('formVariante').scrollIntoView({ behavior: 'smooth' });
        });
    });

    // ── Cancelar edición variante ────────────────
    const btnCancelar = document.getElementById('btnCancelarVariante');
    if (btnCancelar) {
        btnCancelar.addEventListener('click', function () {
            document.getElementById('varianteId').value = '';
            document.getElementById('varNombre').value  = '';
            document.getElementById('varPrecio').value  = '';
            document.getElementById('varStock').value   = '0';
            document.getElementById('varBarras').value  = '';

            document.getElementById('tituloFormVariante').textContent = 'Nueva variante';
            document.getElementById('btnVarianteTexto').textContent   = 'Agregar variante';
            this.classList.add('d-none');
        });
    }

    // ── Eliminar variante ────────────────────────
    document.querySelectorAll('.btn-delete-variante').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id         = this.dataset.id;
            const nombre     = this.dataset.nombre;
            const csrf       = this.dataset.csrf;
            const productoId = document.querySelector('input[name="producto_id"]')?.value
                            ?? document.querySelector('input[name="id"]')?.value;

            Swal.fire({
                icon: 'warning',
                title: '¿Eliminar variante?',
                text: `"${nombre}" será eliminada permanentemente.`,
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?= APP_URL ?>Productos/deleteVariante';
                    form.innerHTML = `
                        <input type="hidden" name="variante_id" value="${id}">
                        <input type="hidden" name="producto_id" value="${productoId}">
                        <input type="hidden" name="csrf_token" value="${csrf}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

});
</script>