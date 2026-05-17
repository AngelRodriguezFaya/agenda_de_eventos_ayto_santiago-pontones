<?php
// editar_usuario.php
session_start();

require_once '/usr/local/lib/php/vendor/autoload.php';
require_once 'bd.php';

// 1. SEGURIDAD: Solo Superusuario
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'superusuario') {
    http_response_code(403);
    die("Error 403: Acceso denegado.");
}

// 2. RECUPERAR USUARIO
$usuario = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_usuario = (int)$_GET['id'];
    $usuario = BD::getUsuarioCompleto($id_usuario);
}

// Si el usuario no existe, volvemos al panel
if (!$usuario) {
    header("Location: superusuario.php");
    exit();
}

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$twig->addGlobal('session', $_SESSION);

// 3. RENDERIZAR
echo $twig->render('editar_usuario.html', [
    'u' => $usuario
]);
?>