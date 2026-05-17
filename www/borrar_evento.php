<?php
// borrar_evento.php
session_start();
require_once 'bd.php';

// 1. SEGURIDAD: Solo Gestores o Superusuarios
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'gestor' && $_SESSION['rol'] !== 'superusuario')) {
    die("Error 403: No tienes permisos.");
}

// 2. COMPROBAR ID Y BORRAR
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_evento = (int)$_GET['id'];
    BD::borrarEvento($id_evento);
}

// 3. VOLVER AL GESTOR
header("Location: gestor.php");
exit();