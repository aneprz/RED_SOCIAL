<?php
require "../../../BD/conexiones.php";
session_start();

if (!isset($_POST['chat_id']) || !isset($_POST['usuarios'])) {
    die("Faltan datos");
}

$chat_id = intval($_POST['chat_id']);
$usuarios = $_POST['usuarios']; // array de IDs

// 1️.Eliminar participantes actuales
$pdo->prepare("DELETE FROM usuarios_chat WHERE chat_id = :chat_id")->execute(['chat_id' => $chat_id]);

// 2️.Insertar los nuevos participantes
$insert = $pdo->prepare("INSERT INTO usuarios_chat (chat_id, usuario_id) VALUES (:chat_id, :usuario_id)");
foreach ($usuarios as $u_id) {
    $insert->execute([
        'chat_id' => $chat_id,
        'usuario_id' => intval($u_id)
    ]);
}

header("Location: ../chat.php?chat_id=$chat_id");
exit;
