<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php';

$usuario_id = intval($_SESSION['id']);

// Inicializamos historial y puntero
if (!isset($_SESSION['reels_vistos'])) $_SESSION['reels_vistos'] = [];
if (!isset($_SESSION['reel_index'])) $_SESSION['reel_index'] = -1;

$accion = $_GET['accion'] ?? 'siguiente';
$current_id = intval($_GET['id'] ?? 0);

if ($accion === 'siguiente') {
    // Si vamos a un nuevo reel, fuera del historial
    if ($_SESSION['reel_index'] === count($_SESSION['reels_vistos']) - 1) {
        // Tomamos un reel nuevo aleatorio
        $ids_vistos_sql = implode(',', $_SESSION['reels_vistos']) ?: '0';
        $query = "
        SELECT p.id, p.imagen_url
        FROM publicaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE u.privacidad = 0
        AND p.id NOT IN ($ids_vistos_sql)
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
            echo "<script>
                    alert('No hay más saals disponibles.');
                    window.location.href = '../Usuarios/perfil.php';
                  </script>";
            exit();
        }
    } else {
        // Vamos adelante dentro del historial
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
        // Si no hay anterior, mantener el actual
        $reel_id = $_SESSION['reels_vistos'][0] ?? 0;
        $res = mysqli_query($conexion, "SELECT id, imagen_url FROM publicaciones WHERE id = $reel_id");
        $reel = mysqli_fetch_assoc($res);
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reels</title>
    <link rel="stylesheet" href="../../../Estilos/estilos_saals.css">
</head>
<?php include __DIR__ . '/../Templates/navBar.php'; ?>
<body>
    <div class="main">
        <div class="reels-screen">
            <div class="reel-container">
                <div class="reel-video-wrapper">
                    <video src="/Php/Crear/uploads/<?= $reel['imagen_url'] ?>" autoplay loop></video>
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