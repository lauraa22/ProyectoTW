<?php
session_start();
require_once 'funciones.php';
include_once 'conexion.php';
include_once 'configuracion.php'; 

    
    $conexion = conectar();

    $id = 1;
    $sql = "SELECT * FROM sitio_web WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt && $fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $nombre_centro = htmlspecialchars($fila['nombre_centro']);
        $logo = htmlspecialchars($fila['logo']); 
    } else {
        $nombre_centro = $descripcion_centro = $hora_apertura = $hora_cierre = $logo = '';
    }
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reserva de Aulas - Inicio</title>
  <link rel="stylesheet" href="estilos.css">

</head>

<body>
   <header>
      <div class="header-main">
        <img src="imagenes/etsiit.png" alt="Logo ETSIIT" width="200">

        <h1><?php echo $nombre_centro; ?></h1>
        <img src="imagenes/UGR-Logo.png" alt="Logo UGR" width="200">
      </div>
      <div class="header-right">
        <a href="#"><img src="imagenes/facebook.png" alt="Facebook"></a>
        <a href="#"><img src="imagenes/Twitter.png" alt="Twitter"></a>
        <a href="#"><img src="imagenes/instagram.png" alt="Instagram"></a>
      </div> 
    </header>

    <nav>
      <div class="nav-left">
        <?php generarMenu(); ?>
      </div> 
    </nav>
  <div class="container">
    <main>
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
        <ul class="opAdmin">
            <li><a href="listausuarios.php">Lista de Usuarios</a></li>
            <li><a href="añadirUsuarios.php">Añadir Usuario</a></li>
            <li><a href="editardatosusuarios.php">Editar Datos Usuarios</a></li>
            <li><a href="borrarUsuarios.php">Borrar Usuario</a></li>
            <li><a href="modificarWeb.php">Modificar Web</a></li>
            <li><a href="listasalas.php">Lista de Salas</a></li>
            <li><a href="añadirSalas.php">Añadir Sala</a></li>
            <li><a href="modificarSalas.php">Editar Datos Salas</a></li>
            <li><a href="borrarSalas.php">Borrar Salas</a></li>
            <li><a href="listalogs.php">Ver logs</a></li>
        </ul>
        <?php endif; ?>
    <?php
    
        if ($_SESSION['rol'] === 'registrado') {
            $DNI = $_SESSION['DNI'];
        } elseif (isset($_POST['DNI_editar'])) {
            $DNI = $_POST['DNI_editar'];
        } elseif (isset($_GET['DNI_editar'])) {
            $DNI = $_GET['DNI_editar'];
        } else {
            // No hay DNI, redirigir al listado
            header("Location: modificarusuarios.php");
            exit;
        }
    
        $datos = obtener_datos_usuario_por_dni($DNI);

        // Verificar si se enviaron datos del formulario de modificación
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Obtener los datos del formulario
            $email = $_POST['correo'] ?? '';
            $clave = $_POST['contraseña'] ?? '';
            $foto = $_POST['foto'] ?? '';
            $rol = $_POST['rol'] ?? 'registrado';

            $errores = [];
            if (empty($_POST['correo'])) {
                $errores['correo'] = 'Debe indiciar un email de contacto';
            } elseif (!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
                $errores['correo'] = 'El email no es válido';
            } else {
                $datos['correo'] = $_POST['correo'];
            }

        
            if (empty($_POST['contraseña']) || empty($_POST['repite_contraseña'])) {
                $errores['contraseña'] = 'Escriba su clave de acceso';
            } elseif ($_POST['contraseña'] !== $_POST['repite_contraseña']) {
                $errores['contraseña'] = 'Deben coincidir ambas claves';
            } elseif (!preg_match('/^\w{4,}$/', $_POST['contraseña'])) {
                $errores['contraseña'] = 'La clave debe contener al menos 4 caracteres alfanuméricos';
            } else {
                $datos['contraseña'] = $_POST['contraseña'];
            }

            if (isset($_FILES['foto'])) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $foto = $_FILES['foto'];
            
                if ($foto['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $foto['tmp_name'];
                    $fileMimeType = mime_content_type($fileTmpPath);
            
                    if (!in_array($fileMimeType, $allowedMimeTypes)) {
                        $errores['foto'] = 'La foto debe estar en formato JPEG, PNG o GIF.';
                    } else {
                        $datos['foto'] = $foto['name']; 
                    }
            
                }
            
            } else {
                $errores['foto'] = 'Debe cargar una foto.';
            }

            if (isset($_POST['rol'])) {
                $datos['rol'] = is_array($_POST['rol']) ? $_POST['rol'][0] : $_POST['rol'];
                $roles_permitidos = ['registrado', 'admin'];
                if (!in_array($datos['rol'], $roles_permitidos)) {
                    $errores['rol'] = 'Rol no válido.';
                }
            } else {
                $datos['rol'] = 'registrado';
            }

            if (empty($errores)) {
                $nombreFoto = $datos['foto'] ?? '';
                if (modificar_datos_usuario($DNI, $email, $clave, $nombreFoto, $rol)) {
                    echo '<h3 class="confirmacion">Usuario modificado correctamente.</h3>';
                } else {
                    echo '<h3 class="error">Error al modificar el usuario.</h3>';
                }
                // Redireccionar según el rol del usuario
                if ($_SESSION['rol'] === 'admin') {
                    header('Location: modificarusuarios.php');
                } /*else {
                    //header('Location: editardatosusuarios.php');
                }*/
                exit;
            } else {
                // Mostrar el formulario con los errores
                mostrarFormularioEdicion($_POST, $errores);
            }
        } else {
            // Mostrar el formulario con los datos del usuario
            mostrarFormularioEdicion($datos);
        }
    ?>
    </main>
  </div>

  <footer>
    <ul>
      <li>Autores: Laura Guirao Torrente y Marcos Ramírez Heras</li>
      <li><a href="documentacion.pdf">Documentación</a></li>
      <li><a href="">Fichero restaurar BBDD</a></li>
    </ul>
  </footer>
</body>

</html>