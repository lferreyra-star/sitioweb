# 🌐 Portafolio Profesional — Luis Fabiano Ferreyra Alanya

Portafolio personal convertido en una **aplicación PHP profesional** con base de
datos **SQLite**, **login seguro** y **panel de administración completo** para
gestionar todo el contenido del sitio sin tocar código.

---

## ✨ Características

### Sitio público
- Diseño moderno oscuro/claro, responsive y animado (el original, mejorado).
- **Todo el contenido es dinámico**: habilidades, educación, experiencia,
  proyectos, textos y datos de contacto se leen desde la base de datos.
- Formulario de contacto con validación **en el servidor**, protección **CSRF**
  y **anti-bots** (honeypot + control de tiempo).
- Efectos: máquina de escribir, barras de progreso, reveal al hacer scroll,
  tema claro/oscuro persistente, botón de WhatsApp configurable.
- SEO: meta description, Open Graph, HTML semántico y `aria-*`.

### Panel de administración (`/admin`)
- **Dashboard** con estadísticas y últimos mensajes.
- **CRUD completo** de: proyectos, habilidades, educación y experiencia.
- **Buzón de mensajes**: leer, marcar, responder por correo y eliminar.
- **Configuración del sitio**: todos los textos, foto, WhatsApp, SEO, etc.
- **Seguridad**: cambio de contraseña y datos de la cuenta.
- Diseño propio, responsive y con confirmación antes de eliminar.

### Seguridad
| Medida | Implementación |
|---|---|
| Contraseñas | `password_hash()` con bcrypt + rehash automático |
| Fuerza bruta | Bloqueo tras 5 intentos fallidos (15 min) |
| SQL injection | Consultas 100 % preparadas (PDO) |
| XSS | Escapado obligatorio con `e()` (htmlspecialchars) |
| CSRF | Token en todos los formularios POST |
| Sesión | Cookies `HttpOnly` + `SameSite=Lax`, regeneración periódica del ID |
| Archivos | `.htaccess` bloquea `app/`, `storage/` y `*.sqlite` |
| Bots | Honeypot + tiempo mínimo de envío en el formulario |

---

## 🚀 Instalación

### Requisitos
- PHP **8.0 o superior** con la extensión **pdo_sqlite** (viene incluida por defecto).
- No hace falta instalar MySQL ni configurar nada: **la base de datos se crea sola**.

### Opción A — XAMPP (Windows)
1. Copia la carpeta del proyecto a `C:\xampp\htdocs\portafolio`.
2. Abre `http://localhost/portafolio` en el navegador.
3. Entra en `http://localhost/portafolio/login.php`.

### Opción B — Servidor de desarrollo de PHP
```bash
cd sitioweb-main
php -S localhost:8000
```
Abre `http://localhost:8000`.

### Opción C — Hosting compartido
Sube **todos los archivos** por FTP/cPanel. Funciona tal cual (Apache + PHP).

---

## 🔑 Acceso inicial

| Campo | Valor |
|---|---|
| URL | `/login.php` |
| Usuario | `admin` |
| Contraseña | `admin123` |

> ⚠️ **Cambia la contraseña inmediatamente**: *Panel → Seguridad → Cambiar contraseña.*

---

## 📁 Estructura del proyecto

```
sitioweb-main/
├── index.php            # Sitio público (dinámico)
├── contacto.php         # Procesa el formulario de contacto
├── login.php            # Acceso al panel
├── logout.php           # Cierre de sesión (POST + CSRF)
├── .htaccess            # Seguridad y enrutado (Apache)
│
├── admin/               # Panel de administración
│   ├── index.php        #   Dashboard
│   ├── proyectos.php    #   CRUD proyectos
│   ├── habilidades.php  #   CRUD habilidades
│   ├── educacion.php    #   CRUD educación
│   ├── experiencia.php  #   CRUD experiencia
│   ├── mensajes.php     #   Buzón de contacto
│   ├── sitio.php        #   Configuración general
│   ├── seguridad.php    #   Cuenta y contraseña
│   └── partials/        #   Plantillas reutilizables
│
├── app/                 # Núcleo de la aplicación (bloqueado por HTTP)
│   ├── bootstrap.php    #   Configuración, sesión, autoload
│   ├── helpers.php      #   Funciones utilitarias (e, url, csrf, flash…)
│   └── src/
│       ├── Database.php #   Conexión + esquema + datos iniciales
│       ├── Auth.php     #   Login seguro y bloqueo por intentos
│       └── Repos/       #   Repositorios (acceso a datos)
│
├── storage/             # Base de datos SQLite (bloqueada por HTTP)
│   └── portafolio.sqlite  (se crea automáticamente)
│
├── css/  (styles.css, admin.css)
├── js/   (script.js, admin.js)
└── img/  (foto.jpg)
```

---

## 🛠️ Gestión del contenido

1. Entra con tu usuario en `/login.php`.
2. **Dashboard** → vista general y accesos rápidos.
3. **Proyectos / Habilidades / Educación / Experiencia** → botón *+ Nuevo*,
   *Editar* o *Eliminar*. El orden se controla con el campo **Orden**.
4. **Mensajes** → todos los contactos del formulario, con badge de no leídos.
5. **Sitio** → edita títulos, textos del hero, "Sobre mí", WhatsApp, foto y SEO.
6. **Seguridad** → cambia tu nombre, correo y contraseña.

Cada acción muestra un mensaje de confirmación y los cambios son **inmediatos**
en el sitio público (patrón POST-Redirect-GET).

---

## 📦 Copias de seguridad

La base de datos es **un único archivo**: `storage/portafolio.sqlite`.
Cópialo periódicamente para respaldar todo el contenido.

---

## 🔧 Solución de problemas

| Problema | Solución |
|---|---|
| Error 500 | Revisa `storage/errores.log` (pon `APP_DEBUG = false` en `app/bootstrap.php` para ver errores en pantalla) |
| No puedo entrar | Comprueba usuario/contraseña; los intentos fallidos bloquean 15 min |
| El enlace "Admin" no funciona | Comprueba que tu servidor ejecuta PHP 8+ y que `storage/` tiene permisos de escritura |
| Subcarpeta (ej. `/portafolio`) | Funciona automáticamente: no hay que configurar rutas |
| Olvidé la contraseña | Borra `storage/portafolio.sqlite` y recarga el sitio: se regenerará con `admin`/`admin123` |

---

## 📄 Licencia

Proyecto personal. Todos los derechos reservados © Luis Fabiano Ferreyra Alanya.
