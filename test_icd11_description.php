<?php
// Test for ICD-11 API with description field
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('TESTING_MODE', true);

require_once __DIR__ . '/api/core/Response.php';
require_once __DIR__ . '/api/services/Icd11Service.php';

echo "<h1>ICD-11 Description Test</h1>";

$service = new Api\Services\Icd11Service();

// Test with MD12 (Tos)
echo "<h2>Testing MD12 (Tos)</h2>";
try {
    $result = $service->getDetailedDiseaseByCode('MD12');
    echo "<pre>";
    print_r($result);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test with BA00 (Hipertensión)
echo "<h2>Testing BA00 (Hipertensión)</h2>";
try {
    $result = $service->getDetailedDiseaseByCode('BA00');
    echo "<pre>";
    print_r($result);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test with 5A11 (Diabetes)
echo "<h2>Testing 5A11 (Diabetes)</h2>";
try {
    $result = $service->getDetailedDiseaseByCode('5A11');
    echo "<pre>";
    print_r($result);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test with XN678 (COVID-19)
echo "<h2>Testing XN678 (COVID-19)</h2>";
try {
    $result = $service->getDetailedDiseaseByCode('XN678');
    echo "<pre>";
    print_r($result);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Test with unknown code
echo "<h2>Testing Unknown Code</h2>";
try {
    $result = $service->getDetailedDiseaseByCode('UNKNOWN123');
    echo "<pre>";
    print_r($result);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
