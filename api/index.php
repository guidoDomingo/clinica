<?php
/**
 * API Router
 * 
 * This file serves as the main entry point for the API
 * It handles routing and dispatches requests to the appropriate controllers
 */

// Iniciar sesión para todas las solicitudes a la API
session_start();

// Set headers for API responses
header('Content-Type: application/json');
// Cambiar de * a la URL específica para permitir credenciales
header('Access-Control-Allow-Origin: http://clinica.test');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include necessary files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Response.php';

// Initialize the Router
$router = new \Api\Core\Router();

// Include routes
require_once __DIR__ . '/routes/api.php';

// Get the request URI and method
$requestUri = isset($_GET['route']) ? $_GET['route'] : '';
$requestMethod = $_SERVER['REQUEST_METHOD'];


// Process the request
try {
    $router->dispatch($requestUri, $requestMethod);
} catch (\Exception $e) {
    \Api\Core\Response::error([
        'message' => [$e->getMessage(),$requestUri, $requestMethod],
        'codes' => $e->getCode() ?: 500
    ], $e->getCode() ?: 500);
}