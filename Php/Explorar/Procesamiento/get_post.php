<?php
session_start();
if(!isset($_SESSION['id'])) exit();

require '../../../BD/conexiones.php';
$post_id = intval($_GET['id'] ?? 0);

$res = $conexion->query("
    SELECT 
        p.id,
        p.imagen_url,
        p.fecha_publicacion,
        u.id AS usuario_id,
        u.username AS usuario,
        u.foto_perfil,
        (SELECT COUNT(*) FROM likes WHERE post_id = $post_id) AS total_likes
    FROM publicaciones p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.id = $post_id
");

$post = $res->fetch_assoc();

/* ===== COMENTARIOS CON FOTO PERFIL ===== */
$comentarios = [];
$cRes = $conexion->query("
    SELECT 
        c.id,
        c.texto,
        u.id AS usuario_id,
        u.username AS usuario,
        u.foto_perfil
    FROM comentarios c
    JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.post_id = $post_id
    ORDER BY c.id ASC
");

while($c = $cRes->fetch_assoc()) {
    $comentarios[] = $c;
}

$post['comentarios'] = $comentarios;

header('Content-Type: application/json');
echo json_encode($post);
