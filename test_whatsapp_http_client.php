<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico WhatsApp PDF</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .card { margin-bottom: 20px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 4px; }
        .test-result { margin-top: 15px; padding: 10px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .info-box { background-color: #e8f4f8; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Diagnóstico: Envío de PDF por WhatsApp</h1>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                1. Entorno y Librerías
            </div>
            <div class="card-body">
                <div class="info-box">
                    <h5>Cliente HTTP:</h5>
                    <div id="clientInfo">Cargando información...</div>
                </div>
                
                <h5>Dependencias Composer:</h5>
                <div id="composerInfo">Cargando información...</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                2. Prueba de Envío
            </div>
            <div class="card-body">
                <form id="testForm">
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono (formato internacional sin +):</label>
                        <input type="text" class="form-control" id="telefono" name="telefono" value="595982313358" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mediaUrl" class="form-label">URL del PDF:</label>
                        <input type="text" class="form-control" id="mediaUrl" name="mediaUrl" value="https://www.africau.edu/images/default/sample.pdf" required>
                        <div class="form-text">Debe ser una URL accesible públicamente</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mediaCaption" class="form-label">Descripción:</label>
                        <input type="text" class="form-control" id="mediaCaption" name="mediaCaption" value="PDF de prueba" required>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" data-endpoint="enviar_pdf_whatsapp.php">Probar envío principal</button>
                        <button type="button" class="btn btn-info test-ajax" data-endpoint="ajax/enviar_media.php">Probar envío AJAX</button>
                        <button type="button" class="btn btn-secondary test-ajax" data-endpoint="ajax/send_pdf_test.php">Probar endpoint de prueba</button>
                    </div>
                </form>
                
                <div class="test-result" id="testResult" style="display:none;"></div>
            </div>
        </div>
    </div>

    <script>
        // Obtener información del cliente HTTP
        fetch('check_http_client.php')
            .then(response => response.json())
            .then(data => {
                const clientDiv = document.getElementById('clientInfo');
                clientDiv.innerHTML = `
                    <p><strong>Cliente disponible:</strong> ${data.client}</p>
                    <p><strong>cURL:</strong> ${data.curl.available ? 'Disponible (versión ' + data.curl.version + ')' : 'No disponible'}</p>
                    <p><strong>Guzzle:</strong> ${data.guzzle.available ? 'Disponible (versión ' + data.guzzle.version + ')' : 'No disponible'}</p>
                    <p><strong>PHP:</strong> ${data.server_info.php_version}</p>
                `;
            })
            .catch(error => {
                document.getElementById('clientInfo').innerHTML = '<p class="text-danger">Error al obtener información del cliente HTTP: ' + error.message + '</p>';
            });

        // Obtener información de composer
        fetch('composer.json')
            .then(response => response.text())
            .then(data => {
                document.getElementById('composerInfo').innerHTML = '<pre>' + data + '</pre>';
            })
            .catch(error => {
                document.getElementById('composerInfo').innerHTML = '<p class="text-danger">Error al obtener información de Composer: ' + error.message + '</p>';
            });

        // Manejar el envío del formulario principal
        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const endpoint = e.submitter.getAttribute('data-endpoint');
            const formData = new FormData(this);
            
            sendRequest(endpoint, formData);
        });

        // Manejar los botones de prueba AJAX
        document.querySelectorAll('.test-ajax').forEach(button => {
            button.addEventListener('click', function() {
                const endpoint = this.getAttribute('data-endpoint');
                const formData = new FormData(document.getElementById('testForm'));
                
                // Para endpoints AJAX que esperan JSON
                const requestData = {
                    telefono: formData.get('telefono'),
                    mediaUrl: formData.get('mediaUrl'),
                    mediaCaption: formData.get('mediaCaption')
                };
                
                sendRequest(endpoint, null, requestData);
            });
        });

        // Función para enviar solicitudes
        function sendRequest(endpoint, formData, jsonData = null) {
            const resultDiv = document.getElementById('testResult');
            resultDiv.style.display = 'block';
            resultDiv.className = 'test-result info-box';
            resultDiv.innerHTML = '<p>Enviando solicitud a ' + endpoint + '...</p>';
            
            let fetchOptions = {
                method: 'POST'
            };
            
            if (formData) {
                fetchOptions.body = formData;
            } else if (jsonData) {
                fetchOptions.body = JSON.stringify(jsonData);
                fetchOptions.headers = {
                    'Content-Type': 'application/json'
                };
            }
            
            fetch(endpoint, fetchOptions)
                .then(response => response.json())
                .then(data => {
                    console.log('Respuesta recibida:', data);
                    
                    if (data.success) {
                        resultDiv.className = 'test-result success';
                        resultDiv.innerHTML = `
                            <h5>¡Envío exitoso!</h5>
                            <p>El documento se envió correctamente.</p>
                            <p><strong>Cliente usado:</strong> ${data.method || 'No especificado'}</p>
                            <p><strong>Mensaje:</strong> ${data.message || 'No hay mensaje'}</p>
                            <h6>Respuesta completa:</h6>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        `;
                    } else {
                        resultDiv.className = 'test-result error';
                        resultDiv.innerHTML = `
                            <h5>Error en el envío</h5>
                            <p><strong>Error:</strong> ${data.error || 'Error desconocido'}</p>
                            <h6>Detalles:</h6>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        `;
                    }
                })
                .catch(error => {
                    resultDiv.className = 'test-result error';
                    resultDiv.innerHTML = `
                        <h5>Error de conexión</h5>
                        <p>${error.message}</p>
                    `;
                });
        }
    </script>

</body>
</html>
