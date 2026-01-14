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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Saals</title>
    <link rel="stylesheet" href="../../../Estilos/estilos_saals.css">
</head>
<body>

<?php include __DIR__ . '/../Templates/navBar.php'; ?>

<div class="tabla-seguidores reel-box">
    <div class="reel-video-container">
        <video src="/Php/Crear/uploads/<?= $reel['imagen_url'] ?>" autoplay muted loop></video>

        <div class="reel-controls">
            <button onclick="anterior()">⬆</button>
            <button onclick="siguiente(<?= $reel['id'] ?>)">⬇</button>
        </div>
    </div>
</div>

<script>
function siguiente(id) {
    window.location.href = "procesarSaals.php?id=" + id;
}

function anterior() {
    window.history.back();
}
</script>

</body>
</html>
