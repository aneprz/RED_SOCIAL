<?php
session_start();
if(!isset($_SESSION['id'])) exit();

require '../../../BD/conexiones.php';
$usuario_id = intval($_SESSION['id']);
$post_id = intval($_POST['post_id'] ?? 0);
$texto = $_POST['texto'] ?? '';

if($post_id && $texto){
    $texto = $conexion->real_escape_string($texto);
    $conexion->query("INSERT INTO comentarios (post_id, usuario_id, texto, fecha) 
                      VALUES ($post_id, $usuario_id, '$texto', NOW())");
    
    $comment_id = $conexion->insert_id; // <--- ID del comentario recién insertado
    echo json_encode([
        'success' => true,
        'usuario' => $_SESSION['username'],
        'comment_id' => $comment_id
    ]);
}
