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

if (!empty($nuevousu)) {
    mysqli_query($conexion,
        "UPDATE usuarios SET username='$nuevousu' WHERE id=$id"
    );
    $_SESSION['username'] = $nuevousu;
}

if (!empty($biografia)) {
    mysqli_query($conexion,
        "UPDATE usuarios SET bio='$biografia' WHERE id=$id"
    );
    $_SESSION['biografia'] = $biografia;
}

if (!empty($foto_perfil)) {
    if (filter_var($foto_perfil, FILTER_VALIDATE_URL)) {
        $foto_perfil = mysqli_real_escape_string($conexion, $foto_perfil);
        mysqli_query($conexion, "UPDATE usuarios SET foto_perfil='$foto_perfil' WHERE id=$id");
        $_SESSION['foto_perfil'] = $foto_perfil;
    } else {
        echo "<script>
            alert('Imagen no válida');
            history.back();
        </script>";
        exit;
    }
}
header("Location: perfil.php");
exit;
