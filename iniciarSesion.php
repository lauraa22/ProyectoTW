<?php
session_start();

require_once 'funciones.php';
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conexion = conectar();
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Consulta para verificar las credenciales
    $consulta = "SELECT * FROM usuarios WHERE email = :email AND clave = :password";
    $sentencia = $conexion->prepare($consulta);
    $sentencia->bindParam(':email', $email);
    $sentencia->bindParam(':password', $password);
    $sentencia->execute();

    if ($sentencia->rowCount() == 1) { // Si encuentra un par de credenciales valida continuamos
        $usuario = $sentencia->fetch(PDO::FETCH_ASSOC);
        $_SESSION['DNI'] = $usuario['DNI'];
        $_SESSION['rol'] = $usuario['rol']; 

        $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha iniciado sesión.";
        registrar_evento_log("inicioSesion", $descripcion);
        header("Location: index.php");
        exit();
    } else {
        $descripcion = "Un usuario ha intentado iniciar sesión sin éxito.";
        registrar_evento_log("inicioSesionFail", $descripcion);
        header("Location: index.php?error=credenciales_invalidas");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
