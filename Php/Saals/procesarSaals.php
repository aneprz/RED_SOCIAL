<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php';

$usuario_id = intval($_SESSION['id']);
$reel_id = intval($_GET['id'] ?? 0);

if ($reel_id > 0) {
    mysqli_query($conexion, "
        INSERT IGNORE INTO publicaciones_vistas (usuario_id, publicacion_id)
        VALUES ($usuario_id, $reel_id)
    ");
}

header("Location: saals.php");
exit();
