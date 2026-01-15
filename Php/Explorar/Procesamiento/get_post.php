<?php
session_start();
if(!isset($_SESSION['id'])) exit();

require '../../../BD/conexiones.php';
$post_id = intval($_GET['id'] ?? 0);

$res = $conexion->query("
    SELECT id, imagen_url, fecha_publicacion,
           (SELECT COUNT(*) FROM likes WHERE post_id=$post_id) as total_likes
    FROM publicaciones
    WHERE id=$post_id
");
$post = $res->fetch_assoc();

// Traer comentarios con ID
$comentarios = [];
$cRes = $conexion->query("
    SELECT c.id, c.texto, u.username AS usuario
    FROM comentarios c
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.post_id = $post_id
    ORDER BY c.id ASC
");
while($c = $cRes->fetch_assoc()) $comentarios[] = $c;

$post['comentarios'] = $comentarios;

header('Content-Type: application/json');
echo json_encode($post);
