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


    // Verificar si el usuario está logueado
    if (!isset($_SESSION['DNI'])) {
        header("Location: iniciarSesion.php");
        exit();
    }

    $DNI = $_SESSION['DNI'];

    $rol = $_SESSION['rol'];

    if ($rol === 'admin') {
        $sql = "SELECT r.id_reserva, r.nombre_sala, r.motivo, r.fecha_reserva, r.hora_inicio, r.hora_fin, u.nombre_usuario
                FROM reservas r
                JOIN usuarios u ON r.DNI = u.DNI
                ORDER BY r.fecha_reserva DESC, r.hora_inicio ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
    } else {
        $sql = "SELECT r.id_reserva, r.nombre_sala, r.motivo, r.fecha_reserva, r.hora_inicio, r.hora_fin
                FROM reservas r
                WHERE r.DNI = :DNI
                ORDER BY r.fecha_reserva DESC, r.hora_inicio ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['DNI' => $DNI]);
    }
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <div class="titulo-reservas">
                <h1>Lista de reservas</h1>
            </div>
            
            <div class="mis_reservas">

                <?php if (count($resultado) > 0): ?>
                    <div class="reservas-lista">
                        <?php foreach ($resultado as $reserva): ?>
                            <div class="reserva">
                                <h2><?= htmlspecialchars($reserva['nombre_sala']) ?></h2>
                                <?php if ($rol === 'admin'): ?>
                                    <p><strong>Usuario:</strong> <?= htmlspecialchars($reserva['nombre_usuario']) ?></p>
                                <?php endif; ?>
                                <p><strong>Motivo:</strong> <?= htmlspecialchars($reserva['motivo']) ?></p>
                                <p><strong>Fecha:</strong> <?= htmlspecialchars($reserva['fecha_reserva']) ?></p>
                                <p><strong>Hora:</strong> <?= htmlspecialchars($reserva['hora_inicio']) ?> - <?= htmlspecialchars($reserva['hora_fin']) ?></p>
                                <form action="cancelar_reserva.php" method="post">
                                    <input type="hidden" name="reserva_id" value="<?= $reserva['id_reserva'] ?>">
                                    <button type="submit">Cancelar</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No tienes reservas realizadas.</p>
                <?php endif; ?>
            </div>
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
