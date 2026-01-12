<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}
include '../../BD/conexiones.php';

$id = $_SESSION['id'];

$usuarios = [];

$resultado = mysqli_query(
    $conexion,
    "SELECT username FROM usuarios WHERE id != $id"
);

while ($fila = mysqli_fetch_assoc($resultado)) {
    $usuarios[] = $fila['username'];
}



?>