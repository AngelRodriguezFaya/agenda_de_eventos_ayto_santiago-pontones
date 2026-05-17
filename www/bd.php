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


    // ========================================================================
    //                      GESTIÓN DE EVENTOS
    // ========================================================================

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

    public static function getListaLocalidades() {
        $db = self::conectar();
        $res = $db->query("SELECT nombre FROM localidades");
        return $res->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Obtiene todos los hashtags asociados a un evento concreto
     */
    public static function getHashtagsEvento($evento_id) {
        $db = self::conectar();
        try {
            // Buscamos los nombres de los hashtags haciendo un JOIN con la tabla intermedia
            $stmt = $db->prepare("SELECT h.etiqueta 
                                FROM hashtags h 
                                INNER JOIN eventos_hashtags eh ON h.id = eh.hashtag_id 
                                WHERE eh.evento_id = ?");
            $stmt->execute([$evento_id]);
            
            // Con FETCH_COLUMN nos devuelve directamente un array simple de strings ['Deporte', 'Naturaleza']
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0); 
        } catch (PDOException $e) {
            error_log("Error en getHashtagsEvento: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Recupera todos los eventos, permitiendo filtrar por un término 
     * buscando en el título o en la descripción según el criterio elegido.
     * 
     * Sólo puede hacerlo el gestor o el superusuario desde el panel de gestión de eventos.
     */
    // public static function getEventosFiltrados($termino = '', $criterio = '') {
    //     $db = self::conectar();
    //     try {
    //         // Empezamos con la consulta base (traemos ID, título, fecha y categoría)
    //         $sql = "SELECT e.id, e.titulo, e.fecha_realizacion, e.descripcion, c.nombre as categoria 
    //                 FROM eventos e 
    //                 LEFT JOIN categorias c ON e.categoria_id = c.id 
    //                 WHERE 1=1";
            
    //         $parametros = [];

    //         // Si el gestor ha escrito algo en la barra de búsqueda
    //         if (!empty($termino)) {
    //             if ($criterio === 'titulo') {
    //                 $sql .= " AND e.titulo LIKE ?";
    //                 $parametros[] = "%" . $termino . "%";
    //             } elseif ($criterio === 'descripcion') {
    //                 $sql .= " AND e.descripcion LIKE ?";
    //                 $parametros[] = "%" . $termino . "%";
    //             } else {
    //                 // Por seguridad: si por algún motivo no llega ni 'titulo' ni 'descripcion',
    //                 // buscamos en ambos campos a la vez por defecto.
    //                 $sql .= " AND (e.titulo LIKE ? OR e.descripcion LIKE ?)";
    //                 $parametros[] = "%" . $termino . "%";
    //                 $parametros[] = "%" . $termino . "%";
    //             }
    //         }

    //         // Ordenamos del más reciente al más antiguo
    //         $sql .= " ORDER BY e.fecha_realizacion DESC";

    //         $stmt = $db->prepare($sql);
    //         $stmt->execute($parametros);
            
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC);

    //     } catch (PDOException $e) {
    //         error_log("Error en getEventosFiltrados: " . $e->getMessage());
    //         return [];
    //     }
    // }

    public static function getEventosFiltrados($termino = '', $criterio = '') {
        $db = self::conectar();
        try {
            // Seleccionamos todos los campos necesarios incluyendo la localidad, el cartel y el grupo de hashtags
            $sql = "SELECT e.id, e.titulo, e.fecha_realizacion, e.descripcion, 
                        c.nombre as categoria, l.nombre as localidad, g.ruta_imagen,
                        GROUP_CONCAT(h.etiqueta SEPARATOR ',') as hashtags_list
                    FROM eventos e 
                    LEFT JOIN categorias c ON e.categoria_id = c.id 
                    LEFT JOIN localidades l ON e.localidad_id = l.id
                    LEFT JOIN galeria_imagenes g ON e.id = g.evento_id AND g.es_cartel = 1
                    LEFT JOIN eventos_hashtags eh ON e.id = eh.evento_id
                    LEFT JOIN hashtags h ON eh.hashtag_id = h.id
                    WHERE 1=1";
            
            $parametros = [];

            if (!empty($termino)) {
                if ($criterio === 'titulo') {
                    $sql .= " AND e.titulo LIKE ?";
                    $parametros[] = "%" . $termino . "%";
                } elseif ($criterio === 'descripcion') {
                    $sql .= " AND e.descripcion LIKE ?";
                    $parametros[] = "%" . $termino . "%";
                } else {
                    $sql .= " AND (e.titulo LIKE ? OR e.descripcion LIKE ?)";
                    $parametros[] = "%" . $termino . "%";
                    $parametros[] = "%" . $termino . "%";
                }
            }

            // Agrupamos por TODAS las columnas seleccionadas para evitar fallos de ONLY_FULL_GROUP_BY
            $sql .= " GROUP BY e.id, e.titulo, e.fecha_realizacion, e.descripcion, c.nombre, l.nombre, g.ruta_imagen";
            $sql .= " ORDER BY e.fecha_realizacion DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($parametros);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Volvemos a poner el log normal y devolver lista vacía para no asustar al usuario final
            error_log("Error en getEventosFiltrados: " . $e->getMessage());
            return [];
        }
    }

    // ========================================================================
    //                      GESTIÓN DE COMENTARIOS
    // ========================================================================

    public static function getComentarios($evento_id) {
        $db = self::conectar();
        $stmt = $db->prepare("SELECT id, nombre, email, texto, fecha_hora FROM comentarios WHERE evento_id = ? ORDER BY fecha_hora DESC");        $stmt->execute([$evento_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Insertar un nuevo comentario en la BD
    public static function insertarComentario($evento_id, $nombre, $email, $texto) {
        $db = self::conectar();
        // fecha_hora asume valor por defecto NOW() en la BD
        // Usamos sentencias preparadas para evitar inyección SQL
        $stmt = $db->prepare("INSERT INTO comentarios (evento_id, nombre, email, texto, fecha_hora) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$evento_id, $nombre, $email, $texto]); // Devuelve true si se insertó correctamente, false si hubo error
    }

    /**
     * Borra un comentario por su ID. Solo lo pueden hacer moderadores o superusuarios.
     */
    public static function borrarComentario($comentario_id) {
        $db = self::conectar();
        try {
            $stmt = $db->prepare("DELETE FROM comentarios WHERE id = ?");
            return $stmt->execute([$comentario_id]);
        } catch (PDOException $e) {
            error_log("Error en borrarComentario: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Actualiza el texto de un comentario. Solo lo pueden hacer moderadores o superusuarios.
     */
    public static function editarComentario($id, $nuevoTexto) {
        $db = self::conectar();
        try {
            $stmt = $db->prepare("UPDATE comentarios SET texto = ? WHERE id = ?");
            return $stmt->execute([$nuevoTexto, $id]);
        } catch (PDOException $e) {
            error_log("Error al editar comentario: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Recupera todos los comentarios de la base de datos con información del evento.
     * Permite filtrar opcionalmente por texto del comentario o por nick/nombre del autor.
     */
    public static function getFiltrarComentarios($texto = '', $autor = '') {
        $db = self::conectar();
        try {
            // Hacemos un INNER JOIN para traernos también el título del evento 
            // y que el moderador sepa a qué noticia/evento corresponde cada comentario.
            $sql = "SELECT c.*, e.titulo AS evento_titulo 
                    FROM comentarios c 
                    INNER JOIN eventos e ON c.evento_id = e.id 
                    WHERE 1=1";
            
            $parametros = [];

            // Si el moderador ha escrito algo en el campo de buscar texto
            if (!empty($texto)) {
                $sql .= " AND c.texto LIKE ?";
                $parametros[] = "%" . $texto . "%";
            }

            // Si el moderador ha escrito algo en el campo de buscar autor
            if (!empty($autor)) {
                $sql .= " AND (c.nombre LIKE ? OR c.email LIKE ?)";
                $parametros[] = "%" . $autor . "%";
                $parametros[] = "%" . $autor . "%";
            }

            // Ordenamos para que salgan primero los más recientes
            $sql .= " ORDER BY c.fecha_hora DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($parametros);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en getFiltrarComentarios: " . $e->getMessage());
            return [];
        }
    }


    // ========================================================================
    //                      GESTIÓN DE USUARIOS
    // ========================================================================

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
    public static function comprobarLogin($nick, $passwordInput) {
        $db = self::conectar();

        try {
            // Paso 1: Buscar al usuario en la BD por su nick
            $stmt = $db->prepare("SELECT id, nick, email, password, rol FROM usuarios WHERE nick = ?");
            $stmt->execute([$nick]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Paso 2: Si el usuario existe y la contraseña es correcta...
            if ($usuario && password_verify($passwordInput, $usuario['password'])) {            // Devolvemos los datos del usuario (sin el hash, por seguridad)
                // Devolvemos los datos del usuario (sin el hash, por seguridad)
                unset($usuario['password']); 
                return $usuario;
            }

            return false; // Si falla la contraseña o el nick no existe
    
        }catch (PDOException $e) {
            // Si hay un error SQL lo imprimimos en el log del servidor
            error_log("Error en comprobarLogin: " . $e->getMessage());
            return false;
        }
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

    /**
     * Actualiza los datos de perfil de un usuario.
     * Si la contraseña no está vacía, la cifra y la actualiza.
     */
    public static function actualizarPerfil($id, $nick, $email, $passwordNueva = null) {
        $db = self::conectar();
        try {
            if (!empty($passwordNueva)) {
                // Si el usuario ha escrito una nueva contraseña, la ciframos con password_hash
                $hash = password_hash($passwordNueva, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET nick = ?, email = ?, password = ? WHERE id = ?");
                return $stmt->execute([$nick, $email, $hash, $id]);
            } else {
                // Si la deja en blanco, mantenemos la contraseña que ya tenía sin tocarla
                $stmt = $db->prepare("UPDATE usuarios SET nick = ?, email = ? WHERE id = ?");
                return $stmt->execute([$nick, $email, $id]);
            }
        } catch (PDOException $e) {
            error_log("Error en actualizarPerfil: " . $e->getMessage());
            return false;
        }
    }

    
}