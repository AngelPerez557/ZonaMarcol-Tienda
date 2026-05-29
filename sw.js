/**
 * sw.js — Service Worker mínimo para ZonaMarcol.
 *
 * Estrategia:
 *   - Cache "shell" de assets críticos al instalar.
 *   - Strategy network-first para HTML (siempre fresh si hay red),
 *     cache-first para assets estáticos (css/js/img).
 *   - skipWaiting + clients.claim para activarse en la primera carga
 *     sin pedir recargar dos veces.
 *
 * No incluye push notifications todavía — eso requiere VAPID keys +
 * suscripción, y el envío real desde PHP necesita una librería como
 * web-push-php. Se deja como upgrade futuro; las notificaciones tiempo
 * real se sirven por SSE mientras el usuario está en la app.
 */
const CACHE_NAME = 'zonamarcol-shell-v1';
const SHELL_ASSETS = [
    './',
    './Content/Dist/css/admin.css',
    './Content/Dist/css/tienda.css',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            // Best-effort: no rompemos la instalación si un asset falla.
            return Promise.allSettled(
                SHELL_ASSETS.map((url) => cache.add(url).catch(() => null))
            );
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // No interceptar SSE / endpoints dinámicos.
    if (url.pathname.includes('/Notificaciones/stream') ||
        url.pathname.includes('/Api/')) {
        return;
    }

    // HTML → network-first.
    if (req.headers.get('accept') && req.headers.get('accept').includes('text/html')) {
        event.respondWith(
            fetch(req).catch(() => caches.match(req).then((m) => m || caches.match('./')))
        );
        return;
    }

    // Assets → cache-first.
    event.respondWith(
        caches.match(req).then((cached) => cached || fetch(req).then((resp) => {
            if (resp && resp.status === 200 && resp.type === 'basic') {
                const copy = resp.clone();
                caches.open(CACHE_NAME).then((c) => c.put(req, copy));
            }
            return resp;
        }))
    );
});
