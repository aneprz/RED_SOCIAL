<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../Sesiones/inicio_sesion.php");
    exit();
}

include '../../BD/conexiones.php';

if (!isset($_POST['id'])) die("ID de usuario no válida.");

$id = intval($_POST['id']);

// 1. Datos del usuario a visitar
$stmt = $pdo->prepare("SELECT foto_perfil, username, bio, privacidad FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$usuario) die("Usuario no encontrado.");

$foto_perfil = '/' . ltrim($usuario['foto_perfil'], '/');
$nombreusu = $usuario['username'];
$biografia = $usuario['bio'];
$esPrivada = $usuario['privacidad'];

// 2. Estadísticas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE seguido_id = ?");
$stmt->execute([$id]);
$seguidores = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = ?");
$stmt->execute([$id]);
$seguidos = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM publicaciones WHERE usuario_id = ?");
$stmt->execute([$id]);
$publicaciones = $stmt->fetchColumn();

// 3. Publicaciones (Traemos contadores para el Grid)
$stmt = $pdo->prepare("
    SELECT p.id, p.imagen_url,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as total_likes,
    (SELECT COUNT(*) FROM comentarios WHERE post_id = p.id) as total_comentarios
    FROM publicaciones p 
    WHERE usuario_id = ? 
    ORDER BY fecha_publicacion DESC
");
$stmt->execute([$id]);
$publicacionesArray = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Estado del botón Seguir
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
    /* Estilos específicos del botón seguir */
    #btnSeguir {
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }
    #btnSeguir[data-estado="Seguir"] { background-color: #28a745; color: white; }
    #btnSeguir[data-estado="Siguiendo"] { background-color: #6c757d; color: white; }
    #btnSeguir[data-estado="Solicitado"] { background-color: #ffc107; color: black; }
    #btnSeguir:hover { opacity: 0.8; }
</style>
</head>
<body>
    <?php include __DIR__ . '/../Templates/navBar.php'; ?>
    
    <main>
        <div class="profile-container">
            <div class="profile-header">
                <img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Foto de perfil">
                <div class="profile-info">
                    <h2><?= htmlspecialchars($nombreusu) ?></h2>
                    <p class="bio"><?= nl2br(htmlspecialchars($biografia)) ?></p>
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
                        $ruta = '/Php/Crear/uploads/' . htmlspecialchars($post['imagen_url']);
                        $ext = strtolower(pathinfo($post['imagen_url'], PATHINFO_EXTENSION));
                        $idPost = $post['id'];
                        $likes = $post['total_likes'];
                        $coments = $post['total_comentarios'];
                    ?>
                    
                    <div class="post" onclick="openModal(<?= $idPost ?>)">
                        <?php if (in_array($ext, ['mp4', 'webm'])): ?>
                            <video class="media" src="<?= $ruta ?>" muted loop onmouseover="this.play()" onmouseout="this.pause()"></video>
                        <?php else: ?>
                            <img class="media" src="<?= $ruta ?>" alt="Post">
                        <?php endif; ?>

                        <div class="overlay">
                            <div class="overlay-info">
                                <span>🌶️ <?= $likes ?></span>
                                <span>💬 <?= $coments ?></span>
                            </div>
                        </div>
                    </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay publicaciones todavía</p>
                <?php endif; ?>
            </div>
        </div> 
    </main>

    <div class="explore-modal" id="postModal">
      <span class="close-modal" onclick="closeModal()">&times;</span>

      <div class="explore-modal-content">
        <div class="modal-left" id="modalMedia"></div>

        <div class="modal-right">
          <div id="modalHeaderContainer"></div>

          <div id="modalComentarios"></div>
          
          <div class="post-meta">
            <button id="likeBtn" class="like-btn" onclick="toggleLike()">
               <img id="likeImg" src="../../Media/meGusta.png" alt="like">
            </button>
            <div id="modalLikes"></div>
            <div id="modalFecha"></div>
          </div>

          <form id="commentForm" onsubmit="return submitComment(event)">
            <input type="hidden" id="modalPostId">
            <input type="text" id="commentText" placeholder="Añade un comentario..." required>
            <button type="submit">Publicar</button>
          </form>
        </div>
      </div>
    </div>

    <?php include __DIR__ . '/../Templates/footer.php'; ?>

    <script>
    // 1. SCRIPT BOTÓN SEGUIR
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btnSeguir');
        if (btn) {
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
                    } catch(e) { console.error('Error JSON:', e); }
                })
                .catch(err => console.error('Fetch error:', err));
            });
        }
    });

    // 2. SCRIPT MODAL (Con lógica de pie de foto)
    
    // Ruta hacia la carpeta de Explorar donde están los PHP
    const RUTA_PROCESAMIENTO = '../Explorar/procesamiento/';

    let pollingInterval = null;
    let likesInterval = null;
    let lastCommentId = 0;
    let currentPostId = null;
    let likedByUser = false;

    // Función auxiliar para renderizar comentarios
    function renderComment(c){
        // En los comentarios la API devuelve 'usuario', en el post devuelve 'usuario' o 'username'
        const nombre = c.usuario || c.username; 
        
        return `
        <div class="comment" data-id="${c.id}">
            <div class="comentarioUsuario">
                <img class="fotoPerfilComentarios" src="${c.foto_perfil}" alt="perfil">
                <div>
                    <span class="comment-user">${nombre}</span>
                    <p class="comment-text">${c.texto}</p>
                </div>
            </div>
        </div>`;
    }

    function openModal(postId){
        fetch(RUTA_PROCESAMIENTO + 'get_post.php?id=' + postId)
        .then(res => res.json())
        .then(data => {
            currentPostId = postId;
            likedByUser = data.liked > 0;

            document.getElementById('likeImg').src = likedByUser 
                ? '../../Media/meGustaDado.png' 
                : '../../Media/meGusta.png';

            // 1. Multimedia
            const mediaDiv = document.getElementById('modalMedia');
            mediaDiv.innerHTML = '';
            // Ajustamos ruta de uploads (desde Busqueda -> Crear)
            const ruta = '../Crear/uploads/' + data.imagen_url;
            const ext = data.imagen_url.split('.').pop().toLowerCase();

            if(['mp4','webm'].includes(ext)){
                mediaDiv.innerHTML = `<video src="${ruta}" controls autoplay loop style="width:100%; height:100%; object-fit:contain;"></video>`;
            } else {
                mediaDiv.innerHTML = `<img src="${ruta}" style="width:100%; height:100%; object-fit:contain;">`;
            }

            // 2. Cabecera (Usuario del Post)
            const headerDiv = document.getElementById('modalHeaderContainer');
            // Validar ruta foto
            const fotoUser = data.foto_perfil ? data.foto_perfil : '/Media/foto_default.png';
            
            headerDiv.innerHTML = `
                <div class="modal-user-header">
                    <form action="usuarioAjeno.php" method="POST" style="display:flex; align-items:center;">
                        <input type="hidden" name="id" value="${data.usuario_id}">
                        <button class="modal-user-button" type="submit">
                            <img class="modal-profile-img" src="${fotoUser}">
                            <span class="modal-username">${data.usuario}</span>
                        </button>
                    </form>
                </div>
            `;

            document.getElementById('modalLikes').innerHTML = '🌶️ ' + data.total_likes + ' picantes';
            document.getElementById('modalFecha').innerHTML = '📅 ' + data.fecha_publicacion;

            // 3. Comentarios + PIE DE FOTO
            const comentariosDiv = document.getElementById('modalComentarios');
            comentariosDiv.innerHTML = '';

            // [LÓGICA CLAVE] Si hay pie de foto, lo ponemos primero
            if (data.pie_foto && data.pie_foto.trim() !== "") {
                const pieObj = {
                    id: 'caption-' + data.id, // ID ficticio
                    usuario: data.usuario,    // El dueño del post
                    foto_perfil: fotoUser,
                    texto: data.pie_foto      // El texto es el pie de foto
                };
                comentariosDiv.innerHTML += renderComment(pieObj);
            }

            // Renderizar comentarios reales
            data.comentarios.forEach(c => {
                comentariosDiv.innerHTML += renderComment(c);
            });

            lastCommentId = data.comentarios.length 
                ? data.comentarios[data.comentarios.length - 1].id 
                : 0;

            document.getElementById('modalPostId').value = postId;
            document.getElementById('postModal').style.display = 'flex';

            if(pollingInterval) clearInterval(pollingInterval);
            if(likesInterval) clearInterval(likesInterval);

            // Polling Comentarios
            pollingInterval = setInterval(() => {
                fetch(`${RUTA_PROCESAMIENTO}get_new_comments.php?post_id=${postId}&last_id=${lastCommentId}`)
                .then(res => res.json())
                .then(comments => {
                    comments.forEach(c => {
                        if(!comentariosDiv.querySelector(`.comment[data-id="${c.id}"]`)){
                            comentariosDiv.innerHTML += renderComment(c);
                            lastCommentId = c.id;
                        }
                    });
                });
            }, 2000);

            // Polling Likes
            likesInterval = setInterval(() => {
                fetch(`${RUTA_PROCESAMIENTO}get_likes.php?post_id=${postId}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('modalLikes').innerHTML = '🌶️ ' + data.total + ' picantes';
                });
            }, 1000);
        });
    }

    function toggleLike(){
        fetch(RUTA_PROCESAMIENTO + 'toggle_like.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'post_id='+currentPostId
        })
        .then(res => res.json())
        .then(data => {
            likedByUser = data.liked;
            document.getElementById('likeImg').src = likedByUser 
                ? '../../Media/meGustaDado.png' 
                : '../../Media/meGusta.png';
            document.getElementById('modalLikes').innerHTML = '🌶️ ' + data.total + ' picantes';
        });
    }

    function closeModal(){
        document.getElementById('postModal').style.display = 'none';
        document.getElementById('modalMedia').innerHTML = ''; 
        if(pollingInterval) clearInterval(pollingInterval);
        if(likesInterval) clearInterval(likesInterval);
    }

    document.getElementById('postModal').addEventListener('click', e => {
        if (e.target.id === 'postModal') closeModal();
    });

    function submitComment(e){
        e.preventDefault();
        const input = document.getElementById('commentText');
        const texto = input.value.trim();
        if(!texto) return;

        fetch(RUTA_PROCESAMIENTO + 'add_comment.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'post_id='+currentPostId+'&texto='+encodeURIComponent(texto)
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                const comentariosDiv = document.getElementById('modalComentarios');
                comentariosDiv.innerHTML += renderComment({
                    id: data.comment_id,
                    usuario: data.usuario,
                    usuario_id: data.usuario_id,
                    foto_perfil: data.foto_perfil,
                    texto: texto
                });
                input.value='';
                lastCommentId = data.comment_id;
                comentariosDiv.scrollTop = comentariosDiv.scrollHeight;
            }
        });
    }
    </script>
</body>
</html>