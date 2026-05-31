<?php
session_start(); // Iniciar sesión para poder usar variables de sesión
session_unset(); // Eliminar todas las variables de sesión
session_destroy(); // Destruir la sesión

// Avisamos a JavaScript que la operación ha sido un éxito
header('Content-Type: application/json');
echo json_encode(["status" => "success"]);
?>
