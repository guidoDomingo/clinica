<?php
/**
 * Script de prueba simplificado para enviar un PDF por WhatsApp usando Guzzle
 */

// Incluir el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Importar las clases de Guzzle
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7;

// Mostrar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar cabeceras para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

try {
    // Obtener datos de la solicitud
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);

    // Verificar si hay datos
    if ($data === null) {
        throw new Exception("Error al decodificar los datos JSON: " . json_last_error_msg());
    }

    // Verificar datos requeridos
    if (empty($data['telefono']) || empty($data['mediaUrl'])) {
        throw new Exception("Faltan datos requeridos: teléfono y mediaUrl son obligatorios");
    }
    
    // Registrar información completa de la solicitud
    error_log('--- NUEVO INTENTO DE ENVÍO DE PDF ---');
    error_log('Teléfono: ' . $data['telefono']);
    error_log('URL del PDF: ' . $data['mediaUrl']);
    error_log('Caption: ' . ($data['mediaCaption'] ?? 'No especificado'));
    
    // Verificar si la URL es accesible mediante una solicitud HEAD
    try {
        $client = new Client([
            'timeout' => 10,
            'verify' => false
        ]);
        
        $headResponse = $client->request('HEAD', $data['mediaUrl']);
        $headStatusCode = $headResponse->getStatusCode();
        $contentType = $headResponse->getHeaderLine('Content-Type');
        
        error_log('Verificación de URL - HEAD Status: ' . $headStatusCode);
        error_log('Verificación de URL - Content-Type: ' . $contentType);
        
        if ($headStatusCode != 200) {
            throw new Exception("La URL del PDF no responde correctamente. Código: " . $headStatusCode);
        }
        
        if (strpos($contentType, 'pdf') === false) {
            error_log('Advertencia: El Content-Type no indica que es un PDF: ' . $contentType);
        }
    } catch (RequestException $e) {
        error_log('Error al verificar la URL mediante HEAD: ' . $e->getMessage());
        // Continuamos aunque falle la verificación HEAD, ya que algunos servidores no lo soportan
    }    // Configuración de la API
    $apiUrl = 'http://aventisdev.com:8082/media.php';
    $username = 'admin';
    $password = '1234';

    // URLs alternativas de PDF que sabemos funcionan bien
    $workingPdfUrls = [
        'mozilla' => 'https://mozilla.github.io/pdf.js/web/compressed.tracemonkey-pldi-09.pdf',
        'w3' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf'
    ];
    
    // Determinar qué URL del PDF usar
    $originalUrl = $data['mediaUrl'];
    $mediaUrl = $originalUrl;
    
    // Si se ha especificado usar una URL alternativa
    if (isset($data['useAlternativePdf']) && $data['useAlternativePdf'] === true) {
        $mediaUrl = $workingPdfUrls['mozilla']; // Usar la URL de Mozilla por defecto
        error_log('Usando URL alternativa de PDF: ' . $mediaUrl);
    }
    
    // Si la URL original contiene "africau.edu" (que no funciona bien), sugerir alternativa
    if (strpos($originalUrl, 'africau.edu') !== false) {
        error_log('⚠️ ADVERTENCIA: La URL africau.edu ha presentado problemas con la API. Se recomienda usar una alternativa.');
    }

    // Datos a enviar
    $postData = [
        'telefono' => $data['telefono'],
        'mediaUrl' => $mediaUrl,
        'mediaCaption' => $data['mediaCaption'] ?? "Documento de la clínica"
    ];

    // Registrar los datos que se enviarán para depuración
    error_log('Enviando datos a API WhatsApp: ' . json_encode($postData));

    // Inicializar el cliente Guzzle
    $client = new Client([
        'base_uri' => $apiUrl,
        'timeout' => 30,
        'verify' => false // No verificar SSL en desarrollo
    ]);

    // Ejecutar la solicitud con Guzzle
    $response = $client->request('POST', '', [
        'auth' => [$username, $password],
        'form_params' => $postData,
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]
    ]);
      // Obtener información de la respuesta
    $httpCode = $response->getStatusCode();
    $responseBody = $response->getBody()->getContents();

    // Registrar respuesta para depuración
    error_log('Respuesta de API - Status Code: ' . $httpCode);
    error_log('Respuesta de API - Body: ' . $responseBody);

    // Verificar código de respuesta HTTP
    if ($httpCode >= 400) {
        throw new Exception("Error HTTP $httpCode al conectar con la API");
    }

    // Intentar decodificar la respuesta JSON
    $responseData = json_decode($responseBody, true);
    if ($responseData === null) {
        // Si no es JSON válido, devolver la respuesta cruda
        echo json_encode([
            'success' => false,
            'error' => 'Respuesta no válida de la API',
            'raw_response' => $responseBody,
            'http_code' => $httpCode
        ]);
    } else {
        // La respuesta es JSON válido - verificar si fue exitoso según la API
        if (isset($responseData['status']) && $responseData['status'] === 'ok') {
            echo json_encode([
                'success' => true,
                'message' => 'PDF enviado correctamente por WhatsApp',
                'data' => $responseData
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'La API devolvió un error',
                'response' => $responseData
            ]);
        }
    }
} catch (RequestException $e) {
    error_log('Error RequestException: ' . $e->getMessage());
    
    $errorResponse = null;
    $errorDetails = null;
    
    // Intentar extraer más información del error
    if ($e->hasResponse()) {
        $errorResponse = $e->getResponse()->getBody()->getContents();
        error_log('Cuerpo de respuesta del error: ' . $errorResponse);
        
        // Intentar decodificar JSON en la respuesta de error
        $errorData = json_decode($errorResponse, true);
        if ($errorData) {
            $errorDetails = $errorData;
        }
    }
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug_info' => [
            'exception' => get_class($e),
            'response' => $errorResponse,
            'details' => $errorDetails,
            'postData' => $postData
        ]
    ]);
} catch (ConnectException $e) {
    error_log('Error ConnectException: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión con la API: ' . $e->getMessage(),
        'debug_info' => [
            'exception' => get_class($e),
            'postData' => $postData
        ]
    ]);
} catch (Exception $e) {
    error_log('Error genérico: ' . $e->getMessage());
    
    // Capturar cualquier error y devolverlo como JSON
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
