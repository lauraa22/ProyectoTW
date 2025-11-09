<?php
require_once 'conexion.php';

if (isset($_GET['id_imagen_sala'])) {
    $conexion = conectar(); 
    $id = intval($_GET['id_imagen_sala']);

    $sql = "SELECT contenido, tipo FROM imagenes_salas WHERE id_imagen_sala = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($imagen = $stmt->fetch(PDO::FETCH_ASSOC)) {
        header("Content-Type: " . $imagen['tipo']);
        echo $imagen['contenido'];
        exit;
    }
}

http_response_code(404);
echo "Imagen no encontrada.";
?>
