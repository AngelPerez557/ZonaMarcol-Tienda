/**
 * sse-notif.js — Cliente Server-Sent Events para notificaciones admin.
 *
 * Reemplaza el polling cada 30s. Se conecta a /Notificaciones/stream
 * que mantiene la conexión abierta y empuja eventos cuando hay nuevas
 * notificaciones sin leer.
 *
 * Reconnexión automática del lado del browser ya está integrada en
 * EventSource. Si el servidor cierra (timeout), reintenta a los 3s.
 *
 * Eventos esperados:
 *   - "notif"  payload JSON { id, tipo, titulo, mensaje, url }
 *   - "count"  payload número total de no leídas
 */
(function () {
    if (!window.EventSource) return;        // browsers antiguos
    if (!document.getElementById('notifBadge')) return;   // no estamos en admin

    const url = APP_URL + 'Notificaciones/stream';
    let evt = null;

    function conectar() {
        evt = new EventSource(url);

        evt.addEventListener('count', function (e) {
            const n = parseInt(e.data, 10) || 0;
            const badge = document.getElementById('notifBadge');
            if (!badge) return;
            badge.textContent = n;
            badge.style.display = n > 0 ? 'inline-block' : 'none';
        });

        evt.addEventListener('notif', function (e) {
            try {
                const data = JSON.parse(e.data);
                // Hook opcional: emitir CustomEvent para que otros JS
                // (toasts, dropdown de notificaciones) reaccionen.
                document.dispatchEvent(new CustomEvent('zm:notification', { detail: data }));
            } catch (err) {
                console.warn('SSE payload inválido:', err);
            }
        });

        evt.onerror = function () {
            // EventSource reintenta solo. Si el server cerró por timeout,
            // forzamos cierre para limpiar y dejamos que el browser lo
            // reconecte con backoff propio.
            if (evt && evt.readyState === EventSource.CLOSED) {
                setTimeout(conectar, 3000);
            }
        };
    }

    conectar();

    // Cerrar al salir para no dejar sockets colgando.
    window.addEventListener('beforeunload', function () {
        if (evt) evt.close();
    });
})();
