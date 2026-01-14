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
            <button onclick="siguiente()">⬇</button>
        </div>
    </div>

</div>

<script>
function siguiente() {
    window.location.href = "saals.php";
}

function anterior() {
    window.history.back();
}
</script>

</body>
</html>
