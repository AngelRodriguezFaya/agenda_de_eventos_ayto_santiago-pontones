<?php
// api_visibilidad.php
session_start();
require_once 'bd.php';

// 1. SEGURIDAD: Solo gestores o superusuarios pueden cambiar la visibilidad
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'gestor' && $_SESSION['rol'] !== 'superusuario')) {
    http_response_code(403); // Código 403 -> Forbidden - Acceso Prohibido
    echo json_encode(["status" => "error", "message" => "No tienes permisos"]);
    exit();
}

// 2. Solo aceptamos peticiones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leemos el JSON que nos manda el fetch
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Si data es correcto y tiene el id del evento y el nuevo estado, 
    // entonces validamos y llamamos al método de la base de datos para actualizar el estado de publicación
    if ($data && isset($data['id_evento']) && isset($data['estado'])) {

        // Id_evento: Forzar conversión a entero y validar que sean números válidos 
        $id_evento = filter_var((int)$data['id_evento'], FILTER_VALIDATE_INT);

        // Estado: Forzar conversión a entero y validar que sea 0 o 1 (publicado o no publicado)
        $estado = in_array($data['estado'], [0, 1, "0", "1"], true) ? (int)$data['estado'] : false;
        
        if ($id_evento !== false && $estado !== false) {
            // Llamamos al método que de bd.php
            $exito = BD::cambiarEstadoPublicacion($id_evento, $estado);

            if ($exito) {
                header('Content-Type: application/json');
                echo json_encode(["status" => "success"]);
            } else {
                http_response_code(500); // Código 500 -> Internal Server Error - Error Interno del Servidor
                echo json_encode(["status" => "error", "message" => "Error al actualizar la base de datos."]);
            }
        } else {
            http_response_code(400); // Código 400 -> Bad Request - Petición Incorrecta
            echo json_encode(["status" => "error", "message" => "Datos inválidos."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "JSON incompleto."]);
    }
} else {
    http_response_code(405); // Código 405 -> Method Not Allowed - Método no permitido
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}
?>