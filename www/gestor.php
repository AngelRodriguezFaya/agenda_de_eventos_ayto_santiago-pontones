<?php
// gestor_eventos.php
session_start();

require_once '/usr/local/lib/php/vendor/autoload.php';
require_once 'bd.php';

// 1. CONTROL DE ACCESO (Gestor o Superusuario)
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'gestor' && $_SESSION['rol'] !== 'superusuario')) {
    http_response_code(403);
    die("Error 403: No tienes permisos para acceder al Panel de Gestión.");
}

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);
$twig->addGlobal('session', $_SESSION);

// 2. RECOGER PARÁMETROS DE BÚSQUEDA
// El término a buscar (si no hay nada, estará vacío)
$termino = filter_input(INPUT_GET, 'termino', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
// El criterio, que sera titulo o descripcion(por defecto buscará en el título)
$criterio = filter_input(INPUT_GET, 'criterio', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'titulo';

// 3. CONSULTA AL MODELO
$eventos = BD::getEventosFiltrados($termino, $criterio);

// 4. RENDERIZADO DE LA PLANTILLA
echo $twig->render('gestor.html', [
    'eventos'  => $eventos,
    'termino'  => $termino,
    'criterio' => $criterio
]);
?>