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

## Mantenimiento del Sistema en Producción

### Limpieza de Archivos Temporales

El sistema genera archivos PDF temporales en las siguientes ubicaciones:
- `pdf_temp/`: PDFs generados temporalmente
- `ajax/temp/pdfs_web/`: PDFs temporales para envío web

Es recomendable limpiar estos directorios periódicamente mediante una tarea programada:

```php
// Ejemplo de script para limpiar archivos temporales
$directorios = ['pdf_temp', 'ajax/temp/pdfs_web', 'uploads/temp'];
foreach ($directorios as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < time() - 86400) { // más de 1 día
                unlink($file);
            }
        }
    }
}
```

### Rotación de Logs

El sistema mantiene logs en la carpeta `logs/`. Para evitar que crezcan indefinidamente:

1. Configura una rotación de logs diaria o semanal
2. Utiliza el siguiente script en una tarea programada:

```php
// Rotar logs con más de 30 días
$logDir = 'logs';
$files = glob($logDir . '/*.log');
foreach ($files as $file) {
    if (is_file($file) && filemtime($file) < time() - (30 * 86400)) {
        $archiveDir = $logDir . '/archive';
        if (!is_dir($archiveDir)) mkdir($archiveDir, 0755, true);
        
        $newName = $archiveDir . '/' . basename($file) . '.' . date('Y-m-d', filemtime($file));
        rename($file, $newName);
    }
}
```

### Respaldo de Base de Datos

Configura respaldos automáticos de la base de datos:

```bash
# Ejemplo de script para respaldo de PostgreSQL
pg_dump -U usuario -Fc nombredb > /ruta/respaldos/clinica_$(date +%Y%m%d).dump
```

### Monitoreo del Sistema

Para monitorear el sistema en producción:

1. Revisa regularmente los logs en `logs/application.log` y `logs/database.log`
2. Configura alertas para errores críticos
3. Monitorea el espacio en disco, especialmente en las carpetas de uploads y PDFs

### Optimización

Para mantener el rendimiento óptimo:

1. Activa el caché de PHP
2. Configura el servidor web con compresión gzip
3. Utiliza un CDN para archivos estáticos si el tráfico aumenta

## Estructura de la Base de Datos
