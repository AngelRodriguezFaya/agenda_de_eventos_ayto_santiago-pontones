<?php

// Iniciamos las sesiones para manejar el estado del usuario
session_start();

require_once '/usr/local/lib/php/vendor/autoload.php';
require_once 'bd.php'; // Incluimos la clase BD

// 1. Configuración de Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

// Pasamos la sesión como una variable global a Twig para no
// tener que pasarla en cada renderizado
$twig->addGlobal('session', $_SESSION);

// 2. Validación y Sanitización de entradas
// filter_input nos asegura que recibimos un entero o nos devuelve false/null
$evento_id = filter_input(INPUT_GET, 'ev', FILTER_VALIDATE_INT);
// Verificamos si la variable 'print' existe y es "true"
$imprimir = filter_input(INPUT_GET, 'print', FILTER_VALIDATE_BOOLEAN);

// 3. Enrutador Lógico
if ($evento_id !== null && $evento_id !== false) {
    // ESTAMOS EN LA VISTA DE DETALLE DE UN EVENTO
    $evento = BD::getEvento($evento_id);

    if ($evento) {
        $galeria = BD::getGaleria($evento_id);
        $comentarios = BD::getComentarios($evento_id);
        $localidades = BD::getListaLocalidades(); // Es necesario para el script JS
        $hashtags = BD::getHashtagsEvento($evento_id); 

        // Elegimos la plantilla según si se ha pedido la versión para imprimir o no
        $plantilla = $imprimir ? 'evento_imprimir.html' : 'evento.html';

        echo $twig->render($plantilla, [
            'evento' => $evento,
            'galeria' => $galeria,
            'comentarios' => $comentarios,
            'localidades' => $localidades,
            'hashtags' => $hashtags
        ]);
    } else {
        // En caso de que el usuario ponga un ID que no existe (ej: /evento/999)
        http_response_code(404);
        echo "Error 404: El evento no existe."; 
    }

} else {
    // ESTAMOS EN LA VISTA DE PORTADA (No hay ID de evento)
    $eventos = BD::getEventos();
    
    // Renderizamos la plantilla de portada con la lista de eventos
    echo $twig->render('portada.html', [
        'eventos' => $eventos
    ]);
}
?>