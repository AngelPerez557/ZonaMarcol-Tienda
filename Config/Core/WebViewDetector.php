<?php

/**
 * WebViewDetector.php — Detecta navegadores in-app de redes sociales
 *
 * F-39 — Las apps de Instagram, Facebook, Messenger, TikTok, etc. usan
 * WebViews internos con restricciones que rompen sistemas web normales:
 *   - PWA no se puede instalar
 *   - Algunas cookies/sesiones fallan en iOS
 *   - Permisos de cámara/galería limitados
 *   - localStorage a veces corrupto
 *   - JavaScript con quirks impredecibles
 *
 * Estrategia mixta:
 *   - Permitir navegación normal dentro del WebView (visualizar la tienda)
 *   - Mostrar un banner sutil con "Abrir en tu navegador real" para
 *     acciones críticas (login admin, checkout, mi perfil cliente)
 *
 * Uso típico en Views:
 *
 *   <?php if (WebViewDetector::isInAppBrowser()): ?>
 *       <div class="banner-webview">
 *           Para una mejor experiencia,
 *           <a href="<?= WebViewDetector::openInBrowserUrl(APP_URL) ?>"
 *              target="_blank" rel="noopener">abre esta página en tu navegador</a>.
 *       </div>
 *   <?php endif; ?>
 */
class WebViewDetector
{
    /**
     * Retorna true si la request viene desde un navegador in-app de
     * red social conocida (Instagram, Facebook, Messenger, TikTok, etc.)
     */
    public static function isInAppBrowser(): bool
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if ($ua === '') {
            return false;
        }

        // Patrones de detección (orden importa — primero los más comunes)
        $patrones = [
            'Instagram',     // Instagram in-app browser
            'FBAN',          // Facebook iOS (FB App Native)
            'FBAV',          // Facebook iOS (FB App Version)
            'FB_IAB',        // Facebook Android in-app browser
            'FBIOS',         // Facebook iOS variants
            'Messenger',     // Facebook Messenger
            'MessengerLite',
            'TikTok',
            'BytedanceWebview', // TikTok subbrand
            'Twitter',
            'LinkedInApp',
            'WhatsApp',
            'WeChat',
            'MicroMessenger',// WeChat
            'Pinterest',
            'Line',
            'Snapchat',
        ];

        foreach ($patrones as $p) {
            if (stripos($ua, $p) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Identifica específicamente Instagram (para mensajes más precisos).
     */
    public static function isInstagram(): bool
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return stripos($ua, 'Instagram') !== false;
    }

    /**
     * Identifica el sistema operativo del visitante (para dar instrucciones
     * acertadas: "tocá los 3 puntos y elegí Chrome" en Android, etc.)
     */
    public static function getMobileOS(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
            return 'ios';
        }
        if (stripos($ua, 'Android') !== false) {
            return 'android';
        }
        return 'other';
    }

    /**
     * Genera la URL "abrir en navegador externo" según el sistema operativo.
     *
     * - Android: usa intent:// para abrir Chrome directamente
     * - iOS: no se puede forzar — se devuelve la misma URL y el usuario
     *        debe tocar "Abrir en Safari" desde el menú de Instagram (...)
     *
     * @param string $url URL a abrir (típicamente APP_URL o la URL actual)
     */
    public static function openInBrowserUrl(string $url): string
    {
        $os = self::getMobileOS();

        if ($os === 'android') {
            // Intent que fuerza Chrome
            $cleanUrl = preg_replace('#^https?://#i', '', $url);
            return 'intent://' . $cleanUrl
                 . '#Intent;scheme=https;package=com.android.chrome;end';
        }

        // iOS y desktop: devolver la misma URL.
        // El banner debe mostrar instrucciones manuales para iOS.
        return $url;
    }

    /**
     * Mensaje de instrucción según el sistema operativo del visitante.
     * Útil para Views que muestran un banner con texto contextualizado.
     */
    public static function instruccion(): string
    {
        $os = self::getMobileOS();

        if ($os === 'ios') {
            return 'Toca los tres puntos (•••) arriba a la derecha y elegí «Abrir en Safari».';
        }
        if ($os === 'android') {
            return 'Toca los tres puntos (⋮) arriba a la derecha y elegí «Abrir en navegador».';
        }
        return 'Copia el enlace y ábrelo en tu navegador habitual.';
    }
}
