<?php
require "procesamientos/conexionBBDD.php";

session_start();

$idUsu=$_SESSION['id'];

$sql = $pdo->prepare("
    SELECT 
        c.id AS chat_id,
        c.es_grupo,
        c.nombre_grupo,
        u.username AS otro_usuario,
        m.texto AS ultimo_mensaje,
        m.fecha AS fecha_mensaje
    FROM chats c
    JOIN usuarios_chat cu ON cu.chat_id = c.id AND cu.usuario_id = :idUsu
    LEFT JOIN usuarios_chat cu2 ON cu2.chat_id = c.id AND cu2.usuario_id != :idUsu
    LEFT JOIN usuarios u ON u.id = cu2.usuario_id
    LEFT JOIN mensajes m ON m.id = (
        SELECT id 
        FROM mensajes 
        WHERE chat_id = c.id 
        ORDER BY fecha DESC 
        LIMIT 1
    )
    ORDER BY m.fecha DESC
");

$sql->execute(["idUsu" => $idUsu]);

$chats = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mis Chats</title>
    <link rel="stylesheet" href="../../../Estilos/estilos_chats.css">
</head>
<body>
<!--Barra de navegación-->
<?php include __DIR__ . '../../../Php/Templates/navBar.php';?>

<a href="nuevo_chat.php" class="btn-nuevo-chat">+ Nuevo Chat</a>
<h2>Mis Chats</h2>

<?php foreach ($chats as $c): ?>

<div class="chat" onclick="location.href='chat.php?chat_id=<?= $c['chat_id'] ?>'">

    <div class="titulo">
        <?php if ($c['es_grupo'] == 1): ?>
            <?= $c['nombre_grupo'] ?>
        <?php else: ?>
            <?= $c['otro_usuario'] ?>
        <?php endif; ?>
    </div>

    <div class="mensaje">
        <?= $c['ultimo_mensaje'] ?: "Sin mensajes todavía" ?>
    </div>

    <div class="fecha">
        <?= $c['fecha_mensaje'] ?>
    </div>

</div>

<?php endforeach; ?>

<!--Footer-->
<?php include __DIR__ . '../../../Php/Templates/footer.php';?>
</body>
</html>
