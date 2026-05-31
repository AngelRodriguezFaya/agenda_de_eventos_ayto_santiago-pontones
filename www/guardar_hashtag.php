<?php
// guardar_hashtag.php
session_start();
require_once 'bd.php';

// 1. SEGURIDAD: Solo gestores o superusuarios pueden añadir hashtags
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'gestor' && $_SESSION['rol'] !== 'superusuario')) {
    http_response_code(403);
    die("Error 403: No tienes permisos para realizar esta acción.");
}

// 2. COMPROBAR QUE LLEGA POR POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recogemos el valor del input name="etiqueta"
    // Usamos trim() para quitar espacios en blanco antes y después, y limpiamos posibles caracteres extraños
    $etiqueta = isset($_POST['etiqueta']) ? trim($_POST['etiqueta']) : '';

    // 3. VALIDACIÓN
    if (!empty($etiqueta)) {
        // ltrim elimina el '#' del principio si el usuario lo llegó a escribir
        $etiqueta_limpia = ltrim($etiqueta, '#'); 
        
        // Sanitizamos la cadena limpia (ej: "Deporte", NO "#Deporte")
        $etiqueta_final = htmlspecialchars($etiqueta_limpia, ENT_QUOTES, 'UTF-8');
        
        // Pasamos a la BD la palabra limpia
        BD::insertarHashtag($etiqueta_final);
    }
}

// 4. Redireccionar de vuelta al gestor para que se refresque la lista
header("Location: gestor_hashtags.php");
exit();
?>