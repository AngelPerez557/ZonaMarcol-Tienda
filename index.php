<?php

/**
 * index.php — Front Controller
 * Punto de entrada único — toda petición pasa por aquí
 * ZonaMarcol — DeskCod
 */

// ─────────────────────────────────────────────
// 1. HEADERS DE SEGURIDAD HTTP
//
// F-10: Content-Security-Policy — segunda línea de defensa contra XSS
// F-11: X-XSS-Protection removido (deprecated, ignorado por navegadores modernos)
// F-12: Strict-Transport-Security solo cuando hay HTTPS real
// ─────────────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
// Permite cámara en mismo origen (necesario para scanner código de barras),
// el resto bloqueado por seguridad.
header('Permissions-Policy: geolocation=(), microphone=(), camera=(self), payment=()');

// ── F-12: HSTS — solo si la request real entra por HTTPS ─────
// Cuando el sistema se sirva por HTTPS detrás de un proxy, descomentar
// la línea de proxy y ajustar según infraestructura (X-Forwarded-Proto).
$esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
if ($esHttps) {
    // max-age 1 año + incluir subdominios. NO preload — eso requiere
    // submission manual en https://hstspreload.org y es irreversible.
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// ── F-10: Content-Security-Policy ───────────────────────────────
// Política estricta pero compatible con el stack actual:
//   - 'self' para todo lo propio
//   - cdn.jsdelivr.net y cdnjs.cloudflare.com (Bootstrap, FontAwesome, SweetAlert)
//   - 'unsafe-inline' en script-src/style-src — necesario para los <style>
//     y <script> inline que tiene el template actual. Cuando se migre todo a
//     archivos externos, esta directiva debe removerse.
//   - frame-ancestors 'self' refuerza X-Frame-Options
$csp = "default-src 'self'; "
     . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
     . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
     . "img-src 'self' data: https: blob:; "
     . "font-src 'self' https://cdnjs.cloudflare.com data:; "
     . "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
     // media-src + worker-src — necesarios para el scanner de código de barras
     // (getUserMedia genera blobs y html5-qrcode usa Web Workers internos)
     . "media-src 'self' blob: mediastream:; "
     . "worker-src 'self' blob:; "
     . "frame-ancestors 'self'; "
     . "form-action 'self'; "
     . "base-uri 'self'; "
     . "object-src 'none'";
header('Content-Security-Policy: ' . $csp);

// ─────────────────────────────────────────────
// 2. CORE
// ─────────────────────────────────────────────
require_once __DIR__ . '/Config/Define.php';
require_once __DIR__ . '/Config/AutoLoad.php';
require_once __DIR__ . '/Config/Core/Auth.php';
require_once __DIR__ . '/Config/Core/RateLimiter.php';
require_once __DIR__ . '/Config/JRequest.php';
require_once __DIR__ . '/Config/JRouter.php';

AutoLoad::run();

// ─────────────────────────────────────────────
// 3. SESIÓN SEGURA
// ─────────────────────────────────────────────
session_name(SESSION_NAME);

session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Timeout de sesión — 2 horas de inactividad ──────
define('SESSION_TIMEOUT', 7200);
if (isset($_SESSION['ultima_actividad'])) {
    if (time() - $_SESSION['ultima_actividad'] > SESSION_TIMEOUT) {
        $esCliente = isset($_SESSION['cliente']);
        session_unset();
        session_destroy();
        header('Location: ' . ($esCliente
            ? APP_URL . 'Tienda/login?expired=1'
            : APP_URL . 'Auth/index?expired=1'));
        exit();
    }
}
$_SESSION['ultima_actividad'] = time();

// ─────────────────────────────────────────────
// 4. RUTA ACTUAL
// ─────────────────────────────────────────────
$urlActual = strtolower(trim($_GET['url'] ?? '', '/'));
$segmento  = explode('/', $urlActual)[0];

// ─────────────────────────────────────────────
// 5. IGNORAR ASSETS DEL NAVEGADOR
// ─────────────────────────────────────────────
$assetsIgnorados = ['favicon.ico', 'robots.txt', 'sitemap.xml', 'apple-touch-icon.png'];
$requestUri      = strtolower($_SERVER['REQUEST_URI'] ?? '');

foreach ($assetsIgnorados as $asset) {
    if (str_contains($requestUri, $asset)) {
        http_response_code(404);
        exit();
    }
}

// ─────────────────────────────────────────────
// 6. LOGOUT (F-09)
//
// Antes el logout se disparaba con cualquier request a /Auth/logout (GET),
// vulnerable a "logout CSRF" — un <img src="/Auth/logout"> en un sitio
// externo deslogueaba al usuario.
//
// Ahora se delega al AuthController::logout(), que valida un token CSRF
// (vía ?csrf=... en GET o csrf_token=... en POST). Si el token no es válido,
// la sesión NO se cierra y se redirige al Dashboard.
// ─────────────────────────────────────────────
// (la interceptación directa fue eliminada — el JRouter se encarga de
//  enrutar /Auth/logout al método del controller que sí valida CSRF)

// ─────────────────────────────────────────────
// 7. CONTROL DE ACCESO
// ─────────────────────────────────────────────
$esRutaPublica = in_array($segmento, PUBLIC_ROUTES, true);

if (!Auth::isLoggedIn() && !$esRutaPublica) {
    $requestUriClean = $_SERVER['REQUEST_URI'] ?? '';
    if (!preg_match('/\.[a-z]{2,4}$/i', $requestUriClean)) {
        $_SESSION['redirect_after_login'] = $requestUriClean;
    }
    header('Location: ' . APP_URL . 'Auth/index');
    exit();
}

// ─────────────────────────────────────────────
// 8. VERIFICACIÓN DE CAJA ABIERTA
// DEBE ir antes de cargar el template para evitar
// "headers already sent" al hacer el redirect
// ─────────────────────────────────────────────
if (Auth::isLoggedIn() && $segmento === 'caja') {
    $metodoUrl = explode('/', $urlActual)[1] ?? 'index';

    // Solo verificamos en rutas que necesitan estado de caja
    if (in_array($metodoUrl, ['index', 'cierre', 'apertura'], true)) {
        $cajaSesionCheck = new CajaSesionModel();
        $sesionCheck     = $cajaSesionCheck->getSesionAbierta(Auth::id());

        // Sin caja abierta → solo puede ir a apertura
        if (!$sesionCheck && $metodoUrl !== 'apertura') {
            header('Location: ' . APP_URL . 'Caja/apertura');
            exit();
        }

        // Con caja abierta → no puede ir a apertura (ya está abierta)
        if ($sesionCheck && $metodoUrl === 'apertura') {
            header('Location: ' . APP_URL . 'Caja/index');
            exit();
        }
    }
}

// ─────────────────────────────────────────────
// 9. TEMPLATE + ROUTER
// ─────────────────────────────────────────────
$rutasSinTemplate = ['login', 'auth', 'tienda', 'api'];

// Rutas completas sin template
$rutasCompletasSinTemplate = ['caja/recibo', 'caja/resumen'];

// Métodos que retornan JSON — no cargar template
$metodosJson = ['toggle', 'delete', 'save', 'saveVariante', 'deleteVariante',
                'darkMode', 'buscar', 'barras', 'cobrar', 'search',
                'cambiarEstado', 'confirmarPago', 'saveProductos', 'saveConfig',
                'dia', 'verificar',
                'checkout', 'guardarRegistro', 'procesarLogin',
                'obtener', 'marcarLeida', 'marcarTodas', 'eliminar',
                'marcarTour', 'activarTour', 'verificarStock', 'toggleFavorito',
                'guardarPerfil', 'cambiarPassword', 'comentar',
                'anular', 'abrir', 'cerrar', 'toggleVisible'];

$metodoActual     = strtolower(explode('/', $urlActual)[1] ?? '');
$metodosJsonLower = array_map('strtolower', $metodosJson);

$esRutaCompletaSinTemplate = (bool) array_filter(
    $rutasCompletasSinTemplate,
    fn($r) => str_starts_with($urlActual, $r)
);

if (!in_array($segmento, $rutasSinTemplate, true)
    && !$esRutaCompletaSinTemplate
    && !in_array($metodoActual, $metodosJsonLower, true)) {
    require_once TEMPLATE_PATH . 'index.php';
}

JRouter::run(new JRequest());