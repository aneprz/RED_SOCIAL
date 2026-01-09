<?php 
session_start();
if (!isset($_SESSION['username'])) {
header("Location: Php/Sesiones/inicio_sesion.php");
exit();
}

include 'procesar_perfil.php';
$foto_perfil=$_SESSION['foto_perfil']?? 'https://images.vexels.com/media/users/3/271222/isolated/preview/a05636f8a6af3dbe8bf21a419c9f183d-icono-de-muslo-de-pollo.png';
$nombreusu = $_SESSION['username'];
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
                    <img src="<?= $foto_perfil ?>" alt="Foto de perfil">
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
                    <div class="post"><img src="../../Imagenes/post1.jpg" alt="Post 1"></div>
                    <div class="post"><img src="../../Imagenes/post2.jpg" alt="Post 2"></div>
                    <div class="post"><img src="../../Imagenes/post3.jpg" alt="Post 3"></div>
                </div>
            </div>
            <!-- Botón de cerrar sesión -->
            <form class="formCerrarSesion" action="../Sesiones/procesamientos/procesar_cerrar_sesion.php" method="post">
                <button type="submit" class="cerrarSesion">Cerrar sesión</button>
            </form>
        </div>
    </main>
    <?php include __DIR__ . '/../Templates/footer.php';?>
</body>
</html>
