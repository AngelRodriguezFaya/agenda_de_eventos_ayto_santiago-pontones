<?php
// API para recibir los comentarios desde JavaScript

require_once 'bd.php'; // Usa nuestra clase singleton PDO
require_once 'vendor/autoload.php';

// Verificamos que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leemos el payload en formato JSON (fetch manda un stream JSON, no datos nativos de formulario por defecto)
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if ($data) {
        $evento_id = filter_var($data['evento_id'], FILTER_VALIDATE_INT);
        $nombre = filter_var($data['nombre'], FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $texto = filter_var($data['texto'], FILTER_SANITIZE_SPECIAL_CHARS);

        if ($evento_id && $nombre && filter_var($email, FILTER_VALIDATE_EMAIL) && $texto) {
            
            try {
                // Guardar en Base de Datos usando nuestro modelo PDO
                BD::insertarComentario($evento_id, $nombre, $email, $texto);
                
                // Devolvemos confirmación
                header('Content-Type: application/json');
                echo json_encode(["status" => "success"]);
            } catch (Exception $e) {
                // Algo falló con la base de datos
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Error interno en la Base de Datos."]);
            }

        } else {
            // Fallo en los parámetros pasados
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Datos de formulario inválidos."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "No se recibió un JSON válido."]);
    }
} else {
    // Solo manejamos POST
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}
?>
