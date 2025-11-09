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
       <?php
          if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['DNI'])) {
              borrar_usuario($_POST['DNI']);
          }

          $usuarios = obtener_usuarios();
        ?>
        <h2 class="titulos-administrador">Lista de Usuarios</h2>
        <table border="1">
        <tr>
          <th>Nombre</th>
          <th>Apellidos</th>
          <th>DNI</th>
          <th>Email</th>
          <th>Clave</th>
          <th>Foto</th>
          <th>Rol</th>
          <th>Acciones</th>
        </tr>
        <?php foreach ($usuarios as $usuario): ?>
          <tr>
            <td><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></td>
            <td><?php echo htmlspecialchars($usuario['apellidos']); ?></td>
            <td><?php echo htmlspecialchars($usuario['DNI']); ?></td>
            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
            <td><?php echo htmlspecialchars($usuario['clave']); ?></td>
            <td><?php echo htmlspecialchars($usuario['foto']); ?></td>
            <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
            <td>
              <form method="post" action="">
                <input type="hidden" name="DNI" value="<?php echo htmlspecialchars($usuario['DNI']); ?>">
                <button type="submit">Borrar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </table>
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