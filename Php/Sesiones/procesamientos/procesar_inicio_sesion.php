<?php
session_start();

include '../../../BD/conexiones.php';

// Recibir los datos del formulario y limpiarlos
$nombre_usuario= $_POST['nombre_usuario'];
$contraseña= $_POST['contraseña'];
$contraseña=hash('sha256', $contraseña);


$query = "SELECT * FROM usuarios WHERE username = '$nombre_usuario' AND password_hash = '$contraseña' LIMIT 1";

$result = mysqli_query($conexion, $query);

// Verificar si el usuario existe
if ($result && mysqli_num_rows($result) > 0) {
    $datos_usuario = mysqli_fetch_assoc($result);
    
    // Guardar datos del usuario en la sesión
    $_SESSION['id'] = $datos_usuario['id'];
    $_SESSION['username'] = $datos_usuario['username'];
    $_SESSION['admin'] = $datos_usuario['admin'];
    $_SESSION['email'] = $datos_usuario['email'];

    // Redirigir a la página de bienvenida
    header("location: ../../index.php");
    exit();
} else {
    // Si el usuario no existe o la contraseña es incorrecta
    echo "<script type='text/javascript'>
        alert('Usuario o contraseña incorrectos. Intenta de nuevo.');
        window.location.href = '../inicio_sesion.php';
    </script>";
    exit();
}
?>