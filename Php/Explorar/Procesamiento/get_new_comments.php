<?php
require '../../../BD/conexiones.php';

$post_id = intval($_GET['post_id']);
$last_id = intval($_GET['last_id']);

$res = $conexion->query("
    SELECT 
        c.id,
        c.texto,
        u.id AS usuario_id,
        u.username AS usuario,
        u.foto_perfil
    FROM comentarios c
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.post_id = $post_id AND c.id > $last_id
    ORDER BY c.id ASC
");

$comments = [];
while($row = $res->fetch_assoc()) {
    $comments[] = $row;
}

echo json_encode($comments);
