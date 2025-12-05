-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-10-2025 a las 01:50:20
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
-- Base de datos: `rrhh_tec`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aprobaciones_permiso`
--

CREATE TABLE `aprobaciones_permiso` (
  `id_aprobacion` int(11) NOT NULL COMMENT 'Identificador de aprobación',
  `id_solicitud` int(11) NOT NULL COMMENT 'Solicitud asociada',
  `aprobador` int(11) NOT NULL COMMENT 'Usuario aprobador',
  `nivel` smallint(6) NOT NULL DEFAULT 1 COMMENT 'Nivel de aprobación',
  `decision` enum('PENDIENTE','APROBADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE' COMMENT 'Decisión',
  `comentario` varchar(200) DEFAULT NULL COMMENT 'Comentario del aprobador',
  `decidido_en` datetime DEFAULT NULL COMMENT 'Fecha/hora de decisión'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Niveles de aprobación por solicitud';

--
-- Volcado de datos para la tabla `aprobaciones_permiso`
--

INSERT INTO `aprobaciones_permiso` (`id_aprobacion`, `id_solicitud`, `aprobador`, `nivel`, `decision`, `comentario`, `decidido_en`) VALUES
(1, 1, 1, 1, 'PENDIENTE', 'En revisión', NULL),
(2, 2, 1, 1, 'APROBADO', 'Permiso concedido', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas`
--

CREATE TABLE `areas` (
  `id_area` int(11) NOT NULL COMMENT 'Identificador del área/departamento',
  `id_empresa` int(11) NOT NULL COMMENT 'Empresa dueña del área',
  `id_area_padre` int(11) DEFAULT NULL COMMENT 'Área padre para jerarquía (autorreferencia)',
  `nombre_area` varchar(100) NOT NULL COMMENT 'Nombre del área',
  `descripcion` varchar(200) DEFAULT NULL COMMENT 'Descripción del área',
  `activa` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Área activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Áreas y jerarquías';

--
-- Volcado de datos para la tabla `areas`
--

INSERT INTO `areas` (`id_area`, `id_empresa`, `id_area_padre`, `nombre_area`, `descripcion`, `activa`) VALUES
(1, 1, NULL, 'Recursos Humanos', 'Área encargada de personal y nómina', 1),
(2, 1, 1, 'Reclutamiento', 'Subárea de Recursos Humanos', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_registros`
--

CREATE TABLE `asistencia_registros` (
  `id_asistencia` bigint(20) NOT NULL COMMENT 'Identificador del registro',
  `id_empleado` int(11) NOT NULL COMMENT 'Empleado',
  `fecha` date NOT NULL COMMENT 'Fecha del día laboral',
  `hora_entrada` time DEFAULT NULL COMMENT 'Hora de entrada',
  `hora_salida` time DEFAULT NULL COMMENT 'Hora de salida',
  `tipo` enum('NORMAL','RETARDO','FALTA','JUSTIFICADO') NOT NULL DEFAULT 'NORMAL' COMMENT 'Clasificación del día',
  `origen` enum('MANUAL','RELOJ','IMPORTACION') NOT NULL DEFAULT 'RELOJ' COMMENT 'Origen del registro',
  `observaciones` varchar(200) DEFAULT NULL COMMENT 'Notas u observaciones'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Asistencia diaria por empleado';

--
-- Volcado de datos para la tabla `asistencia_registros`
--

INSERT INTO `asistencia_registros` (`id_asistencia`, `id_empleado`, `fecha`, `hora_entrada`, `hora_salida`, `tipo`, `origen`, `observaciones`) VALUES
(1, 1, '2025-10-10', '08:03:00', '16:01:00', 'NORMAL', 'RELOJ', 'Entrada puntual'),
(2, 2, '2025-10-10', '08:20:00', '16:15:00', 'RETARDO', 'RELOJ', 'Llegó tarde');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calendarios_laborales`
--

CREATE TABLE `calendarios_laborales` (
  `id_calendario` int(11) NOT NULL COMMENT 'Identificador del calendario',
  `id_empresa` int(11) NOT NULL COMMENT 'Empresa propietaria',
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre del calendario',
  `descripcion` varchar(200) DEFAULT NULL COMMENT 'Descripción del calendario',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Calendario activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Calendarios laborales';

--
-- Volcado de datos para la tabla `calendarios_laborales`
--

INSERT INTO `calendarios_laborales` (`id_calendario`, `id_empresa`, `nombre`, `descripcion`, `activo`) VALUES
(1, 1, 'Calendario 2025', 'Calendario laboral principal', 1),
(2, 2, 'Calendario Monterrey', 'Días laborales Monterrey', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calendario_feriados`
--

CREATE TABLE `calendario_feriados` (
  `id_feriado` int(11) NOT NULL COMMENT 'Identificador del feriado',
  `id_calendario` int(11) NOT NULL COMMENT 'Calendario asociado',
  `fecha` date NOT NULL COMMENT 'Fecha del feriado',
  `descripcion` varchar(150) NOT NULL COMMENT 'Descripción del feriado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Feriados por calendario';

--
-- Volcado de datos para la tabla `calendario_feriados`
--

INSERT INTO `calendario_feriados` (`id_feriado`, `id_calendario`, `fecha`, `descripcion`) VALUES
(1, 1, '2025-01-01', 'Año Nuevo'),
(2, 2, '2025-09-16', 'Día de la Independencia');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `candidatos`
--

CREATE TABLE `candidatos` (
  `id_candidato` int(11) NOT NULL COMMENT 'Identificador del candidato',
  `nombre` varchar(120) NOT NULL COMMENT 'Nombre completo',
  `correo` varchar(120) DEFAULT NULL COMMENT 'Correo de contacto',
  `telefono` varchar(20) DEFAULT NULL COMMENT 'Teléfono',
  `cv` varchar(255) DEFAULT NULL COMMENT 'Ruta/URL del CV',
  `fuente` varchar(80) DEFAULT NULL COMMENT 'Fuente de reclutamiento (LinkedIn, referido, etc.)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Banco de candidatos';

--
-- Volcado de datos para la tabla `candidatos`
--

INSERT INTO `candidatos` (`id_candidato`, `nombre`, `correo`, `telefono`, `cv`, `fuente`) VALUES
(1, 'Pedro Martínez', 'pedro.martinez@gmail.com', '555-9876543', '/cv/pedro.pdf', 'LinkedIn'),
(2, 'Lucía Hernández', 'lucia.hernandez@gmail.com', '555-5678901', '/cv/lucia.pdf', 'Indeed');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conceptos_nomina`
--

CREATE TABLE `conceptos_nomina` (
  `id_concepto` int(11) NOT NULL COMMENT 'Identificador del concepto',
  `tipo` enum('PERCEPCION','DEDUCCION') NOT NULL COMMENT 'Tipo de concepto',
  `clave` varchar(20) NOT NULL COMMENT 'Clave corta (p.ej. SUELDO, ISR)',
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre descriptivo',
  `formula` varchar(255) DEFAULT NULL COMMENT 'Expresión de cálculo (si aplica)',
  `gravable` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Si integra al salario base'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de conceptos de nómina';

--
-- Volcado de datos para la tabla `conceptos_nomina`
--

INSERT INTO `conceptos_nomina` (`id_concepto`, `tipo`, `clave`, `nombre`, `formula`, `gravable`) VALUES
(1, 'PERCEPCION', 'SUELDO', 'Sueldo Base', NULL, 1),
(2, 'DEDUCCION', 'ISR', 'Impuesto Sobre la Renta', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id_empleado` int(11) NOT NULL COMMENT 'Identificador del empleado',
  `id_usuario` int(11) DEFAULT NULL COMMENT 'Usuario del sistema (si aplica)',
  `id_puesto` int(11) NOT NULL COMMENT 'Puesto actual',
  `id_turno` int(11) DEFAULT NULL COMMENT 'Turno asignado',
  `id_ubicacion` int(11) DEFAULT NULL COMMENT 'Ubicación de trabajo',
  `nombre` varchar(120) NOT NULL COMMENT 'Nombre completo',
  `curp` varchar(18) DEFAULT NULL COMMENT 'CURP (si aplica)',
  `rfc` varchar(13) DEFAULT NULL COMMENT 'RFC (si aplica)',
  `nss` varchar(15) DEFAULT NULL COMMENT 'Número de seguro social',
  `fecha_nacimiento` date DEFAULT NULL COMMENT 'Fecha de nacimiento',
  `genero` enum('M','F','OTRO') NOT NULL DEFAULT 'OTRO' COMMENT 'Género',
  `estado_civil` varchar(20) DEFAULT NULL COMMENT 'Estado civil',
  `direccion` text DEFAULT NULL COMMENT 'Domicilio particular',
  `telefono` varchar(20) DEFAULT NULL COMMENT 'Teléfono de contacto',
  `correo` varchar(120) DEFAULT NULL COMMENT 'Correo personal',
  `fecha_ingreso` date NOT NULL COMMENT 'Fecha de ingreso',
  `fecha_baja` date DEFAULT NULL COMMENT 'Fecha de baja (si aplica)',
  `estado` enum('ACTIVO','BAJA') NOT NULL DEFAULT 'ACTIVO' COMMENT 'Estado laboral',
  `jefe_inmediato` int(11) DEFAULT NULL COMMENT 'ID del jefe inmediato'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Expediente principal de empleados';

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id_empleado`, `id_usuario`, `id_puesto`, `id_turno`, `id_ubicacion`, `nombre`, `curp`, `rfc`, `nss`, `fecha_nacimiento`, `genero`, `estado_civil`, `direccion`, `telefono`, `correo`, `fecha_ingreso`, `fecha_baja`, `estado`, `jefe_inmediato`) VALUES
(1, 2, 1, 1, 1, 'Juan Pérez López', 'PELJ900101HDFRRN09', 'PELJ900101AA1', '12345678901', '1990-01-01', 'M', 'Soltero', 'Calle Flores 123', '555-1234567', 'juan.perez@empresa.com', '2022-05-10', NULL, 'ACTIVO', NULL),
(2, NULL, 2, 2, 2, 'María Gómez Ruiz', 'GORM920202MDFRZN08', 'GORM920202BB2', '98765432109', '1992-02-02', 'F', 'Casada', 'Av. Norte 45', '555-7654321', 'maria.gomez@empresa.com', '2023-03-01', NULL, 'ACTIVO', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados_banco`
--

CREATE TABLE `empleados_banco` (
  `id_banco` int(11) NOT NULL COMMENT 'Identificador bancario',
  `id_empleado` int(11) NOT NULL COMMENT 'Empleado dueño',
  `banco` varchar(80) NOT NULL COMMENT 'Nombre del banco',
  `clabe` varchar(18) NOT NULL COMMENT 'CLABE/cuenta para dispersión',
  `titular` varchar(120) NOT NULL COMMENT 'Titular de la cuenta',
  `activa` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Cuenta vigente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cuentas bancarias para pago de nómina';

--
-- Volcado de datos para la tabla `empleados_banco`
--

INSERT INTO `empleados_banco` (`id_banco`, `id_empleado`, `banco`, `clabe`, `titular`, `activa`) VALUES
(1, 1, 'BBVA', '012345678901234567', 'Juan Pérez López', 1),
(2, 2, 'Santander', '765432109876543210', 'María Gómez Ruiz', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados_contactos`
--

CREATE TABLE `empleados_contactos` (
  `id_contacto` int(11) NOT NULL COMMENT 'Identificador de contacto',
  `id_empleado` int(11) NOT NULL COMMENT 'Empleado relacionado',
  `tipo` enum('EMERGENCIA','PERSONAL','OTRO') NOT NULL DEFAULT 'EMERGENCIA' COMMENT 'Tipo de contacto',
  `nombre` varchar(120) NOT NULL COMMENT 'Nombre del contacto',
  `telefono` varchar(20) NOT NULL COMMENT 'Teléfono del contacto',
  `correo` varchar(120) DEFAULT NULL COMMENT 'Correo del contacto',
  `parentesco` varchar(60) DEFAULT NULL COMMENT 'Parentesco/relación',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Registro activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Contactos (emergencia/personal) por empleado';

--
-- Volcado de datos para la tabla `empleados_contactos`
--

INSERT INTO `empleados_contactos` (`id_contacto`, `id_empleado`, `tipo`, `nombre`, `telefono`, `correo`, `parentesco`, `activo`) VALUES
(1, 1, 'EMERGENCIA', 'Laura Pérez', '555-112233', 'laura.perez@gmail.com', 'Hermana', 1),
(2, 2, 'EMERGENCIA', 'Carlos Ruiz', '555-223344', 'carlos.ruiz@gmail.com', 'Esposo', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados_documentos`
--

CREATE TABLE `empleados_documentos` (
  `id_documento` int(11) NOT NULL COMMENT 'Identificador del documento',
  `id_empleado` int(11) NOT NULL COMMENT 'Empleado dueño',
  `tipo` varchar(80) NOT NULL COMMENT 'Tipo (INE, CURP, Contrato, etc.)',
  `ruta` varchar(255) NOT NULL COMMENT 'Ruta/URL del archivo',
  `valido_desde` date DEFAULT NULL COMMENT 'Inicio de validez',
  `valido_hasta` date DEFAULT NULL COMMENT 'Fin de vigencia',
  `verificado` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si fue verificado',
  `subido_por` int(11) DEFAULT NULL COMMENT 'Usuario que carga',
  `subido_en` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha/hora de carga'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Documentos del expediente';

--
-- Volcado de datos para la tabla `empleados_documentos`
--

INSERT INTO `empleados_documentos` (`id_documento`, `id_empleado`, `tipo`, `ruta`, `valido_desde`, `valido_hasta`, `verificado`, `subido_por`, `subido_en`) VALUES
(1, 1, 'INE', '/docs/juan_ine.pdf', '2022-01-01', '2032-01-01', 1, 1, '2025-10-22 15:43:47'),
(2, 2, 'CURP', '/docs/maria_curp.pdf', '2023-01-01', '2033-01-01', 1, 1, '2025-10-22 15:43:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados_historial`
--

CREATE TABLE `empleados_historial` (
  `id_historial` int(11) NOT NULL COMMENT 'Identificador del evento',
  `id_empleado` int(11) NOT NULL COMMENT 'Empleado afectado',
  `tipo_cambio` enum('ALTA','REINGRESO','BAJA','CAMBIO_PUESTO','CAMBIO_SALARIO','CAMBIO_AREA','CAMBIO_JEFE') NOT NULL COMMENT 'Tipo de cambio',
  `detalle` varchar(255) DEFAULT NULL COMMENT 'Descripción del cambio',
  `valor_anterior` varchar(255) DEFAULT NULL COMMENT 'Valor previo',
  `valor_nuevo` varchar(255) DEFAULT NULL COMMENT 'Valor nuevo',
  `realizado_por` int(11) DEFAULT NULL COMMENT 'Usuario responsable',
  `realizado_en` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha/hora del cambio'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Bitácora de cambios del expediente';

--
-- Volcado de datos para la tabla `empleados_historial`
--

INSERT INTO `empleados_historial` (`id_historial`, `id_empleado`, `tipo_cambio`, `detalle`, `valor_anterior`, `valor_nuevo`, `realizado_por`, `realizado_en`) VALUES
(1, 1, 'ALTA', 'Ingreso al sistema', NULL, 'Activo', 1, '2025-10-22 15:43:47'),
(2, 2, 'ALTA', 'Ingreso al sistema', NULL, 'Activo', 1, '2025-10-22 15:43:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id_empresa` int(11) NOT NULL COMMENT 'Identificador de la empresa',
  `nombre` varchar(120) NOT NULL COMMENT 'Nombre legal/comercial',
  `rfc` varchar(20) DEFAULT NULL COMMENT 'Registro fiscal',
  `correo_contacto` varchar(120) DEFAULT NULL COMMENT 'Correo de contacto',
  `telefono` varchar(20) DEFAULT NULL COMMENT 'Teléfono principal',
  `direccion` text DEFAULT NULL COMMENT 'Domicilio completo',
  `activa` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Empresa activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Empresas registradas';

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id_empresa`, `nombre`, `rfc`, `correo_contacto`, `telefono`, `direccion`, `activa`) VALUES
(1, 'Tecnologías Globales SA de CV', 'TGS123456AB1', 'contacto@tglobal.com', '555-112233', 'Av. Reforma 100, CDMX', 1),
(2, 'Innova Digital MX', 'IDM987654CD2', 'info@innovadigital.mx', '555-445566', 'Calle Insurgentes 300, CDMX', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrevistas`
--

CREATE TABLE `entrevistas` (
  `id_entrevista` int(11) NOT NULL COMMENT 'Identificador de entrevista',
  `id_postulacion` int(11) NOT NULL COMMENT 'Postulación asociada',
  `entrevistador` int(11) NOT NULL COMMENT 'Usuario entrevistador',
  `programada_para` datetime NOT NULL COMMENT 'Fecha/hora programada',
  `resultado` enum('PENDIENTE','APROBADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE' COMMENT 'Resultado',
  `notas` varchar(255) DEFAULT NULL COMMENT 'Notas de la entrevista'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Entrevistas de candidatos';

--
-- Volcado de datos para la tabla `entrevistas`
--

INSERT INTO `entrevistas` (`id_entrevista`, `id_postulacion`, `entrevistador`, `programada_para`, `resultado`, `notas`) VALUES
(1, 1, 1, '2025-10-12 10:00:00', 'PENDIENTE', 'Primera entrevista'),
(2, 2, 1, '2025-10-13 11:00:00', 'PENDIENTE', 'Revisión inicial');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nomina_detalle`
--

CREATE TABLE `nomina_detalle` (
  `id_detalle` bigint(20) NOT NULL COMMENT 'Identificador del renglón',
  `id_nomina` int(11) NOT NULL COMMENT 'FK a nómina_empleado',
  `id_concepto` int(11) NOT NULL COMMENT 'Concepto aplicado',
  `monto` decimal(14,2) NOT NULL COMMENT 'Monto del concepto',
  `observacion` varchar(200) DEFAULT NULL COMMENT 'Nota/observación'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Renglones de percepciones/deducciones';

--
-- Volcado de datos para la tabla `nomina_detalle`
--

INSERT INTO `nomina_detalle` (`id_detalle`, `id_nomina`, `id_concepto`, `monto`, `observacion`) VALUES
(1, 1, 1, 12000.00, 'Pago quincenal'),
(2, 1, 2, -1800.00, 'Retención ISR');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nomina_empleado`
--

CREATE TABLE `nomina_empleado` (
  `id_nomina` int(11) NOT NULL COMMENT 'Identificador de nómina por empleado/periodo',
  `id_empleado` int(11) NOT NULL COMMENT 'Empleado',
  `id_periodo` int(11) NOT NULL COMMENT 'Periodo de nómina',
  `total_percepciones` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Total de percepciones',
  `total_deducciones` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Total de deducciones',
  `total_neto` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Neto a pagar',
  `generado_en` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha/hora de generación'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Totales por empleado y periodo';

--
-- Volcado de datos para la tabla `nomina_empleado`
--

INSERT INTO `nomina_empleado` (`id_nomina`, `id_empleado`, `id_periodo`, `total_percepciones`, `total_deducciones`, `total_neto`, `generado_en`) VALUES
(1, 1, 1, 12000.00, 1800.00, 10200.00, '2025-10-22 15:43:47'),
(2, 2, 1, 10000.00, 1500.00, 8500.00, '2025-10-22 15:43:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos_nomina`
--

CREATE TABLE `periodos_nomina` (
  `id_periodo` int(11) NOT NULL COMMENT 'Identificador del periodo',
  `id_empresa` int(11) NOT NULL COMMENT 'Empresa del periodo',
  `tipo` enum('SEMANAL','QUINCENAL','MENSUAL') NOT NULL COMMENT 'Tipo de periodo',
  `fecha_inicio` date NOT NULL COMMENT 'Inicio del periodo',
  `fecha_fin` date NOT NULL COMMENT 'Fin del periodo',
  `estado` enum('ABIERTO','CERRADO') NOT NULL DEFAULT 'ABIERTO' COMMENT 'Estado del periodo'
) ;

--
-- Volcado de datos para la tabla `periodos_nomina`
--

INSERT INTO `periodos_nomina` (`id_periodo`, `id_empresa`, `tipo`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
(1, 1, 'QUINCENAL', '2025-10-01', '2025-10-15', 'CERRADO'),
(2, 1, 'QUINCENAL', '2025-10-16', '2025-10-31', 'ABIERTO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos_sistema`
--

CREATE TABLE `permisos_sistema` (
  `id_permiso` int(11) NOT NULL COMMENT 'Identificador del permiso',
  `clave` varchar(80) NOT NULL COMMENT 'Clave técnica del permiso (ej. EMPLEADOS_VER)',
  `descripcion` varchar(200) NOT NULL COMMENT 'Descripción funcional del permiso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Permisos granulares del sistema';

--
-- Volcado de datos para la tabla `permisos_sistema`
--

INSERT INTO `permisos_sistema` (`id_permiso`, `clave`, `descripcion`) VALUES
(1, 'USUARIOS_VER', 'Permite ver usuarios'),
(2, 'EMPLEADOS_EDITAR', 'Permite editar empleados');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `politicas_vacaciones`
--

CREATE TABLE `politicas_vacaciones` (
  `id_politica` int(11) NOT NULL COMMENT 'Identificador de política',
  `id_empresa` int(11) NOT NULL COMMENT 'Empresa',
  `dias_inicio` int(11) NOT NULL DEFAULT 6 COMMENT 'Días base al primer año',
  `incremento_anual` int(11) NOT NULL DEFAULT 2 COMMENT 'Incremento por antigüedad',
  `dias_max` int(11) NOT NULL DEFAULT 20 COMMENT 'Tope máximo de días',
  `periodo_anual_inicio` date DEFAULT NULL COMMENT 'Inicio del ciclo anual (opcional)',
  `activa` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Política activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Política de vacaciones por empresa';

--
-- Volcado de datos para la tabla `politicas_vacaciones`
--

INSERT INTO `politicas_vacaciones` (`id_politica`, `id_empresa`, `dias_inicio`, `incremento_anual`, `dias_max`, `periodo_anual_inicio`, `activa`) VALUES
(1, 1, 6, 2, 20, '2025-01-01', 1),
(2, 2, 6, 2, 20, '2025-01-01', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `postulaciones`
--

CREATE TABLE `postulaciones` (
  `id_postulacion` int(11) NOT NULL COMMENT 'Identificador de postulación',
  `id_vacante` int(11) NOT NULL COMMENT 'Vacante a la que postula',
  `id_candidato` int(11) NOT NULL COMMENT 'Candidato que postula',
  `estado` enum('POSTULADO','SCREENING','ENTREVISTA','PRUEBA','OFERTA','RECHAZADO','CONTRATADO') NOT NULL DEFAULT 'POSTULADO' COMMENT 'Etapa del proceso',
  `comentarios` varchar(255) DEFAULT NULL COMMENT 'Notas del reclutador',
  `aplicada_en` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha/hora de postulación'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Relación Vacante-Candidato';

--
-- Volcado de datos para la tabla `postulaciones`
--

INSERT INTO `postulaciones` (`id_postulacion`, `id_vacante`, `id_candidato`, `estado`, `comentarios`, `aplicada_en`) VALUES
(1, 1, 1, 'ENTREVISTA', 'Buen perfil', '2025-10-22 15:43:48'),
(2, 2, 2, 'POSTULADO', 'En espera de entrevista', '2025-10-22 15:43:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puestos`
--

CREATE TABLE `puestos` (
  `id_puesto` int(11) NOT NULL COMMENT 'Identificador del puesto',
  `id_area` int(11) NOT NULL COMMENT 'Área a la que pertenece',
  `nombre_puesto` varchar(100) NOT NULL COMMENT 'Nombre del puesto',
  `nivel` enum('OPERATIVO','SUPERVISOR','GERENCIAL','DIRECTIVO') NOT NULL DEFAULT 'OPERATIVO' COMMENT 'Nivel jerárquico',
  `salario_base` decimal(12,2) DEFAULT NULL COMMENT 'Salario base referencial',
  `descripcion` varchar(200) DEFAULT NULL COMMENT 'Descripción breve'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Puestos del organigrama';

--
-- Volcado de datos para la tabla `puestos`
--

INSERT INTO `puestos` (`id_puesto`, `id_area`, `nombre_puesto`, `nivel`, `salario_base`, `descripcion`) VALUES
(1, 1, 'Analista de RRHH', 'OPERATIVO', 12000.00, 'Gestión de personal y asistencia'),
(2, 2, 'Reclutador Jr', 'OPERATIVO', 10000.00, 'Apoyo en procesos de selección');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibos_nomina`
--

CREATE TABLE `recibos_nomina` (
  `id_recibo` int(11) NOT NULL COMMENT 'Identificador del recibo',
  `id_nomina` int(11) NOT NULL COMMENT 'FK a nómina_empleado',
  `folio` varchar(40) NOT NULL COMMENT 'Folio único del recibo',
  `ruta_pdf` varchar(255) DEFAULT NULL COMMENT 'Ruta/URL del PDF',
  `emitido_en` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha/hora de emisión'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Recibos emitidos';

--
-- Volcado de datos para la tabla `recibos_nomina`
--

INSERT INTO `recibos_nomina` (`id_recibo`, `id_nomina`, `folio`, `ruta_pdf`, `emitido_en`) VALUES
(1, 1, 'REC20251001', '/recibos/rec_juan.pdf', '2025-10-22 15:43:47'),
(2, 2, 'REC20251002', '/recibos/rec_maria.pdf', '2025-10-22 15:43:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reglas_asistencia`
--

CREATE TABLE `reglas_asistencia` (
  `id_regla` int(11) NOT NULL COMMENT 'Identificador de regla',
  `id_empresa` int(11) NOT NULL COMMENT 'Empresa aplicable',
  `tolerancia_minutos` int(11) NOT NULL DEFAULT 10 COMMENT 'Tolerancia de retardo',
  `paga_horas_extra` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Pago de horas extra',
  `redondeo_minutos` int(11) NOT NULL DEFAULT 0 COMMENT 'Redondeo de minutos (0=sin)',
  `requiere_justificante` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Justificante para ausencias'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Reglas de asistencia';

--
-- Volcado de datos para la tabla `reglas_asistencia`
--

INSERT INTO `reglas_asistencia` (`id_regla`, `id_empresa`, `tolerancia_minutos`, `paga_horas_extra`, `redondeo_minutos`, `requiere_justificante`) VALUES
(1, 1, 10, 1, 5, 1),
(2, 2, 15, 0, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL COMMENT 'Identificador único del rol',
  `nombre_rol` varchar(50) NOT NULL COMMENT 'Nombre del rol (Administrador, Usuario)',
  `descripcion` varchar(150) DEFAULT NULL COMMENT 'Descripción breve del rol'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de roles del sistema';

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso total al sistema'),
(2, 'Usuario', 'Acceso limitado a módulos asignados');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permiso`
--

CREATE TABLE `rol_permiso` (
  `id_rol` int(11) NOT NULL COMMENT 'FK al rol',
  `id_permiso` int(11) NOT NULL COMMENT 'FK al permiso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Relación N:M entre roles y permisos';

--
-- Volcado de datos para la tabla `rol_permiso`
--

INSERT INTO `rol_permiso` (`id_rol`, `id_permiso`) VALUES
(1, 1),
(1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saldos_vacaciones`
--

CREATE TABLE `saldos_vacaciones` (
  `id_saldo` int(11) NOT NULL COMMENT 'Identificador del saldo',
  `id_empleado` int(11) NOT NULL COMMENT 'Empleado',
  `anio` smallint(6) NOT NULL COMMENT 'Año calendario',
  `dias_asignados` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Días asignados',
  `dias_tomados` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Días tomados',
  `dias_disponibles` decimal(5,2) GENERATED ALWAYS AS (`dias_asignados` - `dias_tomados`) VIRTUAL COMMENT 'Cálculo de disponibles'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Saldos anuales de vacaciones';

--
-- Volcado de datos para la tabla `saldos_vacaciones`
--

INSERT INTO `saldos_vacaciones` (`id_saldo`, `id_empleado`, `anio`, `dias_asignados`, `dias_tomados`) VALUES
(1, 1, 2025, 8.00, 2.00),
(2, 2, 2025, 6.00, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_permiso`
--

CREATE TABLE `solicitudes_permiso` (
  `id_solicitud` int(11) NOT NULL COMMENT 'Identificador de solicitud',
  `id_empleado` int(11) NOT NULL COMMENT 'Empleado solicitante',
  `tipo` enum('VACACIONES','PERMISO','INCAPACIDAD','OTRO') NOT NULL COMMENT 'Tipo de ausencia',
  `fecha_inicio` date NOT NULL COMMENT 'Fecha de inicio',
  `fecha_fin` date NOT NULL COMMENT 'Fecha de fin',
  `dias` decimal(5,2) NOT NULL COMMENT 'Días solicitados',
  `motivo` varchar(200) DEFAULT NULL COMMENT 'Motivo/justificación',
  `estado` enum('PENDIENTE','APROBADO','RECHAZADO','CANCELADO') NOT NULL DEFAULT 'PENDIENTE' COMMENT 'Estado del flujo',
  `documento` varchar(255) DEFAULT NULL COMMENT 'Ruta al comprobante',
  `creado_por` int(11) NOT NULL COMMENT 'Usuario creador',
  `creado_en` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha/hora de creación',
  `actualizado_en` datetime DEFAULT NULL COMMENT 'Última actualización'
) ;

--
-- Volcado de datos para la tabla `solicitudes_permiso`
--

INSERT INTO `solicitudes_permiso` (`id_solicitud`, `id_empleado`, `tipo`, `fecha_inicio`, `fecha_fin`, `dias`, `motivo`, `estado`, `documento`, `creado_por`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 'VACACIONES', '2025-10-20', '2025-10-22', 2.00, 'Descanso familiar', 'PENDIENTE', NULL, 1, '2025-10-22 15:43:47', NULL),
(2, 2, 'PERMISO', '2025-10-15', '2025-10-15', 1.00, 'Cita médica', 'APROBADO', NULL, 1, '2025-10-22 15:43:47', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id_turno` int(11) NOT NULL COMMENT 'Identificador del turno',
  `nombre_turno` varchar(60) NOT NULL COMMENT 'Nombre del turno',
  `hora_entrada` time NOT NULL COMMENT 'Hora de entrada planificada',
  `hora_salida` time NOT NULL COMMENT 'Hora de salida planificada',
  `tolerancia_minutos` int(11) NOT NULL DEFAULT 10 COMMENT 'Minutos de tolerancia',
  `dias_laborales` set('L','M','X','J','V','S','D') NOT NULL DEFAULT 'L,M,X,J,V' COMMENT 'Días laborales'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Turnos laborales';

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`id_turno`, `nombre_turno`, `hora_entrada`, `hora_salida`, `tolerancia_minutos`, `dias_laborales`) VALUES
(1, 'Matutino', '08:00:00', '16:00:00', 10, 'L,M,X,J,V'),
(2, 'Vespertino', '14:00:00', '22:00:00', 10, 'L,M,X,J,V');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicaciones`
--

CREATE TABLE `ubicaciones` (
  `id_ubicacion` int(11) NOT NULL COMMENT 'Identificador de la ubicación',
  `id_empresa` int(11) NOT NULL COMMENT 'Empresa propietaria',
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre de la sede',
  `direccion` text DEFAULT NULL COMMENT 'Dirección física',
  `ciudad` varchar(80) DEFAULT NULL COMMENT 'Ciudad',
  `estado_region` varchar(80) DEFAULT NULL COMMENT 'Estado/Región',
  `pais` varchar(80) DEFAULT NULL COMMENT 'País',
  `activa` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Ubicación activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Sedes/ubicaciones';

--
-- Volcado de datos para la tabla `ubicaciones`
--

INSERT INTO `ubicaciones` (`id_ubicacion`, `id_empresa`, `nombre`, `direccion`, `ciudad`, `estado_region`, `pais`, `activa`) VALUES
(1, 1, 'Sede Central', 'Av. Reforma 100, CDMX', 'Ciudad de México', 'CDMX', 'México', 1),
(2, 2, 'Sucursal Norte', 'Av. Hidalgo 200', 'Monterrey', 'Nuevo León', 'México', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL COMMENT 'Identificador del usuario',
  `username` varchar(50) NOT NULL COMMENT 'Usuario para login',
  `correo` varchar(120) NOT NULL COMMENT 'Correo de contacto y recuperación',
  `contrasena` varchar(255) NOT NULL COMMENT 'Hash de contraseña (bcrypt/argon2)',
  `id_rol` int(11) NOT NULL COMMENT 'Rol asignado (FK roles)',
  `estado` enum('ACTIVO','INACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO' COMMENT 'Estado del usuario',
  `requiere_2FA` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si requiere TOTP/SMS para 2FA',
  `intentos_fallidos` int(11) NOT NULL DEFAULT 0 COMMENT 'Intentos fallidos acumulados',
  `ultimo_acceso` datetime DEFAULT NULL COMMENT 'Último inicio de sesión',
  `creado_en` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación de la cuenta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Usuarios autenticables';

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `username`, `correo`, `contrasena`, `id_rol`, `estado`, `requiere_2FA`, `intentos_fallidos`, `ultimo_acceso`, `creado_en`) VALUES
(1, 'admin', 'admin@empresa.com', '$2b$10$3jf5vPt8AAg30voYEdJfluOhbazHX.BF0vQu/jaOi7joYgjsLWaJG', 1, 'ACTIVO', 0, 0, '2025-10-22 16:35:05', '2025-10-22 15:43:47'),
(2, 'juanperez', 'juan.perez@empresa.com', '$2b$10$dbSWRtpX5vYFemFgxo5DU.w2BwXsssdLhiAxJTIh2H2NQhShB.sP2', 2, 'ACTIVO', 0, 0, '2025-10-22 15:43:47', '2025-10-22 15:43:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacantes`
--

CREATE TABLE `vacantes` (
  `id_vacante` int(11) NOT NULL COMMENT 'Identificador de la vacante',
  `id_area` int(11) NOT NULL COMMENT 'Área solicitante',
  `id_puesto` int(11) NOT NULL COMMENT 'Puesto requerido',
  `id_ubicacion` int(11) DEFAULT NULL COMMENT 'Ubicación del puesto',
  `solicitada_por` int(11) NOT NULL COMMENT 'Usuario solicitante (equivale a requisición)',
  `estatus` enum('EN_APROBACION','APROBADA','ABIERTA','EN_PROCESO','CERRADA') NOT NULL DEFAULT 'EN_APROBACION' COMMENT 'Estatus del ciclo de apertura',
  `requisitos` text DEFAULT NULL COMMENT 'Requisitos del puesto',
  `fecha_publicacion` date DEFAULT NULL COMMENT 'Fecha de publicación',
  `creada_en` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha/hora de creación'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Vacantes (incluye la fase de requisición y aprobación)';

--
-- Volcado de datos para la tabla `vacantes`
--

INSERT INTO `vacantes` (`id_vacante`, `id_area`, `id_puesto`, `id_ubicacion`, `solicitada_por`, `estatus`, `requisitos`, `fecha_publicacion`, `creada_en`) VALUES
(1, 1, 1, 1, 1, 'ABIERTA', 'Lic. en Administración o afín', '2025-10-01', '2025-10-22 15:43:48'),
(2, 2, 2, 2, 1, 'EN_PROCESO', 'Experiencia en reclutamiento', '2025-10-05', '2025-10-22 15:43:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacante_aprobaciones`
--

CREATE TABLE `vacante_aprobaciones` (
  `id_aprobacion` int(11) NOT NULL COMMENT 'Identificador de aprobación de vacante',
  `id_vacante` int(11) NOT NULL COMMENT 'Vacante asociada',
  `aprobador` int(11) NOT NULL COMMENT 'Usuario aprobador',
  `nivel` smallint(6) NOT NULL DEFAULT 1 COMMENT 'Nivel de aprobación',
  `decision` enum('PENDIENTE','APROBADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE' COMMENT 'Decisión',
  `comentario` varchar(200) DEFAULT NULL COMMENT 'Comentario del aprobador',
  `decidido_en` datetime DEFAULT NULL COMMENT 'Fecha/hora de decisión'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Aprobaciones por niveles para vacantes';

--
-- Volcado de datos para la tabla `vacante_aprobaciones`
--

INSERT INTO `vacante_aprobaciones` (`id_aprobacion`, `id_vacante`, `aprobador`, `nivel`, `decision`, `comentario`, `decidido_en`) VALUES
(1, 1, 1, 1, 'APROBADO', 'Aprobación inicial', NULL),
(2, 2, 1, 1, 'PENDIENTE', 'En evaluación', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `aprobaciones_permiso`
--
ALTER TABLE `aprobaciones_permiso`
  ADD PRIMARY KEY (`id_aprobacion`),
  ADD UNIQUE KEY `uq_aprobacion` (`id_solicitud`,`nivel`),
  ADD KEY `fk_apr_usr` (`aprobador`);

--
-- Indices de la tabla `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id_area`),
  ADD KEY `fk_area_emp` (`id_empresa`),
  ADD KEY `fk_area_padre` (`id_area_padre`);

--
-- Indices de la tabla `asistencia_registros`
--
ALTER TABLE `asistencia_registros`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD UNIQUE KEY `uq_asistencia` (`id_empleado`,`fecha`),
  ADD KEY `idx_asistencia_fecha` (`fecha`);

--
-- Indices de la tabla `calendarios_laborales`
--
ALTER TABLE `calendarios_laborales`
  ADD PRIMARY KEY (`id_calendario`),
  ADD KEY `fk_cal_emp` (`id_empresa`);

--
-- Indices de la tabla `calendario_feriados`
--
ALTER TABLE `calendario_feriados`
  ADD PRIMARY KEY (`id_feriado`),
  ADD UNIQUE KEY `uq_feriado` (`id_calendario`,`fecha`);

--
-- Indices de la tabla `candidatos`
--
ALTER TABLE `candidatos`
  ADD PRIMARY KEY (`id_candidato`);

--
-- Indices de la tabla `conceptos_nomina`
--
ALTER TABLE `conceptos_nomina`
  ADD PRIMARY KEY (`id_concepto`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id_empleado`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `curp` (`curp`),
  ADD UNIQUE KEY `rfc` (`rfc`),
  ADD UNIQUE KEY `nss` (`nss`),
  ADD KEY `fk_emp_puesto` (`id_puesto`),
  ADD KEY `fk_emp_turno` (`id_turno`),
  ADD KEY `fk_emp_ubi` (`id_ubicacion`),
  ADD KEY `fk_emp_jefe` (`jefe_inmediato`),
  ADD KEY `idx_empleado_estado` (`estado`);

--
-- Indices de la tabla `empleados_banco`
--
ALTER TABLE `empleados_banco`
  ADD PRIMARY KEY (`id_banco`),
  ADD UNIQUE KEY `uq_emp_clabe` (`id_empleado`,`clabe`);

--
-- Indices de la tabla `empleados_contactos`
--
ALTER TABLE `empleados_contactos`
  ADD PRIMARY KEY (`id_contacto`),
  ADD KEY `fk_cont_emp` (`id_empleado`);

--
-- Indices de la tabla `empleados_documentos`
--
ALTER TABLE `empleados_documentos`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `fk_doc_emp` (`id_empleado`),
  ADD KEY `fk_doc_user` (`subido_por`);

--
-- Indices de la tabla `empleados_historial`
--
ALTER TABLE `empleados_historial`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `fk_hist_emp` (`id_empleado`),
  ADD KEY `fk_hist_usr` (`realizado_por`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id_empresa`),
  ADD UNIQUE KEY `rfc` (`rfc`);

--
-- Indices de la tabla `entrevistas`
--
ALTER TABLE `entrevistas`
  ADD PRIMARY KEY (`id_entrevista`),
  ADD KEY `fk_ent_pos` (`id_postulacion`),
  ADD KEY `fk_ent_usr` (`entrevistador`);

--
-- Indices de la tabla `nomina_detalle`
--
ALTER TABLE `nomina_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_det_nom` (`id_nomina`),
  ADD KEY `fk_det_con` (`id_concepto`);

--
-- Indices de la tabla `nomina_empleado`
--
ALTER TABLE `nomina_empleado`
  ADD PRIMARY KEY (`id_nomina`),
  ADD UNIQUE KEY `uq_nomina_emp_periodo` (`id_empleado`,`id_periodo`),
  ADD KEY `fk_nom_per` (`id_periodo`);

--
-- Indices de la tabla `periodos_nomina`
--
ALTER TABLE `periodos_nomina`
  ADD PRIMARY KEY (`id_periodo`),
  ADD KEY `fk_periodo_emp` (`id_empresa`);

--
-- Indices de la tabla `permisos_sistema`
--
ALTER TABLE `permisos_sistema`
  ADD PRIMARY KEY (`id_permiso`),
  ADD UNIQUE KEY `clave` (`clave`);

--
-- Indices de la tabla `politicas_vacaciones`
--
ALTER TABLE `politicas_vacaciones`
  ADD PRIMARY KEY (`id_politica`),
  ADD KEY `fk_pv_emp` (`id_empresa`);

--
-- Indices de la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  ADD PRIMARY KEY (`id_postulacion`),
  ADD UNIQUE KEY `uq_post` (`id_vacante`,`id_candidato`),
  ADD KEY `fk_pos_can` (`id_candidato`),
  ADD KEY `idx_postulaciones_estado` (`estado`);

--
-- Indices de la tabla `puestos`
--
ALTER TABLE `puestos`
  ADD PRIMARY KEY (`id_puesto`),
  ADD KEY `fk_puesto_area` (`id_area`);

--
-- Indices de la tabla `recibos_nomina`
--
ALTER TABLE `recibos_nomina`
  ADD PRIMARY KEY (`id_recibo`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD KEY `fk_rec_nom` (`id_nomina`);

--
-- Indices de la tabla `reglas_asistencia`
--
ALTER TABLE `reglas_asistencia`
  ADD PRIMARY KEY (`id_regla`),
  ADD KEY `fk_reg_emp` (`id_empresa`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD PRIMARY KEY (`id_rol`,`id_permiso`),
  ADD KEY `fk_rp_perm` (`id_permiso`);

--
-- Indices de la tabla `saldos_vacaciones`
--
ALTER TABLE `saldos_vacaciones`
  ADD PRIMARY KEY (`id_saldo`),
  ADD UNIQUE KEY `uq_saldo` (`id_empleado`,`anio`);

--
-- Indices de la tabla `solicitudes_permiso`
--
ALTER TABLE `solicitudes_permiso`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `fk_sp_emp` (`id_empleado`),
  ADD KEY `fk_sp_user` (`creado_por`),
  ADD KEY `idx_solicitudes_estado` (`estado`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id_turno`);

--
-- Indices de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD PRIMARY KEY (`id_ubicacion`),
  ADD KEY `fk_ubi_emp` (`id_empresa`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `fk_usr_rol` (`id_rol`),
  ADD KEY `idx_usuarios_correo` (`correo`);

--
-- Indices de la tabla `vacantes`
--
ALTER TABLE `vacantes`
  ADD PRIMARY KEY (`id_vacante`),
  ADD KEY `fk_vac_area` (`id_area`),
  ADD KEY `fk_vac_puesto` (`id_puesto`),
  ADD KEY `fk_vac_ubi` (`id_ubicacion`),
  ADD KEY `fk_vac_user` (`solicitada_por`);

--
-- Indices de la tabla `vacante_aprobaciones`
--
ALTER TABLE `vacante_aprobaciones`
  ADD PRIMARY KEY (`id_aprobacion`),
  ADD UNIQUE KEY `uq_vac_apr` (`id_vacante`,`nivel`),
  ADD KEY `fk_vac_apr_usr` (`aprobador`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `aprobaciones_permiso`
--
ALTER TABLE `aprobaciones_permiso`
  MODIFY `id_aprobacion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de aprobación', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `areas`
--
ALTER TABLE `areas`
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del área/departamento', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `asistencia_registros`
--
ALTER TABLE `asistencia_registros`
  MODIFY `id_asistencia` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del registro', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `calendarios_laborales`
--
ALTER TABLE `calendarios_laborales`
  MODIFY `id_calendario` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del calendario', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `calendario_feriados`
--
ALTER TABLE `calendario_feriados`
  MODIFY `id_feriado` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del feriado', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `candidatos`
--
ALTER TABLE `candidatos`
  MODIFY `id_candidato` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del candidato', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `conceptos_nomina`
--
ALTER TABLE `conceptos_nomina`
  MODIFY `id_concepto` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del concepto', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del empleado', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `empleados_banco`
--
ALTER TABLE `empleados_banco`
  MODIFY `id_banco` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador bancario', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `empleados_contactos`
--
ALTER TABLE `empleados_contactos`
  MODIFY `id_contacto` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de contacto', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `empleados_documentos`
--
ALTER TABLE `empleados_documentos`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del documento', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `empleados_historial`
--
ALTER TABLE `empleados_historial`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del evento', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de la empresa', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `entrevistas`
--
ALTER TABLE `entrevistas`
  MODIFY `id_entrevista` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de entrevista', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `nomina_detalle`
--
ALTER TABLE `nomina_detalle`
  MODIFY `id_detalle` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del renglón', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `nomina_empleado`
--
ALTER TABLE `nomina_empleado`
  MODIFY `id_nomina` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de nómina por empleado/periodo', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `periodos_nomina`
--
ALTER TABLE `periodos_nomina`
  MODIFY `id_periodo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del periodo';

--
-- AUTO_INCREMENT de la tabla `permisos_sistema`
--
ALTER TABLE `permisos_sistema`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del permiso', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `politicas_vacaciones`
--
ALTER TABLE `politicas_vacaciones`
  MODIFY `id_politica` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de política', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  MODIFY `id_postulacion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de postulación', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `puestos`
--
ALTER TABLE `puestos`
  MODIFY `id_puesto` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del puesto', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `recibos_nomina`
--
ALTER TABLE `recibos_nomina`
  MODIFY `id_recibo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del recibo', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `reglas_asistencia`
--
ALTER TABLE `reglas_asistencia`
  MODIFY `id_regla` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de regla', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del rol', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `saldos_vacaciones`
--
ALTER TABLE `saldos_vacaciones`
  MODIFY `id_saldo` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del saldo', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `solicitudes_permiso`
--
ALTER TABLE `solicitudes_permiso`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de solicitud';

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id_turno` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del turno', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  MODIFY `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de la ubicación', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del usuario', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vacantes`
--
ALTER TABLE `vacantes`
  MODIFY `id_vacante` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de la vacante', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vacante_aprobaciones`
--
ALTER TABLE `vacante_aprobaciones`
  MODIFY `id_aprobacion` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador de aprobación de vacante', AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `aprobaciones_permiso`
--
ALTER TABLE `aprobaciones_permiso`
  ADD CONSTRAINT `fk_apr_sol` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes_permiso` (`id_solicitud`),
  ADD CONSTRAINT `fk_apr_usr` FOREIGN KEY (`aprobador`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `areas`
--
ALTER TABLE `areas`
  ADD CONSTRAINT `fk_area_emp` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`),
  ADD CONSTRAINT `fk_area_padre` FOREIGN KEY (`id_area_padre`) REFERENCES `areas` (`id_area`);

--
-- Filtros para la tabla `asistencia_registros`
--
ALTER TABLE `asistencia_registros`
  ADD CONSTRAINT `fk_asist_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`);

--
-- Filtros para la tabla `calendarios_laborales`
--
ALTER TABLE `calendarios_laborales`
  ADD CONSTRAINT `fk_cal_emp` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`);

--
-- Filtros para la tabla `calendario_feriados`
--
ALTER TABLE `calendario_feriados`
  ADD CONSTRAINT `fk_fer_cal` FOREIGN KEY (`id_calendario`) REFERENCES `calendarios_laborales` (`id_calendario`);

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `fk_emp_jefe` FOREIGN KEY (`jefe_inmediato`) REFERENCES `empleados` (`id_empleado`),
  ADD CONSTRAINT `fk_emp_puesto` FOREIGN KEY (`id_puesto`) REFERENCES `puestos` (`id_puesto`),
  ADD CONSTRAINT `fk_emp_turno` FOREIGN KEY (`id_turno`) REFERENCES `turnos` (`id_turno`),
  ADD CONSTRAINT `fk_emp_ubi` FOREIGN KEY (`id_ubicacion`) REFERENCES `ubicaciones` (`id_ubicacion`),
  ADD CONSTRAINT `fk_emp_usr` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `empleados_banco`
--
ALTER TABLE `empleados_banco`
  ADD CONSTRAINT `fk_ban_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`);

--
-- Filtros para la tabla `empleados_contactos`
--
ALTER TABLE `empleados_contactos`
  ADD CONSTRAINT `fk_cont_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`);

--
-- Filtros para la tabla `empleados_documentos`
--
ALTER TABLE `empleados_documentos`
  ADD CONSTRAINT `fk_doc_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`),
  ADD CONSTRAINT `fk_doc_user` FOREIGN KEY (`subido_por`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `empleados_historial`
--
ALTER TABLE `empleados_historial`
  ADD CONSTRAINT `fk_hist_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`),
  ADD CONSTRAINT `fk_hist_usr` FOREIGN KEY (`realizado_por`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `entrevistas`
--
ALTER TABLE `entrevistas`
  ADD CONSTRAINT `fk_ent_pos` FOREIGN KEY (`id_postulacion`) REFERENCES `postulaciones` (`id_postulacion`),
  ADD CONSTRAINT `fk_ent_usr` FOREIGN KEY (`entrevistador`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `nomina_detalle`
--
ALTER TABLE `nomina_detalle`
  ADD CONSTRAINT `fk_det_con` FOREIGN KEY (`id_concepto`) REFERENCES `conceptos_nomina` (`id_concepto`),
  ADD CONSTRAINT `fk_det_nom` FOREIGN KEY (`id_nomina`) REFERENCES `nomina_empleado` (`id_nomina`);

--
-- Filtros para la tabla `nomina_empleado`
--
ALTER TABLE `nomina_empleado`
  ADD CONSTRAINT `fk_nom_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`),
  ADD CONSTRAINT `fk_nom_per` FOREIGN KEY (`id_periodo`) REFERENCES `periodos_nomina` (`id_periodo`);

--
-- Filtros para la tabla `periodos_nomina`
--
ALTER TABLE `periodos_nomina`
  ADD CONSTRAINT `fk_periodo_emp` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`);

--
-- Filtros para la tabla `politicas_vacaciones`
--
ALTER TABLE `politicas_vacaciones`
  ADD CONSTRAINT `fk_pv_emp` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`);

--
-- Filtros para la tabla `postulaciones`
--
ALTER TABLE `postulaciones`
  ADD CONSTRAINT `fk_pos_can` FOREIGN KEY (`id_candidato`) REFERENCES `candidatos` (`id_candidato`),
  ADD CONSTRAINT `fk_pos_vac` FOREIGN KEY (`id_vacante`) REFERENCES `vacantes` (`id_vacante`);

--
-- Filtros para la tabla `puestos`
--
ALTER TABLE `puestos`
  ADD CONSTRAINT `fk_puesto_area` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`);

--
-- Filtros para la tabla `recibos_nomina`
--
ALTER TABLE `recibos_nomina`
  ADD CONSTRAINT `fk_rec_nom` FOREIGN KEY (`id_nomina`) REFERENCES `nomina_empleado` (`id_nomina`);

--
-- Filtros para la tabla `reglas_asistencia`
--
ALTER TABLE `reglas_asistencia`
  ADD CONSTRAINT `fk_reg_emp` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`);

--
-- Filtros para la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD CONSTRAINT `fk_rp_perm` FOREIGN KEY (`id_permiso`) REFERENCES `permisos_sistema` (`id_permiso`),
  ADD CONSTRAINT `fk_rp_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

--
-- Filtros para la tabla `saldos_vacaciones`
--
ALTER TABLE `saldos_vacaciones`
  ADD CONSTRAINT `fk_sv_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`);

--
-- Filtros para la tabla `solicitudes_permiso`
--
ALTER TABLE `solicitudes_permiso`
  ADD CONSTRAINT `fk_sp_emp` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`),
  ADD CONSTRAINT `fk_sp_user` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD CONSTRAINT `fk_ubi_emp` FOREIGN KEY (`id_empresa`) REFERENCES `empresas` (`id_empresa`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usr_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

--
-- Filtros para la tabla `vacantes`
--
ALTER TABLE `vacantes`
  ADD CONSTRAINT `fk_vac_area` FOREIGN KEY (`id_area`) REFERENCES `areas` (`id_area`),
  ADD CONSTRAINT `fk_vac_puesto` FOREIGN KEY (`id_puesto`) REFERENCES `puestos` (`id_puesto`),
  ADD CONSTRAINT `fk_vac_ubi` FOREIGN KEY (`id_ubicacion`) REFERENCES `ubicaciones` (`id_ubicacion`),
  ADD CONSTRAINT `fk_vac_user` FOREIGN KEY (`solicitada_por`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `vacante_aprobaciones`
--
ALTER TABLE `vacante_aprobaciones`
  ADD CONSTRAINT `fk_vac_apr_usr` FOREIGN KEY (`aprobador`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_vac_apr_vac` FOREIGN KEY (`id_vacante`) REFERENCES `vacantes` (`id_vacante`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

/********************************************************************************************************************************/

/*   CAMBIOS  */
/*Pegar en el apartado de SQL dentro de PHP*/
ALTER TABLE puestos
  ADD COLUMN activa TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Puesto activo';

ALTER TABLE turnos
  ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Turno activo';

-- Tabla de movimientos de empleados
CREATE TABLE IF NOT EXISTS movimientos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT NOT NULL,
    tipo_movimiento ENUM('BAJA', 'CAMBIO_AREA', 'CAMBIO_PUESTO', 'CAMBIO_JEFE', 'CAMBIO_POSICION') NOT NULL,
    fecha_movimiento DATE NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    observaciones TEXT NULL,
    valor_anterior VARCHAR(255) NULL,
    valor_nuevo VARCHAR(255) NULL,
    autorizado_por INT NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado) ON DELETE CASCADE,
    FOREIGN KEY (autorizado_por) REFERENCES usuarios(id_usuario),
    
    INDEX idx_empleado (id_empleado),
    INDEX idx_tipo (tipo_movimiento),
    INDEX idx_fecha (fecha_movimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar campo fecha_baja a empleados (solo si no existe)
ALTER TABLE empleados 
ADD COLUMN fecha_baja DATE NULL AFTER estado;
