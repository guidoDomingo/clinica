# Clínica - Reserva de Servicios

## Configuración del Sistema

### Requisitos previos

- PHP 7.4 o superior
- PostgreSQL 12 o superior
- Extensiones de PHP:
  - pdo_pgsql
  - pgsql
  - gd (para la generación de PDFs)

### Estructura del Proyecto

- **admin/**: Panel de administración
- **ajax/**: Endpoints para peticiones asíncronas
- **api/**: API REST del sistema
- **config/**: Archivos de configuración
- **controller/**: Controladores MVC
- **lib/**: Librerías y dependencias internas
- **logs/**: Registros del sistema
- **model/**: Modelos de datos
- **pdf_reservas/**: Almacenamiento de PDFs generados para reservas
- **pdf_temp/**: Almacenamiento temporal de PDFs
- **temp/**: Archivos temporales
- **uploads/**: Archivos subidos por usuarios
- **vendor/**: Dependencias de Composer
- **view/**: Vistas y archivos frontend

### Problemas Comunes de Conexión a la Base de Datos

Si estás experimentando problemas de conexión a la base de datos, sigue estos pasos:

1. **Verificar que las extensiones PostgreSQL están habilitadas**

   Ejecuta el archivo `enable_pgsql.php` en el navegador para comprobar si las extensiones están habilitadas. Si no lo están, necesitarás editar tu archivo php.ini.

2. **Habilitar extensiones PostgreSQL en php.ini**

   Localiza tu archivo php.ini (normalmente en la carpeta de instalación de PHP) y busca las siguientes líneas:

   ```
   ;extension=pgsql
   ;extension=pdo_pgsql
   ```

   Quita los punto y coma (;) del inicio de esas líneas para habilitar las extensiones:

   ```
   extension=pgsql
   extension=pdo_pgsql
   ```

   Después de hacer estos cambios, reinicia tu servidor web.

3. **Verificar credenciales de PostgreSQL**

   Asegúrate de que las credenciales de la base de datos en los archivos `model/conexion.php` y `config/config.php` sean correctas.

4. **Verificar que el servicio de PostgreSQL esté en ejecución**

   En Windows, puedes verificar si el servicio está funcionando a través del Administrador de Servicios.

### Diagnóstico de la Reserva de Servicios

Para diagnosticar problemas específicos del sistema de reserva de servicios:

1. Ejecuta `diagnostico_agenda_medico.php` para verificar relaciones entre agendas y médicos.
2. Ejecuta `diagnostico_doctores_fecha.php` para verificar la disponibilidad de médicos por fecha.
3. Ejecuta `test_reserva.php` para probar el flujo completo de reserva.

### Logs de Errores

Los archivos de log se almacenan en la carpeta `logs/`:

- `database.log` - Errores de conexión a la base de datos
- `ajax.log` - Errores en las peticiones AJAX
- `application.log` - Errores generales de la aplicación

## Funcionalidad de Reservas y PDF/WhatsApp

El sistema permite generar y enviar PDFs de reservas médicas por WhatsApp:

- `generar_pdf_reserva.php`: Genera PDFs de reservas usando DomPDF
- `enviar_pdf_whatsapp.php`: Envía PDFs a través de WhatsApp usando una API externa
- `view/js/enviar_pdf_reserva.js`: Script frontend para la funcionalidad

Para más detalles sobre la integración con WhatsApp, consulte el archivo `DOCUMENTACION_WHATSAPP_PDF.md`.

## Mantenimiento del Sistema

Para mantener el sistema limpio y eficiente, se han implementado scripts de mantenimiento:

- `limpiar_logs.php`: Elimina logs antiguos
- `limpiar_pdf_temporales.php`: Elimina PDFs temporales
- `mantenimiento_sistema.php`: Script completo de mantenimiento

### Mantenimiento Automatizado

Para configurar un mantenimiento automático semanal:

1. Ejecute `configurar_mantenimiento_automatico.bat` (requiere privilegios de administrador)
2. Esto configurará una tarea programada para ejecutar la limpieza cada domingo a la 1:00 AM
