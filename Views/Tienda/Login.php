<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="<?= APP_URL ?>Content/Demo/img/zonamarcol_Logo.png"
                             alt="<?= APP_NAME ?>"
                             style="height:44px; width:auto; object-fit:contain; margin-bottom:12px;">
                        <h4 class="fw-bold">Iniciar sesión</h4>
                        <p class="text-muted" style="font-size:0.85rem;">Accede a tu cuenta</p>
                    </div>

                    <?php if (!empty($_GET['expired'])): ?>
                    <div class="alert alert-warning py-2" style="font-size:0.85rem;">
                        <i class="fas fa-clock me-2"></i>
                        Tu sesión expiró. Inicia sesión nuevamente.
                    </div>
                    <?php elseif (!empty($_GET['blocked'])): ?>
                    <div class="alert alert-danger py-2" style="font-size:0.85rem;">
                        <i class="fas fa-lock me-2"></i>
                        Demasiados intentos fallidos. Espera
                        <?= (int)($_GET['min'] ?? 15) ?> minuto(s) antes de intentar.
                    </div>
                    <?php elseif (!empty($error)): ?>
                    <div class="alert alert-danger py-2" style="font-size:0.85rem;">
                        <?php
                        echo match($error) {
                            'credenciales' => 'Correo o contraseña incorrectos.',
                            'inactivo'     => 'Tu cuenta está desactivada.',
                            default        => 'Error al iniciar sesión.'
                        };
                        ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= APP_URL ?>Tienda/procesarLogin" autocomplete="off">
                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Correo electrónico</label>
                            <input type="email" class="form-control" name="email"
                                   placeholder="tu@correo.com" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Contraseña</label>
                            <input type="password" class="form-control" name="password"
                                   placeholder="••••••••" required>
                        </div>

                        <button type="submit" class="btn-rosa w-100 py-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <span class="text-muted" style="font-size:0.85rem;">¿No tienes cuenta?</span>
                        <a href="<?= APP_URL ?>Tienda/registro" style="color:#F5A800; font-weight:600;"> Regístrate</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>