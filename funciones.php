<?php

require_once 'conexion.php';

define('TIEMPO_MAX_RESERVA', 30); // Tiempo máximo de reserva en segundos

function validarDatos() {
    $errores = [];
    $datos = [];
    
    if (empty($_POST['nombre_usuario'])) {
        $errores['nombre_usuario'] = 'Debe escribir su nombre';
    } else {
        $datos['nombre_usuario'] = $_POST['nombre_usuario'];
    }

    if (empty($_POST['apellidos'])) {
        $errores['apellidos'] = 'Debe escribir sus apellidos';
    } else {
        $datos['apellidos'] = $_POST['apellidos'];
    }
    
    if (empty($_POST['DNI'])) {
        $errores['DNI'] = 'El DNI no es válido';
    } elseif (!preg_match('/^[0-9]{8}[A-Za-z]$/', $_POST['DNI'])) {
        $errores['DNI'] = 'El DNI debe tener 8 números seguidos de una letra';
    } else {
        $numeros = substr($_POST['DNI'], 0, 8);
        $letra = strtoupper(substr($_POST['DNI'], -1));
        $letra_correcta = substr("TRWAGMYFPDXBNJZSQVHLCKE", $numeros % 23, 1);
        
        if ($letra != $letra_correcta) {
            $errores['DNI'] = 'La letra del DNI es incorrecta';
        } else {
            $datos['DNI'] = $_POST['DNI'];
        }
    }        
    
    if (empty($_POST['email'])) {
        $errores['email'] = 'Debe indiciar un email de contacto';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El email no es válido';
    } else {
        $datos['email'] = $_POST['email'];
    }

   
    if (empty($_POST['clave']) || empty($_POST['repite_clave'])) {
        $errores['clave'] = 'Escriba su clave de acceso';
    } elseif ($_POST['clave'] !== $_POST['repite_clave']) {
        $errores['clave'] = 'Deben coincidir ambas claves';
    } elseif (!preg_match('/^\w{4,}$/', $_POST['clave'])) {
        $errores['clave'] = 'La clave debe contener al menos 4 caracteres alfanuméricos';
    } else {
        $datos['clave'] = $_POST['clave'];
    }

    if (isset($_FILES['foto'])) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $foto = $_FILES['foto'];
    
        if ($foto['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $foto['tmp_name'];
            $fileMimeType = mime_content_type($fileTmpPath);
    
            if (!in_array($fileMimeType, $allowedMimeTypes)) {
                $errores['foto'] = 'La foto debe estar en formato JPEG, PNG o GIF.';
            } else {
                $datos['foto'] = $foto['name']; 
            }
    
        } elseif ($foto['error'] !== UPLOAD_ERR_NO_FILE) {
            $errores['foto'] = 'Error al subir la foto.';
        } else {
            $errores['foto'] = 'Debe cargar una foto.';
        }
    
    } else {
        $errores['foto'] = 'Debe cargar una foto.';
    }

    if (isset($_POST['rol'])) {
        $datos['rol'] = is_array($_POST['rol']) ? $_POST['rol'][0] : $_POST['rol'];
        $roles_permitidos = ['registrado', 'admin'];
        if (!in_array($datos['rol'], $roles_permitidos)) {
            $errores['rol'] = 'Rol no válido.';
        }
    } else {
        $datos['rol'] = 'registrado';
    }

    return [$datos, $errores];
}

function validarDatosAdmin() {
    $errores = [];
    $datos = [];

    
    if (empty($_POST['nombre_usuario'])) {
        $errores['nombre_usuario'] = 'Debe escribir su nombre';
    } else {
        $datos['nombre_usuario'] = $_POST['nombre_usuario'];
    }

    if (empty($_POST['apellidos'])) {
        $errores['apellidos'] = 'Debe escribir sus apellidos';
    } else {
        $datos['apellidos'] = $_POST['apellidos'];
    }
    
    if (empty($_POST['DNI'])) {
        $errores['DNI'] = 'El DNI no es válido';
    } elseif (!preg_match('/^[0-9]{8}[A-Za-z]$/', $_POST['DNI'])) {
        $errores['DNI'] = 'El DNI debe tener 8 números seguidos de una letra';
    } else {
        $numeros = substr($_POST['DNI'], 0, 8);
        $letra = strtoupper(substr($_POST['DNI'], -1));
        $letra_correcta = substr("TRWAGMYFPDXBNJZSQVHLCKE", $numeros % 23, 1);
        
        if ($letra != $letra_correcta) {
            $errores['DNI'] = 'La letra del DNI es incorrecta';
        } else {
            $datos['DNI'] = $_POST['DNI'];
            $conexion = conectar();

            $sql = "SELECT foto,clave FROM usuarios WHERE DNI = :DNI";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':DNI', $datos['DNI']);
            $stmt->execute();
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($fila && !empty($fila['foto'])) {
                $datos['foto'] = $fila['foto'];
            } else {
                $errores['foto'] = 'No se encontró la foto en la base de datos.';
            }
            if (!empty($fila['clave'])) {
                $datos['clave'] = $fila['clave'];
            } else {
                $errores['clave'] = 'No se encontró la clave en la base de datos.';
            }
        }
    }
    
    if (empty($_POST['email'])) {
        $errores['email'] = 'Debe indiciar un email de contacto';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El email no es válido';
    } else {
        $datos['email'] = $_POST['email'];
    }

    if (isset($_POST['rol'])) {
        $datos['rol'] = is_array($_POST['rol']) ? $_POST['rol'][0] : $_POST['rol'];
        $roles_permitidos = ['registrado', 'admin'];
        if (!in_array($datos['rol'], $roles_permitidos)) {
            $errores['rol'] = 'Rol no válido.';
        }
    } else {
        $datos['rol'] = 'registrado';
    }

    return [$datos, $errores];
}

function obtener_rol_administrador($DNI, $rol = 'admin') {
    $conexion = conectar();

    // Verificar si el DNI ya existe en la base de datos
    $sql_verificar_dni = "SELECT COUNT(*) FROM usuarios WHERE DNI = :DNI";
    $sentencia_verificar_dni = $conexion->prepare($sql_verificar_dni);
    $sentencia_verificar_dni->bindParam(':DNI', $DNI);
    $sentencia_verificar_dni->execute();
    $count = $sentencia_verificar_dni->fetchColumn();

    if ($count > 0) {
        try {
            $sql = "UPDATE usuarios SET rol = :rol WHERE DNI = :DNI";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':DNI', $DNI);  
            $stmt->execute();
            $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha modificado la base de datos: operación UPDATE sobre rol";
            registrar_evento_log("UPDATE", $descripcion);

            return true; 

        } catch (PDOException $e) {
            return false; 
        }
    } else {
        return false; // El DNI no existe
    }
}

function mostrarFormulario($datos = [], $errores = [], $deshabilitado = false) {
    $readonly = $deshabilitado ? 'readonly' : '';
    $disabled = $deshabilitado ? 'disabled' : '';

    $nombre_usuario = $datos['nombre_usuario'] ?? '';
    $apellidos = $datos['apellidos'] ?? '';
    $DNI = $datos['DNI'] ?? '';
    $email = $datos['email'] ?? '';
    $clave = $datos['clave'] ?? '';
    $foto = $datos['foto'] ?? '';
 
    echo '<h2 class="titulos-administrador"> Registro de usuarios</h2>';
    
    echo '<form action="" method="POST" enctype="multipart/form-data" novalidate>';
    echo '<fieldset>';
    echo '<legend>Datos personales</legend>';
    echo '<section>';
    echo '<div>';
    echo '<label>Nombre: <input type="text" name="nombre_usuario" size="20" maxlength="50"  value="' . $nombre_usuario . '" /></label>';
    if (isset($errores['nombre_usuario'])) echo '<span style="color: red;">' . $errores['nombre_usuario'] . '</span>';
    echo '</div>';

    echo '<div>';
    echo '<label>Apellidos: <input type="text" name="apellidos" value="' . $apellidos . '" /></label>';
    if (isset($errores['apellidos'])) echo '<span style="color: red;">' . $errores['apellidos'] . '</span>';
    echo '</div>';

    echo '<div>';
    echo '<label>DNI: <input type="text" name="DNI" maxlength="9" value="' . $DNI . '"/></label>';
    if (isset($errores['DNI'])) echo '<span style="color: red;">' . $errores['DNI'] . '</span>';
    echo '</div>';
    echo '</fieldset>';

    echo '<fieldset>';
    echo '<legend>Datos de acceso</legend>';
    echo '<section>';
    echo '<div>';
    echo '<label>E-mail: <input type="email" name="email" value="' . $email . '" ' . $readonly . '/></label>';
    if (isset($errores['email'])) echo '<span style="color: red;">' . $errores['email'] . '</span>';
    echo '</div>';
    echo '</section>';

    echo '<section>';
    echo '<div>';
    echo '<label>Clave de acceso: <input type="password" name="clave" placeholder="Escriba la clave" value="' . $clave . '" ' . $readonly . '/></label>';
    echo '<label>Repita la clave: <input type="password" name="repite_clave" placeholder="Escriba la misma clave" value="' . $clave . '" ' . $readonly . '/></label>';
    if (isset($errores['clave'])) echo '<span style="color: red;">' . $errores['clave'] . '</span>';
    echo '</div>';
    echo '</section>';
    echo '</fieldset>';

    echo '<div>';
    echo '<label>Fotografía: <input type="file" name="foto" accept="image/*" ' . $disabled . ' /></label>';
    if (isset($errores['foto'])) echo '<span style="color:red;">' . $errores['foto'] . '</span>';
    echo '</div>';

    echo '<input type="hidden" name="version_formulario" value="3.0">';
    echo '<input id="boton" type="submit" name="enviar" value="' . ($deshabilitado ? 'Confirmar datos' : 'Enviar datos') . '"/>';
    echo '</form>';
}


function generarMenu() {
    $menuHTML = ' <ul>
                    <li><a href="index.php">Página principal</a></li>';
    
    if(isset($_SESSION['rol'])) {
        $rol = $_SESSION['rol'];
        switch($rol) {
            case 'registrado':
                $menuHTML .= '<li><a href="aulas.php">Aulas Disponibles</a></li>
                              <li><a href="reservas.php">Reservas</a></li>
                              <li><a href="reservas_usuario.php">Mis reservas</a></li>
                              <li><a href="formularioAdministrador.php">Registro Administrador</a></li>
                              <li><a href="editardatosusuarios.php">Editar Datos</a></li>';
                break;
            case 'admin':
                $menuHTML .= '<li><a href="aulas.php">Aulas Disponibles</a></li>
                              <li><a href="admin.php">Gestiones administrador</a></li>
                              <li><a href="reservas.php">Reservas</a></li>
                              <li><a href="reservas_usuario.php">Gestiones de reservas</a></li>';
                break;
            default:
                break;
        }
        $menuHTML .= '<li><a href="cerrarSesion.php">Cerrar sesión</a></li>';
    } else {
        $menuHTML .= '<li><a href="aulas.php">Aulas Disponibles</a></li>
                      <li><a href="reservas.php">Reservas</a></li>
                      <li><a href="registro.php">Registro</a></li>
                      </ul>
                        <div class= "login">
                            <form action="iniciarSesion.php" method="post">
                                <input type="email" name="email" placeholder="Correo electrónico" required>
                                <input type="password" name="password" placeholder="Contraseña" required><br>
                                <button type="submit">Iniciar sesión</button>
                            </form>
                        </div>';
    }
    echo $menuHTML;
}


function insertar_usuario($nombre_usuario, $apellidos, $DNI, $email, $clave, $foto, $rol = 'registrado') {
    $conexion = conectar();

    // Verificar si el DNI ya existe en la base de datos
    $sql_verificar_dni = "SELECT COUNT(*) FROM usuarios WHERE DNI = :DNI";
    $sentencia_verificar_dni = $conexion->prepare($sql_verificar_dni);
    $sentencia_verificar_dni->bindParam(':DNI', $DNI);
    $sentencia_verificar_dni->execute();
    $count = $sentencia_verificar_dni->fetchColumn();

    // Si el DNI ya existe, devolver un mensaje de error
    if ($count > 0) {
        return false; 
    }

    // Validar el rol antes de la inserción
    $roles_permitidos = ['registrado'];
    if (!in_array($rol, $roles_permitidos)) {
        $rol = 'registrado'; // Valor predeterminado en caso de rol no válido
    }

    // Si el DNI no existe, proceder con la inserción del usuario
    $sql = "INSERT INTO usuarios (nombre_usuario, apellidos, DNI, email, clave, foto, rol) VALUES (:nombre_usuario, :apellidos, :DNI, :email, :clave, :foto, :rol)";
    $stmt= $conexion->prepare($sql);
    $stmt->bindParam(':nombre_usuario', $nombre_usuario);
    $stmt->bindParam(':apellidos', $apellidos);
    $stmt->bindParam(':DNI', $DNI);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':clave', $clave);
    $stmt->bindParam(':foto', $foto);
    $stmt->bindParam(':rol', $rol);
    $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha modificado la base de datos: operación INSERT sobre usuarios";
    registrar_evento_log("UPDATE", $descripcion);
    return $stmt->execute();
}

function mostrarFormularioEdicion($datos = [], $errores = [], $deshabilitado = false) {
    $nombre_usuario = $datos['nombre_usuario'] ?? '';
    $apellidos = $datos['apellidos'] ?? '';
    $DNI = $datos['DNI'] ?? '';
    $email = $datos['email'] ?? '';
    $clave = $datos['clave'] ?? '';
    $foto = $datos['foto'] ?? '';
    $rol = $datos['rol'] ?? 'registrado';
    $disabled = $deshabilitado ? 'readonly' : '';

    echo '<h2 class="titulos-administrador"> Editar usuario</h2>';
    echo '<form action="" method="POST" enctype="multipart/form-data" novalidate>';

    if ($_SESSION['rol'] === 'admin') {
        echo '<fieldset>';
        echo '<legend>Datos personales</legend>';

        echo '<section>';
        echo '<div>';
        echo '<label>Nombre: <input type="text" name="nombre_usuario" size="20" maxlength="50" value="' . htmlspecialchars($nombre_usuario) . '" /></label>';
        if (isset($errores['nombre_usuario'])) echo '<span style="color: red;">' . htmlspecialchars($errores['nombre_usuario']) . '</span>';
        echo '</div>';

        echo '<div>';
        echo '<label>Apellidos: <input type="text" name="apellidos" value="' . htmlspecialchars($apellidos) . '" /></label>';
        if (isset($errores['apellidos'])) echo '<span style="color: red;">' . htmlspecialchars($errores['apellidos']) . '</span>';
        echo '</div>';

        echo '<div>';
        echo '<label>DNI: <input type="text" name="DNI" maxlength="9" value="' . htmlspecialchars($DNI) . '" /></label>';
        if (isset($errores['DNI'])) echo '<span style="color: red;">' . htmlspecialchars($errores['DNI']) . '</span>';
        echo '</div>';
        echo '</section>';

        echo '</fieldset>';

        echo '<fieldset>';
        echo '<legend>Rol de usuario (admin, registrado)</legend>';

        echo '<section>';
        echo '<div>';
        echo '<label>Rol: <select name="rol">';
        echo '<option value="admin"' . ($rol == 'admin' ? ' selected' : '') . '>Admin</option>';
        echo '<option value="registrado"' . ($rol == 'registrado' ? ' selected' : '') . '>Registrado</option>';
        echo '</select></label>';
        if (isset($errores['rol'])) echo '<span style="color: red;">' . htmlspecialchars($errores['rol']) . '</span>';
        echo '</div>';
        echo '</section>';

        echo '</fieldset>';
    }
    
    echo '<fieldset>';
    echo '<legend>Datos de acceso</legend>';

    echo '<section>';
    echo '<div>';
    echo '<label>E-mail: <input type="email" name="email" value="' . htmlspecialchars($email) . '" /></label>';
    if (isset($errores['email'])) echo '<span style="color: red;">' . htmlspecialchars($errores['email']) . '</span>';
    echo '</div>';
    echo '</section>';

    echo '<section>';
    echo '<div>';
    echo '<label>Clave de acceso: <input type="password" name="clave" placeholder="Escriba la clave" value="' . htmlspecialchars($clave) . '" /></label>';
    echo '<label>Repita la clave: <input type="password" name="repite_contraseña" placeholder="Escriba la misma clave" value="' . htmlspecialchars($clave) . '" /></label>';
    if (isset($errores['clave'])) echo '<span style="color: red;">' . htmlspecialchars($errores['clave']) . '</span>';
    echo '</div>';
    echo '</section>';

    echo '</fieldset>';

    echo '<fieldset>';
    echo '<div>';
    echo '<label>Fotografía: <input type="file" name="foto" accept="image/*" ' . $disabled . ' /></label>';
    if (isset($errores['foto'])) echo '<span style="color:red;">' . $errores['foto'] . '</span>';
    echo '</div>';
    echo '</fieldset>';


    echo '<fieldset>';
    echo '<input type="hidden" name="id_editar" value="' . htmlspecialchars($datos['id'] ?? '') . '">';
    echo '<input type="hidden" name="version_formulario" value="3.0">';
    echo '<input id="boton" type="submit" name="editar" value="Editar datos" />';
    echo '</fieldset>';

    echo '</form>';
}

function mostrarFormularioEdicionCentro($datos = [], $errores = [], $deshabilitado = false) {
    $readonly = $deshabilitado ? 'readonly' : '';
    $disabled = $deshabilitado ? 'disabled' : '';

    $nombre_centro = $datos['nombre_centro'] ?? '';
    $descripcion_centro = $datos['descripcion_centro'] ?? '';
    $hora_apertura = $datos['hora_apertura'] ?? '';
    $hora_cierre = $datos['hora_cierre'] ?? '';

    echo '<h2 class="titulos-administrador"> Editar datos del centro</h2>';

    echo '<form action="" method="POST" enctype="multipart/form-data" novalidate>';
    echo '<fieldset>';
    echo '<legend>Datos del centro</legend>';

    echo '<section>';
    echo '<div>';
    echo '<label>Nombre del centro: <input type="text" name="nombre_centro" value="' . htmlspecialchars($nombre_centro) . '" /></label>';
    if (isset($errores['nombre_centro'])) echo '<span style="color: red;">' . htmlspecialchars($errores['nombre_centro']) . '</span>';
    echo '</div>';

    echo '<div>';
    echo '<label>Descripción del centro: <textarea name="descripcion_centro">' . htmlspecialchars($descripcion_centro) . '</textarea></label>';
    if (isset($errores['descripcion_centro'])) echo '<span style="color: red;">' . htmlspecialchars($errores['descripcion_centro']) . '</span>';
    echo '</div>';
    echo '</section>';

    echo '<section>';
    echo '<div>';
    echo '<label>Hora de apertura: <input type="time" name="hora_apertura" value="' . htmlspecialchars($hora_apertura) . '" /></label>';
    if (isset($errores['hora_apertura'])) echo '<span style="color: red;">' . htmlspecialchars($errores['hora_apertura']) . '</span>';
    echo '</div>';

    echo '<div>';
    echo '<label>Hora de cierre: <input type="time" name="hora_cierre" value="' . htmlspecialchars($hora_cierre) . '" /></label>';
    if (isset($errores['hora_cierre'])) echo '<span style="color: red;">' . htmlspecialchars($errores['hora_cierre']) . '</span>';
    echo '</div>';
    echo '</section>';

    echo '<section>';
    echo '<div>';
    echo '<label>Logo del centro: <input type="file" name="logo" accept="image/*" ' . $disabled . ' /></label>';
    if (isset($errores['logo'])) echo '<span style="color: red;">' . htmlspecialchars($errores['logo']) . '</span>';
    echo '</div>';
    echo '</section>';

    echo '<input type="hidden" name="version_formulario" value="3.0">';

    echo '<div>';
    echo '<input id="boton" type="submit" name="editar_centro" value="' . ($deshabilitado ? 'Confirmar datos' : 'Editar datos') . '" />';
    echo '</div>';

    echo '</fieldset>';
    echo '</form>';
}

function mostrarFormularioSalas($datos = [], $errores = [], $deshabilitado = false) {
    $readonly = $deshabilitado ? 'readonly' : '';
    $disabled = $deshabilitado ? 'disabled' : '';

    $nombre_sala = $datos['nombre_sala'] ?? '';
    $ubicacion = $datos['ubicacion'] ?? '';
    $capacidad = $datos['capacidad'] ?? '';
    $reservable = $datos['reservable'] ?? '';
    $descripcion_sala = $datos['descripcion_sala'] ?? '';


    echo '<h2 class="titulos-administrador">Añadir sala</h2>';

    echo '<form action="" method="POST" enctype="multipart/form-data" novalidate>';
    echo '<fieldset>';
    echo '<legend>Datos de la sala</legend>';
    echo '<section>';
    echo '<div>';
    echo '<label>Nombre de la sala: <input type="text" name="nombre_sala" value="' . htmlspecialchars($nombre_sala) . '" /></label>';
    if (isset($errores['nombre_sala'])) echo '<span style="color: red;">' . htmlspecialchars($errores['nombre_sala']) . '</span>';
    echo '</div>';

    echo '<div>';
    echo '<label>Descripción de la sala: <textarea name="descripcion_sala">' . htmlspecialchars($descripcion_sala) . '</textarea></label>';
    if (isset($errores['descripcion_sala'])) echo '<span style="color: red;">' . htmlspecialchars($errores['descripcion_sala']) . '</span>';
    echo '</div>';
    echo '</section>';
    echo '<section>';
    echo '<div>';
    echo '<label>Ubicación: <input type="text" name="ubicacion" value="' . htmlspecialchars($ubicacion) . '" /></label>';
    if (isset($errores['ubicacion'])) echo '<span style="color: red;">' . htmlspecialchars($errores['ubicacion']) . '</span>';
    echo '</div>';

    echo '<div>';
    echo '<label>Capacidad: <input type="number" name="capacidad" value="' . htmlspecialchars($capacidad) . '" /></label>';
    if (isset($errores['capacidad'])) echo '<span style="color: red;">' . htmlspecialchars($errores['capacidad']) . '</span>';
    echo '</div>';

    echo '<div>';
    echo '<label>Reservable: ';
    echo '<select name="reservable" ' . '>';
    echo '<option value="1"' . ($reservable == '1' ? ' selected' : '') . '>Sí</option>';
    echo '<option value="0"' . ($reservable == '0' ? ' selected' : '') . '>No</option>';
    echo '</select>';
    echo '</label>';
    if (isset($errores['reservable'])) echo '<span style="color: red;">' . htmlspecialchars($errores['reservable']) . '</span>';
    echo '</div>';
    echo '</section>';
    echo '<section>';
    echo '<div>';
    echo '<label>Adjunte las imágenes para la sala: <input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*" /></label>';
    if (isset($errores['imagenes'])) echo '<span style="color: red;">' . htmlspecialchars($errores['imagenes']) . '</span>';
    echo '</div>';
    echo '</section>';
    echo '<input type="hidden" name="version_formulario" value="3.0">';

    echo '<div>';
    echo '<input id="boton" type="submit" name="enviar" value="' . ($deshabilitado ? 'Confirmar datos' : 'Enviar datos') . '" />';
    echo '</div>';

    echo '</fieldset>';
    echo '</form>';
}


function mostrarFormularioEdicionSalas($datos = [], $errores = [], $deshabilitado = false) {
    $readonly = $deshabilitado ? 'readonly' : '';
    $disabled = $deshabilitado ? 'disabled' : '';

    $nombre_sala = $datos['nombre_sala'] ?? '';
    $ubicacion = $datos['ubicacion'] ?? '';
    $capacidad = $datos['capacidad'] ?? '';
    $reservable = $datos['reservable'] ?? '';
    $descripcion_sala = $datos['descripcion_sala'] ?? '';
    $imagenes = $datos['imagenes'] ?? null;


    echo '<h2 class="titulos-administrador">Editar sala</h2>';

    echo '<form action="" method="POST" enctype="multipart/form-data" novalidate>';
    echo '<fieldset>';
    echo '<legend>Datos de la sala</legend>';
    echo '<section>';
    echo '<div>';
    echo '<label>Nombre de la sala: <input type="text" name="nombre_sala" value="' . htmlspecialchars($nombre_sala) . '" /></label>';
    if (isset($errores['nombre_sala'])) echo '<span style="color: red;">' . htmlspecialchars($errores['nombre_sala']) . '</span>';
    echo '</div>';

    echo '<div>';
    echo '<label>Descripción de la sala: <textarea name="descripcion_sala">' . htmlspecialchars($descripcion_sala) . '</textarea></label>';
    if (isset($errores['descripcion_sala'])) echo '<span style="color: red;">' . htmlspecialchars($errores['descripcion_sala']) . '</span>';
    echo '</div>';
    echo '</section>';
    echo '<section>';
    echo '<div>';
    echo '<label>Ubicación: <input type="text" name="ubicacion" value="' . htmlspecialchars($ubicacion) . '" /></label>';
    if (isset($errores['ubicacion'])) echo '<span style="color: red;">' . htmlspecialchars($errores['ubicacion']) . '</span>';
    echo '</div>';

    echo '<div>';
    echo '<label>Capacidad: <input type="number" name="capacidad" value="' . htmlspecialchars($capacidad) . '" /></label>';
    if (isset($errores['capacidad'])) echo '<span style="color: red;">' . htmlspecialchars($errores['capacidad']) . '</span>';
    echo '</div>';

    echo '<div>';
    echo '<label>Reservable: ';
    echo '<select name="reservable" ' . $disabled . '>';
    echo '<option value="SI"' . ($reservable == '1' ? ' selected' : '') . '>Sí</option>';
    echo '<option value="NO"' . ($reservable == '0' ? ' selected' : '') . '>No</option>';
    echo '</select>';
    echo '</label>';
    if (isset($errores['reservable'])) echo '<span style="color: red;">' . htmlspecialchars($errores['reservable']) . '</span>';
    echo '</div>';
    echo '</section>';
    echo '<section>';
    echo '<div>';
    echo '<label>Adjunte las imágenes para la sala: <input type="file" name="imagenes[]" id="imagenes" multiple accept="image/*" /></label>';
    if (isset($errores['imagenes'])) echo '<span style="color: red;">' . htmlspecialchars($errores['imagenes']) . '</span>';
    echo '</div>';
    echo '</section>';
    echo '<input type="hidden" name="version_formulario" value="3.0">';

    echo '<div>';
    echo '<input id="boton" type="submit" name="enviar" value="' . ($deshabilitado ? 'Confirmar datos' : 'Editar datos') . '" />';
    echo '</div>';

    echo '</fieldset>';
    echo '</form>';
}

function obtener_datos_web(){
    $conexion = conectar();
    if($conexion) {
        $sql = "SELECT * FROM sitio_web WHERE id = 1";
        $stmt = $conexion->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

function modificar_datos_centro($nombre_centro, $descripcion_centro, $hora_apertura, $hora_cierre, $logo) {
    $conexion = conectar();
    if ($conexion) {
        $sql = "UPDATE sitio_web SET nombre_centro = :nombre_centro, descripcion_centro = :descripcion_centro, hora_apertura = :hora_apertura, hora_cierre = :hora_cierre, logo = :logo WHERE id = 1";
        $stmt= $conexion->prepare($sql);
        $stmt->bindParam(':nombre_centro', $nombre_centro);
        $stmt->bindParam(':descripcion_centro', $descripcion_centro);
        $stmt->bindParam(':hora_apertura', $hora_apertura);
        $stmt->bindParam(':hora_cierre', $hora_cierre);
        $stmt->bindParam(':logo', $logo);
        $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha modificado la base de datos: operación UPDATE sobre sitio_web";
        registrar_evento_log("UPDATE", $descripcion);
        return $stmt->execute();
    }
}

function modificar_datos_usuario($DNI, $email, $clave,$foto, $rol) {
    $conexion = conectar();
    if ($conexion) {
        $sql = "UPDATE usuarios SET email = :email, clave = :clave, foto = :foto, rol = :rol WHERE DNI = :DNI";
        $stmt= $conexion->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':clave', $clave);
        $stmt->bindParam(':foto', $foto);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':DNI', $DNI, PDO::PARAM_STR);

        $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha modificado la base de datos: operación UPDATE sobre usuarios";
        registrar_evento_log("UPDATE", $descripcion);

        return $stmt->execute();
    }
}


function obtener_datos_usuario_por_dni($DNI) {
    $conexion = conectar();
    
    $sql = "SELECT * FROM usuarios WHERE DNI = :DNI";
    $stmt= $conexion->prepare($sql);
    $stmt->bindParam(':DNI', $DNI, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        return false;
    }
}


function obtener_usuarios() {
    $conexion = conectar();
    $sql = "SELECT * FROM usuarios";
    $stmt= $conexion->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtener_logs() {
    $conexion = conectar();
    $sql = "SELECT * FROM logs";
    $stmt= $conexion->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function borrar_usuario($DNI) {
    $conexion = conectar();

    $stmt= $conexion->prepare("DELETE FROM usuarios WHERE DNI = :DNI");
    $stmt->bindParam(':DNI', $DNI);
    $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha modificado la base de datos: operación DELETE sobre usuarios";
    registrar_evento_log("UPDATE", $descripcion);
    $stmt->execute();
}


function obtener_salas(/*$nombre_sala*/) {
    $conexion = conectar();
    $sql = "SELECT * FROM salas";
    $stmt= $conexion->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function borrar_salas($nombre_sala) {
    $conexion = conectar();

    $stmt= $conexion->prepare("DELETE FROM salas WHERE nombre_sala = ?");

    $stmt->bindParam(1, $nombre_sala);

    $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha modificado la base de datos: operación DELETE sobre salas";
    registrar_evento_log("UPDATE", $descripcion);
    $stmt->execute();
}

// Insertar salas
function insertar_sala($nombre_sala, $ubicacion, $capacidad, $descripcion_sala, $reservable) {
    $conexion = conectar();
    $query = "INSERT INTO salas (nombre_sala, ubicacion, capacidad, descripcion_sala, reservable) VALUES (:nombre_sala, :ubicacion, :capacidad, :descripcion_sala, :reservable)";
    $stmt= $conexion->prepare($query);

    $stmt->bindParam(':nombre_sala', $nombre_sala);
    $stmt->bindParam(':capacidad', $capacidad, PDO::PARAM_INT);
    $stmt->bindParam(':reservable', $reservable, PDO::PARAM_INT);
    $stmt->bindParam(':descripcion_sala', $descripcion_sala);
    $stmt->bindParam(':ubicacion', $ubicacion);
    $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha modificado la base de datos: operación INSERT sobre salas";
    registrar_evento_log("UPDATE", $descripcion);
    return $stmt->execute();
}

function insertar_imagen_sala( $nombre_sala, $nombre_imagen, $tipo, $contenido) {
    $conexion = conectar();
    $sql = "INSERT INTO imagenes_salas (nombre_sala, nombre_imagen, tipo, contenido) VALUES (:nombre_sala, :nombre_imagen, :tipo, :contenido)";

    $stmt = $conexion->prepare($sql);

    $stmt->bindParam(':nombre_sala', $nombre_sala, PDO::PARAM_STR);
    $stmt->bindParam(':nombre_imagen', $nombre_imagen, PDO::PARAM_STR);
    $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
    $stmt->bindParam(':contenido', $contenido, PDO::PARAM_LOB); 
    $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha modificado la base de datos: operación INSERT sobre imagenes_salas";
    registrar_evento_log("UPDATE", $descripcion);
    return $stmt->execute();
}


function mostrarFormularioAdministrador($datos = [], $errores = [], $deshabilitado = false) {
    $readonly = $deshabilitado ? 'readonly' : '';
    $disabled = $deshabilitado ? 'disabled' : '';

    $nombre_usuario = $datos['nombre_usuario'] ?? '';
    $apellidos = $datos['apellidos'] ?? '';
    $DNI = $datos['DNI'] ?? '';
    $email = $datos['email'] ?? '';
 
    echo '<h2 class="titulos-administrador"> Registro Administrador</h2>';
    
    echo '<form action="" method="POST" enctype="multipart/form-data" novalidate>';
    echo '<fieldset>';
    echo '<legend>Datos personales</legend>';
    echo '<section>';
    echo '<div>';
    echo '<label>Nombre: <input type="text" name="nombre_usuario" size="20" maxlength="50"  value="' . $nombre_usuario . '" /></label>';
    if (isset($errores['nombre_usuario'])) echo '<span style="color: red;">' . $errores['nombre_usuario'] . '</span>';
    echo '</div>';

    echo '<div>';
    echo '<label>Apellidos: <input type="text" name="apellidos" value="' . $apellidos . '" /></label>';
    if (isset($errores['apellidos'])) echo '<span style="color: red;">' . $errores['apellidos'] . '</span>';
    echo '</div>';

    echo '<div>';
    echo '<label>DNI: <input type="text" name="DNI" maxlength="9" value="' . $DNI . '"/></label>';
    if (isset($errores['DNI'])) echo '<span style="color: red;">' . $errores['DNI'] . '</span>';
    echo '</div>';
    echo '</fieldset>';

    echo '<fieldset>';
    echo '<legend>Datos de acceso</legend>';
    echo '<section>';
    echo '<div>';
    echo '<label>E-mail: <input type="email" name="email" value="' . $email . '" ' . $readonly . '/></label>';
    if (isset($errores['email'])) echo '<span style="color: red;">' . $errores['email'] . '</span>';
    echo '</div>';
    echo '</section>';

    echo '<input type="hidden" name="version_formulario" value="3.0">';
    echo '<input id="boton" type="submit" name="enviar" value="' . ($deshabilitado ? 'Confirmar datos' : 'Enviar datos') . '"/>';
    echo '</form>';
}


// Validar datos de la sala
function validarDatosSala() {
    $errores = [];
    $datos = [];

    // Validar nombre de sala
    if (isset($_POST['nombre_sala']) && !empty(trim($_POST['nombre_sala']))) {
        $datos['nombre_sala'] = trim($_POST['nombre_sala']);
        $nombre_sala = $datos['nombre_sala'];
        // Verificar si el nombre de la sala ya está registrado en la base de datos
        if (existeNombreSala($nombre_sala)) {
            $errores['nombre_sala'] = 'El nombre de la sala ya está en uso.';
        } else {
            $datos['nombre_sala'] = $nombre_sala;
        }
    } else {
        $errores['nombre_sala'] = 'El nombre de la sala es obligatorio.';
    }

    // Validar capacidad
    if (isset($_POST['capacidad']) && filter_var($_POST['capacidad'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
        $datos['capacidad'] = $_POST['capacidad'];
    } else {
        $errores['capacidad'] = 'La capacidad debe ser un número entero positivo.';
    }

    // Validar reservable (esperamos '0' o '1')
    if (isset($_POST['reservable']) && ($_POST['reservable'] === '0' || $_POST['reservable'] === '1')) {
        $datos['reservable'] = $_POST['reservable'];
    } else {
        $errores['reservable'] = 'Debe indicar si la sala es reservable (Sí o No).';
    }

    // Validar descripción
    if (isset($_POST['descripcion_sala']) && !empty(trim($_POST['descripcion_sala']))) {
        $datos['descripcion_sala'] = trim($_POST['descripcion_sala']);
    } else {
        $errores['descripcion_salas'] = 'La descripción es obligatoria.';
    }

    // Validar ubicación
    if (isset($_POST['ubicacion']) && !empty(trim($_POST['ubicacion']))) {
        $datos['ubicacion'] = trim($_POST['ubicacion']);
    } else {
        $errores['ubicacion'] = 'La ubicación es obligatoria.';
    }

    // Validar fotografías
    $imagenes = $_FILES['imagenes'];

    if (isset($imagenes) && !empty($imagenes['name'][0])) {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
        for ($i = 0; $i < count($imagenes['name']); $i++) {
            if ($imagenes['error'][$i] === UPLOAD_ERR_OK) {
                $fileTmpPath = $imagenes['tmp_name'][$i];
                $fileMimeType = mime_content_type($fileTmpPath);
                $fileName = $imagenes['name'][$i];
        
                if (!in_array($fileMimeType, $allowedMimeTypes)) {
                    $errores['imagenes'][$i] = 'El archivo debe estar en formato JPEG, PNG o GIF.';
                } else {
                    $nombre_imagen = $fileName;
                    $contenido = file_get_contents($fileTmpPath);
                    $tipo = $fileMimeType;
        
                    $datos['imagenes'][] = [
                        'nombre_sala' => $nombre_sala,     
                        'nombre_imagen' => $nombre_imagen,
                        'tipo' => $tipo,
                        'contenido' => $contenido
                    ];
                }
            } else {
                $errores['imagenes'][$i] = 'Error en la subida del archivo.';
            }
        }
        
    }
            
    return [$datos, $errores];
}

// Función para verificar si el nombre de sala ya está registrado en la base de datos
function existeNombreSala($nombre_sala) {
    $conexion = conectar();
    $query = "SELECT COUNT(*) FROM salas WHERE nombre_sala = :nombre_sala";
    $stmt= $conexion->prepare($query);
    $stmt->bindParam(':nombre_sala', $nombre_sala);
    $stmt->execute();
    $resultado = $stmt->fetchColumn();
    return $resultado > 0;
}

function modificar_datos_sala($nombre_sala, $ubicacion, $capacidad, $descripcion_sala, $reservable) {    
    $conexion = conectar();
    if ($conexion) {
        $sql = "UPDATE salas SET nombre_sala = :nombre_sala, ubicacion = :ubicacion, capacidad = :capacidad, descripcion_sala = :descripcion_sala, reservable = :reservable WHERE nombre_sala = :nombre_sala";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre_sala', $nombre_sala);
        $stmt->bindParam(':ubicacion', $ubicacion);
        $stmt->bindParam(':capacidad', $capacidad, PDO::PARAM_INT);
        $stmt->bindParam(':descripcion_sala', $descripcion_sala);
        $stmt->bindParam(':reservable', $reservable, PDO::PARAM_INT);

        $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha modificado la base de datos: operación UPDATE sobre salas";
        registrar_evento_log("UPDATE", $descripcion);

        return $stmt->execute();
    }
}


function obtener_sala_por_nombre_sala($nombre_sala) {
    $conexion = conectar();
    $sql = "SELECT * FROM salas WHERE nombre_sala = :nombre_sala";
    $stmt= $conexion->prepare($sql);
    $stmt->bindParam(':nombre_sala', $nombre_sala, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function validarDatosReserva($conexion) {
    $errores = [];
    $datos = [];

    // DNI
    if (empty($_POST['DNI']) || strlen($_POST['DNI']) !== 9) {
        $errores['DNI'] = 'El DNI debe tener 9 caracteres.';
    } else {
        $datos['DNI'] = $_POST['DNI'];
    }

    // Nombre de la sala
    if (empty($_POST['nombre_sala'])) {
        $errores['nombre_sala'] = 'Debes seleccionar una sala.';
    } else {
        $datos['nombre_sala'] = $_POST['nombre_sala'];
    }

    // Motivo
    if (empty($_POST['motivo'])) {
        $errores['motivo'] = 'El motivo de la reserva es obligatorio.';
    } else {
        $datos['motivo'] = $_POST['motivo'];
    }

    // Fecha de la reserva
    if (empty($_POST['fecha_reserva'])) {
        $errores['fecha_reserva'] = 'La fecha de la reserva es obligatoria.';
    } else {
        $datos['fecha_reserva'] = $_POST['fecha_reserva'];
        if ($_POST['fecha_reserva'] < date('Y-m-d')) {
            $errores['fecha_reserva'] = 'No puedes reservar una fecha pasada.';
        }
    }

    // Hora inicio
    if (empty($_POST['hora_inicio'])) {
        $errores['hora_inicio'] = 'La hora de inicio es obligatoria.';
    } else {
        $datos['hora_inicio'] = $_POST['hora_inicio'];
    }

    // Hora fin
    if (empty($_POST['hora_fin'])) {
        $errores['hora_fin'] = 'La hora de fin es obligatoria.';
    } else {
        $datos['hora_fin'] = $_POST['hora_fin'];
    }

    if (!empty($datos['hora_inicio']) && !empty($datos['hora_fin'])) {
        if ($datos['hora_inicio'] >= $datos['hora_fin']) {
            $errores['hora'] = 'La hora de inicio debe ser anterior a la hora de fin.';
        }
    }

    return [$datos, $errores];
}


function insertarReserva($conexion, $dni, $nombre_sala, $motivo, $fecha, $hora_inicio, $hora_fin) {
    $stmt = $conexion->prepare("
        INSERT INTO reservas (DNI, nombre_sala, motivo, fecha_reserva, hora_inicio, hora_fin)
        VALUES (:dni, :nombre_sala, :motivo, :fecha, :hora_inicio, :hora_fin)
    ");
    $stmt->bindParam(':dni', $dni);
    $stmt->bindParam(':nombre_sala', $nombre_sala);
    $stmt->bindParam(':motivo', $motivo);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':hora_inicio', $hora_inicio);
    $stmt->bindParam(':hora_fin', $hora_fin);
    $descripcion = "El usuario con DNI '" . htmlspecialchars($_SESSION['DNI']) . "' ha modificado la base de datos: operación INSERT sobre reservas";
    registrar_evento_log("UPDATE", $descripcion);
    return $stmt->execute();
}




function obtenerReservasPorUsuario($conexion, $dni) {
    $stmt = $conexion->prepare("
        SELECT id_reserva, nombre_sala, motivo, fecha_reserva, hora_inicio, hora_fin
        FROM reservas
        WHERE DNI = ?
        ORDER BY fecha_reserva DESC, hora_inicio ASC
    ");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $reservas = [];
    while ($fila = $resultado->fetch_assoc()) {
        $reservas[] = $fila;
    }
    return $reservas;
}



function registrar_evento_log($tipo_evento, $descripcion) {
    $conexion = conectar(); 
    if (!$conexion) {
        error_log("Error de conexión a BD al intentar registrar evento: " . $tipo_evento . " - " . $descripcion);
        return false;
    }

    $sql = "INSERT INTO logs (descripcion) VALUES (:descripcion)";

    try {
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        return $stmt->execute();

    } catch (PDOException $e) {
        error_log("Error PDO al registrar evento: " . $e->getMessage() . " Evento: " . $tipo_evento . " - " . $descripcion);
        return false;
    }
}

?>


