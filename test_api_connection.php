<?php
/**
 * Script de diagnóstico para probar la conexión a la API externa de envío de documentos
 */

// Configurar cabeceras para texto plano
header('Content-Type: text/plain');

echo "=== PRUEBA DE CONEXIÓN A API EXTERNA ===\n\n";

// Datos para la prueba
$apiUrl = 'http://aventisdev.com:8082/media.php';
$username = 'admin';
$password = '1234';
$telefono = '595982313358';
$mediaUrl = 'https://www.google.com/imgres?q=pdf%20de%20salud&imgurl=https%3A%2F%2Fimgv2-2-f.scribdassets.com%2Fimg%2Fdocument%2F71495665%2Foriginal%2F09236410d4%2F1%3Fv%3D1';
$mediaCaption = 'PRUEBA DE CONEXIÓN';

echo "URL de la API: $apiUrl\n";
echo "Usuario: $username\n";
echo "Teléfono de prueba: $telefono\n\n";

// Verificar que las funciones necesarias estén disponibles
echo "=== VERIFICACIÓN DE FUNCIONES ===\n";
$requiredFunctions = ['curl_init', 'file_get_contents', 'json_decode', 'stream_context_create'];
foreach ($requiredFunctions as $function) {
    echo "Función $function: " . (function_exists($function) ? "Disponible ✅" : "No disponible ❌") . "\n";
}

echo "\n=== VERIFICACIÓN DE CONFIGURACIÓN PHP ===\n";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? "Habilitado ✅" : "Deshabilitado ❌") . "\n";
echo "User Agent: " . ini_get('user_agent') . "\n";
echo "default_socket_timeout: " . ini_get('default_socket_timeout') . " segundos\n";

// Verificar conectividad básica
echo "\n=== PRUEBA DE PING AL SERVIDOR ===\n";
$host = parse_url($apiUrl, PHP_URL_HOST);
$port = parse_url($apiUrl, PHP_URL_PORT) ?: 80;

echo "Servidor: $host\n";
echo "Puerto: $port\n";

$pingStart = microtime(true);
$socket = @fsockopen($host, $port, $errno, $errstr, 5);
$pingEnd = microtime(true);

if (!$socket) {
    echo "Error de conexión: $errstr ($errno) ❌\n";
} else {
    echo "Tiempo de respuesta: " . number_format(($pingEnd - $pingStart) * 1000, 2) . " ms ✅\n";
    fclose($socket);
}

// Intentar realizar una petición básica sin autenticación
echo "\n=== PRUEBA DE ACCESO BÁSICO ===\n";
$basicOptions = [
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: API Test Script\r\n",
        'timeout' => 5
    ]
];
$basicContext = stream_context_create($basicOptions);

echo "Realizando petición GET básica...\n";
$basicResult = @file_get_contents($apiUrl, false, $basicContext);

if ($basicResult === FALSE) {
    echo "Error en la petición básica ❌\n";
    if (isset($http_response_header)) {
        echo "Respuesta del servidor: " . $http_response_header[0] . "\n";
    }
} else {
    echo "Petición básica exitosa ✅\n";
    echo "Respuesta: " . substr($basicResult, 0, 100) . "...\n";
}

// Intentar realizar una petición con autenticación
echo "\n=== PRUEBA DE AUTENTICACIÓN ===\n";
$authHeader = base64_encode($username . ':' . $password);
$authOptions = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                    "Authorization: Basic " . $authHeader . "\r\n",
        'content' => http_build_query(['test' => 'true']),
        'timeout' => 5
    ]
];
$authContext = stream_context_create($authOptions);

echo "Realizando petición con autenticación...\n";
$authResult = @file_get_contents($apiUrl, false, $authContext);

if ($authResult === FALSE) {
    echo "Error en la petición con autenticación ❌\n";
    if (isset($http_response_header)) {
        echo "Respuesta del servidor: " . $http_response_header[0] . "\n";
    }
} else {
    echo "Petición con autenticación exitosa ✅\n";
    echo "Respuesta: " . substr($authResult, 0, 100) . "...\n";
}

// Intentar realizar una petición completa
echo "\n=== PRUEBA DE ENVÍO COMPLETO ===\n";
$postData = http_build_query([
    'telefono' => $telefono,
    'mediaUrl' => $mediaUrl,
    'mediaCaption' => $mediaCaption
]);

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
                    "Authorization: Basic " . $authHeader . "\r\n",
        'method'  => 'POST',
        'content' => $postData,
        'timeout' => 10
    ]
];

echo "Enviando documento de prueba...\n";
$context = stream_context_create($options);
$result = @file_get_contents($apiUrl, false, $context);

if ($result === FALSE) {
    echo "Error en el envío ❌\n";
    if (isset($http_response_header)) {
        echo "Respuesta del servidor: " . $http_response_header[0] . "\n";
    }
} else {
    echo "Envío exitoso ✅\n";
    echo "Respuesta completa:\n" . $result . "\n";
    
    // Intentar decodificar como JSON
    $response = json_decode($result, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "\nRespuesta decodificada:\n";
        print_r($response);
    }
}

echo "\n=== FIN DE LA PRUEBA DE DIAGNÓSTICO ===\n";
?>
