/**
 * sw-loader.js — Registra el Service Worker en cada carga.
 *
 * Política agresiva de actualización:
 *   - `updateViaCache: 'none'` obliga al browser a re-validar el sw.js
 *     contra el servidor, no usar copia cacheada.
 *   - `registration.update()` chequea explícitamente si hay versión
 *     nueva del SW al cargar la página.
 *   - Cuando hay versión nueva (event `updatefound`), el waiting worker
 *     recibe `skipWaiting()` para activarse de inmediato sin esperar a
 *     que el usuario cierre todos los tabs.
 *   - Cuando el control cambia al SW nuevo, recargamos la página una
 *     sola vez para que cargue assets frescos.
 */
(function () {
    if (!('serviceWorker' in navigator)) return;

    let recargando = false;
    navigator.serviceWorker.addEventListener('controllerchange', function () {
        if (recargando) return;
        recargando = true;
        window.location.reload();
    });

    window.addEventListener('load', function () {
        navigator.serviceWorker.register(APP_URL + 'sw.js', {
            scope: APP_URL,
            updateViaCache: 'none'
        }).then(function (reg) {
            // Si ya hay un SW esperando, activarlo ahora.
            if (reg.waiting) {
                reg.waiting.postMessage({ type: 'SKIP_WAITING' });
            }
            // Chequear actualizaciones del SW al cargar la página.
            reg.update();

            // Cuando aparece un SW nuevo durante esta sesión, saltar la
            // espera y activarlo. controllerchange dispara el reload.
            reg.addEventListener('updatefound', function () {
                const newWorker = reg.installing;
                if (!newWorker) return;
                newWorker.addEventListener('statechange', function () {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        newWorker.postMessage({ type: 'SKIP_WAITING' });
                    }
                });
            });
        }).catch(function (err) {
            console.warn('SW registration failed:', err);
        });
    });
})();
