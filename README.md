<h1 align="center"> ReservasAulas</h1>

**ReservasAulas** es una aplicación web desarrollada en **PHP**, **JavaScript** y **MySQL** que permite gestionar la **reserva de aulas y espacios** dentro de un centro educativo.  
El sistema incluye autenticación de usuarios, administración de salas, control de reservas y un panel para gestionar la información del centro.


## Características principales

- **Gestión de usuarios:** registro, inicio de sesión, modificación de datos y roles (`admin` / `registrado`).
- **Gestión de aulas:** alta, edición y eliminación de salas con sus características (capacidad, ubicación, fotos...).
- **Sistema de reservas:** permite registrar, consultar y eliminar reservas de aulas.
- **Panel de administración:** interfaz para editar los datos del centro (nombre, horario, descripción, logotipo...).
- **Soporte multimedia:** subida de imágenes y visualización de fotos asociadas a las salas.
- **Roles y permisos:** los administradores pueden gestionar usuarios y aulas; los usuarios registrados pueden realizar reservas.
- **JavaScript dinámico:** validación de formularios, interacción sin recargar la página y mejora de la experiencia de usuario.
- **Interfaz responsive:** diseño adaptable a distintas pantallas, con HTML5, CSS3 y buenas prácticas de usabilidad.



## Tecnologías utilizadas

- **Backend:** PHP 8  
- **Frontend:** JavaScript, HTML5, CSS3, Bootstrap  
- **Base de datos:** MySQL / MariaDB  
- **Servidor local:** Apache (XAMPP o LAMP)  
- **Conexión segura:** PDO (PHP Data Objects)


## ⚙️ Instalación y configuración

### 1. Clonar el repositorio

git clone https://github.com/<tu-usuario>/ReservasAulas.git 

### 2. Mover el proyecto al servidor local

En Windows (XAMPP):
Copia la carpeta dentro de C:\xampp\htdocs\ReservasAulas

En Linux (LAMP):
Mueve la carpeta a /var/www/html/ReservasAulas

### 3. Configurar la base de datos

El proyecto ya incluye las consultas de creación de tablas en el archivo conexion.php.
Si la base de datos ya fue configurada, no es necesario volver a crearla manualmente.

- Las credenciales y usuarios de acceso se indican en el documento PDF adjunto incluido con el proyecto.


### 4. Ejecutar la aplicación

Abre tu navegador y entra en:

http://localhost/ReservasAulas


## Estructura del Proyecto

| Archivo / Carpeta | Tipo / Función | Descripción |
|--------------------|----------------|--------------|
| `index.php` | Página principal | Muestra la información del centro y acceso al sistema |
| `conexion.php` | Backend / Base de datos | Gestiona la conexión y crea las tablas automáticamente |
| `configuracion.php` | Configuración | Define los parámetros de conexión a la base de datos |
| `funciones.php` | Funciones auxiliares | Contiene formularios, validaciones y operaciones de consulta |
| `aulas.php` | Módulo de gestión | Permite crear, editar y eliminar salas |
| `reservas.php` | Módulo de reservas | Controla la gestión y visualización de reservas |
| `admin.php` | Panel de administración | Interfaz para modificar datos del centro y gestionar usuarios |
| `js/` | Scripts JavaScript | Validaciones y funciones dinámicas del cliente |
| `estilos.css` | Hoja de estilos | Define la apariencia general del sitio |
| `imagenes/` | Recursos gráficos | Logotipos, fotos de aulas y material multimedia |
| `documentacion.pdf` | Documento de referencia | Incluye credenciales y roles de usuario |
| `README.md` | Documentación | Descripción general e instrucciones del proyecto |

## Autores
Proyecto desarrollado como práctica universitaria por:

Laura Guirao Torrente
Marcos Ramírez Heras

Universidad de Granada – Escuela Técnica Superior de Ingenierías Informática y de Telecomunicación (ETSIIT)

## Nota final
Este proyecto forma parte de la asignatura de Tecnologías Web.
Integra programación PHP (servidor), JavaScript (cliente) y una base de datos MySQL, sirviendo como ejemplo completo de aplicación web con autenticación, gestión de usuarios, salas y reservas.

⚙️ Los datos de conexión y usuarios administradores se encuentran en el documento PDF adjunto, no en este repositorio público.

