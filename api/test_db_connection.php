<?php
/**
 * Database Connection Test Script
 * 
 * This script tests the database connection and returns the result in JSON format
 * Ideal for testing with Postman
 */

// Set headers for API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include the database connection class
require_once __DIR__ . '/../model/conexion.php';

try {
    // Test connection using the Conexion class
    $connection = Conexion::conectar();
    
    // If we reach here, connection was successful
    $response = [
        'status' => 'success',
        'message' => 'Database connection successful',
        'connection_details' => [
            'host' => 'localhost',
            'port' => '5432',
            'database' => 'clinica',
            'user' => 'postgres'
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Test if we can query the database
    $stmt = $connection->query("SELECT current_database() as db_name, current_user as user_name");
    $dbInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dbInfo) {
        $response['database_info'] = $dbInfo;
    }
    
    // Return success response
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Return error response
    $response = [
        'status' => 'error',
        'message' => 'Database connection failed',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
}