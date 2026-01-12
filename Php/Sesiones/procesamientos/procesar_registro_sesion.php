<?php
session_start();
require '../../../BD/conexiones.php';

function volverConError($mensaje) {
    $_SESSION['error'] = $mensaje;
    header("Location: ../registro_sesion.php");
    exit();
}

$nombreUsu = trim($_POST['nombre_usuario'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['contraseña'] ?? '';
$pass2 = $_POST['repetirContraseña'] ?? '';
$fechaActual = date("Y-m-d H:i:s");

if ($nombreUsu === '' || $email === '' || $pass === '' || $pass2 === '') {
    volverConError("Todos los campos son obligatorios");
}

if ($pass !== $pass2) {
    volverConError("Las contraseñas no coinciden");
}

if (strlen($pass) < 8) {
    volverConError("La contraseña debe tener al menos 8 caracteres");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    volverConError("Correo electrónico inválido");
}

// Usuario existente
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $nombreUsu);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    volverConError("El nombre de usuario ya existe");
}
$stmt->close();

// Correo existente
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    volverConError("El correo electrónico ya está en uso");
}
$stmt->close();

$hash = password_hash($pass, PASSWORD_DEFAULT);

// Insertar
$stmt = $conexion->prepare(
    "INSERT INTO usuarios (username, email, password_hash, fecha_registro)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $nombreUsu, $email, $hash, $fechaActual);

if (!$stmt->execute()) {
    volverConError("Error interno al crear el usuario");
}

//Para que se ponga la foto de perfil por defecto
$_SESSION['foto_perfil'] = '../../../Media/foto_default.png';

$_SESSION['success'] = "Usuario creado correctamente. Ya puedes iniciar sesión";
header("Location: ../inicio_sesion.php");
exit();
?>