-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: database:3306
-- Tiempo de generación: 03-11-2026 a las 12:01:45
-- Versión del servidor: 8.4.13
-- Versión de PHP: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `SIBW_AGENDA_EVENTOS`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'Deporte'),
(2, 'Cultura'),
(3, 'Ocio'),
(4, 'Otros');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_spanish_ci NOT NULL,
  `texto` text COLLATE utf8mb4_spanish_ci NOT NULL,
  `evento_id` int NOT NULL,
  `fecha_hora` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`id`, `nombre`, `email`, `texto`, `evento_id`, `fecha_hora`) VALUES
(1, 'María Gómez', 'mariagm@gmail.com', '¡Qué ganas de participar este año! El recorrido de la edición anterior fue espectacular. Mi parte favorita fue sin duda la subida del cerro de la Matea.', 1, '2026-04-10 10:15:00'),
(2, 'Carlos López', 'carloslopez@outlook.com', 'La organización de este evento es impecable. Me encanta que hayan modalidades distintas para los que no somos corredores profesionales. ¡Nos vemos en la línea de salida!', 1, '2026-04-10 16:40:00'),
(3, 'Juan Pérez', 'juanitomtb@yahoo.com', 'He participado en varias ediciones y cada año me sorprende la belleza del recorrido. Me encanta ir a Santiago de la Espada para disfrutar de la naturaleza y de la bici.', 1, '2026-04-11 09:05:00'),
(6, 'Sergio Martínez', 'sergio.cine@gmail.com', 'Es increíble que podamos ver cine de este nivel en mitad de la sierra. La calidad de los cortos del año pasado fue impresionante, ¡no me pierdo esta edición por nada!', 3, '2025-07-01 21:00:00'),
(7, 'Elena Vizcaíno', 'elena.v@yahoo.es', 'Lo que más me gusta es el ambiente que se crea en el pueblo durante el festival. Ver los cortos por la noche al aire libre es una experiencia mágica. ¡Totalmente recomendado!', 3, '2025-07-02 23:30:00'),
(8, 'Marcos Reverte', 'mreverte_producciones@outlook.com', 'Como profesional del sector, valoro muchísimo el esfuerzo de organizar un festival calificador para los Goya en un entorno rural. Un diez para la organización y para la hospitalidad de la gente de Santiago.', 3, '2025-07-05 12:10:00'),
(12, 'Raquel Mota', 'raquel.mota88@gmail.com', '¡Las mejores fiestas de la provincia sin duda! Contando los días para que llegue el 14 de agosto.', 5, '2025-08-14 18:00:00'),
(13, 'Francisco G.', 'paco_pontones@yahoo.es', 'Este año los encierros han estado muy bien organizados. Un saludo a toda la comisión de fiestas por el trabajo que hacen.', 5, '2025-08-15 03:20:00'),
(14, 'Sonia López', 'sonia_viajera@outlook.com', 'Me encanta el ambiente de la plaza por las noches. Es increíble ver a todo el pueblo bailando junto.', 5, '2025-08-16 12:45:00'),
(15, 'José Antonio', 'jantonio_segura@gmail.com', 'Los bolos serranos son una tradición que no debemos perder nunca. Muy buena iniciativa incluir el torneo en el programa.', 5, '2025-08-17 21:10:00'),
(16, 'Beatriz Ramos', 'bea.ramos.92@hotmail.com', 'He traído a unos amigos de fuera y se han quedado alucinados con la hospitalidad de la gente. ¡Volveremos el año que viene!', 5, '2025-08-18 11:00:00'),
(17, 'Ismael Ortíz', 'isma_medieval@gmail.com', 'Me sorprendió muchísimo la decoración de las calles, estaba todo cuidado al detalle. El puesto de quesos artesanales era increíble.', 6, '2025-10-10 20:15:00'),
(20, 'Manolo', 'Manolillo@outlook.es', 'Muy chula carrera. que pena que no pase por LA TOBA.\n\n*(Editado por moderador admin)*', 1, '2026-04-27 09:50:49'),
(21, 'Rocio', 'rocioberzosa20@casascarrasco.com', 'un lugar ideal para ir a descansar , estar con la familia y se come muy bien. volveremos a ir sin duda a PONTONES.', 6, '2026-05-14 15:27:53'),
(22, 'Marta Martínez', 'martamm@hotmail.com', 'Un planazo para ir con niños. Disfrutaron muchísimo con los talleres de barro y la música en directo, nos gustó la aldea de FUENTE SEGURA. ¡Repetiremos seguro!\n\n*(Editado por moderador mod)*', 6, '2026-05-17 08:31:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_spanish_ci NOT NULL,
  `fecha_realizacion` datetime NOT NULL,
  `localidad_id` int DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_spanish_ci,
  `categoria_id` int DEFAULT NULL,
  `publicado` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `titulo`, `fecha_realizacion`, `localidad_id`, `descripcion`, `categoria_id`, `publicado`) VALUES
(1, 'VI Zumeta Bike Race', '2026-05-31 00:00:00', 1, 'El espectacular entorno de Santiago-Pontones, en el límite natural con Castilla La Mancha en la zona oriental de la provincia, será el nuevo reto que afronten los bikers jiennenses. Esta edición de la VI Zumeta Bike Race, que se llevará a cabo el sábado 30 de mayo a partir de las 9:30h en el núcleo de población de Santiago de la Espada. Es una prueba puntuable de un apasionante Circuito Provincial de Jaén BTT Maratón 2026. La organización volverá a correr cargo del ayuntamiento local, con cinco exitosas ediciones a sus espaldas que hacen de ella una de las pruebas de referencia del calendario de Jaén, con presencia año tras año de corredores de las provincias y comunidades autónomas cercanas.\r\n\r\nLa carrera contará con dos recorridos adaptados a diferentes niveles: una ruta corta de 44 km y una ruta larga de 73 km, para los más experimentados, atravesando los paisajes más emblemáticos de nuestro municipio.\r\n\r\nLa propuesta este año es un recorrido de 73km que en primer lugar llevará a los corredores en dirección suroeste hacia los Campos de Hernán Perea, girando en El Cerezo para cambiar hacia el norte para afrontar la subida a Eras de Pedro Blázquez. Posteriormente, en el km37, habrá un desvió para aquellos que elijan la ruta corta. Los valientes que afronten el trazado competitivo se enfrentarán por su lado a dificultades como el Collado Los Ruejos, Puerto de Marchena o Patio de la Calabaza con Las Cruces como cota máxima con sus 1732msnm, para finalmente encarar el descenso hasta la meta de Santiago Pontones.\r\n\r\nPara poder participar en este evento es necesario la inscripción. Actualmente el precio de inscripción es de 35€ para federados y 45€ no federados, no pudiéndose llevar a cabo in situ el día de la carrera.', 1, 1),
(2, 'Semana del Libro', '2026-04-20 00:00:00', 1, 'El Ayuntamiento de Santiago-Pontones se complace en anunciar una nueva edición de la &quot;Semana del Libro&quot;, que se celebrará del 20 al 28 de abril con una amplia programación cultural diseñada para fomentar la lectura en el ámbito rural.\r\n\r\nEste año, las actividades no solo se concentrarán en la Biblioteca Municipal de Santiago de la Espada, sino que extenderemos los talleres de lectura, el cuentacuentos infantil y el &quot;bookcrossing&quot; a las pedanías de Pontones y Coto Ríos, acercando la literatura a todos nuestros vecinos.\r\n\r\nLa inauguración tendrá lugar el sábado 20 con la representación de la obra de teatro &quot;Mientras Tanto&quot; en el centro cultural Lugar de Encuentro. La recaudación de esta obra irá destinada a beneficio de los enfermos de Alzheimer. Además, contaremos con la visita de varios escritores locales que realizarán firmas de libros y mesas redondas sobre la importancia de mantener vivas las historias de nuestros pueblos de la Sierra de Segura.', 2, 1),
(3, 'Galapán Film Festival - Edición 2025', '2025-07-21 21:00:00', 1, 'El Galapán Film Festival, Festival Internacional de Cortometrajes de Santiago-Pontones, regresa en su edición 2025 consolidado como uno de los certámenes más prestigiosos del país, contando con el Certificado de Calidad de la AIC y siendo festival calificador para los Premios Goya.\n\nNacido en 2016, este festival transforma Santiago de la Espada en un gran plató natural y un punto de encuentro para cineastas de todo el mundo. Durante una semana, los vecinos y visitantes podrán disfrutar de proyecciones al aire libre de los mejores cortometrajes nacionales e internacionales, seleccionados entre más de mil trabajos recibidos de diversos países.\n\nEl festival destaca por su ambiente familiar y cercano, donde la alfombra roja se mezcla con la hospitalidad serrana. Además de las proyecciones de la Sección Oficial y la Sección Provincial (Jaén), se celebrará la tradicional Gala de Clausura con la entrega de los \"Galapán de Oro\" y el premio especial del público. Una oportunidad única para disfrutar del séptimo arte bajo las estrellas de la Sierra de Segura.', 2, 1),
(4, 'VII Feria de la Trashumancia, Turismo y Costumbres', '2025-06-07 00:00:00', 1, 'Santiago-Pontones celebra la VII edición de la Feria de la Trashumancia, Turismo y Costumbres, un evento dedicado a poner en valor nuestras raíces y la forma de vida tradicional de la Sierra de Segura. Tras un fin de semana inolvidable, la feria se consolida como el motor que impulsa el conocimiento de nuestras tradiciones, llenando de vida las calles de Santiago de la Espada.\r\n\r\nLa feria cuenta con un mercado de artesanos y empresarios locales, talleres en vivo y la participación de grupos de baile que alegran las jornadas con su arte. Entre las actividades destacadas, contamos con la colaboración excepcional de la Asociación &quot;Enganchadas de un Hilo&quot; y exhibiciones de Bio Foto que capturan la esencia del pastoreo. Este evento es una apuesta firme por el turismo rural y el reconocimiento a la labor de nuestros pastores y artesanos, invitando a todos a descubrir el corazón de la trashumancia en Jaén.', 3, 1),
(5, 'Fiestas Populares en Honor a la Purísima Concepción', '2025-08-14 09:00:00', 1, 'Del 14 al 20 de agosto, Santiago de la Espada se viste de gala para celebrar sus fiestas más esperadas en honor a la Purísima Concepción. Unas jornadas donde la tradición, la devoción y la diversión se dan la mano en el corazón de la Sierra de Segura.\n\nEl programa de este año viene cargado de actividades para todas las edades: desde los tradicionales encierros de reses bravas por las calles del pueblo, que atraen a corredores de toda la comarca, hasta las verbenas nocturnas en la plaza. No faltarán los campeonatos de bolos serranos, los pasacalles de la banda de música y las actividades infantiles para los más pequeños. Es el momento del reencuentro con familiares y amigos que regresan al pueblo para disfrutar del ambiente inigualable de nuestras fiestas patronales. ¡Os esperamos para celebrar juntos nuestras tradiciones!', 3, 1),
(6, 'Mercado y Feria Medieval de Pontones', '2025-10-10 11:00:00', 30, 'Durante el segundo fin de semana de octubre, Pontones retrocede en el tiempo hasta la Edad Media. Sus calles se transforman en un auténtico mercado medieval donde el aroma a especias, incienso y comida tradicional inunda el ambiente.\n\nLos visitantes podrán disfrutar de una amplia variedad de puestos de artesanía, cuero, orfebrería y productos gastronómicos de la zona. El evento cuenta con animación callejera, músicos medievales, exhibiciones de cetrería y talleres de oficios antiguos para los más pequeños. Es una oportunidad perfecta para conocer Pontones bajo una luz diferente, rodeados de historia y leyendas en un entorno natural incomparable.', 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos_hashtags`
--

CREATE TABLE `eventos_hashtags` (
  `evento_id` int NOT NULL,
  `hashtag_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `eventos_hashtags`
--

INSERT INTO `eventos_hashtags` (`evento_id`, `hashtag_id`) VALUES
(3, 1),
(5, 1),
(6, 1),
(5, 2),
(6, 2),
(5, 3),
(6, 3),
(1, 4),
(5, 4),
(6, 4),
(3, 5),
(5, 5),
(6, 5),
(1, 6),
(3, 6),
(5, 6),
(1, 12);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `galeria_imagenes`
--

CREATE TABLE `galeria_imagenes` (
  `id` int NOT NULL,
  `ruta_imagen` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `evento_id` int NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `es_cartel` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `galeria_imagenes`
--

INSERT INTO `galeria_imagenes` (`id`, `ruta_imagen`, `evento_id`, `descripcion`, `es_cartel`) VALUES
(1, 'images/VI_ZBR_2026/Podium-Zumeta-2025-2.jpg', 1, NULL, 0),
(2, 'images/VI_ZBR_2026/Salida-Zumeta-2025-9.jpg', 1, NULL, 0),
(3, 'images/VI_ZBR_2026/Salida-Zumeta-2025-46.jpg', 1, NULL, 0),
(8, 'images/fiestas_populares_2025/programa_1.jpeg', 5, NULL, 0),
(9, 'images/fiestas_populares_2025/programa_2.jpeg', 5, NULL, 0),
(10, 'images/fiestas_populares_2025/programa_3.jpeg', 5, NULL, 0),
(11, 'images/fiestas_populares_2025/programa_4.jpeg', 5, NULL, 0),
(12, 'images/fiestas_populares_2025/programa_5.jpeg', 5, NULL, 0),
(13, 'images/fiestas_populares_2025/programa_6.jpeg', 5, NULL, 0),
(14, 'images/fiestas_populares_2025/programa_7.jpeg', 5, NULL, 0),
(15, 'images/fiestas_populares_2025/programa_8.jpeg', 5, NULL, 0),
(16, 'images/fiestas_populares_2025/programa_9.jpeg', 5, NULL, 0),
(17, 'images/fiestas_populares_2025/programa_10.jpeg', 5, NULL, 0),
(18, 'images/fiestas_populares_2025/programa_11.jpeg', 5, NULL, 0),
(19, 'images/fiestas_populares_2025/programa_12.jpeg', 5, NULL, 0),
(20, 'images/fiestas_populares_2025/programa_13.jpeg', 5, NULL, 0),
(21, 'images/fiestas_populares_2025/programa_14.jpeg', 5, NULL, 0),
(22, 'images/fiestas_populares_2025/programa_15.jpeg', 5, NULL, 0),
(23, 'images/fiestas_populares_2025/programa_16.jpeg', 5, NULL, 0),
(24, 'images/feria_medieval_ptns_2025/cartel_feria_1.jpg', 6, NULL, 0),
(25, 'images/feria_medieval_ptns_2025/cartel_feria_2.jpg', 6, NULL, 0),
(26, 'images/feria_medieval_ptns_2025/cartel_feria_3.jpg', 6, NULL, 0),
(27, 'images/VI_ZBR_2026/cartel_ZBR_2026.png', 1, 'Cartel oficial de la sexta edición de la Zumeta Bike Race 2026', 1),
(29, 'images/galapan_film_festival_2025/cartel_galapan_film_festival_edicion_2025.jpg', 3, 'Póster oficial de la edición 2025 del Galapán Film Festival', 1),
(31, 'images/fiestas_populares_2025/cartel_fiestas_populares_2025.jpg', 5, 'Cartel de las Fiestas Populares en Honor a la Purísima Concepción 2025', 1),
(32, 'images/feria_medieval_ptns_2025/cartel_feria_medieval.jpg', 6, 'Cartel ilustrado del Mercado y Feria Medieval de Pontones 2025', 1),
(45, 'images/uploads/1780264133_cartel_cartel_Semana_Libro_2026.jpg', 2, NULL, 1),
(46, 'images/uploads/1780264133_0_galeria_obra_teatro.jpg', 2, NULL, 0),
(47, 'images/uploads/1780264545_cartel_cartel_feria_transhumancia_2025.jpg', 4, NULL, 1),
(48, 'images/uploads/1780264545_0_galeria_cartel_1.jpg', 4, NULL, 0),
(49, 'images/uploads/1780264545_1_galeria_cartel_2.jpg', 4, NULL, 0),
(50, 'images/uploads/1780264545_2_galeria_cartel_3.jpg', 4, NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hashtags`
--

CREATE TABLE `hashtags` (
  `id` int NOT NULL,
  `etiqueta` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `hashtags`
--

INSERT INTO `hashtags` (`id`, `etiqueta`) VALUES
(4, 'AireLibre'),
(5, 'Cultura'),
(6, 'Diversión'),
(3, 'Familiar'),
(2, 'Gratis'),
(1, 'Música'),
(12, 'Naturaleza');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `localidades`
--

CREATE TABLE `localidades` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `localidades`
--

INSERT INTO `localidades` (`id`, `nombre`) VALUES
(1, 'Santiago de la Espada'),
(2, 'La Agragea'),
(3, 'Los Anchos'),
(4, 'Arroyo Venancia'),
(5, 'El Artuñedo'),
(6, 'La Ballestera'),
(7, 'La Cabañuela'),
(8, 'Las Canalejas'),
(9, 'Casas de Carrasco'),
(10, 'Casicas del río Segura'),
(11, 'Los Centenares'),
(12, 'El Cerezo'),
(13, 'Coto Ríos'),
(14, 'Fuente Segura'),
(15, 'Los Goldines'),
(16, 'Las Gorgollitas'),
(17, 'Huelga de Utrera'),
(18, 'Huerta del Manco'),
(19, 'Loma de María Ángela'),
(20, 'Marchena'),
(21, 'La Matea'),
(22, 'Miller'),
(23, 'Montalvo'),
(24, 'La Muela'),
(25, 'Las Nogueras'),
(26, 'El Parralejo'),
(27, 'El Patronato'),
(28, 'Peguera del Madroño'),
(29, 'Pontón Alto'),
(30, 'Pontones'),
(31, 'Poyotello'),
(32, 'Solana de Padilla'),
(33, 'Los Teatinos'),
(34, 'La Toba'),
(35, 'Tobos'),
(36, 'Vites');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nick` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('registrado','moderador','gestor','superusuario') DEFAULT 'registrado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nick`, `email`, `password`, `rol`) VALUES
(2, 'admin', 'admin@santiagopontones.es', '$2y$10$F0TKSHtcoxoeTorxT51FSu0khGwCha2sZRpJTH0TXCztFP7aKV/ai', 'superusuario'),
(3, 'moderador', 'moderador@santiagopontones.es', '$2y$10$bTdMDCwE8wdjG1xJYNOJp..Gl7hSR7VF.n5FdNROzUEqf93JeguGi', 'moderador'),
(4, 'gestor', 'gestor@santiagopontones.es', '$2y$10$qpTdtASbN6JNvNcRu.IlaufTGfGHmcaiuUrPVmjKdikEe0mxFtZ9u', 'gestor'),
(6, 'usuario', 'usuario@santiagopontones.es', '$2y$10$JDBq5Oxp4P1ExZPsH2EUuOS5NA2NATu/3JE17mKuEPbhGmJNarcUm', 'registrado'),
(7, 'usuario2', 'usuario2@santiagopontones.es', '$2y$10$PGf2bWsZzdZjdCBMheqTAOnSE8s3rw.hglhqWRZai1ty7ALqcmpsi', 'registrado');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `localidad_id` (`localidad_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `eventos_hashtags`
--
ALTER TABLE `eventos_hashtags`
  ADD PRIMARY KEY (`evento_id`,`hashtag_id`),
  ADD KEY `hashtag_id` (`hashtag_id`);

--
-- Indices de la tabla `galeria_imagenes`
--
ALTER TABLE `galeria_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Indices de la tabla `hashtags`
--
ALTER TABLE `hashtags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `etiqueta` (`etiqueta`);

--
-- Indices de la tabla `localidades`
--
ALTER TABLE `localidades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nick` (`nick`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `galeria_imagenes`
--
ALTER TABLE `galeria_imagenes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `hashtags`
--
ALTER TABLE `hashtags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `localidades`
--
ALTER TABLE `localidades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`localidad_id`) REFERENCES `localidades` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `eventos_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `eventos_hashtags`
--
ALTER TABLE `eventos_hashtags`
  ADD CONSTRAINT `eventos_hashtags_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventos_hashtags_ibfk_2` FOREIGN KEY (`hashtag_id`) REFERENCES `hashtags` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `galeria_imagenes`
--
ALTER TABLE `galeria_imagenes`
  ADD CONSTRAINT `galeria_imagenes_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
