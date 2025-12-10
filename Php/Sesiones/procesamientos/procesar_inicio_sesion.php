<?php
session_start();

include '../../../BD/conexiones.php';

// Recibir los datos del formulario y limpiarlos
$nombre_usuario= $_POST['nombre_usuario'];
$contraseña= $_POST['contraseña'];

//Busca al usuario por el nombre
$query = "SELECT * FROM usuarios WHERE username = '$nombre_usuario' LIMIT 1";
$result = mysqli_query($conexion, $query);

// Verificar si el usuario existe
if ($result && mysqli_num_rows($result) > 0) {
    $datos_usuario = mysqli_fetch_assoc($result);
    
    if (password_verify($contraseña, $datos_usuario['password_hash'])) {
        // Guardar datos del usuario en la sesión
        $_SESSION['id'] = $datos_usuario['id'];
        $_SESSION['username'] = $datos_usuario['username'];
        $_SESSION['email'] = $datos_usuario['email'];

        // Redirigir a la página de bienvenida
        header("location: ../../../index.php");
        exit(); 
    } else {
        // Contraseña incorrecta
        echo "<script>
                alert('Contraseña incorrecta.');
                window.location.href = '../inicio_sesion.php';
              </script>";
        exit();
    }
} else {
    // Si el usuario no existe o la contraseña es incorrecta
    echo "<script type='text/javascript'>
        alert('El usuario no existe.');
        window.location.href = '../inicio_sesion.php';
    </script>";
    exit();
}
?>