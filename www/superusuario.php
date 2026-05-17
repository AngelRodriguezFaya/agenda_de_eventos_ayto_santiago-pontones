<?php
// superusuario.php
session_start();

require_once '/usr/local/lib/php/vendor/autoload.php';
require_once 'bd.php';

// 1. SEGURIDAD: Solo puede entrar el superusuario
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'superusuario') {
    http_response_code(403);
    die("Error 403: Acceso denegado. Esta zona es exclusiva para la administración suprema.");
}

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$twig->addGlobal('session', $_SESSION);

// 2. PEDIR LOS USUARIOS A LA BASE DE DATOS
$lista_usuarios = BD::getTodosLosUsuarios();

// 3. RENDERIZAR LA PLANTILLA
echo $twig->render('superusuario.html', [
    'usuarios' => $lista_usuarios
]);
?>