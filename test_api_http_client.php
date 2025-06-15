<?php
/**
 * Test de Verificación: Solo Respuestas API (usando HTTP Client)
 * 
 * Este script verifica que todas las respuestas del sistema ICD-11
 * provengan únicamente de la API oficial usando el cliente HTTP nativo de PHP.
 */

// Configuración y encabezados
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

echo "<html><head><title>Verificación: Solo respuestas de API (HTTP Client)</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h1 { color: #2c3e50; }
    .test { margin-bottom: 20px; padding: 15px; border-radius: 5px; }
    .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    .warning { background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
    .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    pre { background: #f8f9fa; padding: 10px; overflow: auto; border-radius: 4px; }
    .api-call { border-left: 4px solid #6c757d; padding-left: 15px; margin: 10px 0; }
    details { margin-top: 10px; }
    summary { cursor: pointer; font-weight: bold; }
</style>";
echo "</head><body>";

echo "<h1>Test de Verificación: Solo Respuestas de API ICD-11 (Cliente HTTP)</h1>";
echo "<p>Este script verifica que todas las respuestas del sistema ICD-11 provengan únicamente de la API oficial usando el cliente HTTP nativo de PHP.</p>";

// Función para realizar peticiones HTTP
function httpRequest($url, $data, $method = 'POST') {
    $context = [
        'http' => [
            'method' => $method,
            'header' => "Content-Type: application/json\r\n" .
                        "Accept: application/json\r\n" .
                        "Content-Length: " . strlen($data) . "\r\n",
            'content' => $data,
            'ignore_errors' => true
        ]
    ];
    
    $context = stream_context_create($context);
    $result = file_get_contents($url, false, $context);
    
    // Obtener los encabezados de respuesta
    $responseHeaders = $http_response_header ?? [];
    $statusCode = 0;
    
    // Extraer el código de estado
    foreach ($responseHeaders as $header) {
        if (strpos($header, 'HTTP/') !== false) {
            preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches);
            if (isset($matches[1])) {
                $statusCode = (int)$matches[1];
            }
        }
    }
    
    return [
        'body' => $result,
        'status' => $statusCode,
        'headers' => $responseHeaders
    ];
}

function runTest($title, $callback) {
    echo "<div class='test'>";
    echo "<h3>$title</h3>";
    
    try {
        $result = $callback();
        if ($result['success']) {
            echo "<div class='success'><strong>✓ ÉXITO:</strong> {$result['message']}</div>";
        } else {
            echo "<div class='error'><strong>✗ ERROR:</strong> {$result['message']}</div>";
        }
        
        if (!empty($result['details'])) {
            echo "<details><summary>Detalles</summary><pre>" . htmlspecialchars(print_r($result['details'], true)) . "</pre></details>";
        }
        
        if (!empty($result['api_call'])) {
            echo "<div class='api-call'><strong>Llamada a la API:</strong><pre>" . htmlspecialchars(print_r($result['api_call'], true)) . "</pre></div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'><strong>✗ EXCEPCIÓN:</strong> {$e->getMessage()}</div>";
        echo "<details><summary>Detalles del error</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
    }
    
    echo "</div>";
}

// Test 1: Verificar que icd11.ajax.php responde correctamente a una búsqueda por código
runTest("Test API principal: Búsqueda por código (HTTP Client)", function() {
    // Crear un objeto con los datos POST para simular una solicitud
    $data = json_encode([
        'action' => 'searchByCode',
        'code' => 'MD12' // Un código ICD conocido: tos
    ]);
    
    // Realizar la solicitud HTTP
    $response = httpRequest('http://localhost/clinica/ajax/icd11.ajax.php', $data);
    
    // Verificar que la respuesta sea válida
    $decodedResponse = json_decode($response['body'], true);
    
    if (!$decodedResponse) {
        return [
            'success' => false, 
            'message' => 'Respuesta no válida',
            'details' => $response['body'],
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    }
    
    // Verificar si la respuesta es correcta
    if ($decodedResponse['success']) {
        // Comprobar que NO tenga marca de fallback
        if (isset($decodedResponse['data']['fallback']) && $decodedResponse['data']['fallback'] === true) {
            return [
                'success' => false,
                'message' => 'La respuesta contiene datos de fallback, pero debería usar solo la API',
                'details' => $decodedResponse,
                'api_call' => [
                    'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                    'method' => 'POST',
                    'data' => $data,
                    'status_code' => $response['status']
                ]
            ];
        }
        
        return [
            'success' => true,
            'message' => 'La API principal responde correctamente solo con datos de la API',
            'details' => $decodedResponse,
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Error en la respuesta: ' . ($decodedResponse['message'] ?? 'Desconocido'),
            'details' => $decodedResponse,
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    }
});

// Test 2: Verificar que el servicio local (icd11_local.php) está desactivado
runTest("Test servicio local: Verificar desactivación (HTTP Client)", function() {
    // Crear datos POST para simular una solicitud
    $data = json_encode([
        'action' => 'searchByCode',
        'code' => 'MD12'
    ]);
    
    // Realizar la solicitud HTTP
    $response = httpRequest('http://localhost/clinica/ajax/icd11_local.php', $data);
    
    // Verificar que la respuesta sea válida
    $decodedResponse = json_decode($response['body'], true);
    
    if (!$decodedResponse) {
        return [
            'success' => false, 
            'message' => 'Respuesta no válida',
            'details' => $response['body'],
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11_local.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    }
    
    // Verificar que el servicio local esté correctamente desactivado
    if (isset($decodedResponse['api_required']) && $decodedResponse['api_required'] === true && 
        $decodedResponse['success'] === false) {
        return [
            'success' => true,
            'message' => 'El servicio local está correctamente desactivado y rechaza solicitudes',
            'details' => $decodedResponse,
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11_local.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    } else if (isset($decodedResponse['success']) && $decodedResponse['success'] === true) {
        return [
            'success' => false,
            'message' => '¡ATENCIÓN! El servicio local sigue proporcionando datos',
            'details' => $decodedResponse,
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11_local.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Respuesta inesperada del servicio local',
            'details' => $decodedResponse,
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11_local.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    }
});

// Test 3: Verificar que falla adecuadamente cuando se le da un código inexistente
runTest("Test API principal: Código inexistente (HTTP Client)", function() {
    // Crear un objeto con los datos POST para simular una solicitud
    $data = json_encode([
        'action' => 'searchByCode',
        'code' => 'NONEXISTENTCODE123' // Un código que seguramente no existe
    ]);
    
    // Realizar la solicitud HTTP
    $response = httpRequest('http://localhost/clinica/ajax/icd11.ajax.php', $data);
    
    // Verificar que la respuesta sea válida
    $decodedResponse = json_decode($response['body'], true);
    
    if (!$decodedResponse) {
        return [
            'success' => false, 
            'message' => 'Respuesta no válida',
            'details' => $response['body'],
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    }
    
    // Para un código inexistente, debería fallar adecuadamente sin usar fallback
    if ($decodedResponse['success'] === false) {
        // Verificar que el mensaje de error NO menciona fallback o datos locales
        $errorMessage = strtolower($decodedResponse['message'] ?? '');
        if (strpos($errorMessage, 'fallback') !== false || strpos($errorMessage, 'local') !== false) {
            return [
                'success' => false,
                'message' => 'El error menciona datos fallback o locales, pero no debería usarlos',
                'details' => $decodedResponse,
                'api_call' => [
                    'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                    'method' => 'POST',
                    'data' => $data,
                    'status_code' => $response['status']
                ]
            ];
        }
        
        return [
            'success' => true,
            'message' => 'La API falla adecuadamente con un código inexistente, sin usar datos locales',
            'details' => $decodedResponse,
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    } else {
        // Si tiene éxito, podría estar usando datos locales
        if (isset($decodedResponse['data']['fallback']) || isset($decodedResponse['data']['local_service'])) {
            return [
                'success' => false,
                'message' => 'La API devolvió datos locales para un código inexistente, no debería hacerlo',
                'details' => $decodedResponse,
                'api_call' => [
                    'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                    'method' => 'POST',
                    'data' => $data,
                    'status_code' => $response['status']
                ]
            ];
        }
        
        return [
            'success' => false,
            'message' => 'La API devolvió éxito para un código que no debería existir',
            'details' => $decodedResponse,
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    }
});

// Test 4: Búsqueda por término con la API principal
runTest("Test API principal: Búsqueda por término (HTTP Client)", function() {
    // Crear un objeto con los datos POST para simular una solicitud
    $data = json_encode([
        'action' => 'searchByTerm',
        'term' => 'diabetes', // Un término médico común
        'language' => 'es'
    ]);
    
    // Realizar la solicitud HTTP
    $response = httpRequest('http://localhost/clinica/ajax/icd11.ajax.php', $data);
    
    // Verificar que la respuesta sea válida
    $decodedResponse = json_decode($response['body'], true);
    
    if (!$decodedResponse) {
        return [
            'success' => false, 
            'message' => 'Respuesta no válida',
            'details' => $response['body'],
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    }
    
    // Verificar si la respuesta es correcta
    if ($decodedResponse['success']) {
        // Comprobar que NO tenga marca de fallback
        if (isset($decodedResponse['data']['fallback']) && $decodedResponse['data']['fallback'] === true) {
            return [
                'success' => false,
                'message' => 'La respuesta contiene datos de fallback, pero debería usar solo la API',
                'details' => $decodedResponse,
                'api_call' => [
                    'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                    'method' => 'POST',
                    'data' => $data,
                    'status_code' => $response['status']
                ]
            ];
        }
        
        return [
            'success' => true,
            'message' => 'La API principal responde correctamente a la búsqueda por término solo con datos de la API',
            'details' => $decodedResponse,
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Error en la respuesta de búsqueda por término: ' . ($decodedResponse['message'] ?? 'Desconocido'),
            'details' => $decodedResponse,
            'api_call' => [
                'url' => 'http://localhost/clinica/ajax/icd11.ajax.php',
                'method' => 'POST',
                'data' => $data,
                'status_code' => $response['status']
            ]
        ];
    }
});

echo "<h2>Conclusiones</h2>";
echo "<p>Estos tests verifican que:</p>";
echo "<ol>";
echo "<li>El endpoint principal (icd11.ajax.php) solo responde con datos de la API oficial</li>";
echo "<li>El servicio local (icd11_local.php) está correctamente desactivado</li>";
echo "<li>Ante códigos inexistentes, el sistema falla adecuadamente sin recurrir a datos locales</li>";
echo "<li>La búsqueda por términos también usa exclusivamente la API oficial</li>";
echo "</ol>";

echo "<div class='info' style='margin-top: 20px;'>";
echo "<strong>Nota sobre el cliente HTTP:</strong> Este test utiliza el cliente HTTP nativo de PHP (<code>file_get_contents</code> con un contexto de flujo) ";
echo "en lugar de cURL para realizar las solicitudes, lo que proporciona una forma alternativa de verificar el funcionamiento de la API.";
echo "</div>";

echo "</body></html>";
?>
