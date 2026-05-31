-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 31-05-2026 a las 02:44:29
-- Versión del servidor: 8.0.45-0ubuntu0.22.04.1
-- Versión de PHP: 8.1.2-1ubuntu2.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pistalibre`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anotacion`
--

DROP TABLE IF EXISTS `anotacion`;
CREATE TABLE IF NOT EXISTS `anotacion` (
  `id_anotacion` int NOT NULL AUTO_INCREMENT,
  `id_reserva` int NOT NULL,
  `equipos` text,
  `resultado` varchar(50) DEFAULT NULL,
  `comentario_instalacion` text,
  `valoracion` int DEFAULT NULL,
  PRIMARY KEY (`id_anotacion`),
  KEY `id_reserva` (`id_reserva`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centro_deportivo`
--

DROP TABLE IF EXISTS `centro_deportivo`;
CREATE TABLE IF NOT EXISTS `centro_deportivo` (
  `id_centro` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `hora_apertura` time DEFAULT NULL,
  `hora_cierre` time DEFAULT NULL,
  `abierto_fines_semana` tinyint(1) DEFAULT NULL,
  `observaciones` text,
  PRIMARY KEY (`id_centro`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `centro_deportivo`
--

INSERT INTO `centro_deportivo` (`id_centro`, `nombre`, `direccion`, `ciudad`, `telefono`, `email`, `hora_apertura`, `hora_cierre`, `abierto_fines_semana`, `observaciones`) VALUES
(1, 'CentroDeportivo1', 'Direccion1', 'Ciudad1', '111111111', 'centro1@test.com', '08:00:00', '22:00:00', 1, 'Abierto toda la semana'),
(2, 'CentroDeportivo2', 'Direccion2', 'Ciudad2', '222222222', 'centro2@test.com', '09:00:00', '21:00:00', 0, 'Cerrado fines de semana'),
(3, 'CentroDeportivo3', 'Direccion3', 'Ciudad3', '333333333', 'centro3@test.com', '08:00:00', '20:00:00', 1, 'Horario reducido fines de semana');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deporte`
--

DROP TABLE IF EXISTS `deporte`;
CREATE TABLE IF NOT EXISTS `deporte` (
  `id_deporte` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `duracion_estandar_min` int DEFAULT NULL,
  `color_visualizacion` varchar(20) DEFAULT NULL,
  `observaciones` text,
  PRIMARY KEY (`id_deporte`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `deporte`
--

INSERT INTO `deporte` (`id_deporte`, `nombre`, `duracion_estandar_min`, `color_visualizacion`, `observaciones`) VALUES
(1, 'Tenis', 60, '#2E7D32', 'Deporte individual'),
(2, 'Padel', 60, '#2E7D32', 'Deporte de pareja'),
(3, 'Baloncesto', 60, '#C65D1A', 'Deporte colectivo'),
(4, 'FutbolSala', 60, '#1E5AA8', 'Deporte colectivo'),
(5, 'Atletismo', 60, '#C65D1A', 'Deporte individual'),
(6, 'Fronton', 60, '#1E5AA8', 'Deporte individual');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `excepcion_reserva_fija`
--

DROP TABLE IF EXISTS `excepcion_reserva_fija`;
CREATE TABLE IF NOT EXISTS `excepcion_reserva_fija` (
  `id_excepcion` int NOT NULL AUTO_INCREMENT,
  `id_reserva_fija` int NOT NULL,
  `fecha` date DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `liberada` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_excepcion`),
  KEY `id_reserva_fija` (`id_reserva_fija`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pista`
--

DROP TABLE IF EXISTS `pista`;
CREATE TABLE IF NOT EXISTS `pista` (
  `id_pista` int NOT NULL AUTO_INCREMENT,
  `id_centro` int NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `cubierta` tinyint(1) DEFAULT NULL,
  `iluminacion` tinyint(1) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_pista`),
  KEY `id_centro` (`id_centro`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `pista`
--

INSERT INTO `pista` (`id_pista`, `id_centro`, `nombre`, `cubierta`, `iluminacion`, `estado`) VALUES
(1, 1, 'C1_Tenis1', 0, 1, 'disponible'),
(2, 1, 'C1_Padel1', 1, 1, 'disponible'),
(3, 1, 'C1_Baloncesto1', 1, 1, 'disponible'),
(4, 1, 'C1_FutbolSala1', 1, 1, 'disponible'),
(5, 1, 'C1_Fronton1', 1, 1, 'disponible'),
(6, 2, 'C2_Tenis1', 0, 1, 'disponible'),
(7, 2, 'C2_Tenis2', 0, 1, 'disponible'),
(8, 2, 'C2_Padel1', 1, 1, 'disponible'),
(9, 2, 'C2_Padel2', 1, 1, 'disponible'),
(10, 2, 'C2_Baloncesto1', 1, 1, 'disponible'),
(11, 2, 'C2_FutbolSala1', 1, 1, 'disponible'),
(12, 2, 'C2_Atletismo1', 0, 1, 'disponible'),
(13, 2, 'C2_Fronton1', 1, 1, 'disponible'),
(14, 3, 'C3_Tenis1', 0, 1, 'disponible'),
(15, 3, 'C3_Padel1', 1, 1, 'disponible'),
(16, 3, 'C3_Baloncesto1', 1, 1, 'disponible'),
(17, 3, 'C3_FutbolSala1', 1, 1, 'disponible'),
(18, 3, 'C3_Atletismo1', 0, 1, 'disponible'),
(19, 3, 'C3_Fronton1', 1, 1, 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pista_deporte`
--

DROP TABLE IF EXISTS `pista_deporte`;
CREATE TABLE IF NOT EXISTS `pista_deporte` (
  `id_pista` int NOT NULL,
  `id_deporte` int NOT NULL,
  PRIMARY KEY (`id_pista`,`id_deporte`),
  KEY `id_deporte` (`id_deporte`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `pista_deporte`
--

INSERT INTO `pista_deporte` (`id_pista`, `id_deporte`) VALUES
(1, 1),
(6, 1),
(7, 1),
(14, 1),
(2, 2),
(8, 2),
(9, 2),
(15, 2),
(3, 3),
(10, 3),
(16, 3),
(4, 4),
(11, 4),
(17, 4),
(12, 5),
(18, 5),
(5, 6),
(13, 6),
(19, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantilla_horaria`
--

DROP TABLE IF EXISTS `plantilla_horaria`;
CREATE TABLE IF NOT EXISTS `plantilla_horaria` (
  `id_plantilla` int NOT NULL AUTO_INCREMENT,
  `id_centro` int NOT NULL,
  `dia_semana` varchar(20) DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `cerrado` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_plantilla`),
  KEY `id_centro` (`id_centro`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `plantilla_horaria`
--

INSERT INTO `plantilla_horaria` (`id_plantilla`, `id_centro`, `dia_semana`, `hora_inicio`, `hora_fin`, `cerrado`) VALUES
(1, 1, 'Lunes', '08:00:00', '22:00:00', 0),
(2, 1, 'Martes', '08:00:00', '22:00:00', 0),
(3, 1, 'Miercoles', '08:00:00', '22:00:00', 0),
(4, 1, 'Jueves', '08:00:00', '22:00:00', 0),
(5, 1, 'Viernes', '08:00:00', '22:00:00', 0),
(6, 1, 'Sabado', '08:00:00', '22:00:00', 0),
(7, 1, 'Domingo', '08:00:00', '22:00:00', 0),
(8, 2, 'Lunes', '09:00:00', '21:00:00', 0),
(9, 2, 'Martes', '09:00:00', '21:00:00', 0),
(10, 2, 'Miercoles', '09:00:00', '21:00:00', 0),
(11, 2, 'Jueves', '09:00:00', '21:00:00', 0),
(12, 2, 'Viernes', '09:00:00', '21:00:00', 0),
(13, 2, 'Sabado', '00:00:00', '00:00:00', 1),
(14, 2, 'Domingo', '00:00:00', '00:00:00', 1),
(15, 3, 'Lunes', '08:00:00', '20:00:00', 0),
(16, 3, 'Martes', '08:00:00', '20:00:00', 0),
(17, 3, 'Miercoles', '08:00:00', '20:00:00', 0),
(18, 3, 'Jueves', '08:00:00', '20:00:00', 0),
(19, 3, 'Viernes', '08:00:00', '20:00:00', 0),
(20, 3, 'Sabado', '10:00:00', '14:00:00', 0),
(21, 3, 'Domingo', '10:00:00', '14:00:00', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reserva`
--

DROP TABLE IF EXISTS `reserva`;
CREATE TABLE IF NOT EXISTS `reserva` (
  `id_reserva` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_pista` int NOT NULL,
  `id_deporte` int NOT NULL,
  `fecha` date DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_reserva`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_pista` (`id_pista`),
  KEY `id_deporte` (`id_deporte`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reserva_extra_clase`
--

DROP TABLE IF EXISTS `reserva_extra_clase`;
CREATE TABLE IF NOT EXISTS `reserva_extra_clase` (
  `id_reserva_extra` int NOT NULL AUTO_INCREMENT,
  `id_reserva_fija` int NOT NULL,
  `id_pista` int NOT NULL,
  `fecha` date DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `observaciones` text,
  `activa` tinyint(1) DEFAULT '1',
  `motivo_cancelacion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_reserva_extra`),
  KEY `id_reserva_fija` (`id_reserva_fija`),
  KEY `id_pista` (`id_pista`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reserva_fija`
--

DROP TABLE IF EXISTS `reserva_fija`;
CREATE TABLE IF NOT EXISTS `reserva_fija` (
  `id_reserva_fija` int NOT NULL AUTO_INCREMENT,
  `id_profesor` int NOT NULL,
  `id_pista` int NOT NULL,
  `id_deporte` int NOT NULL,
  `dia_semana` varchar(20) DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activa` tinyint(1) DEFAULT NULL,
  `observaciones` text,
  PRIMARY KEY (`id_reserva_fija`),
  KEY `id_profesor` (`id_profesor`),
  KEY `id_pista` (`id_pista`),
  KEY `id_deporte` (`id_deporte`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reserva_usuario`
--

DROP TABLE IF EXISTS `reserva_usuario`;
CREATE TABLE IF NOT EXISTS `reserva_usuario` (
  `id_reserva` int NOT NULL,
  `id_usuario` int NOT NULL,
  `es_reservador` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_reserva`,`id_usuario`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

DROP TABLE IF EXISTS `rol`;
CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre`) VALUES
(1, 'usuario'),
(2, 'profesor'),
(3, 'admin');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tramo_horario`
--

DROP TABLE IF EXISTS `tramo_horario`;
CREATE TABLE IF NOT EXISTS `tramo_horario` (
  `id_tramo` int NOT NULL AUTO_INCREMENT,
  `id_plantilla` int NOT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `buffer_minutos` int DEFAULT NULL,
  PRIMARY KEY (`id_tramo`),
  KEY `id_plantilla` (`id_plantilla`)
) ENGINE=InnoDB AUTO_INCREMENT=227 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `tramo_horario`
--

INSERT INTO `tramo_horario` (`id_tramo`, `id_plantilla`, `hora_inicio`, `hora_fin`, `buffer_minutos`) VALUES
(1, 1, '08:00:00', '09:00:00', 0),
(2, 1, '09:00:00', '10:00:00', 0),
(3, 1, '10:00:00', '11:00:00', 0),
(4, 1, '11:00:00', '12:00:00', 0),
(5, 1, '12:00:00', '13:00:00', 0),
(6, 1, '13:00:00', '14:00:00', 0),
(7, 1, '14:00:00', '15:00:00', 0),
(8, 1, '15:00:00', '16:00:00', 0),
(9, 1, '16:00:00', '17:00:00', 0),
(10, 1, '17:00:00', '18:00:00', 0),
(11, 1, '18:00:00', '19:00:00', 0),
(12, 1, '19:00:00', '20:00:00', 0),
(13, 1, '20:00:00', '21:00:00', 0),
(14, 1, '21:00:00', '22:00:00', 0),
(15, 2, '08:00:00', '09:00:00', 0),
(16, 2, '09:00:00', '10:00:00', 0),
(17, 2, '10:00:00', '11:00:00', 0),
(18, 2, '11:00:00', '12:00:00', 0),
(19, 2, '12:00:00', '13:00:00', 0),
(20, 2, '13:00:00', '14:00:00', 0),
(21, 2, '14:00:00', '15:00:00', 0),
(22, 2, '15:00:00', '16:00:00', 0),
(23, 2, '16:00:00', '17:00:00', 0),
(24, 2, '17:00:00', '18:00:00', 0),
(25, 2, '18:00:00', '19:00:00', 0),
(26, 2, '19:00:00', '20:00:00', 0),
(27, 2, '20:00:00', '21:00:00', 0),
(28, 2, '21:00:00', '22:00:00', 0),
(29, 3, '08:00:00', '09:00:00', 0),
(30, 3, '09:00:00', '10:00:00', 0),
(31, 3, '10:00:00', '11:00:00', 0),
(32, 3, '11:00:00', '12:00:00', 0),
(33, 3, '12:00:00', '13:00:00', 0),
(34, 3, '13:00:00', '14:00:00', 0),
(35, 3, '14:00:00', '15:00:00', 0),
(36, 3, '15:00:00', '16:00:00', 0),
(37, 3, '16:00:00', '17:00:00', 0),
(38, 3, '17:00:00', '18:00:00', 0),
(39, 3, '18:00:00', '19:00:00', 0),
(40, 3, '19:00:00', '20:00:00', 0),
(41, 3, '20:00:00', '21:00:00', 0),
(42, 3, '21:00:00', '22:00:00', 0),
(43, 4, '08:00:00', '09:00:00', 0),
(44, 4, '09:00:00', '10:00:00', 0),
(45, 4, '10:00:00', '11:00:00', 0),
(46, 4, '11:00:00', '12:00:00', 0),
(47, 4, '12:00:00', '13:00:00', 0),
(48, 4, '13:00:00', '14:00:00', 0),
(49, 4, '14:00:00', '15:00:00', 0),
(50, 4, '15:00:00', '16:00:00', 0),
(51, 4, '16:00:00', '17:00:00', 0),
(52, 4, '17:00:00', '18:00:00', 0),
(53, 4, '18:00:00', '19:00:00', 0),
(54, 4, '19:00:00', '20:00:00', 0),
(55, 4, '20:00:00', '21:00:00', 0),
(56, 4, '21:00:00', '22:00:00', 0),
(57, 5, '08:00:00', '09:00:00', 0),
(58, 5, '09:00:00', '10:00:00', 0),
(59, 5, '10:00:00', '11:00:00', 0),
(60, 5, '11:00:00', '12:00:00', 0),
(61, 5, '12:00:00', '13:00:00', 0),
(62, 5, '13:00:00', '14:00:00', 0),
(63, 5, '14:00:00', '15:00:00', 0),
(64, 5, '15:00:00', '16:00:00', 0),
(65, 5, '16:00:00', '17:00:00', 0),
(66, 5, '17:00:00', '18:00:00', 0),
(67, 5, '18:00:00', '19:00:00', 0),
(68, 5, '19:00:00', '20:00:00', 0),
(69, 5, '20:00:00', '21:00:00', 0),
(70, 5, '21:00:00', '22:00:00', 0),
(71, 6, '08:00:00', '09:00:00', 0),
(72, 6, '09:00:00', '10:00:00', 0),
(73, 6, '10:00:00', '11:00:00', 0),
(74, 6, '11:00:00', '12:00:00', 0),
(75, 6, '12:00:00', '13:00:00', 0),
(76, 6, '13:00:00', '14:00:00', 0),
(77, 6, '14:00:00', '15:00:00', 0),
(78, 6, '15:00:00', '16:00:00', 0),
(79, 6, '16:00:00', '17:00:00', 0),
(80, 6, '17:00:00', '18:00:00', 0),
(81, 6, '18:00:00', '19:00:00', 0),
(82, 6, '19:00:00', '20:00:00', 0),
(83, 6, '20:00:00', '21:00:00', 0),
(84, 6, '21:00:00', '22:00:00', 0),
(85, 7, '08:00:00', '09:00:00', 0),
(86, 7, '09:00:00', '10:00:00', 0),
(87, 7, '10:00:00', '11:00:00', 0),
(88, 7, '11:00:00', '12:00:00', 0),
(89, 7, '12:00:00', '13:00:00', 0),
(90, 7, '13:00:00', '14:00:00', 0),
(91, 7, '14:00:00', '15:00:00', 0),
(92, 7, '15:00:00', '16:00:00', 0),
(93, 7, '16:00:00', '17:00:00', 0),
(94, 7, '17:00:00', '18:00:00', 0),
(95, 7, '18:00:00', '19:00:00', 0),
(96, 7, '19:00:00', '20:00:00', 0),
(97, 7, '20:00:00', '21:00:00', 0),
(98, 7, '21:00:00', '22:00:00', 0),
(99, 8, '09:00:00', '10:00:00', 0),
(100, 8, '10:00:00', '11:00:00', 0),
(101, 8, '11:00:00', '12:00:00', 0),
(102, 8, '12:00:00', '13:00:00', 0),
(103, 8, '13:00:00', '14:00:00', 0),
(104, 8, '14:00:00', '15:00:00', 0),
(105, 8, '15:00:00', '16:00:00', 0),
(106, 8, '16:00:00', '17:00:00', 0),
(107, 8, '17:00:00', '18:00:00', 0),
(108, 8, '18:00:00', '19:00:00', 0),
(109, 8, '19:00:00', '20:00:00', 0),
(110, 8, '20:00:00', '21:00:00', 0),
(111, 9, '09:00:00', '10:00:00', 0),
(112, 9, '10:00:00', '11:00:00', 0),
(113, 9, '11:00:00', '12:00:00', 0),
(114, 9, '12:00:00', '13:00:00', 0),
(115, 9, '13:00:00', '14:00:00', 0),
(116, 9, '14:00:00', '15:00:00', 0),
(117, 9, '15:00:00', '16:00:00', 0),
(118, 9, '16:00:00', '17:00:00', 0),
(119, 9, '17:00:00', '18:00:00', 0),
(120, 9, '18:00:00', '19:00:00', 0),
(121, 9, '19:00:00', '20:00:00', 0),
(122, 9, '20:00:00', '21:00:00', 0),
(123, 10, '09:00:00', '10:00:00', 0),
(124, 10, '10:00:00', '11:00:00', 0),
(125, 10, '11:00:00', '12:00:00', 0),
(126, 10, '12:00:00', '13:00:00', 0),
(127, 10, '13:00:00', '14:00:00', 0),
(128, 10, '14:00:00', '15:00:00', 0),
(129, 10, '15:00:00', '16:00:00', 0),
(130, 10, '16:00:00', '17:00:00', 0),
(131, 10, '17:00:00', '18:00:00', 0),
(132, 10, '18:00:00', '19:00:00', 0),
(133, 10, '19:00:00', '20:00:00', 0),
(134, 10, '20:00:00', '21:00:00', 0),
(135, 11, '09:00:00', '10:00:00', 0),
(136, 11, '10:00:00', '11:00:00', 0),
(137, 11, '11:00:00', '12:00:00', 0),
(138, 11, '12:00:00', '13:00:00', 0),
(139, 11, '13:00:00', '14:00:00', 0),
(140, 11, '14:00:00', '15:00:00', 0),
(141, 11, '15:00:00', '16:00:00', 0),
(142, 11, '16:00:00', '17:00:00', 0),
(143, 11, '17:00:00', '18:00:00', 0),
(144, 11, '18:00:00', '19:00:00', 0),
(145, 11, '19:00:00', '20:00:00', 0),
(146, 11, '20:00:00', '21:00:00', 0),
(147, 12, '09:00:00', '10:00:00', 0),
(148, 12, '10:00:00', '11:00:00', 0),
(149, 12, '11:00:00', '12:00:00', 0),
(150, 12, '12:00:00', '13:00:00', 0),
(151, 12, '13:00:00', '14:00:00', 0),
(152, 12, '14:00:00', '15:00:00', 0),
(153, 12, '15:00:00', '16:00:00', 0),
(154, 12, '16:00:00', '17:00:00', 0),
(155, 12, '17:00:00', '18:00:00', 0),
(156, 12, '18:00:00', '19:00:00', 0),
(157, 12, '19:00:00', '20:00:00', 0),
(158, 12, '20:00:00', '21:00:00', 0),
(159, 15, '08:00:00', '09:00:00', 0),
(160, 15, '09:00:00', '10:00:00', 0),
(161, 15, '10:00:00', '11:00:00', 0),
(162, 15, '11:00:00', '12:00:00', 0),
(163, 15, '12:00:00', '13:00:00', 0),
(164, 15, '13:00:00', '14:00:00', 0),
(165, 15, '14:00:00', '15:00:00', 0),
(166, 15, '15:00:00', '16:00:00', 0),
(167, 15, '16:00:00', '17:00:00', 0),
(168, 15, '17:00:00', '18:00:00', 0),
(169, 15, '18:00:00', '19:00:00', 0),
(170, 15, '19:00:00', '20:00:00', 0),
(171, 16, '08:00:00', '09:00:00', 0),
(172, 16, '09:00:00', '10:00:00', 0),
(173, 16, '10:00:00', '11:00:00', 0),
(174, 16, '11:00:00', '12:00:00', 0),
(175, 16, '12:00:00', '13:00:00', 0),
(176, 16, '13:00:00', '14:00:00', 0),
(177, 16, '14:00:00', '15:00:00', 0),
(178, 16, '15:00:00', '16:00:00', 0),
(179, 16, '16:00:00', '17:00:00', 0),
(180, 16, '17:00:00', '18:00:00', 0),
(181, 16, '18:00:00', '19:00:00', 0),
(182, 16, '19:00:00', '20:00:00', 0),
(183, 17, '08:00:00', '09:00:00', 0),
(184, 17, '09:00:00', '10:00:00', 0),
(185, 17, '10:00:00', '11:00:00', 0),
(186, 17, '11:00:00', '12:00:00', 0),
(187, 17, '12:00:00', '13:00:00', 0),
(188, 17, '13:00:00', '14:00:00', 0),
(189, 17, '14:00:00', '15:00:00', 0),
(190, 17, '15:00:00', '16:00:00', 0),
(191, 17, '16:00:00', '17:00:00', 0),
(192, 17, '17:00:00', '18:00:00', 0),
(193, 17, '18:00:00', '19:00:00', 0),
(194, 17, '19:00:00', '20:00:00', 0),
(195, 18, '08:00:00', '09:00:00', 0),
(196, 18, '09:00:00', '10:00:00', 0),
(197, 18, '10:00:00', '11:00:00', 0),
(198, 18, '11:00:00', '12:00:00', 0),
(199, 18, '12:00:00', '13:00:00', 0),
(200, 18, '13:00:00', '14:00:00', 0),
(201, 18, '14:00:00', '15:00:00', 0),
(202, 18, '15:00:00', '16:00:00', 0),
(203, 18, '16:00:00', '17:00:00', 0),
(204, 18, '17:00:00', '18:00:00', 0),
(205, 18, '18:00:00', '19:00:00', 0),
(206, 18, '19:00:00', '20:00:00', 0),
(207, 19, '08:00:00', '09:00:00', 0),
(208, 19, '09:00:00', '10:00:00', 0),
(209, 19, '10:00:00', '11:00:00', 0),
(210, 19, '11:00:00', '12:00:00', 0),
(211, 19, '12:00:00', '13:00:00', 0),
(212, 19, '13:00:00', '14:00:00', 0),
(213, 19, '14:00:00', '15:00:00', 0),
(214, 19, '15:00:00', '16:00:00', 0),
(215, 19, '16:00:00', '17:00:00', 0),
(216, 19, '17:00:00', '18:00:00', 0),
(217, 19, '18:00:00', '19:00:00', 0),
(218, 19, '19:00:00', '20:00:00', 0),
(219, 20, '10:00:00', '11:00:00', 0),
(220, 20, '11:00:00', '12:00:00', 0),
(221, 20, '12:00:00', '13:00:00', 0),
(222, 20, '13:00:00', '14:00:00', 0),
(223, 21, '10:00:00', '11:00:00', 0),
(224, 21, '11:00:00', '12:00:00', 0),
(225, 21, '12:00:00', '13:00:00', 0),
(226, 21, '13:00:00', '14:00:00', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) DEFAULT NULL,
  `apellidos` varchar(150) DEFAULT NULL,
  `alias` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `alias` (`alias`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellidos`, `alias`, `email`, `password_hash`, `fecha_registro`, `activo`) VALUES
(1, 'Jose Maria', 'Nuñez Gonzalez', 'Chema', 'chema@test.com', '$2y$10$LMoywRFKa50MctLriJhSE.Fns5BgD5H5TSck4W4MVtXjFwI9mUegW', '2026-03-22 00:05:08', 1),
(2, 'NombreRegistro2', 'Apellido1Registro2 Apellido2Registro2', 'Registro2', 'Registro2@test.com', '$2y$10$zG18HuvMpiJzJdfGgyWqzuqGpKIn7Z4vByBuWTYk8hsIY0LS69P2.', '2026-05-26 21:54:17', 1),
(3, 'NombreProfesor1', 'Apellido1Profesor1 Apellido2Profesor1', 'Profesor1', 'profesor1@test.com', '$2y$10$5x8zhZoAjAcB.X/uDbpGS.EvNozQmbly9Ox9nHCh550.CEitwFM5O', '2026-05-28 15:26:01', 1),
(4, 'NombreRegistro3', 'Apellido1Registro3 Apellido2Registro3', 'Registro3', 'registro3@test.com', '$2y$10$g3eT1jPgruHsMh34LMyIa.h3ymKVkNilxXsSGcyv9JMwojST7RYWm', '2026-05-28 16:13:48', 1),
(5, 'NombreRegistro4', 'Apellido1Registro4 Apellido2Registro4', 'Registro4', 'registro4@test.com', '$2y$10$Z9vC4A4jMix40CmXOIFh3eJcRGTKw6ebAm0GMg5ej7pryQy0nH9eS', '2026-05-28 17:45:18', 0),
(6, 'NombreAdministrador1', 'Apellido1Administrador1 Apellido2Administrador1', 'Administrador1', 'administrador1@test.com', '$2y$10$dxxS6ShG0/s1JGniuw3.J.8jjCg48/ES2nhCi70Qz3fGJLnJsc2z2', '2026-05-30 22:05:52', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_rol`
--

DROP TABLE IF EXISTS `usuario_rol`;
CREATE TABLE IF NOT EXISTS `usuario_rol` (
  `id_usuario` int NOT NULL,
  `id_rol` int NOT NULL,
  PRIMARY KEY (`id_usuario`,`id_rol`),
  KEY `id_rol` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuario_rol`
--

INSERT INTO `usuario_rol` (`id_usuario`, `id_rol`) VALUES
(2, 1),
(4, 1),
(5, 1),
(3, 2),
(1, 3),
(6, 3);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `anotacion`
--
ALTER TABLE `anotacion`
  ADD CONSTRAINT `anotacion_ibfk_1` FOREIGN KEY (`id_reserva`) REFERENCES `reserva` (`id_reserva`);

--
-- Filtros para la tabla `excepcion_reserva_fija`
--
ALTER TABLE `excepcion_reserva_fija`
  ADD CONSTRAINT `excepcion_reserva_fija_ibfk_1` FOREIGN KEY (`id_reserva_fija`) REFERENCES `reserva_fija` (`id_reserva_fija`);

--
-- Filtros para la tabla `pista`
--
ALTER TABLE `pista`
  ADD CONSTRAINT `pista_ibfk_1` FOREIGN KEY (`id_centro`) REFERENCES `centro_deportivo` (`id_centro`);

--
-- Filtros para la tabla `pista_deporte`
--
ALTER TABLE `pista_deporte`
  ADD CONSTRAINT `pista_deporte_ibfk_1` FOREIGN KEY (`id_pista`) REFERENCES `pista` (`id_pista`),
  ADD CONSTRAINT `pista_deporte_ibfk_2` FOREIGN KEY (`id_deporte`) REFERENCES `deporte` (`id_deporte`);

--
-- Filtros para la tabla `plantilla_horaria`
--
ALTER TABLE `plantilla_horaria`
  ADD CONSTRAINT `plantilla_horaria_ibfk_1` FOREIGN KEY (`id_centro`) REFERENCES `centro_deportivo` (`id_centro`);

--
-- Filtros para la tabla `reserva`
--
ALTER TABLE `reserva`
  ADD CONSTRAINT `reserva_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `reserva_ibfk_2` FOREIGN KEY (`id_pista`) REFERENCES `pista` (`id_pista`),
  ADD CONSTRAINT `reserva_ibfk_3` FOREIGN KEY (`id_deporte`) REFERENCES `deporte` (`id_deporte`);

--
-- Filtros para la tabla `reserva_extra_clase`
--
ALTER TABLE `reserva_extra_clase`
  ADD CONSTRAINT `reserva_extra_clase_ibfk_1` FOREIGN KEY (`id_reserva_fija`) REFERENCES `reserva_fija` (`id_reserva_fija`),
  ADD CONSTRAINT `reserva_extra_clase_ibfk_2` FOREIGN KEY (`id_pista`) REFERENCES `pista` (`id_pista`);

--
-- Filtros para la tabla `reserva_fija`
--
ALTER TABLE `reserva_fija`
  ADD CONSTRAINT `reserva_fija_ibfk_1` FOREIGN KEY (`id_profesor`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `reserva_fija_ibfk_2` FOREIGN KEY (`id_pista`) REFERENCES `pista` (`id_pista`),
  ADD CONSTRAINT `reserva_fija_ibfk_3` FOREIGN KEY (`id_deporte`) REFERENCES `deporte` (`id_deporte`);

--
-- Filtros para la tabla `reserva_usuario`
--
ALTER TABLE `reserva_usuario`
  ADD CONSTRAINT `reserva_usuario_ibfk_1` FOREIGN KEY (`id_reserva`) REFERENCES `reserva` (`id_reserva`),
  ADD CONSTRAINT `reserva_usuario_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `tramo_horario`
--
ALTER TABLE `tramo_horario`
  ADD CONSTRAINT `tramo_horario_ibfk_1` FOREIGN KEY (`id_plantilla`) REFERENCES `plantilla_horaria` (`id_plantilla`);

--
-- Filtros para la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD CONSTRAINT `usuario_rol_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `usuario_rol_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
