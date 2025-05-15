<?php
// Test script for ICD-11 API
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define a simple mock Response class if it doesn't exist
class MockResponse {
    public static function json($data, $status = 200) {
        echo "HTTP STATUS: $status\n";
        echo "RESPONSE DATA:\n";
        print_r($data);
        return $data;
    }
}

// Only include the real Response if it exists, otherwise use our mock
if (file_exists(__DIR__ . '/api/core/Response.php')) {
    require_once __DIR__ . '/api/core/Response.php';
} else {
    class_alias('MockResponse', 'Api\Core\Response');
}

require_once __DIR__ . '/api/services/Icd11Service.php';

try {
    // Test direct service call first
    echo "TESTING SERVICE DIRECTLY:\n";
    echo "-----------------------\n";
    $service = new Api\Services\Icd11Service();
      // No more token method in simplified version
    echo "Using simplified service implementation\n";
    
    try {
        $diseaseData = $service->getDetailedDiseaseByCode('MD12');
        echo "Disease data retrieved successfully:\n";
        print_r($diseaseData);
    } catch (Exception $e) {
        echo "ERROR getting disease data: " . $e->getMessage() . "\n";
    }
    
    // Now test via controller
    echo "\n\nTESTING CONTROLLER:\n";
    echo "------------------\n";
    require_once __DIR__ . '/api/controllers/ICD11Controller.php';
    
    $controller = new Api\Controllers\ICD11Controller();
    $response = $controller->getDetailedDiseaseByCode('MD12');
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
