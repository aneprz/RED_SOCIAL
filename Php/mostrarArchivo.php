<?php
if (!isset($_GET['f']) || empty($_GET['f'])) {
    http_response_code(400);
    exit;
}

$archivo = str_replace(['..', '\\'], '', $_GET['f']);

$baseDir = realpath(__DIR__ . '/../Crear/uploads');
$ruta = realpath($baseDir . '/' . $archivo);

if ($ruta === false || strpos($ruta, $baseDir) !== 0 || !file_exists($ruta)) {
    http_response_code(404);
    exit;
}

$ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
$mime = [
    'jpg' => 'image/jpeg',
    'jpeg'=> 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'mp4' => 'video/mp4',
    'webm'=> 'video/webm',
][$ext] ?? 'application/octet-stream';

header("Content-Type: $mime");
header("Content-Length: " . filesize($ruta));
readfile($ruta);
exit;
