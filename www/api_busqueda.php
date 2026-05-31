<?php
// api_busqueda.php
require_once 'bd.php';

header('Content-Type: application/json');

// Solo aceptamos peticiones GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Recogemos y validamos el término de búsqueda pasado como parámetro "q" en la URL
    $termino = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $termino = trim($termino);

    // Si el término tiene menos de 2 caracteres, no buscamos nada
    if (strlen($termino) < 2) {
        echo json_encode([]);
        exit();
    }

    // Buscamos en la BD solo eventos PUBLICADOS que coincidan con el título
    $eventos = BD::buscarEventosPublicados($termino);

    echo json_encode($eventos);

} else {
    http_response_code(405); // Código 405 -> Method Not Allowed - Método no permitido
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}
?>