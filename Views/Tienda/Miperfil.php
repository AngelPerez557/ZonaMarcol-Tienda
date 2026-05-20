<div class="container py-5" style="max-width:600px;">

    <h3 class="fw-bold mb-4">
        <i class="fas fa-user-circle me-2" style="color:#F5A800;"></i>Mi perfil
    </h3>

    <?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
        <i class="fas fa-exclamation-circle"></i>
        <?php
            $errores = [
                'campos'   => 'El nombre y al menos un medio de contacto son obligatorios.',
                'email'    => 'El formato del correo no es válido.',
                'duplicado'=> 'Ese correo ya está registrado por otro cliente.',
                'servidor' => 'Error al actualizar. Intenta de nuevo.',
            ];
            echo $errores[$error] ?? 'Error desconocido.';
        ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($ok)): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
        <i class="fas fa-check-circle"></i>
        Datos actualizados correctamente.
    </div>
    <?php endif; ?>

    <!-- ── Datos personales ── -->
    <div class="card mb-4">
        <div class="card-header fw-semibold">
            <i class="fas fa-id-card me-2"></i>Datos personales
        </div>
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>Tienda/guardarPerfil">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text"
                           name="nombre"
                           class="form-control"
                           value="<?= htmlspecialchars($cliente->nombre ?? '') ?>"
                           required maxlength="120"
                           placeholder="Tu nombre completo">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Correo electrónico</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="<?= htmlspecialchars($cliente->email ?? '') ?>"
                           maxlength="120"
                           placeholder="tu@correo.com">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fab fa-whatsapp me-1 text-success"></i>WhatsApp / Teléfono
                    </label>
                    <input type="text"
                           name="telefono"
                           class="form-control"
                           value="<?= htmlspecialchars($cliente->telefono ?? '') ?>"
                           maxlength="20"
                           placeholder="9999-9999">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Dirección</label>
                    <textarea name="direccion"
                              class="form-control"
                              rows="2"
                              maxlength="255"
                              placeholder="Colonia, calle, referencia..."><?= htmlspecialchars($cliente->direccion ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn-rosa w-100" style="padding:10px;">
                    <i class="fas fa-save me-2"></i>Guardar cambios
                </button>
            </form>
        </div>
    </div>

    <!-- ── Cambiar contraseña ── -->
    <div class="card">
        <div class="card-header fw-semibold">
            <i class="fas fa-lock me-2"></i>Cambiar contraseña
        </div>
        <div class="card-body">

            <?php if (!empty($errorPassword)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-exclamation-circle"></i>
                <?php
                    $erroresPass = [
                        'actual'    => 'La contraseña actual no es correcta.',
                        'coincide'  => 'Las contraseñas nuevas no coinciden.',
                        'corta'     => 'La nueva contraseña debe tener al menos 6 caracteres.',
                        'servidor'  => 'Error al cambiar la contraseña.',
                    ];
                    echo $erroresPass[$errorPassword] ?? 'Error desconocido.';
                ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($okPassword)): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-check-circle"></i>
                Contraseña actualizada correctamente.
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= APP_URL ?>Tienda/cambiarPassword">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Contraseña actual</label>
                    <input type="password"
                           name="password_actual"
                           class="form-control"
                           required
                           placeholder="Tu contraseña actual">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nueva contraseña</label>
                    <input type="password"
                           name="password_nueva"
                           class="form-control"
                           required minlength="6"
                           placeholder="Mínimo 6 caracteres">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Confirmar nueva contraseña</label>
                    <input type="password"
                           name="password_confirmar"
                           class="form-control"
                           required minlength="6"
                           placeholder="Repite la nueva contraseña">
                </div>

                <button type="submit" class="btn-rosa w-100" style="padding:10px;">
                    <i class="fas fa-key me-2"></i>Cambiar contraseña
                </button>
            </form>
        </div>
    </div>

</div>