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
        } elseif ( isset($_POST['nombre_sala'])){
            $nombre_sala = $_POST['nombre_sala'];
        } elseif (isset($_GET['nombre_sala'])) {
            $nombre_sala = $_GET['nombre_sala'];
        } else {
            // No hay DNI, redirigir al listado
            header("Location: modificarSalas.php");
            exit;
        }
    
        $datos = obtener_sala_por_nombre_sala($nombre_sala);

        // Verificar si se enviaron datos del formulario de modificación
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nombre_sala = $_POST['nombre_sala'] ?? '';
            $ubicacion = $_POST['ubicacion'] ?? '';
            $capacidad = $_POST['capacidad'] ?? '';
            $reservable = $_POST['reservable'] ?? '';
            $descripcion_sala = $_POST['descripcion_sala'] ?? '';
            $imagenes = $_FILES['imagenes'] ?? null;
            

            $errores = [];
            if (empty($_POST['nombre_sala'])) {
                $errores['nombre_sala'] = 'Debe poner un nombre para la sala';
            } else {
                $datos['nombre_sala'] = $_POST['nombre_sala'];
            }

            if (empty($_POST['descripcion_sala'])) {
                $errores['descripcion_sala'] = 'Debe poner una descripcion para el la sala';
            } else {
                $datos['descripcion_sala'] = $_POST['descripcion_sala'];
            }

            if (empty($_POST['ubicacion'])) {
                $errores['ubicacion'] = 'Debe poner una hora de apertura del centro';
            } else {
                $datos['ubicacion'] = $_POST['ubicacion'];
            }

            if (isset($_POST['reservable']) && ($_POST['reservable'] === '0' || $_POST['reservable'] === '1')) {
                $datos['reservable'] = $_POST['reservable'];
            } else {
                $errores['reservable'] = 'Debe indicar si la sala es reservable (Sí o No).';
            }
            
            if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $imagenes = $_FILES['imagenes'];
            
                for ($i = 0; $i < count($imagenes['name']); $i++) {
                    if ($imagenes['error'][$i] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $imagenes['tmp_name'][$i];
                        $fileMimeType = mime_content_type($fileTmpPath);
            
                        if (!in_array($fileMimeType, $allowedMimeTypes)) {
                            $errores['imagenes'][$i] = 'El archivo debe estar en formato JPEG, PNG o GIF.';
                        } else {
                            $contenidoBinario = file_get_contents($fileTmpPath);
                            $nombreArchivo = basename($imagenes['name'][$i]);
            
                            if (!insertar_imagen_sala($id_imagen_sala, $nombre_sala, $nombre_imagen, $contenido, $tipo)) {
                                $errores['imagenes'][$i] = 'Error al guardar la imagen en la base de datos.';
                            }
                        }
                    } else {
                        $errores['imagenes'][$i] = 'Error en la subida del archivo.';
                    }
                }
            }
            

            if (empty($errores)) {
                if (modificar_datos_sala($nombre_sala, $ubicacion, $capacidad, $descripcion_sala, $reservable)) {
                    echo '<h3 class="confirmacion">Datos de la sala modificados correctamente.</h3>';
                } else {
                    echo '<h3 class="error">Error al modificar el los datos.</h3>';
                }
                // Redireccionar según el rol del usuario
                if ($_SESSION['rol'] === 'admin') {
                    header('Location: modificarSalas.php');
                } 
                exit;
            } else {
                // Mostrar el formulario con los errores
                mostrarFormularioEdicionSalas($_POST, $errores);
            }
        } else {
            // Mostrar el formulario con los datos del usuario
            mostrarFormularioEdicionSalas($datos);
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