# Backups de Archivos Eliminados

Este directorio contiene respaldos organizados en subcarpetas de todos los archivos que han sido eliminados durante el proceso de limpieza del proyecto para su puesta en producción.

## Estructura de los respaldos

Cada respaldo está organizado en una carpeta con fecha y hora de cuando se realizó el proceso de limpieza:

```
backups/
  ├── limpieza_YYYY-MM-DD_HH-MM-SS/
  │    ├── archivo1.php
  │    ├── archivo2.php
  │    └── ...
  └── ...
```

## Categorías de archivos eliminados

Los archivos se eliminan en las siguientes categorías:

1. **Archivos de diagnóstico**: Documentación y archivos MD con soluciones de problemas
2. **Scripts de mantenimiento y limpieza**: Archivos utilizados durante el desarrollo para mantenimiento
3. **Archivos auxiliares**: Utilidades como phpinfo.php
4. **Scripts de listado**: Archivos PHP que han sido reemplazados por la interfaz principal
5. **Archivos Ajax de prueba y respaldo**: Versiones de prueba y respaldos de archivos AJAX

## Propósito

Estos respaldos se mantienen como medida de seguridad en caso de que sea necesario restaurar algún archivo en el futuro, pero no son necesarios para el funcionamiento normal del sistema en producción.

## Notas

- No se eliminan archivos esenciales para el funcionamiento del sistema
- Los archivos de configuración y código principal no se ven afectados
- Solo se eliminan archivos de prueba, diagnóstico, temporales y respaldos antiguos
