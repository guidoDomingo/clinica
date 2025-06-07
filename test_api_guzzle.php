<?php
/**
 * Script para probar la API de WhatsApp directamente con Guzzle
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

// Verificar si se pasó el parámetro mode=cli
$isCli = (isset($_GET['mode']) && $_GET['mode'] === 'cli') || (PHP_SAPI === 'cli');

if ($isCli) {
    // Modo CLI - sin HTML
    echo "=== TEST API WHATSAPP DIRECTO (GUZZLE) ===\n\n";
} else {
    // Modo web - con HTML
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Prueba Directa de API WhatsApp con Guzzle</title>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            h1 { color: #0066cc; }
            .success { color: green; }
            .error { color: red; }
            .warning { color: orange; }
            .step { margin-bottom: 20px; border-left: 5px solid #ccc; padding-left: 15px; }
            pre { background: #f5f5f5; padding: 10px; overflow: auto; }
            .response { margin-top: 20px; }
        </style>
    </head>
    <body>
        <h1>Prueba Directa de API WhatsApp con Guzzle</h1>\n";
}

// Verificar disponibilidad de Guzzle
if (!class_exists('GuzzleHttp\Client')) {
    outputText("Error: Guzzle no está disponible. Asegúrese de que composer está instalado correctamente.", 'error');
    outputEnd();
    exit;
}

// Obtener los datos (desde CLI o web)
if (PHP_SAPI === 'cli' && $argc > 2) {
    $telefono = $argv[1];
    $mediaUrl = $argv[2];
    $mediaCaption = isset($argv[3]) ? $argv[3] : "PDF de prueba";
} else {
    $telefono = $_GET['telefono'] ?? '595982313358';
    $mediaUrl = $_GET['url'] ?? 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
    $mediaCaption = $_GET['caption'] ?? 'PDF de prueba';
}

// Mostrar información de la prueba
outputText("Información de la prueba:", 'step');
outputText("- Teléfono: $telefono");
outputText("- URL del PDF: $mediaUrl");
outputText("- Caption: $mediaCaption");

// Verificar URL con HEAD primero
outputText("\nVerificando URL del PDF con HEAD...", 'step');
try {
    $client = new Client([
        'timeout' => 10,
        'verify' => false
    ]);
    
    $response = $client->request('HEAD', $mediaUrl);
    $statusCode = $response->getStatusCode();
    $contentType = $response->getHeaderLine('Content-Type');
    $contentLength = $response->getHeaderLine('Content-Length');
    
    outputText("✅ URL accesible (Status: $statusCode)", 'success');
    outputText("- Content-Type: $contentType");
    outputText("- Content-Length: " . ($contentLength ? $contentLength . ' bytes' : 'desconocido'));
    
    if (strpos($contentType, 'pdf') === false) {
        outputText("⚠️ Advertencia: El Content-Type no indica que es un PDF", 'warning');
    }
} catch (RequestException $e) {
    outputText("❌ Error al verificar la URL: " . $e->getMessage(), 'error');
    outputText("(Continuamos de todos modos ya que algunos servidores no soportan HEAD correctamente)");
}

// Configuración de la API
outputText("\nEnviando PDF a la API de WhatsApp...", 'step');
$apiUrl = 'http://aventisdev.com:8082/media.php';
$username = 'admin';
$password = '1234';

// Datos para enviar
$postData = [
    'telefono' => $telefono,
    'mediaUrl' => $mediaUrl,
    'mediaCaption' => $mediaCaption
];

outputText("- API URL: $apiUrl");
outputText("- Datos a enviar: " . json_encode($postData));

try {
    // Cliente HTTP
    $client = new Client([
        'timeout' => 30,
        'verify' => false
    ]);

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
    
    outputText("\nRespuesta de la API:", 'step');
    outputText("- Status Code: $statusCode");
    outputText("- Body: $responseBody");
    
    // Intentar decodificar JSON
    $data = json_decode($responseBody, true);
    if ($data) {
        if (isset($data['status']) && $data['status'] === 'ok') {
            outputText("\n✅ ÉXITO: El PDF se envió correctamente", 'success');
        } else {
            outputText("\n❌ ERROR: La API devolvió un error", 'error');
            outputText("- Detalles: " . json_encode($data, JSON_PRETTY_PRINT));
        }
    } else {
        outputText("\n❌ ERROR: La respuesta no es un JSON válido", 'error');
    }
} catch (RequestException $e) {
    outputText("\nError en la solicitud:", 'error');
    outputText("- " . $e->getMessage());
    
    if ($e->hasResponse()) {
        $errorResponse = $e->getResponse()->getBody()->getContents();
        outputText("\nRespuesta de error:");
        outputText("$errorResponse", 'response');
        
        $errorData = json_decode($errorResponse, true);
        if ($errorData) {
            outputText("\nDetalles del error:");
            outputText(json_encode($errorData, JSON_PRETTY_PRINT), 'response');
        }
    }
} catch (Exception $e) {
    outputText("\nError general:", 'error');
    outputText("- " . $e->getMessage());
}

// Finalizar la salida
outputEnd();

// Funciones auxiliares para la salida
function outputText($text, $class = '') {
    global $isCli;
    
    if ($isCli) {
        echo $text . "\n";
    } else {
        if ($class === 'step') {
            echo "<div class='step'><h3>$text</h3>";
        } else if ($class === 'response') {
            echo "<pre>$text</pre>";
        } else if (!empty($class)) {
            echo "<p class='$class'>$text</p>";
        } else {
            echo "<p>$text</p>";
        }
        
        if ($class === 'step') {
            // No cerrar el div aún, se cerrará en el siguiente paso
        }
    }
}

function outputEnd() {
    global $isCli;
    
    if ($isCli) {
        echo "\n=== FIN DE PRUEBA ===\n";
    } else {
        echo "</div>"; // Cerrar el último div.step
        echo "<p><a href='?'>Reiniciar prueba</a> | <a href='test_pdf_urls.php'>Probar otras URLs</a></p>";
        echo "</body></html>";
    }
}
