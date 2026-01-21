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
    $seguir_mensaje="No sigues a nadie aún. ¡Empieza a seguir gente para ver sus publicaciones!";
} else {
    $ids_sigo_str = implode(',', $ids_sigo);

    // Publicaciones
    $sql_posts = "
        SELECT p.id, p.imagen_url, p.pie_foto, p.fecha_publicacion,
            u.id AS usuario_id, u.username, u.foto_perfil,
            (
                SELECT COUNT(*) 
                FROM likes l 
                WHERE l.post_id = p.id AND l.usuario_id = :user_id
            ) AS liked
        FROM publicaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.usuario_id IN ($ids_sigo_str)
        ORDER BY p.fecha_publicacion DESC
    ";

    $stmt_posts = $pdo->prepare($sql_posts);
    $stmt_posts->execute(['user_id' => $user_id]);
    $publicaciones = $stmt_posts->fetchAll(PDO::FETCH_ASSOC);

    foreach($publicaciones as &$post){
        $stmt_com = $pdo->prepare("SELECT c.texto, u.username, u.foto_perfil
                                    FROM comentarios c
                                    JOIN usuarios u ON c.usuario_id = u.id
                                    WHERE c.post_id = :post_id
                                    ORDER BY c.id ASC
                                    LIMIT 2");
        $stmt_com->execute(['post_id'=>$post['id']]);
        $post['comentarios'] = $stmt_com->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($post); // romper referencia

    // Sugerencias
    $sql_sug = "
        SELECT u.id, u.username, u.foto_perfil
        FROM usuarios u
        WHERE u.id != :user_id
        AND u.id NOT IN ($ids_sigo_str)
        AND EXISTS (
            SELECT 1 
            FROM seguidores s 
            WHERE s.seguidor_id IN ($ids_sigo_str) AND s.seguido_id = u.id
        )
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
    <link rel="stylesheet" href="Estilos/estilos_index.css">
    <link rel="icon" type="image/png" href="/Media/logo.png">
</head>
<body>
<?php include __DIR__ . '/Php/Templates/navBar.php'; ?>

<div class="content container">
    <div class="main">
        <?php if ($seguir_mensaje): ?>
            <div class="no-sigues">
                <img src="/Media/picantes.png" alt="No sigues a nadie">
                <p>No sigues a nadie aún. ¡Empieza a seguir gente para ver sus publicaciones!</p>
                <div class="botones">
                    <form action="Php/Explorar/explorar.php">
                        <button><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="m16.2 7.8-1.8 5.4a2 2 0 0 1-1.3 1.3L7.8 16.2l1.8-5.4a2 2 0 0 1 1.3-1.3z"/><circle cx="12" cy="12" r="10"/></g></svg></button>
                    </form>
                    <form action="Php/Busqueda/busqueda.php">
                        <button><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="m21 21-4.3-4.3"/><circle cx="11" cy="11" r="8"/></g></svg></button>
                    </form>
                </div>
            </div>
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
                        <video class="hover-video" src="<?= htmlspecialchars($archivoRuta) ?>" loop style="width:50%; border-radius:8px;"></video>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($archivoRuta) ?>" alt="Post" style="width:100%; border-radius:8px;">
                    <?php endif; ?>
                    <div class="botonesPublicacion">
                        <button 
                            class="btnMeGusta"
                            data-post-id="<?= $post['id'] ?>"
                            data-liked="<?= $post['liked'] ?>"
                        >
                            <img 
                                class="likeImg"
                                src="<?= $post['liked'] ? '/Media/meGustaDado.png' : '/Media/meGusta.png' ?>"
                                width="28"
                            >
                        </button>
                        <button type="button" class="btnVerMas" onclick="openModal(<?= $post['id'] ?>)"><svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"><path fill="currentColor" d="M4 18q-.825 0-1.412-.587T2 16V4q0-.825.588-1.412T4 2h16q.825 0 1.413.588T22 4v15.575q0 .675-.612.938T20.3 20.3L18 18zm14.85-2L20 17.125V4H4v12zM4 16V4z"/></svg></button>   
                    </div>
                      
                    <?php if (!empty($post['pie_foto'])): ?>
                        <p class="pieFoto"><?= nl2br(htmlspecialchars($post['pie_foto'])) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($post['comentarios'])): ?>
                        <div class="preview-comentarios">
                            <?php foreach($post['comentarios'] as $c): ?>
                                <div class="preview-comment">
                                    <strong><?= htmlspecialchars($c['username']) ?></strong>: <?= htmlspecialchars($c['texto']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="sugerencias">
        <h2>Sugerencias para ti</h2>
        <?php if (!empty($sugerencias)): ?>
            <?php foreach($sugerencias as $user): ?>
                <?php
                    $fotoNombre = $user['foto_perfil'] ?? '';
                    $fotoUrl = $fotoNombre !== '' ? '/Php/Usuarios/fotosDePerfil/' . $fotoNombre : '/Media/foto_default.png';
                ?>
                <div class="suggestion" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                    <div style="display:flex; align-items:center;">
                        <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="Perfil"
                            style="width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right:10px;"
                            onerror="this.onerror=null; this.src='/Media/foto_default.png';">

                        <!-- Link al perfil -->
                        <form action="Php/Busqueda/usuarioAjeno.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit"
                                    style="border:none; background:none; padding:0; cursor:pointer; font-weight:bold; color:#333;">
                                <?= htmlspecialchars($user['username']) ?>
                            </button>
                        </form>
                    </div>

                    <!-- Botón AJAX seguir -->
                    <button type="button" class="btnSeguir" data-id="<?= $user['id'] ?>"
                            style="padding:5px 10px; border-radius:5px; border:1px solid #ccc; background:#f0f0f0; cursor:pointer;">
                        Seguir
                    </button>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay sugerencias por ahora.</p>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL PUBLICACIÓN -->
<div class="explore-modal" id="postModal">
  <span class="close-modal" onclick="closeModal()">&times;</span>

  <div class="explore-modal-content">
    <div class="modal-left" id="modalMedia"></div>

    <div class="modal-right">
      <div id="modalComentarios"></div>

    <div class="info">
        <!-- BOTÓN LIKE DEL MODAL -->
        <button id="modalLikeBtn" class="btnMeGusta" data-post-id="">
            <img id="modalLikeImg" src="/Media/meGusta.png" width="28">
        </button>

        <!-- CONTADOR DE PICANTES -->
        <div id="modalLikes"></div>
        <div id="modalFecha"></div>
    </div>

    <form id="commentForm" onsubmit="return submitComment(event)">
        <input type="hidden" id="modalPostId">
        <input maxlength="100" type="text" id="commentText" placeholder="Escribe un comentario..." required>
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

function openModal(postId) {
    fetch('Php/Index/get_post.php?id=' + postId)
    .then(res => res.json())
    .then(data => {
        if(data.error){
            alert(data.error);
            return;
        }

        const modal = document.getElementById('postModal');
        const mediaDiv = document.getElementById('modalMedia');
        mediaDiv.innerHTML = ''; // limpiar contenido previo

        // --- IMAGEN O VIDEO ---
        const ext = data.imagen_url.split('.').pop().toLowerCase();
        const mediaPath = "/Php/Crear/uploads/" + data.imagen_url;

        if(['mp4','webm'].includes(ext)){
            const video = document.createElement('video');
            video.src = mediaPath;
            video.controls = true;
            video.autoplay = true;
            video.style.maxWidth = '100%';
            video.style.maxHeight = '100%';
            video.style.objectFit = 'contain';

            // reproducir automáticamente
            video.addEventListener('canplay', () => video.play());

            // reiniciar al terminar
            video.addEventListener('ended', () => video.play());

            mediaDiv.appendChild(video);
        } else {
            const img = document.createElement('img');
            img.src = mediaPath;
            img.style.maxWidth = '100%';
            img.style.maxHeight = '100%';
            img.style.objectFit = 'contain';
            mediaDiv.appendChild(img);
        }

        // --- INFO DEL POST ---
        document.getElementById('modalLikes').innerText = '🌶️ ' + data.total_likes + ' picantes';
        document.getElementById('modalFecha').innerText = '📅 ' + data.fecha_publicacion;

        // --- COMENTARIOS EXISTENTES ---
        const comentariosDiv = document.getElementById('modalComentarios');
        comentariosDiv.innerHTML = '';

        data.comentarios.forEach(c => {
            const div = document.createElement('div');
            div.classList.add('comment');
            div.dataset.id = c.id;

            // Estructura con foto de perfil
            div.innerHTML = `
            <div class="comentarioUsuario">
                <img class="fotoPerfilComentarios" src="${c.foto_perfil}" alt="${c.usuario}'s avatar">
                <div>
                    <span class="comment-user">${c.usuario}</span>
                    <br>
                    <span class="comment-text">${c.texto}</span>
                </div>
            </div>
            `;
            comentariosDiv.appendChild(div);
        });

        // último comentario
        lastCommentId = data.comentarios.length ? data.comentarios[data.comentarios.length - 1].id : 0;

        // guardar postId en hidden
        document.getElementById('modalPostId').value = postId;

        // --- CONFIGURAR BOTÓN LIKE DEL MODAL ---
        const modalLikeBtn = document.getElementById('modalLikeBtn');
        const modalLikeImg = document.getElementById('modalLikeImg');

        modalLikeBtn.dataset.postId = postId;
        modalLikeImg.src = data.liked ? '/Media/meGustaDado.png' : '/Media/meGusta.png';

        // mostrar modal
        modal.style.display = 'flex';

        // hacer scroll al último comentario
        comentariosDiv.scrollTop = comentariosDiv.scrollHeight;

        // --- POLLING DE NUEVOS COMENTARIOS ---
        if (pollingInterval) clearInterval(pollingInterval);

        pollingInterval = setInterval(() => {
            fetch(`Php/Explorar/Procesamiento/get_new_comments.php?post_id=${postId}&last_id=${lastCommentId}`)
            .then(res => res.json())
            .then(comments => {
                comments.forEach(c => {
                    if (!comentariosDiv.querySelector(`.comment[data-id="${c.id}"]`)) {
                        const div = document.createElement('div');
                        div.classList.add('comment');
                        div.dataset.id = c.id;
                        div.innerHTML = `
                            <img src="${c.foto_perfil}" alt="${c.usuario}'s avatar">
                            <div>
                                <span class="comment-user">${c.usuario}</span>
                                <span class="comment-text">${c.texto}</span>
                            </div>
                        `;
                        comentariosDiv.appendChild(div);
                        lastCommentId = c.id;
                        comentariosDiv.scrollTop = comentariosDiv.scrollHeight;
                    }
                });
            });
        }, 3000);

    })  
    .catch(err => console.error('Error al cargar el post:', err));
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

    const texto = document.getElementById('commentText').value.trim();
    const post_id = document.getElementById('modalPostId').value;
    if(!texto) return;

    fetch('Php/Index/add_comment.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'post_id='+post_id+'&texto='+encodeURIComponent(texto)
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            const comentariosDiv = document.getElementById('modalComentarios');
            
            // Crear nuevo div para comentario con foto de perfil
            const div = document.createElement('div');
            div.classList.add('comment');
            div.dataset.id = data.comment_id;
            div.innerHTML = `
                <div class="comentarioUsuario">
                    <img class="fotoPerfilComentarios" src="${data.foto_perfil}" alt="${data.usuario}'s avatar">
                    <div>
                        <span class="comment-user">${data.usuario}</span>
                        <p class="comment-text" style="white-space: pre-wrap; overflow-wrap: break-word;">${texto}</p>
                    </div>
                </div>
            `;
            
            comentariosDiv.appendChild(div);
            
            // Limpiar input
            document.getElementById('commentText').value = '';

            // Scroll hacia abajo para ver el nuevo comentario
            comentariosDiv.scrollTop = comentariosDiv.scrollHeight;

            // Actualizar último ID
            lastCommentId = data.comment_id;
        } else {
            alert(data.error);
        }
    })
    .catch(err => console.error(err));
}
/* =========================
   LIKE FEED Y MODAL
========================= */
document.addEventListener('click', e => {

    const btn = e.target.closest('.btnMeGusta');
    if (!btn) return;

    const postId = btn.dataset.postId;
    const img = btn.querySelector('.likeImg');

    fetch('Php/Index/toggle_like.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'post_id=' + postId
    })
    .then(res => res.json())
    .then(data => {
        // cambiar icono
        img.src = data.liked ? '/Media/meGustaDado.png' : '/Media/meGusta.png';

        // actualizar contador si existe en feed
        const contador = btn.parentElement.querySelector('.likeCount');
        if (contador) contador.textContent = '🌶️ ' + data.total;

        // sincronizar modal si está abierto y es el mismo post
        const modalPostId = document.getElementById('modalPostId').value;
        if (modalPostId == postId) {
            document.getElementById('modalLikes').textContent = '🌶️ ' + data.total;

            const modalImg = document.getElementById('modalLikeImg');
            if(modalImg) modalImg.src = data.liked ? '/Media/meGustaDado.png' : '/Media/meGusta.png';
        }
    })
    .catch(err => console.error(err));
});

/* BOTÓN LIKE DEL MODAL (si lo quieres añadir) */
const modalBtn = document.getElementById('modalLikeBtn');
if(modalBtn){
    modalBtn.onclick = () => {
        const postId = document.getElementById('modalPostId').value;

        fetch('Php/Index/toggle_like.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'post_id=' + postId
        })
        .then(res => res.json())
        .then(data => {
            // icono modal
            document.getElementById('modalLikeImg').src = data.liked ? '/Media/meGustaDado.png' : '/Media/meGusta.png';
            document.getElementById('modalLikes').textContent = '🌶️ ' + data.total;

            // sincronizar feed
            const feedBtn = document.querySelector(`.btnMeGusta[data-post-id="${postId}"]`);
            if(feedBtn){
                feedBtn.querySelector('.likeImg').src = data.liked ? '/Media/meGustaDado.png' : '/Media/meGusta.png';
                const contador = feedBtn.parentElement.querySelector('.likeCount');
                if(contador) contador.textContent = '🌶️ ' + data.total;
            }
        })
        .catch(err => console.error(err));
    };
}

document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btnSeguir');
    if (!btn) return;

    const userId = btn.dataset.id;

    fetch('Php/Index/seguir_usuario.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'seguir_id=' + userId
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            btn.textContent = 'Siguiendo';
            btn.disabled = true;
            btn.style.background = '#d0ffd0';
            btn.style.border = '1px solid #8f8';
            btn.style.cursor = 'default';
        } else {
            alert(data.error);
        }
    })
    .catch(err => console.error(err));
});

</script>
</body>
</html>