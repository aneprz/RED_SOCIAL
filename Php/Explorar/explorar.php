<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../Sesiones/inicio_sesion.php");
    exit();
}

require '../../BD/conexiones.php';

/* Obtener publicaciones */
$sql = "SELECT imagen_url FROM publicaciones";
$result = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Explorar</title>

    <style>
        body {
            margin: 0;
            background: #fafafa;
            font-family: Arial, sans-serif;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4px;
            max-width: 900px;
            margin: 20px auto;
        }

        .grid-item {
            width: 100%;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            background: #eee;
        }

        .grid-item img,
        .grid-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../Templates/navBar.php'; ?>

<div class="grid">
    <?php while ($post = $result->fetch_assoc()): ?>
        <div class="grid-item">
            <?php
                $ruta = "../Crear/uploads/" . $post['imagen_url'];
                $ext = strtolower(pathinfo($post['imagen_url'], PATHINFO_EXTENSION));
            ?>

            <?php if (in_array($ext, ['mp4', 'webm'])): ?>
                <video src="<?= $ruta ?>" muted autoplay loop></video>
            <?php else: ?>
                <img src="<?= $ruta ?>" alt="post">
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
</div>

<?php include __DIR__ . '/../Templates/footer.php'; ?>

</body>
</html>
