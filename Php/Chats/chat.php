<?php
require "procesamientos/conexionBBDD.php";
session_start();

if (!isset($_GET['chat_id'])) {
    die("No se especificó chat_id");
}

$chat_id = intval($_GET['chat_id']);
$idUsu = $_SESSION['id'];

// 1️⃣ Obtener información del chat
$sql = $pdo->prepare("
    SELECT c.id, c.es_grupo, c.nombre_grupo
    FROM chats c
    WHERE c.id = :chat_id
");
$sql->execute(['chat_id' => $chat_id]);
$chat = $sql->fetch(PDO::FETCH_ASSOC);

if (!$chat) {
    die("Chat no encontrado");
}

// 2️⃣ Obtener los participantes (solo para mostrar nombres en chats 1 a 1)
$sql = $pdo->prepare("
    SELECT u.id, u.username
    FROM usuarios_chat uc
    JOIN usuarios u ON u.id = uc.usuario_id
    WHERE uc.chat_id = :chat_id AND u.id != :idUsu
");
$sql->execute(['chat_id' => $chat_id, 'idUsu' => $idUsu]);
$otrosUsuarios = $sql->fetchAll(PDO::FETCH_ASSOC);

// 3️⃣ Obtener mensajes del chat
$sql = $pdo->prepare("
    SELECT m.id, m.usuario_id, u.username, m.texto, m.fecha
    FROM mensajes m
    JOIN usuarios u ON u.id = m.usuario_id
    WHERE m.chat_id = :chat_id
    ORDER BY m.fecha ASC
");
$sql->execute(['chat_id' => $chat_id]);
$mensajes = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <link rel="stylesheet" href="../../../Estilos/estilos_chats.css">
    <style>
        .mensaje { margin: 5px 0; }
        .mensaje.tuyo { font-weight: bold; }
    </style>
</head>
<body>

<?php include __DIR__ . '../../../Php/Templates/navBar.php';?>
<main>

    <a class="volver" href="chats.php"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/></svg></a>
    <h2>
        <?php 
            if ($chat['es_grupo']) {
                echo $chat['nombre_grupo'];
            } else {
                echo $otrosUsuarios[0]['username'] ?? "Usuario";
            }
        ?>
    </h2>

    <div id="chat-mensajes">
        <?php foreach ($mensajes as $m): ?>
            <div class="mensaje <?= $m['usuario_id'] == $idUsu ? 'tuyo' : '' ?>">
                <strong><?= $m['usuario_id'] == $idUsu ? "Tú" : $m['username'] ?>:</strong>
                <?= htmlspecialchars($m['texto']) ?>
                <small>(<?= $m['fecha'] ?>)</small>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Formulario para enviar mensajes -->
    <form action="procesamientos/guardarMensajes.php" method="post">
        <input type="hidden" name="chat_id" value="<?= $chat_id ?>">
        <input type="hidden" name="usuario_id" value="<?= $idUsu ?>">
        <input type="text" name="mensaje" placeholder="Escribe tu mensaje..." required>
        <button type="submit">Enviar</button>
    </form>
</main>
<?php include __DIR__ . '../../../Php/Templates/footer.php';?>
</body>
</html>