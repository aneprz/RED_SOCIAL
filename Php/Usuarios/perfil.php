<?php 
session_start();
if (!isset($_SESSION['username'])) {
header("Location: Php/Sesiones/inicio_sesion.php");
exit();
}

include 'procesar_perfil.php';
$nombreusu = $_SESSION['username'];
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
                    <img src="https://i.scdn.co/image/ab67616d00001e0237ebe5a4594a9569e0821dd3" alt="Foto de perfil">
                    <div class="profile-info">
                        <h2><?php echo $nombreusu; ?></h2>
                        <p class="bio">Esta es tu biografía. Puedes poner algo sobre ti.</p>
                        <div class="stats">
                            <span><strong><?= $publicaciones ?></strong> publicaciones</span>
                            <a href="tablaSeguidores.php"><span><strong><?= $seguidores ?></strong> seguidores</span></a>
                            <a href="tablaSeguidos.php"><span><strong><?= $seguidos ?></strong> siguiendo</span></a>
                        </div>
                    </div>
                </div>

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
