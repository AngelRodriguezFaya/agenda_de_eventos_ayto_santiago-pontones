<?php
// Iniciamos la sesión para acceder a $_SESSION
session_start();

require_once '/usr/local/lib/php/vendor/autoload.php';
require_once 'bd.php';

// 1. CONTROL DE ACCESO: Si no ha inciado sesión, denegamos el acceso
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Configuración de Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

// Pasamos la sesión como una variable global a Twig
$twig->addGlobal('session', $_SESSION);

// Icializamos las variables que usaremos
$id_usuario = $_SESSION['id'];
$error = null; // Guarda un mensaje de error si algo falla
$exito = null; // Guarda un mensaje de éxito si la actualización se hizo correctamente

// 2. PROCESAMIENTO DEL FORMULARIO (si se ha enviado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizamos las entradas del formulario
    $nick = filter_input(INPUT_POST, 'nick', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? ''; // La contraseña se valida con estos filtros porque eliminaria caracteres especialesç

    if(!$nick || !$email) {
        $error = "Error, introduce un nombre de usuario y un correo electrónico válidos.";
    } else {
        // Guardamos los cambios en la BD
        $resultado = BD::actualizarPerfil($id_usuario, $nick, $email, $password);

        if($resultado){
            $exito = true;
            // Actualizamos la variable de sesión con el nuevo nick (si se ha cambiado)
            $_SESSION['nick'] = $nick;
        } else {
            $error = "Error al actualizar el perfil. Es posible que el nick o el email ya estén en uso por otro usuario.";
        }
    }
}

// 3. RENDERIZAMOS LA PLANTILLA DE PERFIL, PASÁNDOLE LOS NUEVOS DATOS 
$datosUsuario = BD::getUsuario($id_usuario); // Obtenemos los datos actualizados del usuario

// Renderizamos la plantilla de perfil, pasándole las variables de control.
echo $twig->render('perfil.html', [
    'usuario' => $datosUsuario,
    'error' => $error,
    'exito' => $exito
]);

?>