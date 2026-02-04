<?php 
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../Sesiones/inicio_sesion.php");
    exit();
}

include 'procesar_perfil.php';

// Evitar warnings si no hay sesión
$foto_perfil = $_SESSION['foto_perfil'] ?? '../../Media/foto_default.png';
$nombreusu   = $_SESSION['username'] ?? '';
$biografia   = $_SESSION['biografia'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil</title>
  <link rel="stylesheet" href="../../Estilos/estilos_perfil.css">
  <link rel="icon" href="../../Media/logo.png">
</head>
<body>
    <?php include __DIR__ . '/../Templates/navBar.php';?>
    <main>
        <div class="profile-container">
            <form class="formCerrarSesion" action="../Sesiones/procesamientos/procesar_cerrar_sesion.php" method="post">
                <button type="submit" class="cerrarSesion">Cerrar sesión</button>
            </form>
            <div class="profile-header">
                <img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Foto de perfil">
                <div class="profile-info">
                    <h2><?= htmlspecialchars($nombreusu) ?></h2>
                    <p class="bio"><?php echo nl2br(htmlspecialchars($biografia)); ?></p>
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
                            $url = $post['imagen_url'];
                            $pid = $post['id'];
                            $likes = $post['total_likes'];
                            $coments = $post['total_comentarios'];
                            
                            $ruta = "../Crear/uploads/" . htmlspecialchars($url);
                            $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                        ?>
                        
                        <div class="post" onclick="openModal(<?= $pid ?>)">
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
            <button id="likeBtn" style="background:none; border:none; cursor:pointer;" onclick="toggleLike()">
               <img id="likeImg" src="../../Media/meGusta.png" width="28">
            </button>
            <div id="modalLikes" style="font-weight:bold; margin-top:5px;"></div>
            <div id="modalFecha" style="font-size:12px; color:#888;"></div>
          </div>

          <form id="commentForm" onsubmit="return submitComment(event)">
            <input type="hidden" id="modalPostId">
            <input type="text" id="commentText" placeholder="Añade un comentario..." required>
            <button type="submit">Publicar</button>
          </form>
        </div>
      </div>
    </div>
    <script>
    const RUTA_BASE = '../Explorar/Procesamiento/'; 

    let pollingInterval = null;
    let likesInterval = null;
    let lastCommentId = 0;
    let currentPostId = null;

    // Función auxiliar para generar HTML de comentario (o pie de foto)
    function renderCommentHTML(c) {
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
        fetch(RUTA_BASE + 'get_post.php?id=' + postId)
        .then(res => res.json())
        .then(data => {
            currentPostId = postId;
            
            // 1. Multimedia
            const mediaDiv = document.getElementById('modalMedia');
            mediaDiv.innerHTML = '';
            const ext = data.imagen_url.split('.').pop().toLowerCase();
            const rutaImg = "../Crear/uploads/" + data.imagen_url;

            if(['mp4','webm'].includes(ext)){
                mediaDiv.innerHTML = `<video src="${rutaImg}" controls autoplay loop></video>`;
            } else {
                mediaDiv.innerHTML = `<img src="${rutaImg}">`;
            }

            // 2. Cabecera del Usuario (Fija arriba)
            const headerDiv = document.getElementById('modalHeaderContainer');
            const fotoUser = data.foto_perfil ? data.foto_perfil : '/Media/foto_default.png';
            
            headerDiv.innerHTML = `
                <div style="display:flex; justify-content: space-between; align-items: center; width: 100%; padding-right: 15px;">
                    
                    <div class="modal-user-header">
                        <form action="../Busqueda/usuarioAjeno.php" method="POST" style="display:flex; align-items:center; margin:0;">
                            <input type="hidden" name="id" value="${data.usuario_id}">
                            <button class="modal-user-button" type="submit">
                                <img class="modal-profile-img" src="${fotoUser}">
                                <span class="modal-username">${data.usuario}</span>
                            </button>
                        </form>
                    </div>

                    <img src="../../Media/basura.png" 
                         onclick="confirmarBorrado(${postId})" 
                         style="cursor:pointer; width:20px; height:20px;" 
                         title="Eliminar publicación">
                </div>
            `;

            // 3. Comentarios + PIE DE FOTO
            const comentariosDiv = document.getElementById('modalComentarios');
            comentariosDiv.innerHTML = '';

            if (data.pie_foto && data.pie_foto.trim() !== "") {
                const pieObj = {
                    id: 'caption', // ID ficticio
                    usuario: data.usuario, // El dueño del post
                    foto_perfil: fotoUser,
                    texto: data.pie_foto
                };
                comentariosDiv.innerHTML += renderCommentHTML(pieObj);
            }

            // Renderizar el resto de comentarios reales
            data.comentarios.forEach(c => {
                comentariosDiv.innerHTML += renderCommentHTML(c);
            });

            // 4. Datos Footer
            const likeImg = document.getElementById('likeImg');
            likeImg.src = (data.liked > 0) ? '../../Media/meGustaDado.png' : '../../Media/meGusta.png';
            document.getElementById('modalLikes').innerText = '🌶️ ' + data.total_likes + ' picantes';
            document.getElementById('modalFecha').innerText = '📅 ' + data.fecha_publicacion;
            document.getElementById('modalPostId').value = postId;

            // Mostrar
            document.getElementById('postModal').style.display = 'flex';

            // Polling
            iniciarPolling(postId, data.comentarios);
        })
        .catch(err => console.error(err));
    }

    function closeModal(){
        document.getElementById('postModal').style.display = 'none';
        document.getElementById('modalMedia').innerHTML = '';
        if(pollingInterval) clearInterval(pollingInterval);
        if(likesInterval) clearInterval(likesInterval);
    }

    document.getElementById('postModal').addEventListener('click', e => {
        if(e.target.id === 'postModal') closeModal();
    });

    function toggleLike(){
        fetch(RUTA_BASE + 'toggle_like.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'post_id=' + currentPostId
        })
        .then(res => res.json())
        .then(data => {
            const img = document.getElementById('likeImg');
            img.src = data.liked ? '../../Media/meGustaDado.png' : '../../Media/meGusta.png';
            document.getElementById('modalLikes').innerText = '🌶️ ' + data.total + ' picantes';
        });
    }

    function submitComment(e){
        e.preventDefault();
        const txt = document.getElementById('commentText').value.trim();
        if(!txt) return;

        fetch(RUTA_BASE + 'add_comment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'post_id=' + currentPostId + '&texto=' + encodeURIComponent(txt)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                const div = document.getElementById('modalComentarios');
                div.innerHTML += renderCommentHTML({
                    id: data.comment_id,
                    usuario: data.usuario,
                    foto_perfil: data.foto_perfil,
                    texto: txt
                });
                document.getElementById('commentText').value = '';
                div.scrollTop = div.scrollHeight;
                lastCommentId = data.comment_id;
            }
        });
    }

    function iniciarPolling(postId, initialComments){
        if(pollingInterval) clearInterval(pollingInterval);
        if(likesInterval) clearInterval(likesInterval);

        // Ultimo ID
        if(initialComments && initialComments.length > 0){
            lastCommentId = initialComments[initialComments.length - 1].id;
        } else {
            lastCommentId = 0;
        }

        pollingInterval = setInterval(() => {
            fetch(`${RUTA_BASE}get_new_comments.php?post_id=${postId}&last_id=${lastCommentId}`)
            .then(res => res.json())
            .then(comments => {
                const div = document.getElementById('modalComentarios');
                comments.forEach(c => {
                    div.innerHTML += renderCommentHTML(c);
                    lastCommentId = c.id;
                });
            });
        }, 2000);

        likesInterval = setInterval(() => {
            fetch(`${RUTA_BASE}get_likes.php?post_id=${postId}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('modalLikes').innerText = '🌶️ ' + data.total + ' picantes';
            });
        }, 1000);
    }
    function confirmarBorrado(postId) {
        const confirmar = confirm("¿Estás seguro de borrar esta publicación? No se podrá recuperar.");
        
        if (confirmar) {
            // Llamamos al archivo que acabamos de crear en la misma carpeta
            fetch('eliminar_post.php', { 
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'post_id=' + postId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Publicación eliminada.");
                    closeModal();
                    location.reload(); // Recargamos para que desaparezca de la cuadrícula
                } else {
                    alert("Error: " + (data.error || "No se pudo eliminar"));
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error de conexión");
            });
        }
    }
    </script>
<?php include __DIR__ . '/../Templates/footer.php';?>
</body>
</html>