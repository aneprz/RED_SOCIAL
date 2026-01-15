<?php
require '../../../BD/conexiones.php';

$post_id = intval($_GET['post_id']);

$res = $conexion->query("
    SELECT COUNT(*) total FROM likes WHERE post_id = $post_id
");

echo json_encode([
    'total' => $res->fetch_assoc()['total']
]);
