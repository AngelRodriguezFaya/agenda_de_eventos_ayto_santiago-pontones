<?php
// api_busqueda_gestor.php
session_start();
require_once 'bd.php';

header('Content-Type: application/json');

// 1. SEGURIDAD: Solo gestores o superusuarios pueden acceder a esta parte (gestión de eventos)
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'gestor' && $_SESSION['rol'] !== 'superusuario')) {
    http_response_code(403); // Código 403 -> Forbidden - Acceso Prohibido
    echo json_encode(["status" => "error", "message" => "No tienes permisos"]);
    exit();
}

// 2. Solo aceptamos peticiones GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Recogemos los parámetros de búsqueda
    $termino = filter_input(INPUT_GET, 'termino', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $criterio = filter_input(INPUT_GET, 'criterio', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'titulo';
    $termino = trim($termino);

    // Llamamos al método de la base de datos para buscar eventos según el término y criterio
    $eventos = BD::getEventosFiltrados($termino, $criterio);

    echo json_encode($eventos);
} else {
    http_response_code(405); // Código 405 -> Method Not Allowed - Método no permitido
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}
?>