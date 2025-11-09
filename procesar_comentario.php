<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['DNI'])) {
    $usuario = $_SESSION['DNI'];
    $nombre_sala = $_POST['nombre_sala'];
    $texto = $_POST['comentario'];

    $conexion = conectar();

    $sql = "INSERT INTO comentarios (nombre_sala, DNI, texto) VALUES (:nombre_sala, :DNI, :texto)";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':nombre_sala', $nombre_sala, PDO::PARAM_STR);
    $stmt->bindParam(':DNI', $usuario, PDO::PARAM_STR);
    $stmt->bindParam(':texto', $texto, PDO::PARAM_STR);
    $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha modificado la base de datos: operación INSERT sobre comentarios";
    registrar_evento_log("UPDATE", $descripcion);
    $stmt->execute();
}

header("Location: aulas.php");
exit;
