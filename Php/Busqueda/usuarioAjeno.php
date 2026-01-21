<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php';

if (!isset($_POST['id'])) {
    die("ID de usuario no valida.");
}

$id = intval($_POST['id']);

$resultUsuario = mysqli_query($conexion, "SELECT foto_perfil, username, bio FROM usuarios WHERE id = $id");

if (!$resultUsuario || mysqli_num_rows($resultUsuario) == 0) {
    die("Usuario no encontrado.");
}

$usuario = mysqli_fetch_assoc($resultUsuario);
$foto_perfil = '/' . ltrim($usuario['foto_perfil'], '/');
$nombreusu = $usuario['username'];
$biografia = $usuario['bio'];

$resultSeguidores = mysqli_query($conexion, "SELECT COUNT(seguidor_id) AS total FROM seguidores WHERE seguido_id = $id");
$seguidores = mysqli_fetch_assoc($resultSeguidores)['total'];

// Verificar si el usuario actual ya sigue al usuario del perfil
$usuarioActualId = $_SESSION['id']; // Asumo que guardas el id del usuario en la sesión
$resultYaSigue = mysqli_query($conexion, "SELECT * FROM seguidores WHERE seguidor_id = $usuarioActualId AND seguido_id = $id");
$yaSigue = mysqli_num_rows($resultYaSigue) > 0;

$resultSeguidos = mysqli_query($conexion, "SELECT COUNT(seguido_id) AS total FROM seguidores WHERE seguidor_id = $id");
$seguidos = mysqli_fetch_assoc($resultSeguidos)['total'];

$resultPublicaciones = mysqli_query($conexion, "SELECT COUNT(usuario_id) AS total FROM publicaciones WHERE usuario_id = $id");
$publicaciones = mysqli_fetch_assoc($resultPublicaciones)['total'];

$publicacionesArray = [];
$resultPost = mysqli_query($conexion, "SELECT imagen_url FROM publicaciones WHERE usuario_id = $id");

if ($resultPost) {
    while ($rowPost = mysqli_fetch_assoc($resultPost)) {
        $publicacionesArray[] = $rowPost['imagen_url'];
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($nombreusu) ?></title>
    <link rel="stylesheet" href="../../Estilos/estilos_perfil.css">
</head>
<body>
    <?php include __DIR__ . '/../Templates/navBar.php'; ?>
    <main>
        <div class="objetos">
            <div class="profile-container">
                <div class="profile-header">
                    <img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Foto de perfil">
                    <div class="profile-info">
                        <h2><?= htmlspecialchars($nombreusu) ?></h2>
                        <p class="bio"><?= htmlspecialchars($biografia) ?></p>
                        <div class="stats">
                            <span><strong><?= $publicaciones ?></strong> publicaciones</span>
                            <a href="tablaSeguidores.php"><span><strong><?= $seguidores ?></strong> seguidores</span></a>
                            <a href="tablaSeguidos.php"><span><strong><?= $seguidos ?></strong> siguiendo</span></a>
                        </div>
                        <?php
                            $miId = $_SESSION['id'];
                            if ($miId != $id) {
                                // Por seguridad, inicializamos como "Seguir". El estado real lo dará el PHP de seguir_usuario.php al hacer click
                                $estadoBtn = 'Seguir';
                            } else {
                                $estadoBtn = '';
                            }
                        ?>
                        <?php if ($miId != $id): ?>
                            <button id="btnSeguir" data-id="<?= $id ?>"><?= $estadoBtn ?></button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="profile-posts">
                    <?php if (!empty($publicacionesArray)): ?>
                        <?php foreach ($publicacionesArray as $post): ?>
                            <?php
                                $ruta = '/Php/Crear/uploads/' . htmlspecialchars($post);
                                $ext = strtolower(pathinfo($post, PATHINFO_EXTENSION));
                            ?>
                            <div class="post">
                                <?php if (in_array($ext, ['mp4', 'webm'])): ?>
                                    <video src="<?= $ruta ?>" muted autoplay loop></video>
                                <?php elseif (in_array($ext, ['jpeg', 'jpg', 'png', 'gif'])): ?>
                                    <img src="<?= $ruta ?>" alt="Post">
                                <?php else: ?>
                                    <p>Archivo no soportado: <?= htmlspecialchars($post) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No hay publicaciones todavía</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/../Templates/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btnSeguir');
            if (!btn) return; // <--- si no existe, salir

            btn.addEventListener('click', () => {
                const idUsuario = btn.getAttribute('data-id');

                fetch('../Usuarios/seguir_usuario.php', { // ajustar ruta
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id_usuario=${idUsuario}`
                })
                .then(response => response.text())
                .then(text => {
                    console.log('Respuesta cruda:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.status === 'success') {
                            switch(data.estado) {
                                case 'siguiendo': btn.textContent = 'Siguiendo'; break;
                                case 'solicitado': btn.textContent = 'Solicitado'; break;
                                case 'no_seguido': btn.textContent = 'Seguir'; break;
                            }
                        } else {
                            alert('Error: ' + (data.message || 'Inténtalo de nuevo'));
                        }
                    } catch(e) {
                        console.error('Error parseando JSON:', e);
                    }
                })
                .catch(err => console.error('Fetch error:', err));
            });
        });
    </script>
</body>
</html>
