<?php
/**
 * Test de Verificación: Solo Respuestas API
 * 
 * Este script verifica que todas las respuestas del sistema ICD-11
 * provengan únicamente de la API oficial y que las respuestas fallback/locales
 * estén correctamente desactivadas.
 */

// Configuración y encabezados
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

echo "<html><head><title>Verificación: Solo respuestas de API</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { color: #2c3e50; }
    .test { margin-bottom: 20px; padding: 15px; border-radius: 5px; }
    .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
    .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
    .warning { background-color: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
    .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    pre { background: #f8f9fa; padding: 10px; overflow: auto; }
</style>";
echo "</head><body>";

echo "<h1>Test de Verificación: Solo Respuestas de API ICD-11</h1>";
echo "<p>Este script verifica que todas las respuestas del sistema ICD-11 provengan únicamente de la API oficial.</p>";

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
    } catch (Exception $e) {
        echo "<div class='error'><strong>✗ EXCEPCIÓN:</strong> {$e->getMessage()}</div>";
        echo "<details><summary>Detalles del error</summary><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></details>";
    }
    
    echo "</div>";
}

// Test 1: Verificar que icd11.ajax.php responde correctamente a una búsqueda por código
runTest("Test API principal: Búsqueda por código", function() {
    // Crear un objeto con los datos POST para simular una solicitud
    $postData = json_encode([
        'action' => 'searchByCode',
        'code' => 'MD12' // Un código ICD conocido: tos
    ]);
    
    // Inicializar cURL
    $ch = curl_init('http://localhost/clinica/ajax/icd11.ajax.php');
    
    // Configurar la solicitud cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json', 
        'Content-Length: ' . strlen($postData)
    ]);
    
    // Ejecutar y obtener la respuesta
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Verificar que la respuesta sea válida
    $decodedResponse = json_decode($response, true);
    
    if (!$decodedResponse) {
        return [
            'success' => false, 
            'message' => 'Respuesta no válida',
            'details' => $response
        ];
    }
    
    // Verificar si la respuesta es correcta
    if ($decodedResponse['success']) {
        // Comprobar que NO tenga marca de fallback
        if (isset($decodedResponse['data']['fallback']) && $decodedResponse['data']['fallback'] === true) {
            return [
                'success' => false,
                'message' => 'La respuesta contiene datos de fallback, pero debería usar solo la API',
                'details' => $decodedResponse
            ];
        }
        
        return [
            'success' => true,
            'message' => 'La API principal responde correctamente solo con datos de la API',
            'details' => $decodedResponse
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Error en la respuesta: ' . ($decodedResponse['message'] ?? 'Desconocido'),
            'details' => $decodedResponse
        ];
    }
});

// Test 2: Verificar que el servicio local (icd11_local.php) está desactivado
runTest("Test servicio local: Verificar desactivación", function() {
    // Crear datos POST para simular una solicitud
    $postFields = http_build_query([
        'action' => 'searchByCode',
        'code' => 'MD12'
    ]);
    
    // Inicializar cURL
    $ch = curl_init('http://localhost/clinica/ajax/icd11_local.php');
    
    // Configurar la solicitud cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    
    // Ejecutar y obtener la respuesta
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Verificar que la respuesta sea válida
    $decodedResponse = json_decode($response, true);
    
    if (!$decodedResponse) {
        return [
            'success' => false, 
            'message' => 'Respuesta no válida',
            'details' => $response
        ];
    }
    
    // Verificar que el servicio local esté correctamente desactivado
    if (isset($decodedResponse['api_required']) && $decodedResponse['api_required'] === true && 
        $decodedResponse['success'] === false) {
        return [
            'success' => true,
            'message' => 'El servicio local está correctamente desactivado y rechaza solicitudes',
            'details' => $decodedResponse
        ];
    } else if (isset($decodedResponse['success']) && $decodedResponse['success'] === true) {
        return [
            'success' => false,
            'message' => '¡ATENCIÓN! El servicio local sigue proporcionando datos',
            'details' => $decodedResponse
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Respuesta inesperada del servicio local',
            'details' => $decodedResponse
        ];
    }
});

// Test 3: Verificar que falla adecuadamente cuando se le da un código inexistente
runTest("Test API principal: Código inexistente", function() {
    // Crear un objeto con los datos POST para simular una solicitud
    $postData = json_encode([
        'action' => 'searchByCode',
        'code' => 'NONEXISTENTCODE123' // Un código que seguramente no existe
    ]);
    
    // Inicializar cURL
    $ch = curl_init('http://localhost/clinica/ajax/icd11.ajax.php');
    
    // Configurar la solicitud cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json', 
        'Content-Length: ' . strlen($postData)
    ]);
    
    // Ejecutar y obtener la respuesta
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Verificar que la respuesta sea válida
    $decodedResponse = json_decode($response, true);
    
    if (!$decodedResponse) {
        return [
            'success' => false, 
            'message' => 'Respuesta no válida',
            'details' => $response
        ];
    }
    
    // Para un código inexistente, debería fallar adecuadamente sin usar fallback
    if ($decodedResponse['success'] === false) {
        // Verificar que el mensaje de error NO menciona fallback o datos locales
        $errorMessage = strtolower($decodedResponse['message']);
        if (strpos($errorMessage, 'fallback') !== false || strpos($errorMessage, 'local') !== false) {
            return [
                'success' => false,
                'message' => 'El error menciona datos fallback o locales, pero no debería usarlos',
                'details' => $decodedResponse
            ];
        }
        
        return [
            'success' => true,
            'message' => 'La API falla adecuadamente con un código inexistente, sin usar datos locales',
            'details' => $decodedResponse
        ];
    } else {
        // Si tiene éxito, podría estar usando datos locales
        if (isset($decodedResponse['data']['fallback']) || isset($decodedResponse['data']['local_service'])) {
            return [
                'success' => false,
                'message' => 'La API devolvió datos locales para un código inexistente, no debería hacerlo',
                'details' => $decodedResponse
            ];
        }
        
        return [
            'success' => false,
            'message' => 'La API devolvió éxito para un código que no debería existir',
            'details' => $decodedResponse
        ];
    }
});

echo "<h2>Conclusiones</h2>";
echo "<p>Estos tests verifican que:</p>";
echo "<ol>";
echo "<li>El endpoint principal (icd11.ajax.php) solo responde con datos de la API oficial</li>";
echo "<li>El servicio local (icd11_local.php) está correctamente desactivado</li>";
echo "<li>Ante códigos inexistentes, el sistema falla adecuadamente sin recurrir a datos locales</li>";
echo "</ol>";

echo "</body></html>";
?>
