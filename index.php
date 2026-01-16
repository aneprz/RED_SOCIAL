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
    <link rel="stylesheet" href="Estilos/estilos_index.css">
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
                    <div class="botones">
                        <button class="btnMeGusta"><img src="/Media/meGusta.png" alt="" width="28px" height="28px"></button>
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
                    $fotoRuta = !empty($user['foto_perfil']) && file_exists(__DIR__ . 'Php/Usuarios/fotosDePerfil/' . $user['foto_perfil'])
                        ? 'Php/Usuarios/fotosDePerfil/' . $user['foto_perfil']
                        : 'Media/foto_default.png';
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
<div class="explore-modal" id="postModal">
  <span class="close-modal" onclick="closeModal()">&times;</span>

  <div class="explore-modal-content">
    <div class="modal-left" id="modalMedia"></div>

    <div class="modal-right">
      <div id="modalComentarios"></div>

      <div class="info">
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
            video.muted = true;
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
        document.getElementById('modalLikes').innerText = '🌶️ ' + data.sals + ' picantes';
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
</script>
</body>
</html>