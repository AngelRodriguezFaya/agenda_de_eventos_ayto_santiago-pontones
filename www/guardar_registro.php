<?php
// guardar_registro.php
session_start();
require_once 'bd.php';

// Redirigir si ya está logueado
if (isset($_SESSION['rol'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nick = trim($_POST['nick'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // 1. Comprobar campos vacíos
    if (empty($nick) || empty($email) || empty($password) || empty($password_confirm)) {
        header("Location: registro.php?error=" . urlencode("Todos los campos son obligatorios."));
        exit();
    }

    // 2. Comprobar que las contraseñas coinciden
    if ($password !== $password_confirm) {
        header("Location: registro.php?error=" . urlencode("Las contraseñas no coinciden."));
        exit();
    }

    // 3. Comprobar si el usuario ya existe en la Base de Datos
    if (BD::existeUsuario($nick, $email)) {
        header("Location: registro.php?error=" . urlencode("El Nick o el Email ya están registrados en el sistema. Prueba con otros diferentes."));
        exit();
    }

    // 4. Proceder con el registro
    if (BD::registrarUsuario($nick, $email, $password)) {
        header("Location: registro.php?exito=" . urlencode("¡Cuenta creada con éxito! Ya puedes acceder a la plataforma."));
        exit();
    } else {
        header("Location: registro.php?error=" . urlencode("Error interno del servidor al crear la cuenta. Inténtalo más tarde."));
        exit();
    }
}

// Si alguien intenta entrar a este archivo directamente sin pasar por POST, lo devolvemos
header("Location: registro.php");
exit();
?>