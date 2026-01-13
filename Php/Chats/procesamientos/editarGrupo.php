<?php
require "../../../BD/conexiones.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso no permitido");
}

if (!isset($_POST['chat_id'])) {
    die("No se especificó chat_id");
}

$chat_id = intval($_POST['chat_id']);
$idUsu = $_SESSION['id'];

// Obtener información del chat para verificar que es grupo
$sql = $pdo->prepare("SELECT * FROM chats WHERE id = :chat_id AND es_grupo = 1");
$sql->execute(['chat_id' => $chat_id]);
$chat = $sql->fetch(PDO::FETCH_ASSOC);

if (!$chat) {
    die("Chat no encontrado o no es un grupo");
}

// 1️⃣ Actualizar nombre del grupo
$nombre_grupo = trim($_POST['nombre_grupo'] ?? $chat['nombre_grupo']);
if ($nombre_grupo !== '') {
    $sql = $pdo->prepare("UPDATE chats SET nombre_grupo = :nombre WHERE id = :chat_id");
    $sql->execute([
        'nombre' => $nombre_grupo,
        'chat_id' => $chat_id
    ]);
}

// 3️⃣ Editar participantes si vienen en el POST
if (isset($_POST['participantes']) && is_array($_POST['participantes'])) {
    $nuevos = $_POST['participantes'];

    // Borrar participantes existentes (excepto tú mismo para que no te saques)
    $sql = $pdo->prepare("DELETE FROM usuarios_chat WHERE chat_id = :chat_id AND usuario_id != :yo");
    $sql->execute([
        'chat_id' => $chat_id,
        'yo' => $idUsu
    ]);

    // Insertar los nuevos
    $stmt = $pdo->prepare("INSERT INTO usuarios_chat (chat_id, usuario_id) VALUES (:chat_id, :usuario_id)");
    foreach ($nuevos as $uid) {
        $uid = intval($uid);
        if ($uid === $idUsu) continue; // nunca borrar al creador del grupo
        $stmt->execute([
            'chat_id' => $chat_id,
            'usuario_id' => $uid
        ]);
    }
}

// 4️⃣ Redirigir al chat
header("Location: ../chat.php?chat_id=$chat_id");
exit;
?>
