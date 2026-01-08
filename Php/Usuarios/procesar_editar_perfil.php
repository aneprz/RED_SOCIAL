<?php
include '../../BD/conexiones.php';

$nuevousu   = $_POST['nuevousu'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$biografia  = $_POST['biografia'] ?? '';
$id=$_SESSION['id'] ?? '';
$nombreusu=$_SESSION['username'] ?? '';
$query = "SELECT foto_perfil, username FROM usuarios where id = $id";
$result = mysqli_query($conexion, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $usuario = mysqli_fetch_assoc($result);
    $foto_perfil = $usuario['foto_perfil'];
}
if (!empty($contrasena)) {
    $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);
    $sql = "UPDATE usuarios SET password_hash = '$contrasenaHash' WHERE id = $id";
    header("location: perfil.php");
    exit(); 
}
if (!empty($nuevousu)) {
    $sql = "UPDATE usuarios SET username = '$nuevousu' WHERE id = $id";
    header("location: perfil.php");
    exit(); 
}
if (!empty($biografia)) {
    $sql = "UPDATE usuarios SET username = '$biografia' WHERE id = $id";
    header("location: perfil.php");
    exit(); 
}
?>
