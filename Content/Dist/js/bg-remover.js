/**
 * bg-remover.js — Componente reusable de remoción de fondo client-side.
 *
 * Motor: @imgly/background-removal v1.x cargado desde CDN (jsDelivr).
 * Corre 100% en el navegador con ONNX Runtime + WebAssembly. La imagen
 * NO sale del browser. Sin API keys ni costos.
 *
 * Primera ejecución descarga ~25MB de modelo + WASM (browser cachea).
 * Después de eso, ~3-8 segundos por imagen según tamaño y CPU.
 *
 * USO en una vista:
 *
 *   <input type="file" id="miInput" data-bg-remover>
 *   <img id="miPreview" data-bg-preview-for="miInput">
 *
 * El JS detecta los `data-bg-remover` al cargar y inyecta un botón
 * "Quitar fondo" + estado de progreso debajo del input. Cuando el
 * usuario clickea, procesa el archivo actual del input, reemplaza la
 * imagen y refresca el preview asociado.
 *
 * Si el browser no soporta (sin WebAssembly), oculta el botón en silencio.
 */
(function () {
    'use strict';

    // Detección rápida — sin WASM no hay forma de correr el modelo.
    if (typeof WebAssembly !== 'object') return;

    // Estado global del módulo
    let removeFn = null;          // función resuelta una vez cargada la lib
    let cargandoLib = null;       // promesa de carga lazy

    /**
     * Carga la librería @imgly/background-removal en demanda.
     * Devuelve una promesa que resuelve a la función removeBackground.
     */
    function cargarLib() {
        if (removeFn) return Promise.resolve(removeFn);
        if (cargandoLib) return cargandoLib;

        cargandoLib = new Promise((resolve, reject) => {
            // ESM dinámico — el bundle UMD es enorme, conviene el module.
            import('https://cdn.jsdelivr.net/npm/@imgly/background-removal@1.4.5/dist/browser.mjs')
                .then((mod) => {
                    removeFn = mod.removeBackground || mod.default;
                    if (typeof removeFn !== 'function') {
                        reject(new Error('removeBackground no encontrada en el módulo.'));
                        return;
                    }
                    resolve(removeFn);
                })
                .catch(reject);
        });
        return cargandoLib;
    }

    /**
     * Reemplaza el contenido de un input[type=file] con un nuevo File.
     * Usa DataTransfer porque .files es read-only directo.
     */
    function setInputFile(input, file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        // Dispara change para que otros listeners reaccionen.
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    /**
     * Genera nombre de archivo "foo.jpg" → "foo_sinfondo.png".
     */
    function nombreSinFondo(original) {
        const idx = original.lastIndexOf('.');
        const base = idx >= 0 ? original.slice(0, idx) : original;
        return base + '_sinfondo.png';
    }

    /**
     * Procesa el archivo del input y reemplaza por el resultado.
     */
    async function procesar(input, statusEl, previewImg, btn) {
        if (!input.files || input.files.length === 0) {
            statusEl.textContent = 'Primero elegí una imagen.';
            statusEl.className = 'small text-warning mt-1';
            return;
        }
        const file = input.files[0];

        btn.disabled = true;
        statusEl.className = 'small text-muted mt-1';
        statusEl.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Cargando modelo y procesando...';

        try {
            const remove = await cargarLib();
            // El modelo devuelve un Blob PNG con alpha.
            const blob = await remove(file);
            const nuevoFile = new File(
                [blob],
                nombreSinFondo(file.name),
                { type: 'image/png', lastModified: Date.now() }
            );

            setInputFile(input, nuevoFile);

            // Preview opcional
            if (previewImg) {
                const url = URL.createObjectURL(blob);
                previewImg.src = url;
                // Liberar memoria del objeto URL cuando se descarte
                previewImg.addEventListener('load', () => URL.revokeObjectURL(url), { once: true });
            }

            statusEl.className = 'small text-success mt-1';
            statusEl.innerHTML = '<i class="fas fa-check me-1"></i>Fondo removido. Guardá el formulario para subirla.';
        } catch (err) {
            console.error('[bg-remover]', err);
            statusEl.className = 'small text-danger mt-1';
            statusEl.textContent = 'No se pudo procesar: ' + (err.message || 'error desconocido');
        } finally {
            btn.disabled = false;
        }
    }

    /**
     * Inyecta UI (botón + status) para un input dado.
     */
    function montar(input) {
        if (input.dataset.bgRemoverMounted === '1') return;
        input.dataset.bgRemoverMounted = '1';

        const wrapper = document.createElement('div');
        wrapper.className = 'mt-2 d-flex flex-column gap-1';

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-warning';
        btn.innerHTML = '<i class="fas fa-magic me-1"></i>Quitar fondo de la imagen';

        const status = document.createElement('div');
        status.className = 'small text-muted mt-1';
        status.innerHTML = '<i class="fas fa-info-circle me-1"></i>'
            + 'La primera vez descarga ~25MB (cacheado después). '
            + 'La imagen nunca sale de tu navegador.';

        wrapper.appendChild(btn);
        wrapper.appendChild(status);

        // Insertar después del input
        if (input.parentElement) {
            input.parentElement.appendChild(wrapper);
        }

        // Preview asociado por id
        const inputId = input.id;
        const previewImg = inputId
            ? document.querySelector('[data-bg-preview-for="' + inputId + '"]')
            : null;

        btn.addEventListener('click', () => procesar(input, status, previewImg, btn));
    }

    function init() {
        document.querySelectorAll('input[type=file][data-bg-remover]').forEach(montar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
