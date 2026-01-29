<?php
session_start();
include '../../BD/conexiones.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'No logueado']);
    exit;
}

$mi_id = $_SESSION['id'];

try {
    $respuesta = [];

    // 1. SOLICITUDES
    $sql_solicitudes = "
        SELECT s.id as solicitud_id, u.username, u.foto_perfil, u.id as usuario_origen_id, 'solicitud' as tipo
        FROM solicitudes_seguimiento s
        JOIN usuarios u ON s.solicitante_id = u.id
        WHERE s.receptor_id = :mi_id AND s.estado = 'pendiente'
    ";
    $stmt = $pdo->prepare($sql_solicitudes);
    $stmt->execute(['mi_id' => $mi_id]);
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. NOTIFICACIONES REGULARES (Likes, Follows...)

    $sql_notif = "
        SELECT n.id, n.tipo, n.id_post, n.fecha, 
               u.username, u.foto_perfil, u.id as usuario_origen_id,
               (SELECT COUNT(*) FROM likes WHERE post_id = n.id_post) as num_likes
        FROM notificaciones n
        JOIN usuarios u ON n.id_emisor = u.id
        WHERE n.id_usuario = :mi_id 
        AND n.tipo != 'solicitud'  
        ORDER BY n.fecha DESC LIMIT 20
    ";
    $stmt = $pdo->prepare($sql_notif);
    $stmt->execute(['mi_id' => $mi_id]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- NUEVO: Asignar textos según el tipo de notificación ---
    foreach ($notificaciones as &$notif) {
        // Inicializamos una variable para el texto
        $notif['mensaje_texto'] = '';

        if ($notif['tipo'] === 'etiqueta') {
            // El texto exacto que pediste
            $notif['mensaje_texto'] = "te ha etiquetado para que colabores en una publicación";
        } 
        elseif ($notif['tipo'] === 'like') {
            $notif['mensaje_texto'] = "le ha dado like a tu publicación";
        }
        elseif ($notif['tipo'] === 'follow') {
            $notif['mensaje_texto'] = "ha comenzado a seguirte";
        }
        else {
            // Texto por defecto si hay otro tipo
            $notif['mensaje_texto'] = "interactuó contigo";
        }
    }
    unset($notif); // Romper la referencia del foreach

    // 3. SUGERENCIAS (CORREGIDO EL ERROR DE PARÁMETRO DOBLE)
    $sugerencias = [];
    if (count($notificaciones) < 5) {
        $sql_sug = "
            SELECT id, username, foto_perfil, id as usuario_origen_id, 'sugerencia' as tipo 
            FROM usuarios 
            WHERE id != :mi_id1 
            AND id NOT IN (SELECT seguido_id FROM seguidores WHERE seguidor_id = :mi_id2)
            LIMIT 3
        ";
        $stmt = $pdo->prepare($sql_sug);
        // Pasamos el ID dos veces con nombres distintos para evitar conflicto
        $stmt->execute(['mi_id1' => $mi_id, 'mi_id2' => $mi_id]);
        $sugerencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $respuesta['data'] = array_merge($solicitudes, $notificaciones, $sugerencias);
    $respuesta['success'] = true;

    echo json_encode($respuesta);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>