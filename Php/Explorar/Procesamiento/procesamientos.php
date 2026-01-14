<?php
require_once __DIR__ . '/../../../BD/conexiones.php';

function obtenerFotos() {
    global $pdo;

    $sql = "
        SELECT 
            p.imagen_url,
            p.pie_foto,
            p.fecha_publicacion,
            u.username
        FROM publicaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.fecha_publicacion DESC
    ";

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
