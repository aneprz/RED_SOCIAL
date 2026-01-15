<?php
session_start();
if(!isset($_SESSION['id'])) exit();

require '../../../BD/conexiones.php';

$usuario_id = intval($_SESSION['id']);
$post_id = intval($_POST['post_id'] ?? 0);
$texto = trim($_POST['texto'] ?? '');

if($post_id && $texto){
    $texto = $conexion->real_escape_string($texto);

    $conexion->query("
        INSERT INTO comentarios (post_id, usuario_id, texto, fecha)
        VALUES ($post_id, $usuario_id, '$texto', NOW())
    ");

    $comment_id = $conexion->insert_id;

    // Obtener foto perfil del usuario
    $res = $conexion->query("
        SELECT foto_perfil FROM usuarios WHERE id = $usuario_id
    ");
    $user = $res->fetch_assoc();

    echo json_encode([
        'success' => true,
        'comment_id' => $comment_id,
        'usuario' => $_SESSION['username'],
        'usuario_id' => $usuario_id,
        'foto_perfil' => $user['foto_perfil']
    ]);
}
