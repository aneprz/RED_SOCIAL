<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../../BD/conexiones.php';
session_start();

$usuario_id = $_SESSION['id'] ?? null;
if (!$usuario_id) die("Debes estar logueado.");

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
    die("Error al subir archivo.");
}

$file = $_FILES['file'];

$allowedMimes = ['image/jpeg','image/png','image/gif','video/mp4','video/webm'];
$allowedExts = ['jpg','jpeg','png','gif','mp4','webm'];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$realType = finfo_file($finfo, $file['tmp_name']);

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if(!in_array($realType, $allowedMimes) || !in_array($ext, $allowedExts)){
    die("Archivo no permitido.");
}

$maxSize = 100 * 1024 * 1024;
if ($file['size'] > $maxSize) die("Archivo demasiado grande.");

$filename = uniqid('post_') . "." . $ext;
$filepath = $uploadDir . $filename;

/* 🔹 OPTIMIZAR IMAGEN ANTES DE GUARDAR */
if (strpos($realType, 'image') === 0) {
    $image = imagecreatefromstring(file_get_contents($file['tmp_name']));
    $width = imagesx($image);
    $height = imagesy($image);

    $maxWidth = 1080;
    if ($width > $maxWidth) {
        $newHeight = ($height / $width) * $maxWidth;
        $resized = imagecreatetruecolor($maxWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
        imagejpeg($resized, $filepath, 85);
    } else {
        move_uploaded_file($file['tmp_name'], $filepath);
    }
} 
/* 🔹 GUARDAR VIDEO */
else {
    move_uploaded_file($file['tmp_name'], $filepath);
}

/* DATOS DEL FORM */
$caption = htmlspecialchars($_POST['caption'] ?? '', ENT_QUOTES);
$ubicacion = htmlspecialchars($_POST['ubicacion'] ?? '', ENT_QUOTES);
$tags_usernames = $_POST['tags_names'] ?? [];

/* INSERT POST */
$stmt = $conexion->prepare("
INSERT INTO publicaciones 
(usuario_id, imagen_url, pie_foto, ubicacion, fecha_publicacion, sals) 
VALUES (?, ?, ?, ?, NOW(), 0)
");

$stmt->bind_param("isss", $usuario_id, $filepath, $caption, $ubicacion);
$stmt->execute();
$post_id = $stmt->insert_id;
$stmt->close();

/* INSERT TAGS */
foreach($tags_usernames as $username){
    $username = ltrim($username, '@');

    $res = $conexion->prepare("SELECT id FROM usuarios WHERE username = ?");
    $res->bind_param("s", $username);
    $res->execute();
    $res->bind_result($etiquetado_id);

    if($res->fetch()){
        $res->close();
        $stmt2 = $conexion->prepare("
        INSERT INTO etiquetas_publicacion 
        (post_id, usuario_etiquetado_id) 
        VALUES (?, ?)
        ");
        $stmt2->bind_param("ii", $post_id, $etiquetado_id);
        $stmt2->execute();
        $stmt2->close();
    } else {
        $res->close();
    }
}

header("Location: ../../Php/Explorar/explorar.php");
exit;
?>
