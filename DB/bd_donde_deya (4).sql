-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-10-2024 a las 02:28:38
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bd_donde_deya`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `carrito_id` int(11) NOT NULL,
  `usuario_id` int(10) NOT NULL,
  `producto_id` int(20) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `categoria_id` int(7) NOT NULL,
  `categoria_nombre` varchar(50) NOT NULL,
  `categoria_ubicacion` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`categoria_id`, `categoria_nombre`, `categoria_ubicacion`) VALUES
(1, 'Cerveza', ''),
(2, 'Vino', ''),
(3, 'Whiskey', ''),
(4, 'Mecato', ''),
(5, 'Aguardiente', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `total` decimal(10,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id`, `pedido_id`, `producto_id`, `cantidad`, `total`) VALUES
(68, 48, 2, 4, 240.000),
(69, 49, 2, 3, 180.000),
(70, 50, 3, 1, 20.000),
(71, 51, 3, 3, 60.000),
(72, 51, 14, 1, 2.000),
(73, 52, 13, 4, 160.000),
(74, 53, 5, 5, 1000.000),
(75, 54, 13, 1, 40.000),
(76, 54, 8, 1, 80.000),
(77, 54, 16, 1, 3.000),
(78, 54, 12, 2, 60.000),
(79, 54, 2, 1, 60.000),
(80, 55, 8, 1, 80.000),
(81, 56, 2, 2, 120.000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `pedido_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_pedido` datetime NOT NULL DEFAULT current_timestamp(),
  `direccion` varchar(255) NOT NULL,
  `metodo_pago` enum('Transferencia Nequi','Efectivo') NOT NULL,
  `estado` enum('Pendiente','Aprobado','Cancelado') NOT NULL DEFAULT 'Pendiente',
  `total` decimal(10,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`pedido_id`, `usuario_id`, `fecha_pedido`, `direccion`, `metodo_pago`, `estado`, `total`) VALUES
(48, 2, '2024-09-26 20:27:14', 'Carrera 89 No 05, Suba Rincón, Bosques de Salamanca', 'Efectivo', 'Aprobado', 240.000),
(49, 2, '2024-09-26 20:41:14', 'Carrera 89 No 05, Suba Rincón, Bosques de Salamanca', 'Transferencia Nequi', 'Aprobado', 180.000),
(50, 2, '2024-09-26 20:52:50', 'Carrera 89 No 127 - 05, Suba Rincón, Bosques de Salamanca', 'Transferencia Nequi', 'Aprobado', 20.000),
(51, 2, '2024-10-01 19:25:21', 'Carrera 89 No 127 - 05, Suba Rincón, Bosques de Salamanca', 'Transferencia Nequi', '', 62.000),
(52, 2, '2024-10-01 20:34:45', 'Carrera 89 No 127  - 05, Suba Rincón, Bosques de Salamanca Torre 4 - 603', 'Efectivo', '', 160.000),
(53, 2, '2024-10-03 20:30:48', 'Carrera 89 No 127 - 05, Suba Rincón, Bosques de Salamanca', 'Transferencia Nequi', '', 1000.000),
(54, 2, '2024-10-03 21:38:19', 'Carrera 89 No 127 - 05, Suba Rincón, Bosques de Salamanca', 'Efectivo', '', 243.000),
(55, 2, '2024-10-05 19:59:53', 'Carrera 89 No 127 - 05, Suba Rincón, Bosques de Salamanca', 'Transferencia Nequi', 'Aprobado', 80.000),
(56, 2, '2024-10-06 14:23:39', 'Carrera 89 No 127 - 05, Suba Rincón, Bosques de Salamanca', 'Efectivo', 'Aprobado', 120.000);

--
-- Disparadores `pedidos`
--
DELIMITER $$
CREATE TRIGGER `after_pedido_aprobado` AFTER UPDATE ON `pedidos` FOR EACH ROW BEGIN
    IF OLD.estado = 'Pendiente' AND NEW.estado = 'Aprobado' THEN
        INSERT INTO ventas (pedido_id, usuario_id, fecha_pedido, direccion, metodo_pago, total)
        VALUES (NEW.pedido_id, NEW.usuario_id, NEW.fecha_pedido, NEW.direccion, NEW.metodo_pago, NEW.total);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `producto_id` int(20) NOT NULL,
  `producto_codigo` varchar(70) NOT NULL,
  `producto_nombre` varchar(70) NOT NULL,
  `producto_precio` decimal(10,3) NOT NULL,
  `producto_stock` int(25) NOT NULL,
  `producto_foto` varchar(500) NOT NULL,
  `categoria_id` int(7) NOT NULL,
  `usuario_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`producto_id`, `producto_codigo`, `producto_nombre`, `producto_precio`, `producto_stock`, `producto_foto`, `categoria_id`, `usuario_id`) VALUES
(1, '345678', 'Cerveza Poker X 30 Unidades', 60.000, 0, 'Cerveza_Poker_X_30_Unidades_9.jpg', 1, 1),
(2, '123456', 'Cerveza Águila X 30 Unidades', 60.000, 5, 'Cerveza_Águila_X_30_Unidades_17.jpg', 1, 1),
(3, '567890', 'Cerveza Corona X 6', 20.000, 6, 'Cerveza_Corona_X_6_29.png', 1, 1),
(5, '34567', 'Old Par', 200.000, 9, 'Old_Par_13.png', 3, 1),
(6, '56789', 'Buchanas', 500.000, 0, 'Buchanas_50.png', 3, 1),
(7, '456678', 'Red Label', 180.000, 0, 'Red_Label_82.png', 3, 1),
(8, '45667', 'Vino Gato Negro', 80.000, 8, 'Vino_Gato_Negro_90.png', 2, 1),
(9, '34466778', 'Vino Finca Las Moras', 120.000, 0, 'Vino_Finca_Las_Moras_88.png', 2, 1),
(10, '34566789', 'Vino Finca Las Moras Syrah Rose', 130.000, 2, 'Vino_Finca_Las_Moras_Syrah_Rose_91.png', 2, 1),
(11, '76890', 'Aguardiente Antioqueño', 30.000, 10, 'Aguardiente_Antioqueño_32.png', 5, 1),
(12, '345678901', 'Aguardiente Nectar', 30.000, 4, 'Aguardiente_Nectar_12.png', 5, 1),
(13, '234561', 'Aguardiente Antioqueño (Sin Azucar)', 40.000, 10, 'Aguardiente_Antioqueño_(Sin_Azucar)_37.png', 5, 1),
(14, '444444', 'Papas Margarita Limón', 2.000, 9, 'Papas_Margarita_Limón_67.jpg', 4, 1),
(15, '33333', 'Brownies Ramo', 5.000, 3, 'Brownies_Ramo_89.png', 4, 1),
(16, '4456778', 'De Todito Picante', 3.000, 10, 'De_Todito_Picante_99.png', 4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripción` text NOT NULL,
  `precio` decimal(10,3) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `activo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripción`, `precio`, `id_categoria`, `activo`) VALUES
(1, 'Cerveza Poker X 30 Unidades', 'Canasta de Cerveza Poker', 60.000, 1, 1),
(2, 'Cerveza Aguila X 30 Unidades', 'Cerveza en Canasta', 64.000, 1, 1),
(3, 'Cerveza Aguila X 6 Unidades', 'Cerveza En Lata', 16.000, 1, 1),
(4, 'Cerveza Aguila X 6 Unidades', 'Cerveza en Lata', 16.000, 1, 1),
(5, 'Cerveza Corona X 6 Unidades', 'Cerveza En Botella ', 28.900, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `usuario_id` int(10) NOT NULL,
  `usuario_nombre` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `usuario_apellido` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `usuario_usuario` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `usuario_clave` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `usuario_email` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `usuario_rol` int(2) NOT NULL,
  `usuario_activo` int(2) NOT NULL,
  `usuario_token` varchar(255) NOT NULL,
  `usuario_token_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`usuario_id`, `usuario_nombre`, `usuario_apellido`, `usuario_usuario`, `usuario_clave`, `usuario_email`, `usuario_rol`, `usuario_activo`, `usuario_token`, `usuario_token_expiration`) VALUES
(1, 'Administrador', 'Principal', 'Administrador', '$2y$10$EPY9LSLOFLDDBriuJICmFOqmZdnDXxLJG8YFbog5LcExp77DBQvgC', '', 1, 0, '', NULL),
(2, 'Johan', 'Rodriguez', 'Johan01', '$2y$10$6qIgDiqFxSD5j0awYJ19h.QMasyP7YRGePy6NU/HjUxifHlDIitq2', 'johanstevenrodriguezcardoso@gmail.com', 0, 0, '', NULL),
(3, 'Cliente', 'Nuevo', 'cliente01', '$2y$10$wzCu.aS22KiSiZaNrZfrHuJGzGZVDo60vZOzSVoFEUTJ6lFGSOkr2', 'cliente@gmail.com', 0, 0, '', NULL),
(6, 'Miguel', 'Suárez', 'Miguel01', '$2y$10$8alMiXGoWPPqxRBFGR.3E.2sooYqOj4zqW8mOcVoazj32jGQ/dUl.', 'miguel01@gmail.com', 1, 0, '', NULL),
(9, 'Carlos ', 'Cruz', 'Carlos01', '$2y$10$U9tLoHGnYGEfE77gQtu2ouz2sakzL3rZbCqoDRgGv9A8VA9VELyAC', 'johansrodriguezc@juandelcorral.edu.co', 0, 1, '', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `venta_id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_pedido` datetime DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `total` decimal(10,3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`venta_id`, `pedido_id`, `usuario_id`, `fecha_pedido`, `direccion`, `metodo_pago`, `total`) VALUES
(24, 48, 2, '2024-09-26 20:27:14', 'Carrera 89 No 05, Suba Rincón, Bosques de Salamanca', 'Efectivo', 240.000),
(26, 49, 2, '2024-09-26 20:41:14', 'Carrera 89 No 05, Suba Rincón, Bosques de Salamanca', 'Transferencia Nequi', 180.000),
(27, 50, 2, '2024-09-26 20:52:50', 'Carrera 89 No 127 - 05, Suba Rincón, Bosques de Salamanca', 'Transferencia Nequi', 20.000),
(28, 55, 2, '2024-10-05 19:59:53', 'Carrera 89 No 127 - 05, Suba Rincón, Bosques de Salamanca', 'Transferencia Nequi', 80.000),
(29, 56, 2, '2024-10-06 14:23:39', 'Carrera 89 No 127 - 05, Suba Rincón, Bosques de Salamanca', 'Efectivo', 120.000);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`carrito_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`categoria_id`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`pedido_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`producto_id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`usuario_id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`venta_id`),
  ADD KEY `pedido_id` (`pedido_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `carrito_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `categoria_id` int(7) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `pedido_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `producto_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `usuario_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `venta_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usuario_id`),
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`);

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`pedido_id`),
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usuario_id`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`categoria_id`),
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usuario_id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`pedido_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
