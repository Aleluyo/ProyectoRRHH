# 🏢 RRHH_TEC — Sistema Integral de Recursos Humanos

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-10.4+-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.0+-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Status](https://img.shields.io/badge/Status-Activo-success?style=for-the-badge)

**RRHH_TEC** es una solución web moderna, modular y robusta diseñada para centralizar y optimizar la gestión del capital humano. Desarrollada con una arquitectura limpia en **PHP nativo** y estilizada con **TailwindCSS**, ofrece una experiencia de usuario fluida y profesional.

---

## 🚀 Características Principales

El sistema está dividido en módulos estratégicos para cubrir todas las necesidades del departamento de RRHH:

### 👥 Gestión de Empleados
*   **Expediente Digital 360°**: Información personal, contactos de emergencia y documentos.
*   **Historial Laboral**: Seguimiento de puestos, áreas y cambios internos.
*   **Gestión de Documentos**: Carga y visualización de contratos, CVs, etc.

### 💸 Nómina Inteligente
*   **Cálculo Automatizado**: Percepciones, deducciones y salario neto.
*   **Recibos de Nómina**: Generación de recibos listos para imprimir.
*   **Histórico y Archivo**: Control de periodos activos y archivado de nóminas pasadas (Cerrar y Archivar).

### 📅 Asistencia y Tiempos
*   **Control de Asistencia**: Registro de entradas, salidas y retardos.
*   **Gestión de Incidencias**: Vacaciones, incapacidades y permisos.
*   **Reportes**: Tarjetas de asistencia detalladas.

### 🤝 Reclutamiento y Selección
*   **Bolsa de Trabajo Interna**: Publicación y gestión de vacantes (Aprobación, Abierta, Cerrada).
*   **Pipeline de Candidatos**: Seguimiento desde la aplicación hasta la contratación.
*   **Entrevistas**: Programación y registro de resultados.

### ⚙️ Configuración Organizacional
*   **Empresas Multi-Entidad**: Gestión de múltiples razones sociales.
*   **Estructura Jerárquica**: Definición de Áreas y Puestos.
*   **Seguridad**: Control de acceso basado en Roles (RBAC).

---

## 🛠️ Stack Tecnológico

*   **Backend**: PHP 8 (Vanilla, MVC Architecture).
*   **Base de Datos**: MariaDB / MySQL.
*   **Frontend**: HTML5, TailwindCSS (CDN), JavaScript (ES6+).
*   **Herramientas**: Composer, Git.

---

## 💻 Instalación y Configuración

Sigue estos pasos para desplegar el proyecto en tu entorno local (XAMPP/WAMP/Laragon):

### 1. Clonar el Repositorio
```bash
git clone https://github.com/tu-usuario/rrhh-tec.git
cd rrhh-tec
```

### 2. Base de Datos
Crea una base de datos llamada `rrhh_tec` e importa los scripts en el siguiente orden:

1.  `sql/rrhh_tec_final_corregido.sql` _(Estructura)_
2.  `sql/rrhh_tec_datos_iniciales.sql` _(Datos Semilla)_

```bash
mysql -u root -p rrhh_tec < sql/rrhh_tec_final_corregido.sql
mysql -u root -p rrhh_tec < sql/rrhh_tec_datos_iniciales.sql
```

### 3. Configuración
Asegúrate de que el archivo `config/db.php` tenga las credenciales correctas:

```php
// config/db.php
$host = 'localhost';
$db   = 'rrhh_tec';
$user = 'root';
$pass = '';
```

### 4. Ejecutar
Abre tu navegador y accede a:
`http://localhost/ProyectoRRHH/public/`

---

## 📄 Licencia

Este proyecto es de uso privado y educativo.

---