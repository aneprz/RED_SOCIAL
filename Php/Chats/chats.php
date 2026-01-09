<?php
require "../../BD/conexiones.php";

session_start();

$idUsu = $_SESSION['id'];

// Consulta corregida con parámetros únicos
$sql = $pdo->prepare("
    SELECT 
        c.id AS chat_id,
        c.es_grupo,
        c.nombre_grupo,
        u.username AS otro_usuario,
        m.texto AS ultimo_mensaje,
        m.fecha AS fecha_mensaje,
        c.fecha_creacion
    FROM chats c
    JOIN usuarios_chat cu ON cu.chat_id = c.id AND cu.usuario_id = :idUsu1
    LEFT JOIN usuarios_chat cu2 ON cu2.chat_id = c.id AND cu2.usuario_id != :idUsu2
    LEFT JOIN usuarios u ON u.id = cu2.usuario_id
    LEFT JOIN mensajes m ON m.id = (
        SELECT id 
        FROM mensajes 
        WHERE chat_id = c.id 
        ORDER BY fecha DESC 
        LIMIT 1
    )
    ORDER BY COALESCE(m.fecha, c.fecha_creacion) DESC
");

// Ejecutamos la consulta con los parámetros correctos
$sql->execute([
    "idUsu1" => $idUsu,
    "idUsu2" => $idUsu
]);

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

<main>
    <div class="encabezado">
        <h2>Mis Chats</h2>
        <a href="nuevo_chat.php" class="btn-nuevo-chat">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24">
                <path fill="currentColor" d="M7 9h8V7H7zm0 4h5v-2H7zm10 7v-3h-3v-2h3v-3h2v3h3v2h-3v3zM3 20V5q0-.825.588-1.412T5 3h12q.825 0 1.413.588T19 5v5.075q-.25-.05-.5-.062T18 10q-2.525 0-4.262 1.75T12 16q0 .25.013.5t.062.5H6z"/>
            </svg>
        </a>
    </div>

    <?php foreach ($chats as $c): ?>
    <div class="chat" onclick="location.href='chat.php?chat_id=<?= $c['chat_id'] ?>'">

        <div class="fotoPerfil">
            <!-- Aquí podrías poner la foto de perfil si la tienes -->
        </div>

        <div class="titulo">
            <?php 
                $nombre = $c['es_grupo'] ? $c['nombre_grupo'] : $c['otro_usuario'];
            ?>
            <?= htmlspecialchars($nombre) ?>
        </div>

        <div class="mensaje">
            <?= htmlspecialchars($c['ultimo_mensaje'] ?: "Sin mensajes todavía") ?>
        </div>

        <div class="fecha">
            <?= $c['fecha_mensaje'] ?: $c['fecha_creacion'] ?>
        </div>

    </div>
    <?php endforeach; ?>

</main>
<!--Footer-->
<?php include __DIR__ . '../../../Php/Templates/footer.php';?>
</body>
</html>
