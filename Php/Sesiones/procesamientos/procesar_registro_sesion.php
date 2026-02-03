<?php
session_start();

// 1. Cargar PHPMailer y Base de Datos
// Ajustamos la ruta para salir de: procesamientos -> Login -> Php -> RAÍZ -> vendor
require '../../../vendor/autoload.php'; 
require '../../../BD/conexiones.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function volverConError($mensaje) {
    $_SESSION['error'] = $mensaje;
    // Ajustamos la ruta para volver al formulario
    header("Location: ../registro_sesion.php"); 
    exit();
}

$nombreUsu = trim($_POST['nombre_usuario'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['contraseña'] ?? '';
$pass2 = $_POST['repetirContraseña'] ?? '';
$fechaActual = date("Y-m-d H:i:s");

// --- VALIDACIONES ---
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

// Verificar Usuario existente
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $nombreUsu);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    volverConError("El nombre de usuario ya existe");
}
$stmt->close();

// Verificar Correo existente
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    volverConError("El correo electrónico ya está en uso");
}
$stmt->close();

// --- PREPARAR DATOS ---
$hash = password_hash($pass, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(16)); // Generamos el token único

// --- INSERTAR EN BD ---
// Añadimos token_confirmacion y confirmado (0)
$stmt = $conexion->prepare(
    "INSERT INTO usuarios (username, email, password_hash, fecha_registro, token_confirmacion, confirmado)
     VALUES (?, ?, ?, ?, ?, 0)"
);
$stmt->bind_param("sssss", $nombreUsu, $email, $hash, $fechaActual, $token);

if ($stmt->execute()) {
    
    // --- ENVIAR CORREO CON PHPMAILER ---
    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP de Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'juegosrarossss@gmail.com';  
        $mail->Password   = 'fwhnkmntmvpmoeld';         
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Remitente y Destinatario
        $mail->setFrom('no-reply@salsagram.com', 'Salsagram');
        $mail->addAddress($email);

        // Contenido
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Confirma tu registro en Salsagram 💃';
        
        // Enlace de confirmación (Usando tu IP de AWS)
        $link = "http://54.227.147.179/Php/Login/confirmar.php?email=$email&token=$token";

        $mail->Body = "
            <div style='font-family: sans-serif; text-align: center; padding: 20px;'>
                <h2 style='color: #d63384;'>¡Bienvenido a Salsagram, $nombreUsu!</h2>
                <p>Estás a un paso de empezar a bailar.</p>
                <p>Haz clic en el botón para activar tu cuenta:</p>
                <br>
                <a href='$link' style='background-color: #d63384; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>CONFIRMAR CUENTA</a>
                <br><br>
                <p style='font-size: 12px; color: grey;'>Si no te registraste, ignora este mensaje.</p>
            </div>
        ";

        $mail->send();

        // ÉXITO
        $_SESSION['success'] = "¡Registro correcto! Hemos enviado un correo a <b>$email</b>. Por favor, revísalo para activar tu cuenta.";
        header("Location: ../registro_sesion.php"); // Volvemos al registro para mostrar el mensaje
        exit();

    } catch (Exception $e) {
        // Si falla el correo pero se guardó en BD, podríamos borrarlo o avisar al admin.
        // Por ahora mostramos error.
        volverConError("Usuario registrado pero no se pudo enviar el email. Error: " . $mail->ErrorInfo);
    }

} else {
    volverConError("Error interno al crear el usuario: " . $stmt->error);
}

$stmt->close();
$conexion->close();
?>