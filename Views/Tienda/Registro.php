<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png"
                             alt="<?= APP_NAME ?>"
                             style="height:44px; width:auto; object-fit:contain; margin-bottom:12px;">
                        <h4 class="fw-bold">Crear cuenta</h4>
                        <p class="text-muted" style="font-size:0.85rem;">Regístrate para hacer pedidos y dar seguimiento a tus órdenes</p>
                    </div>

                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2" style="font-size:0.85rem;">
                        <?php
                        echo match($error) {
                            'campos'   => 'Nombre, correo y contraseña son obligatorios.',
                            'password' => 'Las contraseñas no coinciden.',
                            'email'    => 'Ya existe una cuenta con ese correo.',
                            'csrf'     => 'Error de seguridad. Intenta de nuevo.',
                            default    => 'Error al crear la cuenta.'
                        };
                        ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= APP_URL ?>Tienda/guardarRegistro" autocomplete="off">
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre"
                                   placeholder="Tu nombre" required autofocus maxlength="120">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email"
                                   placeholder="tu@correo.com" required maxlength="120">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Teléfono / WhatsApp
                                <span class="text-muted fw-normal">(opcional)</span>
                            </label>
                            <input type="text" class="form-control" name="telefono"
                                   placeholder="9999-9999" maxlength="20">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password"
                                   placeholder="Mínimo 8 caracteres" required minlength="6">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirmar contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password2"
                                   placeholder="Repite tu contraseña" required>
                        </div>

                        <button type="submit" class="btn-rosa w-100 py-2">
                            <i class="fas fa-user-plus me-2"></i>Crear cuenta
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <span class="text-muted" style="font-size:0.85rem;">¿Ya tienes cuenta?</span>
                        <a href="<?= APP_URL ?>Tienda/login" style="color:#F5A800; font-weight:600;"> Ingresar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>