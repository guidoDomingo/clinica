<?php
// Test script for the ICD-11 API with URL parameter handling

// Set the error reporting to show all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define constants
define('API_ROOT', dirname(__FILE__) . '/api');

// Load required files
require_once API_ROOT . '/core/Response.php';
require_once API_ROOT . '/services/Icd11Service.php';
require_once API_ROOT . '/controllers/ICD11Controller.php';
require_once API_ROOT . '/core/Router.php';

// Create a test router
$router = new Api\Core\Router();

// Add the disease route
$router->get('disease/{code}', 'Api\Controllers\ICD11Controller', 'getDetailedDiseaseByCode');

// Test URLs
$testUrls = [
    'disease/MD12',
    'disease/BA00',
    'disease/XN678',
    'disease/5A11',
    'disease/UNKNOWN_CODE'
];

// Run tests
echo "<h1>ICD-11 API Router Test</h1>";
echo "<hr>";

foreach ($testUrls as $url) {
    echo "<h3>Testing URL: $url</h3>";
    try {
        // Simulate request
        $result = $router->dispatch($url, 'GET');
        echo "<pre>";
        var_dump($result);
        echo "</pre>";
        echo "<p style='color:green'>✓ Test passed!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Test failed: " . $e->getMessage() . "</p>";
    }
    echo "<hr>";
}

echo "<p>All tests completed.</p>";
