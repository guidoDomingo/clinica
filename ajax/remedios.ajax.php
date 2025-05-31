<?php
/**
 * Archivo para procesar las peticiones AJAX relacionadas con la búsqueda de remedios
 */

require_once '../vendor/autoload.php'; // Cargar autoloader de Composer
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Configurar cabeceras para JSON
header('Content-Type: application/json');

// Verificar que se haya enviado un término de búsqueda
if (!isset($_GET['query']) || empty($_GET['query'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Se requiere un término de búsqueda'
    ]);
    exit;
}

$query = $_GET['query'];

// Crear cliente HTTP
$client = new Client();

try {
    // Realizar la petición a la API externa con autenticación básica
    $response = $client->request('GET', 'http://aventisdev.com:8081/buscar/' . urlencode($query), [
        'auth' => ['admin', '1234']
    ]);

    // Obtener el cuerpo de la respuesta
    $body = $response->getBody()->getContents();
    
    // Parsear la respuesta JSON
    $data = json_decode($body, true);
    
    // Enviar la respuesta al cliente
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
} catch (RequestException $e) {
    // Manejar errores de la petición
    if ($e->hasResponse()) {
        $statusCode = $e->getResponse()->getStatusCode();
        echo json_encode([
            'status' => 'error',
            'message' => 'Error en la API: ' . $statusCode,
            'details' => $e->getMessage()
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error de conexión con la API',
            'details' => $e->getMessage()
        ]);
    }
}
?>
