# Solución para el problema de guardado de consultas

## Diagnóstico del problema

Después de realizar un análisis detallado, hemos identificado dos problemas:

1. **Error en la llamada a bindParam()**: El error principal es que se estaba intentando usar el operador de fusión de null (`??`) directamente dentro de la función `bindParam()`, lo que no es posible ya que esta función espera una variable por referencia y no una expresión.

   ```
   Fatal error: Uncaught Error: PDOStatement::bindParam(): Argument #2 ($var) could not be passed by reference in C:\laragon\www\clinica\model\consultas.model.php:147
   ```

2. **Falta de la extensión pdo_pgsql**: Adicionalmente, la extensión **pdo_pgsql** no está habilitada en PHP, lo que impide la conexión a la base de datos PostgreSQL una vez que se solucione el primer problema.

## Pasos para solucionar el problema

1. **Corregir el error de bindParam en consultas.model.php**:
   - Se ha modificado el archivo `consultas.model.php` para usar variables intermedias con `bindParam()` en lugar de expresiones directas.
   - El problema estaba en líneas como:
     ```php
     $stmt->bindParam(":motivoscomunes", $datos["motivoscomunes"] ?? '', PDO::PARAM_STR);
     ```
   - Se ha corregido a:
     ```php
     $motivoscomunes = isset($datos["motivoscomunes"]) ? $datos["motivoscomunes"] : '';
     $stmt->bindParam(":motivoscomunes", $motivoscomunes, PDO::PARAM_STR);
     ```

2. **Habilitar la extensión pdo_pgsql en PHP**:
   - Localiza el archivo php.ini que está usando tu instalación de Laragon
   - Normalmente estará en: `C:\laragon\bin\php\php-X.X.X\php.ini`
   - Edita este archivo y busca las líneas:
     ```
     ;extension=pdo_pgsql
     ;extension=pgsql
     ```
   - Quita los punto y coma del inicio para habilitar estas extensiones:
     ```
     extension=pdo_pgsql
     extension=pgsql
     ```
   - Guarda el archivo

2. **Reiniciar el servidor web**:
   - Detén y reinicia Laragon para que los cambios en php.ini tengan efecto

3. **Probar la conexión**:
   - Ejecuta el script de prueba: `php test_insercion_consulta.php`
   - Si funciona correctamente, verás un mensaje de éxito con el ID de la consulta insertada

4. **Verificar los logs detallados**:
   - Una vez habilitada la extensión, intenta guardar una consulta desde la interfaz
   - Revisa los logs detallados en: `c:\laragon\www\clinica\logs\debug_consultas_detallado.log`

## Archivos de diagnóstico creados

1. **logs/debug_guardar_detallado.php**:
   - Sistema mejorado de logs para rastrear el proceso completo

2. **test_insercion_consulta.php**:
   - Script para probar la inserción directa en la tabla consultas

3. **check_php_extensions.php**:
   - Script para verificar las extensiones PHP habilitadas

4. **phpinfo.php**:
   - Script para ver la información completa de la configuración PHP

## Notas adicionales

- Se han agregado logs detallados en puntos críticos del código (controlador, modelo y capa de acceso a datos)
- Se han implementado verificaciones adicionales para los campos obligatorios
- Se ha mejorado el manejo de errores para proporcionar mensajes más informativos
