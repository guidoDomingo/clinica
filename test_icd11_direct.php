<?php
/**
 * Test de la API ICD-11
 * 
 * Este script realiza pruebas directas a la API de ICD-11 para verificar
 * que la conexión funciona correctamente.
 */

// Configuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "== TEST DE CONEXIÓN A LA API DE ICD-11 ==\n\n";

// 1. Verificar dependencias básicas
echo "1. Verificando dependencias básicas...\n";

$curlAvailable = function_exists('curl_init');
echo "   - cURL disponible: " . ($curlAvailable ? "SÍ" : "NO") . "\n";

$allowUrlFopen = ini_get('allow_url_fopen');
echo "   - allow_url_fopen: " . ($allowUrlFopen ? "HABILITADO" : "DESHABILITADO") . "\n";

echo "   - Versión PHP: " . PHP_VERSION . "\n";
echo "   - SAPI: " . php_sapi_name() . "\n\n";

// 2. Intentar conectarse a la API sin usar el archivo icd11.ajax.php
echo "2. Intentando conexión directa a la API...\n";

// Credenciales (mismas que en icd11.ajax.php)
$clientId = '97bc4e27-44a4-4a37-9e56-b65708f709a5_874d810b-8f96-4c9e-9c13-e66a78e8051f';
$clientSecret = '0EPvwLIAEFBdQgnaxbJAT2IaoPu4V9kvkATe9JlbCo4=';
$tokenUrl = 'https://icdaccessmanagement.who.int/connect/token';
$apiBaseUrl = 'https://id.who.int/icd/release/11/2022-02';

// Función para realizar petición HTTP usando cURL o file_get_contents
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    global $curlAvailable;
    
    // Intentar primero con cURL si está disponible
    if ($curlAvailable) {
        try {
            echo "   - Usando cURL para la solicitud HTTP\n";
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }
            }
            
            if (!empty($headers)) {
                $headerList = [];
                foreach ($headers as $key => $value) {
                    $headerList[] = "$key: $value";
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headerList);
            }
            
            $response = curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                echo "   - Error cURL: $error\n";
                throw new Exception("Error cURL: $error");
            }
            
            return [
                'body' => $response,
                'status' => $status
            ];
        } catch (Exception $e) {
            echo "   - Excepción usando cURL: " . $e->getMessage() . "\n";
        }
    }
    
    // Alternativa: file_get_contents
    if (ini_get('allow_url_fopen')) {
        try {
            echo "   - Usando file_get_contents para la solicitud HTTP\n";
            
            $context = [];
            
            if ($method === 'POST') {
                $context['http'] = [
                    'method' => 'POST',
                    'content' => $data
                ];
            }
            
            if (!empty($headers)) {
                $headerStr = '';
                foreach ($headers as $key => $value) {
                    $headerStr .= "$key: $value\r\n";
                }
                $context['http']['header'] = $headerStr;
            }
            
            $streamContext = stream_context_create($context);
            $response = file_get_contents($url, false, $streamContext);
            
            if ($response === false) {
                $error = error_get_last();
                echo "   - Error file_get_contents: " . ($error['message'] ?? 'Desconocido') . "\n";
                throw new Exception("Error file_get_contents: " . ($error['message'] ?? 'Desconocido'));
            }
            
            // Obtener el código de estado
            $status = 200;
            foreach ($http_response_header ?? [] as $header) {
                if (preg_match('#HTTP/[0-9\.]+\s+([0-9]+)#', $header, $matches)) {
                    $status = intval($matches[1]);
                    break;
                }
            }
            
            return [
                'body' => $response,
                'status' => $status
            ];
        } catch (Exception $e) {
            echo "   - Excepción usando file_get_contents: " . $e->getMessage() . "\n";
        }
    }
    
    echo "   - No hay métodos HTTP disponibles para realizar la solicitud\n";
    throw new Exception("No hay métodos HTTP disponibles");
}

try {
    // 2.1 Obtener token
    echo "   - Solicitando token de acceso...\n";
    
    $tokenResponse = makeRequest(
        $tokenUrl, 
        'POST',
        http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => 'icdapi_access',
            'grant_type' => 'client_credentials'
        ]),
        ['Content-Type' => 'application/x-www-form-urlencoded']
    );
    
    if ($tokenResponse['status'] != 200) {
        echo "   - Error al obtener token. HTTP Code: " . $tokenResponse['status'] . "\n";
        echo "   - Respuesta: " . substr($tokenResponse['body'], 0, 200) . "\n";
        throw new Exception("Error al obtener token de acceso");
    }
    
    $tokenData = json_decode($tokenResponse['body'], true);
    if (!isset($tokenData['access_token'])) {
        throw new Exception("Respuesta de token inválida");
    }
    
    $accessToken = $tokenData['access_token'];
    echo "   - Token de acceso obtenido correctamente\n";
    
    // 2.2 Hacer una consulta simple a la API
    echo "\n   - Realizando consulta de prueba (código MD12)...\n";
    
    $searchResponse = makeRequest(
        $apiBaseUrl . '/mms/lookup?q=MD12',
        'GET', 
        null, 
        [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
            'Accept-Language' => 'es, en'
        ]
    );
    
    if ($searchResponse['status'] != 200) {
        echo "   - Error en consulta. HTTP Code: " . $searchResponse['status'] . "\n";
        echo "   - Respuesta: " . substr($searchResponse['body'], 0, 200) . "\n";
        throw new Exception("Error en consulta a la API");
    }
    
    $searchData = json_decode($searchResponse['body'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar respuesta JSON");
    }
    
    echo "   - Consulta exitosa. Datos recibidos:\n";
    
    if (isset($searchData['destinationEntities']) && !empty($searchData['destinationEntities'])) {
        foreach ($searchData['destinationEntities'] as $index => $entity) {
            echo "     * Entidad #" . ($index + 1) . ":\n";
            echo "       - Código: " . ($entity['theCode'] ?? 'No disponible') . "\n";
            echo "       - Título: " . ($entity['title'] ?? 'No disponible') . "\n";
        }
    } else {
        echo "   - No se encontraron entidades en la respuesta\n";
    }
    
    echo "\n3. Conclusión:\n";
    echo "   La conexión directa a la API fue EXITOSA.\n";
    echo "   Si el sistema no funciona correctamente, el problema está en el código de integración,\n";
    echo "   no en la disponibilidad de cURL o en la conexión a la API.\n";
    
} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "\n3. Conclusión:\n";
    echo "   La conexión directa a la API FALLÓ.\n";
    echo "   Revise las dependencias y la conexión a Internet.\n";
}

echo "\n== FIN DEL TEST ==\n";
