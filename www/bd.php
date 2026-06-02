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

    /**
     * Obtener todos los eventos para la portada.
     * Sólo se muestran los eventos publicados (publicado = 1) y se ordenan por fecha de realización descendente.
     */
    public static function getEventos() {
        $db = self::conectar();
        $res = $db->query("SELECT e.id, e.titulo, e.fecha_realizacion, g.ruta_imagen, c.nombre as categoria 
                            FROM eventos e 
                            LEFT JOIN galeria_imagenes g ON e.id = g.evento_id AND g.es_cartel = 1 
                            LEFT JOIN categorias c ON e.categoria_id = c.id
                            WHERE e.publicado = 1
                            ORDER BY e.fecha_realizacion DESC");
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener un evento específico con su localidad y categoría (JOIN).
     * ADEMÁS nos traemos el cartel (es_cartel = 1).
     */
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

    /**
     * Busca eventos publicados cuyo título coincida con el término.
     * Solo devuelve id y título, que es lo que necesita el desplegable de la portada.
     */
    public static function buscarEventosPublicados($termino) {
        $db = self::conectar();
        try {
            $stmt = $db->prepare("SELECT id, titulo 
                                FROM eventos 
                                WHERE publicado = 1 
                                AND titulo LIKE ? 
                                ORDER BY fecha_realizacion DESC
                                LIMIT 8");
            $stmt->execute(['%' . $termino . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en buscarEventosPublicados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todas las imágenes de la galería de un evento (es_cartel = 0). 
     * Devuelve un array con id, ruta_imagen y descripción de cada imagen.
     */
    public static function getGaleria($evento_id) {
        $db = self::conectar();
        $stmt = $db->prepare("SELECT id, ruta_imagen, descripcion 
                            FROM galeria_imagenes 
                            WHERE evento_id = ? 
                            AND es_cartel = 0");
        $stmt->execute([$evento_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las localidades disponibles para mostrar en la portada 
     */
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
     * Obtiene los eventos filtrados por título, descripción o hashtag. 
     * Si no se especifica ningún término, devuelve todos los eventos.
     */
    public static function getEventosFiltrados($termino = '', $criterio = '') {
        $db = self::conectar();
        try {
            // Seleccionamos todos los campos necesarios incluyendo la localidad, el cartel y el grupo de hashtags
            $sql = "SELECT e.id, e.titulo, e.fecha_realizacion, e.descripcion, e.publicado, 
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
                } elseif ($criterio === 'hashtag') {
                    // SUBCONSULTA: Buscamos si el ID del evento está en la lista de eventos que tienen este hashtag
                    $sql .= " AND e.id IN (
                                SELECT eh2.evento_id 
                                FROM eventos_hashtags eh2 
                                JOIN hashtags h2 ON eh2.hashtag_id = h2.id 
                                WHERE h2.etiqueta LIKE ?
                                )";
                    $parametros[] = "%" . $termino . "%";
                } else {
                    $sql .= " AND (e.titulo LIKE ? OR e.descripcion LIKE ?)";
                    $parametros[] = "%" . $termino . "%";
                    $parametros[] = "%" . $termino . "%";
                }
            }

            // Agrupamos por TODAS las columnas seleccionadas para evitar fallos de ONLY_FULL_GROUP_BY
            $sql .= " GROUP BY e.id, e.titulo, e.fecha_realizacion, e.descripcion, e.publicado, c.nombre, l.nombre, g.ruta_imagen";
            $sql .= " ORDER BY e.fecha_realizacion DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($parametros);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en getEventosFiltrados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todas las categorías disponibles para el desplegable del Gestor (id y nombre)
     */
    public static function getCategorias() {
        $db = self::conectar();
        $res = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las localidades para el desplegable del Gestor (id y nombre)
     */
    public static function getLocalidadesDropdown() {
        $db = self::conectar();
        $res = $db->query("SELECT id, nombre FROM localidades ORDER BY nombre ASC");
        return $res->fetchAll(PDO::FETCH_ASSOC); 
    }

    /**
     * Inserta un nuevo evento en la base de datos y devuelve su ID generada.
     */
    public static function insertarEvento($titulo, $descripcion, $fecha, $categoria_id, $localidad_id) {
        $db = self::conectar();
        $stmt = $db->prepare("INSERT INTO eventos (titulo, descripcion, fecha_realizacion, categoria_id, localidad_id) 
                            VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$titulo, $descripcion, $fecha, $categoria_id, $localidad_id])) {
            return $db->lastInsertId(); // Devolvemos el ID del evento recién creado
        }
        return false;
    }

    /**
     * Actualiza un evento existente.
     */
    public static function actualizarEvento($id, $titulo, $descripcion, $fecha, $categoria_id, $localidad_id) {
        $db = self::conectar();
        $stmt = $db->prepare("UPDATE eventos 
                            SET titulo = ?, descripcion = ?, fecha_realizacion = ?, categoria_id = ?, localidad_id = ? 
                            WHERE id = ?");
        return $stmt->execute([$titulo, $descripcion, $fecha, $categoria_id, $localidad_id, $id]);
    }

    /**
     * Guarda la ruta de una imagen en la galería. 
     * Si es un cartel (es_cartel=1), primero borra el cartel anterior.
     */
    public static function guardarImagenEvento($ruta_imagen, $evento_id, $es_cartel = 0) {
        $db = self::conectar();
        
        if ($es_cartel == 1) {
            // Borramos el registro del cartel viejo para que solo haya 1 cartel por evento
            $stmtDel = $db->prepare("DELETE FROM galeria_imagenes WHERE evento_id = ? AND es_cartel = 1");
            $stmtDel->execute([$evento_id]);
        }
        
        $stmt = $db->prepare("INSERT INTO galeria_imagenes (ruta_imagen, evento_id, es_cartel) VALUES (?, ?, ?)");
        return $stmt->execute([$ruta_imagen, $evento_id, $es_cartel]);
    }

    /**
     * Elimina una imagen concreta de la galería (físicamente del disco duro y de la BD).
     */
    public static function borrarImagenPorId($id_imagen) {
        $db = self::conectar();
        
        // Primero recuperamos la ruta para borrar el archivo de la carpeta
        $stmt = $db->prepare("SELECT ruta_imagen FROM galeria_imagenes WHERE id = ?");
        $stmt->execute([$id_imagen]);
        $img = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($img) {
            // Si el archivo existe físicamente en el servidor, lo borramos (unlink)
            if (file_exists($img['ruta_imagen'])) {
                unlink($img['ruta_imagen']); 
            }
            // Borramos el registro de la tabla
            $stmtDel = $db->prepare("DELETE FROM galeria_imagenes WHERE id = ?");
            return $stmtDel->execute([$id_imagen]);
        }
        return false;
    }

    /**
     * Sincroniza los hashtags de un evento. Borra los viejos y mete los nuevos.
     */
    public static function actualizarHashtags($evento_id, $cadena_hashtags) {
        $db = self::conectar();
        
        // 1. Borramos las relaciones anteriores de este evento
        $stmt = $db->prepare("DELETE FROM eventos_hashtags WHERE evento_id = ?");
        $stmt->execute([$evento_id]);

        if (empty(trim($cadena_hashtags))) return true;

        // 2. Procesamos la cadena (ej: "Música, Fiesta, Cultura")
        $tags = explode(',', $cadena_hashtags);
        foreach ($tags as $tag) {
            $tag = trim($tag); // Quitamos espacios sobrantes
            if (empty($tag)) continue;

            // 3. Verificamos si la etiqueta ya existe en la tabla `hashtags`
            $stmtBusca = $db->prepare("SELECT id FROM hashtags WHERE etiqueta = ?");
            $stmtBusca->execute([$tag]);
            $hashtagRow = $stmtBusca->fetch(PDO::FETCH_ASSOC);

            if ($hashtagRow) {
                $hashtag_id = $hashtagRow['id'];
            } else {
                // Si no existe, la insertamos como un nuevo hashtag en el sistema
                $stmtIns = $db->prepare("INSERT INTO hashtags (etiqueta) VALUES (?)");
                $stmtIns->execute([$tag]);
                $hashtag_id = $db->lastInsertId();
            }

            // 4. Creamos la nueva relación en la tabla intermedia
            $stmtVinculo = $db->prepare("INSERT INTO eventos_hashtags (evento_id, hashtag_id) VALUES (?, ?)");
            $stmtVinculo->execute([$evento_id, $hashtag_id]);
        }
        return true;
    }

    /**
     * Elimina un evento por completo: borra físicamente sus imágenes del servidor 
     * y luego elimina el registro de la base de datos.
     */
    public static function borrarEvento($id_evento) {
        $db = self::conectar();
        try {
            // 1. Buscamos todas las imágenes asociadas a este evento (cartel y galería)
            $stmt = $db->prepare("SELECT ruta_imagen FROM galeria_imagenes WHERE evento_id = ?");
            $stmt->execute([$id_evento]);
            $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 2. Recorremos las imágenes y las borramos del disco duro del servidor
            foreach ($imagenes as $img) {
                if (!empty($img['ruta_imagen']) && file_exists($img['ruta_imagen'])) {
                    unlink($img['ruta_imagen']); 
                }
            }
            
            // 3. Borramos el evento. (El ON DELETE CASCADE de MySQL borrará el resto de tablas)
            $stmtDel = $db->prepare("DELETE FROM eventos WHERE id = ?");
            return $stmtDel->execute([$id_evento]);
            
        } catch (PDOException $e) {
            error_log("Error al borrar evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia el estado de publicación de un evento (publicado = 1 o 0). Solo lo pueden hacer gestores o superusuarios.
     */
    public static function cambiarEstadoPublicacion($id_evento, $nuevo_estado) {
        $db = self::conectar();
        try {
            $stmt = $db->prepare("UPDATE eventos SET publicado = ? WHERE id = ?");
            return $stmt->execute([$nuevo_estado, $id_evento]);
        } catch (PDOException $e) {
            error_log("Error en cambiarEstadoPublicacion: " . $e->getMessage());
            return false;
        }
    }




    // ========================================================================
    //                      GESTIÓN DE HASHTAGS
    // ========================================================================

    /**
     * Obtiene todos los hashtags del sistema.
     */
    public static function getTodosLosHashtags() {
        $db = self::conectar();
        try {
            $stmt = $db->query("SELECT id, etiqueta FROM hashtags ORDER BY etiqueta ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getTodosLosHashtags: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Comprueba si un hashtag ya existe para evitar duplicados.
     */
    public static function existeHashtag($etiqueta) {
        $db = self::conectar();
        try {
            $stmt = $db->prepare("SELECT id FROM hashtags WHERE etiqueta = ?");
            $stmt->execute([$etiqueta]);
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Inserta un nuevo hashtag.
     */
    public static function insertarHashtag($etiqueta) {
        $db = self::conectar();
        try {
            // // Nos aseguramos de guardarlo siempre con el # delante y en minúsculas
            // $etiqueta = strtolower(trim($etiqueta));
            // if (strpos($etiqueta, '#') !== 0) {
            //     $etiqueta = '#' . $etiqueta;
            // }

            $stmt = $db->prepare("INSERT INTO hashtags (etiqueta) VALUES (?)");
            return $stmt->execute([$etiqueta]);
        } catch (PDOException $e) {
            error_log("Error al insertar hashtag: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Borra un hashtag por su ID.
     */
    public static function borrarHashtag($id) {
        $db = self::conectar();
        try {
            $stmt = $db->prepare("DELETE FROM hashtags WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al borrar hashtag: " . $e->getMessage());
            return false;
        }
    }




    // ========================================================================
    //                      GESTIÓN DE COMENTARIOS
    // ========================================================================

    /**
     * Obtiene todos los comentarios de un evento específico, ordenados por fecha de 
     * publicación descendente (más recientes primero).
     * Devuelve un array con id, nombre, email, texto y fecha_hora de cada comentario.
     */
    public static function getComentarios($evento_id) {
        $db = self::conectar();
        $stmt = $db->prepare("SELECT id, nombre, email, texto, fecha_hora 
                            FROM comentarios 
                            WHERE evento_id = ? 
                            ORDER BY fecha_hora DESC");        
        $stmt->execute([$evento_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insertar un nuevo comentario en la BD
     */
    public static function insertarComentario($evento_id, $nombre, $email, $texto) {
        $db = self::conectar();
        // fecha_hora asume valor por defecto NOW() en la BD
        // Usamos sentencias preparadas para evitar inyección SQL
        $stmt = $db->prepare("INSERT INTO comentarios (evento_id, nombre, email, texto, fecha_hora) 
                            VALUES (?, ?, ?, ?, NOW())");
        // Devuelve true si se insertó correctamente, false si hubo error
        return $stmt->execute([$evento_id, $nombre, $email, $texto]); 
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
     * El rol se asigna como 'registrado' por defecto.
     * Devuelve true si el registro fue exitoso, false si hubo un error (por ejemplo, nick o email ya existe).
     */
    public static function registrarUsuario($nick, $email, $password) {
        $db = self::conectar();
        try {
            // Cifrado obligatorio de contraseña (password_hash)
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO usuarios (nick, email, password, rol) VALUES (?, ?, ?, 'registrado')");
            return $stmt->execute([$nick, $email, $hash]);
        } catch (PDOException $e) {
            error_log("Error al registrar usuario: " . $e->getMessage());
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
            if ($usuario && password_verify($passwordInput, $usuario['password'])) {            
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

    /**
     * Comprueba si ya existe un usuario con el mismo nick o email.
     */
    public static function existeUsuario($nick, $email) {
        $db = self::conectar();
        $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE nick = ? OR email = ?");
        $stmt->execute([$nick, $email]);
        return (int)$stmt->fetchColumn() > 0;
    }




    // ========================================================================
    //               GESTIÓN DE USUARIOS (SUPERUSUARIO)
    // ========================================================================

    /**
     * Devuelve una lista con todos los usuarios registrados en el sistema.
     */
    public static function getTodosLosUsuarios() {
        $db = self::conectar();
        try {
            $stmt = $db->query("SELECT id, nick, email, rol FROM usuarios ORDER BY id ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getTodosLosUsuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Devuelve todos los datos de un usuario (incluido el rol) para el Superusuario.
     */
    public static function getUsuarioCompleto($id) {
        $db = self::conectar();
        try {
            $stmt = $db->prepare("SELECT id, nick, email, rol FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getUsuarioCompleto: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza los datos y el rol de un usuario (Función exclusiva del Superusuario).
     */
    public static function actualizarUsuarioPorSuperusuario($id, $nick, $email, $rol, $passwordNueva = null) {
        $db = self::conectar();
        try {
            if (!empty($passwordNueva)) {
                $hash = password_hash($passwordNueva, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET nick = ?, email = ?, rol = ?, password = ? WHERE id = ?");
                return $stmt->execute([$nick, $email, $rol, $hash, $id]);
            } else {
                $stmt = $db->prepare("UPDATE usuarios SET nick = ?, email = ?, rol = ? WHERE id = ?");
                return $stmt->execute([$nick, $email, $rol, $id]);
            }
        } catch (PDOException $e) {
            error_log("Error en actualizarUsuarioPorSuperusuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cuenta cuántos superusuarios hay registrados en el sistema.
     */
    public static function contarSuperusuarios() {
        $db = self::conectar();
        $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'superusuario'");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Elimina un usuario por su ID.
     */
    public static function borrarUsuario($id) {
        $db = self::conectar();
        try {
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error al borrar usuario: " . $e->getMessage());
            return false;
        }
    }
}