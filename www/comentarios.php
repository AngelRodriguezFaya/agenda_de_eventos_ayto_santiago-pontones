<?php
// comentarios.php

// Iniciamos la sesión 
session_start();

require_once '/usr/local/lib/php/vendor/autoload.php';
require_once 'bd.php';

// 1. CONTROL DE ACCESO
// Si no hay sesión o el rol no es moderador ni superusuario, le denegamos el acceso
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'moderador' && $_SESSION['rol'] !== 'superusuario')) {
    http_response_code(403);
    die("Error 403: No tienes permisos para acceder a la gestión de comentarios.");
}

// Configuración obligatoria de Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

// Pasamos la sesión de forma global para que el menú del header se pinte bien
$twig->addGlobal('session', $_SESSION);

// 2. RECOGIDA DE FILTROS PARA EL BUSCADOR
// Recogemos lo que el usuario escriba en el buscador de la cabecera de la tabla
$buscarTexto  = filter_input(INPUT_GET, 'buscar_texto', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$buscarAutor  = filter_input(INPUT_GET, 'buscar_autor', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

// 3. CONSULTA AL MODELO
// Llamamos a la función de la BDpara traernos los comentarios filtrados
$listadoComentarios = BD::getFiltrarComentarios($buscarTexto, $buscarAutor);

// 4. RENDERIZADO DE LA PLANTILLA
// Le enviamos a Twig los comentarios obtenidos y los filtros actuales para mantenerlos en los inputs
echo $twig->render('comentarios.html', [
    'comentarios'  => $listadoComentarios,
    'filtro_texto' => $buscarTexto,
    'filtro_autor' => $buscarAutor
]);