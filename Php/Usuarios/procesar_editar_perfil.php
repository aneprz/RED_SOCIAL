<?php
session_start();
include '../../BD/conexiones.php';

$nuevousu   = $_POST['nuevousu'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$biografia  = $_POST['biografia'] ?? '';
$id = $_SESSION['id'] ?? '';

if (!$id) {
    return;
}

/* ---------- CONTRASEÑA ---------- */
if (!empty($contrasena)) {
    $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);
    mysqli_query(
        $conexion,  
        "UPDATE usuarios SET password_hash = '$contrasenaHash' WHERE id = $id"
    );
}

/* ---------- USERNAME ---------- */
if (!empty($nuevousu)) {
    mysqli_query(
        $conexion,
        "UPDATE usuarios SET username = '$nuevousu' WHERE id = $id"
    );
    $_SESSION['username'] = $nuevousu;
}

/* ---------- BIOGRAFÍA ---------- */
if (!empty($biografia)) {
    mysqli_query(
        $conexion,
        "UPDATE usuarios SET bio = '$biografia' WHERE id = $id"
    );
}
