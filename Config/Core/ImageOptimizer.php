<?php

/**
 * ImageOptimizer.php — Conversión y redimensionado de imágenes a WebP
 *
 * Centraliza el procesamiento de imágenes subidas (productos, banners,
 * combos, galería, clientes, etc.) con dos objetivos:
 *
 *   1. PESO — convierte JPG/PNG a WebP (típicamente 70-80% más pequeñas
 *      con la misma calidad visual). Una imagen de 2 MB pasa a ~250 KB.
 *
 *   2. DIMENSIONES — redimensiona al ancho máximo configurado (1920px
 *      por defecto). Lo que sube el usuario rara vez se ve a más de
 *      ese tamaño en la app, así que guardar 4000px es desperdicio.
 *
 * Usa la extensión GD nativa de PHP (sin Composer ni librerías externas).
 *
 * Uso típico desde un Controller:
 *
 *   $nombreFinal = ImageOptimizer::process(
 *       $_FILES['imagen'],
 *       PRODUCT_IMAGE_UPLOAD_DIR,
 *       'prod_'
 *   );
 *   if ($nombreFinal === null) {
 *       // Error: leer ImageOptimizer::$lastError para el motivo real
 *   }
 */
class ImageOptimizer
{
    // Ancho máximo en píxeles — 1920 (Full HD).
    // Imágenes más grandes se redimensionan manteniendo aspect ratio.
    // 1920px es más que suficiente para cualquier pantalla actual; nadie ve
    // imágenes a más resolución que eso en la app. Imágenes de 4000px+
    // ocupan 10MB+ sin beneficio visible — solo retrasan la carga.
    private const MAX_WIDTH = 1920;

    // Calidad WebP — 92 es "visualmente indistinguible del original".
    // Comparativa de un JPEG típico de 2 MB:
    //   - Calidad 75 → 250 KB, pérdida levemente visible al hacer zoom
    //   - Calidad 82 → 350 KB, sin pérdida en uso normal
    //   - Calidad 92 → 600 KB, idéntico al ojo humano (RECOMENDADO)
    //   - Calidad 100 → 1.2 MB, sin pérdida (gasta espacio por nada)
    // 92 da ~60% de ahorro sin que se note diferencia.
    private const QUALITY = 92;

    // Tamaño máximo del archivo de entrada (10 MB).
    // Debe ir alineado con upload_max_filesize de PHP. El optimizador comprime
    // fuerte después (10 MB en crudo → cientos de KB como WebP), así que un
    // límite holgado de entrada no impacta el almacenamiento final.
    private const MAX_INPUT_SIZE = 10 * 1024 * 1024;

    // Motivo del último fallo de process(). El Controller lo lee para mostrar
    // un mensaje real en vez de uno genérico. null = sin error / éxito.
    public static ?string $lastError = null;

    /**
     * Procesa un archivo subido vía $_FILES y lo guarda como .webp.
     *
     * @param array  $file        Elemento de $_FILES (con 'tmp_name', 'size', etc.)
     * @param string $destinoDir  Carpeta destino — debe terminar en DS
     * @param string $prefijo     Prefijo del nombre (ej. 'prod_', 'banner_')
     * @return string|null        Nombre del archivo guardado, o null si falla
     */
    public static function process(array $file, string $destinoDir, string $prefijo = 'img_'): ?string
    {
        self::$lastError = null;

        // 1. Validaciones básicas
        // El código de error de $_FILES distingue el motivo real: si la imagen
        // supera upload_max_filesize, PHP entrega error = UPLOAD_ERR_INI_SIZE.
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if (empty($file) || $err !== UPLOAD_ERR_OK) {
            self::$lastError = self::uploadErrorMessage($err);
            return null;
        }
        if (($file['size'] ?? 0) > self::MAX_INPUT_SIZE) {
            $maxMb = (int) (self::MAX_INPUT_SIZE / 1024 / 1024);
            self::$lastError = "La imagen pesa más de {$maxMb} MB. Redúcela e intenta de nuevo.";
            return null;
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            self::$lastError = 'Origen de archivo inválido.';
            return null;
        }

        // 2. Validación MIME real (no se confía en la extensión)
        $mime = self::detectMime($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            self::$lastError = 'Formato no permitido. Usa JPG, PNG o WEBP.';
            return null;
        }

        // 3. Si GD no está disponible o no soporta WebP, fallback a guardar tal cual
        $gdAvailable = function_exists('gd_info');
        $canCreate = function_exists('imagecreatefromjpeg') || function_exists('imagecreatefrompng') || function_exists('imagecreatefromwebp');
        $canSaveWebP = function_exists('imagewebp');
        if (!$gdAvailable || !$canCreate || !$canSaveWebP) {
            $nombre = self::fallbackMove($file, $destinoDir, $prefijo);
            if ($nombre === null) {
                self::$lastError = 'No se pudo guardar la imagen (GD/WebP no disponible).';
            }
            return $nombre;
        }

        // 4. Cargar la imagen original según su MIME
        $img = self::loadImage($file['tmp_name'], $mime);
        if ($img === null) {
            self::$lastError = 'La imagen está dañada o no se pudo leer.';
            return null;
        }

        // 5. Redimensionar si excede MAX_WIDTH
        $img = self::resizeIfNeeded($img);

        // 6. Generar nombre único con extensión .webp (sin importar el origen)
        $nombreArchivo = uniqid($prefijo, true) . '.webp';
        $rutaCompleta  = rtrim($destinoDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nombreArchivo;

        // Asegurar que el directorio destino exista y sea escribible
        if (!is_dir($destinoDir)) {
            @mkdir($destinoDir, 0755, true);
        }
        if (!is_dir($destinoDir) || !is_writable($destinoDir)) {
            imagedestroy($img);
            self::$lastError = 'No se pudo guardar la imagen: destino no disponible o no escribible.';
            error_log('[ImageOptimizer] Destino no escribible: ' . $destinoDir);

            // Registro diagnóstico en proyecto para facilitar debugging local
            try {
                $debug = [
                    'time' => date('c'),
                    'destino' => $destinoDir,
                    'realpath' => realpath($destinoDir) ?: null,
                    'is_dir' => is_dir($destinoDir),
                    'is_writable' => is_writable($destinoDir),
                    'php_sapi' => PHP_SAPI,
                    'php_os' => PHP_OS,
                    'user' => get_current_user(),
                    'cwd' => getcwd(),
                ];
                $debugFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'image_optimizer_debug.log';
                @file_put_contents($debugFile, json_encode($debug, JSON_PRETTY_PRINT) . "\n", FILE_APPEND | LOCK_EX);
            } catch (\Throwable $e) {
                // no-op: no queremos fallar por el logger
            }

            return null;
        }

        // 7. Guardar como WebP
        $ok = @imagewebp($img, $rutaCompleta, self::QUALITY);
        imagedestroy($img);

        if (!$ok) {
            // Intentar identificar el error y hacer fallback a mover el archivo original
            $last = error_get_last();
            $detail = $last['message'] ?? '';
            $msg = 'No se pudo procesar la imagen (conversión a WebP).';
            if ($detail) $msg .= ' ' . $detail;
            self::$lastError = $msg;
            error_log('[ImageOptimizer] imagewebp failed: ' . $detail . ' — destino: ' . $rutaCompleta);

            // Fallback: intentar mover el archivo subido tal cual (mantener extensión original)
            $extension = strtolower(pathinfo($file['name'] ?? 'img.jpg', PATHINFO_EXTENSION));
            $extension = $extension ?: 'jpg';
            $fallbackName = uniqid($prefijo, true) . '.' . $extension;
            $fallbackPath = rtrim($destinoDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fallbackName;

            if (isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
                if (@move_uploaded_file($file['tmp_name'], $fallbackPath)) {
                    // Devolver el nombre fallback y conservar el mensaje de error para depuración
                    return $fallbackName;
                }
            }

            return null;
        }
        return $nombreArchivo;
    }

    /**
     * Traduce un código de error de $_FILES[...]['error'] a un mensaje legible.
     * Centraliza el mapeo para que los Controllers no lo dupliquen.
     */
    private static function uploadErrorMessage(int $err): string
    {
        switch ($err) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'La imagen supera el límite de subida del servidor. '
                     . 'Sube upload_max_filesize en php.ini o usa una imagen más liviana.';
            case UPLOAD_ERR_PARTIAL:
                return 'La subida se interrumpió. Intenta de nuevo.';
            case UPLOAD_ERR_NO_FILE:
                return 'No se seleccionó ninguna imagen.';
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
                return 'Error del servidor al recibir el archivo.';
            default:
                return 'No se pudo subir la imagen.';
        }
    }

    /**
     * Convierte un archivo existente en disco a WebP.
     * Usado por el script batch de migración de imágenes viejas.
     */
    public static function convertFile(string $sourcePath, ?string $destinoPath = null): ?string
    {
        if (!is_readable($sourcePath)) return null;
        if (!function_exists('imagewebp')) return null;

        $mime = self::detectMime($sourcePath);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        // Verificar funciones específicas para el MIME
        if ($mime === 'image/jpeg' && !function_exists('imagecreatefromjpeg')) return null;
        if ($mime === 'image/png'  && !function_exists('imagecreatefrompng'))  return null;
        if ($mime === 'image/webp' && !function_exists('imagecreatefromwebp')) return null;

        $img = self::loadImage($sourcePath, $mime);
        if ($img === null) return null;

        $img = self::resizeIfNeeded($img);

        // Forzar extensión .webp en destino para evitar confusiones
        if ($destinoPath === null) {
            $destinoPath = preg_replace('/\.(jpe?g|png|webp)$/i', '.webp', $sourcePath);
        } else {
            $destinoPath = preg_replace('/\.(jpe?g|png|webp)$/i', '.webp', $destinoPath);
            if (!preg_match('/\.webp$/i', $destinoPath)) {
                $destinoPath .= '.webp';
            }
        }

        // Asegurar directorio destino
        $destDir = dirname($destinoPath);
        if (!is_dir($destDir)) @mkdir($destDir, 0755, true);
        if (!is_dir($destDir) || !is_writable($destDir)) {
            imagedestroy($img);
            error_log('[ImageOptimizer] destino no escribible: ' . $destDir);
            return null;
        }

        $ok = @imagewebp($img, $destinoPath, self::QUALITY);
        imagedestroy($img);

        return $ok ? $destinoPath : null;
    }

    // ─────────────────────────────────────────────
    // INTERNOS
    // ─────────────────────────────────────────────

    private static function detectMime(string $path): string
    {
        if (!function_exists('finfo_open')) {
            return mime_content_type($path) ?: '';
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $path);
        finfo_close($finfo);
        return $mime ?: '';
    }

    /**
     * Crea un recurso GD a partir del archivo según su MIME.
     * Devuelve null si la imagen está corrupta.
     */
    private static function loadImage(string $path, string $mime)
    {
        try {
            switch ($mime) {
                case 'image/jpeg':
                    return @imagecreatefromjpeg($path) ?: null;
                case 'image/png':
                    $img = @imagecreatefrompng($path);
                    if (!$img) return null;
                    // Preservar transparencia al convertir a WebP
                    imagepalettetotruecolor($img);
                    imagealphablending($img, true);
                    imagesavealpha($img, true);
                    return $img;
                case 'image/webp':
                    return @imagecreatefromwebp($path) ?: null;
                default:
                    return null;
            }
        } catch (\Throwable $e) {
            error_log('[ImageOptimizer::loadImage] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Si la imagen excede MAX_WIDTH, la redimensiona manteniendo aspect ratio.
     * Si ya está dentro del límite, devuelve el recurso original.
     */
    private static function resizeIfNeeded($img)
    {
        $w = imagesx($img);
        $h = imagesy($img);

        if ($w <= self::MAX_WIDTH) return $img;

        $newW = self::MAX_WIDTH;
        $newH = (int) round($h * ($newW / $w));

        $resized = imagecreatetruecolor($newW, $newH);
        // Preservar transparencia
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefilledrectangle($resized, 0, 0, $newW, $newH, $transparent);

        imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
        imagedestroy($img);

        return $resized;
    }

    /**
     * Fallback cuando GD no está disponible — mueve el archivo tal cual.
     * No debería usarse en producción, pero evita romper uploads si el
     * server no tiene GD instalado (XAMPP/PHP-FPM lo trae por default).
     */
    private static function fallbackMove(array $file, string $destinoDir, string $prefijo): ?string
    {
        $extension = strtolower(pathinfo($file['name'] ?? 'img.jpg', PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) return null;

        $nombre  = uniqid($prefijo, true) . '.' . $extension;
        $destino = rtrim($destinoDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nombre;

        return move_uploaded_file($file['tmp_name'], $destino) ? $nombre : null;
    }

    /**
     * Guarda el upload tal cual (sin convertir). Valida tamaño y MIME.
     * Útil cuando la conversión a WebP no funciona en el servidor.
     * Devuelve el nombre de archivo guardado o null si falla.
     */
    public static function saveUploadedRaw(array $file, string $destinoDir, string $prefijo = 'img_'): ?string
    {
        // Validaciones básicas similares a process()
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if (empty($file) || $err !== UPLOAD_ERR_OK) {
            self::$lastError = self::uploadErrorMessage($err);
            return null;
        }
        if (($file['size'] ?? 0) > self::MAX_INPUT_SIZE) {
            $maxMb = (int) (self::MAX_INPUT_SIZE / 1024 / 1024);
            self::$lastError = "La imagen pesa más de {$maxMb} MB. Redúcela e intenta de nuevo.";
            return null;
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            self::$lastError = 'Origen de archivo inválido.';
            return null;
        }

        $mime = self::detectMime($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            self::$lastError = 'Formato no permitido. Usa JPG, PNG o WEBP.';
            return null;
        }

        if (!is_dir($destinoDir)) @mkdir($destinoDir, 0755, true);
        if (!is_dir($destinoDir) || !is_writable($destinoDir)) {
            self::$lastError = 'No se pudo guardar la imagen: destino no disponible o no escribible.';
            error_log('[ImageOptimizer::saveUploadedRaw] Destino no escribible: ' . $destinoDir);
            return null;
        }

        $extension = strtolower(pathinfo($file['name'] ?? 'img.jpg', PATHINFO_EXTENSION)) ?: 'jpg';
        $nombre  = uniqid($prefijo, true) . '.' . $extension;
        $destino = rtrim($destinoDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nombre;

        return move_uploaded_file($file['tmp_name'], $destino) ? $nombre : (self::$lastError = 'No se pudo mover el archivo.', null);
    }
}
