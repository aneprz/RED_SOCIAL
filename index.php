<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: Php/Sesiones/inicio_sesion.php");
    exit();
}

include __DIR__ . '/BD/conexiones.php'; // PDO

$user_id = $_SESSION['id'];

// IDs de usuarios que sigo
$stmt = $pdo->prepare("SELECT seguido_id FROM seguidores WHERE seguidor_id = :id");
$stmt->execute(['id' => $user_id]);
$ids_sigo = $stmt->fetchAll(PDO::FETCH_COLUMN);

$seguir_mensaje = null;
$sugerencias = [];
$publicaciones = [];

if (empty($ids_sigo)) {
    $seguir_mensaje = "No sigues a nadie aún. ¡Empieza a seguir gente para ver sus publicaciones!";
} else {
    $ids_sigo_str = implode(',', $ids_sigo);

    // Publicaciones
    $sql_posts = "
        SELECT p.id, p.imagen_url, p.pie_foto, p.fecha_publicacion,
               u.id AS usuario_id, u.username, u.foto_perfil
        FROM publicaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.usuario_id IN ($ids_sigo_str)
        ORDER BY p.fecha_publicacion DESC
    ";
    $stmt_posts = $pdo->query($sql_posts);
    $publicaciones = $stmt_posts->fetchAll(PDO::FETCH_ASSOC);

    // Sugerencias
    $sql_sug = "
        SELECT DISTINCT u.id, u.username, u.foto_perfil
        FROM seguidores s
        JOIN usuarios u ON s.seguido_id = u.id
        WHERE s.seguidor_id IN ($ids_sigo_str)
          AND u.id != :user_id
          AND u.id NOT IN ($ids_sigo_str)
        ORDER BY RAND()
        LIMIT 5
    ";
    $stmt_sug = $pdo->prepare($sql_sug);
    $stmt_sug->execute(['user_id'=>$user_id]);
    $sugerencias = $stmt_sug->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inicio</title>
    <link rel="stylesheet" href="/Estilos/estilos.css">
    <link rel="icon" type="image/png" href="/Media/logo.png">
</head>
<body>
<?php include __DIR__ . '/Php/Templates/navBar.php'; ?>

<div class="content container">
    <div class="main">
        <?php if ($seguir_mensaje): ?>
            <p><?= htmlspecialchars($seguir_mensaje) ?></p>
        <?php else: ?>
            <?php foreach($publicaciones as $post): ?>
                <?php
                    $ext = strtolower(pathinfo($post['imagen_url'], PATHINFO_EXTENSION));
                    $fotoPerfil = !empty($post['foto_perfil']) ? $post['foto_perfil'] : '/Media/foto_default.png';
                    $archivoRuta = !empty($post['imagen_url']) && file_exists(__DIR__ . '/Php/Crear/uploads/' . $post['imagen_url'])
                        ? '/Php/Crear/uploads/' . $post['imagen_url']
                        : '/Media/foto_default.png';
                ?>
                <div class="post">
                    <div style="display:flex; align-items:center; margin-bottom:5px;">
                        <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Perfil"
                             style="width:20px; height:20px; border-radius:50%; object-fit:cover; margin-right:10px;">
                        <strong><?= htmlspecialchars($post['username']) ?></strong>
                    </div>
                    <em class="fecha"><?= htmlspecialchars($post['fecha_publicacion']) ?></em>

                    <?php if (in_array($ext, ['mp4','webm'])): ?>
                        <video class="hover-video" src="<?= htmlspecialchars($archivoRuta) ?>" muted loop style="width:50%; border-radius:8px;"></video>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($archivoRuta) ?>" alt="Post" style="width:100%; border-radius:8px;">
                    <?php endif; ?>

                    <?php if (!empty($post['pie_foto'])): ?>
                        <p class="pieFoto"><?= nl2br(htmlspecialchars($post['pie_foto'])) ?></p>
                    <?php endif; ?>

                    <button type="button" class="btnVerMas" onclick="openModal(<?= $post['id'] ?>)"
                        style="margin-top:10px; padding:5px 10px; border:none; background:#FF6B6B; color:white; border-radius:5px; cursor:pointer;">
                        Ver más
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="sugerencias">
        <h2>Sugerencias para ti</h2>
        <?php if (!empty($sugerencias)): ?>
            <?php foreach($sugerencias as $user): ?>
                <?php
                    $fotoRuta = !empty($user['foto_perfil']) && file_exists(__DIR__ . '/Php/Usuarios/fotosDePerfil/' . $user['foto_perfil'])
                        ? '/Php/Usuarios/fotosDePerfil/' . $user['foto_perfil']
                        : '/Media/foto_default.png';
                ?>
                <div class="suggestion" style="display:flex; align-items:center; margin-bottom:10px;">
                    <img src="<?= htmlspecialchars($fotoRuta) ?>" alt="Perfil"
                         style="width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right:10px;">
                    <form action="Php/Busqueda/usuarioAjeno.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        <button type="submit" style="border:none; background:none; padding:0; cursor:pointer; font-weight:bold; color:#333;">
                            <?= htmlspecialchars($user['username']) ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay sugerencias por ahora.</p>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL PUBLICACIÓN -->
<div class="post-modal" id="postModal" style="display:none;">
    <span class="close-modal" onclick="closeModal()">&times;</span>
    <div class="modal-content">
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

<?php include __DIR__ . '/Php/Templates/footer.php'; ?>

<script>
const videos = document.querySelectorAll('.hover-video');
videos.forEach(video=>{
    video.addEventListener('mouseenter',()=>video.play());
    video.addEventListener('mouseleave',()=>{video.pause(); video.currentTime=0;});
});

let pollingInterval = null;
let lastCommentId = 0;

function openModal(postId){
    fetch('Php/Index/get_post.php?id='+postId)
    .then(res => res.json())
    .then(data => {
        if(data.error){
            alert(data.error);
            return;
        }

        const modal = document.getElementById('postModal');
        const mediaDiv = document.getElementById('modalMedia');
        mediaDiv.innerHTML = '';

        const ext = data.imagen_url.split('.').pop().toLowerCase();
        const mediaPath = "/Php/Crear/uploads/" + data.imagen_url;

        if(['mp4','webm'].includes(ext)){
            const video = document.createElement('video');
            video.src = mediaPath;
            video.controls = true;
            video.autoplay = true;
            video.muted = true;
            video.style.maxWidth = '100%';
            video.style.maxHeight = '100%';
            mediaDiv.appendChild(video);
        } else {
            const img = document.createElement('img');
            img.src = mediaPath;
            img.style.width = '100%';
            img.style.borderRadius = '8px';
            mediaDiv.appendChild(img);
        }

        document.getElementById('modalLikes').innerText = '🌶️ ' + data.sals + ' picantes';
        document.getElementById('modalFecha').innerText = '📅 ' + data.fecha_publicacion;

        const comentariosDiv = document.getElementById('modalComentarios');
        comentariosDiv.innerHTML = '';
        data.comentarios.forEach(c=>{
            comentariosDiv.innerHTML += `<div class="comment" data-id="${c.id}"><strong>${c.usuario}</strong>: ${c.texto}</div>`;
        });

        lastCommentId = data.comentarios.length ? data.comentarios[data.comentarios.length-1].id : 0;
        document.getElementById('modalPostId').value = postId;

        modal.style.display = 'flex';

        if(pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(()=>{
            fetch(`Php/Explorar/Procesamiento/get_new_comments.php?post_id=${postId}&last_id=${lastCommentId}`)
            .then(res => res.json())
            .then(comments => {
                comments.forEach(c => {
                    if(!comentariosDiv.querySelector(`.comment[data-id="${c.id}"]`)){
                        comentariosDiv.innerHTML += `<div class="comment" data-id="${c.id}"><strong>${c.usuario}</strong>: ${c.texto}</div>`;
                        lastCommentId = c.id;
                    }
                });
            });
        }, 3000);
    })
    .catch(err => console.error(err));
}

function closeModal(){
    const modal = document.getElementById('postModal');
    modal.style.display='none';
    if(pollingInterval){ clearInterval(pollingInterval); pollingInterval=null; }
}

document.getElementById('postModal').addEventListener('click', e=>{
    if(e.target.id==='postModal') closeModal();
});

function submitComment(e){
    e.preventDefault();
    const texto=document.getElementById('commentText').value.trim();
    const post_id=document.getElementById('modalPostId').value;
    if(!texto) return;

    fetch('Php/Crear/procesamiento/add_comment.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'post_id='+post_id+'&texto='+encodeURIComponent(texto)
    }).then(res=>res.json()).then(data=>{
        if(data.success){
            const comentariosDiv = document.getElementById('modalComentarios');
            comentariosDiv.innerHTML+=`<div class="comment" data-id="${data.comment_id}"><span class="comment-user">${data.usuario}</span>: <span class="comment-text">${texto}</span></div>`;
            document.getElementById('commentText').value='';
            lastCommentId=data.comment_id;
        }
    });
}
</script>
</body>
</html>
