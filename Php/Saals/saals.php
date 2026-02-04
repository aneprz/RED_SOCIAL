<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php';

$usuario_id = intval($_SESSION['id']);

// Inicializamos historial y puntero
if (!isset($_SESSION['reels_vistos'])) $_SESSION['reels_vistos'] = [];
if (!isset($_SESSION['reel_index'])) $_SESSION['reel_index'] = -1;

$accion = $_GET['accion'] ?? 'siguiente';

if ($accion === 'siguiente') {
    // Si vamos a un nuevo reel, fuera del historial
    if ($_SESSION['reel_index'] === count($_SESSION['reels_vistos']) - 1) {
        
        $ids_vistos_sql = implode(',', $_SESSION['reels_vistos']) ?: '0';
        
        // 1. FILTRO SQL: Solo busca archivos que terminen en video (.mp4, .mov, etc)
        $query = "
        SELECT p.id, p.imagen_url
        FROM publicaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE u.privacidad = 0
        AND p.id NOT IN ($ids_vistos_sql)
        AND (
            p.imagen_url LIKE '%.mp4' 
            OR p.imagen_url LIKE '%.MP4'
            OR p.imagen_url LIKE '%.mov'
            OR p.imagen_url LIKE '%.MOV'
        )
        ORDER BY RAND()
        LIMIT 1
        ";
        
        $result = mysqli_query($conexion, $query);
        $reel = mysqli_fetch_assoc($result);

        if ($reel) {
            $_SESSION['reels_vistos'][] = $reel['id'];
            $_SESSION['reel_index']++;

            // Guardamos en base de datos
            mysqli_query($conexion, "
                INSERT IGNORE INTO publicaciones_vistas (usuario_id, publicacion_id)
                VALUES ($usuario_id, {$reel['id']})
            ");
        } else {
            // Si no encuentra videos, avisa y redirige
            echo "<script>
                    alert('No hay más videos disponibles para ver.');
                    window.location.href = '../Usuarios/perfil.php';
                  </script>";
            exit();
        }
    } else {
        // Historial: adelante
        $_SESSION['reel_index']++;
        $reel_id = $_SESSION['reels_vistos'][$_SESSION['reel_index']];
        $res = mysqli_query($conexion, "SELECT id, imagen_url FROM publicaciones WHERE id = $reel_id");
        $reel = mysqli_fetch_assoc($res);
    }
}

if ($accion === 'anterior') {
    if ($_SESSION['reel_index'] > 0) {
        $_SESSION['reel_index']--;
        $reel_id = $_SESSION['reels_vistos'][$_SESSION['reel_index']];
        $res = mysqli_query($conexion, "SELECT id, imagen_url FROM publicaciones WHERE id = $reel_id");
        $reel = mysqli_fetch_assoc($res);
    } else {
        $reel_id = $_SESSION['reels_vistos'][0] ?? 0;
        if($reel_id > 0) {
            $res = mysqli_query($conexion, "SELECT id, imagen_url FROM publicaciones WHERE id = $reel_id");
            $reel = mysqli_fetch_assoc($res);
        }
    }
}

// Validación extra por si $reel llega vacío (para evitar errores en el HTML)
if (!$reel) {
    echo "<script>window.location.href = '../Usuarios/perfil.php';</script>";
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reels</title>
    <link rel="stylesheet" href="../../../Estilos/estilos_saals.css">
    <style>
        /* Estilo rápido para asegurar que el video ocupe todo */
        video {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Esto hace que rellene la pantalla sin deformarse */
            background: #000;
        }
    </style>
</head>
<?php include __DIR__ . '/../Templates/navBar.php'; ?>
<body>
    <div class="main">
        <div class="reels-screen">
            <div class="reel-container">
                <div class="reel-video-wrapper">
                    <video 
                        src="../Crear/uploads/<?= htmlspecialchars($reel['imagen_url']) ?>" 
                        autoplay 
                        loop  
                        playsinline 
                        onloadedmetadata="this.volume=0.2"
                    >
                        Tu navegador no soporta videos.
                    </video>
                </div>

                <div class="reel-controls">
                    <?php if ($_SESSION['reel_index'] > 0): ?>
                    <form method="get">
                        <input type="hidden" name="accion" value="anterior">
                        <button type="submit">⬆</button>
                    </form>
                    <?php endif; ?>

                    <form method="get">
                        <input type="hidden" name="accion" value="siguiente">
                        <button type="submit">⬇</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>