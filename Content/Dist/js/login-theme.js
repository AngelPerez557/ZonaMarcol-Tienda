(function () {
    'use strict';

    // ─────────────────────────────────────────────
    // REFERENCIAS AL DOM
    // ─────────────────────────────────────────────
    const themeToggle = document.getElementById('themeToggleLogin');
    const themeIcon   = document.getElementById('themeIconLogin');
    const loginBody   = document.querySelector('.login-body');

    if (!loginBody) return;

    // ─────────────────────────────────────────────
    // CLAVE DE LOCALSTORAGE
    // Usa la misma clave que theme-switcher.js del sistema
    // para que el dark mode sea consistente entre login y dashboard
    // ─────────────────────────────────────────────
    const STORAGE_KEY = 'app-theme';

    // ─────────────────────────────────────────────
    // APLICAR MODO
    // Agrega o quita la clase 'dark-mode' — misma clase que el sistema
    // ─────────────────────────────────────────────
    function applyMode(isDark) {
        if (isDark) {
            loginBody.classList.add('dark-mode');
            if (themeIcon) themeIcon.className = 'fas fa-sun';
        } else {
            loginBody.classList.remove('dark-mode');
            if (themeIcon) themeIcon.className = 'fas fa-moon';
        }
    }

    // ─────────────────────────────────────────────
    // RESTAURAR PREFERENCIA AL CARGAR
    // Lee la misma clave que usa el sistema principal
    // Si el usuario activó dark mode en el dashboard
    // el login también arranca en dark mode automáticamente
    // ─────────────────────────────────────────────
    const saved  = localStorage.getItem(STORAGE_KEY);
    const isDark = saved === 'dark';

    // Aplica el modo inmediatamente — evita flash
    applyMode(isDark);

    // ─────────────────────────────────────────────
    // TOGGLE — Click en el botón de dark mode
    // ─────────────────────────────────────────────
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const currentlyDark = loginBody.classList.contains('dark-mode');
            const newMode       = !currentlyDark;

            // Aplica el modo visualmente
            applyMode(newMode);

            // Persiste en localStorage — misma clave que el sistema
            localStorage.setItem(STORAGE_KEY, newMode ? 'dark' : 'light');
        });
    }

})();