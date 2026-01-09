<?php
require "conexionBBDD.php";

$chat_id = $_POST['chat_id'];
$usuario_id = $_POST['usuario_id'];
$mensaje = $_POST['mensaje'];

$sql = $pdo->prepare("
    INSERT INTO mensajes (chat_id, usuario_id, texto, leido)
    VALUES (?, ?, ?, 0)
");

$sql->execute([$chat_id, $usuario_id, $mensaje]);

header("Location: ../chat.php?chat_id=" . urlencode($chat_id));
exit(); // Siempre usar exit() después de header()
?>
