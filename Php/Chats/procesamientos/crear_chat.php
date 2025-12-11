<?php
require "conexionBBDD.php";
session_start();

$idUsu = $_SESSION['id'];
$otroUsuario = $_POST['usuario_id'];

// Crear un chat 1 a 1
$sql = $pdo->prepare("INSERT INTO chats (es_grupo, nombre_grupo) VALUES (0, NULL)");
$sql->execute();
$chat_id = $pdo->lastInsertId();

// Asociar los dos usuarios al chat
$sql = $pdo->prepare("INSERT INTO usuarios_chat (chat_id, usuario_id) VALUES (?, ?), (?, ?)");
$sql->execute([$chat_id, $idUsu, $chat_id, $otroUsuario]);

// Redirigir al chat recién creado
header("Location: ../chats.php?chat_id=$chat_id");
exit;
?>