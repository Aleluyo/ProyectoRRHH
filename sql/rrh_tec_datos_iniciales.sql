-- ================================================
-- DATOS INICIALES RRH_TEC (Compacta)
-- Solo inserciones - Datos simulados realistas (2 por tabla)
-- ================================================

USE rrhh_tec;

-- 1) AUTENTICACIÓN Y SEGURIDAD
INSERT INTO roles (nombre_rol, descripcion) VALUES
('Administrador', 'Acceso total al sistema'),
('Usuario', 'Acceso limitado a módulos asignados');

INSERT INTO permisos_sistema (clave, descripcion) VALUES
('USUARIOS_VER', 'Permite ver usuarios'),
('EMPLEADOS_EDITAR', 'Permite editar empleados');

INSERT INTO usuarios (username, correo, contrasena, id_rol, estado, requiere_2FA, intentos_fallidos, ultimo_acceso, creado_en) VALUES
('admin', 'admin@empresa.com', 'admin123', 1, 'ACTIVO', FALSE, 0, NOW(), NOW()),
('juanperez', 'juan.perez@empresa.com', 'user123', 2, 'ACTIVO', FALSE, 0, NOW(), NOW());

INSERT INTO rol_permiso (id_rol, id_permiso) VALUES
(1,1),(1,2);

-- 2) ESTRUCTURA ORGANIZACIONAL
INSERT INTO empresas (nombre, rfc, correo_contacto, telefono, direccion, activa) VALUES
('Tecnologías Globales SA de CV', 'TGS123456AB1', 'contacto@tglobal.com', '555-112233', 'Av. Reforma 100, CDMX', TRUE),
('Innova Digital MX', 'IDM987654CD2', 'info@innovadigital.mx', '555-445566', 'Calle Insurgentes 300, CDMX', TRUE);

INSERT INTO ubicaciones (id_empresa, nombre, direccion, ciudad, estado_region, pais, activa) VALUES
(1, 'Sede Central', 'Av. Reforma 100, CDMX', 'Ciudad de México', 'CDMX', 'México', TRUE),
(2, 'Sucursal Norte', 'Av. Hidalgo 200, Monterrey', 'Monterrey', 'Nuevo León', 'México', TRUE);

INSERT INTO areas (id_empresa, id_area_padre, nombre_area, descripcion, activa) VALUES
(1, NULL, 'Recursos Humanos', 'Área encargada de personal y nómina', TRUE),
(1, 1, 'Reclutamiento', 'Subárea de Recursos Humanos', TRUE);

INSERT INTO turnos (nombre_turno, hora_entrada, hora_salida, tolerancia_minutos, dias_laborales) VALUES
('Matutino', '08:00:00', '16:00:00', 10, 'L,M,X,J,V'),
('Vespertino', '14:00:00', '22:00:00', 10, 'L,M,X,J,V');

INSERT INTO puestos (id_area, nombre_puesto, nivel, salario_base, descripcion) VALUES
(1, 'Analista de RRHH', 'OPERATIVO', 12000.00, 'Gestión de personal y asistencia'),
(2, 'Reclutador Jr', 'OPERATIVO', 10000.00, 'Apoyo en procesos de selección');

-- 3) GESTIÓN DE EMPLEADOS
INSERT INTO empleados (id_usuario, id_puesto, id_turno, id_ubicacion, nombre, curp, rfc, nss, fecha_nacimiento, genero, estado_civil, direccion, telefono, correo, fecha_ingreso, estado) VALUES
(2, 1, 1, 1, 'Juan Pérez López', 'PELJ900101HDFRRN09', 'PELJ900101AA1', '12345678901', '1990-01-01', 'M', 'Soltero', 'Calle Flores 123', '555-1234567', 'juan.perez@empresa.com', '2022-05-10', 'ACTIVO'),
(NULL, 2, 2, 2, 'María Gómez Ruiz', 'GORM920202MDFRZN08', 'GORM920202BB2', '98765432109', '1992-02-02', 'F', 'Casada', 'Av. Norte 45', '555-7654321', 'maria.gomez@empresa.com', '2023-03-01', 'ACTIVO');

INSERT INTO empleados_contactos (id_empleado, tipo, nombre, telefono, correo, parentesco) VALUES
(1, 'EMERGENCIA', 'Laura Pérez', '555-112233', 'laura.perez@gmail.com', 'Hermana'),
(2, 'EMERGENCIA', 'Carlos Ruiz', '555-223344', 'carlos.ruiz@gmail.com', 'Esposo');

INSERT INTO empleados_banco (id_empleado, banco, clabe, titular, activa) VALUES
(1, 'BBVA', '012345678901234567', 'Juan Pérez López', TRUE),
(2, 'Santander', '765432109876543210', 'María Gómez Ruiz', TRUE);

INSERT INTO empleados_documentos (id_empleado, tipo, ruta, valido_desde, valido_hasta, verificado, subido_por) VALUES
(1, 'INE', '/docs/juan_ine.pdf', '2022-01-01', '2032-01-01', TRUE, 1),
(2, 'CURP', '/docs/maria_curp.pdf', '2023-01-01', '2033-01-01', TRUE, 1);

INSERT INTO empleados_historial (id_empleado, tipo_cambio, detalle, valor_anterior, valor_nuevo, realizado_por) VALUES
(1, 'ALTA', 'Ingreso al sistema', NULL, 'Activo', 1),
(2, 'ALTA', 'Ingreso al sistema', NULL, 'Activo', 1);

-- 4) ASISTENCIA Y PERMISOS
INSERT INTO calendarios_laborales (id_empresa, nombre, descripcion, activo) VALUES
(1, 'Calendario 2025', 'Calendario laboral principal', TRUE),
(2, 'Calendario Monterrey', 'Días laborales Monterrey', TRUE);

INSERT INTO calendario_feriados (id_calendario, fecha, descripcion) VALUES
(1, '2025-01-01', 'Año Nuevo'),
(2, '2025-09-16', 'Día de la Independencia');

INSERT INTO reglas_asistencia (id_empresa, tolerancia_minutos, paga_horas_extra, redondeo_minutos, requiere_justificante) VALUES
(1, 10, TRUE, 5, TRUE),
(2, 15, FALSE, 0, TRUE);

INSERT INTO asistencia_registros (id_empleado, fecha, hora_entrada, hora_salida, tipo, origen, observaciones) VALUES
(1, '2025-10-10', '08:03:00', '16:01:00', 'NORMAL', 'RELOJ', 'Entrada puntual'),
(2, '2025-10-10', '08:20:00', '16:15:00', 'RETARDO', 'RELOJ', 'Llegó tarde');

INSERT INTO politicas_vacaciones (id_empresa, dias_inicio, incremento_anual, dias_max, periodo_anual_inicio, activa) VALUES
(1, 6, 2, 20, '2025-01-01', TRUE),
(2, 6, 2, 20, '2025-01-01', TRUE);

INSERT INTO saldos_vacaciones (id_empleado, anio, dias_asignados, dias_tomados) VALUES
(1, 2025, 8, 2),
(2, 2025, 6, 0);

INSERT INTO solicitudes_permiso (id_empleado, tipo, fecha_inicio, fecha_fin, dias, motivo, estado, creado_por) VALUES
(1, 'VACACIONES', '2025-10-20', '2025-10-22', 2, 'Descanso familiar', 'PENDIENTE', 1),
(2, 'PERMISO', '2025-10-15', '2025-10-15', 1, 'Cita médica', 'APROBADO', 1);

INSERT INTO aprobaciones_permiso (id_solicitud, aprobador, nivel, decision, comentario) VALUES
(1, 1, 1, 'PENDIENTE', 'En revisión'),
(2, 1, 1, 'APROBADO', 'Permiso concedido');

-- 5) NÓMINA
INSERT INTO periodos_nomina (id_empresa, tipo, fecha_inicio, fecha_fin, estado) VALUES
(1, 'QUINCENAL', '2025-10-01', '2025-10-15', 'CERRADO'),
(1, 'QUINCENAL', '2025-10-16', '2025-10-31', 'ABIERTO');

INSERT INTO conceptos_nomina (tipo, clave, nombre, formula, gravable) VALUES
('PERCEPCION', 'SUELDO', 'Sueldo Base', NULL, TRUE),
('DEDUCCION', 'ISR', 'Impuesto Sobre la Renta', NULL, TRUE);

INSERT INTO nomina_empleado (id_empleado, id_periodo, total_percepciones, total_deducciones, total_neto) VALUES
(1, 1, 12000.00, 1800.00, 10200.00),
(2, 1, 10000.00, 1500.00, 8500.00);

INSERT INTO nomina_detalle (id_nomina, id_concepto, monto, observacion) VALUES
(1, 1, 12000.00, 'Pago quincenal'),
(1, 2, -1800.00, 'Retención ISR');

INSERT INTO recibos_nomina (id_nomina, folio, ruta_pdf) VALUES
(1, 'REC20251001', '/recibos/rec_juan.pdf'),
(2, 'REC20251002', '/recibos/rec_maria.pdf');

-- 6) RECLUTAMIENTO
INSERT INTO vacantes (id_area, id_puesto, id_ubicacion, solicitada_por, estatus, requisitos, fecha_publicacion) VALUES
(1, 1, 1, 1, 'ABIERTA', 'Lic. en Administración o afín', '2025-10-01'),
(2, 2, 2, 1, 'EN_PROCESO', 'Experiencia en reclutamiento', '2025-10-05');

INSERT INTO vacante_aprobaciones (id_vacante, aprobador, nivel, decision, comentario) VALUES
(1, 1, 1, 'APROBADO', 'Aprobación inicial'),
(2, 1, 1, 'PENDIENTE', 'En evaluación');

INSERT INTO candidatos (nombre, correo, telefono, cv, fuente) VALUES
('Pedro Martínez', 'pedro.martinez@gmail.com', '555-9876543', '/cv/pedro.pdf', 'LinkedIn'),
('Lucía Hernández', 'lucia.hernandez@gmail.com', '555-5678901', '/cv/lucia.pdf', 'Indeed');

INSERT INTO postulaciones (id_vacante, id_candidato, estado, comentarios) VALUES
(1, 1, 'ENTREVISTA', 'Buen perfil'),
(2, 2, 'POSTULADO', 'En espera de entrevista');

INSERT INTO entrevistas (id_postulacion, entrevistador, programada_para, resultado, notas) VALUES
(1, 1, '2025-10-12 10:00:00', 'PENDIENTE', 'Primera entrevista'),
(2, 1, '2025-10-13 11:00:00', 'PENDIENTE', 'Revisión inicial');
