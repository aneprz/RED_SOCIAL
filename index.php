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

//Si no sigo a nadie
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
    unset($post);

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
                            data-liked="<?= $post['liked'] ?>">
                            <img class="likeImg"
                                src="<?= $post['liked'] ? '/Media/meGustaDado.png' : '/Media/meGusta.png' ?>"
                                width="28">
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

                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
                    $stmt->execute([$user_id, $user['id']]);
                    $yaSigo = $stmt->fetchColumn();

                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_seguimiento WHERE solicitante_id = ? AND receptor_id = ? AND estado = 'pendiente'");
                    $stmt->execute([$user_id, $user['id']]);
                    $yaSolicite = $stmt->fetchColumn();

                    $stmt = $pdo->prepare("SELECT privacidad FROM usuarios WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    $esPrivada = $stmt->fetchColumn();

                    // Determinar texto del botón
                    if ($yaSigo > 0) {
                        $estadoBtn = 'Siguiendo';
                    } elseif ($yaSolicite > 0 && $esPrivada == 1) {
                        $estadoBtn = 'Solicitado';
                    } else {
                        $estadoBtn = 'Seguir';
                    }

                ?>
                <div class="suggestion" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                    <div style="display:flex; align-items:center;">
                        <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="Perfil"
                            style="width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right:10px;"
                            onerror="this.onerror=null; this.src='/Media/foto_default.png';">

                        <form action="Php/Busqueda/usuarioAjeno.php" method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" class="username-link" title="<?= htmlspecialchars($user['username']) ?>"
                                    style="border:none; background:none; padding:0; cursor:pointer; font-weight:bold; color:#333;">
                                <?= htmlspecialchars($user['username']) ?>
                            </button>
                        </form>
                    </div>

                    <button type="button" class="btnSeguir" data-id="<?= $user['id'] ?>" data-estado="<?= $estadoBtn ?>"
                            style="padding:5px 10px; border-radius:8px; font-weight:bold; cursor:pointer;
                                border:none; transition:0.2s; font-size: 12px;">
                        <?= $estadoBtn ?>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay sugerencias por ahora.</p>
        <?php endif; ?>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.btnSeguir').forEach(btn => {
            btn.addEventListener('click', () => {
                const userId = btn.getAttribute('data-id');

                btn.disabled = true; //Botón bloqueado

                fetch('Php/Usuarios/seguir_usuario.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/x-www-form-urlencoded'},
                    body: 'id_usuario=' + encodeURIComponent(userId)
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false; // Desbloquear boton siempre al recibir respuesta
                    
                    if(data.status === 'success'){
                        // Pasamos a minúsculas para que no falle
                        const nuevoEstado = data.estado.toLowerCase(); 
                        
                        // Aplicar cambios visuales
                        if(nuevoEstado === 'siguiendo'){
                            btn.textContent = 'Siguiendo';
                            btn.setAttribute('data-estado', 'Siguiendo');
                        } else if(nuevoEstado === 'solicitado'){
                            btn.textContent = 'Solicitado';
                            btn.setAttribute('data-estado', 'Solicitado');
                        } else {
                            btn.textContent = 'Seguir';
                            btn.setAttribute('data-estado', 'Seguir');
                        }
                    } else {
                        // Esto nos dice exactamente el mensaje de error del PHP
                        console.error("Respuesta del servidor:", data);
                        alert('Error del servidor: ' + (data.message || data.error || 'Desconocido'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    btn.disabled = false;
                    alert('Error de red');
                });
            });
        });
    });
    </script>
</div>

<div class="explore-modal" id="postModal">
  <span class="close-modal" onclick="closeModal()">&times;</span>

  <div class="explore-modal-content">
    <div class="modal-left" id="modalMedia"></div>

    <div class="modal-right">
        <div id="modalComentarios"></div>

        <div class="info">
            <button id="modalLikeBtn" class="btnMeGusta" data-post-id="">
                <img id="modalLikeImg" src="/Media/meGusta.png" width="28">
            </button>

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

    // Función auxiliar para renderizar comentarios (y el pie de foto)
    function renderComment(c) {
        // En los comentarios viene 'usuario', en el post (pie de foto) viene 'username'
        const nombreUsuario = c.usuario || c.username; 
        
        return `
            <div class="comment" data-id="${c.id}">
                <div class="comentarioUsuario">
                    <img class="fotoPerfilComentarios" src="${c.foto_perfil}" alt="avatar">
                    <div>
                        <span class="comment-user">${nombreUsuario}</span>
                        <p class="comment-text" style="white-space: pre-wrap; overflow-wrap: break-word;">${c.texto}</p>
                    </div>
                </div>
            </div>
        `;
    }

    function openModal(postId) {
        fetch('Php/Index/get_post.php?id=' + postId)
        .then(res => res.json())
        .then(data => {
            if(data.error){ alert(data.error); return; }

            const modal = document.getElementById('postModal');
            const mediaDiv = document.getElementById('modalMedia');
            const comentariosDiv = document.getElementById('modalComentarios');
            
            mediaDiv.innerHTML = ''; 
            comentariosDiv.innerHTML = '';

            // --- NUEVA CABECERA DEL USUARIO ---
            const userHeader = document.createElement('div');
            userHeader.className = 'modal-user-header';
            
            // Validar foto de perfil
            const fotoUrl = data.foto_perfil ? data.foto_perfil : '/Media/foto_default.png';

            userHeader.innerHTML = 
                `<form action="Php/Busqueda/usuarioAjeno.php" method="POST" class="modal-user-form">
                    <input type="hidden" name="id" value="${data.usuario_id}">
                    <button type="submit" class="modal-user-button">
                        <img src="${fotoUrl}" alt="Perfil" class="modal-profile-img">
                        <span class="modal-username">${data.username}</span>
                    </button>
                </form>`;
            comentariosDiv.appendChild(userHeader);
            // --- FIN CABECERA ---

            // Renderizado de media (Imagen o Video)
            const ext = data.imagen_url.split('.').pop().toLowerCase();
            const mediaPath = "/Php/Crear/uploads/" + data.imagen_url;

            if(['mp4','webm'].includes(ext)){
                const video = document.createElement('video');
                video.src = mediaPath;
                video.controls = true; video.autoplay = true;
                video.style.maxWidth = '100%'; video.style.maxHeight = '100%';
                mediaDiv.appendChild(video);
            } else {
                const img = document.createElement('img');
                img.src = mediaPath;
                img.style.maxWidth = '100%'; img.style.maxHeight = '100%';
                mediaDiv.appendChild(img);
            }

            // ===============================================
            // 1. INYECTAR PIE DE FOTO COMO PRIMER COMENTARIO
            // ===============================================
            if (data.pie_foto && data.pie_foto.trim() !== "") {
                const pieDeFotoObj = {
                    id: 'caption-' + data.id, // ID temporal
                    username: data.username,  // Usamos el nombre del autor
                    foto_perfil: data.foto_perfil,
                    texto: data.pie_foto
                };
                comentariosDiv.innerHTML += renderComment(pieDeFotoObj);
            }

            // 2. Cargar comentarios existentes
            data.comentarios.forEach(c => {
                comentariosDiv.innerHTML += renderComment(c);
            });

            // Configuración restante del modal...
            document.getElementById('modalLikes').innerText = '🌶️ ' + data.total_likes + ' picantes';
            document.getElementById('modalFecha').innerText = '📅 ' + data.fecha_publicacion;
            document.getElementById('modalPostId').value = postId;
            
            // Icono Like
            const modalImg = document.getElementById('modalLikeImg');
            if(modalImg) modalImg.src = data.liked ? '/Media/meGustaDado.png' : '/Media/meGusta.png';

            modal.style.display = 'flex';
            comentariosDiv.scrollTop = 0; 
        });
    }

    function closeModal(){
        const modal = document.getElementById('postModal');
        modal.style.display='none';
        document.getElementById('modalMedia').innerHTML = ''; // Detener videos
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
                
                // Usamos la función renderComment para mantener el estilo
                comentariosDiv.innerHTML += renderComment({
                    id: data.comment_id,
                    foto_perfil: data.foto_perfil,
                    usuario: data.usuario,
                    texto: texto
                });
                
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

    /* LIKE FEED Y MODAL*/
    document.addEventListener('click', e => {

        const btn = e.target.closest('.btnMeGusta');
        if (!btn) return;

        // Si es el botón del modal, tomamos el ID del input hidden, si es del feed, del data-attribute
        let postId = btn.dataset.postId;
        if (!postId && btn.id === 'modalLikeBtn') {
            postId = document.getElementById('modalPostId').value;
        }

        if(!postId) return;

        const img = btn.querySelector('img') || document.getElementById('modalLikeImg');

        fetch('Php/Index/toggle_like.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'post_id=' + postId
        })
        .then(res => res.json())
        .then(data => {
            // cambiar icono
            if(img) img.src = data.liked ? '/Media/meGustaDado.png' : '/Media/meGusta.png';

            // actualizar contador si existe en feed
            const feedBtn = document.querySelector(`.btnMeGusta[data-post-id="${postId}"]`);
            if (feedBtn) {
                const contador = feedBtn.parentElement.querySelector('.likeCount'); // Si tienes contador en feed
                const feedImg = feedBtn.querySelector('.likeImg');
                if(feedImg) feedImg.src = data.liked ? '/Media/meGustaDado.png' : '/Media/meGusta.png';
            }

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
</script>
</body>
</html>