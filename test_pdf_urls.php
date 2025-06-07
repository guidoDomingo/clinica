<?php
/**
 * Script para probar diferentes PDFs públicos con la API de WhatsApp
 * Este script ayuda a identificar qué URLs de PDF funcionan con la API externa
 */

// Incluir el autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

// Importar las clases de Guzzle
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

// Mostrar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Lista de PDFs públicos para probar
$pdfUrls = [
    // PDFs públicos comunes
    'africau' => 'https://www.africau.edu/images/default/sample.pdf',
    'mozilla' => 'https://media.mozilla.org/pdfjs/web/compressed.tracemonkey-pldi-09.pdf',
    'adobe' => 'https://acrobatusers.com/assets/uploads/public_downloads/2217/pdf_sample_file.pdf',
    'w3' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
    'pdfs_org' => 'https://pdfobject.com/pdf/sample.pdf',
    // URLs con HTTPS
    'github' => 'https://github.github.com/training-kit/downloads/github-git-cheat-sheet.pdf',
    'state_gov' => 'https://www.state.gov/wp-content/uploads/2019/05/Diversity-and-Inclusion-Strategic-Plan.pdf',
    // PDFs pequeños
    'small_pdf' => 'https://smallpdf.com/sample.pdf',
];

// Función para verificar disponibilidad de un PDF mediante una solicitud HEAD
function checkPdfAvailability($url) {
    try {
        $client = new Client([
            'timeout' => 10,
            'verify' => false
        ]);
        
        // Intentar solicitud HEAD primero
        $headResponse = $client->request('HEAD', $url);
        $headStatusCode = $headResponse->getStatusCode();
        $headContentType = $headResponse->getHeaderLine('Content-Type');
        
        $result = [
            'url' => $url,
            'head_status' => $headStatusCode,
            'head_content_type' => $headContentType,
            'supports_head' => true
        ];
        
        // Si la solicitud HEAD fue exitosa, intentar GET parcial
        if ($headStatusCode == 200) {
            try {
                $getResponse = $client->request('GET', $url, [
                    'headers' => [
                        'Range' => 'bytes=0-1023' // Solo obtener el primer KB
                    ]
                ]);
                
                $result['get_status'] = $getResponse->getStatusCode();
                $result['get_content_type'] = $getResponse->getHeaderLine('Content-Type');
                $result['content_length'] = $getResponse->getHeaderLine('Content-Length');
                $result['supports_range'] = ($getResponse->getStatusCode() == 206);
            } catch (RequestException $e) {
                $result['get_error'] = $e->getMessage();
            }
        }
        
        return $result;
    } catch (RequestException $e) {
        return [
            'url' => $url,
            'supports_head' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Configurar para devolver HTML o JSON
$format = isset($_GET['format']) ? $_GET['format'] : 'html';
if ($format === 'json') {
    header('Content-Type: application/json');
}

// Verificar cada URL
$results = [];
foreach ($pdfUrls as $name => $url) {
    $results[$name] = checkPdfAvailability($url);
}

// Si se solicitó formato JSON, devolver resultados como JSON
if ($format === 'json') {
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}

// De lo contrario, mostrar resultados en HTML
?>
<!DOCTYPE html>
<html>
<head>
    <title>Prueba de URLs de PDF</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .result-table { margin-top: 20px; }
        .success { background-color: #d4edda; }
        .warning { background-color: #fff3cd; }
        .error { background-color: #f8d7da; }
        .test-section { margin-top: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Prueba de URLs de PDF para API de WhatsApp</h1>
        <p class="lead">Este script verifica la disponibilidad y características de diferentes PDFs públicos para encontrar uno compatible con la API de WhatsApp.</p>
        
        <div class="alert alert-info">
            <p><strong>Nota:</strong> La API de WhatsApp hace una verificación previa tipo HEAD para comprobar que el archivo existe antes de descargarlo. Si ese check devuelve un código diferente a 200, la API rechazará el archivo.</p>
        </div>
        
        <h2>Resultados de disponibilidad</h2>
        <table class="table table-bordered result-table">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>URL</th>
                    <th>Soporte HEAD</th>
                    <th>Estado HEAD</th>
                    <th>Content-Type</th>
                    <th>Soporte Range</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $name => $result): ?>
                <?php 
                    $rowClass = '';
                    if (isset($result['error'])) {
                        $rowClass = 'error';
                    } elseif ($result['head_status'] == 200 && strpos($result['head_content_type'], 'pdf') !== false) {
                        $rowClass = 'success';
                    } elseif ($result['head_status'] == 200) {
                        $rowClass = 'warning';
                    }
                ?>
                <tr class="<?= $rowClass ?>">
                    <td><?= htmlspecialchars($name) ?></td>
                    <td><a href="<?= htmlspecialchars($result['url']) ?>" target="_blank"><?= htmlspecialchars($result['url']) ?></a></td>
                    <td><?= isset($result['supports_head']) ? ($result['supports_head'] ? 'Sí' : 'No') : 'Error' ?></td>
                    <td><?= isset($result['head_status']) ? $result['head_status'] : 'N/A' ?></td>
                    <td><?= isset($result['head_content_type']) ? $result['head_content_type'] : 'N/A' ?></td>
                    <td><?= isset($result['supports_range']) ? ($result['supports_range'] ? 'Sí' : 'No') : 'N/A' ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary test-url" data-url="<?= htmlspecialchars($result['url']) ?>">Probar envío</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="test-section">
            <h2>Prueba de envío</h2>
            <div class="card">
                <div class="card-body">
                    <form id="testForm">
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono (formato internacional sin +):</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" value="595982313358" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="mediaUrl" class="form-label">URL del PDF:</label>
                            <input type="text" class="form-control" id="mediaUrl" name="mediaUrl" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="mediaCaption" class="form-label">Descripción:</label>
                            <input type="text" class="form-control" id="mediaCaption" name="mediaCaption" value="PDF de prueba" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Probar envío</button>
                    </form>
                    
                    <div class="mt-3" id="testResult" style="display:none;">
                        <div class="card">
                            <div class="card-header" id="resultHeader">Resultado</div>
                            <div class="card-body" id="resultBody"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Manejar clics en botones de prueba
        document.querySelectorAll('.test-url').forEach(button => {
            button.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                document.getElementById('mediaUrl').value = url;
                document.getElementById('testForm').scrollIntoView({ behavior: 'smooth' });
            });
        });
        
        // Manejar envío del formulario
        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const resultDiv = document.getElementById('testResult');
            const resultHeader = document.getElementById('resultHeader');
            const resultBody = document.getElementById('resultBody');
            
            resultDiv.style.display = 'block';
            resultHeader.textContent = 'Enviando...';
            resultHeader.className = 'card-header bg-info text-white';
            resultBody.innerHTML = '<p>Procesando solicitud, por favor espere...</p>';
            
            // Recopilar datos del formulario
            const telefono = document.getElementById('telefono').value;
            const mediaUrl = document.getElementById('mediaUrl').value;
            const mediaCaption = document.getElementById('mediaCaption').value;
            
            // Enviar solicitud a la API
            fetch('ajax/send_pdf_test.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    telefono: telefono,
                    mediaUrl: mediaUrl,
                    mediaCaption: mediaCaption
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultHeader.textContent = '¡Éxito!';
                    resultHeader.className = 'card-header bg-success text-white';
                    resultBody.innerHTML = `
                        <p>El PDF se envió correctamente.</p>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                } else {
                    resultHeader.textContent = 'Error';
                    resultHeader.className = 'card-header bg-danger text-white';
                    resultBody.innerHTML = `
                        <p><strong>Error:</strong> ${data.error || 'Error desconocido'}</p>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    `;
                }
            })
            .catch(error => {
                resultHeader.textContent = 'Error';
                resultHeader.className = 'card-header bg-danger text-white';
                resultBody.innerHTML = `<p>Error de conexión: ${error.message}</p>`;
            });
        });
    </script>
</body>
</html>
