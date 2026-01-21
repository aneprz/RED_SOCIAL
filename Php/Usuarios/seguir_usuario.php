<?php
session_start();
require '../../BD/conexiones.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || !isset($_POST['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
    exit;
}

$yo = $_SESSION['id'];
$otro_usuario = $_POST['id_usuario'];

if ($yo == $otro_usuario) {
    echo json_encode(['status' => 'error', 'message' => 'No puedes seguirte a ti mismo']);
    exit;
}

try {
    // ---------------------------------------------------------
    // 1. OBTENER PRIVACIDAD (0 = Pública, 1 = Privada)
    // ---------------------------------------------------------
    
    // NOTA: Si tu columna se llama 'es_privada' en vez de 'privacidad', cámbialo aquí abajo:
    $stmt = $pdo->prepare("SELECT privacidad FROM usuarios WHERE id = ?");
    $stmt->execute([$otro_usuario]);
    
    // fetchColumn devuelve el valor directo (0 o 1)
    $valor_privacidad = $stmt->fetchColumn(); 

    // Verificamos si existe el usuario (fetchColumn devuelve false si no hay filas)
    if ($valor_privacidad === false) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
        exit;
    }

    // AQUI ESTA EL CAMBIO CLAVE:
    // Convertimos a entero por seguridad y comparamos con 1
    $es_privada = ((int)$valor_privacidad === 1); 

    // ---------------------------------------------------------
    // 2. COMPROBAR ESTADO ACTUAL
    // ---------------------------------------------------------
    
    // A. ¿Ya lo sigo?
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
    $stmt->execute([$yo, $otro_usuario]);
    $ya_sigo = $stmt->fetchColumn();

    // B. ¿Ya solicité?
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_seguimiento WHERE solicitante_id = ? AND receptor_id = ?");
    $stmt->execute([$yo, $otro_usuario]);
    $ya_solicite = $stmt->fetchColumn();

    // ---------------------------------------------------------
    // 3. LÓGICA PRINCIPAL
    // ---------------------------------------------------------

    if ($ya_sigo > 0) {
        // --- YA LO SIGO -> UNFOLLOW ---
        $pdo->prepare("DELETE FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?")->execute([$yo, $otro_usuario]);
        $estado = 'no_seguido';

    } elseif ($ya_solicite > 0) {
        // --- YA SOLICITÉ -> CANCELAR SOLICITUD ---
        $pdo->prepare("DELETE FROM solicitudes_seguimiento WHERE solicitante_id = ? AND receptor_id = ?")->execute([$yo, $otro_usuario]);
        // Limpiar notifiación vieja
        $pdo->prepare("DELETE FROM notificaciones WHERE id_usuario = ? AND id_emisor = ? AND tipo = 'solicitud'")->execute([$otro_usuario, $yo]);
        $estado = 'no_seguido';

    } else {
        // --- NO HAY RELACIÓN -> INTENTAR SEGUIR ---
        
        if ($es_privada) {
            // A. ES PRIVADA (1) -> CREAR SOLICITUD
            
            // 1. Insertamos la solicitud de seguimiento
            $pdo->prepare("INSERT INTO solicitudes_seguimiento (solicitante_id, receptor_id, estado) VALUES (?, ?, 'pendiente')")->execute([$yo, $otro_usuario]);
            
            // 2. CORRECCIÓN: Borrar notificaciones duplicadas previas
            // Antes de crear la notificación, borramos si ya existe alguna de tipo 'solicitud' de mí para él.
            $pdo->prepare("DELETE FROM notificaciones WHERE id_usuario = ? AND id_emisor = ? AND tipo = 'solicitud'")->execute([$otro_usuario, $yo]);

            // 3. Ahora sí, insertamos la nueva notificación limpia
            $pdo->prepare("INSERT INTO notificaciones (id_usuario, id_emisor, tipo, fecha) VALUES (?, ?, 'solicitud', NOW())")->execute([$otro_usuario, $yo]);
            
            $estado = 'solicitado';

        } else {
            // B. ES PÚBLICA (0) -> SEGUIR DIRECTO
            $pdo->prepare("INSERT INTO seguidores (seguidor_id, seguido_id, fecha) VALUES (?, ?, NOW())")->execute([$yo, $otro_usuario]);
            
            // Opcional: También evitamos duplicar notificaciones de 'follow' si la persona te sigue y te deja de seguir mucho
            $pdo->prepare("DELETE FROM notificaciones WHERE id_usuario = ? AND id_emisor = ? AND tipo = 'follow'")->execute([$otro_usuario, $yo]);

            $pdo->prepare("INSERT INTO notificaciones (id_usuario, id_emisor, tipo, fecha) VALUES (?, ?, 'follow', NOW())")->execute([$otro_usuario, $yo]);
            
            $estado = 'siguiendo';
        }
    }

    echo json_encode(['status' => 'success', 'estado' => $estado]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de BD: ' . $e->getMessage()]);
}
?>