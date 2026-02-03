<?php
session_start();

// Ajusta la ruta si es necesario (según tu estructura de carpetas)
require '../../../BD/conexiones.php';

// Función para devolver errores de forma limpia (igual que en registro)
function volverConError($mensaje) {
    $_SESSION['error_login'] = $mensaje; // Usamos 'error_login' para no mezclar con registro
    header("Location: ../inicio_sesion.php"); 
    exit();
}

// 1. Recibir y limpiar datos
$nombreUsu = trim($_POST['nombre_usuario'] ?? '');
$pass = $_POST['contraseña'] ?? '';

if (empty($nombreUsu) || empty($pass)) {
    volverConError("Por favor, rellena todos los campos.");
}

// 2. Consulta SEGURA (Prepared Statement)
// Pedimos también la columna 'confirmado' y 'privacidad'
$sql = "SELECT id, username, email, password_hash, privacidad, confirmado FROM usuarios WHERE username = ?";
$stmt = $conexion->prepare($sql);

if ($stmt === false) {
    volverConError("Error interno de la base de datos.");
}

$stmt->bind_param("s", $nombreUsu);
$stmt->execute();
$stmt->store_result();

// 3. Verificar si existe el usuario
if ($stmt->num_rows > 0) {
    $stmt->bind_result($id, $username, $email, $hash, $privacidad, $confirmado);
    $stmt->fetch();

    // 4. Verificar Contraseña
    if (password_verify($pass, $hash)) {
        
        // --- AQUÍ ESTÁ LA MAGIA DE LA CONFIRMACIÓN ---
        if ($confirmado == 0) {
            volverConError("Tu cuenta no ha sido activada. Por favor, revisa tu correo electrónico (mira en Spam si no lo ves).");
        }
        // ---------------------------------------------

        // Si todo está bien, creamos la sesión
        $_SESSION['id'] = $id; // ID simple
        $_SESSION['user_id'] = $id; // ID para compatibilidad con otros scripts
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['privacidad'] = $privacidad;

        // Limpiamos errores antiguos si los hubiera
        unset($_SESSION['error_login']);

        // Redirigir al Index
        header("Location: ../../../index.php");
        exit();

    } else {
        volverConError("La contraseña es incorrecta.");
    }
} else {
    volverConError("El usuario no existe.");
}

$stmt->close();
$conexion->close();
?>