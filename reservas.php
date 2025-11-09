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
        $hora_apertura = htmlspecialchars($fila['hora_apertura']);
        $hora_cierre = htmlspecialchars($fila['hora_cierre']);
    } else {
        $nombre_centro = $descripcion_centro = $hora_apertura = $hora_cierre = $logo = '';
    }

    // Obtener la fecha consultada
    $fecha_actual = isset($_GET['fecha']) ? date('Y-m-d', strtotime($_GET['fecha'])) : date('Y-m-d');    
    $timestamp = strtotime($fecha_actual);
    $dia = date('d', $timestamp);
    $mes = date('m', $timestamp);
    $ano = date('Y', $timestamp);

    // Calcular fechas para los botones
    $hoy = date('Y-m-d');
    $dia_anterior = date('Y-m-d', strtotime('-1 day', $timestamp));
    $dia_siguiente = date('Y-m-d', strtotime('+1 day', $timestamp));
    $mes_anterior = date('Y-m-d', strtotime('-1 month', $timestamp));
    $mes_siguiente = date('Y-m-d', strtotime('+1 month', $timestamp));

    $usuario_dni = '';
    $usuario_rol = '';

    if (
        (isset($_SESSION['rol']) && $_SESSION['rol'] === 'registrado') ||
        (isset($_SESSION['rol'], $_SESSION['DNI']) && $_SESSION['rol'] === 'admin')
    ) {
        $usuario_dni = $_SESSION['DNI'];
        $usuario_rol = $_SESSION['rol'];
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

    <main>
        
        <div class="titulo-reservas">
            <h1>Reservas para el día <?php echo date('d/m/Y', $timestamp); ?></h1>
        </div>
        
        <div class="contenedor-calendario">        

            <div class="cal-botones">
                <form method="get" action="reservas.php">
                    <input type="hidden" name="fecha" value="<?php echo $hoy; ?>">
                    <button type="submit">Hoy</button>
                </form>
                <form method="get" action="reservas.php">
                    <input type="hidden" name="fecha" value="<?php echo $dia_anterior; ?>">
                    <button type="submit">Retroceder día</button>
                </form>
                <form method="get" action="reservas.php">
                    <input type="hidden" name="fecha" value="<?php echo $dia_siguiente; ?>">
                    <button type="submit">Avanzar día</button>
                </form>
                <form method="get" action="reservas.php">
                    <input type="hidden" name="fecha" value="<?php echo $mes_anterior; ?>">
                    <button type="submit">Retroceder mes</button>
                </form>
                <form method="get" action="reservas.php">
                    <input type="hidden" name="fecha" value="<?php echo $mes_siguiente; ?>">
                    <button type="submit">Avanzar mes</button>
                </form>
            </div>

            <div class="calendario-tabla">
                <table>
                    <thead>
                        <tr>
                            <th colspan="7">
                                <?php
                                $meses = [
                                    '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
                                    '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
                                    '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
                                    '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
                                ];
                                echo $meses[$mes] . " $ano";
                                ?>
                            </th>
                        </tr>
                        <tr>
                            <th>L</th><th>M</th><th>X</th><th>J</th><th>V</th><th>S</th><th>D</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $primer_dia_mes = mktime(0, 0, 0, $mes, 1, $ano);
                        $dia_semana_inicio = date('N', $primer_dia_mes); // Lunes = 1
                        $dias_mes = date('t', $primer_dia_mes);

                        $contador = 1;
                        echo "<tr>";

                        // Celdas vacías hasta que empiece el mes
                        for ($i = 1; $i < $dia_semana_inicio; $i++) {
                            echo "<td></td>";
                        }

                        for ($dia_mes = 1; $dia_mes <= $dias_mes; $dia_mes++) {
                            $fecha_loop = date('Y-m-d', mktime(0, 0, 0, $mes, $dia_mes, $ano));
                            $clase = '';
                            if ($fecha_loop == date('Y-m-d')) $clase .= ' hoy';
                            if ($fecha_loop == $fecha_actual) $clase .= ' activo';

                            echo "<td class='$clase'><a href='reservas.php?fecha=$fecha_loop'>$dia_mes</a></td>";

                            if ((($dia_semana_inicio - 1 + $dia_mes) % 7) == 0) {
                                echo "</tr><tr>";
                            }
                        }

                        echo "</tr>";
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <p> Para hacer una reserva debes clickar en la tabla que aula quieres reservar y la hora deseada. </p>
        </div>



        <?php 
            //Recuperacion de datos en la base de datos
            // Salas
            $sql = "SELECT COUNT(*) AS numero FROM salas";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            $resultado1 = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalSalas = $resultado1['numero'];

            $sql = "SELECT * FROM salas";
            $resultado2 = $conexion->query($sql);
            $salas = $resultado2->fetchAll(PDO::FETCH_ASSOC);      

            // Horas            
            $hora_apertura_min = explode(':', $hora_apertura);
            $hora_cierre_min = explode(':', $hora_cierre);

            $hInicio = (int)$hora_apertura_min[0];
            if ((int)$hora_apertura_min[1] > 0) {
                $hInicio++; 
            }

            $hFin = (int)$hora_cierre_min[0]; 

            $diferenciaHoras = $hFin - $hInicio;

            //Reservas
            $sql = "SELECT * FROM reservas WHERE fecha_reserva = :fecha_reserva";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':fecha_reserva', $fecha_actual);
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            //Atributos de las reservas
            $horaDeReservasPorSala = [];
            
            foreach ($reservas as $reserva) {
                $nombre_sala = $reserva['nombre_sala']; 
                $motivo = $reserva['motivo'];
                $hora_inicio = (int) explode(':', $reserva['hora_inicio'])[0];
                $hora_fin = (int) explode(':', $reserva['hora_fin'])[0];
                $DNI = $reserva['DNI'];
                $sql = "SELECT nombre_usuario FROM usuarios WHERE DNI = :DNI";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':DNI', $DNI);
                $stmt->execute();
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($resultado && isset($resultado['nombre_usuario'])) {
                    $nombre_usuario_reserva = $resultado['nombre_usuario'];
                } else {
                    $nombre_usuario_reserva = "No encontrado";
                }


                for ($h = $hora_inicio; $h < $hora_fin; $h++) {
                    $horaDeReservasPorSala[$nombre_sala][$h] = [
                        'motivo' => $motivo,
                        'usuario' => $nombre_usuario_reserva
                    ];
                }
            }

            echo '<table border="1" cellspacing="0" cellpadding="5">';
                    
            echo '<tr>';
            echo '<th>'. $fecha_actual . '</th>';
            for ($j = 0; $j < $diferenciaHoras; $j++) {
                echo '<th>' . ($hInicio + $j) . ':00h</th>';
            }
            echo '</tr>';
            for ($i = 0; $i < $totalSalas; $i++) {
                $reservable = $salas[$i]['reservable'];
                $nombre_sala_actual_tabla = $salas[$i]['nombre_sala'];
                
                echo '<tr>';
                echo '<th>' . $salas[$i]['nombre_sala'] . '</th>';
                for ($j = 0; $j < $diferenciaHoras; $j++) {
                    $hora_actual = $hInicio + $j;
                    if (isset($horaDeReservasPorSala[$nombre_sala_actual_tabla][$hora_actual])) {
                        $reservaInfo = $horaDeReservasPorSala[$nombre_sala_actual_tabla][$hora_actual];
                        echo '<td style="background-color: #d8ecd4;">' . 
                            htmlspecialchars($reservaInfo['motivo']) . 
                            ' - (' . htmlspecialchars($reservaInfo['usuario']) . ')' . 
                            '</td>';
                    }
                    else {
                        if ($usuario_rol === 'admin' || $reservable) {
                            // Solo admins o salas reservables pueden reservar
                            echo "<td class='celda-libre' data-sala='" . htmlspecialchars($nombre_sala_actual_tabla) . "' data-hora='" . $hora_actual . "' data-fecha='" . $fecha_actual . "'></td>";
                        } else {
                            echo "<td style='background-color: #f9d6d5;' title='Sala no reservable'>No reservable</td>";
                        }
                    }
                }
                echo '</tr>';
            }
            
        
        echo '</table>';
        ?>
        

        <div id="formularioReserva" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:20px; border:1px solid black; z-index:9999;">            
            <form method="POST" action="procesar_reserva.php">
                <input type="hidden" name="nombre_sala" id="inputSala">
                <input type="hidden" name="hora_inicio" id="inputHora">
                <input type="hidden" name="hora_fin" id="inputHoraFin">
                <input type="hidden" name="fecha_reserva" id="inputFecha">

                <input type="hidden" name="DNI" value="<?php echo $usuario_dni; ?>">


                <label for="motivo">Motivo:</label><br>
                <textarea name="motivo" required></textarea><br><br>

                <button type="submit">Reservar</button>
                <button type="button" onclick="cerrarFormulario()">Cancelar</button>
            </form>
        </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const usuarioRegistrado = <?php echo json_encode($usuario_rol === 'registrado'); ?>;
            const usuarioAdmin = <?php echo json_encode($usuario_rol === 'admin'); ?>;

            document.querySelectorAll('.celda-libre').forEach(celda => {
                celda.addEventListener('click', (e) => {
                    if (!(usuarioRegistrado || usuarioAdmin)) {
                        e.preventDefault();
                        alert('Debe registrarse para hacer una reserva.');
                        return;
                    }

                    const sala = celda.dataset.sala;
                    const hora = celda.dataset.hora;
                    const fecha = celda.dataset.fecha;

                    // Rellenar campos ocultos del formulario
                    document.getElementById('inputSala').value = sala;
                    document.getElementById('inputHora').value = hora + ':00';
                    document.getElementById('inputFecha').value = fecha;
                    document.getElementById('inputHoraFin').value = parseInt(hora) + 1 + ':00'; // Hora fin es una hora después

                    // Mostrar el formulario
                    document.getElementById('formularioReserva').style.display = 'block';
                });
            });
        });

        function cerrarFormulario() {
            document.getElementById('formularioReserva').style.display = 'none';
        }
    </script>


        </div>
    </main>
    <footer>
        <ul>
            <li>Autores: Laura Guirao Torrente y Marcos Ramírez Heras</li>
            <li><a href="documentacion.pdf">Documentación</a></li>
            <li><a href="">Fichero restaurar BBDD</a></li>
        </ul>
    </footer>
    
</body>
</html>
 