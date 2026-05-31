<?php
// borrar_usuario.php
session_start();
require_once 'bd.php';

// 1. SEGURIDAD: Solo Superusuario
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'superusuario') {
    die("Error 403: No tienes permisos.");
}

// 2. RECIBIR LA ORDEN
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_borrar = (int)$_GET['id'];
    $usuario_a_borrar = BD::getUsuarioCompleto($id_borrar);

    if ($usuario_a_borrar) {
        
        // REGLA: Si intentamos borrar a un superusuario...
        if ($usuario_a_borrar['rol'] === 'superusuario') {
            $total_supers = BD::contarSuperusuarios();
            // y solo queda 1 superusuario, abortamos el borrado
            if ($total_supers <= 1) {
                die("
                    <div class='contenedor-error-critico'>
                        <h2 class='titulo-error-critico'>🚨 ERROR CRÍTICO DE SEGURIDAD</h2>
                        <p>Eres el <strong>último superusuario</strong> del sistema. No puedes eliminar esta cuenta hasta que le des permisos de superusuario a otra persona.</p>
                        <a href='superusuario.php' class='btn-error-critico'>Volver al panel</a>
                    </div>
                ");
            }
        }

        // Si pasa las comprobaciones, procedemos a borrar al usuario de la BD
        BD::borrarUsuario($id_borrar);
        
        // Si el superusuario se ha borrado A SÍ MISMO (y había más de uno),
        // tenemos que destruir su sesión y echarlo a la portada.
        $id_sesion = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0;
        if ($id_borrar === (int)$id_sesion) {
            session_destroy();
            header("Location: index.php");
            exit();
        }
    }
}

// 3. VOLVER AL PANEL
header("Location: superusuario.php");
exit();