<?php
session_start();
if(!isset($_SESSION['id'])) exit;

require '../../../BD/conexiones.php';

$usuario_id = intval($_SESSION['id']);
$post_id = intval($_POST['post_id']);

$res = $conexion->query("
    SELECT id FROM likes 
    WHERE post_id = $post_id AND usuario_id = $usuario_id
");

if($res->num_rows > 0){
    // Quitar like
    $conexion->query("
        DELETE FROM likes 
        WHERE post_id = $post_id AND usuario_id = $usuario_id
    ");
    $liked = false;
} else {
    // Dar like
    $conexion->query("
        INSERT INTO likes (post_id, usuario_id, fecha)
        VALUES ($post_id, $usuario_id, NOW())
    ");
    $liked = true;
}

// Total likes
$total = $conexion->query("
    SELECT COUNT(*) total FROM likes WHERE post_id = $post_id
")->fetch_assoc()['total'];

echo json_encode([
    'liked' => $liked,
    'total' => $total
]);
