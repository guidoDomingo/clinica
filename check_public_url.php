<?php
/**
 * Utilidad para verificar si una URL es accesible públicamente
 * 
 * Este script permite comprobar si una URL es accesible desde Internet
 * utilizando servicios públicos.
 */

/**
 * Verifica si una URL es accesible públicamente
 * 
 * @param string $url La URL a verificar
 * @return array Resultado de la comprobación
 */
function checkPublicUrl($url) {
    if (empty($url)) {
        return [
            'accessible' => false,
            'error' => 'URL vacía'
        ];
    }
    
    // Intentar acceder directamente a la URL
    $localAccess = checkDirectAccess($url);
    
    // Log del resultado
    error_log("Verificación local de URL $url: " . ($localAccess ? 'ACCESIBLE' : 'NO ACCESIBLE'));
    
    // Devolver resultados
    return [
        'url' => $url,
        'accessible_locally' => $localAccess,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

/**
 * Intenta acceder directamente a una URL
 * 
 * @param string $url La URL a verificar
 * @return bool TRUE si es accesible, FALSE en caso contrario
 */
function checkDirectAccess($url) {
    try {
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'timeout' => 5,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $headers = @get_headers($url, 1, $context);
        
        if ($headers) {
            if (isset($headers[0])) {
                // Extraer código de respuesta
                preg_match('/HTTP\/\d\.\d\s+(\d+)/', $headers[0], $matches);
                $responseCode = isset($matches[1]) ? (int)$matches[1] : 0;
                
                // Códigos 2xx indican éxito
                return $responseCode >= 200 && $responseCode < 300;
            }
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error al verificar URL: " . $e->getMessage());
        return false;
    }
}

// Si se llama directamente al script, realizar la comprobación
if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    
    $url = isset($_GET['url']) ? $_GET['url'] : '';
    
    if (empty($url)) {
        echo json_encode([
            'error' => 'Parámetro URL vacío',
            'usage' => 'Añada ?url=https://ejemplo.com'
        ]);
        exit;
    }
    
    $result = checkPublicUrl($url);
    echo json_encode($result, JSON_PRETTY_PRINT);
}
?>
