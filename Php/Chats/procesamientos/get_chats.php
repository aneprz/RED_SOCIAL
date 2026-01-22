<?php
require "../../../BD/conexiones.php";
session_start();

$idUsu = $_SESSION['id'];

$sql = $pdo->prepare("
SELECT
    c.id AS chat_id,
    c.es_grupo,
    c.nombre_grupo,
    (
        SELECT u.username
        FROM usuarios u
        JOIN usuarios_chat uc ON uc.usuario_id = u.id
        WHERE uc.chat_id = c.id AND u.id != :yo1
        LIMIT 1
    ) AS otro_usuario,
    (
        SELECT u.foto_perfil
        FROM usuarios u
        JOIN usuarios_chat uc ON uc.usuario_id = u.id
        WHERE uc.chat_id = c.id AND u.id != :yo2
        LIMIT 1
    ) AS foto_perfil,
    m.texto AS ultimo_mensaje,
    m.fecha AS fecha_mensaje,
    c.fecha_creacion,
    (
        SELECT COUNT(*)
        FROM mensajes m2
        WHERE m2.chat_id = c.id AND m2.usuario_id != :yo3 AND m2.leido = 0
    ) AS no_leidos
FROM chats c
JOIN usuarios_chat cu ON cu.chat_id = c.id AND cu.usuario_id = :yo4
LEFT JOIN mensajes m ON m.id = (
    SELECT id
    FROM mensajes
    WHERE chat_id = c.id
    ORDER BY fecha DESC
    LIMIT 1
)
ORDER BY COALESCE(m.fecha, c.fecha_creacion) DESC
");

$sql->execute([
    "yo1" => $idUsu,
    "yo2" => $idUsu,
    "yo3" => $idUsu,
    "yo4" => $idUsu
]);

echo json_encode($sql->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);