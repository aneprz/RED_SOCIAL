<?php
require "../../BD/conexiones.php";
session_start();

if (!isset($_GET['chat_id'])) {
    die("No se especificó chat_id");
}

$chat_id = intval($_GET['chat_id']);
$idUsu = $_SESSION['id'];

// 1️⃣ Obtener información del chat
$sql = $pdo->prepare("
    SELECT c.id, c.es_grupo, c.nombre_grupo
    FROM chats c
    WHERE c.id = :chat_id
");
$sql->execute(['chat_id' => $chat_id]);
$chat = $sql->fetch(PDO::FETCH_ASSOC);

if (!$chat) {
    die("Chat no encontrado");
}

// 2️⃣ Obtener los participantes (solo para mostrar nombres en chats 1 a 1)
$sql = $pdo->prepare("
    SELECT m.id, m.usuario_id, u.username, m.texto, m.fecha, m.leido
    FROM mensajes m
    JOIN usuarios u ON u.id = m.usuario_id
    WHERE m.chat_id = :chat_id
    ORDER BY m.fecha ASC
");
$sql->execute(['chat_id' => $chat_id]);
$otrosUsuarios = $sql->fetchAll(PDO::FETCH_ASSOC);

//Para guardar todos los participantes en los chats grupales
$participantes = [];
if ($chat['es_grupo']) {
    $sql = $pdo->prepare("
        SELECT u.username
        FROM usuarios_chat uc
        JOIN usuarios u ON u.id = uc.usuario_id
        WHERE uc.chat_id = :chat_id
    ");
    $sql->execute(['chat_id' => $chat_id]);
    $participantes = $sql->fetchAll(PDO::FETCH_COLUMN); // obtenemos solo los nombres
}

// 3️⃣ Obtener mensajes del chat
$sql = $pdo->prepare("
    SELECT m.id, m.usuario_id, u.username, u.foto_perfil, m.texto, m.fecha, m.leido
    FROM mensajes m
    JOIN usuarios u ON u.id = m.usuario_id
    WHERE m.chat_id = :chat_id
    ORDER BY m.fecha ASC
");
$sql->execute(['chat_id' => $chat_id]);
$mensajes = $sql->fetchAll(PDO::FETCH_ASSOC);

//para saber si se ha leido
$marcarLeidos = $pdo->prepare("
    UPDATE mensajes
    SET leido = 1
    WHERE chat_id = :chat_id
    AND usuario_id != :yo
");
$marcarLeidos->execute([
    "chat_id" => $_GET['chat_id'],
    "yo" => $_SESSION['id']
]);

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
        $fotoPerfil = '../../../Media/foto_grupo_default.png'; // siempre por defecto para grupos
    } else {
        $nombreChat = $otrosUsuarios[0]['username'] ?? "Usuario";

        // Si la URL existe y no está vacía, la usamos; si no, la por defecto
        $fotoPerfil = (!empty($otrosUsuarios[0]['foto_perfil']) && trim($otrosUsuarios[0]['foto_perfil']) !== '')
                    ? $otrosUsuarios[0]['foto_perfil']
                    : '../../../Media/foto_default.png';
    }
    ?>

    <div class="encabezado">
        <a class="volver" href="chats.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/>
            </svg>
        </a>

        <?php
        // =========================
        // Obtener información del chat
        // =========================
        if ($chat['es_grupo']) {
            // Chat grupal
            $nombreChat = $chat['nombre_grupo'] ?: "Grupo sin nombre";
            $fotoPerfil = '../../../Media/foto_grupo_default.png';

            // Participantes del grupo
            $sql = $pdo->prepare("
                SELECT u.username
                FROM usuarios_chat uc
                JOIN usuarios u ON u.id = uc.usuario_id
                WHERE uc.chat_id = :chat_id
            ");
            $sql->execute(['chat_id' => $chat_id]);
            $participantes = $sql->fetchAll(PDO::FETCH_COLUMN);

        } else {
            // Chat 1 a 1: obtener el otro usuario
            $sql = $pdo->prepare("
                SELECT u.id, u.username, u.foto_perfil
                FROM usuarios_chat uc
                JOIN usuarios u ON u.id = uc.usuario_id
                WHERE uc.chat_id = :chat_id AND u.id != :idUsu
                LIMIT 1
            ");
            $sql->execute(['chat_id' => $chat_id, 'idUsu' => $idUsu]);
            $otroUsuario = $sql->fetch(PDO::FETCH_ASSOC);

            $nombreChat = $otroUsuario['username'] ?? "Usuario";
            $fotoPerfil = !empty($otroUsuario['foto_perfil']) 
                        ? $otroUsuario['foto_perfil'] 
                        : '../../../Media/foto_default.png';
        }
        ?>

        <img src="<?= htmlspecialchars($fotoPerfil) ?>" 
            alt="Foto de <?= htmlspecialchars($nombreChat) ?>" 
            onerror="this.onerror=null;this.src='../../../Media/foto_default.png';"
            style="width:50px; height:50px; border-radius:50%; object-fit:cover;">

        <div class="tituloIntegrantes">
            <h2><?= htmlspecialchars($nombreChat) ?></h2>

            <?php if ($chat['es_grupo'] && !empty($participantes)): ?>
                <div class="nombresParticipantes">
                    <i><?= htmlspecialchars(implode(", ", $participantes)) ?></i>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($chat['es_grupo']): ?>
            <button id="btnConfigGrupo" class="btnConfig">⚙ Configuración</button>
        <?php endif; ?>

    </div>

    <div id="chat-mensajes">
        <?php foreach ($mensajes as $m): 
            $esTuyo = $m['usuario_id'] == $idUsu;
            // Obtener foto de perfil del usuario
            $fotoUsuario = '../../../Media/foto_default.png'; // por defecto
            foreach ($otrosUsuarios as $u) {
                if ($u['id'] == $m['usuario_id']) {
                    if (!empty($u['foto_perfil']) && trim($u['foto_perfil']) !== '') {
                        $fotoUsuario = $u['foto_perfil'];
                    }
                    break;
                }
            }
        ?>
            <div class="mensaje <?= $esTuyo ? 'tuyo' : 'otro' ?>">
                <?php if (!$esTuyo && $chat['es_grupo']): ?>
                    <div class="infoUsuario">
                        <img src="<?= htmlspecialchars($fotoUsuario) ?>" 
                            alt="Foto de <?= htmlspecialchars($m['username']) ?>" 
                            onerror="this.onerror=null;this.src='../../../Media/foto_default.png';"
                            class="fotoUsuario">
                        <span class="nombreUsuario"><?= htmlspecialchars($m['username']) ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="textoMensaje">
                    <?= htmlspecialchars($m['texto']) ?>
                    <br>
                    <small class="fecha">(<?= $m['fecha'] ?>)</small>

                    <?php if ($esTuyo): ?>
                        <?php if ($m['leido']): ?>
                            <span class="leidoEstado"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="m10.6 13.8l-2.15-2.15q-.275-.275-.7-.275t-.7.275t-.275.7t.275.7L9.9 15.9q.3.3.7.3t.7-.3l5.65-5.65q.275-.275.275-.7t-.275-.7t-.7-.275t-.7.275zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22"/></svg></span>
                        <?php else: ?>
                            <span class="noLeidoEstado"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="m10.6 13.8l-2.15-2.15q-.275-.275-.7-.275t-.7.275t-.275.7t.275.7L9.9 15.9q.3.3.7.3t.7-.3l5.65-5.65q.275-.275.275-.7t-.275-.7t-.7-.275t-.7.275zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const chatMensajes = document.getElementById('chat-mensajes');
        chatMensajes.scrollTop = chatMensajes.scrollHeight;
    });
    </script>

    <!-- Formulario para enviar mensajes -->
    <form class="formularioMensajes" action="procesamientos/guardarMensajes.php" method="post">
        <input type="hidden" name="chat_id" value="<?= $chat_id ?>">
        <input type="hidden" name="usuario_id" value="<?= $idUsu ?>">
        <input class="escribirMensaje" type="text" name="mensaje" placeholder="Escribe tu mensaje..." required>
        <button type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M3 20v-6l8-2l-8-2V4l19 8z"/></svg></button>
    </form>

    <?php if ($chat['es_grupo']): ?>
        <div id="modalConfigGrupo" class="modal">
            <div class="modal-content">
                <span class="cerrar">&times;</span>
                <h3>Configuración del grupo</h3>

                <!-- Formulario para editar nombre y foto -->
                <form action="procesamientos/editarGrupo.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="chat_id" value="<?= $chat_id ?>">
                    <label for="nombre_grupo">Nombre del grupo:</label>
                    <input type="text" id="nombre_grupo" name="nombre_grupo" value="<?= htmlspecialchars($chat['nombre_grupo']) ?>" required>

                    <button type="submit">Guardar cambios</button>
                </form>

                <?php
                    $participantes = [];
                    if ($chat['es_grupo']) {
                        $sql = $pdo->prepare("
                            SELECT u.id, u.username
                            FROM usuarios_chat uc
                            JOIN usuarios u ON u.id = uc.usuario_id
                            WHERE uc.chat_id = :chat_id
                        ");
                        $sql->execute(['chat_id' => $chat_id]);
                        $participantes = $sql->fetchAll(PDO::FETCH_ASSOC); // ahora es un array con id y username
                    }
                ?>

                <h4>Participantes</h4>
                <form action="procesamientos/editarParticipantes.php" method="post" id="formParticipantes">
                    <input type="hidden" name="chat_id" value="<?= $chat_id ?>">

                    <!-- Input de búsqueda -->
                    <input type="text" id="buscarUsuario" placeholder="Buscar usuario..." autocomplete="off">

                    <!-- Lista de resultados -->
                    <div id="resultadosBusqueda" class="resultadosBusqueda"></div>

                    <!-- Participantes seleccionados -->
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

                // Lista de todos los usuarios que sigues (puedes filtrar por los que sigues desde PHP)
                const todosUsuarios = <?= json_encode($todosUsuarios) ?>;

                // Función para renderizar resultados filtrados
                buscarInput.addEventListener('input', () => {
                    const query = buscarInput.value.toLowerCase().trim();
                    resultados.innerHTML = '';

                    if(query === '') return;

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
                    const span = document.createElement('span');
                    span.classList.add('usuarioTag');
                    span.dataset.username = usuario.username;
                    span.innerHTML = `
                        ${usuario.username}
                        <button type="button" class="quitarUsuario">&times;</button>
                        <input type="hidden" name="usuarios[]" value="${usuario.id}">
                    `;
                    seleccionados.appendChild(span);

                    span.querySelector('.quitarUsuario').addEventListener('click', () => {
                        span.remove();
                    });

                    resultados.innerHTML = '';
                    buscarInput.value = '';
                }

                // Quitar usuarios ya seleccionados
                document.querySelectorAll('.quitarUsuario').forEach(btn => {
                    btn.addEventListener('click', e => {
                        e.target.parentElement.remove();
                    });
                });
                </script>

                <style>
                .resultadosBusqueda{
                    background: #222;
                    color: #fff;
                    max-height: 150px;
                    overflow-y: auto;
                    border: 1px solid #444;
                    margin-top: 5px;
                }
                .resultadoUsuario{
                    padding: 5px;
                    cursor: pointer;
                }
                .resultadoUsuario:hover{
                    background: #555;
                }
                .usuariosSeleccionados{
                    display: flex;
                    flex-wrap: wrap;
                    margin-top: 10px;
                    gap: 5px;
                }
                .usuarioTag{
                    background: #444;
                    color: #fff;
                    padding: 3px 7px;
                    border-radius: 5px;
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                .usuarioTag button{
                    background: transparent;
                    border: none;
                    color: #fff;
                    cursor: pointer;
                }
                </style>

            </div>
        </div>
    <?php endif; ?>
    <script>
        const btnConfig = document.getElementById('btnConfigGrupo');
        const modal = document.getElementById('modalConfigGrupo');
        const spanCerrar = document.querySelector('.cerrar');

        btnConfig.addEventListener('click', () => {
            modal.style.display = 'block';
        });

        spanCerrar.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</main>
<?php include __DIR__ . '../../../Php/Templates/footer.php';?>
</body>
</html>