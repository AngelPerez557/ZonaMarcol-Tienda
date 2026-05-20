<?php

/**
 * AutoLoad.php — Cargador automático de clases
 * Registra las carpetas del sistema para que PHP resuelva
 * cualquier clase automáticamente por su nombre, sin require_once manuales.
 */

class AutoLoad
{
    // ─────────────────────────────────────────────
    // 1. DIRECTORIOS DONDE PHP BUSCARÁ CLASES
    // ─────────────────────────────────────────────
    private static array $directories = [
        CONFIG_PATH,
        CORE_PATH,
        CONTROLLERS_PATH,
        MODELS_PATH,
        ROOT . 'Entity' . DS,
    ];

    // ─────────────────────────────────────────────
    // 2. REGISTRO DEL AUTOLOADER
    //
    // SEGURIDAD (F-14):
    // Antes de hacer file_exists, validamos $className contra una whitelist
    // de caracteres (letras, números, _, /, \) para evitar path traversal
    // y nombres con caracteres extraños que rompan el sistema de archivos.
    // ─────────────────────────────────────────────
    public static function run(): void
    {
        spl_autoload_register(function (string $className) {

            // ── DEFENSA EN PROFUNDIDAD (F-14) ──
            // Solo caracteres alfanuméricos, underscore y separadores de namespace
            if (!preg_match('/^[A-Za-z0-9_\\\\\/]+$/', $className)) {
                return;
            }

            // Normaliza namespaces a separador del SO
            // Ej: "Config\JRouter" → "Config/JRouter" (Linux) o "Config\JRouter" (Windows)
            $className = str_replace(['\\', '/'], DS, $className);

            // Defensa contra path traversal — bloquea ".." en el nombre normalizado
            if (str_contains($className, '..')) {
                return;
            }

            // Búsqueda en cada directorio registrado
            foreach (self::$directories as $directory) {

                $file = $directory . $className . '.php';

                // Si el archivo existe, lo carga y detiene la búsqueda
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }

                // Segunda búsqueda: elimina el segmento de carpeta del nombre de clase
                // Ej: "Controllers/EjemploController" → busca "EjemploController.php"
                $lastSep        = strrpos($className, DS);
                $classNameShort = $lastSep !== false
                    ? substr($className, $lastSep + 1)
                    : $className;
                $fileShort      = $directory . $classNameShort . '.php';

                if (file_exists($fileShort)) {
                    require_once $fileShort;
                    return;
                }
            }
        });
    }

    // ─────────────────────────────────────────────
    // 3. REGISTRO DE DIRECTORIO ADICIONAL EN TIEMPO DE EJECUCIÓN
    // Útil para módulos o plugins futuros
    // ─────────────────────────────────────────────
    public static function addDirectory(string $path): void
    {
        if (is_dir($path) && !in_array($path, self::$directories)) {
            self::$directories[] = rtrim($path, DS) . DS;
        }
    }
}
