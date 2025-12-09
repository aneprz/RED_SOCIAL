<?php
session_start();
include '../../BD/conexiones.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Salsagram</title>

    <!--Bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--CSS-->
    <link rel="stylesheet" href="">
    <link rel="icon" href="">
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="card p-4" style="max-width: 400px; width: 100%;">
            <h2 class="text-center mb-4">Registrarse como salsero</h2>

            <form action="procesamientos/procesar_registro_sesion.php" method="post">
                <div class="mb-3">
                    <label for="nombre_usuario" class="form-label">Nombre de Usuario:</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico:</label>
                    <input type="text" id="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="contraseña" class="form-label">Contraseña:</label>
                    <input type="password" id="contraseña" name="contraseña" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="repetirContraseña" class="form-label">Repita la contraseña:</label>
                    <input type="password" id="repetirContraseña" name="repetirContraseña" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
            </form>
        </div>
    </div>    
</body>
</html>