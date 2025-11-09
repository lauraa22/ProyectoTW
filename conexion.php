<?php

require_once 'configuracion.php';

function conectar() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
    try {
        $conexion = new PDO($dsn, DB_USER, DB_PASS);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    } catch (PDOException $e) {
        echo 'Error de conexión: ' . $e->getMessage();
        die();
    }
}

function verificarYCrearTablas() {
    $conexion = conectar();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS usuarios (
        nombre_usuario VARCHAR(12) PRIMARY KEY,
        apellidos VARCHAR(30) NOT NULL,
        DNI VARCHAR(9) NOT NULL UNIQUE,
        email VARCHAR(30) NOT NULL UNIQUE,
        clave VARCHAR(20) NOT NULL,
        foto VARCHAR(100) NOT NULL,
        rol ENUM('registrado', 'admin') DEFAULT 'registrado'
    )";
    $sqlSalas = "
    CREATE TABLE IF NOT EXISTS salas (
        nombre_sala VARCHAR(20) PRIMARY KEY,
        capacidad INT NOT NULL,
        reservable TINYINT(1) NOT NULL,
        descripcion TEXT,
        ubicacion VARCHAR(20) NOT NULL,
        fotos varchar(100)
    )";
    $sqlReservas = "
    CREATE TABLE IF NOT EXISTS reservas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre_usuario INT NOT NULL,
        nombre_sala INT NOT NULL,
        motivo TEXT,
        fecha DATE NOT NULL,
        hora_incio TIME NOT NULL,
        hora_final TIME NOT NULL,
        FOREIGN KEY (nombre_usuario) REFERENCES usuarios(nombre_usuario),
        FOREIGN KEY (nombre_sala) REFERENCES salas(nombre_sala)
    )";
    
    $sqlLog = "
    CREATE TABLE IF NOT EXISTS logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        descripcion TEXT
    )";
    try {
        $conexion->exec($sql);
        $conexion->exec($sqlSalas);
        $conexion->exec($sqlReservas);
        $conexion->exec($sqlLog);
    } catch (PDOException $e) {
        echo 'Error al crear la tabla: ' . $e->getMessage();
    }
}

verificarYCrearTablas();
?>