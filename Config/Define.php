<?php

/**
 * Define.php — Constantes globales del sistema
 * ZonaMarcol — DeskCod
 *
 * Único archivo donde se definen rutas y configuración base.
 * Todos los demás archivos consumen estas constantes, nunca hardcodean rutas.
 */

// ─────────────────────────────────────────────
// 1. SEPARADOR DE DIRECTORIOS
// ─────────────────────────────────────────────
define('DS', DIRECTORY_SEPARATOR);

// ─────────────────────────────────────────────
// 2. RUTAS DEL SISTEMA DE ARCHIVOS
// ROOT apunta a la raíz del proyecto
// ─────────────────────────────────────────────
define('ROOT',             realpath(__DIR__ . DS . '..') . DS);
define('CONFIG_PATH',      ROOT . 'Config'      . DS);
define('CORE_PATH',        CONFIG_PATH . 'Core' . DS);
define('CONTROLLERS_PATH', ROOT . 'Controllers' . DS);
define('MODELS_PATH',      ROOT . 'Models'      . DS);
define('VIEWS_PATH',       ROOT . 'Views'       . DS);
define('TEMPLATE_PATH',    ROOT . 'Template'    . DS);
define('CONTENT_PATH',     ROOT . 'Content'     . DS);
define('BD_PATH',          ROOT . 'BD'          . DS);
define('ENTITY_PATH',      ROOT . 'Entity'      . DS);

// ─────────────────────────────────────────────
// 3. RUTAS DE IMÁGENES
// Rutas físicas para guardar archivos subidos
// ─────────────────────────────────────────────
define('IMG_BASE_DIR',              CONTENT_PATH . 'Demo' . DS . 'img' . DS);
define('PRODUCT_IMAGE_UPLOAD_DIR',  IMG_BASE_DIR . 'Productos'  . DS);
define('VARIANTE_IMAGE_UPLOAD_DIR', IMG_BASE_DIR . 'Variantes'  . DS);
define('BANNER_IMAGE_UPLOAD_DIR',   IMG_BASE_DIR . 'Banners'    . DS);
define('CLIENTE_IMAGE_UPLOAD_DIR',  IMG_BASE_DIR . 'Clientes'   . DS);
// Reservados para próximos módulos
define('SERVICIO_IMAGE_UPLOAD_DIR', IMG_BASE_DIR . 'Servicios'  . DS); // catálogo de servicios técnicos
define('EQUIPO_LOGO_UPLOAD_DIR',    IMG_BASE_DIR . 'Equipos'    . DS); // escudos de equipos
define('LIGA_LOGO_UPLOAD_DIR',      IMG_BASE_DIR . 'Ligas'      . DS); // logos de ligas
define('COMPETICION_PARCHE_DIR',    IMG_BASE_DIR . 'Competiciones' . DS); // parches
define('EQUIPACION_IMAGE_UPLOAD_DIR', IMG_BASE_DIR . 'Equipaciones' . DS); // imágenes de camisas

// ─────────────────────────────────────────────
// 4. URL BASE — Se calcula automáticamente
// Funciona en localhost, subdirectorio y Ubuntu
// sin modificar nada entre entornos
// ─────────────────────────────────────────────
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base     = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';

define('APP_URL', $protocol . '://' . $host . $base);

// ─────────────────────────────────────────────
// 5. BASE DE DATOS
// ─────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'zonamarcol');
define('DB_USER',    'ZonaMarcol');
define('DB_PASSWORD','AaPR2005');
define('DB_PASS',    DB_PASSWORD);
define('DB_CHARSET', 'utf8mb4');

// ─────────────────────────────────────────────
// 6. CONFIGURACIÓN DE LA APLICACIÓN
// ─────────────────────────────────────────────
define('APP_NAME',    getenv('APP_NAME') ?: 'Zona Marcol');
define('APP_VERSION', '1.0.0');
define('APP_LOCALE',  'es_HN');

// ─────────────────────────────────────────────
// 7. WHATSAPP
// Número centralizado — único lugar para cambiar
// Formato internacional sin + ni espacios
// ─────────────────────────────────────────────
define('WA_NUMBER', getenv('WA_NUMBER') ?: '50499873125');

// ─────────────────────────────────────────────
// 8. CONFIGURACIÓN DE SESIÓN
// SESSION_NAME único por sistema — evita conflictos
// entre proyectos en el mismo servidor
// ─────────────────────────────────────────────
define('SESSION_NAME',     'zonamarcol_session');
define('SESSION_LIFETIME', 3600);

// ─────────────────────────────────────────────
// 9. RUTAS PÚBLICAS
// No requieren autenticación del panel admin
// El index.php raíz las usa para el control de acceso
// ─────────────────────────────────────────────
define('PUBLIC_ROUTES', [
    'login',
    'auth',
    'tienda',
    'api',
]);

// ─────────────────────────────────────────────
// 10. ESTADOS DE PEDIDOS (e-commerce)
// ─────────────────────────────────────────────
define('PEDIDO_PENDIENTE',   'Pendiente');
define('PEDIDO_PREPARACION', 'En preparacion');
define('PEDIDO_LISTO',       'Listo');
define('PEDIDO_EN_CAMINO',   'En camino');
define('PEDIDO_ENTREGADO',   'Entregado');
define('PEDIDO_CANCELADO',   'Cancelado');

// ─────────────────────────────────────────────
// 11. ESTADOS DE SERVICIO TÉCNICO (mantenimiento)
// Flujo rígido: Recibido → Diagnóstico → Esperando aprobación
//             → En reparación → Listo → Entregado
// Cancelado se permite en cualquier punto previo a Entregado.
// ─────────────────────────────────────────────
define('OS_RECIBIDO',              'Recibido');
define('OS_DIAGNOSTICO',           'Diagnostico');
define('OS_ESPERANDO_APROBACION',  'Esperando aprobacion');
define('OS_EN_REPARACION',         'En reparacion');
define('OS_LISTO',                 'Listo');
define('OS_ENTREGADO',             'Entregado');
define('OS_CANCELADO',             'Cancelado');

// ─────────────────────────────────────────────
// 12. GARANTÍAS POST-ENTREGA (días)
// ─────────────────────────────────────────────
define('GARANTIA_SERVICIO_DIAS', 30); // limpieza, mantenimiento general
define('GARANTIA_REPUESTO_DIAS', 60); // piezas reemplazadas

// ─────────────────────────────────────────────
// 13. ESTADOS DE PEDIDOS DE CAMISETAS (broker)
// Pendiente_pago → Confirmado → En_proveedor → Recibido → Entregado
// Cancelado en cualquier punto previo a Entregado.
// ─────────────────────────────────────────────
define('PC_PENDIENTE_PAGO', 'Pendiente_pago');
define('PC_CONFIRMADO',     'Confirmado');
define('PC_EN_PROVEEDOR',   'En_proveedor');
define('PC_RECIBIDO',       'Recibido');
define('PC_ENTREGADO',      'Entregado');
define('PC_CANCELADO',      'Cancelado');

// Porcentaje de anticipo obligatorio
define('CAMISETA_ANTICIPO_PCT', 50);

// ─────────────────────────────────────────────
// 14. MÉTODOS DE PAGO
// ─────────────────────────────────────────────
define('PAGO_EFECTIVO',      'Efectivo');
define('PAGO_TARJETA',       'Tarjeta');
define('PAGO_TRANSFERENCIA', 'Transferencia');

// ─────────────────────────────────────────────
// 15. ENTORNO
// 'development' → muestra errores en pantalla
// 'production'  → oculta errores, los loguea
//
// Cambiar a 'production' antes de subir al servidor
// ─────────────────────────────────────────────
define('APP_ENV', getenv('APP_ENV') ?: 'development');

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
    ini_set('log_errors', 1);
}

// ─────────────────────────────────────────────
// HELPERS GLOBALES
// ─────────────────────────────────────────────

if (!function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $map  = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
                 'ñ'=>'n','ü'=>'u','à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u'];
        $text = strtr($text, $map);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}
