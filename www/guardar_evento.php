<?php
// guardar_evento.php
session_start();
require_once 'bd.php';

// 1. SEGURIDAD: Solo Gestores o Superusuarios pueden guardar eventos
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'gestor' && $_SESSION['rol'] !== 'superusuario')) {
    die("Error: No tienes permisos para realizar esta acción.");
}

// Comprobamos que los datos vengan por POST (que se haya pulsado el botón del formulario)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. RECOGER Y LIMPIAR DATOS DE TEXTO
    $id_evento    = isset($_POST['id_evento']) && is_numeric($_POST['id_evento']) ? (int)$_POST['id_evento'] : null;
    $titulo       = htmlspecialchars($_POST['titulo'] ?? '');
    $fecha        = htmlspecialchars($_POST['fecha_realizacion'] ?? '');
    $categoria_id = (int)($_POST['categoria_id'] ?? 0);
    $localidad_id = (int)($_POST['localidad_id'] ?? 0);
    $descripcion  = htmlspecialchars($_POST['descripcion'] ?? '');
    $hashtags     = htmlspecialchars($_POST['hashtags'] ?? '');

    // 3. INSERTAR O ACTUALIZAR EL EVENTO EN LA BASE DE DATOS, dependiendo de si $id_evento tiene un valor o no
    if ($id_evento) {
        // MODO EDITAR
        BD::actualizarEvento($id_evento, $titulo, $descripcion, $fecha, $categoria_id, $localidad_id);
    } else {
        // MODO CREAR
        $id_evento = BD::insertarEvento($titulo, $descripcion, $fecha, $categoria_id, $localidad_id);
        if (!$id_evento) {
            die("Error crítico: No se pudo crear el evento en la base de datos.");
        }
    }

    // 4. ACTUALIZAR LOS HASHTAGS
    BD::actualizarHashtags($id_evento, $hashtags);

    // 5. BORRAR IMÁGENES DE LA GALERÍA (Si el gestor marcó algún checkbox de "Borrar")
    if (isset($_POST['borrar_imagen']) && is_array($_POST['borrar_imagen'])) {
        foreach ($_POST['borrar_imagen'] as $id_imagen_borrar) {
            BD::borrarImagenPorId((int)$id_imagen_borrar);
        }
    }

    // 6. GESTIÓN DE SUBIDA DE ARCHIVOS FÍSICOS AL SERVIDOR
    
    // Creamos una carpeta 'uploads' dentro de 'images' si no existe
    $directorio_destino = 'images/uploads/';
    if (!file_exists($directorio_destino)) {
        mkdir($directorio_destino, 0777, true);
    }

    // A) SUBIDA DEL CARTEL (Solo 1 archivo)
    if (isset($_FILES['cartel']) && $_FILES['cartel']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = $_FILES['cartel']['name'];
        $archivo_temporal = $_FILES['cartel']['tmp_name'];
        
        // Generamos un nombre único para que no se sobreescriban carteles con el mismo nombre
        $nombre_unico = time() . "_cartel_" . basename($nombre_archivo);
        $ruta_final = $directorio_destino . $nombre_unico;

        // Movemos el archivo de la memoria temporal a nuestra carpeta
        if (move_uploaded_file($archivo_temporal, $ruta_final)) {
            // Guardamos la ruta en la base de datos (es_cartel = 1)
            BD::guardarImagenEvento($ruta_final, $id_evento, 1);
        }
    }

    // B) SUBIDA DE LA GALERÍA (Múltiples archivos)
    // Al ser un input 'multiple', $_FILES['galeria']['name'] es un Array
    if (isset($_FILES['galeria']) && !empty($_FILES['galeria']['name'][0])) {
        
        $total_imagenes = count($_FILES['galeria']['name']);
        
        for ($i = 0; $i < $total_imagenes; $i++) {
            // Comprobamos que esta imagen en concreto no tenga errores
            if ($_FILES['galeria']['error'][$i] === UPLOAD_ERR_OK) {
                
                $nombre_archivo = $_FILES['galeria']['name'][$i];
                $archivo_temporal = $_FILES['galeria']['tmp_name'][$i];
                
                $nombre_unico = time() . "_" . $i . "_galeria_" . basename($nombre_archivo);
                $ruta_final = $directorio_destino . $nombre_unico;

                // Movemos el archivo y guardamos en BD (es_cartel = 0)
                if (move_uploaded_file($archivo_temporal, $ruta_final)) {
                    BD::guardarImagenEvento($ruta_final, $id_evento, 0);
                }
            }
        }
    }

    // 7. REDIRECCIÓN FINAL AL PANEL DEL GESTOR
    // Una vez terminado todo, devolvemos al usuario al panel para que vea los cambios
    header("Location: gestor.php");
    exit();
} else {
    // Si alguien intenta entrar a este archivo escribiendo la URL sin pasar por el formulario
    header("Location: gestor.php");
    exit();
}