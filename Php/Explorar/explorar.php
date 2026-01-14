<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../Sesiones/inicio_sesion.php");
    exit();
}

require '../../BD/conexiones.php';

$usuario_id = intval($_SESSION['id']);

// Obtener publicaciones con cantidad de likes y comentarios
$sql = "SELECT p.id, p.imagen_url,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS total_likes,
            (SELECT COUNT(*) FROM comentarios WHERE post_id = p.id) AS total_comentarios
        FROM publicaciones p
        ORDER BY p.id DESC";
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
            position: relative;
            background: #eee;
            border-radius: 8px;
        }

        .media-wrapper {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .media {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s, filter 0.3s;
        }

        .grid-item:hover .media {
            filter: brightness(0.5);
            transform: scale(1.03);
        }

        /* Overlay oculto por defecto */
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s;
            color: white;
        }

        .grid-item:hover .overlay {
            opacity: 1;
        }

        .overlay-info {
            display: flex;
            gap: 15px;
            font-size: 18px;
            font-weight: bold;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.7);
        }

        /* Responsive para móviles */
        @media (max-width: 700px) {
            .grid {
                grid-template-columns: repeat(2, 1fr);
                max-width: 95%;
            }
        }

        @media (max-width: 400px) {
            .grid {
                grid-template-columns: 1fr;
            }
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
                $ext = strtolower(string: pathinfo($post['imagen_url'], PATHINFO_EXTENSION));
            ?>
            <div class="media-wrapper">
                <?php if (in_array($ext, ['mp4', 'webm'])): ?>
                    <video class="media hover-video" src="<?= $ruta ?>" muted loop></video>
                <?php else: ?>
                    <img class="media" src="<?= $ruta ?>" alt="post">
                <?php endif; ?>

                <!-- Overlay con iconos -->
                <div class="overlay">
                    <div class="overlay-info">
                        <span class="like">🌶️ <?= $post['total_likes'] ?></span>
                        <span class="comentario">💬 <?= $post['total_comentarios'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<script>
    // Reproducir videos solo al hover
    const videos = document.querySelectorAll('.hover-video');

    videos.forEach(video => {
        video.addEventListener('mouseenter', () => video.play());
        video.addEventListener('mouseleave', () => {
            video.pause();
            video.currentTime = 0;
        });
    });
</script>

<?php include __DIR__ . '/../Templates/footer.php'; ?>

</body>
</html>
