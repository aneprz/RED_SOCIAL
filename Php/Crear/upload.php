<?php
require 'conexion.php'; // tu conexión a la base de datos

session_start();
$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) die("Debes estar logueado.");

// Carpeta uploads
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Validar archivo
if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) die("Error al subir archivo.");
$file = $_FILES['file'];

// Validar tipo
$allowedTypes = ['image/jpeg','image/png','image/gif','video/mp4','video/webm'];
if (!in_array($file['type'],$allowedTypes)) die("Tipo de archivo no permitido.");

// Guardar archivo
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('post_') . "." . $ext;
$filepath = $uploadDir . $filename;
move_uploaded_file($file['tmp_name'], $filepath);

// Recibir campos
$caption = $_POST['caption'] ?? '';
$ubicacion = $_POST['ubicacion'] ?? '';
$tags_usernames = $_POST['tags_names'] ?? [];
$tags_x = $_POST['tags_x'] ?? [];
$tags_y = $_POST['tags_y'] ?? [];

// Insertar en publicaciones
$stmt = $conn->prepare("INSERT INTO publicaciones (usuario_id, imagen_url, pie_foto, ubicacion, fecha_publicacion, sals) VALUES (?, ?, ?, ?, NOW(), 0)");
$stmt->bind_param("isss", $usuario_id, $filepath, $caption, $ubicacion);
$stmt->execute();
$post_id = $stmt->insert_id;

// Insertar etiquetas
foreach($tags_usernames as $i => $username) {
    $username = ltrim($username, '@'); // quitar @
    $res = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
    $res->bind_param("s", $username);
    $res->execute();
    $res->bind_result($etiquetado_id);
    if ($res->fetch()) {
        $res->close();
        $stmt2 = $conn->prepare("INSERT INTO etiquetas_publicacion (post_id, usuario_etiquetado_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $post_id, $etiquetado_id);
        $stmt2->execute();
        $stmt2->close();
    }
}

// OK
echo "¡Publicación creada!";
