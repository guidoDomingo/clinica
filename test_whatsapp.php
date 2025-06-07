<?php
/**
 * Script para probar el envío de PDFs por WhatsApp
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Envío de PDF por WhatsApp</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #007bff;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, button {
            padding: 8px 12px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        #resultado {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 15px;
            margin-top: 15px;
            display: none;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            overflow: auto;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Prueba de Envío de PDF por WhatsApp</h1>
        
        <div class="card">
            <h2>Instrucciones</h2>
            <p>Esta herramienta te permite probar el envío de PDFs por WhatsApp utilizando el endpoint <code>ajax/enviar_media.php</code>.</p>
            <p>El sistema:</p>
            <ol>
                <li>Utiliza <code>generar_pdf_reserva.php</code> para crear el PDF.</li>
                <li>Envía la URL del PDF a <code>ajax/enviar_media.php</code>.</li>
                <li>El endpoint se comunica con la API de WhatsApp.</li>
            </ol>
        </div>
        
        <div class="card">
            <h2>Formulario de prueba</h2>
            <div class="form-group">
                <label for="reservaId">ID de la Reserva:</label>
                <input type="number" id="reservaId" value="20" min="1">
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono (formato internacional sin +):</label>
                <input type="text" id="telefono" placeholder="Ejemplo: 595982313358">
            </div>
            <div class="form-group">
                <button id="btnTest">Enviar PDF por WhatsApp</button>
            </div>
            <div id="resultado"></div>
        </div>
    </div>
    
    <script>
        document.getElementById('btnTest').addEventListener('click', function() {
            const telefono = document.getElementById('telefono').value.trim();
            const reservaId = document.getElementById('reservaId').value.trim();
            const resultadoDiv = document.getElementById('resultado');
            
            if (!telefono) {
                alert('Por favor ingrese un número de teléfono');
                return;
            }
            
            if (!reservaId) {
                alert('Por favor ingrese un ID de reserva');
                return;
            }
            
            resultadoDiv.style.display = 'block';
            resultadoDiv.innerHTML = '<p>Enviando PDF, por favor espere...</p>';
            
            // Construir URL del PDF
            const pdfUrl = window.location.origin + '/generar_pdf_reserva.php?id=' + reservaId;
            
            // Realizar petición directa al script de envío de media
            fetch('ajax/enviar_media.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    telefono: telefono,
                    mediaUrl: pdfUrl,
                    mediaCaption: 'Confirmación de reserva médica'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log(data);
                if (data.success) {
                    resultadoDiv.innerHTML = '<p class="success">✓ PDF enviado correctamente</p><pre>' + 
                                           JSON.stringify(data, null, 2) + '</pre>';
                } else {
                    resultadoDiv.innerHTML = '<p class="error">✗ Error al enviar el PDF</p><pre>' + 
                                          JSON.stringify(data, null, 2) + '</pre>';
                }
            })
            .catch(error => {
                console.error(error);
                resultadoDiv.innerHTML = '<p class="error">✗ Error en la solicitud: ' + error.message + '</p>';
            });
        });
    </script>
</body>
</html>
