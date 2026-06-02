/* ════════════════════════════════════════════════════════════════
   am-tour.js — Tour guiado multi-página del admin
   ─────────────────────────────────────────────────────────────────
   Sistema unificado que reemplaza los tours independientes
   (Dashboard, Caja, etc.). El tour ahora navega entre páginas
   para mostrar cada módulo en su contexto real.

   Disparo:
   - Automático en el primer login (window.AM_TOUR_COMPLETADO === false)
   - Manual: window.amTourIniciar() — desde el botón "Repetir tour"

   El progreso se guarda en localStorage para sobrevivir a las
   redirecciones entre páginas.
   ════════════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    // ── Storage keys ───────────────────────────────────────────
    const STORAGE_KEY  = 'am_tour_state';
    const COMPLETED_OK = 'am_tour_completed_ack';

    // ── Definición del tour (los pasos ordenados) ──────────────
    // Cada paso tiene:
    //   route    — URL relativa (sin host). Si no estamos ahí, navegamos.
    //   target   — selector CSS del elemento. Vacío = popover centrado.
    //   icon     — clase FontAwesome para el ícono del título
    //   title    — título del popover (sin emojis)
    //   body     — HTML de la descripción
    //   side     — posición del popover relativa al target (top|bottom|left|right)
    const STEPS = [
        {
            route: 'Dashboard/index',
            target: null,
            icon: 'fa-hand-wave',
            title: '¡Bienvenida al panel!',
            body: 'Hola <strong>{NOMBRE}</strong>. Te voy a guiar paso a paso por los módulos principales del sistema para que puedas gestionar Zona Marcol desde el primer día. Toca <strong>Siguiente</strong> para empezar.',
            side: 'over'
        },
        {
            route: 'Dashboard/index',
            target: '#tour-menu, [data-tour="menu"], .sidebar',
            icon: 'fa-bars',
            title: 'Menú principal',
            body: 'Desde acá accedes a todos los módulos del sistema. En móvil, toca el botón <strong>☰</strong> para abrir el menú lateral.',
            side: 'right'
        },
        {
            route: 'Dashboard/index',
            target: '#tour-notif, [data-tour="notif"], .notif-bell',
            icon: 'fa-bell',
            title: 'Notificaciones',
            body: 'Te avisa cuando llegan pedidos nuevos u otros eventos del sistema en tiempo real. El número rojo indica cuántas tienes sin leer. Se refresca cada 30 segundos.',
            side: 'bottom'
        },
        {
            route: 'Dashboard/index',
            target: '#tour-resumen, [data-tour="resumen"], .row.g-3.mb-4',
            icon: 'fa-chart-pie',
            title: 'Resumen del día',
            body: 'Tu vistazo rápido cada mañana: ventas de hoy, pedidos pendientes y clientes registrados.',
            side: 'bottom'
        },
        // ── Caja (navega a /Caja/index) ─────────────────────────
        {
            route: 'Caja/index',
            target: null,
            icon: 'fa-cash-register',
            title: 'Caja — Punto de venta',
            body: 'Ahora estás en <strong>Caja</strong>. Acá registras todas las ventas presenciales. Te muestro las 3 zonas principales.',
            side: 'over'
        },
        {
            route: 'Caja/index',
            target: '#tour-buscador-caja, [data-tour="buscador-caja"]',
            icon: 'fa-search',
            title: 'Buscar producto',
            body: 'Escribe el nombre o pasa el escáner de código de barras. Los resultados aparecen al instante.',
            side: 'bottom'
        },
        {
            route: 'Caja/index',
            target: '#tour-carrito-caja, [data-tour="carrito-caja"]',
            icon: 'fa-shopping-cart',
            title: 'Carrito de venta',
            body: 'Los productos que vas agregando aparecen acá con sus precios. Si hay descuento activo se aplica automáticamente.',
            side: 'left'
        },
        {
            route: 'Caja/index',
            target: '#tour-btn-cobrar, [data-tour="btn-cobrar"]',
            icon: 'fa-check-circle',
            title: 'Cobrar y emitir recibo',
            body: 'Elige el método de pago (<strong>Efectivo / Tarjeta / Transferencia</strong>) y toca <strong>Cobrar</strong>. El sistema descuenta stock y genera el recibo.',
            side: 'top'
        },
        // ── Productos (navega a /Productos/index) ───────────────
        {
            route: 'Productos/index',
            target: null,
            icon: 'fa-boxes-stacked',
            title: 'Catálogo de productos',
            body: 'Acá administras tu inventario. Crear, editar, activar/desactivar productos, gestionar variantes y stock.',
            side: 'over'
        },
        // ── Pedidos (navega a /Pedidos/index) ───────────────────
        {
            route: 'Pedidos/index',
            target: null,
            icon: 'fa-truck',
            title: 'Pedidos en línea',
            body: 'Los pedidos de la tienda aparecen acá. Avanza el estado: <strong>Pendiente → En preparación → Listo → En camino → Entregado</strong>.',
            side: 'over'
        },
        // ── Reportes ────────────────────────────────────────────
        {
            route: 'Reportes/ventas',
            target: null,
            icon: 'fa-chart-line',
            title: 'Reportes y estadísticas',
            body: 'Gráficas de ventas por día/mes, top de productos, métodos de pago e inventario. Toda la información para entender tu negocio.',
            side: 'over'
        },
        // ── Cierre ──────────────────────────────────────────────
        {
            route: 'Dashboard/index',
            target: null,
            icon: 'fa-star',
            title: '¡Listo para empezar!',
            body: 'Eso es lo esencial. Si querés repetir el tour, lo encontrás en el menú de usuario arriba a la derecha → <strong>Repetir tour</strong>. ¡Éxitos con tu negocio!',
            side: 'over'
        }
    ];

    // ── Helpers ────────────────────────────────────────────────

    function getCurrentPath() {
        // Extrae el "url" param o el path actual
        const u = new URLSearchParams(window.location.search).get('url') || '';
        if (u) return u.replace(/^\/|\/$/g, '').toLowerCase();
        // Fallback: pathname menos APP_URL base
        const base = (window.AM_APP_URL || '/').replace(/^https?:\/\/[^/]+/, '');
        let p = window.location.pathname.replace(base, '');
        return p.replace(/^\/|\/$/g, '').toLowerCase();
    }

    function getState() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY)) || null;
        } catch (e) {
            return null;
        }
    }

    function setState(stepIndex) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
            stepIndex,
            startedAt: Date.now()
        }));
    }

    function clearState() {
        localStorage.removeItem(STORAGE_KEY);
    }

    function markCompleted() {
        clearState();
        localStorage.setItem(COMPLETED_OK, '1');
        // Notificar al backend via AJAX si está disponible
        if (typeof window.amMarcarTour === 'function') {
            window.amMarcarTour();
        }
    }

    // ── Renderizado del popover ────────────────────────────────

    function renderPopover(step) {
        const nombre = (window.AM_USER_NOMBRE || 'Usuario');
        const body   = step.body.replace(/\{NOMBRE\}/g, nombre);
        const iconHtml = '<span class="am-tour-icon"><i class="fas ' + step.icon + '"></i></span>';

        return {
            title: iconHtml + '<span>' + step.title + '</span>',
            description: body,
            side: step.side === 'over' ? undefined : (step.side || 'bottom'),
            align: 'start'
        };
    }

    // ── Inicialización de Driver.js ────────────────────────────

    function getDriverFn() {
        if (window.driver && window.driver.js) return window.driver.js.driver;
        return window.driver;
    }

    // ── Engine del tour ────────────────────────────────────────

    function buildStepsFromIndex(startIndex) {
        // Solo se incluyen los pasos de la ruta actual.
        // El último paso de cada ruta tiene un onNextClick que navega a la siguiente.
        const currentRoute = getCurrentPath();
        const result = [];
        let routeStarted = false;

        for (let i = startIndex; i < STEPS.length; i++) {
            const step = STEPS[i];
            const stepRoute = step.route.toLowerCase();

            if (stepRoute !== currentRoute) {
                if (routeStarted) {
                    // Hay más pasos de la misma ruta antes; este paso es de OTRA ruta.
                    // Aquí cortamos y agregamos un step "puente" que navega.
                    result.push({
                        popover: {
                            title: '<span class="am-tour-icon"><i class="fas fa-arrow-right"></i></span><span>Vamos a la siguiente sección</span>',
                            description: 'Toca <strong>Siguiente</strong> para continuar.',
                            showButtons: ['next', 'close']
                        },
                        onNextClick: function () {
                            setState(i);
                            window.location.href = (window.AM_APP_URL || '/') + STEPS[i].route;
                        }
                    });
                    break;
                } else {
                    // Aún no encontramos un step de la ruta actual.
                    // Si el primer step es de otra ruta, navegamos directo.
                    if (i === startIndex) {
                        setState(i);
                        window.location.href = (window.AM_APP_URL || '/') + step.route;
                        return null; // Aborta el build, ya redirigimos
                    }
                    continue;
                }
            }

            routeStarted = true;
            const driverStep = {
                element: step.target || undefined,
                popover: renderPopover(step)
            };

            // Marcar como último paso "real" si es el último de esta sesión de pasos
            const isLastOfTour = (i === STEPS.length - 1);
            if (isLastOfTour) {
                driverStep.popover.showButtons = ['previous', 'close'];
            }

            result.push(driverStep);
        }
        return result;
    }

    function startFromIndex(stepIndex) {
        const driverFn = getDriverFn();
        if (typeof driverFn !== 'function') {
            console.warn('[am-tour] Driver.js no está cargado');
            return;
        }

        const steps = buildStepsFromIndex(stepIndex);
        if (steps === null) return; // Redirigió, se reanudará al cargar la nueva página
        if (steps.length === 0) {
            markCompleted();
            return;
        }

        setState(stepIndex);

        const t = driverFn({
            showProgress:    true,
            popoverClass:    'am-driver-popover',
            nextBtnText:     'Siguiente',
            prevBtnText:     'Atrás',
            doneBtnText:     '¡Listo!',
            allowClose:      true,
            disableActiveInteraction: false,
            onDestroyStarted: function () {
                // Si llegó al final, marcar completado.
                // Si cerró a mitad, mantener el progreso para "Repetir tour" después.
                const total = steps.length;
                const active = t.getActiveIndex();
                if (active >= total - 1) {
                    markCompleted();
                } else {
                    // Usuario cerró a mitad → consideramos descartado, limpiar.
                    markCompleted();
                }
                t.destroy();
            },
            steps: steps
        });

        t.drive();
    }

    // ── API pública ────────────────────────────────────────────

    /**
     * Inicia el tour desde el principio.
     * Usado por el botón "Repetir tour".
     */
    window.amTourIniciar = function () {
        localStorage.removeItem(COMPLETED_OK);
        startFromIndex(0);
    };

    /**
     * Reanuda el tour desde un step específico.
     * Usado internamente al cargar páginas después de una navegación.
     */
    window.amTourReanudar = function () {
        const state = getState();
        if (!state || typeof state.stepIndex !== 'number') return false;
        startFromIndex(state.stepIndex);
        return true;
    };

    // ── Auto-inicialización al cargar la página ────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Si hay tour en progreso (post-redirección) — reanudar.
        const state = getState();
        if (state) {
            // Pequeño delay para que renderice el DOM
            setTimeout(function () { startFromIndex(state.stepIndex); }, 350);
            return;
        }

        // Si es la primera vez del usuario → arrancar.
        if (window.AM_TOUR_COMPLETADO === false && !localStorage.getItem(COMPLETED_OK)) {
            setTimeout(function () { startFromIndex(0); }, 600);
        }
    });
})();
