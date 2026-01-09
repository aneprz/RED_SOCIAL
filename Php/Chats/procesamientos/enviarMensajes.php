<?php
require "../../../BD/conexiones.php";

$chat_id = $_POST['chat_id'];
$usuario_id = $_POST['usuario_id'];
$mensaje = $_POST['mensaje'];

$sql = $pdo->prepare("
    INSERT INTO mensajes (chat_id, usuario_id, texto, leido)
    VALUES (?, ?, ?, 0)
");

$sql->execute([$chat_id, $usuario_id, $mensaje]);

echo "OK";
?>
