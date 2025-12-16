<?php
if (!isset($_SESSION['username'])) {
header("Location: Php/Sesiones/inicio_sesion.php");
exit();
}
include '../../BD/conexiones.php';

$query = "SELECT id, username, foto_perfil FROM usuarios";
$result = mysqli_query($conexion, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $usuario = mysqli_fetch_assoc($result);

    $id = $usuario['id'];
    $username = $usuario['username'];
    $foto_perfil = $usuario['foto_perfil'];
}
?>
