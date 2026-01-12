<?php
session_start();
require '../../../BD/conexiones.php';

// Validar existencia de campos
if (
    empty($_POST['nombre_usuario']) ||
    empty($_POST['email']) ||
    empty($_POST['contraseña']) ||
    empty($_POST['repetirContraseña'])
) {
    die("Error: todos los campos son obligatorios");
}

$nombreUsu = trim($_POST["nombre_usuario"]);
$email = trim($_POST["email"]);
$contraseña = $_POST["contraseña"];
$repetirContraseña = $_POST["repetirContraseña"];
$fechaActual = date("Y-m-d H:i:s");

// Validar contraseñas
if ($contraseña !== $repetirContraseña) {
    die("Error: las contraseñas no coinciden");
}

// Validar longitud mínima
if (strlen($contraseña) < 8) {
    die("Error: la contraseña debe tener al menos 8 caracteres");
}

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Error: correo electrónico inválido");
}

// Verificar usuario existente
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $nombreUsu);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Error: el nombre de usuario ya existe");
}
$stmt->close();

// Verificar correo existente
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Error: el correo ya está en uso");
}
$stmt->close();

// Hash de contraseña
$contraseñaHash = password_hash($contraseña, PASSWORD_DEFAULT);

// Insertar usuario
$stmt = $conexion->prepare(
    "INSERT INTO usuarios (username, email, password_hash, fecha_registro)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $nombreUsu, $email, $contraseñaHash, $fechaActual);

if ($stmt->execute()) {
    $_SESSION['foto_perfil'] = '../../../Media/foto_default.png';
    header("Location: ../inicio_sesion.php");
    exit();
} else {
    die("Error al registrar el usuario");
}

$stmt->close();
mysqli_close($conexion);
?>
