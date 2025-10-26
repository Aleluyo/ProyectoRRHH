RRHH_TEC — Sistema de Recursos Humanos
======================================

RRHH_TEC es una aplicación web modular desarrollada en PHP (XAMPP/MariaDB) para la gestión integral de Recursos Humanos en organizaciones.

------------------------------------------------------------------------

Objetivo
--------
Optimizar y automatizar las operaciones clave del departamento de Recursos Humanos: control de empleados, asistencia, permisos, nómina y reclutamiento.

Módulos principales
-------------------

- **Autenticación y Seguridad**  
  Gestión de usuarios, roles y permisos.
- **Estructura Organizacional**  
  Empresas, áreas, puestos y turnos.
- **Gestión de Empleados**  
  Expediente completo, documentos, contactos y historial laboral.
- **Asistencia y Permisos**  
  Calendarios, reglas, registros de asistencia y vacaciones.
- **Nómina**  
  Percepciones, deducciones y emisión de recibos.
- **Reclutamiento y Selección**  
  Vacantes, candidatos y entrevistas.

Requisitos
----------

- XAMPP (PHP 8.0 o superior, MariaDB)
- Git
- Navegador web moderno
- phpMyAdmin o cliente MySQL

Base de datos
-------------

Incluye archivos para inicializar el sistema:

- `sql/rrhh_tec_final_corregido.sql` — Estructura del sistema.
- `sql/rrhh_tec_datos_iniciales.sql` — Datos de prueba (2 registros por tabla).

Importar base de datos con:

    mysql -u root -p < sql/rrhh_tec_final_corregido.sql
    mysql -u root -p < sql/rrhh_tec_datos_iniciales.sql

Notas
-----

El nombre de la base de datos debe ser: **rrhh_tec**
