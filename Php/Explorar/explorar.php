<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../Sesiones/inicio_sesion.php");
    exit();
}

require '../../BD/conexiones.php';

$sql = "SELECT p.id, p.imagen_url,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS total_likes,
            (SELECT COUNT(*) FROM comentarios WHERE post_id = p.id) AS total_comentarios
        FROM publicaciones p
        ORDER BY p.id DESC";
$result = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Explorar</title>
<link rel="stylesheet" href="../../Estilos/estilos_explorar.css">
</head>
<body>

<?php include __DIR__ . '/../Templates/navBar.php'; ?>

<div class="grid">
<?php while($post = $result->fetch_assoc()): ?>
<?php
    $ruta = "../Crear/uploads/".$post['imagen_url']; 
    $ext = strtolower(pathinfo($post['imagen_url'], PATHINFO_EXTENSION));
?>
<div class="grid-item" onclick="openModal(<?= $post['id'] ?>)">
    <div class="media-wrapper">
        <?php if(in_array($ext,['mp4','webm'])): ?>
            <video class="media hover-video" src="<?= $ruta ?>" muted loop></video>
        <?php else: ?>
            <img class="media" src="<?= $ruta ?>" alt="post">
        <?php endif; ?>
        <div class="overlay">
            <div class="overlay-info">
                <span>🌶️ <?= $post['total_likes'] ?></span>
                <span>💬 <?= $post['total_comentarios'] ?></span>
            </div>
        </div>
    </div>
</div>
<?php endwhile; ?>
</div>

<!-- MODAL -->
<div class="explore-modal" id="postModal">
  <span class="close-modal" onclick="closeModal()">&times;</span>

  <div class="explore-modal-content">
    <div class="modal-left" id="modalMedia"></div>

    <div class="modal-right">

      <!-- USUARIO CREADOR -->
      <div class="post-owner" id="modalOwner"></div>

      <!-- COMENTARIOS -->
      <div id="modalComentarios"></div>

      <!-- META -->
      <div class="post-meta">
        <button id="likeBtn" class="like-btn" onclick="toggleLike()">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path fill="currentColor"
              d="M4 18q-.825 0-1.412-.587T2 16V4q0-.825.588-1.412T4 2h16q.825 0 1.413.588T22 4v15.575q0 .675-.612.938T20.3 20.3L18 18zm14.85-2L20 17.125V4H4v12zM4 16V4z"/>
          </svg>
        </button>

        <div id="modalLikes"></div>
        <div id="modalFecha"></div>
      </div>

      <!-- FORM -->
      <form id="commentForm" onsubmit="return submitComment(event)">
        <input type="hidden" id="modalPostId">
        <input type="text" id="commentText" placeholder="Escribe un comentario..." required>
        <button type="submit">Comentar</button>
      </form>

    </div>
  </div>
</div>

<script>
document.querySelectorAll('.hover-video').forEach(v => {
    v.addEventListener('mouseenter', ()=>v.play());
    v.addEventListener('mouseleave', ()=>{v.pause(); v.currentTime=0;});
});

let pollingInterval = null;
let lastCommentId = 0;
let currentPostId = null;
let likedByUser = false;

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
            <span class="comment-text">${c.texto}</span>
        </div>
    </div>`;
}

function openModal(postId){
    fetch('procesamiento/get_post.php?id='+postId)
    .then(res => res.json())
    .then(data => {

        currentPostId = postId;
        likedByUser = data.liked > 0;

        document.getElementById('likeBtn')
            .classList.toggle('liked', likedByUser);

        const mediaDiv = document.getElementById('modalMedia');
        mediaDiv.innerHTML = '';

        const ext = data.imagen_url.split('.').pop().toLowerCase();

        if(['mp4','webm'].includes(ext)){
            const video = document.createElement('video');
            video.src = "../Crear/uploads/"+data.imagen_url;
            video.controls = true;
            video.autoplay = true;
            video.muted = true;
            video.loop = true;
            mediaDiv.appendChild(video);
        } else {
            const img = document.createElement('img');
            img.src = "../Crear/uploads/"+data.imagen_url;
            mediaDiv.appendChild(img);
        }

        document.getElementById('modalOwner').innerHTML = `
            <form action="../Busqueda/usuarioAjeno.php" method="POST" class="comment-avatar-form">
                <input type="hidden" name="id" value="${data.usuario_id}">
                <button type="submit">
                    <img src="${data.foto_perfil}" alt="perfil">
                </button>
            </form>
            <span class="post-user">${data.usuario}</span>
        `;

        document.getElementById('modalLikes').innerHTML =
            '🌶️ ' + data.total_likes + ' picantes';

        document.getElementById('modalFecha').innerHTML =
            '📅 ' + data.fecha_publicacion;

        const comentariosDiv = document.getElementById('modalComentarios');
        comentariosDiv.innerHTML = '';

        data.comentarios.forEach(c=>{
            comentariosDiv.innerHTML += renderComment(c);
        });

        lastCommentId = data.comentarios.length
            ? data.comentarios[data.comentarios.length - 1].id
            : 0;

        document.getElementById('modalPostId').value = postId;
        document.getElementById('postModal').style.display = 'flex';

        if(pollingInterval) clearInterval(pollingInterval);

        pollingInterval = setInterval(() => {
            fetch(`procesamiento/get_new_comments.php?post_id=${postId}&last_id=${lastCommentId}`)
            .then(res => res.json())
            .then(comments => {
                comments.forEach(c => {
                    if(!comentariosDiv.querySelector(`.comment[data-id="${c.id}"]`)){
                        comentariosDiv.innerHTML += renderComment(c);
                        lastCommentId = c.id;
                    }
                });
            });
        }, 3000);
    });
}

function toggleLike(){
    fetch('procesamiento/toggle_like.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'post_id='+currentPostId
    })
    .then(res => res.json())
    .then(data => {
        likedByUser = data.liked;
        document.getElementById('likeBtn')
            .classList.toggle('liked', likedByUser);

        document.getElementById('modalLikes')
            .innerHTML = '🌶️ ' + data.total + ' picantes';
    });
}

function closeModal(){
    document.getElementById('postModal').style.display = 'none';
    if(pollingInterval){
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

document.getElementById('postModal').addEventListener('click', e => {
    if (e.target.id === 'postModal') closeModal();
});

function submitComment(e){
    e.preventDefault();
    const texto = commentText.value.trim();
    if(!texto) return;

    fetch('procesamiento/add_comment.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'post_id='+currentPostId+'&texto='+encodeURIComponent(texto)
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            modalComentarios.innerHTML += renderComment({
                id: data.comment_id,
                usuario: data.usuario,
                usuario_id: data.usuario_id,
                foto_perfil: data.foto_perfil,
                texto
            });
            commentText.value='';
            lastCommentId = data.comment_id;
        }
    });
}
</script>

<?php include __DIR__ . '/../Templates/footer.php'; ?>
</body>
</html>
