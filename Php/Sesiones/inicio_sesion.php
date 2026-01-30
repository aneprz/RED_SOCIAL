<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="" type="">
    <title>Inicio sesión - Salsagram</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../Estilos/estilos_sesiones.css">
</head>
<body>
    <div class="container d-flex justify-content-center my-5">
        <div class="card p-4" style="max-width: 400px; width: 100%;">
            <div class="d-flex justify-content-center align-items-center m-3">
                <img class="w-25 mx-2" src="../../Media/logo.png" alt="">
                <h2>Iniciar sesión</h2>
            </div>

            <form action="procesamientos/procesar_inicio_sesion.php" method="post">
                <div class="mb-3">
                    <label for="username_login" class="form-label">Nombre de Usuario:</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password_login" class="form-label">Contraseña:</label>
                    <div class="input-group">
                        <input type="password" id="contraseña" name="contraseña" class="form-control border-end-0" required>
                        
                        <button class="btn border border-start-0 bg-white text-secondary" type="button" id="btnVerPass" style="border-color: #dee2e6;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-slash-fill" viewBox="0 0 16 16" id="iconoPass">
                                <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7.029 7.029 0 0 0 2.79-.588zM5.21 3.088A7.028 7.028 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474L5.21 3.089z"/>
                                <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829l-2.83-2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12-.708.708z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <p>¿No tienes usuario? <a href="registro_sesion.php">Créalo aquí</a></p>

                <button type="submit" class="botonIniciarSesion w-100">Iniciar Sesión</button>
            </form>
        </div>
    </div>
    <script src="validaciones/validacion_inicio.js"></script>
</body>
</html>