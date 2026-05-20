<div class="container-fluid py-4">

    <!-- ─────────────────────────────────────────────
         CABECERA
         ───────────────────────────────────────────── -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-user-circle me-2" style="color:#F5A800;"></i>
                Mi Perfil
            </h4>
            <small class="text-muted">Actualiza tu información personal y contraseña.</small>
        </div>
        <a href="<?= APP_URL ?>Dashboard/index" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">

            <!-- ── Datos personales ── -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-id-card me-2"></i>Datos personales
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Usuarios/guardarPerfil"
                          enctype="multipart/form-data"
                          autocomplete="off">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

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
                                <i class="fas fa-camera me-1"></i>Cambiar foto
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
                            <label class="form-label fw-semibold">
                                Nombre completo <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   name="nombre"
                                   maxlength="120"
                                   value="<?= htmlspecialchars($usuario->nombre ?? '') ?>"
                                   required>
                        </div>

                        <!-- Username -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Usuario
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text"
                                       class="form-control"
                                       name="username"
                                       maxlength="60"
                                       placeholder="nombre_usuario"
                                       value="<?= htmlspecialchars($usuario->username ?? '') ?>"
                                       autocomplete="off">
                            </div>
                            <small class="text-muted">Sin espacios ni caracteres especiales.</small>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Correo electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   class="form-control"
                                   name="email"
                                   maxlength="120"
                                   value="<?= htmlspecialchars($usuario->email ?? '') ?>"
                                   required>
                        </div>

                        <!-- Teléfono -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Teléfono
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   name="telefono"
                                   maxlength="20"
                                   placeholder="9999-9999"
                                   value="<?= htmlspecialchars($usuario->telefono ?? '') ?>">
                        </div>

                        <!-- Rol — solo lectura -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Rol asignado</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= htmlspecialchars($usuario->rol_nombre ?? '') ?>"
                                   disabled>
                            <small class="text-muted">El rol solo puede ser modificado por un administrador.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Guardar cambios
                        </button>

                    </form>
                </div>
            </div>

            <!-- ── Cambiar contraseña ── -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-lock me-2"></i>Cambiar contraseña
                </div>
                <div class="card-body">
                    <form method="POST"
                          action="<?= APP_URL ?>Usuarios/cambiarPassword"
                          autocomplete="off">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contraseña actual</label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control"
                                       name="password_actual"
                                       id="passActual"
                                       required
                                       placeholder="Tu contraseña actual">
                                <button type="button" class="btn btn-outline-secondary btn-ver-pass"
                                        data-target="passActual">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nueva contraseña</label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control"
                                       name="password_nueva"
                                       id="passNueva"
                                       required minlength="6"
                                       placeholder="Mínimo 6 caracteres">
                                <button type="button" class="btn btn-outline-secondary btn-ver-pass"
                                        data-target="passNueva">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirmar nueva contraseña</label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control"
                                       name="password_confirmar"
                                       id="passConfirmar"
                                       required minlength="6"
                                       placeholder="Repite la nueva contraseña">
                                <button type="button" class="btn btn-outline-secondary btn-ver-pass"
                                        data-target="passConfirmar">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-key me-2"></i>Cambiar contraseña
                        </button>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Preview foto
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

    // Toggle mostrar/ocultar contraseña
    document.querySelectorAll('.btn-ver-pass').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const icon  = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

});
</script>