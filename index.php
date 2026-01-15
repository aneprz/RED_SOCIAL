<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include __DIR__ . '/BD/conexiones.php'; // tu conexión PDO ($pdo)

$user_id = $_SESSION['id'];

// 1. Obtener IDs de los usuarios que sigo
$stmt = $pdo->prepare("SELECT seguido_id FROM seguidores WHERE seguidor_id = :id");
$stmt->execute(['id' => $user_id]);
$ids_sigo = $stmt->fetchAll(PDO::FETCH_COLUMN);

$seguir_mensaje = null;
$sugerencias = [];
$publicaciones = [];

if (empty($ids_sigo)) {
    $seguir_mensaje = "No sigues a nadie aún. ¡Empieza a seguir gente para ver sus publicaciones!";
} else {
    $ids_sigo_str = implode(',', $ids_sigo);

    // 2. Obtener publicaciones con foto de perfil
    $sql_posts = "
        SELECT 
            p.id,
            p.imagen_url,
            p.pie_foto,
            p.fecha_publicacion,
            u.id AS usuario_id,
            u.username,
            u.foto_perfil
        FROM publicaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.usuario_id IN ($ids_sigo_str)
        ORDER BY p.fecha_publicacion DESC
    ";
    $stmt_posts = $pdo->query($sql_posts);
    $publicaciones = $stmt_posts->fetchAll(PDO::FETCH_ASSOC);

    // 3. Obtener sugerencias de usuarios
    $sql_sug = "SELECT DISTINCT u.id, u.username, u.foto_perfil
                FROM seguidores s
                JOIN usuarios u ON s.seguido_id = u.id
                WHERE s.seguidor_id IN ($ids_sigo_str)
                  AND u.id != :user_id
                  AND u.id NOT IN ($ids_sigo_str)
                ORDER BY RAND()
                LIMIT 5";
    $stmt_sug = $pdo->prepare($sql_sug);
    $stmt_sug->execute(['user_id' => $user_id]);
    $sugerencias = $stmt_sug->fetchAll(PDO::FETCH_ASSOC);
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

                    // Foto de perfil del usuario (fallback si no tiene)
                    $fotoPerfil = !empty($post['foto_perfil'])
                        ? $post['foto_perfil']
                        : '/Media/foto_default.png';

                    // Ruta del archivo de la publicación
                    $archivoRuta = !empty($post['imagen_url']) && file_exists(__DIR__ . '/Php/Crear/uploads/' . $post['imagen_url'])
                        ? '/Php/Crear/uploads/' . $post['imagen_url']
                        : '/Media/foto_default.png';
                ?>
                <div class="post">
                    <!-- Encabezado con foto de perfil y nombre -->
                    <div style="display:flex; align-items:center; margin-bottom:5px;">
                        <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Perfil"
                             style="width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right:10px;">
                        <strong><?= htmlspecialchars($post['username']) ?></strong>
                    </div>
                    <em><?= htmlspecialchars($post['fecha_publicacion']) ?></em>

                    <?php if (!empty($post['pie_foto'])): ?>
                        <p><?= nl2br(htmlspecialchars($post['pie_foto'])) ?></p>
                    <?php endif; ?>

                    <?php if (in_array($ext, ['mp4','webm'])): ?>
                        <video class="hover-video" src="<?= htmlspecialchars($archivoRuta) ?>" muted loop style="width:100%; border-radius:8px;"></video>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($archivoRuta) ?>" alt="Post" style="width:100%; border-radius:8px;">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

    <div class="sugerencias">
        <h3>Sugerencias para ti</h3>
        <?php if (!empty($sugerencias)): ?>
            <?php foreach($sugerencias as $user): ?>
                <div class="suggestion" style="display:flex; align-items:center; margin-bottom:10px;">
                    <?php
                        $fotoRuta = !empty($user['foto_perfil']) && file_exists(__DIR__ . '/Php/Usuarios/fotosDePerfil/' . $user['foto_perfil'])
                            ? '/Php/Usuarios/fotosDePerfil/' . $user['foto_perfil']
                            : '/Media/foto_default.png';
                    ?>
                    <img src="<?= htmlspecialchars($fotoRuta) ?>" alt="Perfil"
                         style="width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right:10px;">

                    <form action="Php/Busqueda/usuarioAjeno.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        <button type="submit" style="border:none; background:none; padding:0; cursor:pointer; font-weight:bold; color:#333;">
                            <?= htmlspecialchars($user['username']) ?>
                        </button>
                    </form>
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
