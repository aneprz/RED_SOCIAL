<?php
require "../../BD/conexiones.php";
session_start();

if (!isset($_GET['chat_id'])) {
    die("No se especificó chat_id");
}

$chat_id = intval($_GET['chat_id']);
$idUsu = $_SESSION['id'];

// 1️⃣ Información del chat
$sql = $pdo->prepare("SELECT c.id, c.es_grupo, c.nombre_grupo FROM chats c WHERE c.id = :chat_id");
$sql->execute(['chat_id' => $chat_id]);
$chat = $sql->fetch(PDO::FETCH_ASSOC);

if (!$chat) die("Chat no encontrado");

// 2️⃣ Participantes y mensajes
$sql = $pdo->prepare("
    SELECT m.id, m.usuario_id, u.username, u.foto_perfil, m.texto, m.fecha, m.leido
    FROM mensajes m
    JOIN usuarios u ON u.id = m.usuario_id
    WHERE m.chat_id = :chat_id
    ORDER BY m.fecha ASC
");
$sql->execute(['chat_id' => $chat_id]);
$mensajes = $sql->fetchAll(PDO::FETCH_ASSOC);

// Marcar mensajes como leídos
$pdo->prepare("
    UPDATE mensajes SET leido = 1
    WHERE chat_id = :chat_id AND usuario_id != :yo
")->execute(['chat_id' => $chat_id, 'yo' => $idUsu]);

// 1️⃣ Obtener participantes del chat (grupo o privado)
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.foto_perfil
    FROM usuarios_chat uc
    JOIN usuarios u ON u.id = uc.usuario_id
    WHERE uc.chat_id = :chat_id
");
$stmt->execute(['chat_id' => $chat_id]);
$participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Todos los usuarios que sigues (para búsqueda)
$todosUsuarios = $pdo->query("SELECT id, username FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <link rel="stylesheet" href="../../../Estilos/estilos_chat.css">
    <link rel="icon" type="image/png" href="/Media/logo.png">
</head>
<body>
<?php include __DIR__ . '../../../Php/Templates/navBar.php';?>

<main>
    <?php
        // Determinar encabezado
        if ($chat['es_grupo']) {
            $nombreChat = $chat['nombre_grupo'] ?: "Grupo sin nombre";
            $fotoPerfil = '../../../Media/foto_grupo_default.png';
            $otroUsuario = null; // no hay otro usuario, es grupo
        } else {
            // Chat privado → buscar al otro usuario
            $otroUsuario = null;
            foreach ($participantes as $p) {
                if ($p['id'] != $idUsu) {
                    $otroUsuario = $p;
                    break;
                }
            }
            $nombreChat = $otroUsuario['username'] ?? "Usuario";
            $fotoPerfil = !empty($otroUsuario['foto_perfil'])
                ? $otroUsuario['foto_perfil']
                : '../../../Media/foto_default.png';
        }
    ?>
    <div class="encabezado">
        <a class="volver" href="chats.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/>
            </svg>
        </a>
        <img src="<?= htmlspecialchars($fotoPerfil) ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
        <div class="tituloIntegrantes">
            <?php if ($otroUsuario): ?>
                <!--lleva al perfil del otro usuario -->
                <form action="../Busqueda/usuarioAjeno.php" method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $otroUsuario['id'] ?>">
                    <button type="submit" style="border:none; background:none; padding:0; cursor:pointer;">
                        <h2><?= htmlspecialchars($nombreChat) ?></h2>
                    </button>
                </form>
            <?php else: ?>
                <h2><?= htmlspecialchars($nombreChat) ?></h2>
            <?php endif; ?>

            <?php if ($chat['es_grupo'] && $participantes): ?>
                <i style="color: #c9c9c9;">
                    <?php
                    $nombres = [];
                    foreach ($participantes as $p) {
                        if ($p['id'] != $idUsu) {
                            $nombres[] = '<form action="../Busqueda/usuarioAjeno.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="' . $p['id'] . '">
                                            <button type="submit" style="border:none; background:none; padding:0; cursor:pointer; color:inherit;">
                                                ' . htmlspecialchars($p['username']) . '
                                            </button>
                                        </form>';
                        }
                    }
                    echo implode(", ", $nombres); // Separados por comas en la misma línea
                    ?>
                </i>
            <?php endif; ?>
        </div>

        <?php if ($chat['es_grupo']): ?>
            <button id="btnConfigGrupo" class="btnConfig">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M10.825 22q-.675 0-1.162-.45t-.588-1.1L8.85 18.8q-.325-.125-.612-.3t-.563-.375l-1.55.65q-.625.275-1.25.05t-.975-.8l-1.175-2.05q-.35-.575-.2-1.225t.675-1.075l1.325-1Q4.5 12.5 4.5 12.337v-.675q0-.162.025-.337l-1.325-1Q2.675 9.9 2.525 9.25t.2-1.225L3.9 5.975q.35-.575.975-.8t1.25.05l1.55.65q.275-.2.575-.375t.6-.3l.225-1.65q.1-.65.588-1.1T10.825 2h2.35q.675 0 1.163.45t.587 1.1l.225 1.65q.325.125.613.3t.562.375l1.55-.65q.625-.275 1.25-.05t.975.8l1.175 2.05q.35.575.2 1.225t-.675 1.075l-1.325 1q.025.175.025.338v.674q0 .163-.05.338l1.325 1q.525.425.675 1.075t-.2 1.225l-1.2 2.05q-.35.575-.975.8t-1.25-.05l-1.5-.65q-.275.2-.575.375t-.6.3l-.225 1.65q-.1.65-.587 1.1t-1.163.45zm1.225-6.5q1.45 0 2.475-1.025T15.55 12t-1.025-2.475T12.05 8.5q-1.475 0-2.488 1.025T8.55 12t1.013 2.475T12.05 15.5"/>
                </svg>
            </button>
        <?php endif; ?>
    </div>


    <div id="chat-mensajes">
        <?php foreach ($mensajes as $m): 
            $esTuyo = $m['usuario_id'] == $idUsu;
        ?>
            <div class="mensaje <?= $esTuyo ? 'tuyo' : 'otro' ?>">
                <?php if (!$esTuyo && $chat['es_grupo']): ?>
                    <div class="infoUsuario">
                        <img src="<?= htmlspecialchars($m['foto_perfil'] ?: '../../../Media/foto_default.png') ?>" class="fotoUsuario">
                        <span><?= htmlspecialchars($m['username']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="textoMensaje">
                    <?= htmlspecialchars($m['texto']) ?><br>
                    <small>
                        <?= $m['fecha'] ?>
                        <?php if ($esTuyo): ?>
                            <span class="estadoLeido">
                                <?= $m['leido'] ? '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path fill="currentColor" d="m10.6 13.8l-2.15-2.15q-.275-.275-.7-.275t-.7.275t-.275.7t.275.7L9.9 15.9q.3.3.7.3t.7-.3l5.65-5.65q.275-.275.275-.7t-.275-.7t-.7-.275t-.7.275zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path fill="currentColor" d="M12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22"/></svg>' ?>
                            </span>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Formulario para enviar mensajes -->
    <form class="formularioMensajes" action="procesamientos/guardarMensajes.php" method="post">
        <input type="hidden" name="chat_id" value="<?= $chat_id ?>">
        <input type="hidden" name="usuario_id" value="<?= $idUsu ?>">
        <input class="barraMensajes" type="text" name="mensaje" placeholder="Escribe tu mensaje..." required>
        <button class="botonEnviarMensaje" type="submit">Enviar</button>
    </form>

    <?php if ($chat['es_grupo']): ?>
    <div id="modalConfigGrupo" class="modal">
        <div class="modal-content">
            <span class="cerrar">&times;</span>
            <h3>Configuración del grupo</h3>

            <!-- Editar nombre del grupo -->
            <form action="procesamientos/editarGrupo.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="chat_id" value="<?= $chat_id ?>">
                <label>Nombre del grupo:</label>
                <input maxlength="40" type="text" name="nombre_grupo" value="<?= htmlspecialchars($chat['nombre_grupo']) ?>" required>
                <button type="submit">Guardar cambios</button>
            </form>

            <h4>Participantes</h4>
            <form action="procesamientos/editarParticipantes.php" method="post" id="formParticipantes">
                <input type="hidden" name="chat_id" value="<?= $chat_id ?>">
                <input type="text" id="buscarUsuario" placeholder="Buscar usuario..." autocomplete="off">
                <div id="resultadosBusqueda" class="resultadosBusqueda"></div>
                <div id="usuariosSeleccionados" class="usuariosSeleccionados">
                    <?php foreach ($participantes as $p): ?>
                        <span class="usuarioTag" data-username="<?= htmlspecialchars($p['username']) ?>">
                            <?= htmlspecialchars($p['username']) ?>
                            <button type="button" class="quitarUsuario">&times;</button>
                            <input type="hidden" name="usuarios[]" value="<?= $p['id'] ?>">
                        </span>
                    <?php endforeach; ?>
                </div>
                <button type="submit" style="margin-top:5px;">Actualizar participantes</button>
            </form>

            <script>
                const buscarInput = document.getElementById('buscarUsuario');
                const resultados = document.getElementById('resultadosBusqueda');
                const seleccionados = document.getElementById('usuariosSeleccionados');
                const todosUsuarios = <?= json_encode($todosUsuarios) ?>;

                buscarInput.addEventListener('input', () => {
                    const query = buscarInput.value.toLowerCase().trim();
                    resultados.innerHTML = '';
                    if(!query) return;

                    const filtrados = todosUsuarios.filter(u => 
                        u.username.toLowerCase().includes(query) &&
                        !document.querySelector(`#usuariosSeleccionados input[value="${u.id}"]`)
                    );

                    filtrados.forEach(u => {
                        const div = document.createElement('div');
                        div.textContent = u.username;
                        div.classList.add('resultadoUsuario');
                        div.addEventListener('click', () => agregarUsuario(u));
                        resultados.appendChild(div);
                    });
                });

                function agregarUsuario(usuario){
                    if(document.querySelector(`#usuariosSeleccionados input[value="${usuario.id}"]`)) return;
                    const span = document.createElement('span');
                    span.classList.add('usuarioTag');
                    span.dataset.username = usuario.username;
                    span.innerHTML = `
                        ${usuario.username} 
                        <button type="button" class="quitarUsuario">&times;</button>
                        <input type="hidden" name="usuarios[]" value="${usuario.id}">
                    `;
                    seleccionados.appendChild(span);
                    resultados.innerHTML = '';
                    buscarInput.value = '';
                }

                seleccionados.addEventListener('click', (e) => {
                    if(e.target.classList.contains('quitarUsuario')){
                        e.target.parentElement.remove();
                    }
                });
            </script>

            <style>
                .resultadosBusqueda{background:#222;color:#fff;max-height:150px;overflow-y:auto;border:1px solid #444;margin-top:5px;}
                .resultadoUsuario{padding:5px;cursor:pointer;}
                .resultadoUsuario:hover{background:#555;}
                .usuariosSeleccionados{display:flex;flex-wrap:wrap;margin-top:10px;gap:5px;}
                .usuarioTag{background:#444;color:#fff;padding:3px 7px;border-radius:5px;display:flex;align-items:center;gap:5px;}
                .usuarioTag button{background:transparent;border:none;color:#fff;cursor:pointer;}
            </style>
        </div>
    </div>
    <?php endif; ?>
</main>
<script>
    function scrollChatAlFinal() {
        const chatMensajes = document.getElementById('chat-mensajes');
        if (chatMensajes) {
            chatMensajes.scrollTop = chatMensajes.scrollHeight;
        }
    }

    // Llamar la función cuando cargue la página
    window.addEventListener('load', scrollChatAlFinal);

    //Para el ajax
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Elementos Generales
        const chatMensajes = document.getElementById('chat-mensajes');
        const formulario = document.querySelector('.formularioMensajes');
        const chatId = <?= json_encode($chat_id) ?>;
        const idUsu = <?= json_encode($idUsu) ?>;
        const esGrupo = <?= $chat['es_grupo'] ? 'true' : 'false' ?>;
        let ultimoContenido = "";

        // 2. Lógica del Modal (Solo si existe)
        const btnConfig = document.getElementById('btnConfigGrupo');
        const modal = document.getElementById('modalConfigGrupo');
        const spanCerrar = document.querySelector('.cerrar');

        if (btnConfig && modal && spanCerrar) {
            btnConfig.addEventListener('click', () => modal.style.display = 'block');
            spanCerrar.addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', (e) => {
                if (e.target == modal) modal.style.display = 'none';
            });
        }

        // 3. Funciones de Chat
        function escaparHtml(texto) {
            const div = document.createElement('div');
            div.textContent = texto;
            return div.innerHTML;
        }

        function scrollAlFinal() {
            if (chatMensajes) {
                chatMensajes.scrollTop = chatMensajes.scrollHeight;
            }
        }

        function cargarMensajes() {
            // Ajustamos la ruta para que sea relativa al archivo actual
            fetch('procesamientos/get_mensajes.php?chat_id=' + chatId)
                .then(res => {
                    if (!res.ok) throw new Error("Error 404/500 en get_mensajes.php");
                    return res.json();
                })
                .then(mensajes => {
                    const contenidoSync = JSON.stringify(mensajes);
                    if (contenidoSync === ultimoContenido) return;
                    ultimoContenido = contenidoSync;

                    let html = '';
                    mensajes.forEach(m => {
                        const esTuyo = m.usuario_id == idUsu;
                        let infoUsuario = '';
                        if (!esTuyo && esGrupo) {
                            infoUsuario = `<div class="infoUsuario"><span>${escaparHtml(m.username)}</span></div>`;
                        }
                        html += `
                            <div class="mensaje ${esTuyo ? 'tuyo' : 'otro'}">
                                ${infoUsuario}
                                <div class="textoMensaje">
                                    ${escaparHtml(m.texto)}<br>
                                    <small>${m.fecha}</small>
                                </div>
                            </div>`;
                    });

                    chatMensajes.innerHTML = html;
                    scrollAlFinal();
                })
                .catch(err => console.error("Error en Fetch:", err));
        }

        // 4. Evento de Envío
        // Busca el formulario directamente por su clase
        const formChat = document.querySelector('.formularioMensajes'); 

        if (formChat) {
            formChat.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                fetch('procesamientos/guardarMensajes.php', {
                    method: 'POST',
                    body: formData
                })
                .then(() => {
                    this.reset();
                    if (typeof cargarMensajes === "function") {
                        cargarMensajes();
                    }
                })
                .catch(err => console.error("Error al enviar:", err));
            });
        } else {
            console.warn("No se encontró el formulario .formularioMensajes en el DOM");
        }

        // 5. Inicio
        scrollAlFinal();
        cargarMensajes();
        setInterval(cargarMensajes, 2000);
    });
</script>
</body>
</html>