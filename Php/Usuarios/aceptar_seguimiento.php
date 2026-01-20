<?php
session_start();
include '../../BD/conexiones.php';

if (!isset($_SESSION['id']) || !isset($_POST['solicitante_id'])) {
    die(json_encode(['success' => false]));
}

$mi_id = $_SESSION['id']; 
$solicitante_id = $_POST['solicitante_id']; 

try {
    $pdo->beginTransaction();

    // 1. Actualizar estado solicitud
    $stmt = $pdo->prepare("UPDATE solicitudes_seguimiento SET estado = 'aceptada' WHERE solicitante_id = ? AND receptor_id = ?");
    $stmt->execute([$solicitante_id, $mi_id]);

    // 2. Insertar en seguidores
    $stmt = $pdo->prepare("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id, fecha) VALUES (?, ?, NOW())");
    $stmt->execute([$solicitante_id, $mi_id]);

    // 3. Borrar notificación (CORREGIDO: Usamos 'follow_request')
    $stmt = $pdo->prepare("DELETE FROM notificaciones WHERE id_usuario = ? AND id_emisor = ? AND tipo = 'follow_request'");
    $stmt->execute([$mi_id, $solicitante_id]);
    
    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>