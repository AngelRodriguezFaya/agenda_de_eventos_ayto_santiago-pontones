<?php
// registro.php
session_start();

require_once '/usr/local/lib/php/vendor/autoload.php';
require_once 'bd.php';

// Si un usuario ya ha iniciado sesión, no tiene sentido que se registre de nuevo
if (isset($_SESSION['rol'])) {
    header("Location: index.php");
    exit();
}

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$twig->addGlobal('session', $_SESSION);

// Recogemos los posibles mensajes de exito o error para pasarlos a Twig
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
$exito = isset($_GET['exito']) ? htmlspecialchars($_GET['exito']) : null;

echo $twig->render('registro.html', [
    'error' => $error,
    'exito' => $exito
]);
?>