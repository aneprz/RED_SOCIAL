<?php
session_start();
include '../../../BD/conexiones.php';

$nombreUsu = $_POST["nombre_usuario"];
$email = $_POST["email"];
$contraseña = $_POST["contraseña"];
$repetirContraseña = $_POST["repetirContraseña"];
$fechaActual=date("Y-m-d H:i:s");
$contraseñaHash = password_hash($contraseña, PASSWORD_DEFAULT);

//Validar que las contraseñas introducidas coincidan
if ($contraseña != $repetirContraseña){
    echo "<script>alert('Error. Las contraseñas no coinciden')</script>";
    exit();
}

//Validar que el formato del correo electrónico sea válido
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Error. Correo electrónico inválido')</script>";
    exit();
}

//Validar que no exista ya el usuario
$verificar_usuario = mysqli_query($conexion, "SELECT * FROM usuarios WHERE username='$nombreUsu' ");
if (mysqli_num_rows($verificar_usuario) > 0){
    echo "<script>alert('Error. Este usuario ya está en uso')</script>";
    exit();
}

//Validar que el correo no esté en uso
$verificar_correo = mysqli_query($conexion, "SELECT * FROM usuarios WHERE email='$email' ");
if (mysqli_num_rows($verificar_correo) > 0){
    echo "<script>alert('Error. El correo electrónico ya está en uso')</script>";
    exit();
}


$query = "INSERT INTO usuarios (username, email, password_hash, fecha_registro) 
VALUES ('$nombreUsu', '$email', '$contraseñaHash', '$fechaActual')";

$ejecutar = mysqli_query($conexion, $query);

if ($ejecutar){
    echo "<script>
            alert('Usuario creado correctamente');
            window.location.href = '../inicio_sesion.php';
          </script>";

} else {
    echo "<div class='alert alert-danger shadow rounded'>
            Error al insertar usuario: " . mysqli_error($conexion) . "
          </div>";
}
$_SESSION['foto_perfil'] = 'https://images.vexels.com/media/users/3/271222/isolated/preview/a05636f8a6af3dbe8bf21a419c9f183d-icono-de-muslo-de-pollo.png';;
mysqli_close($conexion);
?>