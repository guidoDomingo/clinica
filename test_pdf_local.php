<?php
/**
 * Script para probar la subida de PDF a un servidor y luego enviarlo por WhatsApp
 * 
 * Este enfoque resuelve problemas de accesibilidad al PDF al cargarlo primero al servidor
 * y luego generando una URL pública accesible para la API de WhatsApp
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

// Crear directorio para PDFs temporales si no existe
$uploadDir = __DIR__ . '/pdf_temp/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Función para generar URL pública para un archivo
function getPublicUrl($path) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . '://' . $host;
    
    // Convertir la ruta del archivo a una URL relativa a la raíz web
    $relativePath = str_replace(__DIR__, '', $path);
    $relativePath = str_replace('\\', '/', $relativePath);
    if (strpos($relativePath, '/') !== 0) {
        $relativePath = '/' . $relativePath;
    }
    
    return $baseUrl . $relativePath;
}

// Procesar el formulario si se envió
$uploadMessage = '';
$uploadedUrl = '';
$testResult = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si se está subiendo un archivo
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $tempFile = $_FILES['pdf_file']['tmp_name'];
        $fileName = 'test_' . time() . '_' . $_FILES['pdf_file']['name'];
        $targetFile = $uploadDir . $fileName;
        
        // Intentar mover el archivo subido
        if (move_uploaded_file($tempFile, $targetFile)) {
            $uploadedUrl = getPublicUrl($targetFile);
            $uploadMessage = "✅ PDF subido correctamente y disponible en: <a href='$uploadedUrl' target='_blank'>$uploadedUrl</a>";
        } else {
            $uploadMessage = "❌ Error al subir el PDF. Verifique permisos.";
        }
    }
    
    // Si se solicitó enviar el PDF por WhatsApp
    if (isset($_POST['send_whatsapp']) && !empty($_POST['telefono']) && !empty($_POST['mediaUrl'])) {
        $telefono = $_POST['telefono'];
        $mediaUrl = $_POST['mediaUrl'];
        $mediaCaption = $_POST['mediaCaption'] ?? 'PDF de prueba';
        
        // Configurar cliente Guzzle
        $client = new Client([
            'timeout' => 30,
            'verify' => false
        ]);
        
        // Datos para enviar
        $postData = [
            'telefono' => $telefono,
            'mediaUrl' => $mediaUrl,
            'mediaCaption' => $mediaCaption
        ];
        
        // Configuración de la API
        $apiUrl = 'http://aventisdev.com:8082/media.php';
        $username = 'admin';
        $password = '1234';
        
        try {
            // Ejecutar la solicitud
            $response = $client->request('POST', $apiUrl, [
                'auth' => [$username, $password],
                'form_params' => $postData,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            
            // Intentar decodificar JSON
            $data = json_decode($responseBody, true);
            
            $testResult = [
                'success' => ($statusCode === 200 && isset($data['status']) && $data['status'] === 'ok'),
                'status_code' => $statusCode,
                'response' => $data ?? $responseBody,
                'raw_response' => $responseBody
            ];
        } catch (RequestException $e) {
            $errorResponse = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'Sin respuesta';
            $errorData = json_decode($errorResponse, true);
            
            $testResult = [
                'success' => false,
                'error' => $e->getMessage(),
                'response' => $errorData ?? $errorResponse,
                'raw_response' => $errorResponse
            ];
        } catch (Exception $e) {
            $testResult = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

// HTML para la página
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir y Enviar PDF</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .card { margin-bottom: 20px; }
        .alert-message { margin-top: 15px; }
        #testResult { margin-top: 20px; }
        .info-box { background-color: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Prueba de PDF local para WhatsApp</h1>
        
        <div class="alert alert-info">
            <p><strong>¿Qué hace esta herramienta?</strong></p>
            <p>Sube un archivo PDF a este servidor y luego envía la URL pública del PDF a la API de WhatsApp.
            Esto ayuda a resolver problemas donde la API no puede acceder a URLs externas o privadas.</p>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        1. Subir archivo PDF
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="pdf_file" class="form-label">Seleccione un archivo PDF:</label>
                                <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept=".pdf">
                            </div>
                            <button type="submit" class="btn btn-primary">Subir PDF</button>
                        </form>
                        
                        <?php if (!empty($uploadMessage)): ?>
                            <div class="alert <?php echo strpos($uploadMessage, '❌') === 0 ? 'alert-danger' : 'alert-success'; ?> alert-message">
                                <?php echo $uploadMessage; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        2. Enviar PDF por WhatsApp
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono (formato internacional sin +):</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" value="595982313358" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mediaUrl" class="form-label">URL del PDF:</label>
                                <input type="text" class="form-control" id="mediaUrl" name="mediaUrl" value="<?php echo htmlspecialchars($uploadedUrl); ?>" required>
                                <div class="form-text">Use la URL del PDF subido o ingrese otra URL pública</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mediaCaption" class="form-label">Descripción:</label>
                                <input type="text" class="form-control" id="mediaCaption" name="mediaCaption" value="PDF de prueba">
                            </div>
                            
                            <input type="hidden" name="send_whatsapp" value="1">
                            <button type="submit" class="btn btn-success">Enviar PDF por WhatsApp</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($testResult)): ?>
            <div id="testResult" class="card">
                <div class="card-header <?php echo $testResult['success'] ? 'bg-success' : 'bg-danger'; ?> text-white">
                    Resultado de la prueba: <?php echo $testResult['success'] ? 'Éxito' : 'Error'; ?>
                </div>
                <div class="card-body">
                    <?php if ($testResult['success']): ?>
                        <p class="text-success">✅ El PDF se ha enviado correctamente.</p>
                    <?php else: ?>
                        <p class="text-danger">❌ Error: <?php echo htmlspecialchars($testResult['error'] ?? 'Error desconocido'); ?></p>
                    <?php endif; ?>
                    
                    <div class="info-box">
                        <h5>Detalles de la respuesta:</h5>
                        <pre><?php echo htmlspecialchars(json_encode($testResult, JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="test_pdf_urls.php" class="btn btn-outline-primary">Probar otras URLs de PDF</a>
            <a href="test_api_guzzle.php" class="btn btn-outline-secondary">Diagnóstico API Directo</a>
        </div>
    </div>
</body>
</html>
