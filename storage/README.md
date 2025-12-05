# Storage Directory

Este directorio contiene los archivos subidos por los usuarios.

## Estructura

- `documentos/` - Documentos de expedientes de empleados
  - `{id_empleado}/` - Carpeta por cada empleado con sus documentos

## Permisos

Este directorio debe tener permisos de escritura (755 o 775) para que el servidor web pueda guardar archivos.

## Seguridad

- Los archivos NO son accesibles directamente por URL
- Solo se pueden descargar a través del controlador con validación de permisos
- Se recomienda agregar este directorio al .gitignore para no versionar archivos sensibles
