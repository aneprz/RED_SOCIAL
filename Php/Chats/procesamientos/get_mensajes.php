<?php
require "../../../BD/conexiones.php";
session_start();

header('Content-Type: application/json');

if (!isset($_GET['chat_id']) || !isset($_SESSION['id'])) {
    echo json_encode([]);
    exit;
}

$chat_id = intval($_GET['chat_id']);
$idUsu = $_SESSION['id'];

try {
    // 1. Obtener los mensajes
    $sql = $pdo->prepare("
        SELECT m.id, m.usuario_id, u.username, u.foto_perfil, m.texto, m.fecha, m.leido
        FROM mensajes m
        JOIN usuarios u ON u.id = m.usuario_id
        WHERE m.chat_id = :chat_id
        ORDER BY m.fecha ASC
    ");
    $sql->execute(['chat_id' => $chat_id]);
    $mensajes = $sql->fetchAll(PDO::FETCH_ASSOC);

    // 2. Marcar como leídos 
    $pdo->prepare("
        UPDATE mensajes SET leido = 1
        WHERE chat_id = :chat_id AND usuario_id != :yo AND leido = 0
    ")->execute(['chat_id' => $chat_id, 'yo' => $idUsu]);

    echo json_encode($mensajes);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}