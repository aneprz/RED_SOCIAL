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
    <link rel="stylesheet" href="../../../Estilos/estilos_sesiones.css">
    <link rel="icon" href="">
</head>
<body>
    <div class="container d-flex justify-content-center my-5">
        <div class="card p-4" style="max-width: 400px; width: 100%;">
            <h2 class="text-center mb-4">Registrarse como salsero</h2>

            <!-- Mensajes de error / éxito -->
            <?php
            if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
            <?php 
                unset($_SESSION['error']);
            endif;

            if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
            <?php 
                unset($_SESSION['success']);
            endif;
            ?>

            <form action="procesamientos/procesar_registro_sesion.php" method="post">
                <div class="mb-3">
                    <input type="text" id="nombre_usuario" name="nombre_usuario" class="form-control minusculas" placeholder="Nombre de usuario" required>
                </div>

                <div class="mb-3">
                    <input type="text" id="email" name="email" class="form-control" placeholder="Correo electrónico" required>
                </div>

                <div class="mb-3">
                    <input placeholder="Contraseña" type="password" id="contraseña" name="contraseña" class="form-control" onclick="textoValidarContraseña()" required>
                </div>

                <div class="mb-3">
                    <input placeholder="Repetir contraseña" type="password" id="repetirContraseña" name="repetirContraseña" class="form-control" onclick="textoValidarContraseña()" required>
                </div>
                
                <!--Esto solo se muestra si se hace click en los inputs de contraseña-->
                <div id="requisitosContraseña" class="my-3" style="display: none;">
                    <p class="textoContraseña" id="longitud"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="M12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>
                    Mínimo 8 carácteres.</p>
                    <p class="textoContraseña" id="mayuscula"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="M12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>
                    Al menos una mayúscula.</p>
                    <p class="textoContraseña" id="minuscula"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="M12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>
                    Al menos una minúscula.</p>
                    <p class="textoContraseña" id="numero"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="M12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>
                    Al menos un número.</p>
                    <p class="textoContraseña" id="caracterEspecial"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="M12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>
                    Al menos un carácter especial.</p>
                    <p class="textoContraseña" id="contraseñasRepetidas"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="M12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>
                    Se repiten las contraseñas.</p>
                </div>

                <button id="registrarse" type="submit" class="botonRegistroSesion w-100" disabled>Registrarse</button>
                <p>¿Ya tienes usuario?<a href="inicio_sesion.php">Inicia sesión</a></p>
                
                <!--JavaScript-->
                <script src="validaciones/validacion_registro.js"></script>
            </form>
        </div>
    </div>    
</body>
</html>