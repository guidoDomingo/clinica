# Documentación: Envío de PDFs de Reservas por WhatsApp

## Descripción General

Esta funcionalidad permite enviar PDFs de confirmación de reservas médicas directamente a los pacientes a través de WhatsApp. Se integra con una API externa (AventisAPI) que proporciona el servicio de mensajería.

## Requisitos Técnicos

- PHP 7.2 o superior
- Composer para gestión de dependencias
- Biblioteca Guzzle para peticiones HTTP
- Biblioteca dompdf para generación de PDFs
- Acceso al servidor de la API de WhatsApp (http://aventisdev.com:8082/media.php)

## Flujo de Funcionamiento

1. **Generación del PDF**: Cuando se solicita un PDF, se genera utilizando dompdf y se almacena temporalmente en el servidor.
2. **Envío del PDF**: El PDF se envía a la API externa junto con el número de teléfono del paciente.
3. **Respuesta de la API**: Se recibe y procesa la respuesta de la API, mostrando el resultado al usuario.
4. **Registro de actividad**: Se registra cada intento de envío en un archivo de log para seguimiento.

## Utilización para Usuarios Finales

### Enviar un PDF por WhatsApp desde la tabla de reservas

1. En la tabla de reservas, localice la reserva que desee enviar.
2. Haga clic en el icono de WhatsApp en la columna de acciones.
3. Introduzca el número de teléfono del paciente (formato internacional sin +, ej: 595982313358).
4. El sistema enviará el PDF y mostrará una notificación de éxito o error.

### Enviar un PDF específico por WhatsApp

El sistema ahora también permite enviar PDFs específicos utilizando una herramienta de prueba:

1. Abra la página `test_pdf_url_especifica.html` para acceder a la herramienta.
2. Seleccione una de las tres opciones disponibles:
   - **URL de PDF**: Ingrese una URL pública de un PDF existente.
   - **Subir PDF**: Suba un nuevo archivo PDF desde su computadora.
   - **PDF Local**: Pruebe con una ruta local (podría utilizar un PDF de respaldo).
3. Ingrese el número de teléfono del destinatario.
4. Haga clic en "Enviar PDF por WhatsApp".

### Consideraciones importantes

- El teléfono debe estar en formato internacional sin el signo "+" (ej: 595982313358).
- En entorno de desarrollo, se utiliza un PDF de prueba (W3.org) en lugar del PDF real.
- En producción, se utiliza el PDF generado para la reserva específica.
- Para URLs de PDFs específicas, asegúrese de que sean públicamente accesibles para la API externa.

## Detalles Técnicos para Desarrolladores

### Archivos Principales

- `enviar_pdf_reserva.js`: Gestiona la interfaz de usuario y peticiones AJAX.
- `send_pdf_test.php`: Endpoint que procesa la solicitud y se comunica con la API.
- `log_whatsapp_envios.php`: Sistema de registro de actividad.
- `generar_pdf_reserva.php`: Genera el PDF con información de la reserva.
- `upload_pdf.php`: Endpoint para subir y validar PDFs.
- `pdf_uploader.php`: Funciones auxiliares para manejar PDFs.
- `test_pdf_url_especifica.html`: Herramienta para probar envíos con PDFs específicos.

### Sistema de Respaldo

El sistema incluye un mecanismo de respaldo automático que:
1. Intenta utilizar la URL local del PDF generado.
2. Si detecta que es un entorno de desarrollo (localhost), utiliza automáticamente un PDF de respaldo público.
3. Registra la URL utilizada en cada envío en los logs para diagnóstico.
4. Permite especificar URLs personalizadas para casos especiales.

### Validación de PDFs

Antes de enviar un PDF por WhatsApp, el sistema:
1. Verifica que la URL sea accesible mediante una solicitud HEAD.
2. Comprueba que el tipo de contenido sea aplicación/pdf.
3. Si el PDF no es accesible, utiliza automáticamente una URL de respaldo.

### Registro y Monitoreo

El sistema crea automáticamente logs mensuales en la carpeta `logs/` con información detallada de cada envío:
- Fecha y hora del envío
- ID de reserva
- Número de teléfono
- Resultado (éxito o error)
- Mensaje de error (si aplica)
- IP del remitente
- Navegador utilizado

## Solución de Problemas

### El PDF no se envía correctamente

1. Verificar que el número de teléfono esté en formato internacional sin "+".
2. Comprobar que la API externa esté operativa (http://aventisdev.com:8082).
3. Revisar los logs en la carpeta `logs/` para identificar el error específico.
4. Asegurarse de que el PDF sea accesible desde Internet (importante para producción).

### Error "URL del PDF no accesible"

Esta situación es normal en entorno de desarrollo, donde el sistema utilizará automáticamente un PDF de respaldo. En producción, debe asegurarse de que los PDFs generados sean accesibles desde Internet.

### Envío de PDFs locales

Los PDFs locales como `file:///C:/Users/...` no son accesibles para la API externa. Utilice una de estas alternativas:
1. Suba el PDF utilizando la herramienta de prueba (`test_pdf_url_especifica.html`).
2. Coloque el PDF en un servidor web accesible públicamente.
3. Utilice la funcionalidad estándar de generación de PDFs del sistema.

## Mejoras Futuras

- Implementar una cola de envíos para gestionar grandes volúmenes.
- Añadir interfaz de administración para monitoreo de envíos.
- Mejorar el almacenamiento de PDFs con una solución más robusta (Amazon S3, por ejemplo).
- Desarrollar una función para convertir PDFs locales en accesibles mediante un servicio de alojamiento temporal.

---

*Esta documentación fue actualizada el 6 de junio de 2025. Consulte con el administrador del sistema para conocer posibles actualizaciones.*
