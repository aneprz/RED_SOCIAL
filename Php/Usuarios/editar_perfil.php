<?php
session_start();
if (!isset($_SESSION['username'])) {
header("Location: Php/Sesiones/inicio_sesion.php");
exit();
}

include 'procesar_editar_perfil.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
      <link rel="stylesheet" href="../../Estilos/estilos_editar_perfil.css">

</head>
<body>
    <div class="profile-container">
        <h1>Editar perfil</h1>
        <form class="profile-form" 
            action="procesar_editar_perfil.php" 
            method="post" 
            enctype="multipart/form-data">

            <div class="profile-photo">
                <img src="<?= $foto_perfil ?>" alt="Foto de perfil"><br>
                <input type="file" name="foto_perfil" accept="image/*">
            </div>

            <label>
                Nombre de usuario
                <input type="text" name="nuevousu" placeholder="<?php echo $nombreusu; ?>">
            </label>

            <label>
                Contraseña
                <input type="password" name="contrasena" placeholder="Nueva contraseña">
            </label>

            <label>
                Biografía
                <textarea name="biografia" placeholder="Cuéntanos algo sobre ti..."></textarea>
            </label>

            <button type="submit">Guardar cambios</button>
        </form>
        </div>
</body>
</html>