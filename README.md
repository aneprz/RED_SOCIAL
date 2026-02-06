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
