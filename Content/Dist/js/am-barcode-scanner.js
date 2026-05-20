/* ════════════════════════════════════════════════════════════════
   am-barcode-scanner.js — Scanner de código de barras con cámara

   Requiere: html5-qrcode (cargado en footer.php desde CDN)
   API:
     window.amBarcodeScanner.open(inputElement)
       → abre el modal de cámara
       → al detectar un código válido, autocompleta inputElement.value
       → cierra el modal

   Soporta los formatos más comunes en retail:
     EAN-13, EAN-8, UPC-A, UPC-E, Code128, Code39, ITF, Codabar
   ════════════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    const MODAL_ID  = 'amBarcodeModal';
    const VIDEO_ID  = 'amBarcodeVideo';
    const STATUS_ID = 'amBarcodeStatus';
    let scanner     = null;
    let targetInput = null;

    // ── Inyectar estilos una sola vez ──────────────────────────
    function injectStyles() {
        if (document.getElementById('am-barcode-styles')) return;
        const css = `
            #${MODAL_ID} {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.88);
                z-index: 99999;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 16px;
            }
            #${MODAL_ID}.am-open { display: flex; }
            #${MODAL_ID} .am-bc-box {
                background: #fff;
                border-radius: 16px;
                width: 100%;
                max-width: 480px;
                overflow: hidden;
                box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            }
            #${MODAL_ID} .am-bc-head {
                background: linear-gradient(135deg, #F5A800, #8C6300);
                color: #fff;
                padding: 14px 18px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            #${MODAL_ID} .am-bc-head h5 {
                margin: 0;
                font-size: 1rem;
                font-weight: 600;
            }
            #${MODAL_ID} .am-bc-close {
                background: rgba(255,255,255,0.2);
                color: #fff;
                border: none;
                border-radius: 50%;
                width: 30px;
                height: 30px;
                cursor: pointer;
                font-size: 1.1rem;
                line-height: 1;
                transition: background 0.18s;
            }
            #${MODAL_ID} .am-bc-close:hover { background: rgba(255,255,255,0.35); }
            #${MODAL_ID} #${VIDEO_ID} {
                width: 100%;
                aspect-ratio: 4 / 3;
                background: #000;
                position: relative;
            }
            #${MODAL_ID} #${VIDEO_ID} video {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover;
            }
            #${MODAL_ID} .am-bc-status {
                padding: 12px 18px;
                font-size: 0.88rem;
                color: #555;
                text-align: center;
                background: #fdfafa;
                border-top: 1px solid #f3e6e7;
            }
            #${MODAL_ID} .am-bc-status.error { color: #b00020; background: #fdecea; }
            #${MODAL_ID} .am-bc-status.success { color: #1b5e20; background: #e8f5e9; }
            #${MODAL_ID} .am-bc-actions {
                padding: 12px 18px 16px;
                display: flex;
                gap: 8px;
                justify-content: flex-end;
                background: #fdfafa;
            }
            #${MODAL_ID} .am-bc-btn {
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 0.86rem;
                font-weight: 600;
                cursor: pointer;
                border: none;
                transition: all 0.18s;
            }
            #${MODAL_ID} .am-bc-btn-cancel {
                background: #fff;
                color: #555;
                border: 1.5px solid #ddd;
            }
            #${MODAL_ID} .am-bc-btn-cancel:hover { border-color: #F5A800; color: #F5A800; }
            #${MODAL_ID} .am-bc-btn-manual {
                background: #F5A800;
                color: #fff;
            }
            #${MODAL_ID} .am-bc-btn-manual:hover { background: #C58800; }

            body.dark-mode #${MODAL_ID} .am-bc-box { background: #2a2a2e; }
            body.dark-mode #${MODAL_ID} .am-bc-status { background: #232327; color: #d0d0d3; border-top-color: #3a3a3f; }
            body.dark-mode #${MODAL_ID} .am-bc-actions { background: #232327; }
            body.dark-mode #${MODAL_ID} .am-bc-btn-cancel { background: #2a2a2e; color: #d0d0d3; border-color: #4a4a4f; }
        `;
        const s = document.createElement('style');
        s.id = 'am-barcode-styles';
        s.textContent = css;
        document.head.appendChild(s);
    }

    // ── Inyectar el modal DOM una sola vez ─────────────────────
    function injectModal() {
        if (document.getElementById(MODAL_ID)) return;
        const div = document.createElement('div');
        div.id = MODAL_ID;
        div.innerHTML = `
            <div class="am-bc-box" role="dialog" aria-modal="true" aria-label="Escanear código de barras">
                <div class="am-bc-head">
                    <h5><i class="fas fa-barcode me-2"></i>Escanear código de barras</h5>
                    <button type="button" class="am-bc-close" aria-label="Cerrar">&times;</button>
                </div>
                <div id="${VIDEO_ID}"></div>
                <div id="${STATUS_ID}" class="am-bc-status">Iniciando cámara…</div>
                <div class="am-bc-actions">
                    <button type="button" class="am-bc-btn am-bc-btn-cancel">Cancelar</button>
                </div>
            </div>
        `;
        document.body.appendChild(div);

        div.querySelector('.am-bc-close').addEventListener('click', close);
        div.querySelector('.am-bc-btn-cancel').addEventListener('click', close);
        div.addEventListener('click', function (e) {
            if (e.target === div) close();
        });
    }

    function setStatus(text, type) {
        const el = document.getElementById(STATUS_ID);
        if (!el) return;
        el.textContent = text;
        el.className = 'am-bc-status' + (type ? ' ' + type : '');
    }

    function open(inputEl) {
        if (!inputEl) return;
        targetInput = inputEl;
        injectStyles();
        injectModal();

        const modal = document.getElementById(MODAL_ID);
        modal.classList.add('am-open');

        if (typeof Html5Qrcode === 'undefined') {
            setStatus('Librería de escaneo no cargada. Recargá la página.', 'error');
            return;
        }

        setStatus('Iniciando cámara…');

        scanner = new Html5Qrcode(VIDEO_ID);
        const config = {
            fps: 10,
            qrbox: { width: 280, height: 140 },
            aspectRatio: 1.333,
            // Formatos aceptados (1D barcodes principalmente)
            formatsToSupport: [
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.ITF,
                Html5QrcodeSupportedFormats.CODABAR,
                Html5QrcodeSupportedFormats.QR_CODE
            ]
        };

        scanner.start(
            { facingMode: 'environment' },  // cámara trasera por defecto
            config,
            onScanSuccess,
            // onScanError silenciado — se llama miles de veces mientras busca
            function () {}
        ).catch(function (err) {
            console.error('[BarcodeScanner] start error:', err);
            let msg = 'No se pudo acceder a la cámara.';
            if (String(err).indexOf('NotAllowed') !== -1) {
                msg = 'Permisos de cámara denegados. Habilitalos en el navegador.';
            } else if (String(err).indexOf('NotFound') !== -1) {
                msg = 'No se detectó cámara en este dispositivo.';
            } else if (String(err).indexOf('NotReadable') !== -1) {
                msg = 'La cámara está siendo usada por otra app. Cerrala e intentá de nuevo.';
            } else if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
                msg = 'El scanner requiere HTTPS para funcionar fuera de localhost.';
            }
            setStatus(msg, 'error');
        });
    }

    function onScanSuccess(decodedText) {
        if (!targetInput) return;
        setStatus('Código detectado: ' + decodedText, 'success');
        targetInput.value = decodedText;
        // Dispara evento input por si hay listeners atados
        targetInput.dispatchEvent(new Event('input', { bubbles: true }));
        targetInput.dispatchEvent(new Event('change', { bubbles: true }));

        // Vibración táctil si el dispositivo lo soporta
        if (navigator.vibrate) navigator.vibrate(150);

        // Cierra el scanner después de un breve delay para que el usuario vea el feedback
        setTimeout(close, 700);
    }

    function close() {
        const modal = document.getElementById(MODAL_ID);
        if (modal) modal.classList.remove('am-open');

        if (scanner) {
            scanner.stop().then(function () {
                scanner.clear();
                scanner = null;
            }).catch(function () {
                scanner = null;
            });
        }
        targetInput = null;
    }

    // ── API pública ────────────────────────────────────────────
    window.amBarcodeScanner = {
        open: open,
        close: close
    };
})();
