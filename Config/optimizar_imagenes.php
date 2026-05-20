<?php

/**
 * optimizar_imagenes.php — Script CLI para convertir imágenes existentes a WebP
 *
 * Uso (desde la raíz del proyecto):
 *   php Config/optimizar_imagenes.php           → dry-run (solo lista)
 *   php Config/optimizar_imagenes.php --apply   → ejecuta la conversión
 *
 * Lo que hace:
 *   1. Recorre Content/Demo/img/ recursivamente
 *   2. Por cada .jpg/.jpeg/.png/.png encontrado:
 *      - Genera la versión .webp con el mismo nombre base
 *      - Si --apply: actualiza la BD reemplazando el nombre de archivo
 *        en productos, variantes, banners, clientes, usuarios
 *      - Borra el original (opcional con --delete-original)
 *   3. Reporta ahorro total de espacio
 */

if (php_sapi_name() !== 'cli') {
    die("Este script solo se ejecuta desde línea de comandos.\n");
}

// Cargar el sistema
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', realpath(__DIR__ . DS . '..') . DS);

require_once ROOT . 'Config' . DS . 'Define.php';
require_once ROOT . 'Config' . DS . 'AutoLoad.php';
require_once ROOT . 'Config' . DS . 'Core' . DS . 'ImageOptimizer.php';
AutoLoad::run();

$apply          = in_array('--apply', $argv ?? [], true);
$deleteOriginal = in_array('--delete-original', $argv ?? [], true);

echo str_repeat('═', 60) . "\n";
echo "OPTIMIZACIÓN DE IMÁGENES A WEBP\n";
echo "Modo: " . ($apply ? "APLICAR cambios" : "DRY-RUN (solo simular)") . "\n";
echo "Borrar originales: " . ($deleteOriginal ? "SÍ" : "NO") . "\n";
echo str_repeat('═', 60) . "\n\n";

// Carpetas a recorrer
$carpetas = [
    PRODUCT_IMAGE_UPLOAD_DIR,
    VARIANTE_IMAGE_UPLOAD_DIR,
    BANNER_IMAGE_UPLOAD_DIR,
    CLIENTE_IMAGE_UPLOAD_DIR,
    IMG_BASE_DIR . 'Usuarios' . DS,
];

$totalAntes  = 0;
$totalDespues = 0;
$convertidos = 0;
$saltados    = 0;
$fallidos    = 0;

foreach ($carpetas as $carpeta) {
    if (!is_dir($carpeta)) continue;
    echo ">>> Procesando: $carpeta\n";

    $files = glob($carpeta . '*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE) ?: [];
    if (empty($files)) {
        echo "    (sin archivos para convertir)\n\n";
        continue;
    }

    foreach ($files as $file) {
        $sizeAntes = filesize($file);
        $totalAntes += $sizeAntes;

        $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file);

        // Si ya existe el .webp, saltar
        if (file_exists($webpPath)) {
            echo "    [SKIP]   " . basename($file) . " (ya existe .webp)\n";
            $saltados++;
            continue;
        }

        if (!$apply) {
            echo "    [DRY]    " . basename($file) . " → " . basename($webpPath) . "\n";
            continue;
        }

        $resultado = ImageOptimizer::convertFile($file, $webpPath);
        if (!$resultado || !file_exists($webpPath)) {
            echo "    [FAIL]   " . basename($file) . "\n";
            $fallidos++;
            continue;
        }

        $sizeDespues = filesize($webpPath);
        $totalDespues += $sizeDespues;
        $ahorro = round((1 - $sizeDespues / $sizeAntes) * 100);
        echo "    [OK]     " . basename($file) . " → " . basename($webpPath)
           . " (" . number_format($sizeAntes/1024, 0) . "KB → "
           . number_format($sizeDespues/1024, 0) . "KB, -$ahorro%)\n";
        $convertidos++;

        // Actualizar la BD reemplazando el nombre del archivo
        try {
            $oldName = basename($file);
            $newName = basename($webpPath);
            $pdo = Conexion::getInstance();
            $tablas = ['productos', 'producto_variantes', 'combos', 'banners',
                       'galeria_clientes', 'users'];
            foreach ($tablas as $tabla) {
                // Detectar columna de imagen según la tabla
                $col = ($tabla === 'banners') ? 'imagen_url'
                     : ($tabla === 'galeria_clientes' ? 'imagen_url'
                     : ($tabla === 'combos' ? 'imagen_url'
                     : ($tabla === 'users' ? 'foto' : 'image_url')));
                $stmt = $pdo->prepare("UPDATE `$tabla` SET `$col` = ? WHERE `$col` = ?");
                $stmt->execute([$newName, $oldName]);
            }
        } catch (\Throwable $e) {
            echo "    [WARN]   BD update falló: " . $e->getMessage() . "\n";
        }

        // Borrar original si así lo pidieron
        if ($deleteOriginal && file_exists($file)) {
            @unlink($file);
        }
    }
    echo "\n";
}

echo str_repeat('═', 60) . "\n";
echo "RESUMEN\n";
echo "Convertidos:  $convertidos\n";
echo "Saltados:     $saltados\n";
echo "Fallidos:     $fallidos\n";
if ($apply && $totalAntes > 0) {
    $ahorroTotal = round((1 - $totalDespues / $totalAntes) * 100);
    echo "Tamaño antes: " . number_format($totalAntes/1024/1024, 2) . " MB\n";
    echo "Tamaño después: " . number_format($totalDespues/1024/1024, 2) . " MB\n";
    echo "Ahorro:       $ahorroTotal%\n";
}
echo str_repeat('═', 60) . "\n";

if (!$apply) {
    echo "\nFue DRY-RUN. Para ejecutar de verdad:\n";
    echo "  php Config/optimizar_imagenes.php --apply\n";
    echo "  php Config/optimizar_imagenes.php --apply --delete-original\n";
}
