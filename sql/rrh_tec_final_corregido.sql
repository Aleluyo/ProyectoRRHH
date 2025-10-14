-- ================================================
-- BASE DE DATOS: RRH_TEC (Compacta) - MariaDB / XAMPP
-- Esquema compacto corregido y ordenado para importación directa.
-- - DATETIME (no TIMESTAMP)
-- - Comentarios por campo/tabla
-- - InnoDB + utf8mb4
-- ================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS rrhh_tec;
CREATE DATABASE rrhh_tec CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE rrhh_tec;

-- =======================================================
-- 1) AUTENTICACIÓN Y SEGURIDAD
-- =======================================================

CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del rol',
    nombre_rol VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nombre del rol (Administrador, Usuario)',
    descripcion VARCHAR(150) NULL COMMENT 'Descripción breve del rol'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catálogo de roles del sistema';

CREATE TABLE permisos_sistema (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del permiso',
    clave VARCHAR(80) NOT NULL UNIQUE COMMENT 'Clave técnica del permiso (ej. EMPLEADOS_VER)',
    descripcion VARCHAR(200) NOT NULL COMMENT 'Descripción funcional del permiso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Permisos granulares del sistema';

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del usuario',
    username VARCHAR(50) NOT NULL UNIQUE COMMENT 'Usuario para login',
    correo VARCHAR(120) NOT NULL UNIQUE COMMENT 'Correo de contacto y recuperación',
    contrasena VARCHAR(255) NOT NULL COMMENT 'Hash de contraseña (bcrypt/argon2)',
    id_rol INT NOT NULL COMMENT 'Rol asignado (FK roles)',
    estado ENUM('ACTIVO','INACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO' COMMENT 'Estado del usuario',
    requiere_2FA BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Si requiere TOTP/SMS para 2FA',
    intentos_fallidos INT NOT NULL DEFAULT 0 COMMENT 'Intentos fallidos acumulados',
    ultimo_acceso DATETIME NULL COMMENT 'Último inicio de sesión',
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación de la cuenta',
    CONSTRAINT fk_usr_rol FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Usuarios autenticables';

CREATE TABLE rol_permiso (
    id_rol INT NOT NULL COMMENT 'FK al rol',
    id_permiso INT NOT NULL COMMENT 'FK al permiso',
    PRIMARY KEY (id_rol, id_permiso),
    CONSTRAINT fk_rp_rol FOREIGN KEY (id_rol) REFERENCES roles(id_rol),
    CONSTRAINT fk_rp_perm FOREIGN KEY (id_permiso) REFERENCES permisos_sistema(id_permiso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Relación N:M entre roles y permisos';

CREATE INDEX idx_usuarios_correo ON usuarios(correo);

-- =======================================================
-- 2) ESTRUCTURA ORGANIZACIONAL
-- =======================================================

CREATE TABLE empresas (
    id_empresa INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador de la empresa',
    nombre VARCHAR(120) NOT NULL COMMENT 'Nombre legal/comercial',
    rfc VARCHAR(20) NULL UNIQUE COMMENT 'Registro fiscal',
    correo_contacto VARCHAR(120) NULL COMMENT 'Correo de contacto',
    telefono VARCHAR(20) NULL COMMENT 'Teléfono principal',
    direccion TEXT NULL COMMENT 'Domicilio completo',
    activa BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Empresa activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Empresas registradas';

CREATE TABLE ubicaciones (
    id_ubicacion INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador de la ubicación',
    id_empresa INT NOT NULL COMMENT 'Empresa propietaria',
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre de la sede',
    direccion TEXT NULL COMMENT 'Dirección física',
    ciudad VARCHAR(80) NULL COMMENT 'Ciudad',
    estado_region VARCHAR(80) NULL COMMENT 'Estado/Región',
    pais VARCHAR(80) NULL COMMENT 'País',
    activa BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Ubicación activa',
    CONSTRAINT fk_ubi_emp FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Sedes/ubicaciones';

CREATE TABLE areas (
    id_area INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del área/departamento',
    id_empresa INT NOT NULL COMMENT 'Empresa dueña del área',
    id_area_padre INT NULL COMMENT 'Área padre para jerarquía (autorreferencia)',
    nombre_area VARCHAR(100) NOT NULL COMMENT 'Nombre del área',
    descripcion VARCHAR(200) NULL COMMENT 'Descripción del área',
    activa BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Área activa',
    CONSTRAINT fk_area_emp FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa),
    CONSTRAINT fk_area_padre FOREIGN KEY (id_area_padre) REFERENCES areas(id_area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Áreas y jerarquías';

CREATE TABLE turnos (
    id_turno INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del turno',
    nombre_turno VARCHAR(60) NOT NULL COMMENT 'Nombre del turno',
    hora_entrada TIME NOT NULL COMMENT 'Hora de entrada planificada',
    hora_salida TIME NOT NULL COMMENT 'Hora de salida planificada',
    tolerancia_minutos INT NOT NULL DEFAULT 10 COMMENT 'Minutos de tolerancia',
    dias_laborales SET('L','M','X','J','V','S','D') NOT NULL DEFAULT 'L,M,X,J,V' COMMENT 'Días laborales'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Turnos laborales';

CREATE TABLE puestos (
    id_puesto INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del puesto',
    id_area INT NOT NULL COMMENT 'Área a la que pertenece',
    nombre_puesto VARCHAR(100) NOT NULL COMMENT 'Nombre del puesto',
    nivel ENUM('OPERATIVO','SUPERVISOR','GERENCIAL','DIRECTIVO') NOT NULL DEFAULT 'OPERATIVO' COMMENT 'Nivel jerárquico',
    salario_base DECIMAL(12,2) NULL COMMENT 'Salario base referencial',
    descripcion VARCHAR(200) NULL COMMENT 'Descripción breve',
    CONSTRAINT fk_puesto_area FOREIGN KEY (id_area) REFERENCES areas(id_area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Puestos del organigrama';

-- =======================================================
-- 3) GESTIÓN DE EMPLEADOS (EXPEDIENTE)
-- =======================================================

CREATE TABLE empleados (
    id_empleado INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del empleado',
    id_usuario INT NULL UNIQUE COMMENT 'Usuario del sistema (si aplica)',
    id_puesto INT NOT NULL COMMENT 'Puesto actual',
    id_turno INT NULL COMMENT 'Turno asignado',
    id_ubicacion INT NULL COMMENT 'Ubicación de trabajo',
    nombre VARCHAR(120) NOT NULL COMMENT 'Nombre completo',
    curp VARCHAR(18) NULL UNIQUE COMMENT 'CURP (si aplica)',
    rfc VARCHAR(13) NULL UNIQUE COMMENT 'RFC (si aplica)',
    nss VARCHAR(15) NULL UNIQUE COMMENT 'Número de seguro social',
    fecha_nacimiento DATE NULL COMMENT 'Fecha de nacimiento',
    genero ENUM('M','F','OTRO') NOT NULL DEFAULT 'OTRO' COMMENT 'Género',
    estado_civil VARCHAR(20) NULL COMMENT 'Estado civil',
    direccion TEXT NULL COMMENT 'Domicilio particular',
    telefono VARCHAR(20) NULL COMMENT 'Teléfono de contacto',
    correo VARCHAR(120) NULL COMMENT 'Correo personal',
    fecha_ingreso DATE NOT NULL COMMENT 'Fecha de ingreso',
    fecha_baja DATE NULL COMMENT 'Fecha de baja (si aplica)',
    estado ENUM('ACTIVO','BAJA') NOT NULL DEFAULT 'ACTIVO' COMMENT 'Estado laboral',
    jefe_inmediato INT NULL COMMENT 'ID del jefe inmediato',
    CONSTRAINT fk_emp_usr FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    CONSTRAINT fk_emp_puesto FOREIGN KEY (id_puesto) REFERENCES puestos(id_puesto),
    CONSTRAINT fk_emp_turno FOREIGN KEY (id_turno) REFERENCES turnos(id_turno),
    CONSTRAINT fk_emp_ubi FOREIGN KEY (id_ubicacion) REFERENCES ubicaciones(id_ubicacion),
    CONSTRAINT fk_emp_jefe FOREIGN KEY (jefe_inmediato) REFERENCES empleados(id_empleado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Expediente principal de empleados';

CREATE TABLE empleados_contactos (
    id_contacto INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador de contacto',
    id_empleado INT NOT NULL COMMENT 'Empleado relacionado',
    tipo ENUM('EMERGENCIA','PERSONAL','OTRO') NOT NULL DEFAULT 'EMERGENCIA' COMMENT 'Tipo de contacto',
    nombre VARCHAR(120) NOT NULL COMMENT 'Nombre del contacto',
    telefono VARCHAR(20) NOT NULL COMMENT 'Teléfono del contacto',
    correo VARCHAR(120) NULL COMMENT 'Correo del contacto',
    parentesco VARCHAR(60) NULL COMMENT 'Parentesco/relación',
    activo BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Registro activo',
    CONSTRAINT fk_cont_emp FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Contactos (emergencia/personal) por empleado';

CREATE TABLE empleados_banco (
    id_banco INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador bancario',
    id_empleado INT NOT NULL COMMENT 'Empleado dueño',
    banco VARCHAR(80) NOT NULL COMMENT 'Nombre del banco',
    clabe VARCHAR(18) NOT NULL COMMENT 'CLABE/cuenta para dispersión',
    titular VARCHAR(120) NOT NULL COMMENT 'Titular de la cuenta',
    activa BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Cuenta vigente',
    CONSTRAINT uq_emp_clabe UNIQUE (id_empleado, clabe),
    CONSTRAINT fk_ban_emp FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Cuentas bancarias para pago de nómina';

CREATE TABLE empleados_documentos (
    id_documento INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del documento',
    id_empleado INT NOT NULL COMMENT 'Empleado dueño',
    tipo VARCHAR(80) NOT NULL COMMENT 'Tipo (INE, CURP, Contrato, etc.)',
    ruta VARCHAR(255) NOT NULL COMMENT 'Ruta/URL del archivo',
    valido_desde DATE NULL COMMENT 'Inicio de validez',
    valido_hasta DATE NULL COMMENT 'Fin de vigencia',
    verificado BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Si fue verificado',
    subido_por INT NULL COMMENT 'Usuario que carga',
    subido_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha/hora de carga',
    CONSTRAINT fk_doc_emp FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    CONSTRAINT fk_doc_user FOREIGN KEY (subido_por) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Documentos del expediente';

CREATE TABLE empleados_historial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del evento',
    id_empleado INT NOT NULL COMMENT 'Empleado afectado',
    tipo_cambio ENUM('ALTA','REINGRESO','BAJA','CAMBIO_PUESTO','CAMBIO_SALARIO','CAMBIO_AREA','CAMBIO_JEFE') NOT NULL COMMENT 'Tipo de cambio',
    detalle VARCHAR(255) NULL COMMENT 'Descripción del cambio',
    valor_anterior VARCHAR(255) NULL COMMENT 'Valor previo',
    valor_nuevo VARCHAR(255) NULL COMMENT 'Valor nuevo',
    realizado_por INT NULL COMMENT 'Usuario responsable',
    realizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha/hora del cambio',
    CONSTRAINT fk_hist_emp FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    CONSTRAINT fk_hist_usr FOREIGN KEY (realizado_por) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Bitácora de cambios del expediente';

-- =======================================================
-- 4) ASISTENCIA, INCIDENCIAS Y PERMISOS
-- =======================================================

CREATE TABLE calendarios_laborales (
    id_calendario INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del calendario',
    id_empresa INT NOT NULL COMMENT 'Empresa propietaria',
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre del calendario',
    descripcion VARCHAR(200) NULL COMMENT 'Descripción del calendario',
    activo BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Calendario activo',
    CONSTRAINT fk_cal_emp FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Calendarios laborales';

CREATE TABLE calendario_feriados (
    id_feriado INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del feriado',
    id_calendario INT NOT NULL COMMENT 'Calendario asociado',
    fecha DATE NOT NULL COMMENT 'Fecha del feriado',
    descripcion VARCHAR(150) NOT NULL COMMENT 'Descripción del feriado',
    UNIQUE KEY uq_feriado (id_calendario, fecha),
    CONSTRAINT fk_fer_cal FOREIGN KEY (id_calendario) REFERENCES calendarios_laborales(id_calendario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Feriados por calendario';

CREATE TABLE reglas_asistencia (
    id_regla INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador de regla',
    id_empresa INT NOT NULL COMMENT 'Empresa aplicable',
    tolerancia_minutos INT NOT NULL DEFAULT 10 COMMENT 'Tolerancia de retardo',
    paga_horas_extra BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Pago de horas extra',
    redondeo_minutos INT NOT NULL DEFAULT 0 COMMENT 'Redondeo de minutos (0=sin)',
    requiere_justificante BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Justificante para ausencias',
    CONSTRAINT fk_reg_emp FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Reglas de asistencia';

CREATE TABLE asistencia_registros (
    id_asistencia BIGINT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del registro',
    id_empleado INT NOT NULL COMMENT 'Empleado',
    fecha DATE NOT NULL COMMENT 'Fecha del día laboral',
    hora_entrada TIME NULL COMMENT 'Hora de entrada',
    hora_salida TIME NULL COMMENT 'Hora de salida',
    tipo ENUM('NORMAL','RETARDO','FALTA','JUSTIFICADO') NOT NULL DEFAULT 'NORMAL' COMMENT 'Clasificación del día',
    origen ENUM('MANUAL','RELOJ','IMPORTACION') NOT NULL DEFAULT 'RELOJ' COMMENT 'Origen del registro',
    observaciones VARCHAR(200) NULL COMMENT 'Notas u observaciones',
    UNIQUE KEY uq_asistencia (id_empleado, fecha),
    CONSTRAINT fk_asist_emp FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Asistencia diaria por empleado';

CREATE TABLE politicas_vacaciones (
    id_politica INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador de política',
    id_empresa INT NOT NULL COMMENT 'Empresa',
    dias_inicio INT NOT NULL DEFAULT 6 COMMENT 'Días base al primer año',
    incremento_anual INT NOT NULL DEFAULT 2 COMMENT 'Incremento por antigüedad',
    dias_max INT NOT NULL DEFAULT 20 COMMENT 'Tope máximo de días',
    periodo_anual_inicio DATE NULL COMMENT 'Inicio del ciclo anual (opcional)',
    activa BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Política activa',
    CONSTRAINT fk_pv_emp FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Política de vacaciones por empresa';

CREATE TABLE saldos_vacaciones (
    id_saldo INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del saldo',
    id_empleado INT NOT NULL COMMENT 'Empleado',
    anio SMALLINT NOT NULL COMMENT 'Año calendario',
    dias_asignados DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Días asignados',
    dias_tomados DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Días tomados',
    -- CORRECCIÓN APLICADA: Se añadió el tipo de dato DECIMAL(5,2)
    dias_disponibles DECIMAL(5,2) AS (dias_asignados - dias_tomados) VIRTUAL COMMENT 'Cálculo de disponibles',
    UNIQUE KEY uq_saldo (id_empleado, anio),
    CONSTRAINT fk_sv_emp FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'Saldos anuales de vacaciones';

CREATE TABLE solicitudes_permiso (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador de solicitud',
    id_empleado INT NOT NULL COMMENT 'Empleado solicitante',
    tipo ENUM('VACACIONES','PERMISO','INCAPACIDAD','OTRO') NOT NULL COMMENT 'Tipo de ausencia',
    fecha_inicio DATE NOT NULL COMMENT 'Fecha de inicio',
    fecha_fin DATE NOT NULL COMMENT 'Fecha de fin',
    dias DECIMAL(5,2) NOT NULL COMMENT 'Días solicitados',
    motivo VARCHAR(200) NULL COMMENT 'Motivo/justificación',
    estado ENUM('PENDIENTE','APROBADO','RECHAZADO','CANCELADO') NOT NULL DEFAULT 'PENDIENTE' COMMENT 'Estado del flujo',
    documento VARCHAR(255) NULL COMMENT 'Ruta al comprobante',
    creado_por INT NOT NULL COMMENT 'Usuario creador',
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha/hora de creación',
    actualizado_en DATETIME NULL COMMENT 'Última actualización',
    CONSTRAINT chk_fechas_permiso CHECK (fecha_fin >= fecha_inicio),
    CONSTRAINT fk_sp_emp FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    CONSTRAINT fk_sp_user FOREIGN KEY (creado_por) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Solicitudes de vacaciones/permisos';

CREATE TABLE aprobaciones_permiso (
    id_aprobacion INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador de aprobación',
    id_solicitud INT NOT NULL COMMENT 'Solicitud asociada',
    aprobador INT NOT NULL COMMENT 'Usuario aprobador',
    nivel SMALLINT NOT NULL DEFAULT 1 COMMENT 'Nivel de aprobación',
    decision ENUM('PENDIENTE','APROBADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE' COMMENT 'Decisión',
    comentario VARCHAR(200) NULL COMMENT 'Comentario del aprobador',
    decidido_en DATETIME NULL COMMENT 'Fecha/hora de decisión',
    UNIQUE KEY uq_aprobacion (id_solicitud, nivel),
    CONSTRAINT fk_apr_sol FOREIGN KEY (id_solicitud) REFERENCES solicitudes_permiso(id_solicitud),
    CONSTRAINT fk_apr_usr FOREIGN KEY (aprobador) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Niveles de aprobación por solicitud';

-- =======================================================
-- 5) NÓMINA
-- =======================================================

CREATE TABLE periodos_nomina (
    id_periodo INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del periodo',
    id_empresa INT NOT NULL COMMENT 'Empresa del periodo',
    tipo ENUM('SEMANAL','QUINCENAL','MENSUAL') NOT NULL COMMENT 'Tipo de periodo',
    fecha_inicio DATE NOT NULL COMMENT 'Inicio del periodo',
    fecha_fin DATE NOT NULL COMMENT 'Fin del periodo',
    estado ENUM('ABIERTO','CERRADO') NOT NULL DEFAULT 'ABIERTO' COMMENT 'Estado del periodo',
    CONSTRAINT chk_periodo_fechas CHECK (fecha_fin >= fecha_inicio),
    CONSTRAINT fk_periodo_emp FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Periodos de nómina';

CREATE TABLE conceptos_nomina (
    id_concepto INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del concepto',
    tipo ENUM('PERCEPCION','DEDUCCION') NOT NULL COMMENT 'Tipo de concepto',
    clave VARCHAR(20) NOT NULL UNIQUE COMMENT 'Clave corta (p.ej. SUELDO, ISR)',
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre descriptivo',
    formula VARCHAR(255) NULL COMMENT 'Expresión de cálculo (si aplica)',
    gravable BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'Si integra al salario base'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catálogo de conceptos de nómina';

CREATE TABLE nomina_empleado (
    id_nomina INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador de nómina por empleado/periodo',
    id_empleado INT NOT NULL COMMENT 'Empleado',
    id_periodo INT NOT NULL COMMENT 'Periodo de nómina',
    total_percepciones DECIMAL(14,2) NOT NULL DEFAULT 0 COMMENT 'Total de percepciones',
    total_deducciones DECIMAL(14,2) NOT NULL DEFAULT 0 COMMENT 'Total de deducciones',
    total_neto DECIMAL(14,2) NOT NULL DEFAULT 0 COMMENT 'Neto a pagar',
    generado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha/hora de generación',
    UNIQUE KEY uq_nomina_emp_periodo (id_empleado, id_periodo),
    CONSTRAINT fk_nom_emp FOREIGN KEY (id_empleado) REFERENCES empleados(id_empleado),
    CONSTRAINT fk_nom_per FOREIGN KEY (id_periodo) REFERENCES periodos_nomina(id_periodo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Totales por empleado y periodo';

CREATE TABLE nomina_detalle (
    id_detalle BIGINT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del renglón',
    id_nomina INT NOT NULL COMMENT 'FK a nómina_empleado',
    id_concepto INT NOT NULL COMMENT 'Concepto aplicado',
    monto DECIMAL(14,2) NOT NULL COMMENT 'Monto del concepto',
    observacion VARCHAR(200) NULL COMMENT 'Nota/observación',
    CONSTRAINT fk_det_nom FOREIGN KEY (id_nomina) REFERENCES nomina_empleado(id_nomina),
    CONSTRAINT fk_det_con FOREIGN KEY (id_concepto) REFERENCES conceptos_nomina(id_concepto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Renglones de percepciones/deducciones';

CREATE TABLE recibos_nomina (
    id_recibo INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del recibo',
    id_nomina INT NOT NULL COMMENT 'FK a nómina_empleado',
    folio VARCHAR(40) NOT NULL UNIQUE COMMENT 'Folio único del recibo',
    ruta_pdf VARCHAR(255) NULL COMMENT 'Ruta/URL del PDF',
    emitido_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha/hora de emisión',
    CONSTRAINT fk_rec_nom FOREIGN KEY (id_nomina) REFERENCES nomina_empleado(id_nomina)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Recibos emitidos';

-- =======================================================
-- 6) RECLUTAMIENTO Y SELECCIÓN
-- =======================================================

CREATE TABLE vacantes (
    id_vacante INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador de la vacante',
    id_area INT NOT NULL COMMENT 'Área solicitante',
    id_puesto INT NOT NULL COMMENT 'Puesto requerido',
    id_ubicacion INT NULL COMMENT 'Ubicación del puesto',
    solicitada_por INT NOT NULL COMMENT 'Usuario solicitante (equivale a requisición)',
    estatus ENUM('EN_APROBACION','APROBADA','ABIERTA','EN_PROCESO','CERRADA') NOT NULL DEFAULT 'EN_APROBACION' COMMENT 'Estatus del ciclo de apertura',
    requisitos TEXT NULL COMMENT 'Requisitos del puesto',
    fecha_publicacion DATE NULL COMMENT 'Fecha de publicación',
    creada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha/hora de creación',
    CONSTRAINT fk_vac_area FOREIGN KEY (id_area) REFERENCES areas(id_area),
    CONSTRAINT fk_vac_puesto FOREIGN KEY (id_puesto) REFERENCES puestos(id_puesto),
    CONSTRAINT fk_vac_ubi FOREIGN KEY (id_ubicacion) REFERENCES ubicaciones(id_ubicacion),
    CONSTRAINT fk_vac_user FOREIGN KEY (solicitada_por) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Vacantes (incluye la fase de requisición y aprobación)';

CREATE TABLE vacante_aprobaciones (
    id_aprobacion INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador de aprobación de vacante',
    id_vacante INT NOT NULL COMMENT 'Vacante asociada',
    aprobador INT NOT NULL COMMENT 'Usuario aprobador',
    nivel SMALLINT NOT NULL DEFAULT 1 COMMENT 'Nivel de aprobación',
    decision ENUM('PENDIENTE','APROBADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE' COMMENT 'Decisión',
    comentario VARCHAR(200) NULL COMMENT 'Comentario del aprobador',
    decidido_en DATETIME NULL COMMENT 'Fecha/hora de decisión',
    UNIQUE KEY uq_vac_apr (id_vacante, nivel),
    CONSTRAINT fk_vac_apr_vac FOREIGN KEY (id_vacante) REFERENCES vacantes(id_vacante),
    CONSTRAINT fk_vac_apr_usr FOREIGN KEY (aprobador) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Aprobaciones por niveles para vacantes';

CREATE TABLE candidatos (
    id_candidato INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador del candidato',
    nombre VARCHAR(120) NOT NULL COMMENT 'Nombre completo',
    correo VARCHAR(120) NULL COMMENT 'Correo de contacto',
    telefono VARCHAR(20) NULL COMMENT 'Teléfono',
    cv VARCHAR(255) NULL COMMENT 'Ruta/URL del CV',
    fuente VARCHAR(80) NULL COMMENT 'Fuente de reclutamiento (LinkedIn, referido, etc.)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Banco de candidatos';

CREATE TABLE postulaciones (
    id_postulacion INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador de postulación',
    id_vacante INT NOT NULL COMMENT 'Vacante a la que postula',
    id_candidato INT NOT NULL COMMENT 'Candidato que postula',
    estado ENUM('POSTULADO','SCREENING','ENTREVISTA','PRUEBA','OFERTA','RECHAZADO','CONTRATADO') NOT NULL DEFAULT 'POSTULADO' COMMENT 'Etapa del proceso',
    comentarios VARCHAR(255) NULL COMMENT 'Notas del reclutador',
    aplicada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha/hora de postulación',
    UNIQUE KEY uq_post (id_vacante, id_candidato),
    CONSTRAINT fk_pos_vac FOREIGN KEY (id_vacante) REFERENCES vacantes(id_vacante),
    CONSTRAINT fk_pos_can FOREIGN KEY (id_candidato) REFERENCES candidatos(id_candidato)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Relación Vacante-Candidato';

CREATE TABLE entrevistas (
    id_entrevista INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador de entrevista',
    id_postulacion INT NOT NULL COMMENT 'Postulación asociada',
    entrevistador INT NOT NULL COMMENT 'Usuario entrevistador',
    programada_para DATETIME NOT NULL COMMENT 'Fecha/hora programada',
    resultado ENUM('PENDIENTE','APROBADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE' COMMENT 'Resultado',
    notas VARCHAR(255) NULL COMMENT 'Notas de la entrevista',
    CONSTRAINT fk_ent_pos FOREIGN KEY (id_postulacion) REFERENCES postulaciones(id_postulacion),
    CONSTRAINT fk_ent_usr FOREIGN KEY (entrevistador) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Entrevistas de candidatos';

-- =======================================================
-- 7) ÍNDICES ADICIONALES
-- =======================================================

CREATE INDEX idx_empleado_estado ON empleados(estado);
CREATE INDEX idx_asistencia_fecha ON asistencia_registros(fecha);
CREATE INDEX idx_postulaciones_estado ON postulaciones(estado);
CREATE INDEX idx_solicitudes_estado ON solicitudes_permiso(estado);

SET FOREIGN_KEY_CHECKS = 1;