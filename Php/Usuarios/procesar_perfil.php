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
$publicacionesArray = [];

$id = (int)$id;

// CAMBIO AQUÍ: Añadimos las subconsultas para contar likes y comentarios
$queryPosts = "SELECT p.id, p.imagen_url,
               (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as total_likes,
               (SELECT COUNT(*) FROM comentarios WHERE post_id = p.id) as total_comentarios
               FROM publicaciones p 
               WHERE usuario_id = $id 
               ORDER BY fecha_publicacion DESC";

$resultPost = mysqli_query($conexion, $queryPosts);

if ($resultPost) {
    while ($rowPost = mysqli_fetch_assoc($resultPost)) {
        $publicacionesArray[] = $rowPost;
    }
}
//foto_perfil
$fotoPerfil = mysqli_query(
    $conexion,
    "SELECT foto_perfil AS foto FROM usuarios WHERE id = $id"
);

if ($fotoPerfil && mysqli_num_rows($fotoPerfil) > 0) {
    $rowFoto = mysqli_fetch_assoc($fotoPerfil);
    $_SESSION['foto_perfil'] = $rowFoto['foto'];
}

?>

