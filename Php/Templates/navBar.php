<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Salsagram</title>
  <link rel="icon" type="image/png" href="/Media/logo.png">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../Estilos/estilos_navbar.css">
  <link rel="stylesheet" href="../../Estilos/notificaciones.css">
</head>
<body>

  <div class="topbar d-flex d-md-none">
    <button class="btn btn-outline-light" data-bs-toggle="offcanvas" data-bs-target="#menuMovil">☰</button>
    <div class="d-flex align-items-center gap-2 fw-bold">
      <img src="/Media/logo.png" width="30"> Salsagram
    </div>
  </div>

  <aside class="sidebar d-none d-md-flex">

    <div class="marca">
      <img src="/Media/logo.png" alt="logo"> Salsagram
    </div>

    <a href="../../index.php" class="nav-item-custom active">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .7-1.53l7-6a2 2 0 0 1 2.6 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></g></svg>
      Inicio
    </a>

    <a href="../../Php/Busqueda/busqueda.php" class="nav-item-custom">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="m21 21-4.3-4.3"/><circle cx="11" cy="11" r="8"/></g></svg>
      Búsqueda
    </a>

    <a href="../../Php/Explorar/explorar.php" class="nav-item-custom">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="m16.2 7.8-1.8 5.4a2 2 0 0 1-1.3 1.3L7.8 16.2l1.8-5.4a2 2 0 0 1 1.3-1.3z"/><circle cx="12" cy="12" r="10"/></g></svg>
      Explorar
    </a>

    <a href="../../Php/Saals/saals.php" class="nav-item-custom">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M15 9.4a.65.65 0 0 1 0 1.1l-4 2.35a.64.64 0 0 1-.97-.56V7.65a.64.64 0 0 1 .97-.56zM7 21h10"/><rect width="20" height="14" x="2" y="3" rx="2"/></g></svg>
      Sals
    </a>

    <a href="../../Php/Chats/chats.php" class="nav-item-custom">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke-linecap="round" stroke-linejoin="round" d="M22 17a2 2 0 0 1-2 2H7a2 2 0 0 0-1.4.6L3.4 22A.7.7 0 0 1 2 21.3V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/></svg>
      Chats
    </a>

    <a href="#" class="nav-item-custom" id="btnNotificaciones" onclick="abrirNotificaciones(event)">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M15.4 15.6a7.9 6 135 1 1 6.2-6.2a4.5 3.4 135 0 0-6.2 6.2"/><path d="m8.3 12.7-2.6 2.6a2.5 2.5 0 1 0-1.6 4.7a2.5 2.5 0 1 0 4 1.3l2.6-2.6"/></g></svg>
      Notificaciones
      <span id="badgeNotificaciones" style="display:none; background:#E94B3C; color:white; border-radius:50%; padding:2px 6px; font-size:10px; margin-left:auto;">0</span>
    </a>

    <a href="#" class="nav-item-custom" id="abrirCrear" role="button">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7v14"/></svg>
      Crear
    </a>

    <a href="../../Php/Usuarios/perfil.php" class="nav-item-custom">Perfil</a>

    <div class="mt-auto">
      <a href="#" class="nav-item-custom">Más</a>
    </div>

  </aside> <div id="modalNotificaciones" class="modal-notificaciones">
    <div class="modal-content-notif">
      <span class="close-notif" onclick="cerrarNotificaciones()">&times;</span>
      <h2 style="margin-top:0; font-size:1.2rem; border-bottom:1px solid #333; padding-bottom:10px;">Notificaciones</h2>
      <div id="listaNotificaciones" class="notificaciones-list">
        <p style="text-align:center; color:#ccc; padding:20px;">Cargando...</p>
      </div>
    </div>
  </div>

  <div id="modal" class="modal" role="dialog" tabindex="-1">
    <div class="modal-overlay" id="modalOverlay"></div>
    <div class="modal-content" role="document">
      <button id="closeModal" class="modal-close" aria-label="Cerrar ventana">&times;</button>
      <h2 id="modalTitle" class="modal-title">Crear nueva publicación</h2>
      
      <form id="postForm" action="../../Php/Crear/upload.php" method="post" enctype="multipart/form-data" style="width:100%;max-width:760px; display:flex; flex-direction:column; gap:12px;">
        <div id="uploadArea">
          <label class="btn-select" for="fileInput">Seleccionar foto o vídeo</label>
          <input id="fileInput" name="file" type="file" accept="image/*,video/*" style="display:none" />
          <p class="upload-hint">Elige un archivo.</p>
        </div>
        <div id="previewContainer" style="display:none;">
          <div id="mediaWrapper" class="media-wrapper"></div>
        </div>
        <div id="formFields" style="display:none; margin-top:6px; flex-direction:column; gap:12px;">
          <label>Pie de foto
            <textarea name="caption" id="caption" rows="3" style="width:100%;padding:8px;border-radius:8px;"></textarea>
          </label>
          <label>Ubicación
            <input type="text" name="ubicacion" placeholder="Ciudad, país..." />
          </label>
          <div id="tagsArea">
             <input id="manualTagInput" type="text" placeholder="Etiquetar..." style="width:100%;padding:8px;border-radius:6px; margin-bottom:5px;" />
             <div id="tagSuggestions" class="tag-suggestions"></div>
             <div id="tagsList" class="tags-list"></div>
          </div>
          <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;">
            <button id="cancelUpload" type="button" class="btn btn-ghost">Cancelar</button>
            <button id="btnSubmitFile" type="button" class="btn primary">Subir Archivo</button>
          </div>
        </div>
        <div id="hiddenTagInputs"></div>
      </form>
    </div>
  </div>

  <div class="offcanvas offcanvas-start" id="menuMovil">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title d-flex align-items-center gap-2">
        <img src="/Media/logo.png" width="36"> Salsagram
      </h5>
      <button class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <a href="../../index.php" class="nav-item-custom active d-block mb-2">Inicio</a>
      <a href="../Busqueda/busqueda.php" class="nav-item-custom active d-block mb-2">Búsqueda</a>
      <a href="../Explorar/explorar.php" class="nav-item-custom active d-block mb-2">Explorar</a>
      <a href="../Saals/saals.php" class="nav-item-custom active d-block mb-2">Sals</a>
      <a href="../Chats/chats.php" class="nav-item-custom active d-block mb-2">Chats</a>
      <a href="#" class="nav-item-custom d-block mb-2" onclick="abrirNotificaciones(event); bootstrap.Offcanvas.getInstance(document.getElementById('menuMovil')).hide()">Notificaciones</a>
      <a href="#" class="nav-item-custom d-block mb-2" id="abrirCrearMovil">Crear</a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../../Js/Crear/crear.js" defer></script>
  <script src="../../Js/notificaciones.js" defer></script>

</body>
</html>