<?php
// guardar_usuario.php
session_start();
require_once 'bd.php';

// 1. SEGURIDAD: Solo Superusuario
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'superusuario') {
    die("Error: No tienes permisos.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. RECOGER DATOS
    $id_usuario = isset($_POST['id_usuario']) && is_numeric($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : null;
    $nick       = htmlspecialchars($_POST['nick'] ?? '');
    $email      = htmlspecialchars($_POST['email'] ?? '');
    $rol        = htmlspecialchars($_POST['rol'] ?? 'registrado');
    $password   = $_POST['password'] ?? ''; // La contraseña no se limpia con htmlspecialchars porque altera el hash

    if ($id_usuario) {
        // 3. ACTUALIZAR EN BD
        BD::actualizarUsuarioPorSuperusuario($id_usuario, $nick, $email, $rol, $password);
        
        // Comprobamos si la ID existe en la sesión (ya sea como 'id_usuario' o como 'id')
        $id_sesion = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0;
        
        if ($id_sesion > 0 && $id_usuario === (int)$id_sesion) {
            $_SESSION['nick'] = $nick;
            $_SESSION['rol']  = $rol;
        }
    }
}

// 4. REDIRECCIÓN DE VUELTA AL PANEL
header("Location: superusuario.php");
exit();
?>