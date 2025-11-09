<?php

session_start();
include("conexion.php");
include("funciones.php");

$conexion = conectar();

if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'registrado' && $_SESSION['rol'] !== 'admin') || !isset($_SESSION['DNI'])) {
    // No está registrado, mostrar error 
    echo "<p style='color:red;'>Debe registrarse e iniciar sesión para hacer una reserva.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    list($datos, $errores) = validarDatosReserva($conexion);

    if (empty($errores)) {
        if (insertarReserva(
            $conexion,
            $datos['DNI'],
            $datos['nombre_sala'],
            $datos['motivo'],
            $datos['fecha_reserva'],
            $datos['hora_inicio'],
            $datos['hora_fin']
        )) {
            echo "<h3>Reserva realizada correctamente.</h3>";
        } else {
            echo "<h3>Error al guardar la reserva.</h3>";
        }
    } else {
        foreach ($errores as $campo => $mensaje) {
            echo "<p style='color:red;'>$mensaje</p>";
        }
    }
}


$nombre_sala = $_POST['nombre_sala'];
$usuario_rol = $_SESSION['rol'] ?? '';

// Consultar si la sala es reservable
$sql = "SELECT reservable FROM salas WHERE nombre_sala = :nombre_sala";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':nombre_sala', $nombre_sala);
$stmt->execute();
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

if ($resultado && $resultado['reservable'] == 0 && $usuario_rol !== 'admin') {
    die('Error: No tienes permisos para reservar esta sala.');
}


?>
