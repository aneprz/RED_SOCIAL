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
    echo "<div class='alert alert-warning shadow rounded'>Error. Las contraseñas no coinciden.</div>";
    exit();
}

//Validar que el formato del correo electrónico sea válido
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<div class='alert alert-warning shadow rounded'>Error. Correo electrónico inválido.</div>";
    exit();
}

//Validar que no exista ya el usuario
$verificar_usuario = mysqli_query($conexion, "SELECT * FROM usuarios WHERE username='$nombreUsu' ");
if (mysqli_num_rows($verificar_usuario) > 0){
    echo "<div class='alert alert-warning shadow rounded'>Este usuario ya está en uso, intenta con uno diferente.</div>";
    exit();
}

//Validar que el correo no esté en uso
$verificar_correo = mysqli_query($conexion, "SELECT * FROM usuarios WHERE email='$email' ");
if (mysqli_num_rows($verificar_correo) > 0){
    echo "<div class='alert alert-warning shadow rounded'>Este correo ya está en uso, intenta con uno diferente.</div>";
    exit();
}


$query = "INSERT INTO usuarios (username, email, password_hash, fecha_registro) 
VALUES ('$nombreUsu', '$email', '$contraseñaHash', '$fechaActual')";

$ejecutar = mysqli_query($conexion, $query);

if ($ejecutar){
    echo "<script>
            alert('Usuario creado correctamente');
            window.location.href = '../registro_sesion.php';
          </script>";

} else {
    echo "<div class='alert alert-danger shadow rounded'>
            Error al insertar usuario: " . mysqli_error($conexion) . "
          </div>";
}

mysqli_close($conexion);
?>