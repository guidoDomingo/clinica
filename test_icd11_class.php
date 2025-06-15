<?php
/**
 * Prueba directa de la clase ICD11Ajax
 * 
 * Este script importa y prueba directamente la clase ICD11Ajax
 * para verificar si el problema está en la clase o en cómo se utiliza.
 */

// Configuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

// Verificar que el archivo existe antes de incluirlo
$icd11AjaxFile = __DIR__ . '/ajax/icd11.ajax.php';
if (!file_exists($icd11AjaxFile)) {
    die("Error: No se encontró el archivo icd11.ajax.php");
}

// Función para probar la detección de cURL
function testCurlDetection() {
    $result = function_exists('curl_init');
    echo "<p>Verificación de función curl_init(): " . 
        ($result ? '<span style="color:green">DISPONIBLE</span>' : '<span style="color:red">NO DISPONIBLE</span>') . "</p>";
         
    if ($result) {
        // Verificar si realmente podemos usarlo
        try {
            $ch = curl_init('https://example.com');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                echo "<p style='color:red'>Error al usar cURL: $error</p>";
            } else {
                echo "<p style='color:green'>cURL funciona correctamente</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color:red'>Excepción al usar cURL: " . $e->getMessage() . "</p>";
        }
    }
    
    return $result;
}

// Función para crear una clase de prueba que extienda ICD11Ajax sin la verificación inicial de cURL
class TestICD11Ajax {
    private $clientId = '97bc4e27-44a4-4a37-9e56-b65708f709a5_874d810b-8f96-4c9e-9c13-e66a78e8051f';
    private $clientSecret = '0EPvwLIAEFBdQgnaxbJAT2IaoPu4V9kvkATe9JlbCo4=';
    private $tokenUrl = 'https://icdaccessmanagement.who.int/connect/token';
    private $apiBaseUrl = 'https://id.who.int/icd/release/11/2022-02';
    private $accessToken = null;
    private $tokenExpiry = 0;
    
    public function testSearch($code) {
        try {
            $token = $this->getAccessToken();
            
            // Construir URL para buscar por código
            $url = $this->apiBaseUrl . '/mms/lookup?q=' . urlencode($code);
            
            // Realizar la solicitud HTTP
            $response = $this->httpRequest($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Accept-Language' => 'es, en'
                ]
            ]);
            
            return [
                'success' => true,
                'response' => $response,
                'data' => json_decode($response['body'], true)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    private function getAccessToken() {
        // Verificar si ya tenemos un token válido
        if ($this->accessToken && time() < $this->tokenExpiry) {
            return $this->accessToken;
        }
        
        try {
            // Realizar solicitud HTTP para obtener token
            $response = $this->httpRequest($this->tokenUrl, [
                'method' => 'POST',
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'data' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'icdapi_access',
                    'grant_type' => 'client_credentials'
                ]
            ]);
            
            // Verificar respuesta
            if ($response['status'] != 200) {
                throw new Exception('Error al obtener token de acceso. Código HTTP: ' . $response['status']);
            }
            
            // Decodificar respuesta
            $data = json_decode($response['body'], true);
            if (!isset($data['access_token'])) {
                throw new Exception('Respuesta de token no válida');
            }
            
            // Almacenar token y tiempo de expiración
            $this->accessToken = $data['access_token'];
            $this->tokenExpiry = time() + ($data['expires_in'] ?? 3600) - 60;
            
            return $this->accessToken;
        } catch (Exception $e) {
            throw new Exception('Error al obtener token de acceso: ' . $e->getMessage());
        }
    }
    
    private function httpRequest($url, $options = []) {
        $method = $options['method'] ?? 'GET';
        $headers = $options['headers'] ?? [];
        $data = $options['data'] ?? null;
        
        // Intentar usar cURL primero
        if (function_exists('curl_init')) {
            try {
                // Configurar cURL
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                // Configurar método
                if ($method === 'POST') {
                    curl_setopt($ch, CURLOPT_POST, true);
                    if ($data) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
                    }
                }
                
                // Agregar encabezados
                if (!empty($headers)) {
                    $headersList = [];
                    foreach ($headers as $key => $value) {
                        $headersList[] = "$key: $value";
                    }
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headersList);
                }
                
                // Ejecutar la solicitud
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                // Verificar si hubo error
                if ($error) {
                    throw new Exception("Error cURL: $error");
                }
                
                return [
                    'body' => $response,
                    'status' => $httpCode,
                    'method_used' => 'curl'
                ];
            } catch (Exception $curlError) {
                // Si falla cURL, intentamos con file_get_contents
                echo "<p style='color:orange'>Error con cURL: " . $curlError->getMessage() . ". Intentando alternativa...</p>";
            }
        } else {
            echo "<p style='color:orange'>cURL no está disponible. Intentando alternativa...</p>";
        }
        
        // Alternativa: usar file_get_contents con stream context
        if (function_exists('file_get_contents') && function_exists('stream_context_create')) {
            try {
                // Configurar el contexto
                $context = [];
                
                if ($method === 'POST') {
                    $context['http'] = [
                        'method' => 'POST',
                        'header' => 'Content-Type: application/x-www-form-urlencoded',
                        'content' => is_array($data) ? http_build_query($data) : $data
                    ];
                } else {
                    $context['http'] = [
                        'method' => 'GET'
                    ];
                }
                
                // Agregar encabezados personalizados
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
                    throw new Exception("Error con file_get_contents: " . ($error['message'] ?? 'Desconocido'));
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
                    'status' => $status,
                    'method_used' => 'file_get_contents'
                ];
            } catch (Exception $fileError) {
                echo "<p style='color:red'>Error con file_get_contents: " . $fileError->getMessage() . "</p>";
                throw $fileError; 
            }
        }
        
        // Si llegamos aquí, ningún método funcionó
        throw new Exception('No se pudo realizar la solicitud HTTP: ni cURL ni file_get_contents funcionan correctamente');
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba directa de ICD11Ajax</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        h1, h2 {
            color: #2c3e50;
            border-bottom: 1px solid #3498db;
            padding-bottom: 10px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .panel {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .panel-heading {
            background-color: #f8f9fa;
            padding: 10px;
            margin: -15px -15px 15px -15px;
            border-bottom: 1px solid #ddd;
            border-radius: 5px 5px 0 0;
            font-weight: bold;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .warning {
            color: orange;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            overflow: auto;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Prueba directa de ICD11Ajax</h1>
        
        <p>Este script prueba directamente la funcionalidad de ICD11Ajax sin pasar por el flujo normal de la aplicación.</p>
        
        <div class="panel">
            <div class="panel-heading">1. Verificación de cURL</div>
            <?php $curlAvailable = testCurlDetection(); ?>
        </div>
        
        <div class="panel">
            <div class="panel-heading">2. Prueba directa de API</div>
            <?php
            try {
                $tester = new TestICD11Ajax();
                $result = $tester->testSearch('MD12');
                
                if ($result['success']) {
                    echo "<p class='success'>La prueba directa fue exitosa</p>";
                    
                    // Mostrar datos obtenidos
                    if (isset($result['data']['destinationEntities'])) {
                        echo "<p>Datos obtenidos:</p>";
                        echo "<pre>";
                        foreach ($result['data']['destinationEntities'] as $entity) {
                            echo "Código: " . ($entity['theCode'] ?? 'No disponible') . "\n";
                            echo "Título: " . ($entity['title'] ?? 'No disponible') . "\n\n";
                        }
                        echo "</pre>";
                    }
                    
                    echo "<p>Método HTTP utilizado: <strong>" . 
                        ($result['response']['method_used'] ?? 'Desconocido') . "</strong></p>";
                } else {
                    echo "<p class='error'>La prueba directa falló: " . $result['message'] . "</p>";
                }
            } catch (Exception $e) {
                echo "<p class='error'>Excepción en la prueba directa: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>
        
        <div class="panel">
            <div class="panel-heading">3. Conclusión</div>
            <?php
            if ($curlAvailable) {
                echo "<p>cURL está disponible en este servidor, pero podría haber un problema con cómo se detecta o utiliza en la clase ICD11Ajax.</p>";
            } else {
                echo "<p class='warning'>cURL NO está disponible en este servidor. Se debe usar una implementación alternativa.</p>";
            }
            
            if (isset($result) && $result['success']) {
                echo "<p class='success'>La API de ICD-11 funciona correctamente con nuestra implementación de prueba.</p>";
            } else {
                echo "<p class='error'>Hay problemas para conectarse a la API de ICD-11. Verifique la configuración del servidor y los requisitos.</p>";
            }
            ?>
            
            <p>Recomendaciones:</p>
            <ul>
                <li>Actualice el código en <code>icd11.ajax.php</code> para usar la implementación de <code>httpRequest</code> de esta prueba</li>
                <li>Elimine o comente el bloque de verificación de cURL al inicio del archivo</li>
                <li>Asegúrese de que <code>allow_url_fopen</code> esté habilitado como alternativa</li>
            </ul>
        </div>
    </div>
</body>
</html>
