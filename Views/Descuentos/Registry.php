<div class="container-fluid py-4">

    <!-- CABECERA -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-percent me-2" style="color:#F5A800;"></i>
                <?= $esEdicion ? 'Editar Descuento' : 'Nuevo Descuento' ?>
            </h4>
            <small class="text-muted">
                <?= $esEdicion ? 'Modifica los datos del descuento.' : 'Crea un nuevo descuento para la tienda y caja.' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Descuentos/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tag me-2"></i>Datos del descuento
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>Descuentos/save" autocomplete="off">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <?php if ($esEdicion): ?>
                        <input type="hidden" name="id" value="<?= $descuento['id'] ?>">
                        <?php endif; ?>

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   name="nombre"
                                   maxlength="100"
                                   placeholder="Ej: Descuento de verano, Promo labiales..."
                                   value="<?= htmlspecialchars($descuento['nombre'] ?? '') ?>"
                                   required autofocus>
                        </div>

                        <!-- Porcentaje -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Porcentaje de descuento <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number"
                                       class="form-control"
                                       name="porcentaje"
                                       min="1" max="99" step="0.01"
                                       placeholder="Ej: 15"
                                       value="<?= htmlspecialchars($descuento['porcentaje'] ?? '') ?>"
                                       required
                                       id="inputPorcentaje">
                                <span class="input-group-text fw-bold" style="color:#F5A800;">%</span>
                            </div>
                            <small class="text-muted">Entre 1% y 99%</small>
                        </div>

                        <!-- Aplica a -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Aplica a <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex gap-2 mb-2">
                                <button type="button"
                                        class="btn-selector flex-fill <?= ($descuento['aplica_a'] ?? 'todo') === 'todo' ? 'activo' : '' ?>"
                                        data-valor="todo" id="btnTodo">
                                    <i class="fas fa-store me-1"></i>Toda la tienda
                                </button>
                                <button type="button"
                                        class="btn-selector flex-fill <?= ($descuento['aplica_a'] ?? '') === 'categoria' ? 'activo' : '' ?>"
                                        data-valor="categoria" id="btnCategoria">
                                    <i class="fas fa-tags me-1"></i>Por categoría
                                </button>
                            </div>
                            <input type="hidden" name="aplica_a" id="inputAplicaA"
                                   value="<?= htmlspecialchars($descuento['aplica_a'] ?? 'todo') ?>">
                        </div>

                        <!-- Categoría (solo si aplica_a = categoria) -->
                        <div class="mb-3" id="seccionCategoria"
                             style="<?= ($descuento['aplica_a'] ?? 'todo') !== 'categoria' ? 'display:none;' : '' ?>">
                            <label class="form-label fw-semibold">
                                Categoría <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="categoria_id" id="selectCategoria">
                                <option value="">Seleccionar categoría...</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat->id ?>"
                                        <?= (int)($descuento['categoria_id'] ?? 0) === $cat->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat->nombre) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Fechas -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">
                                    Fecha inicio <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       class="form-control"
                                       name="fecha_inicio"
                                       value="<?= htmlspecialchars($descuento['fecha_inicio'] ?? date('Y-m-d')) ?>"
                                       required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">
                                    Fecha fin <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       class="form-control"
                                       name="fecha_fin"
                                       value="<?= htmlspecialchars($descuento['fecha_fin'] ?? '') ?>"
                                       required>
                            </div>
                        </div>

                        <!-- Activo (solo en edición) -->
                        <?php if ($esEdicion): ?>
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       role="switch"
                                       id="activo"
                                       name="activo"
                                       <?= ($descuento['activo'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="activo">
                                    Descuento activo
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Preview -->
                        <div class="mb-4 p-3 rounded" style="background:rgba(245,168,0,0.08); border:1px solid rgba(245,168,0,0.2);" id="previewDescuento">
                            <small class="text-muted d-block mb-1">
                                <i class="fas fa-eye me-1"></i>Vista previa del badge:
                            </small>
                            <span id="badgePreview"
                                  style="background:#dc3545; color:#fff; padding:4px 12px; border-radius:20px; font-size:0.85rem; font-weight:700;">
                                -<?= $descuento['porcentaje'] ?? '0' ?>% OFF
                            </span>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $esEdicion ? 'Guardar cambios' : 'Crear descuento' ?>
                            </button>
                            <a href="<?= APP_URL ?>Descuentos/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.btn-selector {
    padding: 8px 12px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    background: #fff;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}
.btn-selector.activo {
    border-color: #F5A800;
    background: #F5A800;
    color: #fff;
}
.btn-selector:hover:not(.activo) {
    border-color: #F5A800;
    color: #F5A800;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Toggle aplica_a ──────────────────────────
    const inputAplicaA     = document.getElementById('inputAplicaA');
    const seccionCategoria = document.getElementById('seccionCategoria');
    const selectCategoria  = document.getElementById('selectCategoria');

    document.querySelectorAll('.btn-selector').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.btn-selector').forEach(b => b.classList.remove('activo'));
            this.classList.add('activo');
            const valor = this.dataset.valor;
            inputAplicaA.value = valor;

            if (valor === 'categoria') {
                seccionCategoria.style.display = '';
                selectCategoria.required = true;
            } else {
                seccionCategoria.style.display = 'none';
                selectCategoria.required = false;
                selectCategoria.value = '';
            }
        });
    });

    // ── Preview badge en tiempo real ─────────────
    const inputPct    = document.getElementById('inputPorcentaje');
    const badgePreview = document.getElementById('badgePreview');

    inputPct.addEventListener('input', function () {
        const val = parseFloat(this.value) || 0;
        badgePreview.textContent = `-${val}% OFF`;
    });

});
</script>