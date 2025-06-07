# Diagnóstico y Soluciones: API WhatsApp PDF

## Problema Identificado
La API externa (waapi.app) está devolviendo un error 400 al intentar enviar un PDF. El mensaje específico es:
> "Unable to download media file. Received status code 404 on preflight head check. Expected 200."

Esto significa que la API de WhatsApp está intentando hacer una solicitud HEAD para verificar si el PDF existe antes de descargarlo y enviarlo, pero está recibiendo un 404 (no encontrado) en esa verificación previa.

## Causas Potenciales del Problema

1. **Accesibilidad del PDF**: El servidor donde está alojado el PDF puede no permitir solicitudes HEAD o puede tener reglas de configuración específicas que bloquean este tipo de solicitud preliminar.

2. **CORS (Cross-Origin Resource Sharing)**: El servidor donde está alojado el PDF puede tener restricciones de CORS que impiden que la API externa acceda al archivo.

3. **Protecciones de hotlinking**: Algunos servidores impiden que sus recursos se enlacen desde dominios externos (hotlinking protection).

4. **Problemas con la URL**: La URL podría no ser accesible públicamente o podría tener caracteres que necesitan ser codificados correctamente.

5. **Caducidad del enlace**: Algunos servidores generan URLs temporales que caducan después de cierto tiempo.

## Soluciones Propuestas

### 1. Utilizar URL de PDF público en servidores confiables

**Pros:**
- Implementación inmediata, sin cambios en el código
- Sin esfuerzo adicional en mantener infraestructura

**Contras:**
- Dependencia de servicios externos
- No es una solución sostenible para PDFs generados dinámicamente
- Posibles problemas de privacidad al utilizar hosting externo

### 2. Subir el PDF a un servidor propio accesible públicamente

**Pros:**
- Control total sobre el acceso al PDF
- Se puede configurar el servidor para permitir solicitudes HEAD y CORS
- Mayor privacidad que los servicios externos

**Contras:**
- Requiere un servidor web accesible públicamente
- Necesita configuración específica del servidor

### 3. Cambiar el enfoque de la API - Enviar el PDF como archivo adjunto

**Pros:**
- Elimina la necesidad de que la API descargue el PDF
- Mayor fiabilidad

**Contras:**
- Requiere modificar el contrato de la API externa
- Posible aumento en el tráfico de red

### 4. Usar un servicio de almacenamiento público (como AWS S3 o Google Cloud Storage)

**Pros:**
- Alta disponibilidad y fiabilidad
- Configuración CORS sencilla
- Soporte explícito para solicitudes HEAD

**Contras:**
- Costo adicional
- Complejidad de configuración e integración
- Posibles problemas de privacidad

### 5. Implementar un proxy para servir PDFs

**Pros:**
- Control total sobre las respuestas HTTP
- Puede configurarse para responder correctamente a solicitudes HEAD
- Mantiene los PDFs en el servidor privado

**Contras:**
- Complejidad de implementación
- Requiere recursos de servidor adicionales

## Recomendación a Corto Plazo

1. **Probar con diferentes URLs de PDFs públicos** para identificar qué servidores funcionan correctamente con la API. Hemos creado la herramienta `test_pdf_urls.php` para este propósito.

2. **Implementar la solución de PDF local** (`test_pdf_local.php`) que sube el PDF al servidor propio y genera una URL pública antes de enviarlo a la API.

3. **Verificar que el servidor está configurado correctamente** para manejar solicitudes HEAD y tiene las cabeceras CORS adecuadas para archivos PDF.

## Recomendación a Largo Plazo

1. **Implementar almacenamiento en la nube** para los PDFs generados, utilizando servicios como AWS S3 o Google Cloud Storage que están específicamente diseñados para este caso de uso.

2. **Considerar la posibilidad de modificar la API** para aceptar archivos binarios directamente si es una opción viable.

3. **Implementar un sistema de limpieza** para eliminar PDFs antiguos y evitar acumulación de archivos en el servidor.

## Próximos Pasos

1. Continuar las pruebas con `test_pdf_urls.php` para identificar una fuente de PDF que funcione consistentemente.

2. Implementar la solución de subida local con `test_pdf_local.php` como solución temporal.

3. Realizar la configuración adecuada del servidor web para permitir solicitudes HEAD y configurar CORS correctamente.

4. Evaluar la viabilidad de las soluciones a largo plazo según los requisitos específicos del proyecto.
