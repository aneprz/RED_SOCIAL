<?php
session_start();
require '../../BD/conexiones.php';

if(!isset($_SESSION['id'])) {
    echo json_encode(['success'=>false,'error'=>'No logueado']);
    exit;
}

$post_id = intval($_POST['post_id']);
$texto = trim($_POST['texto']);
$usuario_id = $_SESSION['id'];

if(empty($texto)){
    echo json_encode(['success'=>false,'error'=>'Comentario vacío']);
    exit;
}

// Insertar comentario
$stmt = $conexion->prepare("INSERT INTO comentarios (post_id, usuario_id, texto, fecha) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $post_id, $usuario_id, $texto);
if($stmt->execute()){
    $comment_id = $stmt->insert_id;
    
    // Obtener username
    $stmt2 = $conexion->prepare("SELECT username FROM usuarios WHERE id=?");
    $stmt2->bind_param("i",$usuario_id);
    $stmt2->execute();
    $res = $stmt2->get_result()->fetch_assoc();
    $usuario = $res['username'];

    echo json_encode(['success'=>true,'comment_id'=>$comment_id,'usuario'=>$usuario]);
} else {
    echo json_encode(['success'=>false,'error'=>'Error al insertar comentario']);
}
?>