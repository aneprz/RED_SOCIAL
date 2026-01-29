const modalNotif = document.getElementById("modalNotificaciones");
const badge = document.getElementById("badgeNotificaciones");

// --- 1. DETECCIÓN INTELIGENTE DE RUTA ---
// Si la URL contiene "/Php/", significa que estamos dentro de una subcarpeta (Explorar, Perfil, etc.)
// y necesitamos salir con "../". Si no, estamos en el index y entramos con "Php/".
const inSubFolder = window.location.href.includes("/Php/");
const rutaBase = inSubFolder ? "../" : "Php/"; 

// --- APERTURA Y CIERRE DEL MODAL ---

function abrirNotificaciones(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    if(modalNotif) modalNotif.style.display = "block";
    
    // Ocultar el badge rojo al abrir
    if (badge) badge.style.display = 'none';

    cargarContenidoNotificaciones();
}

function cerrarNotificaciones() {
    if (modalNotif) modalNotif.style.display = "none";
}

// Cerrar si clic fuera del contenido
window.addEventListener('click', (e) => {
    if (e.target === modalNotif) {
        cerrarNotificaciones();
    }
});

// --- CARGA DE DATOS ---

function cargarContenidoNotificaciones() {
    const lista = document.getElementById("listaNotificaciones");
    if(!lista) return;

    lista.innerHTML = '<p style="text-align:center; padding:10px; color:#666;">Cargando...</p>';

    // CORREGIDO: Usamos rutaBase
    fetch(rutaBase + 'Notificaciones/obtener.php') 
        .then(res => res.json())
        .then(data => {
            if (!data.success && data.error) {
                lista.innerHTML = `<p style="color:red; text-align:center;">Error: ${data.error}</p>`;
                return;
            }

            if (!data.data || data.data.length === 0) {
                lista.innerHTML = '<p style="text-align:center; color:#888; padding: 20px;">No tienes notificaciones nuevas.</p>';
                return;
            }

            lista.innerHTML = ''; 

            data.data.forEach(item => {
                let html = '';
                const foto = item.foto_perfil || '/Media/foto_default.png';

                // CORREGIDO: Usamos la misma lógica para el action del formulario
                const actionForm = rutaBase + 'Busqueda/usuarioAjeno.php';

                const htmlFoto = `
                    <form action="${actionForm}" method="POST" style="display:inline; margin-right: 10px;">
                        <input type="hidden" name="id" value="${item.usuario_origen_id}">
                        <button type="submit" style="border:none; background:none; padding:0; cursor:pointer;" title="Ver perfil">
                            <img src="${foto}" class="notif-img">
                        </button>
                    </form>
                `;
                // ----------------------------------------------------

                // 1. Solicitud
                if (item.tipo === 'solicitud') {
                    html = `
                        <div class="notificacion-item">
                            ${htmlFoto}
                            <div class="notif-info">
                                <strong>${item.username}</strong> quiere seguirte.
                            </div>
                            <button class="btn-accion btn-aceptar" onclick="aceptarSolicitud(${item.usuario_origen_id})">Aceptar</button>
                        </div>`;
                }
                // 2. Follow
                else if (item.tipo === 'follow') {
                    html = `
                        <div class="notificacion-item">
                            ${htmlFoto}
                            <div class="notif-info">
                                <strong>${item.username}</strong> ha comenzado a seguirte.
                            </div>
                            <button class="btn-accion btn-seguir" onclick="seguirDeVuelta(${item.usuario_origen_id}, this)">Seguir</button>
                        </div>`;
                }
                // 3. Like
                else if (item.tipo === 'like') {
                    const totalPicantes = item.num_likes || 1; 
                    html = `
                        <div class="notificacion-item">
                            ${htmlFoto}
                            <div class="notif-info">
                                A <strong>${item.username}</strong> le gustó tu publicación. <br>
                                <span style="font-size:0.9em; color:#666;">🌶️ ${totalPicantes} picantes.</span>
                            </div>
                        </div>`;
                }
                // 4. Sugerencia
                else if (item.tipo === 'sugerencia') {
                    html = `
                        <div class="notificacion-item" style="background:rgba(0,0,0,0.03)">
                            ${htmlFoto}
                            <div class="notif-info">
                                Sugerencia: <strong>${item.username}</strong>
                            </div>
                            <button class="btn-accion btn-seguir" onclick="seguirDeVuelta(${item.usuario_origen_id}, this)">Seguir</button>
                        </div>`;
                }
                // 5. Etiqueta
                else if (item.tipo === 'etiqueta') {
                    html = `
                        <div class="notificacion-item">
                            ${htmlFoto}
                            <div class="notif-info">
                                <strong>${item.username}</strong> te ha etiquetado para que colabores en una publicación.
                            </div>
                        </div>`;
                }

                lista.innerHTML += html;
            });
        })
        .catch(err => {
            console.error(err);
            lista.innerHTML = '<p style="color:red; text-align:center;">Error de conexión.</p>';
        });
}

// --- ACCIONES (Seguir y Aceptar) ---

function aceptarSolicitud(idUsuario) {
    // CORREGIDO: Usamos rutaBase
    fetch(rutaBase + 'Usuarios/aceptar_seguimiento.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'solicitante_id=' + idUsuario
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            cargarContenidoNotificaciones();
        } else {
            alert("No se pudo aceptar la solicitud.");
        }
    })
    .catch(err => console.error("Error aceptando solicitud", err));
}

function seguirDeVuelta(idUsuario, btnElement) {
    btnElement.disabled = true;
    const textoOriginal = btnElement.innerText;
    btnElement.innerText = "..."; 

    // CORREGIDO: Usamos rutaBase
    fetch(rutaBase + 'Usuarios/seguir_usuario.php', { 
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id_usuario=' + idUsuario 
    })
    .then(res => res.json())
    .then(data => {
        btnElement.disabled = false;

        if(data.status === 'success') {
            if(data.estado === 'solicitado') {
                btnElement.innerText = "Solicitado";
                btnElement.classList.add('btn-gris'); 
                btnElement.classList.remove('btn-seguir');
            } 
            else if (data.estado === 'siguiendo') {
                btnElement.innerText = "Siguiendo";
                btnElement.classList.add('btn-gris');
                btnElement.classList.remove('btn-seguir');
            } 
            else { // no_seguido
                btnElement.innerText = "Seguir";
                btnElement.classList.remove('btn-gris');
                btnElement.classList.add('btn-seguir');
            }
        } else {
            console.warn(data.message);
            btnElement.innerText = textoOriginal;
        }
    })
    .catch(err => {
        console.error("Error al seguir:", err);
        btnElement.disabled = false;
        btnElement.innerText = textoOriginal;
    });
}

// --- ACTUALIZACIÓN DE LIKES EN TIEMPO REAL (SOLO EN INDEX) ---

setInterval(() => {
    // Solo ejecutamos esto si estamos en el index (donde rutaBase es 'Php/')
    // O si decides tener likes en tiempo real en subpáginas, usa rutaBase también.
    
    const contadores = document.querySelectorAll("[id^='likes-count-']");
    if(contadores.length === 0) return;

    let ids = [];
    contadores.forEach(el => {
        const id = el.id.replace("likes-count-", "");
        if(id) ids.push(id);
    });

    if(ids.length > 0) {
        // CORREGIDO: Usamos rutaBase
        fetch(rutaBase + 'Index/get_likes_updates.php?ids=' + ids.join(','))
            .then(res => res.json())
            .then(data => {
                for (const [pid, total] of Object.entries(data)) {
                    const el = document.getElementById('likes-count-' + pid);
                    if(el) el.innerText = total;
                }
            })
            .catch(e => console.log("Error silencioso actualizando likes"));
    }
}, 4000);