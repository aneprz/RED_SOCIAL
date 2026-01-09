<?php
require "../../../BD/conexiones.php";
session_start();

$idUsu = $_SESSION['id'];
$otroUsuario = $_POST['usuario_id'];

// Crear un chat 1 a 1
$sql=$pdo->prepare("
    SELECT uc1.chat_id
    FROM usuarios_chat uc1
    JOIN usuarios_chat uc2 ON uc1.chat_id = uc2.chat_id
    JOIN chats c ON c.id = uc1.chat_id
    WHERE uc1.usuario_id = ?
      AND uc2.usuario_id = ?
      AND c.es_grupo = 0
    LIMIT 1
"); //comprueba si ya existe un chat con ese usuario
$sql->execute([$idUsu, $otroUsuario]);

$chatExistente = $sql->fetch(PDO::FETCH_ASSOC);

if ($chatExistente) {
    //Si ya existe redirige a ese chat
    $chat_id = $chatExistente['chat_id'];
    header("Location: ../chats.php?chat_id=$chat_id");
    exit;
}

/*SI NO EXISTE, CREAR EL CHAT */
$sql = $pdo->prepare("INSERT INTO chats (es_grupo, nombre_grupo) VALUES (0, NULL)");
$sql->execute();
$chat_id = $pdo->lastInsertId();

/*ASOCIAR LOS DOS USUARIOS */
$sql = $pdo->prepare("
    INSERT INTO usuarios_chat (chat_id, usuario_id)
    VALUES (?, ?), (?, ?)
");
$sql->execute([$chat_id, $idUsu, $chat_id, $otroUsuario]);

/*REDIRIGIR */
header("Location: ../chats.php?chat_id=$chat_id");
exit;
?>