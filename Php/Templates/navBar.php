<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Sidebar</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #0f0f0f;
      color: #fff;
    }

    .navbar {
      width: 250px;
      height: 100vh;
      background: #111;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .marca {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 20px;
    }

    a.nav-item {
      padding: 10px;
      border-radius: 8px;
      cursor: pointer;
      color: #fff;
      text-decoration: none;
      transition: background 0.2s;

      display: flex;             
      align-items: center;        
      gap: 10px;                  
    }

    a.nav-item:hover {
      background: #1a1a1a;
    }

    .active {
      font-weight: bold;
    }

    .spacer {
      flex: 1;
    }

    .imagen {
      width: 30px;
      height: auto;
      margin-right: 8px;
      vertical-align: middle;
    }

    svg {
      width: 22px;       
      height: 22px;
      flex-shrink: 0;   
    }

  </style>
</head>
<body>
  <div class="navbar">
    <div class="marca">Salsagram</div>

    <!-- Iconos lucide -->
    <a href="" class="nav-item active">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></g></svg>
      Inicio
    </a>

    <a href="" class="nav-item">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="m21 21l-4.34-4.34"/><circle cx="11" cy="11" r="8"/></g></svg>
      Búsqueda
    </a>

    <a href="" class="nav-item">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="m16.24 7.76l-1.804 5.411a2 2 0 0 1-1.265 1.265L7.76 16.24l1.804-5.411a2 2 0 0 1 1.265-1.265z"/><circle cx="12" cy="12" r="10"/></g></svg>
      Explorar
    </a>

    <a href="" class="nav-item">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M15.033 9.44a.647.647 0 0 1 0 1.12l-4.065 2.352a.645.645 0 0 1-.968-.56V7.648a.645.645 0 0 1 .967-.56zM7 21h10"/><rect width="20" height="14" x="2" y="3" rx="2"/></g></svg>
      Reels
    </a>

    <a href="" class="nav-item">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/></svg>
      Mensajes
    </a>

    <a href="" class="nav-item">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M15.4 15.63a7.875 6 135 1 1 6.23-6.23a4.5 3.43 135 0 0-6.23 6.23"/><path d="m8.29 12.71l-2.6 2.6a2.5 2.5 0 1 0-1.65 4.65A2.5 2.5 0 1 0 8.7 18.3l2.59-2.59"/></g></svg>
      Notificaciones
    </a>

    <a href="" class="nav-item">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7v14"/></svg>
      Crear
    </a>

    <a href="" class="nav-item">Perfil</a>

    <div class="spacer"></div>

    <a href="" class="nav-item">Más</a>
  </div>
</body>
</html>
