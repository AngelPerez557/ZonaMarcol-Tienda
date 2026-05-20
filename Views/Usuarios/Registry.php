<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-<?= $usuario->Found ? 'user-edit' : 'user-plus' ?> me-2"
                   style="color:#F5A800;"></i>
                <?= htmlspecialchars($pageTitle) ?>
            </h4>
            <small class="text-muted">
                <?= $usuario->Found
                    ? 'Modifica los datos del usuario.'
                    : 'Completa el formulario para crear un nuevo usuario del panel.' ?>
            </small>
        </div>
        <a href="<?= APP_URL ?>Usuarios/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-cog me-2"></i>Datos del usuario
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Usuarios/save"
                          enctype="multipart/form-data"
                          autocomplete="off">

                        <!-- CSRF -->
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <!-- ID oculto en edición -->
                        <?php if ($usuario->Found): ?>
                        <input type="hidden" name="id" value="<?= $usuario->id ?>">
                        <?php endif; ?>

                        <!-- Foto de perfil -->
                        <div class="mb-4 text-center">
                            <div style="
                                width:90px; height:90px; border-radius:50%;
                                margin:0 auto 12px;
                                background-image: url('<?= $usuario->getFotoUrl() ?>');
                                background-size: cover;
                                background-position: center;
                                background-color: rgba(245,168,0,0.12);
                                border: 3px solid rgba(245,168,0,0.4);"
                                id="previewFoto">
                            </div>
                            <label for="foto" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-camera me-1"></i>
                                <?= $usuario->Found ? 'Cambiar foto' : 'Subir foto' ?>
                            </label>
                            <input type="file"
                                   class="d-none"
                                   id="foto"
                                   name="foto"
                                   accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted d-block mt-1">JPG, PNG o WEBP. Máx. 2MB</small>
                        </div>

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre completo <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="nombre"
                                   name="nombre"
                                   maxlength="120"
                                   placeholder="Nombre del usuario"
                                   value="<?= htmlspecialchars($usuario->nombre ?? '') ?>"
                                   required
                                   autofocus>
                        </div>

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">
                                Usuario
                                <span class="text-muted fw-normal">(opcional — para iniciar sesión)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text"
                                    class="form-control"
                                    id="username"
                                    name="username"
                                    maxlength="60"
                                    placeholder="nombre_usuario"
                                    value="<?= htmlspecialchars($usuario->username ?? '') ?>"
                                    autocomplete="off">
                            </div>
                            <small class="text-muted">Sin espacios ni caracteres especiales.</small>
                        </div>

                        <!-- Correo -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                Correo electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   class="form-control"
                                   id="email"
                                   name="email"
                                   maxlength="120"
                                   placeholder="correo@ejemplo.com"
                                   value="<?= htmlspecialchars($usuario->email ?? '') ?>"
                                   required>
                        </div>

                        <!-- Teléfono -->
                        <div class="mb-3">
                            <label for="telefono" class="form-label fw-semibold">
                                Teléfono
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="telefono"
                                   name="telefono"
                                   maxlength="20"
                                   placeholder="9999-9999"
                                   value="<?= htmlspecialchars($usuario->telefono ?? '') ?>">
                        </div>

                        <!-- Rol -->
                        <div class="mb-3">
                            <label for="rol_id" class="form-label fw-semibold">
                                Rol <span class="text-danger">*</span>
                            </label>
                            <?php if ($esPropioUsuario): ?>
                            <!-- Si se edita a sí mismo no puede cambiar su rol -->
                            <input type="hidden" name="rol_id" value="<?= $usuario->rol_id ?>">
                            <input type="text"
                                   class="form-control"
                                   value="<?= htmlspecialchars($usuario->getNombreRol()) ?>"
                                   disabled>
                            <small class="text-muted">No puedes cambiar tu propio rol.</small>
                            <?php else: ?>
                            <select class="form-select" id="rol_id" name="rol_id" required>
                                <option value="">Seleccionar rol...</option>
                                <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol->id ?>"
                                        <?= (int)($usuario->rol_id ?? 0) === $rol->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($rol->nombre) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php endif; ?>
                        </div>

                        <!-- Estado -->
                        <?php if ($usuario->Found && !$esPropioUsuario): ?>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       role="switch"
                                       id="activo"
                                       name="activo"
                                       <?= $usuario->isActivo() ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="activo">
                                    Usuario activo
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Contraseña -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                Contraseña
                                <?php if ($usuario->Found): ?>
                                <span class="text-muted fw-normal">(dejar vacío para no cambiar)</span>
                                <?php else: ?>
                                <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control"
                                       id="password"
                                       name="password"
                                       maxlength="100"
                                       placeholder="<?= $usuario->Found ? 'Nueva contraseña...' : 'Contraseña del usuario...' ?>"
                                       autocomplete="new-password"
                                       <?= !$usuario->Found ? 'required' : '' ?>>
                                <button type="button"
                                        class="btn btn-outline-secondary"
                                        id="btnVerPassword">
                                    <i class="fas fa-eye" id="iconPassword"></i>
                                </button>
                            </div>
                            <?php if (!$usuario->Found): ?>
                            <small class="text-muted">Mínimo 8 caracteres recomendado.</small>
                            <?php endif; ?>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-save me-2"></i>
                                <?= $usuario->Found ? 'Guardar cambios' : 'Crear usuario' ?>
                            </button>
                            <a href="<?= APP_URL ?>Usuarios/index" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Toggle mostrar/ocultar contraseña
    document.getElementById('btnVerPassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = document.getElementById('iconPassword');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    // Preview foto de perfil
    document.getElementById('foto').addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('previewFoto').style.backgroundImage =
                    `url('${e.target.result}')`;
            };
            reader.readAsDataURL(file);
        }
    });

});
</script>