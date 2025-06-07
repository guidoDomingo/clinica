<?php
/**
 * Script para verificar qué cliente HTTP está disponible en el servidor
 * 
 * Este archivo devuelve información sobre qué cliente HTTP está disponible para usar:
 * - cURL
 * - Guzzle
 * - Ambos
 * - Ninguno
 */

// Incluir el autoloader de Composer para verificar Guzzle
require_once __DIR__ . '/vendor/autoload.php';

// Configurar cabeceras para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Verificar disponibilidad de cURL
$curlAvailable = function_exists('curl_version');
$curlVersion = $curlAvailable ? curl_version()['version'] : 'No disponible';

// Verificar disponibilidad de Guzzle
$guzzleAvailable = class_exists('GuzzleHttp\Client');
$guzzleVersion = 'No disponible';

// Obtener versión de Guzzle si está disponible
if ($guzzleAvailable) {
    // Intentar obtener la versión de Guzzle
    try {
        $reflector = new ReflectionClass('GuzzleHttp\Client');
        $guzzleComposerJson = dirname(dirname($reflector->getFileName())) . '/composer.json';
        
        if (file_exists($guzzleComposerJson)) {
            $composerData = json_decode(file_get_contents($guzzleComposerJson), true);
            $guzzleVersion = $composerData['version'] ?? '7.x';
        } else {
            $guzzleVersion = 'Instalado (versión desconocida)';
        }
    } catch (Exception $e) {
        $guzzleVersion = 'Error al determinar versión';
    }
}

// Determinar qué cliente utilizar
$clientToUse = 'Ninguno disponible';

if ($curlAvailable) {
    $clientToUse = 'cURL';
} elseif ($guzzleAvailable) {
    $clientToUse = 'Guzzle';
}

if ($curlAvailable && $guzzleAvailable) {
    $clientToUse = 'cURL (principal) con Guzzle como alternativa';
}

// Preparar respuesta
$response = [
    'curl' => [
        'available' => $curlAvailable,
        'version' => $curlVersion
    ],
    'guzzle' => [
        'available' => $guzzleAvailable,
        'version' => $guzzleVersion
    ],
    'client' => $clientToUse,
    'server_info' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'
    ]
];

// Devolver respuesta
echo json_encode($response, JSON_PRETTY_PRINT);
?>
