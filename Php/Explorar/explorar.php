<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../Sesiones/inicio_sesion.php");
    exit();
}

require '../../BD/conexiones.php';

$usuario_id = intval($_SESSION['id']);

$sql = "SELECT p.id, p.imagen_url, p.fecha_publicacion,
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

<!-- MODAL EXPLORAR -->
<div class="explore-modal" id="postModal">
  <span class="close-modal" onclick="closeModal()">&times;</span>

  <div class="explore-modal-content">
    <div class="modal-left" id="modalMedia"></div>

    <div class="modal-right">
      <div class="info">
        <div id="modalLikes"></div>
        <div id="modalFecha"></div>
      </div>

      <div id="modalComentarios"></div>

      <form id="commentForm" onsubmit="return submitComment(event)">
        <input type="hidden" id="modalPostId">
        <input type="text" id="commentText" placeholder="Escribe un comentario..." required>
        <button type="submit">Comentar</button>
      </form>
    </div>
  </div>
</div>

<script>
// Hover videos
document.querySelectorAll('.hover-video').forEach(v => {
    v.addEventListener('mouseenter', ()=>v.play());
    v.addEventListener('mouseleave', ()=>{v.pause(); v.currentTime=0;});
});

let pollingInterval = null;
let lastCommentId = 0;

// Abrir modal
function openModal(postId) {
    fetch('procesamiento/get_post.php?id='+postId)
    .then(res => res.json())
    .then(data => {
        const modal = document.getElementById('postModal');
        const mediaDiv = document.getElementById('modalMedia');
        mediaDiv.innerHTML = '';

        const ext = data.imagen_url.split('.').pop().toLowerCase();
        if(['mp4','webm'].includes(ext)){
            const video = document.createElement('video');
            video.src = "../Crear/uploads/"+data.imagen_url;
            video.controls = true;
            video.autoplay = true;
            mediaDiv.appendChild(video);
        } else {
            const img = document.createElement('img');
            img.src = "../Crear/uploads/"+data.imagen_url;
            mediaDiv.appendChild(img);
        }

        document.getElementById('modalLikes').innerHTML = '🌶️ ' + data.total_likes + ' picantes';
        document.getElementById('modalFecha').innerHTML = '📅 ' + data.fecha_publicacion;

        const comentariosDiv = document.getElementById('modalComentarios');
        comentariosDiv.innerHTML = '';

        // Insertar comentarios existentes
        data.comentarios.forEach(c=>{
            comentariosDiv.innerHTML += `
                <div class="comment" data-id="${c.id}">
                    <span class="comment-user">${c.usuario}</span>:
                    <span class="comment-text">${c.texto}</span>
                </div>`;
        });

        lastCommentId = data.comentarios.length > 0 ? data.comentarios[data.comentarios.length - 1].id : 0;

        document.getElementById('modalPostId').value = postId;
        modal.style.display = 'flex';

        // Detener cualquier polling previo
        if(pollingInterval) clearInterval(pollingInterval);

        // Iniciar polling de comentarios
        pollingInterval = setInterval(() => {
            fetch(`procesamiento/get_new_comments.php?post_id=${postId}&last_id=${lastCommentId}`)
            .then(res => res.json())
            .then(comments => {
                const comentariosDiv = document.getElementById('modalComentarios');
                comments.forEach(c => {
                    if(!comentariosDiv.querySelector(`.comment[data-id="${c.id}"]`)){
                        comentariosDiv.innerHTML += `
                            <div class="comment" data-id="${c.id}">
                                <span class="comment-user">${c.usuario}</span>:
                                <span class="comment-text">${c.texto}</span>
                            </div>`;
                        lastCommentId = c.id;
                    }
                });
            });
        }, 3000);
    });
}

// Cerrar modal
function closeModal(){
    document.getElementById('postModal').style.display = 'none';
    if(pollingInterval){
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

// Cerrar al hacer click fuera
document.getElementById('postModal').addEventListener('click', e => {
    if (e.target.id === 'postModal') closeModal();
});

// Enviar comentario
function submitComment(e){
    e.preventDefault();

    const texto = document.getElementById('commentText').value.trim();
    const post_id = document.getElementById('modalPostId').value;
    if(!texto) return;

    fetch('procesamiento/add_comment.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'post_id='+post_id+'&texto='+encodeURIComponent(texto)
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            const comentariosDiv = document.getElementById('modalComentarios');
            comentariosDiv.innerHTML += `
                <div class="comment" data-id="${data.comment_id}">
                    <span class="comment-user">${data.usuario}</span>:
                    <span class="comment-text">${texto}</span>
                </div>`;
            document.getElementById('commentText').value='';
            lastCommentId = data.comment_id;
        }
    });
}
</script>

<?php include __DIR__ . '/../Templates/footer.php'; ?>
</body>
</html>
