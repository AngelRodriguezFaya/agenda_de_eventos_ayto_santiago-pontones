<?php
session_start();
require_once 'bd.php'; // Archivo del Modelo

// Indicamos que responderemos en formato JSON
// en lugar de una pagina HTML, para que el cliente (JS) pueda procesarlo
// y mostrar mensajes de error o redirigir al usuario sin recargar la página
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leemos el JSON que nos manda el fetch
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $nick = $data['nick'] ?? '';
    $password = $data['password'] ?? '';

    // Validamos que se hayan recibido ambos campos
    if ($nick && $password) {
        // Obtenemos el usuario de la BD
        $usuario = BD::comprobarLogin($nick, $password); 

        // Si el usuario existe (el nombre y la contraseña son correctos) 
        if ($usuario) {
            
            // Creamos las variables de sesión 
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nick'] = $usuario['nick'];
            $_SESSION['rol'] = $usuario['rol'];

            echo json_encode(["status" => "success"]);
        } else {
            http_response_code(401); // Error de no autorizado
            echo json_encode(["status" => "error", "mensaje" => "Credenciales incorrectas"]);
        }
    } else {
        http_response_code(400); // Bad request
        echo json_encode(["status" => "error", "mensaje" => "Faltan datos"]);
    }
}