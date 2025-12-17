<?php
if (!isset($_SESSION['username'])) {
header("Location: Php/Sesiones/inicio_sesion.php");
exit();
}
include '../../BD/conexiones.php';

$id=$_SESSION['id'];

$query = "SELECT foto_perfil, username FROM usuarios where id = $id";
$result = mysqli_query($conexion, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $usuario = mysqli_fetch_assoc($result);
    $foto_perfil = $usuario['foto_perfil'];
}
?>
