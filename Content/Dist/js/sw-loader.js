/**
 * sw-loader.js — Registra el Service Worker en la primera carga.
 * Carga lazy (defer en el script tag) para no bloquear el render.
 */
(function () {
    if (!('serviceWorker' in navigator)) return;
    window.addEventListener('load', function () {
        // El SW vive en la raíz del proyecto (no en /Content/) para que
        // su scope cubra toda la app — Service Workers solo controlan
        // las URLs dentro de su carpeta de origen.
        navigator.serviceWorker.register(APP_URL + 'sw.js', { scope: APP_URL })
            .catch(function (err) {
                console.warn('SW registration failed:', err);
            });
    });
})();
