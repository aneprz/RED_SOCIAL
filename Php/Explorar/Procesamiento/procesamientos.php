<?php
require "../../BD/conexiones.php";

function obtenerFotos() {
    global $pdo;

    $sql = "SELECT imagen_url FROM publicaciones";
    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
