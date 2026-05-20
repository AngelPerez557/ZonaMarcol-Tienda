(function () {
    'use strict';

    // ─────────────────────────────────────────────
    // REFERENCIAS AL DOM
    // ─────────────────────────────────────────────
    const loginForm     = document.getElementById('loginForm');
    const btnLogin      = document.getElementById('btnLogin');
    const togglePass    = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon       = document.getElementById('eyeIcon');

    // ─────────────────────────────────────────────
    // MOSTRAR / OCULTAR CONTRASEÑA
    // ─────────────────────────────────────────────
    if (togglePass && passwordInput && eyeIcon) {
        togglePass.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type   = isPassword ? 'text' : 'password';
            eyeIcon.className    = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    }

    // ─────────────────────────────────────────────
    // EFECTOS DE FOCO EN INPUTS
    // Agrega clase 'focused' al wrapper del input
    // para efectos visuales adicionales en CSS
    // ─────────────────────────────────────────────
    document.querySelectorAll('.form-control-modern').forEach(input => {
        input.addEventListener('focus', function () {
            this.closest('.form-floating-modern')?.classList.add('focused');
        });

        input.addEventListener('blur', function () {
            if (!this.value) {
                this.closest('.form-floating-modern')?.classList.remove('focused');
            }
        });
    });

    // ─────────────────────────────────────────────
    // ENVÍO DEL FORMULARIO
    // Valida los campos antes de enviar al servidor
    // El POST va al AuthController::login() via JRouter
    // ─────────────────────────────────────────────
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const email    = document.getElementById('email')?.value.trim();
            const password = document.getElementById('password')?.value.trim();

            // ── Validación de email ──────────────────
            if (!email) {
                Swal.fire({
                    icon:              'warning',
                    title:             'Campo requerido',
                    text:              'El correo electrónico es obligatorio.',
                    confirmButtonText: 'Entendido',
                    allowOutsideClick: false,
                    allowEscapeKey:    false
                });
                return;
            }

            // Formato de email válido
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.fire({
                    icon:              'warning',
                    title:             'Correo inválido',
                    text:              'Ingresá un correo electrónico válido.',
                    confirmButtonText: 'Entendido',
                    allowOutsideClick: false,
                    allowEscapeKey:    false
                });
                return;
            }

            // ── Validación de contraseña ─────────────
            if (!password) {
                Swal.fire({
                    icon:              'warning',
                    title:             'Campo requerido',
                    text:              'La contraseña es obligatoria.',
                    confirmButtonText: 'Entendido',
                    allowOutsideClick: false,
                    allowEscapeKey:    false
                });
                return;
            }

            // ── Mostrar loader en el botón ───────────
            // Deshabilita el botón para evitar doble envío
            if (btnLogin) {
                btnLogin.classList.add('loading');
                btnLogin.disabled = true;
            }

            // Envía el formulario al servidor — AuthController::login()
            loginForm.submit();
        });
    }

})();