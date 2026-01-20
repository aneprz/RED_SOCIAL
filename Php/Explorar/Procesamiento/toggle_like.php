<?php
session_start();
if(!isset($_SESSION['id'])) exit;

// Ajusta los path si es necesario. En tu ejemplo pusiste ../../../
require '../../../BD/conexiones.php';

$usuario_id = intval($_SESSION['id']); // Quien da el like
$post_id = intval($_POST['post_id']);

// 1. Verificar si ya existe el like
$res = $conexion->query("
    SELECT id FROM likes 
    WHERE post_id = $post_id AND usuario_id = $usuario_id
");

if($res->num_rows > 0){
    // ----------------------
    // QUITAR LIKE
    // ----------------------
    $conexion->query("
        DELETE FROM likes 
        WHERE post_id = $post_id AND usuario_id = $usuario_id
    ");
    $liked = false;

    // (Opcional) Borrar la notificación anterior si quita el like
    // Esto mantiene la BD limpia
    $conexion->query("
        DELETE FROM notificaciones 
        WHERE id_emisor = $usuario_id 
        AND tipo = 'like' 
        AND id_post = $post_id
    ");

} else {
    // ----------------------
    // DAR LIKE
    // ----------------------
    $conexion->query("
        INSERT INTO likes (post_id, usuario_id, fecha)
        VALUES ($post_id, $usuario_id, NOW())
    ");
    $liked = true;

    // ----------------------
    // CREAR NOTIFICACIÓN
    // ----------------------
    
    // A. Obtener ID del dueño del post
    $query_owner = $conexion->query("SELECT usuario_id FROM publicaciones WHERE id = $post_id");
    if ($row = $query_owner->fetch_assoc()) {
        $owner_id = $row['usuario_id'];

        // B. Solo notificar si no es mi propia foto
        if ($owner_id != $usuario_id) {
            
            // C. Verificar que no exista ya la notificación (anti-spam)
            $check_notif = $conexion->query("
                SELECT id FROM notificaciones 
                WHERE id_usuario = $owner_id 
                AND id_emisor = $usuario_id 
                AND tipo = 'like' 
                AND id_post = $post_id
            ");

            if ($check_notif->num_rows == 0) {
                // D. Insertar notificación
                // Asegúrate que tu tabla notificaciones tenga los campos en este orden o ajusta el INSERT
                $sql_insert = "INSERT INTO notificaciones (id_usuario, id_emisor, tipo, id_post, fecha) 
                               VALUES ($owner_id, $usuario_id, 'like', $post_id, NOW())";
                $conexion->query($sql_insert);
            }
        }
    }
}

// Total likes actualizados
$total = $conexion->query("
    SELECT COUNT(*) total FROM likes WHERE post_id = $post_id
")->fetch_assoc()['total'];

echo json_encode([
    'liked' => $liked,
    'total' => $total
]);
?>