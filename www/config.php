<?php
// config.php - Configuración centralizada de la base de datos

// Host de la base de datos. 
define('DB_HOST', 'database');

// Usuario con privilegios en nuestra BD
define('DB_USER', 'sibwuser');

// Contraseña del usuario
define('DB_PASS', 'sibwpass');

// El nombre de la base de datos
define('DB_NAME', 'SIBW_AGENDA_EVENTOS');

// Juego de caracteres para no tener problemas con caracteres especiales
define('DB_CHARSET', 'utf8mb4');

// (Opcional) URL Base para facilitarte la creación de enlaces en Twig
// Sirve para que no se te rompan las imágenes, los enlaces ni los estilos 
// cuando pasemos de la portada (/) a la vista de un evento (/evento/1).
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
define('BASE_URL', $protocol . $_SERVER['HTTP_HOST'] . '/');
?>