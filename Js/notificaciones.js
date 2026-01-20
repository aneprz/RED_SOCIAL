const modalNotif = document.getElementById("modalNotificaciones");
const badge = document.getElementById("badgeNotificaciones");

// Función principal para abrir (llamada desde el HTML onclick)
function abrirNotificaciones(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Mostrar modal
    modalNotif.style.display = "block";
    
    // Ocultar el badge rojo
    if (badge) badge.style.display = 'none';

    // Cargar datos
    cargarContenidoNotificaciones();
}

// Función para cerrar (llamada desde el HTML onclick)
function cerrarNotificaciones() {
    if (modalNotif) modalNotif.style.display = "none";
}

// Cerrar si clic fuera del contenido
window.addEventListener('click', (e) => {
    if (e.target === modalNotif) {
        cerrarNotificaciones();
    }
});

function cargarContenidoNotificaciones() {
    const lista = document.getElementById("listaNotificaciones");
    lista.innerHTML = '<p style="text-align:center; padding:10px;">Cargando...</p>';

    fetch('../../Php/Notificaciones/obtener.php')
        .then(res => res.json()) // IMPORTANTE: Esperamos JSON, no text
        .then(data => {
            if (!data.success) {
                lista.innerHTML = `<p style="color:red; text-align:center;">Error: ${data.error}</p>`;
                return;
            }

            lista.innerHTML = '';
            if (data.data.length === 0) {
                lista.innerHTML = '<p style="text-align:center; color:#888;">No tienes notificaciones nuevas.</p>';
                return;
            }

            // Generar HTML basado en el JSON
            data.data.forEach(item => {
                let html = '';
                
                // Tipo 1: Solicitud de seguimiento
                if (item.tipo === 'solicitud') {
                    html = `
                        <div class="notificacion-item">
                            <img src="${item.foto_perfil || '/Media/foto_default.png'}" class="notif-img">
                            <div class="notif-info">
                                <strong>${item.username}</strong> quiere seguirte.
                            </div>
                            <button class="btn-accion btn-aceptar" onclick="aceptarSolicitud(${item.usuario_origen_id})">Aceptar</button>
                        </div>
                    `;
                }
                // Tipo 2: Te han seguido
                else if (item.tipo === 'follow') {
                    html = `
                        <div class="notificacion-item">
                            <img src="${item.foto_perfil || '/Media/foto_default.png'}" class="notif-img">
                            <div class="notif-info">
                                <strong>${item.username}</strong> ha comenzado a seguirte.
                            </div>
                            <button class="btn-accion btn-seguir" onclick="seguirDeVuelta(${item.usuario_origen_id})">Seguir</button>
                        </div>
                    `;
                }
                // Tipo 3: Like
                else if (item.tipo === 'like') {
                    // Si por error num_likes viene vacío, ponemos 1 (el del usuario que acaba de dar like)
                    const totalPicantes = item.num_likes || 1; 
                    
                    html = `
                        <div class="notificacion-item">
                            <img src="${item.foto_perfil || '/Media/foto_default.png'}" class="notif-img">
                            <div class="notif-info">
                                A <strong>${item.username}</strong> le gustó tu publicación, tiene: ${totalPicantes} Picante.
                            </div>
                        </div>
                    `;
                }
                // Tipo 4: Sugerencia
                else if (item.tipo === 'sugerencia') {
                    html = `
                        <div class="notificacion-item" style="background:rgba(255,255,255,0.02)">
                            <img src="${item.foto_perfil || '/Media/foto_default.png'}" class="notif-img">
                            <div class="notif-info">
                                Sugerencia: <strong>${item.username}</strong>
                            </div>
                            <button class="btn-accion btn-seguir" onclick="seguirDeVuelta(${item.usuario_origen_id})">Seguir</button>
                        </div>
                    `;
                }

                lista.innerHTML += html;
            });
        })
        .catch(err => {
            console.error(err);
            lista.innerHTML = '<p style="color:red;">Error de conexión.</p>';
        });
}

// Lógica de Likes en tiempo real (Index)
setInterval(() => {
    // Busca elementos que empiecen por "likes-count-"
    const contadores = document.querySelectorAll("[id^='likes-count-']");
    if(contadores.length === 0) return;

    let ids = [];
    contadores.forEach(el => {
        // Extraer el ID numérico del string "likes-count-15" -> "15"
        const id = el.id.replace("likes-count-", "");
        if(id) ids.push(id);
    });

    if(ids.length > 0) {
        // Usamos fetch a get_likes_updates.php (asegurate de crear este archivo como dijimos antes)
        fetch('../../Php/Index/get_likes_updates.php?ids=' + ids.join(','))
            .then(res => res.json())
            .then(data => {
                for (const [pid, total] of Object.entries(data)) {
                    const el = document.getElementById('likes-count-' + pid);
                    if(el) el.innerText = total;
                }
            })
            .catch(e => console.log("Error actualizando likes"));
    }
}, 4000);

function aceptarSolicitud(idUsuario) {
    // CORRECCIÓN: Ruta apuntando a Php/Usuarios/aceptar_seguimiento.php
    fetch('../../Php/Usuarios/aceptar_seguimiento.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'solicitante_id=' + idUsuario
    }).then(res => res.json()).then(data => {
        // Recargar la lista si todo sale bien
        cargarContenidoNotificaciones();
        // Opcional: Actualizar contadores si los tienes visibles
    }).catch(err => console.error("Error aceptando solicitud", err));
}

function seguirDeVuelta(idUsuario) {

    fetch('/Php/Usuarios/seguir_usuario.php', { 
        
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id_usuario=' + idUsuario 
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            if(data.estado === 'solicitado') {
                alert("Solicitud enviada (Cuenta Privada)");
            } else if (data.estado === 'siguiendo') {
                alert("Ahora sigues a este usuario");
            } else {
                alert("Dejaste de seguir/solicitar");
            }
            // Recargar notificaciones para actualizar botones si es necesario
            cargarContenidoNotificaciones(); 
        }
    })
    .catch(err => console.error("Error al seguir:", err));
}