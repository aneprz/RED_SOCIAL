<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php';

$usuario_id = intval($_SESSION['id']);

$query = "
SELECT p.id, p.imagen_url 
FROM publicaciones p
JOIN usuarios u ON p.usuario_id = u.id
WHERE u.privacidad = 0
AND p.id NOT IN (
    SELECT publicacion_id 
    FROM publicaciones_vistas 
    WHERE usuario_id = $usuario_id
)
ORDER BY RAND()
LIMIT 1
";

$result = mysqli_query($conexion, $query);
$reel = mysqli_fetch_assoc($result);

if (!$reel) {
    echo "No hay más reels disponibles.";
    exit();
}

$reel_id = $reel['id'];

mysqli_query($conexion, "
    INSERT IGNORE INTO publicaciones_vistas (usuario_id, publicacion_id)
    VALUES ($usuario_id, $reel_id)
");
?>
