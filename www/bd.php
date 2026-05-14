<?php
require_once 'config.php';

class BD {
    // Atributo de clase que encapsula la conexión a la BD.
    private static $conexion = null;

    // Constructor privado para implementar Singleton
    private function __construct() {}

    // Conexión única por petición HTTP
    public static function conectar() {
        if (self::$conexion === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                self::$conexion = new PDO($dsn, DB_USER, DB_PASS);
                // Prevenir inyección de SQL y mostrar errores en modo desarrollo
                self::$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Error crítico de conexión: " . $e->getMessage());
            }
        }
        return self::$conexion;
    }

    // Obtener todos los eventos para la portada
    public static function getEventos() {
        $db = self::conectar();
        $res = $db->query("SELECT e.id, e.titulo, e.fecha_realizacion, g.ruta_imagen, c.nombre as categoria 
                            FROM eventos e 
                            LEFT JOIN galeria_imagenes g ON e.id = g.evento_id AND g.es_cartel = 1 
                            LEFT JOIN categorias c ON e.categoria_id = c.id
                            ORDER BY e.fecha_realizacion DESC");
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un evento específico con su localidad y categoría (JOIN)
    // ADEMÁS nos traemos el cartel (es_cartel = 1)
    public static function getEvento($id) {
        $db = self::conectar();
        $stmt = $db->prepare("SELECT e.*, l.nombre as localidad, c.nombre as categoria, g.ruta_imagen as cartel 
                            FROM eventos e 
                            LEFT JOIN localidades l ON e.localidad_id = l.id 
                            LEFT JOIN categorias c ON e.categoria_id = c.id 
                            LEFT JOIN galeria_imagenes g ON e.id = g.evento_id AND g.es_cartel = 1
                            WHERE e.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getGaleria($evento_id) {
        $db = self::conectar();
        $stmt = $db->prepare("SELECT ruta_imagen, descripcion FROM galeria_imagenes WHERE evento_id = ? AND es_cartel = 0");
        $stmt->execute([$evento_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getComentarios($evento_id) {
        $db = self::conectar();
        $stmt = $db->prepare("SELECT nombre, email, texto, fecha_hora FROM comentarios WHERE evento_id = ? ORDER BY fecha_hora DESC");
        $stmt->execute([$evento_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Para el ejercicio de censura de localidades en el JS
    public static function getListaLocalidades() {
        $db = self::conectar();
        $res = $db->query("SELECT nombre FROM localidades");
        return $res->fetchAll(PDO::FETCH_COLUMN);
    }

    // Insertar un nuevo comentario en la BD
    public static function insertarComentario($evento_id, $nombre, $email, $texto) {
        $db = self::conectar();
        // fecha_hora asume valor por defecto NOW() en la BD
        // Usamos sentencias preparadas para evitar inyección SQL
        $stmt = $db->prepare("INSERT INTO comentarios (evento_id, nombre, email, texto, fecha_hora) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$evento_id, $nombre, $email, $texto]); // Devuelve true si se insertó correctamente, false si hubo error
    }

    // =====================================================================================================
    //              GESTIÓN DE USUARIOS
    // =====================================================================================================

    /**
     * Registra un nuevo usuario en la BD con su nick, email y contraseña.
     * La contraseña se almacena como un hash seguro usando password_hash().
     * Devuelve true si el registro fue exitoso, false si hubo un error (por ejemplo, nick o email ya existe).
     */
    public static function registrarUsuario($nick, $email, $password) {
        $db = self::conectar();
        
        // Creamos el hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $db->prepare("INSERT INTO usuarios (nick, email, password_hash) VALUES (?, ?, ?)");
            return $stmt->execute([$nick, $email, $password_hash]);
        } catch (PDOException $e) {
            return false; 
        }
    }

    /**
     * Comprueba si existe un usuario con esas credenciales en la BD.
     * 
     * Si el usuario existe y la contraseña es correcta, devuelve un array con los datos del usuario (id, nick, email).
     * Si no existe, devuelve false.
     */
    public static function comprobarLogin($nick, $password) {
        $db = self::conectar();
        
        // Paso 1: Buscar al usuario en la BD por su nick
        $stmt = $db->prepare("SELECT id, nick, email, password_hash FROM usuarios WHERE nick = ?");
        $stmt->execute([$nick]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Paso 2: Si el usuario existe y la contraseña es correcta...
        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            // Devolvemos los datos del usuario (sin el hash, por seguridad)
            unset($usuario['password_hash']); 
            return $usuario;
        }

        return false;
    }

    /**
     * Devuelve los datos de un usuario por su ID.
     */
    public static function getUsuario($id) {
        $db = self::conectar();
        $stmt = $db->prepare("SELECT id, nick, email FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}