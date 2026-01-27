<?php
// Validar sesión
if (!isset($_SESSION['username'])) {
    header("Location: ../Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php';

$id = intval($_SESSION['id']);

// 1. Estadísticas
$sqlSeguidores = "SELECT COUNT(*) FROM seguidores WHERE seguido_id = $id";
$seguidores = $conexion->query($sqlSeguidores)->fetch_column();

$sqlSeguidos = "SELECT COUNT(*) FROM seguidores WHERE seguidor_id = $id";
$seguidos = $conexion->query($sqlSeguidos)->fetch_column();

$sqlPublicaciones = "SELECT COUNT(*) FROM publicaciones WHERE usuario_id = $id";
$publicaciones = $conexion->query($sqlPublicaciones)->fetch_column();

// 2. Obtener Publicaciones con Likes y Comentarios
$publicacionesArray = [];
$queryPosts = "SELECT p.id, p.imagen_url,
               (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as total_likes,
               (SELECT COUNT(*) FROM comentarios WHERE post_id = p.id) as total_comentarios
               FROM publicaciones p 
               WHERE usuario_id = $id 
               ORDER BY fecha_publicacion DESC";

$resultPost = $conexion->query($queryPosts);

if ($resultPost) {
    while ($rowPost = $resultPost->fetch_assoc()) {
        $publicacionesArray[] = $rowPost;
    }
}

// 3. Foto de perfil
$resFoto = $conexion->query("SELECT foto_perfil FROM usuarios WHERE id = $id");
if ($resFoto && $rowFoto = $resFoto->fetch_assoc()) {
    $_SESSION['foto_perfil'] = $rowFoto['foto_perfil'];
}
?>