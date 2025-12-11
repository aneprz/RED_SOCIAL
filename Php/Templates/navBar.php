<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Salsagram</title>
  <link rel="icon" type="image/png" href="/Media/logo.png">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root {
      --sidebar-width: 250px;
    }

    body {
      margin: 0;
      font-family: "Poppins", sans-serif;
      background: #f4f4f4;
      color: #222;
    }

    /* ---- SIDEBAR FIJO DESKTOP ---- */
    .sidebar {
      width: var(--sidebar-width);
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      padding: 20px;
      background: linear-gradient(180deg, #e65a36, #c13722);
      border-right: 6px solid #802015;

      display: flex;
      flex-direction: column;
      gap: 15px;

      color: #fff3e6;
      z-index: 1030;
    }

    /* Marca */
    .marca {
      font-size: 24px;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .marca img {
      width: 40px;
      height: auto;
      filter: drop-shadow(0 2px 3px black);
      border-radius: 0.5em;
    }

    /* Items */
    .nav-item-custom {
      display: flex;
      align-items: center;
      gap: 12px;

      text-decoration: none;
      color: #fff;
      padding: 11px 12px;

      background: rgba(0, 0, 0, 0.15);
      border: 3px solid rgba(0, 0, 0, 0.25);
      border-radius: 14px;

      transition: background .15s, transform .1s;
    }

    .nav-item-custom:hover {
      background: rgba(255, 120, 60, 0.25);
      transform: translateY(-2px);
      color: #fff;
    }

    .nav-item-custom.active {
      background: rgba(255, 140, 70, 0.35);
      border-color: rgba(0, 0, 0, 0.35);
    }

    /* SVG ICONS */
    .nav-item-custom svg {
      width: 26px;
      height: 26px;
      stroke-width: 2.5;
      stroke: #fff3e6;
      filter: drop-shadow(0 1px 1px black);
      flex-shrink: 0;
    }

    /* ---- MAIN CONTENT ---- */
    .main {
      margin-left: var(--sidebar-width);
      padding: 30px;
    }

    /* ---- RESPONSIVE ---- */
    @media (max-width: 768px) {
      .sidebar { display:none; }
      .main { margin-left: 0; padding-top: 80px; }
    }

    /* Topbar for mobile */
    .topbar {
      position: fixed;
      top:0;
      left:0;
      right:0;
      height: 60px;
      background: #ffffffdd;
      backdrop-filter: blur(5px);

      display: flex;
      align-items: center;
      gap: 12px;
      padding: 8px 16px;

      z-index: 1040;
    }

    @media (min-width:768px) {
      .topbar { display:none; }
    }
  </style>
</head>

<body>

  <!-- TOPBAR (solo móvil) -->
  <div class="topbar d-flex d-md-none">
    <button class="btn btn-outline-dark" data-bs-toggle="offcanvas" data-bs-target="#menuMovil">
      ☰
    </button>
    <div class="d-flex align-items-center gap-2 fw-bold">
      <img src="/Media/logo.png" width="30"> Salsagram
    </div>
  </div>

  <!-- ---- SIDEBAR FIJO ---- -->
  <aside class="sidebar d-none d-md-flex">

    <div class="marca">
      <img src="/Media/logo.png" alt="logo">
      Salsagram
    </div>

    <a href="../../index.php" class="nav-item-custom active">
      <!-- INICIO -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/>
          <path d="M3 10a2 2 0 0 1 .7-1.53l7-6a2 2 0 0 1 2.6 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        </g>
      </svg>
      Inicio
    </a>

    <a href="#" class="nav-item-custom">
      <!-- BUSQUEDA -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="m21 21-4.3-4.3"/><circle cx="11" cy="11" r="8"/>
        </g>
      </svg>
      Búsqueda
    </a>

    <a href="#" class="nav-item-custom">
      <!-- EXPLORAR -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="m16.2 7.8-1.8 5.4a2 2 0 0 1-1.3 1.3L7.8 16.2l1.8-5.4a2 2 0 0 1 1.3-1.3z"/>
          <circle cx="12" cy="12" r="10"/>
        </g>
      </svg>
      Explorar
    </a>

    <a href="#" class="nav-item-custom">
      <!-- REELS -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 9.4a.65.65 0 0 1 0 1.1l-4 2.35a.64.64 0 0 1-.97-.56V7.65a.64.64 0 0 1 .97-.56zM7 21h10"/>
          <rect width="20" height="14" x="2" y="3" rx="2"/>
        </g>
      </svg>
      Reels
    </a>

    <a href="../../Php/Chats/chats.php" class="nav-item-custom">
      <!-- MENSAJES -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path fill="none" stroke-linecap="round" stroke-linejoin="round"
              d="M22 17a2 2 0 0 1-2 2H7a2 2 0 0 0-1.4.6L3.4 22A.7.7 0 0 1 2 21.3V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/>
      </svg>
      Mensajes
    </a>

    <a href="#" class="nav-item-custom">
      <!-- NOTIFICACIONES -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15.4 15.6a7.9 6 135 1 1 6.2-6.2a4.5 3.4 135 0 0-6.2 6.2"/>
          <path d="m8.3 12.7-2.6 2.6a2.5 2.5 0 1 0-1.6 4.7a2.5 2.5 0 1 0 4 1.3l2.6-2.6"/>
        </g>
      </svg>
      Notificaciones
    </a>

    <a href="../../Php/Crear/crear.php" class="nav-item-custom">
      <!-- CREAR -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path fill="none" stroke-linecap="round" stroke-linejoin="round"
              d="M5 12h14m-7-7v14"/>
      </svg>
      Crear
    </a>

    <a href="../../Php/Usuarios/perfil.php" class="nav-item-custom">Perfil</a>

    <div class="mt-auto">
      <a href="#" class="nav-item-custom">Más</a>
    </div>
  </aside>

  <!-- ---- OFFCANVAS (MÓVIL) ---- -->
  <div class="offcanvas offcanvas-start" id="menuMovil">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title d-flex align-items-center gap-2">
        <img src="/Media/logo.png" width="36"> Salsagram
      </h5>
      <button class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">

      <!-- MISMO MENU, MISMO ICONOS -->
      <a href="#" class="nav-item-custom active d-block mb-2">Inicio</a>
      <a href="#" class="nav-item-custom d-block mb-2">Búsqueda</a>
      <a href="#" class="nav-item-custom d-block mb-2">Explorar</a>
      <a href="#" class="nav-item-custom d-block mb-2">Reels</a>
      <a href="#" class="nav-item-custom d-block mb-2">Mensajes</a>
      <a href="#" class="nav-item-custom d-block mb-2">Notificaciones</a>
      <a href="#" class="nav-item-custom d-block mb-2">Crear</a>
      <a href="#" class="nav-item-custom d-block mb-2">Perfil</a>
      <hr>
      <a href="#" class="nav-item-custom d-block">Más</a>

    </div>
  </div>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
