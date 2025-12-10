<?php

//CUANTOS ME SIGUEN
session_start();
include '../../BD/conexiones.php';

$id = intval($_SESSION['id']);

$resultSeguidores = mysqli_query($conexion, "SELECT COUNT(seguidor_id) AS total FROM seguidores WHERE seguido_id = $id");

$row = mysqli_fetch_assoc($resultSeguidores);
$seguidores = $row['total'];

//A CUANTOS SIGO

$resultSeguidos = mysqli_query($conexion, "SELECT COUNT(seguido_id) AS total FROM seguidores WHERE seguido_id = $id");

$row = mysqli_fetch_assoc($resultSeguidos);
$seguidos = $row['total'];
?>
