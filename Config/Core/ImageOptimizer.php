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
 *   2. DIMENSIONES — redimensiona al ancho máximo configurado (1200px
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
 *       // Error: archivo inválido / sin GD / fallo de conversión
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

    // Tamaño máximo del archivo de entrada (5 MB).
    private const MAX_INPUT_SIZE = 5 * 1024 * 1024;

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
        // 1. Validaciones básicas
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        if (($file['size'] ?? 0) > self::MAX_INPUT_SIZE) return null;
        if (!is_uploaded_file($file['tmp_name'])) return null;

        // 2. Validación MIME real (no se confía en la extensión)
        $mime = self::detectMime($file['tmp_name']);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        // 3. Si GD no está disponible, fallback a guardar tal cual
        if (!function_exists('imagecreatefromjpeg') ||
            !function_exists('imagewebp')) {
            return self::fallbackMove($file, $destinoDir, $prefijo);
        }

        // 4. Cargar la imagen original según su MIME
        $img = self::loadImage($file['tmp_name'], $mime);
        if ($img === null) return null;

        // 5. Redimensionar si excede MAX_WIDTH
        $img = self::resizeIfNeeded($img);

        // 6. Generar nombre único con extensión .webp (sin importar el origen)
        $nombreArchivo = uniqid($prefijo, true) . '.webp';
        $rutaCompleta  = rtrim($destinoDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nombreArchivo;

        // 7. Guardar como WebP
        $ok = @imagewebp($img, $rutaCompleta, self::QUALITY);
        imagedestroy($img);

        return $ok ? $nombreArchivo : null;
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

        $img = self::loadImage($sourcePath, $mime);
        if ($img === null) return null;

        $img = self::resizeIfNeeded($img);

        if ($destinoPath === null) {
            $destinoPath = preg_replace('/\.(jpe?g|png|webp)$/i', '.webp', $sourcePath);
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
}
