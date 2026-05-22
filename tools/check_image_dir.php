<?php
require_once __DIR__ . '/../Config/Define.php';

$dir = PRODUCT_IMAGE_UPLOAD_DIR;
$info = [
    'time' => date('c'),
    'dir' => $dir,
    'realpath' => realpath($dir) ?: null,
    'exists' => is_dir($dir),
    'is_writable' => is_writable($dir),
    'php_sapi' => PHP_SAPI,
    'php_os' => PHP_OS,
    'user' => get_current_user(),
    'cwd' => getcwd(),
];
header('Content-Type: application/json');
echo json_encode($info, JSON_PRETTY_PRINT);
