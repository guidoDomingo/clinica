# Clínica - Reserva de Servicios

## Configuración del Sistema

### Requisitos previos

- PHP 7.4 o superior
- PostgreSQL 12 o superior
- Extensiones de PHP:
  - pdo_pgsql
  - pgsql

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
