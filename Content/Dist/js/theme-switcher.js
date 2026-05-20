(function () {
    'use strict';

    // ─────────────────────────────────────────────
    // REFERENCIAS AL DOM
    // ─────────────────────────────────────────────
    const body      = document.getElementById('appBody');
    const btnToggle = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');

    // Si no existe el botón en la vista actual (ej: login) no ejecuta nada
    if (!body || !btnToggle) return;

    // ─────────────────────────────────────────────
    // CLAVE DE LOCALSTORAGE — Genérica por proyecto
    // ─────────────────────────────────────────────
    const STORAGE_KEY = 'app-theme';

    // ─────────────────────────────────────────────
    // APLICAR MODO
    // Agrega o quita la clase 'dark-mode' del body
    // y actualiza el ícono del botón
    // ─────────────────────────────────────────────
    function applyMode(isDark) {
        if (isDark) {
            body.classList.add('dark-mode');
            if (themeIcon) themeIcon.className = 'fas fa-sun';
        } else {
            body.classList.remove('dark-mode');
            if (themeIcon) themeIcon.className = 'fas fa-moon';
        }
    }

    // ─────────────────────────────────────────────
    // SINCRONIZAR CON SESIÓN PHP
    // Notifica al servidor el estado actual del dark mode
    // para que $_SESSION['dark_mode'] esté actualizado
    // y el header.php no genere flash al recargar la página
    // ─────────────────────────────────────────────
    function syncSession(isDark) {
        fetch(APP_URL + 'Auth/darkMode', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ dark_mode: isDark })
        }).catch(function () {
            // Si el fetch falla el modo visual ya fue aplicado
            // Solo se perderá la persistencia en sesión PHP
            console.warn('No se pudo sincronizar el dark mode con la sesión.');
        });
    }

    // ─────────────────────────────────────────────
    // RESTAURAR PREFERENCIA AL CARGAR
    // Lee localStorage — tiene prioridad sobre el estado del body
    // porque PHP ya aplicó la clase en el servidor si había sesión
    // ─────────────────────────────────────────────
    const saved = localStorage.getItem(STORAGE_KEY);

    if (saved !== null) {
        // Si hay preferencia guardada en localStorage la aplica
        applyMode(saved === 'dark');
    } else {
        // Si no hay localStorage lee el estado que PHP aplicó en el body
        const isDark = body.classList.contains('dark-mode');
        applyMode(isDark);
        localStorage.setItem(STORAGE_KEY, isDark ? 'dark' : 'light');
    }

    // ─────────────────────────────────────────────
    // TOGGLE — Click en el botón de dark mode
    // ─────────────────────────────────────────────
    btnToggle.addEventListener('click', function () {
        const isDark = !body.classList.contains('dark-mode');

        // Aplica el modo visualmente de inmediato
        applyMode(isDark);

        // Persiste en localStorage para restaurar al recargar
        localStorage.setItem(STORAGE_KEY, isDark ? 'dark' : 'light');

        // Sincroniza con la sesión PHP en segundo plano
        syncSession(isDark);
    });

    // ─────────────────────────────────────────────
    // APP_URL — disponible como variable global PHP
    // Se imprime en el footer antes de cargar este script
    // ─────────────────────────────────────────────

})();