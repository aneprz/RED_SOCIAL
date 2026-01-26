<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php'; // Se asume que $pdo está definido

if (!isset($_POST['id'])) die("ID de usuario no válida.");

$id = intval($_POST['id']);

// Datos del usuario
$stmt = $pdo->prepare("SELECT foto_perfil, username, bio, privacidad FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$usuario) die("Usuario no encontrado.");

$foto_perfil = '/' . ltrim($usuario['foto_perfil'], '/');
$nombreusu = $usuario['username'];
$biografia = $usuario['bio'];
$esPrivada = $usuario['privacidad'];

// Estadísticas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE seguido_id = ?");
$stmt->execute([$id]);
$seguidores = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = ?");
$stmt->execute([$id]);
$seguidos = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM publicaciones WHERE usuario_id = ?");
$stmt->execute([$id]);
$publicaciones = $stmt->fetchColumn();

// Publicaciones
$stmt = $pdo->prepare("SELECT imagen_url FROM publicaciones WHERE usuario_id = ?");
$stmt->execute([$id]);
$publicacionesArray = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Estado del botón
$miId = $_SESSION['id'];
$estadoBtn = '';
if ($miId != $id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
    $stmt->execute([$miId, $id]);
    $yaSigo = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_seguimiento WHERE solicitante_id = ? AND receptor_id = ?");
    $stmt->execute([$miId, $id]);
    $yaSolicite = $stmt->fetchColumn();

    if ($yaSigo > 0) $estadoBtn = 'Siguiendo';
    elseif ($yaSolicite > 0) $estadoBtn = 'Solicitado';
    else $estadoBtn = 'Seguir';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil de <?= htmlspecialchars($nombreusu) ?></title>
<link rel="stylesheet" href="../../Estilos/estilos_perfil.css">
<link rel="icon" type="image/png" href="/Media/logo.png">
<style>
    /* Botón de seguimiento */
    #btnSeguir {
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }

    /* Estado Seguir */
    #btnSeguir[data-estado="Seguir"] {
        background-color: #28a745;
        color: white;
    }

    /* Estado Siguiendo */
    #btnSeguir[data-estado="Siguiendo"] {
        background-color: #6c757d;
        color: white;
    }

    /* Estado Solicitado */
    #btnSeguir[data-estado="Solicitado"] {
        background-color: #ffc107;
        color: black;
    }

    #btnSeguir:hover {
        opacity: 0.8;
    }
</style>
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
                <?php if ($miId != $id): ?>
                    <button id="btnSeguir" data-id="<?= $id ?>" data-estado="<?= $estadoBtn ?>"><?= $estadoBtn ?></button>
                <?php endif; ?>
            </div>
        </div>

        <div class="profile-posts">
            <?php if (!empty($publicacionesArray)): ?>
                <?php foreach ($publicacionesArray as $post):
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
    if (!btn) return;

    const actualizarEstilo = (estado) => {
        btn.textContent = estado;
        btn.setAttribute('data-estado', estado);
    };

    btn.addEventListener('click', () => {
        const idUsuario = btn.getAttribute('data-id');

        fetch('../Usuarios/seguir_usuario.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id_usuario=${idUsuario}`
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (data.status === 'success') {
                    switch(data.estado) {
                        case 'siguiendo': actualizarEstilo('Siguiendo'); break;
                        case 'solicitado': actualizarEstilo('Solicitado'); break;
                        case 'no_seguido': actualizarEstilo('Seguir'); break;
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
