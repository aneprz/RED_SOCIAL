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

// Participantes del grupo (id y username)
$participantes = [];
if ($chat['es_grupo']) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username
        FROM usuarios_chat uc
        JOIN usuarios u ON u.id = uc.usuario_id
        WHERE uc.chat_id = :chat_id
    ");
    $stmt->execute(['chat_id' => $chat_id]);
    $participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Todos los usuarios que sigues (para búsqueda)
$todosUsuarios = $pdo->query("SELECT id, username FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <link rel="stylesheet" href="../../../Estilos/estilos_chat.css">
</head>
<body>
<?php include __DIR__ . '../../../Php/Templates/navBar.php';?>

<main>
    <?php
    if ($chat['es_grupo']) {
        $nombreChat = $chat['nombre_grupo'] ?: "Grupo sin nombre";
        $fotoPerfil = '../../../Media/foto_grupo_default.png';
    } else {
        $nombreChat = $mensajes[0]['username'] ?? "Usuario";
        $fotoPerfil = !empty($mensajes[0]['foto_perfil']) ? $mensajes[0]['foto_perfil'] : '../../../Media/foto_default.png';
    }
    ?>

    <div class="encabezado">
        <a class="volver" href="chats.php">← Volver</a>
        <img src="<?= htmlspecialchars($fotoPerfil) ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
        <div class="tituloIntegrantes">
            <h2><?= htmlspecialchars($nombreChat) ?></h2>
            <?php if ($chat['es_grupo'] && $participantes): ?>
                <i><?= htmlspecialchars(implode(", ", array_column($participantes, 'username'))) ?></i>
            <?php endif; ?>
        </div>
        <?php if ($chat['es_grupo']): ?>
            <button id="btnConfigGrupo" class="btnConfig">⚙ Configuración</button>
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
                    <small><?= $m['fecha'] ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Formulario para enviar mensajes -->
    <form class="formularioMensajes" action="procesamientos/guardarMensajes.php" method="post">
        <input type="hidden" name="chat_id" value="<?= $chat_id ?>">
        <input type="hidden" name="usuario_id" value="<?= $idUsu ?>">
        <input type="text" name="mensaje" placeholder="Escribe tu mensaje..." required>
        <button type="submit">Enviar</button>
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
                <input type="text" name="nombre_grupo" value="<?= htmlspecialchars($chat['nombre_grupo']) ?>" required>
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
                <button type="submit">Actualizar participantes</button>
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

    <script>
        const btnConfig = document.getElementById('btnConfigGrupo');
        const modal = document.getElementById('modalConfigGrupo');
        const spanCerrar = document.querySelector('.cerrar');
        btnConfig.addEventListener('click', () => modal.style.display='block');
        spanCerrar.addEventListener('click', () => modal.style.display='none');
        window.addEventListener('click', (e) => {if(e.target==modal) modal.style.display='none';});
    </script>
</main>

<?php include __DIR__ . '../../../Php/Templates/footer.php';?>
</body>
</html>
