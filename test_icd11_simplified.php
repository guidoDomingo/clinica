<?php
// Simple test for ICD-11 API
define('TESTING_MODE', true);

require_once __DIR__ . '/api/core/Response.php';
require_once __DIR__ . '/api/services/Icd11Service.php';
require_once __DIR__ . '/api/controllers/ICD11Controller.php';

// Test with different codes
$testCodes = ['MD12', 'BA00', '5A11', 'XN678', 'UNKNOWN_CODE'];

foreach ($testCodes as $code) {
    echo "==============================\n";
    echo "TESTING CODE: $code\n";
    echo "==============================\n";
    
    try {
        $controller = new Api\Controllers\ICD11Controller();
        $response = $controller->getDetailedDiseaseByCode($code);
        
        // Response will be handled by the Response class, but we'll add some info here
        echo "Test completed successfully\n\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n\n";
    }
}
