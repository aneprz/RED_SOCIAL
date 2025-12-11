<?php
require "conexionBBDD.php";

$chat_id = $_GET['chat_id'];

$sql = $pdo->prepare("
    SELECT m.id, m.usuario_id, u.username, m.texto, m.fecha
    FROM mensajes m
    JOIN usuarios u ON m.usuario_id = u.id
    WHERE m.chat_id = ?
    ORDER BY m.fecha ASC
");

$sql->execute([$chat_id]);

echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC));
?>
