-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 29-07-2024 a las 19:16:22
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db_restaurante`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(48) NOT NULL,
  `nombre` varchar(48) NOT NULL,
  `apellido` varchar(48) NOT NULL,
  `rol` varchar(48) NOT NULL,
  `estado` varchar(48) DEFAULT NULL,
  `fecha_baja` date DEFAULT NULL,
  `email` varchar(48) NOT NULL,
  `contrasenia` varchar(255) NOT NULL,
  `cant_operaciones` int(48) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `nombre`, `apellido`, `rol`, `estado`, `fecha_baja`, `email`, `contrasenia`, `cant_operaciones`) VALUES
(8, 'leonel', 'messi', 'socio', 'activo', NULL, 'leo@gmail.com', '$2y$10$QW7tGYX./d9Xg87SoLB60eKzyQzaPWBHBackKiUvYlRU/xQ6wFuRW', 0),
(9, 'pepe', 'lopez', 'cervecero', 'activo', NULL, 'pepe@gmail.com', '$2y$10$tbjY9xL54YODIcyHdeS1GujLyO3T43ctzi0IkhShtH.Rq.IEBOLx.', 3),
(12, 'daniel', 'mendez', 'cocinero', 'activo', NULL, 'daniel@gmail.com', '$2y$10$zGJwC8UBryc4SwGaxsauS.6hKm1wsMA1stVzpJ8lxoIXZiC8PR/ZS', 1),
(13, 'roberto', 'carlos', 'bartender', 'activo', NULL, 'roberto@gmail.com', '$2y$10$7CqBFpBRSThKKOciVPSx/ejJyrHUdc4scNAIoAFBoMdnJzEFEUdji', 7),
(14, 'marta', 'lopez', 'socio', 'activo', NULL, 'marta@gmail.com', '$2y$10$I42FkZDT5qh7xvao9yyCV.n/LYO4CkwcJVq7xZ05osQFlGRmU8yym', 0),
(15, 'romeo', 'santos', 'admin', 'activo', NULL, 'romeo@gmail.com', '$2y$10$puLDDHiONJXldBQtMb6B9.k4JojF45P7S0xYZRFl5x020y4LYnbmG', 0),
(16, 'roman', 'riquelme', 'cocinero', 'activo', NULL, 'roman@gmail.com', '$2y$10$8w6xPmenKALcZtnD0QsNpegjNJNunhGjsLm8mIsezzn07GjbVFCWu', 2),
(17, 'dario', 'gomez', 'bartender', 'activo', NULL, 'dario@gmail.com', '$2y$10$Lch1WXzPGMEIfzskn3c9D.taDNv/aNj2UlmNU0lZY1ynZN/9B0xxW', 0),
(18, 'pedro', 'sanchez', 'mozo', 'activo', NULL, 'pedrito@gmail.com', '$2y$10$fC7kboIVjuZQh/08q.JzGuQuJnPF.78spjXwDzmupOGPJxMKxpD26', 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta_cliente`
--

CREATE TABLE `encuesta_cliente` (
  `id` int(48) NOT NULL,
  `codigo_mesa` varchar(15) NOT NULL,
  `codigo_pedido` varchar(48) NOT NULL,
  `puntuacion_mesa` int(15) NOT NULL,
  `puntuacion_restaurante` int(15) NOT NULL,
  `puntuacion_mozo` int(15) NOT NULL,
  `puntuacion_cocinero` int(15) DEFAULT NULL,
  `puntuacion_bartender` int(15) DEFAULT NULL,
  `puntuacion_cervecero` int(15) DEFAULT NULL,
  `descripcion` varchar(48) NOT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `encuesta_cliente`
--

INSERT INTO `encuesta_cliente` (`id`, `codigo_mesa`, `codigo_pedido`, `puntuacion_mesa`, `puntuacion_restaurante`, `puntuacion_mozo`, `puntuacion_cocinero`, `puntuacion_bartender`, `puntuacion_cervecero`, `descripcion`, `fecha`) VALUES
(9, 'abc12', 'gfd12', 8, 8, 7, NULL, NULL, 7, 'buen lugar para comer', '2024-07-02'),
(10, 'bnm12', 'abc12', 8, 8, 7, 7, NULL, NULL, 'buen lugar para comer', '2024-07-16'),
(11, 'ert13', 'you12', 8, 8, 7, 7, NULL, NULL, 'buen lugar para comer', '2024-07-29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs`
--

CREATE TABLE `logs` (
  `id` int(48) NOT NULL,
  `id_usuario` int(40) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `logs`
--

INSERT INTO `logs` (`id`, `id_usuario`, `fecha`, `hora`) VALUES
(1, 8, '2024-06-24', '19:43:40.000000'),
(2, 8, '2024-06-24', '19:48:58.000000'),
(3, 8, '2024-06-24', '20:15:54.000000'),
(4, 18, '2024-06-24', '20:18:53.000000'),
(5, 12, '2024-06-24', '20:35:21.000000'),
(6, 13, '2024-06-24', '20:36:10.000000'),
(7, 13, '2024-06-24', '20:40:23.000000'),
(8, 8, '2024-06-24', '20:42:42.000000'),
(9, 18, '2024-06-24', '20:43:27.000000'),
(10, 18, '2024-06-24', '20:47:11.000000'),
(11, 8, '2024-06-24', '20:59:10.000000'),
(12, 8, '2024-06-24', '21:21:07.000000'),
(13, 8, '2024-06-24', '21:44:47.000000'),
(14, 8, '2024-06-24', '22:04:27.000000'),
(15, 8, '2024-06-24', '22:11:12.000000'),
(16, 8, '2024-06-24', '22:37:05.000000'),
(17, 18, '2024-06-24', '22:59:24.000000'),
(18, 13, '2024-06-24', '23:01:20.000000'),
(19, 13, '2024-06-24', '23:11:07.000000'),
(20, 13, '2024-06-24', '23:17:43.000000'),
(21, 13, '2024-06-24', '23:20:37.000000'),
(22, 13, '2024-06-24', '23:33:29.000000'),
(23, 8, '2024-06-24', '23:34:19.000000'),
(24, 13, '2024-06-24', '23:40:21.000000'),
(25, 8, '2024-06-24', '23:41:04.000000'),
(26, 18, '2024-06-24', '23:52:45.000000'),
(27, 18, '2024-06-24', '23:58:28.000000'),
(28, 8, '2024-06-25', '01:10:10.000000'),
(29, 8, '2024-06-25', '01:38:40.000000'),
(30, 18, '2024-06-25', '01:44:44.000000'),
(31, 18, '2024-06-25', '01:54:02.000000'),
(32, 18, '2024-06-25', '02:08:51.000000'),
(33, 8, '2024-06-25', '02:08:56.000000'),
(34, 8, '2024-06-25', '02:29:12.000000'),
(35, 8, '2024-06-25', '02:43:13.000000'),
(36, 8, '2024-06-25', '02:51:42.000000'),
(37, 8, '2024-06-25', '02:58:28.000000'),
(38, 15, '2024-06-26', '00:03:19.000000'),
(39, 8, '2024-06-26', '00:05:29.000000'),
(40, 8, '2024-06-26', '00:33:39.000000'),
(41, 8, '2024-06-29', '20:39:47.000000'),
(42, 9, '2024-06-29', '20:42:45.000000'),
(43, 8, '2024-06-29', '20:44:28.000000'),
(44, 9, '2024-06-29', '20:45:23.000000'),
(45, 8, '2024-06-29', '21:44:58.000000'),
(46, 8, '2024-06-29', '21:59:40.000000'),
(47, 8, '2024-06-29', '22:00:15.000000'),
(48, 15, '2024-07-02', '20:53:24.000000'),
(49, 18, '2024-07-02', '20:53:34.000000'),
(50, 15, '2024-07-02', '20:57:53.000000'),
(51, 15, '2024-07-02', '21:07:58.000000'),
(52, 15, '2024-07-02', '21:12:22.000000'),
(53, 15, '2024-07-02', '21:22:26.000000'),
(54, 15, '2024-07-02', '21:29:24.000000'),
(55, 15, '2024-07-02', '21:33:16.000000'),
(56, 18, '2024-07-02', '21:35:42.000000'),
(57, 18, '2024-07-02', '21:38:14.000000'),
(58, 18, '2024-07-02', '21:51:00.000000'),
(59, 18, '2024-07-02', '23:34:42.000000'),
(60, 9, '2024-07-02', '23:36:50.000000'),
(61, 18, '2024-07-02', '23:38:34.000000'),
(62, 8, '2024-07-02', '23:39:20.000000'),
(63, 18, '2024-07-02', '23:47:37.000000'),
(64, 18, '2024-07-02', '23:54:28.000000'),
(65, 8, '2024-07-02', '23:54:33.000000'),
(66, 8, '2024-07-03', '00:04:03.000000'),
(67, 8, '2024-07-03', '00:18:38.000000'),
(68, 18, '2024-07-03', '01:15:45.000000'),
(69, 8, '2024-07-03', '01:15:51.000000'),
(70, 8, '2024-07-16', '00:00:06.000000'),
(71, 18, '2024-07-16', '00:02:49.000000'),
(72, 18, '2024-07-16', '00:27:57.000000'),
(73, 8, '2024-07-16', '00:34:30.000000'),
(74, 8, '2024-07-16', '00:40:59.000000'),
(75, 8, '2024-07-16', '00:52:23.000000'),
(76, 8, '2024-07-16', '01:00:38.000000'),
(77, 8, '2024-07-16', '01:07:14.000000'),
(78, 8, '2024-07-16', '01:17:39.000000'),
(79, 8, '2024-07-16', '01:22:41.000000'),
(80, 8, '2024-07-16', '01:27:43.000000'),
(81, 8, '2024-07-16', '02:07:15.000000'),
(82, 8, '2024-07-16', '02:13:38.000000'),
(83, 8, '2024-07-16', '02:19:28.000000'),
(84, 8, '2024-07-16', '02:28:49.000000'),
(85, 8, '2024-07-16', '02:39:28.000000'),
(86, 18, '2024-07-16', '02:51:31.000000'),
(87, 18, '2024-07-16', '02:57:48.000000'),
(88, 18, '2024-07-16', '03:04:20.000000'),
(89, 8, '2024-07-16', '03:08:06.000000'),
(90, 18, '2024-07-16', '03:11:30.000000'),
(91, 8, '2024-07-16', '03:12:50.000000'),
(92, 8, '2024-07-16', '03:23:39.000000'),
(93, 18, '2024-07-16', '03:27:41.000000'),
(94, 8, '2024-07-16', '03:29:30.000000'),
(95, 8, '2024-07-16', '03:40:08.000000'),
(96, 8, '2024-07-16', '03:40:11.000000'),
(97, 18, '2024-07-16', '03:49:16.000000'),
(98, 8, '2024-07-16', '03:51:09.000000'),
(99, 8, '2024-07-16', '04:01:17.000000'),
(100, 8, '2024-07-16', '04:19:20.000000'),
(101, 8, '2024-07-16', '04:19:26.000000'),
(102, 8, '2024-07-16', '04:29:46.000000'),
(103, 8, '2024-07-16', '16:54:47.000000'),
(104, 8, '2024-07-16', '17:06:18.000000'),
(105, 18, '2024-07-16', '17:09:56.000000'),
(106, 8, '2024-07-16', '17:12:35.000000'),
(107, 8, '2024-07-16', '17:54:49.000000'),
(108, 18, '2024-07-16', '18:04:45.000000'),
(109, 12, '2024-07-16', '18:05:15.000000'),
(110, 18, '2024-07-16', '18:09:21.000000'),
(111, 8, '2024-07-16', '18:11:06.000000'),
(112, 18, '2024-07-16', '18:15:06.000000'),
(113, 8, '2024-07-16', '18:20:57.000000'),
(114, 8, '2024-07-16', '18:24:54.000000'),
(115, 8, '2024-07-17', '00:28:04.000000'),
(116, 8, '2024-07-17', '00:32:58.000000'),
(117, 8, '2024-07-17', '00:41:32.000000'),
(118, 8, '2024-07-17', '00:43:01.000000'),
(119, 8, '2024-07-17', '00:55:36.000000'),
(120, 8, '2024-07-17', '01:01:33.000000'),
(121, 8, '2024-07-17', '01:15:32.000000'),
(122, 8, '2024-07-17', '02:04:38.000000'),
(123, 8, '2024-07-17', '02:08:40.000000'),
(124, 8, '2024-07-17', '02:13:30.000000'),
(125, 8, '2024-07-17', '02:20:02.000000'),
(126, 8, '2024-07-17', '02:24:02.000000'),
(127, 8, '2024-07-17', '02:31:20.000000'),
(128, 8, '2024-07-17', '02:34:52.000000'),
(129, 8, '2024-07-17', '02:57:49.000000'),
(130, 8, '2024-07-17', '03:01:37.000000'),
(131, 8, '2024-07-17', '03:06:32.000000'),
(132, 8, '2024-07-17', '03:11:50.000000'),
(133, 8, '2024-07-17', '03:13:20.000000'),
(134, 8, '2024-07-17', '03:13:24.000000'),
(135, 8, '2024-07-17', '03:20:06.000000'),
(136, 8, '2024-07-17', '03:22:24.000000'),
(137, 8, '2024-07-17', '03:32:27.000000'),
(138, 8, '2024-07-29', '15:22:10.000000'),
(139, 16, '2024-07-29', '15:36:14.000000'),
(140, 16, '2024-07-29', '15:47:37.000000'),
(141, 18, '2024-07-29', '15:48:00.000000'),
(142, 8, '2024-07-29', '15:49:52.000000'),
(143, 18, '2024-07-29', '15:52:28.000000'),
(144, 18, '2024-07-29', '15:54:18.000000'),
(145, 8, '2024-07-29', '15:54:21.000000'),
(146, 8, '2024-07-29', '16:10:14.000000'),
(147, 8, '2024-07-29', '16:49:51.000000');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id` int(48) NOT NULL,
  `codigoIdentificacion` varchar(48) NOT NULL,
  `estado` varchar(48) NOT NULL,
  `fecha_baja` date DEFAULT NULL,
  `encuesta_realizada` tinyint(1) NOT NULL,
  `cantidad_usos` int(48) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id`, `codigoIdentificacion`, `estado`, `fecha_baja`, `encuesta_realizada`, `cantidad_usos`) VALUES
(1, 'abc12', 'con cliente esperando pedido', NULL, 0, 2),
(2, 'fgh12', 'con cliente esperando pedido', NULL, 0, 1),
(3, 'qwe12', 'libre', NULL, 0, 1),
(4, 'bnm12', 'libre', NULL, 0, 2),
(8, 'ert13', 'libre', NULL, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(48) NOT NULL,
  `codigoAlfanumerico` varchar(48) NOT NULL,
  `nombreCliente` varchar(48) NOT NULL,
  `codigoMesa` varchar(48) NOT NULL,
  `estado` varchar(48) NOT NULL,
  `precioFinal` int(48) NOT NULL,
  `tiempoEstimado` int(48) DEFAULT NULL,
  `fecha_baja` date DEFAULT NULL,
  `tiempo_inicio` time DEFAULT NULL,
  `tiempo_final` time DEFAULT NULL,
  `nombre_foto` varchar(48) DEFAULT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `codigoAlfanumerico`, `nombreCliente`, `codigoMesa`, `estado`, `precioFinal`, `tiempoEstimado`, `fecha_baja`, `tiempo_inicio`, `tiempo_final`, `nombre_foto`, `fecha`) VALUES
(15, 'gfd12', 'martin', 'abc12', 'entregado', 100, 5, NULL, '23:38:05', '23:45:20', 'abc12_gfd12.png', '2024-07-02'),
(16, 'abc12', 'joaquin', 'bnm12', 'entregado', 1000, 5, NULL, '18:06:42', '18:09:11', 'bnm12_abc12.png', '2024-07-16'),
(17, 'you12', 'marta', 'ert13', 'entregado', 1000, 2, NULL, '15:37:54', '15:47:43', 'ert13_you12.png', '2024-07-29'),
(18, 'ami31', 'cristian', 'fgh12', 'pendiente', 1000, NULL, NULL, NULL, NULL, 'fgh12_ami31.png', '2024-07-29'),
(19, 'zxc12', 'mario', 'abc12', 'pendiente', 1000, NULL, NULL, NULL, NULL, 'abc12_zxc12.png', '2024-07-29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_producto`
--

CREATE TABLE `pedido_producto` (
  `id` int(48) NOT NULL,
  `codigo_pedido` varchar(48) NOT NULL,
  `id_producto` int(48) NOT NULL,
  `estado` varchar(48) NOT NULL,
  `id_usuario` int(48) DEFAULT NULL,
  `tiempo_producto` int(48) DEFAULT NULL,
  `fecha_baja` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_producto`
--

INSERT INTO `pedido_producto` (`id`, `codigo_pedido`, `id_producto`, `estado`, `id_usuario`, `tiempo_producto`, `fecha_baja`) VALUES
(20, 'gfd12', 2, 'listo para servir', 9, 5, NULL),
(21, 'abc12', 6, 'listo para servir', 12, 5, NULL),
(22, 'you12', 6, 'listo para servir', 16, 2, NULL),
(23, 'ami31', 6, 'pendiente', NULL, NULL, NULL),
(24, 'zxc12', 6, 'pendiente', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(50) NOT NULL,
  `nombre` varchar(48) NOT NULL,
  `tipo` varchar(48) NOT NULL,
  `precio` int(48) NOT NULL,
  `fecha_baja` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `tipo`, `precio`, `fecha_baja`) VALUES
(1, 'hamburguesa', 'comida', 500, NULL),
(2, 'quilmes', 'cerveza', 100, NULL),
(3, 'martini', 'trago', 250, NULL),
(5, 'vodka', 'trago', 340, NULL),
(6, 'milanesa a caballo', 'comida', 1000, NULL),
(7, 'hamburguesa de garbanzo', 'comida', 100, NULL),
(8, 'corona', 'cerveza', 200, NULL),
(9, 'daikiri', 'trago', 300, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `encuesta_cliente`
--
ALTER TABLE `encuesta_cliente`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedido_producto`
--
ALTER TABLE `pedido_producto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(48) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `encuesta_cliente`
--
ALTER TABLE `encuesta_cliente`
  MODIFY `id` int(48) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(48) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(48) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(48) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `pedido_producto`
--
ALTER TABLE `pedido_producto`
  MODIFY `id` int(48) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
