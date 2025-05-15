<?php
// Test for ICD-11 API with description field in JSON format
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('TESTING_MODE', true);

require_once __DIR__ . '/api/core/Response.php';
require_once __DIR__ . '/api/services/Icd11Service.php';

header('Content-Type: application/json');

// Function to test a code and return results
function testCode($service, $code) {
    try {
        $result = $service->getDetailedDiseaseByCode($code);
        return [
            'success' => true,
            'code' => $code,
            'data' => $result
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'code' => $code,
            'error' => $e->getMessage()
        ];
    }
}

$service = new Api\Services\Icd11Service();

// Test all codes
$results = [
    'MD12' => testCode($service, 'MD12'),
    'BA00' => testCode($service, 'BA00'),
    '5A11' => testCode($service, '5A11'),
    'XN678' => testCode($service, 'XN678'),
    'UNKNOWN' => testCode($service, 'UNKNOWN123')
];

// Output JSON
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
