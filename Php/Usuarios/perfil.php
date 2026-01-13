<?php 
session_start();
if (!isset($_SESSION['username'])) {
header("Location: Php/Sesiones/inicio_sesion.php");
exit();
}

include 'procesar_perfil.php';

$stmt = $conexion->prepare(
    "SELECT foto_perfil FROM usuarios WHERE id = ?"
);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

$foto_perfil = $usuario['foto_perfil'] ?: 'foto_default.png';

$nombreusu = $_SESSION['username']?? '';
$biografia = $_SESSION['biografia']?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil</title>
  <link rel="stylesheet" href="../../Estilos/estilos_perfil.css">
</head>
<body>
    <?php include __DIR__ . '/../Templates/navBar.php';?>
    <main>
        <div class="objetos">
            <div class="profile-container">
                <div class="profile-header">
                    

                    <img src="../../Media/foto_default.png" alt="Foto perfil">


                    <div class="profile-info">
                        <h2><?php echo $nombreusu; ?></h2>
                        <p class="bio"><?php echo $biografia; ?></p>
                        <div class="stats">
                            <span><strong><?= $publicaciones ?></strong> publicaciones</span>
                            <a href="tablaSeguidores.php"><span><strong><?= $seguidores ?></strong> seguidores</span></a>
                            <a href="tablaSeguidos.php"><span><strong><?= $seguidos ?></strong> siguiendo</span></a>
                        </div>
                    </div>
                </div>

                <div><a href="editar_perfil.php"><button class="botonEditarPerfil">Editar perfil</button></a></div>
                <div class="profile-posts">
    <?php if (!empty($publicacionesArray)): ?>
        <?php foreach ($publicacionesArray as $post): ?>
            <?php
                $ruta = "../Crear/uploads/" . htmlspecialchars($post);
                $ext = strtolower(pathinfo($post, PATHINFO_EXTENSION));
            ?>
            <div class="post">
                <?php if (in_array($ext, ['mp4', 'webm'])): ?>
                    <video src="<?= $ruta ?>" muted autoplay loop></video>
                <?php else: ?>
                    <img src="<?= $ruta ?>" alt="Post">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay publicaciones todavía</p>
    <?php endif; ?>
</div>

            <!-- Botón de cerrar sesión -->
        </div>
        <form class="formCerrarSesion" action="../Sesiones/procesamientos/procesar_cerrar_sesion.php" method="post">
        <button type="submit" class="cerrarSesion">Cerrar sesión</button>
        </form>
    </main>
    <?php include __DIR__ . '/../Templates/footer.php';?>
</body>
</html>
