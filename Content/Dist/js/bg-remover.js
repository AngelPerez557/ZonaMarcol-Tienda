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

    // CDN base para los assets del modelo (ONNX + WASM). La librería los
    // busca relativo a su `publicPath`. Sin esto carga 404.
    const IMGLY_VERSION = '1.4.5';
    const IMGLY_BASE    = 'https://cdn.jsdelivr.net/npm/@imgly/background-removal@'
                        + IMGLY_VERSION + '/dist/';

    /**
     * Carga la librería @imgly/background-removal en demanda.
     * Usa esm.sh que resuelve dependencias internas del paquete (jsdelivr
     * ESM "crudo" no las bundlea y falla con "Failed to fetch dynamically
     * imported module"). Fallback a unpkg si esm.sh está caído.
     */
    function cargarLib() {
        if (removeFn) return Promise.resolve(removeFn);
        if (cargandoLib) return cargandoLib;

        const fuentes = [
            'https://esm.sh/@imgly/background-removal@' + IMGLY_VERSION,
            'https://esm.run/@imgly/background-removal@' + IMGLY_VERSION,
        ];

        cargandoLib = (async () => {
            let ultimoError = null;
            for (const url of fuentes) {
                try {
                    const mod = await import(url);
                    const fn = mod.removeBackground || mod.default;
                    if (typeof fn === 'function') {
                        removeFn = fn;
                        return fn;
                    }
                } catch (err) {
                    ultimoError = err;
                    console.warn('[bg-remover] fallo carga desde', url, err);
                }
            }
            throw ultimoError || new Error('No se pudo cargar la librería.');
        })();

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
            // publicPath le dice a la lib dónde buscar el modelo ONNX +
            // los binarios WASM. Sin esto, los busca relativo a la página
            // actual y da 404.
            const blob = await remove(file, {
                publicPath: IMGLY_BASE,
                debug: false,
                progress: (key, current, total) => {
                    if (key && key.indexOf('fetch') === 0) {
                        const pct = total ? Math.round((current / total) * 100) : 0;
                        statusEl.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>'
                            + 'Descargando modelo... ' + pct + '%';
                    } else if (key === 'compute:inference') {
                        statusEl.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>'
                            + 'Procesando imagen...';
                    }
                },
            });
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
