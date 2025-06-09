# Logs del Sistema

Este directorio contiene los archivos de registro (logs) del sistema para facilitar diagnóstico de errores y monitoreo de actividades.

## Archivos de log principales

- **application.log**: Log general de la aplicación, errores no categorizados
- **archivos.log**: Registro de operaciones con archivos (uploads, eliminaciones)
- **consultas.log**: Registro de creación y modificación de consultas médicas
- **database.log**: Errores y operaciones críticas de base de datos
- **reservas.log**: Registro de creación, modificación y eliminación de reservas
- **servicios.log**: Registro de operaciones con servicios médicos

## Logs de depuración

- **debug_consultas.log**: Información detallada para depurar operaciones de consultas
- **debug_consultas_detallado.log**: Información extendida para problemas complejos con consultas
- **debug_guardar.php**: Script auxiliar para depurar procesos de guardado
- **debug_guardar_detallado.php**: Script auxiliar extendido para problemas de guardado

## Logs de componentes específicos

- **slots.log**: Registro de operaciones relacionadas con slots de horarios
- **whatsapp_envios_YYYY_MM.log**: Registro mensual de envíos por WhatsApp

## Estructura de logs

Los logs tienen el siguiente formato general:
```
[FECHA HORA] [NIVEL] [IP] [USUARIO] Mensaje
```

Ejemplo:
```
[2025-06-07 13:45:22] [ERROR] [192.168.1.10] [admin] Error al guardar la consulta: mensaje detallado
```

## Rotación de logs

Los logs son rotados automáticamente cuando tienen más de 30 días de antigüedad.
Los logs rotados se mueven a la subcarpeta `archive/`.

## Mantenimiento

Se recomienda ejecutar el script `limpieza_automatica.php` periódicamente para 
mantener el tamaño de los archivos de log bajo control.
