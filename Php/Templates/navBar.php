<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Sidebar Estilo Salsagram</title>

  <style>
    body {
      margin: 0;
      font-family: "Poppins", sans-serif;
      background: linear-gradient(180deg, #d94830, #b22a1e);
      color: #fff;
    }

    .navbar {
      width: 250px;
      height: 100vh;
      background: linear-gradient(180deg, #e65a36, #c13722);
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 15px;
      border-right: 6px solid #802015;
    }

    .marca {
      font-size: 24px;
      font-weight: 800;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
      color: #fff3e6;
    }

    .marca img {
      width: 40px;
      height: auto;
      filter: drop-shadow(0px 2px 3px #000);
    }

    a.nav-item {
      padding: 12px;
      border-radius: 14px;
      cursor: pointer;
      color: #fff;
      text-decoration: none;

      display: flex;
      align-items: center;
      gap: 12px;

      background: rgba(0, 0, 0, 0.15);
      border: 3px solid rgba(0, 0, 0, 0.25);

      transition: background 0.2s, transform 0.1s;
    }

    a.nav-item:hover {
      background: rgba(255, 120, 60, 0.25);
      transform: translateY(-2px);
    }

    .active {
      background: rgba(255, 140, 70, 0.35);
      border-color: rgba(0, 0, 0, 0.35);
    }

    .spacer {
      flex: 1;
    }

    svg {
      width: 26px;
      height: 26px;
      flex-shrink: 0;
      stroke-width: 2.5;
      stroke: #fff3e6;
      filter: drop-shadow(0px 1px 1px #000);
    }
  </style>
</head>

<body>
  <div class="navbar">
    <div class="marca">
      <img src="/Media/logo.png" alt="logo">
      Salsagram
    </div>

    <a href="" class="nav-item active">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/>
          <path d="M3 10a2 2 0 0 1 .7-1.53l7-6a2 2 0 0 1 2.6 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        </g>
      </svg>
      Inicio
    </a>

    <a href="" class="nav-item">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="m21 21l-4.3-4.3"/><circle cx="11" cy="11" r="8"/>
        </g>
      </svg>
      Búsqueda
    </a>

    <a href="" class="nav-item">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="m16.2 7.8-1.8 5.4a2 2 0 0 1-1.3 1.3L7.8 16.2l1.8-5.4a2 2 0 0 1 1.3-1.3z"/>
          <circle cx="12" cy="12" r="10"/>
        </g>
      </svg>
      Explorar
    </a>

    <a href="" class="nav-item">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 9.4a.65.65 0 0 1 0 1.1l-4 2.35a.64.64 0 0 1-.97-.56V7.65a.64.64 0 0 1 .97-.56zM7 21h10"/>
          <rect width="20" height="14" x="2" y="3" rx="2"/>
        </g>
      </svg>
      Reels
    </a>

    <a href="" class="nav-item">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path fill="none" stroke-linecap="round" stroke-linejoin="round"
              d="M22 17a2 2 0 0 1-2 2H7a2 2 0 0 0-1.4.6L3.4 22A.7.7 0 0 1 2 21.3V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/>
      </svg>
      Mensajes
    </a>

    <a href="" class="nav-item">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15.4 15.6a7.9 6 135 1 1 6.2-6.2a4.5 3.4 135 0 0-6.2 6.2"/>
          <path d="m8.3 12.7-2.6 2.6a2.5 2.5 0 1 0-1.6 4.7a2.5 2.5 0 1 0 4 1.3l2.6-2.6"/>
        </g>
      </svg>
      Notificaciones
    </a>

    <a href="" class="nav-item">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path fill="none" stroke-linecap="round" stroke-linejoin="round"
              d="M5 12h14m-7-7v14"/>
      </svg>
      Crear
    </a>

    <a href="" class="nav-item">Perfil</a>

    <div class="spacer"></div>

    <a href="" class="nav-item">Más</a>
  </div>
</body>
</html>
