<?php

/**
 * JRouter.php — Resuelve qué controlador y método ejecutar para cada URL
 *
 * SEGURIDAD (F-04):
 * Antes de instanciar una clase, el nombre se valida contra una
 * WHITELIST AUTO-GENERADA escaneando la carpeta /Controllers/ al boot.
 * Esto evita instanciar clases arbitrarias que pudieran existir en otros
 * directorios registrados por el AutoLoad (ej. una clase auxiliar con
 * sufijo "Controller" en /Models o /Config).
 *
 * La whitelist se cachea en memoria estática durante toda la request,
 * por lo que el costo del escaneo es mínimo (1 sola lectura de directorio).
 */
class JRouter
{
    // Controlador por defecto cuando la URL está vacía
    private static string $defaultController = 'DashboardController';

    // Método por defecto cuando no se especifica en la URL
    private static string $defaultMethod = 'index';

    // Cache de la whitelist de controladores válidos.
    // Se llena la primera vez que se llama a getControllerWhitelist().
    private static ?array $controllerWhitelist = null;

    // ─────────────────────────────────────────────
    // PUNTO DE ENTRADA DEL ENRUTADOR
    // ─────────────────────────────────────────────

    // Recibe el JRequest, resuelve el controlador y método, y los ejecuta.
    public static function run(JRequest $request): void
    {
        $controllerName = self::resolveController($request->getController());
        $methodName     = self::resolveMethod($request->getMethod());

        // ── 1. Whitelist — solo se instancian controladores reales ──
        // Aunque AutoLoad encontrara una clase con sufijo "Controller"
        // en cualquier otro directorio registrado, NO se permite ejecutarla.
        if (!in_array($controllerName, self::getControllerWhitelist(), true)) {
            self::notFound("Controlador '{$controllerName}' no autorizado.");
            return;
        }

        // ── 2. Existencia de la clase (defensa en profundidad) ──
        if (!class_exists($controllerName)) {
            self::notFound("Controlador '{$controllerName}' no encontrado.");
            return;
        }

        // ── 3. Instanciación y ejecución ──
        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            self::notFound("Método '{$methodName}' no encontrado en '{$controllerName}'.");
            return;
        }

        // Parámetros adicionales — segmentos 2 en adelante
        $params = self::resolveParams($request->getSegments());

        call_user_func_array([$controller, $methodName], $params);
    }

    // ─────────────────────────────────────────────
    // WHITELIST DE CONTROLADORES (F-04)
    // ─────────────────────────────────────────────

    // Lee la carpeta /Controllers/ una sola vez por request y retorna
    // los nombres de clase válidos (sin extensión .php).
    // Cachea el resultado en memoria estática.
    private static function getControllerWhitelist(): array
    {
        if (self::$controllerWhitelist !== null) {
            return self::$controllerWhitelist;
        }

        $whitelist = [];

        if (defined('CONTROLLERS_PATH') && is_dir(CONTROLLERS_PATH)) {
            // Solo archivos *.php directamente dentro de Controllers/
            // No incluye subdirectorios — el sistema no usa namespaces en routing.
            $files = glob(CONTROLLERS_PATH . '*Controller.php') ?: [];

            foreach ($files as $file) {
                $className = basename($file, '.php');
                // Defensa adicional: solo nombres con caracteres válidos
                if (preg_match('/^[A-Za-z0-9_]+Controller$/', $className)) {
                    $whitelist[] = $className;
                }
            }
        }

        self::$controllerWhitelist = $whitelist;
        return $whitelist;
    }

    // ─────────────────────────────────────────────
    // RESOLUCIÓN DE CONTROLADOR
    // ─────────────────────────────────────────────

    // Construye el nombre completo de la clase del controlador
    // Ej: "Ejemplo" → "EjemploController"
    private static function resolveController(string $name): string
    {
        if (empty($name)) {
            return self::$defaultController;
        }

        // Filtrar cualquier carácter no permitido — defensa en profundidad
        // antes de la whitelist (evita class_exists con paths raros).
        $name = preg_replace('/[^A-Za-z0-9_]/', '', $name);

        // Capitaliza el primer carácter para respetar convención de clase
        $name = ucfirst(strtolower($name));

        if (str_ends_with($name, 'Controller')) {
            return $name;
        }

        return $name . 'Controller';
    }

    // ─────────────────────────────────────────────
    // RESOLUCIÓN DE MÉTODO
    // ─────────────────────────────────────────────

    private static function resolveMethod(string $method): string
    {
        if (empty($method)) {
            return self::$defaultMethod;
        }

        // Filtrar caracteres no permitidos antes de method_exists.
        $method = preg_replace('/[^A-Za-z0-9_]/', '', $method);

        return lcfirst($method);
    }

    // ─────────────────────────────────────────────
    // RESOLUCIÓN DE PARÁMETROS
    // ─────────────────────────────────────────────

    // Segmento 0 = controlador, Segmento 1 = método, Segmento 2+ = parámetros
    private static function resolveParams(array $segments): array
    {
        return array_values(array_slice($segments, 2));
    }

    // ─────────────────────────────────────────────
    // MANEJO DE RUTAS NO ENCONTRADAS
    // ─────────────────────────────────────────────

    private static function notFound(string $message): void
    {
        http_response_code(404);

        if (defined('APP_ENV') && APP_ENV === 'development') {
            die("<h2 style='font-family:monospace;color:#c0392b;'>404 | {$message}</h2>");
        }

        $view404 = VIEWS_PATH . '404' . DS . 'index.php';

        if (file_exists($view404)) {
            require_once $view404;
        } else {
            die('<h2>404 | Página no encontrada.</h2>');
        }
    }
}
