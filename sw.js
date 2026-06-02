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
// Bumpear este string invalida el cache anterior — útil cuando se modifica
// algún JS/CSS y los browsers tienen cacheada la versión vieja del SW.
const CACHE_NAME = 'zonamarcol-shell-v2';
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

    // 1. NUNCA interceptar cross-origin (CDNs, esm.sh, jsdelivr, fonts,
    //    Google Analytics, etc.). El SW solo conoce su propio origin —
    //    interceptar requests externas rompe ESM dynamic imports y CORS.
    if (url.origin !== self.location.origin) return;

    // 2. No interceptar SSE, endpoints AJAX/JSON, ni rutas admin que
    //    devuelven HTML dinámico con sesión (cachearlas mostraría datos
    //    de otro usuario).
    if (url.pathname.includes('/Notificaciones/stream') ||
        url.pathname.includes('/Api/')               ||
        url.pathname.includes('/Productos/')         ||
        url.pathname.includes('/Pedidos/')           ||
        url.pathname.includes('/PedidosCamiseta/')   ||
        url.pathname.includes('/Solicitudes/')       ||
        url.pathname.includes('/Ordenes/')           ||
        url.pathname.includes('/Caja/')              ||
        url.pathname.includes('/Reportes/')          ||
        url.pathname.includes('/Auth/')              ||
        url.pathname.includes('/Tienda/mis')) {
        return;
    }

    // 3. HTML → network-first con fallback al shell.
    if (req.headers.get('accept') && req.headers.get('accept').includes('text/html')) {
        event.respondWith(
            fetch(req)
                .catch(() => caches.match(req).then((m) => m || caches.match('./')))
        );
        return;
    }

    // 4. Assets estáticos → cache-first. Falla del network: devolver
    //    Response vacía no rompe la app.
    event.respondWith(
        caches.match(req).then((cached) => {
            if (cached) return cached;
            return fetch(req).then((resp) => {
                if (resp && resp.status === 200 && resp.type === 'basic') {
                    const copy = resp.clone();
                    caches.open(CACHE_NAME).then((c) => c.put(req, copy));
                }
                return resp;
            }).catch(() => new Response('', { status: 504, statusText: 'Offline' }));
        })
    );
});
