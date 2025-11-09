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
        $nombre_centro = $logo = '';
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
            header("Location: index.php");
        }
    
        $datos = obtener_datos_web();

        // Verificar si se enviaron datos del formulario de modificación
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Obtener los datos del formulario
            $nombre_centro = $_POST['nombre_centro'] ?? '';
            $logo = $_POST['logo'] ?? '';
            $descripcion_centro = $_POST['descripcion_centro'] ?? '';
            $hora_apertura = $_POST['hora_apertura'] ?? '';
            $hora_cierre = $_POST['hora_cierre'] ?? '';
            

            $errores = [];
            if (empty($_POST['nombre_centro'])) {
                $errores['nombre_centro'] = 'Debe poner un nombre para el centro';
            } else {
                $datos['nombre_centro'] = $_POST['nombre_centro'];
            }

            if (empty($_POST['descripcion_centro'])) {
                $errores['descripcion_centro'] = 'Debe poner una descripcion para el centro';
            } else {
                $datos['descripcion_centro'] = $_POST['descripcion_centro'];
            }

            if (empty($_POST['hora_apertura'])) {
                $errores['hora_apertura'] = 'Debe poner una hora de apertura del centro';
            } else {
                $datos['hora_apertura'] = $_POST['hora_apertura'];
            }

            if (empty($_POST['hora_cierre'])) {
                $errores['hora_cierre'] = 'Debe poner una hora de cierre del centro';
            } else {
                $datos['hora_cierre'] = $_POST['hora_cierre'];
            }
            

            if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $logo = $_FILES['logo'];
            
                if ($logo['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $logo['tmp_name'];
                    $fileMimeType = mime_content_type($fileTmpPath);
            
                    if (!in_array($fileMimeType, $allowedMimeTypes)) {
                        $errores['logo'] = 'El logo debe estar en formato JPEG, PNG o GIF.';
                    } else {
                        $nombreArchivo = basename($logo['name']);
                        $tipologo = $logo['tipo'];
                        $contenidologo = $logo['contenido'];
                        $sql = "INSERT INTO imagenes_salas (nombre_sala, nombre_imagen, tipo, contenido) VALUES (:web, :logo, :$tipologo, :$contenidologo)";
                        $stmt = $conexion->prepare($sql);
                        $stmt->bindParam(':logo', $logo, PDO::PARAM_STR);
                        
                        if (move_uploaded_file($fileTmpPath, $rutaDestino)) {
                            $datos['logo'] = $rutaDestino;  // Guardar ruta completa para la base de datos
                        } else {
                            $errores['logo'] = 'Error al guardar la imagen.';
                        }
                    }
                }
            } else {
                $datos['logo'] = $datos['logo'] ?? '';
            }
            

            if (empty($errores)) {
                $nombreCentro = $datos['nombre_centro'] ?? '';
                if (modificar_datos_centro($nombreCentro, $descripcion_centro, $hora_apertura, $hora_cierre,$logo)) {
                    echo '<h3 class="confirmacion">Datos del centro modificados correctamente.</h3>';
                } else {
                    echo '<h3 class="error">Error al modificar el los datos.</h3>';
                }
                
            } else {
                // Mostrar el formulario con los errores
                mostrarFormularioEdicionCentro($_POST, $errores);
            }
        } else {
            // Mostrar el formulario con los datos del usuario
            mostrarFormularioEdicionCentro($datos);
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