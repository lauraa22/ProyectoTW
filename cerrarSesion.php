<?php
require_once 'funciones.php';
session_start();
$descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha cerrado la sesión.";
registrar_evento_log("cerrarSesion", $descripcion);
session_unset();
session_destroy();

header("Location: index.php");
exit();
?>