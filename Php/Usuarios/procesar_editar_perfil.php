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

header("Location: perfil.php");
exit;
