<?php
session_start();
require_once 'bd.php';

header('Content-Type: application/json');

// 1. Comprobamos que el usuario tiene permisos (es moderador o superusuario)
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'moderador' && $_SESSION['rol'] !== 'superusuario')) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permisos para editar.']);
    exit();
}

// 2. RECIBIR LOS DATOS: recibimos el ID del comentario y el nuevo texto que nos manda JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$id_comentario = $data['id'] ?? null;
$texto_nuevo = $data['texto'] ?? null;

// 3. EJECUTAR Y RESPONDER: Si tenemos ambos datos, editamos el comentario en la base de datos
if ($id_comentario && $texto_nuevo) {
    
    // IMPORTANTE: Limpiamos el comentario por si ya había sido editado antes, para no duplicar la firma
    $texto_limpio = preg_replace('/\n\n\*\(Editado por moderador .*\)\*/', '', $texto_nuevo);
    
    // Ponemos la firma
    $firma = "\n\n*(Editado por moderador " . $_SESSION['nick'] . ")*";
    $texto_final = $texto_limpio . $firma; // Concatenamos el texto limpio con la firma del moderador

    // Guardamos en la base de datos
    if (BD::editarComentario($id_comentario, $texto_final)) {
        // Devolvemos el texto final para que Javascript actualice el DOM
        echo json_encode(['exito' => true, 'texto_actualizado' => $texto_final]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar en la base de datos.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos.']);
}