-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-06-2025 a las 03:47:03
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `turismo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id_admin` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id_admin`, `nombre`, `email`, `contraseña`, `fecha_registro`) VALUES
(2, 'maximo avalos', 'maximo@gmail.com', '$2y$10$5sLPPDE4C5eMBtMpAY.Fge46GcqPrdiKiAr4UJ/FmUq5Xqfg11gme', '2025-06-26 15:55:04'),
(3, 'maximo avalos', 'maximo1312@gmail.com', '$2y$10$4s/UoW3qdFC5umqsxkfCe.SjrT0wDroos3y8pB.bM5gEBYrpCj9iK', '2025-06-26 16:07:03'),
(4, 'lautaro', 'lauta1312@gmail.com', '$2y$10$/WQNIycGSEwxZhZ4e03p7OjhjAJ1qsb2SQXnBA4nnkrJa0/Bxg/Ri', '2025-06-26 23:05:31'),
(5, 'Alex Tintaya', 'Alaits@hotmail.com', '$2y$10$1b98YTXX5zvDH3pjxvL.j.05J2S1pM9aR.w050lfAPllXrvdI4WS2', '2025-06-27 04:32:23'),
(6, 'Maximo Avalos Spotorno', 'MaximoAvalos@gmail.com', '$2y$10$zgpGk3Cib6H1uEMrxT5au.zkUf5a.vkT9qfMaCbbUJMRg0n3AzFIS', '2025-06-27 04:34:20'),
(7, 'Lautaro Frizzo', 'danilo@gmail.com', '$2y$10$qjQ.6dCIDT8zVjDJ6vXaMeOJCxQPVscGpGI79d24wcJOnigUwY1ce', '2025-06-27 18:32:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `fecha_registro` date DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `apellido`, `email`, `contraseña`, `fecha_registro`) VALUES
(4, 'lautaro', 'ardiles', 'lauta1312@gmail.com', '$2y$10$gpuERB2xVNssIONIcVIrse67ysqJLzPR9.CeDo/n791Oes4XmdR3G', '2025-06-25'),
(5, 'Maximo', 'Avalos', 'alaits@hotmail.com', '$2y$10$Zd1MnqAvamUwpQpz3.99qeYL5KChGkTItiCzYhYvokM30l4/PTZfG', '2025-06-26'),
(6, 'maxomas', 'Avalos', 'daniilorizzo@gmail.com', '$2y$10$fy.vVsKpjmVDrIq.v6nMC.XTF3FlSuOcqUW6Q31a4FdCswr0MD/ZK', '2025-06-26'),
(7, 'Skibidi', 'Sigma', 'brunomontenegro.abcde@gmail.com', '$2y$10$9b/JSc4xaO4k5qxUkJo9e.X9Cb5n3izUsvyBvdWj4Qie4FAKpZnNy', '2025-06-26'),
(8, 'Pilin', 'Trump', 'papajonessorrentinos@gmail.com', '$2y$10$FAe1iaz/DIiLUC/3REjB8exK9dN6cac.u4fzbgToBueQPz5Hxyd7m', '2025-06-26'),
(9, 'asdsa', 'asdasd', 'asdas@gmai.com', '$2y$10$tscTlFgc5MlV69C7kPmRyOf1LcalEDhoYpwBRiSiBKp0o/Y6p3YGG', '2025-06-27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(3, 3, 8, 1, 200000.00),
(4, 4, 8, 1, 200000.00),
(5, 5, 8, 1, 200000.00),
(9, 7, 6, 1, 80000.00),
(10, 8, 6, 2, 80000.00),
(11, 9, 6, 1, 80000.00),
(13, 10, 6, 1, 80000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha_pedido` datetime DEFAULT current_timestamp(),
  `estado` varchar(50) DEFAULT 'Pendiente',
  `total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_cliente`, `fecha_pedido`, `estado`, `total`) VALUES
(1, 5, '2025-06-26 14:41:02', 'Anulado', 240000.00),
(2, 5, '2025-06-26 14:47:15', 'Entregado', 120000.00),
(3, 5, '2025-06-26 14:55:30', 'Entregado', 200000.00),
(4, 6, '2025-06-26 14:59:48', 'Anulado', 200000.00),
(5, 4, '2025-06-26 15:19:41', 'Anulado', 200000.00),
(7, 4, '2025-06-27 04:12:57', 'Anulado', 80000.00),
(8, 4, '2025-06-27 04:18:21', 'Anulado', 160000.00),
(9, 4, '2025-06-27 04:28:26', 'Entregado', 140000.00),
(10, 4, '2025-06-27 04:29:03', 'Anulado', 80000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `codigo_producto` varchar(20) NOT NULL,
  `descripcion` text NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `destino` varchar(100) DEFAULT 'Argentina',
  `duracion_dias` int(11) DEFAULT 7
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `codigo_producto`, `descripcion`, `precio_unitario`, `disponible`, `destino`, `duracion_dias`) VALUES
(6, 'PKG002', 'Buenos Aires cultural', 80000.00, 1, 'Buenos Aires', 5),
(7, 'PKG003', 'Salta - Norte Argentino', 95000.00, 1, 'Salta', 6),
(8, 'PKG004', 'Brasil - All Inclusive', 200000.00, 1, 'Brasil', 8),
(9, 'PKG005', 'Patagonia Mágica', 175000.00, 1, 'El Calafate', 7),
(10, 'PKG006', 'Mendoza y los vinos', 110000.00, 1, 'Mendoza', 5),
(12, 'PKG007', 'Chile - Ciudad', 75000.00, 1, 'Argentina', 7),
(13, 'PKG008', 'Mexico - Tamaulipas', 160000.00, 1, 'Argentina', 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `fecha_venta` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `codigo_producto` (`codigo_producto`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD UNIQUE KEY `id_pedido` (`id_pedido`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`),
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
