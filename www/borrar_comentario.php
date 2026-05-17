<?php
session_start();
require_once 'bd.php';

header('Content-Type: application/json');

// 1. Comprobamos que el usuario tiene permisos
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'moderador' && $_SESSION['rol'] !== 'superusuario')) {
    // Si no es moderador o superusuario, no puede pasar (Error 403 Prohibido)
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para realizar esta acción.']);
    exit();
}

// 2. RECIBIR LOS DATOS: Leemos el ID que nos manda JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$id_comentario = $data['id'] ?? null;

// 3. EJECUTAR Y RESPONDER
if ($id_comentario && BD::borrarComentario($id_comentario)) {
    echo json_encode(['exito' => true]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No se pudo borrar el comentario.']);
}

?>