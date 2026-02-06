# 💃 Guía de Despliegue: Salsagram en AWS

Bienvenido a la guía paso a paso para desplegar **Salsagram** en un servidor Ubuntu. Sigue estas instrucciones al pie de la letra para tener tu red social funcionando en minutos.

---

## ☁️ PARTE 1: Crear el Servidor (AWS Console)

1.  Entra en **AWS Academy** > **Consola AWS** > **EC2**.
2.  Haz clic en **Launch Instance** (Lanzar instancia).
3.  **Configuración:**
    * **Nombre:** `Servidor-Salsagram`
    * **OS:** Ubuntu (Server 24.04 o 22.04 LTS).
    * **Tipo:** `t2.micro` o `t3.micro`.
    * **Key Pair:** `Vockey`.
    * **Network settings:** Marca ☑️ **Allow HTTPS** y ☑️ **Allow HTTP**.
4.  Haz clic en **Launch Instance**.

### 🔗 Asignar IP Estática (Elastic IP)
*Esto es obligatorio para que la dirección de tu red social no cambie.*

1.  Menú izquierdo > **Elastic IPs** > **Allocate Elastic IP address** > **Allocate**.
2.  Selecciona la IP creada > **Actions** > **Associate Elastic IP address**.
3.  Elige tu instancia `Servidor-Salsagram` y dale a **Associate**.
4.  **¡Copia esa IP!** (Ej: `54.210.x.x`). Esa es la dirección de tu red social.

---

## 💻 PARTE 2: Conectarse al Servidor

1.  Ve al apartado **Instances** (Instancias).
2.  Selecciona `Servidor-Salsagram` (estado "Running").
3.  Clic en el botón **Connect** (arriba a la derecha).
4.  Pestaña **EC2 Instance Connect** > Deja el usuario como `ubuntu`.
5.  Clic en el botón naranja **Connect**.
6.  *Se abrirá una terminal negra en tu navegador. Ya estás dentro.*

---

## ⚠️ VARIABLES IMPORTANTES (Cheat Sheet)

Para que esto funcione a la primera, hemos configurado todo con estos datos fijos. **Lee esto antes de empezar.**

| Variable | Valor |
| :--- | :--- |
| **Usuario BD** | `admin` |
| **Contraseña BD** | `admin123` |
| **Base de Datos** | `red_social` |
| **Gmail Emisor** | `juegosrarossss@gmail.com` |
| **App Password** | `fwhnkmntmvpmoeld` |
| **IP del Servidor** | *Detectada automáticamente por el código* |

---

## 🚀 PASO 1: Instalar Software Necesario

Actualizamos el servidor e instalamos Nginx, MySQL, PHP 8.3 y herramientas necesarias.

```bash
sudo apt update
sudo apt install -y nginx mysql-server php-fpm php-mysql php-curl php-gd php-mbstring php-xml php-zip git unzip curl
```

---

## 📦 PASO 2: Descargar Código e Instalar Librerías

Clonamos el repositorio y usamos Composer para instalar la librería de correos (PHPMailer). Si pide confirmación escribe `yes`.

```bash
cd /var/www
sudo rm -rf RED_SOCIAL
sudo git clone [https://github.com/aneprz/RED_SOCIAL.git](https://github.com/aneprz/RED_SOCIAL.git)
cd RED_SOCIAL
sudo curl -sS [https://getcomposer.org/installer](https://getcomposer.org/installer) | sudo php
sudo php composer.phar install
sudo php composer.phar require phpmailer/phpmailer
```

---

## 🗄️ PASO 3: Base de Datos (Corregida)

Aquí creamos la base de datos `red_social`, el usuario `admin` y las tablas necesarias.

1. Entra a MySQL:
```bash
sudo mysql
```

2. **Copia y pega este bloque entero dentro de MySQL:**

```sql
DROP DATABASE IF EXISTS red_social;
CREATE DATABASE red_social;
CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED WITH mysql_native_password BY 'admin123';
GRANT ALL PRIVILEGES ON red_social.* TO 'admin'@'localhost';
FLUSH PRIVILEGES;

USE red_social;

-- 1. USUARIOS
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    bio TEXT,
    foto_perfil VARCHAR(255),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    privacidad BOOLEAN DEFAULT FALSE,
    token_confirmacion VARCHAR(255) DEFAULT NULL,
    confirmado TINYINT(1) DEFAULT 0
);

-- 2. SEGUIDORES
CREATE TABLE seguidores (
    seguidor_id INT,
    seguido_id INT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (seguidor_id, seguido_id),
    FOREIGN KEY (seguidor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (seguido_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 3. SOLICITUDES
CREATE TABLE solicitudes_seguimiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitante_id INT,
    receptor_id INT,
    estado VARCHAR(20) DEFAULT 'pendiente',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (solicitante_id, receptor_id),
    FOREIGN KEY (solicitante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (receptor_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 4. PUBLICACIONES
CREATE TABLE publicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    imagen_url VARCHAR(255) NOT NULL,
    pie_foto TEXT,
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 5. LIKES
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    usuario_id INT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES publicaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 6. COMENTARIOS
CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    usuario_id INT,
    texto TEXT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES publicaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 7. CHATS Y MENSAJES
CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    es_grupo BOOLEAN DEFAULT FALSE,
    nombre_grupo VARCHAR(100),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE usuarios_chat (
    chat_id INT,
    usuario_id INT,
    PRIMARY KEY (chat_id, usuario_id),
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT,
    usuario_id INT,
    texto TEXT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    leido BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 8. NOTIFICACIONES
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_emisor INT,
    tipo VARCHAR(50),
    id_post INT,
    leida BOOLEAN DEFAULT FALSE,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_emisor) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_post) REFERENCES publicaciones(id) ON DELETE SET NULL
);

EXIT;
```

---

## 🔌 PASO 4: Conectar PHP con la Base de Datos

Creamos el archivo de conexión con las credenciales nuevas.

1. Abre el archivo:
```bash
sudo nano /var/www/RED_SOCIAL/BD/conexiones.php
```

2. **Borra todo y pega esto:**

```php
<?php
if (!isset($pdo) && !isset($conexion)) {

    $host = "localhost";
    $db   = "red_social";
    $user = "admin";
    $pass = "admin123";
    $charset = "utf8mb4";

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        die("Error de conexión PDO: " . $e->getMessage());
    }

    $conexion = mysqli_connect($host, $user, $pass, $db);
    if (!$conexion) {
        die("Error de conexión MySQLi: " . mysqli_connect_error());
    }
}

if (!function_exists('obtenerFotoPerfil')) {
    function obtenerFotoPerfil($usuarioId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id = :id");
            $stmt->execute(['id' => $usuarioId]);
            $row = $stmt->fetch();
            if ($row && !empty($row['foto_perfil'])) {
                return $row['foto_perfil'];
            } else {
                return '/Media/foto_default.png';
            }
        } catch (Exception $e) {
            return '/Media/foto_default.png';
        }
    }
}
?>
```
*Para guardar en Nano: `CTRL + O`, `Enter`, `CTRL + X`.*

---

## 🌐 PASO 5: Configuración de Nginx

Configuramos el servidor web para que entienda PHP.

1. Abre la configuración:
```bash
sudo nano /etc/nginx/sites-available/default
```

2. **Borra todo y pega esto:**

```nginx
server {
    listen 80;
    listen [::]:80;

    root /var/www/RED_SOCIAL;
    index index.php index.html index.htm;

    server_name _;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

---

## 📂 PASO 6: Crear Carpetas y Permisos

Como Git no sube carpetas vacías, las creamos a mano y damos permisos para subir fotos.

```bash
# Crear carpetas faltantes
sudo mkdir -p /var/www/RED_SOCIAL/Php/Crear/uploads
sudo mkdir -p /var/www/RED_SOCIAL/Php/Usuarios/fotosDePerfil

# Asignar dueño
sudo chown -R ubuntu:www-data /var/www/RED_SOCIAL

# Permisos generales
sudo chmod -R 755 /var/www/RED_SOCIAL

# Permisos de escritura para uploads
sudo chmod -R 775 /var/www/RED_SOCIAL/Php/Crear/uploads
sudo chmod -R 775 /var/www/RED_SOCIAL/Php/Usuarios/fotosDePerfil
```

---

## 📧 PASO 7: Corregir el Registro y Email

Configuramos el registro para que detecte tu IP automáticamente al enviar el correo.

1. Abre el archivo:
```bash
sudo nano /var/www/RED_SOCIAL/Php/Sesiones/procesamientos/procesar_registro_sesion.php
```

2. **Borra todo y pega esto:**

```php
<?php
session_start();
require '../../../vendor/autoload.php'; 
require '../../../BD/conexiones.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function volverConError($mensaje) {
    $_SESSION['error'] = $mensaje;
    header("Location: ../registro_sesion.php"); 
    exit();
}

$nombreUsu = trim($_POST['nombre_usuario'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['contraseña'] ?? '';
$pass2 = $_POST['repetirContraseña'] ?? '';
$fechaActual = date("Y-m-d H:i:s");

if ($nombreUsu === '' || $email === '' || $pass === '' || $pass2 === '') { volverConError("Todos los campos son obligatorios"); }
if ($pass !== $pass2) { volverConError("Las contraseñas no coinciden"); }
if (strlen($pass) < 8) { volverConError("La contraseña debe tener al menos 8 caracteres"); }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { volverConError("Correo electrónico inválido"); }

$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $nombreUsu);
$stmt->execute();
if ($stmt->fetch()) { volverConError("El nombre de usuario ya existe"); }
$stmt->close();

$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->fetch()) { volverConError("El correo electrónico ya está en uso"); }
$stmt->close();

$hash = password_hash($pass, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(16));

$stmt = $conexion->prepare("INSERT INTO usuarios (username, email, password_hash, fecha_registro, token_confirmacion, confirmado) VALUES (?, ?, ?, ?, ?, 0)");
$stmt->bind_param("sssss", $nombreUsu, $email, $hash, $fechaActual, $token);

if ($stmt->execute()) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'juegosrarossss@gmail.com';  
        $mail->Password   = 'fwhnkmntmvpmoeld';         
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('no-reply@salsagram.com', 'Salsagram');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Confirma tu registro en Salsagram 💃';
        
        // IP AUTOMÁTICA
        $dominio_actual = $_SERVER['HTTP_HOST'];
        $link = "http://$dominio_actual/Php/Sesiones/confirmar.php?email=$email&token=$token";

        $mail->Body = "<div style='text-align:center;'>
            <h2>¡Bienvenido, $nombreUsu!</h2>
            <p>Haz clic abajo para activar tu cuenta:</p>
            <a href='$link' style='background:#d63384;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>CONFIRMAR CUENTA</a>
        </div>";

        $mail->send();
        $_SESSION['success'] = "Registro correcto. Revisa tu correo ($email) para activar la cuenta.";
        header("Location: ../registro_sesion.php"); 
        exit();

    } catch (Exception $e) {
        volverConError("Error enviando email: " . $mail->ErrorInfo);
    }
} else {
    volverConError("Error interno BD");
}
$stmt->close();
$conexion->close();
?>
```

---

## ✅ PASO 8: Reiniciar y Listo

Aplicamos los cambios y reiniciamos los servicios.

```bash
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
```

Utiliza este comando para saber tu IP pública:

```bash
curl ifconfig.me
```

**¡Ya lo tienes!** 💃
