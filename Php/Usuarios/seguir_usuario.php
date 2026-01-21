<?php
session_start();
// Ajustamos la ruta de conexión (Usuario -> Php -> Raiz -> BD)
include '../../BD/conexiones.php';

if (!isset($_SESSION['id']) || !isset($_POST['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
    exit;
}

$yo = $_SESSION['id'];
$otro_usuario = $_POST['id_usuario'];

if ($yo == $otro_usuario) exit; 

// 1. Verificamos si la cuenta destino es PRIVADA
$stmt = $pdo->prepare("SELECT privacidad FROM usuarios WHERE id = ?");
$stmt->execute([$otro_usuario]);
$es_privada = $stmt->fetchColumn();

// 2. Comprobar estado actual
// A. ¿Ya lo sigo? (Tabla seguidores: seguidor_id / seguido_id)
$ya_sigo = $pdo->query("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = $yo AND seguido_id = $otro_usuario")->fetchColumn();

// B. ¿Ya solicité? (Tabla solicitudes: solicitante_id / receptor_id)
$ya_solicite = $pdo->query("SELECT COUNT(*) FROM solicitudes_seguimiento WHERE solicitante_id = $yo AND receptor_id = $otro_usuario")->fetchColumn();

// --- LÓGICA PRINCIPAL ---

if ($ya_sigo > 0) {
    // CASO 1: YA LO SIGO -> Dejar de seguir (Unfollow)
    $pdo->prepare("DELETE FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?")->execute([$yo, $otro_usuario]);
    $estado = 'no_seguido';

} elseif ($ya_solicite > 0) {
    // CASO 2: YA SOLICITÉ -> Cancelar solicitud
    $pdo->prepare("DELETE FROM solicitudes_seguimiento WHERE solicitante_id = ? AND receptor_id = ?")->execute([$yo, $otro_usuario]);
    
    // Borrar la notificación de solicitud para limpiar
    $pdo->prepare("DELETE FROM notificaciones WHERE id_usuario = ? AND id_emisor = ? AND tipo = 'solicitud'")->execute([$otro_usuario, $yo]);
    $estado = 'no_seguido';

} else {
    // CASO 3: NO HAY RELACIÓN -> Intentar seguir
    
    if ($es_privada == 1) {
        // A. Es Privada -> Crear Solicitud
        $stmt = $pdo->prepare("INSERT INTO solicitudes_seguimiento (solicitante_id, receptor_id, estado) VALUES (?, ?, 'pendiente')");
        $stmt->execute([$yo, $otro_usuario]);
        
        // IMPORTANTE: Crear notificación tipo 'solicitud'
        $pdo->prepare("INSERT INTO notificaciones (id_usuario, id_emisor, tipo, fecha) VALUES (?, ?, 'solicitud', NOW())")->execute([$otro_usuario, $yo]);
        
        $estado = 'solicitado';
    } else {
        // B. Es Pública -> Seguir directo
        $pdo->prepare("INSERT INTO seguidores (seguidor_id, seguido_id, fecha) VALUES (?, ?, NOW())")->execute([$yo, $otro_usuario]);
        
        // Notificación de follow
        $pdo->prepare("INSERT INTO notificaciones (id_usuario, id_emisor, tipo, fecha) VALUES (?, ?, 'follow', NOW())")->execute([$otro_usuario, $yo]);
        
        $estado = 'siguiendo';
    }
}

echo json_encode(['status' => 'success', 'estado' => $estado]);
?>