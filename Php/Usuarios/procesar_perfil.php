<?php
if (!isset($_SESSION['username'])) {
header("Location: Php/Sesiones/inicio_sesion.php");
exit();
}

//CUANTOS ME SIGUEN


include '../../BD/conexiones.php';

$id = intval($_SESSION['id']);

$resultSeguidores = mysqli_query($conexion, "SELECT COUNT(seguidor_id) AS total FROM seguidores WHERE seguido_id = $id");

$rowSeguidores = mysqli_fetch_assoc($resultSeguidores);
$seguidores = $rowSeguidores['total'];

//A CUANTOS SIGO
$resultSeguidos = mysqli_query($conexion, "SELECT COUNT(seguido_id) AS total FROM seguidores WHERE seguidor_id = $id");

$rowSeguidos = mysqli_fetch_assoc($resultSeguidos);
$seguidos = $rowSeguidos['total'];

//CUANTAS PUBLICACIONES TENGO
$resultPublicaciones = mysqli_query($conexion, "SELECT COUNT(usuario_id) AS total FROM publicaciones WHERE usuario_id = $id");

$rowPublicaciones = mysqli_fetch_assoc($resultPublicaciones);
$publicaciones = $rowPublicaciones['total'];

//POST
$resultPost = mysqli_query($conexion, "SELECT imagen_url AS post FROM publicaciones WHERE usuario_id = $id");
$publicacionesArray = [];
if ($resultPost && mysqli_num_rows($resultPost) > 0) {
    while ($rowPost = mysqli_fetch_assoc($resultPost)) {
        $publicacionesArray[] = $rowPost['post'];
    }
}

?>

