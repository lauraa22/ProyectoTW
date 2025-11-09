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
      <?php
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['enviar'])) {
          list($datos, $errores) = validarDatosAdmin();

          if (empty($errores)) {
            if (obtener_rol_administrador( $datos['DNI'],'admin')) {
              echo '<h3 class="confirmacion">Rol actualizado correctamente, vuelva a iniciar sesión.</h3>';
            } else {
              echo '<h3 class="aseguro">Error al modificar el rol.</h3>';
            }
          } else {
            mostrarFormularioAdministrador($datos, $errores);
          }
        }
      } else {
        // MOSTRAR FORMULARIO POR PRIMERA VEZ
        mostrarFormularioAdministrador();
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