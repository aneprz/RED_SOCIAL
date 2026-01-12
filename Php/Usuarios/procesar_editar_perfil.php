<?php
session_start();
include '../../BD/conexiones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

$id = $_SESSION['id'] ?? '';
if (!$id) {
    return;
}

$foto_perfil = $_POST['foto_perfil'] ?? '';
$nuevousu  = $_POST['nuevousu'] ?? '';
$biografia = $_POST['biografia'] ?? '';


/* ========= VALIDAR USUARIO ========= */
if (!empty($nuevousu)) {

    $nuevousu = mysqli_real_escape_string($conexion, $nuevousu);

    $consulta = mysqli_query(
        $conexion,
        "SELECT id FROM usuarios WHERE username='$nuevousu' AND id != $id"
    );

    if (mysqli_num_rows($consulta) > 0) {
        echo "<script>
            alert('Nombre en uso');
            history.back();
        </script>";
        exit;
    }

    mysqli_query(
        $conexion,
        "UPDATE usuarios SET username='$nuevousu' WHERE id=$id"
    );

    $_SESSION['username'] = $nuevousu;
}


/* ========= BIOGRAFÍA ========= */
if (!empty($biografia)) {
    $biografia = mysqli_real_escape_string($conexion, $biografia);
    $biografia = mb_substr($biografia, 0, 150, 'UTF-8');

    mysqli_query(
        $conexion,
        "UPDATE usuarios SET bio='$biografia' WHERE id=$id"
    );

    $_SESSION['biografia'] = $biografia;
}


/* ========= FOTO PERFIL ========= */
if (!empty($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {

    $file = $_FILES['foto_perfil'];

    $allowedMimes = ['image/jpeg','image/png','image/gif'];
    $allowedExts  = ['jpg','jpeg','png','gif'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realType = finfo_file($finfo, $file['tmp_name']);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($realType, $allowedMimes) || !in_array($ext, $allowedExts)) {
        die("Archivo no permitido.");
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) die("Archivo demasiado grande.");

    $uploadDir = __DIR__ . '/fotosDePerfil/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $filename = uniqid('perfil_', true) . '.' . $ext;
    $filepath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        die("Error al guardar la imagen.");
    }

    // Guardamos la ruta relativa para usarla en HTML
    $foto_perfil_db = 'fotosDePerfil/' . $filename;

    mysqli_query($conexion, "UPDATE usuarios SET foto_perfil='$foto_perfil_db' WHERE id=$id");
    $_SESSION['foto_perfil'] = $foto_perfil_db;
}

header("Location: perfil.php");
exit;
