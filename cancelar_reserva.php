<?php
session_start();
include 'conexion.php';

$conexion = conectar();
if (!isset($_SESSION['DNI'])) {
    header("Location: iniciarSesion.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reserva_id'])) {
   $DNI = $_SESSION['DNI'];
    $rol = $_SESSION['rol'];
    $reserva_id = $_POST['reserva_id'];

    if ($rol === 'admin') {
        // El admin puede borrar cualquier reserva
        $sql = "DELETE FROM reservas WHERE id_reserva = ?";
        $stmt = $conexion->prepare($sql);
        $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha modificado la base de datos: operación DELETE sobre reservas";
        registrar_evento_log("UPDATE", $descripcion);
        $stmt->execute([$reserva_id]);
    } else {
        // El usuario normal solo puede borrar sus propias
        $sql = "DELETE FROM reservas WHERE id_reserva = ? AND DNI = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$reserva_id, $DNI]);
    }


}

header("Location: reservas_usuario.php");
exit();
