<?php
    session_start(); 
    include_once 'funciones.php'; 
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
            $descripcion_centro = htmlspecialchars($fila['descripcion_centro']);
            $hora_apertura = htmlspecialchars($fila['hora_apertura']);
            $hora_cierre = htmlspecialchars($fila['hora_cierre']);
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
            <h3>Instalaciones y servicios</h3>
            <ul>
                <li><a href="#Biblioteca">Biblioteca</a></li>
                <li><a href="#Zonaexterior">Zona exterior</a></li>
                <li><a href="#Cafeteria">Cafetería y comedor</a></li>
                <li><a href="#Conserjeria">Conserjería</a></li>
                <li><a href="#Secretaria">Secretaría</a></li>
                <li><a href="#Cesion_espacios">Cesión de espacios</a></li>
                <?php
                    // Total de aulas
                    $stmtTotal = $conexion->query("SELECT COUNT(*) FROM salas");
                    $totalAulas = $stmtTotal->fetchColumn();

                    // Capacidad total
                    $stmtCapacidad = $conexion->query("SELECT SUM(capacidad) FROM salas");
                    $capacidadTotal = $stmtCapacidad->fetchColumn();

                    // Reservas de hoy (asumiendo tabla 'reservas' con campo 'fecha')
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
            <section class="descripcion">
                <h2>DESCRIPCIÓN DEL CENTRO</h2>
                <p><?php echo $descripcion_centro; ?></p>
                <p>El horario de apertura al público es: de lunes a viernes de <?php echo substr($hora_apertura, 0, 5); ?> a <?php echo substr($hora_cierre, 0, 5); ?> horas ininterrumpidamente.</p>
                <p>El edificio de la ETSIIT se encuentra en: </p>
                <ul>
                    <li>Calle Periodista Daniel Saucedo Aranda s/n</li>
                    <li>E-18071 (Granada-Spain)</li>
                </ul>
                <img src="imagenes/universidad_entrada.jpg" alt="Imagen de la entrada del centro"  width="600">
            </section>

            <section>
                <h2>Instalaciones y servicios</h2>
                <ul class="instalaciones">
                    <li>
                        <h4 id="Biblioteca"> Biblioteca</h4>
                        <div>
                          <img src="imagenes/biblioteca.jpg" alt="Biblioteca">
                        </div>
                    </li>
                    <li>
                        <h4 id="Zonaexterior">  Zona exterior</h4>
                        <div>
                            <p>La ETSIIT dispone de una zona exterior con jardín, mesas y bancos. Además dispone de un aparcamiento de bicicletas en la entrada del edificio.</p>
                            <img src="imagenes/exterior_universidad.jpg" alt="Imagen de la zona exterior del centro" width="500" height="400">
                        </div>
                    </li>
                    <li>
                        <h4 id="Cafeteria"> Cafetería y comedor</h4>
                        <div>
                            <p>La ETSIIT cuenta con una cafetería y un comedor universitario, ambos situados en la planta baja del edificio principal.</p>
                            <img src="imagenes/cafeteria.jpeg" alt="Imagen de la cafeteria del centro" width="400">
                            <img src="imagenes/comedor.jpeg" alt="Imagen del comedor del centro" width="400">
                        </div>
                    </li>
                    <li>
                        <h4 id="Conserjeria"> Conserjería</h4>
                        <div>
                            <p>Horarios: de lunes a viernes de 8:00 a 22:00.</p>
                        </div>

                    </li>
                    <li><h4 id="Secretaria"> Secretaría</h4>
                    
                        <div>
                            <p>El horario de atención al público es de 9 a 14 horas, de lunes a viernes.</p>
                            <p>Puedes pedir tu cita del siguiente modo:</p>
                            <ul>
                                <li>Presencialmente: En la máquina expendedora de la Secretaría.</li>
                                <li>Online: Cita previa a través de <a href="https://ciges2.ugr.es/">CIGES</a> o desde tu móvil con Android.</li>
                            </ul>
                            <img src="imagenes/secretaria.jpeg" alt="Imagen de la secretaría del centro" width="500">
                        </div>
                    </li>
                    <li><h4 id="Cesion_espacios"> Cesión de espacios</h4>
                        <div>
                        <p>La normativa y precios del alquiler de aulas de informática, aulas de teoría, aulas de prácticas, salas de reuniones, salas de conferencias, salas de trabajo, etc. se puede consultar en los presupuestos de la Universidad de Granada.</p>
                        </div>
                    </li>
                </ul>
            </section>

            <section>
                <h2>Eventos de Interés</h2>
                <ul>
                    <li><a href="https://etsiit.ugr.es/la-escuela/actividades/dia-escuela">El Día de la Escuela</a></li>
                    <li><a href="https://etsiit.ugr.es/la-escuela/actividades/desafio-tecnologico">Desafío Tecnológico</a></li>
                    <li><a href="https://etsiit.ugr.es/la-escuela/noticias/charla">Charlas y talleres</a></li>
                    <li><a href="https://etsiit.ugr.es/la-escuela/actividades/desafio-tecnologico-jr-2122">Desafío Tecnologico Jr.</a></li>
                    <li><a href="https://etsiit.ugr.es/la-escuela/actividades/olimpiadainformatica">Olimpiada Informatica Granada</a></li>
                    <li><a href="https://etsiit.ugr.es/la-escuela/noticias/convocatoria-becas-educacion-curso-20252026">Convocatoria Becas Educacion Curso 2025/2026</a></li>
                </ul>
            </section>
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
