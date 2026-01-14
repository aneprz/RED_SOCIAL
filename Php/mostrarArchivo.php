<?php
// mostrar_archivo.php

// Revisamos que venga el parámetro 'f'
if (!isset($_GET['f']) || empty($_GET['f'])) {
    http_response_code(400);
    echo "Archivo no especificado";
    exit;
}

$archivo = basename($_GET['f']); // evitar rutas maliciosas
$ruta = __DIR__ . '/../Crear/uploads/' . $archivo; // ruta real al archivo

// Verificamos que el archivo exista
if (!file_exists($ruta)) {
    http_response_code(404);
    echo "Archivo no encontrado";
    exit;
}

// Detectamos el tipo MIME
$ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
switch ($ext) {
    case 'jpg':
    case 'jpeg':
        $tipo = 'image/jpeg';
        break;
    case 'png':
        $tipo = 'image/png';
        break;
    case 'gif':
        $tipo = 'image/gif';
        break;
    case 'mp4':
        $tipo = 'video/mp4';
        break;
    case 'webm':
        $tipo = 'video/webm';
        break;
    default:
        $tipo = 'application/octet-stream';
        break;
}

// Cabeceras para que el navegador interprete el archivo
header('Content-Type: ' . $tipo);
header('Content-Length: ' . filesize($ruta));
header('Cache-Control: max-age=3600');

// Leemos y enviamos el archivo
readfile($ruta);
exit;
