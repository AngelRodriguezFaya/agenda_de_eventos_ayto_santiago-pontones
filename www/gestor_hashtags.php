<?php
// gestor_hashtags.php
session_start();

require_once '/usr/local/lib/php/vendor/autoload.php';
require_once 'bd.php';

// 1. SEGURIDAD: Solo Gestor y Superusuario
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['gestor', 'superusuario'])) {
    http_response_code(403);
    die("Error 403: Acceso denegado. Solo gestores y superusuarios pueden administrar hashtags.");
}

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$twig->addGlobal('session', $_SESSION);

// 2. Cargar datos y posibles mensajes
$hashtags = BD::getTodosLosHashtags();
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
$exito = isset($_GET['exito']) ? htmlspecialchars($_GET['exito']) : null;

// 3. Renderizar
echo $twig->render('gestor_hashtags.html', [
    'hashtags' => $hashtags,
    'error' => $error,
    'exito' => $exito
]);
?>