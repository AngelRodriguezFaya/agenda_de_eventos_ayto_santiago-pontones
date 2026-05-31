<?php
// borrar_hashtag.php
session_start();
require_once 'bd.php';

// Seguridad
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['gestor', 'superusuario'])) {
    die("Error 403: No tienes permisos.");
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if (BD::borrarHashtag($id)) {
        header("Location: gestor_hashtags.php?exito=" . urlencode("Hashtag eliminado correctamente."));
    } else {
        header("Location: gestor_hashtags.php?error=" . urlencode("Error al intentar eliminar el hashtag."));
    }
    exit();
}

header("Location: gestor_hashtags.php");
exit();
?>