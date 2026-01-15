<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include __DIR__ . '/BD/conexiones.php'; // tu conexión PDO ($pdo)

$user_id = $_SESSION['id']; // id del usuario logueado

// 1. Obtener los IDs de los usuarios que sigo
$stmt = $pdo->prepare("SELECT seguido_id FROM seguidores WHERE seguidor_id = :id");
$stmt->execute(['id' => $user_id]);
$ids_sigo = $stmt->fetchAll(PDO::FETCH_COLUMN);

$seguir_mensaje = null;
$sugerencias = [];
$publicaciones = [];

// 2. Si no sigo a nadie, mostrar mensaje
if (empty($ids_sigo)) {
    $seguir_mensaje = "No sigues a nadie aún. ¡Empieza a seguir gente para ver sus publicaciones!";
} else {
    $ids_sigo_str = implode(',', $ids_sigo); // para usar en IN()

    // 3. Traemos las publicaciones usando tu función de Explorar
    require __DIR__ . '/Php/Explorar/Procesamiento/procesamientos.php';
    $publicaciones = obtenerFotos();

    // 4. Obtener 5 sugerencias: personas que siguen mis seguidos, que yo no sigo y no soy yo
    $sql_sug = "SELECT DISTINCT u.id, u.username 
                FROM seguidores s
                JOIN usuarios u ON s.seguido_id = u.id
                WHERE s.seguidor_id IN ($ids_sigo_str)
                  AND u.id != :user_id
                  AND u.id NOT IN ($ids_sigo_str)
                ORDER BY RAND()
                LIMIT 5";
    $stmt_sug = $pdo->prepare($sql_sug);
    $stmt_sug->execute(['user_id' => $user_id]);
    $sugerencias = $stmt_sug->fetchAll();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inicio</title>
    <link rel="stylesheet" href="/Estilos/estilos.css">
    <link rel="icon" type="image/png" href="/Media/logo.png">
</head>
<body>
    <?php include __DIR__ . '/Php/Templates/navBar.php'; ?>

    <div class="content container">
        <div class="main">

            <?php if ($seguir_mensaje): ?>
                <p><?= htmlspecialchars($seguir_mensaje) ?></p>
            <?php else: ?>
                <?php foreach($publicaciones as $post): ?>
                    <?php
                        $ext = strtolower(pathinfo($post['imagen_url'], PATHINFO_EXTENSION));
                        // URL usando mostrarArchivo.php
                        $ruta = "/Php/Explorar/mostrar_archivo.php?f=" . urlencode($post['imagen_url']);
                    ?>
                    <div class="post">
                        <strong><?= htmlspecialchars($post['username']) ?></strong><br>
                        <em><?= htmlspecialchars($post['fecha_publicacion']) ?></em>

                        <?php if (!empty($post['pie_foto'])): ?>
                            <p><?= nl2br(htmlspecialchars($post['pie_foto'])) ?></p>
                        <?php endif; ?>

                        <?php if (in_array($ext, ['mp4','webm'])): ?>
                            <video class="hover-video" src="<?= $ruta ?>" muted loop style="width:100%; border-radius:8px;"></video>
                        <?php else: ?>
                            <img src="<?= $ruta ?>" alt="Post" style="width:100%; border-radius:8px;">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

        <div class="sugerencias">
            <h3>Sugerencias para ti</h3>
            <?php if (!empty($sugerencias)): ?>
                <?php foreach($sugerencias as $user): ?>
                    <div class="suggestion">
                        <?= htmlspecialchars($user['username']) ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay sugerencias por ahora.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/Php/Templates/footer.php'; ?>

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
</body>
</html>
