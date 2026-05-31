<?php
// editar_evento.php
session_start();

require_once '/usr/local/lib/php/vendor/autoload.php';
require_once 'bd.php';

// 1. CONTROL DE ACCESO (Solo Gestor o Superusuario)
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'gestor' && $_SESSION['rol'] !== 'superusuario')) {
    http_response_code(403);
    die("Error 403: No tienes permisos para acceder a esta sección.");
}

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$twig->addGlobal('session', $_SESSION);

// 2. CARGAR LISTAS PARA LOS DESPLEGABLES
$categorias = BD::getCategorias();
$localidades = BD::getLocalidadesDropdown(); 

// Variables por defecto (Modo "Añadir" porque no tienen datos aún)
$evento = null;
$cartel_actual = null;
$galeria_actual = null;
$hashtags_string = '';

// 3. MODO EDITAR: Recuperar los datos si existe una ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_evento = (int)$_GET['id'];
    
    $evento = BD::getEvento($id_evento);
    
    if ($evento) {
        // Preparamos el cartel en el formato que espera Twig
        if (!empty($evento['cartel'])) {
            $cartel_actual = ['ruta_imagen' => $evento['cartel']];
        }

        // Obtenemos la galería de imágenes y los hashtags asociados al evento
        $galeria_actual = BD::getGaleria($id_evento);
        $hashtags_array = BD::getHashtagsEvento($id_evento);

        // Convertimos el array de hashtags en un texto.
        $hashtags_string = implode(', ', $hashtags_array);
        
    } else {
        header("Location: gestor.php");
        exit();
    }
}

// 4. RENDERIZAR LA PLANTILLA
echo $twig->render('editar_evento.html', [
    'categorias'      => $categorias,
    'localidades'     => $localidades,
    'evento'          => $evento,
    'cartel_actual'   => $cartel_actual,
    'galeria_actual'  => $galeria_actual,
    'hashtags_string' => $hashtags_string
]);