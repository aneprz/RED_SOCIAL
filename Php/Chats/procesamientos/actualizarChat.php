<div id="chat" style="border:1px solid #ccc; padding:10px; width:300px; height:250px; overflow-y:auto;"></div>

<input type="text" id="mensaje">
<button onclick="enviar()">Enviar</button>

<script>
let chat_id = 1;     // el chat que quieres cargar
let usuario_id = 1;  // el usuario logueado

function cargarChat() {
    fetch("leerMensajes.php?chat_id=" + chat_id)
    .then(r => r.json())
    .then(mensajes => {
        let html = "";
        mensajes.forEach(m => {
            html += `<p><b>${m.username}:</b> ${m.texto}</p>`;
        });
        document.getElementById("chat").innerHTML = html;
    });
}

// refresca cada 1 segundo
setInterval(cargarChat, 1000);

function enviar() {
    let mensaje = document.getElementById("mensaje").value;

    fetch("enviarMensajes.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `chat_id=${chat_id}&usuario_id=${usuario_id}&mensaje=${mensaje}`
    }).then(() => {
        document.getElementById("mensaje").value = "";
        cargarChat(); // recarga inmediato
    });
}
</script>
