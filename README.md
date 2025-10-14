# 🧩 RRH_TEC — Sistema de Recursos Humanos

**RRH_TEC** es una aplicación web desarrollada en **PHP (XAMPP/MariaDB)** para la gestión integral de Recursos Humanos.

## 🚀 Objetivo
Optimizar y automatizar las operaciones del departamento de RRHH: control de empleados, asistencia, permisos, nómina y reclutamiento.

---

## 🏗️ Módulos Principales
1. **Autenticación y Seguridad**  
   - Gestión de usuarios, roles y permisos.
2. **Estructura Organizacional**  
   - Empresas, áreas, puestos y turnos.
3. **Gestión de Empleados**  
   - Expediente completo, documentos, contactos, historial.
4. **Asistencia y Permisos**  
   - Calendarios, reglas, registros de asistencia, vacaciones.
5. **Nómina**  
   - Percepciones, deducciones, recibos.
6. **Reclutamiento y Selección**  
   - Vacantes, candidatos, entrevistas.

---

## ⚙️ Requisitos
- **XAMPP** (PHP ≥ 8.0, MariaDB)
- **Git**
- **Navegador Web**
- **phpMyAdmin** o cliente MySQL

---

## 🗄️ Base de Datos
Incluye:
- `sql/rrh_tec_final_corregido.sql` → Estructura del sistema  
- `sql/rrh_tec_datos_iniciales.sql` → Datos iniciales de prueba (2 por tabla)

### Importar base de datos:
```bash
mysql -u root -p < sql/rrh_tec_final_corregido.sql
mysql -u root -p < sql/rrh_tec_datos_iniciales.sql
