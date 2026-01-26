<?php 
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../Sesiones/inicio_sesion.php");
    exit();
}

include 'procesar_perfil.php';
$foto_perfil = $_SESSION['foto_perfil'];
$nombreusu = $_SESSION['username'] ?? '';
$biografia = $_SESSION['biografia'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil</title>
  <link rel="stylesheet" href="../../Estilos/estilos_perfil.css">
</head>
<body>
    <?php include __DIR__ . '/../Templates/navBar.php';?>
    <main>
        <div class="objetos">
            <div class="profile-container">
                <div class="profile-header">
                    <img src="<?= $foto_perfil ?>" alt="Foto de perfil">
                    <div class="profile-info">
                        <h2><?php echo $nombreusu; ?></h2>
                        <p class="bio"><?php echo $biografia; ?></p>
                        <div class="stats">
                            <span><strong><?= $publicaciones ?></strong> publicaciones</span>
                            <a href="tablaSeguidores.php"><span><strong><?= $seguidores ?></strong> seguidores</span></a>
                            <a href="tablaSeguidos.php"><span><strong><?= $seguidos ?></strong> siguiendo</span></a>
                        </div>
                    </div>
                </div>

                <div><a href="editar_perfil.php"><button class="botonEditarPerfil">Editar perfil</button></a></div>
                
                <div class="profile-posts">
                <?php if (!empty($publicacionesArray)): ?>
                    <?php foreach ($publicacionesArray as $post): ?>
                        <?php
                            $urlImagen = $post['imagen_url'];
                            $idPost = $post['id'];
                            
                            // Nuevas variables (si son null, ponemos 0)
                            $likes = $post['total_likes'] ?? 0;
                            $comentarios = $post['total_comentarios'] ?? 0;

                            $ruta = "../Crear/uploads/" . htmlspecialchars($urlImagen);
                            $ext = strtolower(pathinfo($urlImagen, PATHINFO_EXTENSION));
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
                                    <span>💬 <?= $comentarios ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay publicaciones todavía</p>
                <?php endif; ?>
            </div>
            </div>
            
            <form class="formCerrarSesion" action="../Sesiones/procesamientos/procesar_cerrar_sesion.php" method="post">
                <button type="submit" class="cerrarSesion">Cerrar sesión</button>
            </form>
        </div>
    </main>

    <div class="explore-modal" id="postModal">
      <span class="close-modal" onclick="closeModal()">&times;</span>

      <div class="explore-modal-content">
        <div class="modal-left" id="modalMedia"></div>

        <div class="modal-right">
          <div class="post-owner" id="modalOwner"></div>

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
            <input type="text" id="commentText" placeholder="Escribe un comentario..." required>
            <button type="submit">Comentar</button>
          </form>
        </div>
      </div>
    </div>

    <?php include __DIR__ . '/../Templates/footer.php';?>

    <script>
    // IMPORTANTE: Ajusta esta ruta a donde tengas tus archivos de procesamiento
    // (get_post.php, add_comment.php, etc.)
    const RUTA_PROCESAMIENTO = '../Explorar/procesamiento/';

    let pollingInterval = null;
    let likesInterval = null;
    let lastCommentId = 0;
    let currentPostId = null;
    let likedByUser = false;

    // Render HTML de un comentario
    function renderComment(c){
        return `
        <div class="comment" data-id="${c.id}">
            <form action="../Busqueda/usuarioAjeno.php" method="POST" class="comment-avatar-form">
                <input type="hidden" name="id" value="${c.usuario_id}">
                <button type="submit">
                    <img src="${c.foto_perfil}" alt="perfil">
                </button>
            </form>
            <div class="comment-content">
                <span class="comment-user">${c.usuario}</span>
                <span style="color:#333;">${c.texto}</span>
            </div>
        </div>`;
    }

    // Abrir Modal
    function openModal(postId){
        fetch(RUTA_PROCESAMIENTO + 'get_post.php?id=' + postId)
        .then(res => res.json())
        .then(data => {

            currentPostId = postId;
            likedByUser = data.liked > 0;

            // Icono Like
            document.getElementById('likeImg').src = likedByUser 
                ? '../../Media/meGustaDado.png' 
                : '../../Media/meGusta.png';

            // Multimedia (Video o Imagen)
            const mediaDiv = document.getElementById('modalMedia');
            mediaDiv.innerHTML = '';
            const ext = data.imagen_url.split('.').pop().toLowerCase();
            const ruta = "../Crear/uploads/" + data.imagen_url;

            if(['mp4','webm'].includes(ext)){
                const video = document.createElement('video');
                video.src = ruta;
                video.controls = true;
                video.autoplay = true;
                video.loop = true;
                mediaDiv.appendChild(video);
            } else {
                const img = document.createElement('img');
                img.src = ruta;
                mediaDiv.appendChild(img);
            }

            // Dueño del post
            document.getElementById('modalOwner').innerHTML = `
                <form action="../Busqueda/usuarioAjeno.php" method="POST" class="comment-avatar-form">
                    <input type="hidden" name="id" value="${data.usuario_id}">
                    <button type="submit">
                        <img src="${data.foto_perfil}" alt="perfil">
                    </button>
                </form>
                <span class="post-user">${data.usuario}</span>
            `;

            // Info extra
            document.getElementById('modalLikes').innerHTML = '🌶️ ' + data.total_likes + ' picantes';
            document.getElementById('modalFecha').innerHTML = '📅 ' + data.fecha_publicacion;

            // Cargar comentarios
            const comentariosDiv = document.getElementById('modalComentarios');
            comentariosDiv.innerHTML = '';
            data.comentarios.forEach(c => {
                comentariosDiv.innerHTML += renderComment(c);
            });

            lastCommentId = data.comentarios.length 
                ? data.comentarios[data.comentarios.length - 1].id 
                : 0;

            document.getElementById('modalPostId').value = postId;
            document.getElementById('postModal').style.display = 'flex';

            // Reiniciar intervalos
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

    // Toggle Like
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

    // Cerrar Modal
    function closeModal(){
        document.getElementById('postModal').style.display = 'none';
        document.getElementById('modalMedia').innerHTML = ''; 
        if(pollingInterval) clearInterval(pollingInterval);
        if(likesInterval) clearInterval(likesInterval);
    }

    // Click fuera para cerrar
    document.getElementById('postModal').addEventListener('click', e => {
        if (e.target.id === 'postModal') closeModal();
    });

    // Enviar Comentario
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