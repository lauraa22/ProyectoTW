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
        <aside>
            <h3>Aulas</h3>
            <ul>
                <?php
                $sql = "SELECT nombre_sala FROM salas ORDER BY nombre_sala ASC";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();

                while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $nombre_sala = htmlspecialchars($fila['nombre_sala']);
                    echo "<li>$nombre_sala</li>";
                }
                ?>
                <?php
                    // Total de aulas
                    $stmtTotal = $conexion->query("SELECT COUNT(*) FROM salas");
                    $totalAulas = $stmtTotal->fetchColumn();

                    // Capacidad total
                    $stmtCapacidad = $conexion->query("SELECT SUM(capacidad) FROM salas");
                    $capacidadTotal = $stmtCapacidad->fetchColumn();

                    // Reservas de hoy 
                    $hoy = date("Y-m-d");
                    $stmtReservas = $conexion->prepare("SELECT COUNT(*) FROM reservas WHERE fecha_reserva = :hoy");
                    $stmtReservas->bindParam(':hoy', $hoy);
                    $stmtReservas->execute();
                    $reservasHoy = $stmtReservas->fetchColumn();
                ?>

                <li><p>Número total de aulas: <?php echo $totalAulas; ?></p></li>
                <li><p>Capacidad total: <?php echo $capacidadTotal; ?> personas</p></li>
                <li><p>Reservas de hoy: <?php echo $reservasHoy; ?></p></li>

            </ul>
         </aside>
         
         <main>

        <h1>Salas Disponibles</h1>
        <?php
            // Obtener todas las salas ordenadas por nombre
            $sqlSalas = "SELECT * FROM salas ORDER BY nombre_sala ASC";
            $stmtSalas = $conexion->prepare($sqlSalas);
            $stmtSalas->execute();

            while ($sala = $stmtSalas->fetch(PDO::FETCH_ASSOC)) {
                $nombre_sala = htmlspecialchars($sala['nombre_sala']);
                $ubicacion = htmlspecialchars($sala['ubicacion']);
                $capacidad = htmlspecialchars($sala['capacidad']);
                $descripcion_sala = htmlspecialchars($sala['descripcion_sala']);
                $reservable = $sala['reservable'] ? '1' : '0';

                echo "<section class='sala'>";
                echo "<h2>$nombre_sala</h2>";
                echo "<p><strong>Ubicación:</strong> $ubicacion</p>";
                echo "<p><strong>Número de puestos disponibles:</strong> $capacidad</p>";
                echo "<p><strong>Descripción:</strong> $descripcion_sala</p>";
                $reservable = $sala['reservable'] ? 'Sí' : 'No';
                echo "<p><strong>Reservable:</strong> $reservable</p>";
            
                // Obtener fotos asociadas
                $sqlFotos = "SELECT id_imagen_sala FROM imagenes_salas WHERE nombre_sala = :nombre_sala";
                $stmtFotos = $conexion->prepare($sqlFotos);
                $stmtFotos->bindParam(':nombre_sala', $sala['nombre_sala'], PDO::PARAM_STR);            
                $stmtFotos->execute();

                $fotos = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);
                if (count($fotos) > 0) {
                    echo "<div class='galeria'>";
                    foreach ($fotos as $foto) {
                        $id_imagen = $foto['id_imagen_sala'];

                        echo "<img src='ver_imagen.php?id_imagen_sala=$id_imagen' alt='Foto de $nombre_sala' width='300' style='margin:10px'>"; 
                    }
                    echo "</div>";
                } else {
                    echo "<p><em>No hay fotografías disponibles para esta sala.</em></p>";
                }

                echo "</section>";


                // Mostrar comentarios
                $sqlComentarios = "SELECT DNI, texto, fecha_comentario FROM comentarios WHERE nombre_sala = :nombre_sala ORDER BY fecha_comentario DESC";
                $stmtComentarios = $conexion->prepare($sqlComentarios);
                $stmtComentarios->bindParam(':nombre_sala', $sala['nombre_sala'], PDO::PARAM_STR);
                $stmtComentarios->execute();

                $comentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);
                echo "<div class='comentarios'>";
                echo "<h3>Comentarios:</h3>";

                if (count($comentarios) > 0) {
                    foreach ($comentarios as $comentario) {
                        $usuario = htmlspecialchars($comentario['DNI']);
                        $texto = htmlspecialchars($comentario['texto']);
                        $fecha = htmlspecialchars($comentario['fecha_comentario']);
                        echo "<div class='comentario'>";
                        echo "<p><strong>$usuario</strong> ($fecha):</p>";
                        echo "<p>$texto</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p><em>No hay comentarios todavía.</em></p>";
                }


                if (isset($_SESSION['rol']) && ($_SESSION['rol'] === 'registrado' || $_SESSION['rol'] === 'admin')) {              
                    echo "<form method='post' action='procesar_comentario.php'>";
                    echo "<input type='hidden' name='nombre_sala' value='" . htmlspecialchars($sala['nombre_sala']) . "'>";
                    echo "<textarea name='comentario' required placeholder='Escribe tu comentario aquí...' rows='3' cols='50'></textarea><br>";
                    echo "<button type='submit'>Enviar comentario</button>";
                    echo "</form>";
                } else {
                    echo "<p><em>Registrate para dejar un comentario.</em></p>";
                }
                echo "</div>";

            }
        ?>

    
        </main>
    </div>

    <footer>
        <ul>
            <li>Autores: Laura Guirao Torrente y Marcos Ramírez Heras</li>
            <li><a href="documentacion.pdf">Documentación</a></li>
            <li><a href="">Fichero restaurar BBDD</a></li>
    </footer>
</body>
</html>
