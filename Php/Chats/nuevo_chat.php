<?php
require "../../BD/conexiones.php";
session_start();

$idUsu = $_SESSION['id'];

// Traer todos los usuarios excepto el actual
$sql = $pdo->prepare("
    SELECT u.id, u.username
    FROM usuarios u
    INNER JOIN seguidores s ON s.seguido_id= u.id
    WHERE s.seguidor_id = :idUsu");
$sql->execute(["idUsu" => $idUsu]);
$usuarios = $sql->fetchAll(PDO::FETCH_ASSOC);

// Generar token anti-doble envío
if (!isset($_SESSION['nuevo_chat_token'])) {
    $_SESSION['nuevo_chat_token'] = bin2hex(random_bytes(16));
}
$token = $_SESSION['nuevo_chat_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nuevo chat</title>
<link rel="stylesheet" href="../../../Estilos/estilos_chats.css">
<style>
.usuario-opcion { cursor: pointer; padding: 5px; }
.usuario-opcion:hover { background-color: #eee; }
.seleccionados span { margin-right: 5px; background: #ddd; padding: 3px 6px; border-radius: 3px; display: inline-block; }
.seleccionados span button { margin-left: 3px; background-color: #E94B3C; color: white; border: none; border-radius: 3px; cursor: pointer; }
</style>
</head>
<body>
<?php include __DIR__ . '../../../Php/Templates/navBar.php';?>
<main>
<div class="encabezadoNuevoChat">
    <a class="volver" href="chats.php">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path fill="currentColor" d="m9.55 12l7.35 7.35q.375.375.363.875t-.388.875t-.875.375t-.875-.375l-7.7-7.675q-.3-.3-.45-.675t-.15-.75t.15-.75t.45-.675l7.7-7.7q.375-.375.888-.363t.887.388t.375.875t-.375.875z"/>
        </svg>
    </a>
    <h2>Crear Nuevo Chat</h2>
</div>

<form action="procesamientos/crear_chat.php" method="post" id="formChat">
    <input type="hidden" name="chat_token" value="<?= $token ?>">

    <label for="tipoChat">Tipo de chat:</label>
    <select id="tipoChat" name="tipo_chat" required>
        <option value="">--Selecciona--</option>
        <option value="individual">Individual</option>
        <option value="grupo">Grupo</option>
    </select>

    <div id="nombreGrupoDiv" style="display:none; margin-top:10px;">
        <label for="nombreGrupo">Nombre del grupo:</label>
        <input type="text" id="nombreGrupo" name="nombre_grupo">
    </div>

    <div id="buscadorUsuariosDiv" style="display:none; margin-top:10px;">
        <label for="buscadorUsuarios">Buscar usuario(s):</label>
        <input type="text" id="buscadorUsuarios" placeholder="Escribe un nombre...">
        <div id="resultadosUsuarios"></div>
        <div class="seleccionados" id="usuariosSeleccionados"></div>
    </div>
    <button class="crearChat" type="submit">Crear Chat</button>
</form>
</main>

<script>
const usuarios = <?= json_encode($usuarios) ?>;
const tipoChatSelect = document.getElementById('tipoChat');
const nombreGrupoDiv = document.getElementById('nombreGrupoDiv');
const buscadorUsuariosDiv = document.getElementById('buscadorUsuariosDiv');
const buscadorUsuarios = document.getElementById('buscadorUsuarios');
const resultadosUsuarios = document.getElementById('resultadosUsuarios');
const usuariosSeleccionados = document.getElementById('usuariosSeleccionados');
const form = document.getElementById('formChat');
const btnSubmit = form.querySelector('button[type="submit"]');

let seleccionados = [];

tipoChatSelect.addEventListener('change', () => {
    seleccionados = [];
    usuariosSeleccionados.innerHTML = '';
    buscadorUsuarios.value = '';
    resultadosUsuarios.innerHTML = '';

    if(tipoChatSelect.value === 'grupo') {
        nombreGrupoDiv.style.display = 'block';
        buscadorUsuariosDiv.style.display = 'block';
    } else if(tipoChatSelect.value === 'individual') {
        nombreGrupoDiv.style.display = 'none';
        buscadorUsuariosDiv.style.display = 'block';
    } else {
        nombreGrupoDiv.style.display = 'none';
        buscadorUsuariosDiv.style.display = 'none';
    }
});

buscadorUsuarios.addEventListener('input', () => {
    const texto = buscadorUsuarios.value.toLowerCase();
    resultadosUsuarios.innerHTML = '';
    if(texto === '') return;

    const filtrados = usuarios.filter(u => 
        u.username.toLowerCase().includes(texto) && !seleccionados.find(s => s.id === u.id)
    );

    filtrados.forEach(u => {
        const div = document.createElement('div');
        div.textContent = u.username;
        div.className = 'usuario-opcion';
        div.addEventListener('click', () => agregarUsuario(u));
        resultadosUsuarios.appendChild(div);
    });
});

function agregarUsuario(usuario) {
    if(seleccionados.find(u => u.id === usuario.id)) return;

    if(tipoChatSelect.value === 'individual') {
        // Para chat individual solo 1 usuario
        seleccionados = [usuario];
        renderSeleccionados();
    } else {
        // Para chat grupal máximo 15 usuarios
        if(seleccionados.length >= 15) {
            alert("No puedes agregar más de 15 usuarios a un chat grupal.");
            return;
        }
        seleccionados.push(usuario);
        renderSeleccionados();
    }

    resultadosUsuarios.innerHTML = '';
    buscadorUsuarios.value = '';
}

function renderSeleccionados() {
    usuariosSeleccionados.innerHTML = '';
    seleccionados.forEach(u => {
        const span = document.createElement('span');
        span.textContent = u.username;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = 'X';
        btn.onclick = () => {
            seleccionados = seleccionados.filter(s => s.id !== u.id);
            renderSeleccionados();
        };
        span.appendChild(btn);
        usuariosSeleccionados.appendChild(span);
    });
}

// Enviar formulario
form.addEventListener('submit', e => {
    if(seleccionados.length === 0) {
        e.preventDefault();
        alert("Debes seleccionar al menos un usuario.");
        return;
    }

    // Validar mínimo y máximo según tipo de chat
    if(tipoChatSelect.value === 'grupo') {
        if(seleccionados.length < 2) {
            e.preventDefault();
            alert("Un chat grupal debe tener al menos 3 miembros.");
            return;
        }
        if(seleccionados.length > 15) {
            e.preventDefault();
            alert("Un chat grupal no puede tener más de 15 miembros.");
            return;
        }
    } else if(tipoChatSelect.value === 'individual') {
        if(seleccionados.length > 1) {
            e.preventDefault();
            alert("Un chat individual solo puede tener un usuario seleccionado.");
            return;
        }
    }

    btnSubmit.disabled = true;

    // Evitar duplicados
    seleccionados = [...new Map(seleccionados.map(u => [u.id, u])).values()];

    // Limpiar antiguos inputs
    document.querySelectorAll('input[name="usuarios[]"]').forEach(i => i.remove());

    // Crear inputs ocultos
    seleccionados.forEach(u => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'usuarios[]';
        input.value = u.id;
        form.appendChild(input);
    });
});
</script>

<?php include __DIR__ . '../../../Php/Templates/footer.php';?>
</body>
</html>
