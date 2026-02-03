<?php
// Php/Login/confirmar.php
require '../../BD/conexiones.php';

$mensaje = "";
$tipoAlerta = "";

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    // Verificar si existe usuario con ese email, token y que NO esté confirmado (0)
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? AND token_confirmacion = ? AND confirmado = 0");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // ACTIVAR CUENTA
        $stmt->close();
        
        $update = $conexion->prepare("UPDATE usuarios SET confirmado = 1, token_confirmacion = NULL WHERE email = ?");
        $update->bind_param("s", $email);
        
        if ($update->execute()) {
            $titulo = "¡Cuenta Activada! 🎉";
            $mensaje = "Tu correo ha sido verificado correctamente. Ya puedes iniciar sesión.";
            $tipoAlerta = "success";
            $mostrarBoton = true;
        } else {
            $titulo = "Error";
            $mensaje = "Hubo un error al activar la cuenta.";
            $tipoAlerta = "danger";
            $mostrarBoton = false;
        }
    } else {
        $titulo = "Enlace inválido ❌";
        $mensaje = "El enlace ya ha sido usado o ha caducado.";
        $tipoAlerta = "warning";
        $mostrarBoton = true;
    }
} else {
    header("Location: inicio_sesion.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación - Salsagram</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../Estilos/estilos_sesiones.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-5 text-center shadow" style="max-width: 500px;">
            <div class="mb-4">
                <img src="../../Media/logo.png" alt="Salsagram" width="80">
            </div>
            
            <h2 class="mb-3"><?= $titulo ?></h2>
            
            <div class="alert alert-<?= $tipoAlerta ?>" role="alert">
                <?= $mensaje ?>
            </div>

            <?php if (isset($mostrarBoton) && $mostrarBoton): ?>
                <a href="inicio_sesion.php" class="btn btn-primary w-100 mt-3">Ir a Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>