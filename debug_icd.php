<?php
/**
 * Debug script for ICD-11 API
 * This script tests direct access to the ICD-11 API without going through the AJAX handler
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// API credentials and URLs
$clientId = '97bc4e27-44a4-4a37-9e56-b65708f709a5_874d810b-8f96-4c9e-9c13-e66a78e8051f';
$clientSecret = '0EPvwLIAEFBdQgnaxbJAT2IaoPu4V9kvkATe9JlbCo4=';
$tokenUrl = 'https://icdaccessmanagement.who.int/connect/token';
$apiBaseUrl = 'https://id.who.int/icd/release/11/2022-02';

// Format output as HTML
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>ICD-11 API Debug</title><style>
body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.5; }
pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
.error { color: red; }
.success { color: green; }
.note { color: blue; }
</style></head><body>";

echo "<h1>ICD-11 API Debug Tool</h1>";

// Function to print formatted information
function printInfo($title, $content, $type = 'info') {
    echo "<h3>$title</h3>";
    if (is_array($content) || is_object($content)) {
        echo "<pre class='$type'>" . htmlspecialchars(print_r($content, true)) . "</pre>";
    } else {
        echo "<pre class='$type'>" . htmlspecialchars($content) . "</pre>";
    }
}

// Check HTTP request capabilities
echo "<h2>Environment Check</h2>";
$curlAvailable = function_exists('curl_init');
$fileGetContentsAvailable = function_exists('file_get_contents') && ini_get('allow_url_fopen');
$httpsAvailable = in_array('https', stream_get_wrappers());
$openSslLoaded = extension_loaded('openssl');

printInfo("HTTP Request Methods", [
    'curl_available' => $curlAvailable ? 'Yes' : 'No',
    'file_get_contents_available' => $fileGetContentsAvailable ? 'Yes' : 'No',
    'https_wrapper_available' => $httpsAvailable ? 'Yes' : 'No',
    'openssl_extension' => $openSslLoaded ? 'Yes' : 'No',
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'stream_wrappers' => stream_get_wrappers()
]);

if (!$httpsAvailable) {
    echo "<div class='alert alert-danger'>";
    echo "<h3 class='error'>¡ERROR CRÍTICO: Soporte HTTPS no disponible!</h3>";
    echo "<p>El servidor PHP no tiene habilitado el soporte para HTTPS, que es necesario para conectar con la API de ICD-11.</p>";
    echo "<h4>Solución:</h4>";
    echo "<ol>";
    echo "<li>Asegúrese de que la extensión OpenSSL está habilitada en php.ini</li>";
    echo "<li>Descomente la línea <code>extension=openssl</code> en php.ini</li>";
    echo "<li>Reinicie su servidor web</li>";
    echo "</ol>";
    echo "</div>";
}

if (!$curlAvailable && !$fileGetContentsAvailable) {
    die("<p class='error'>ERROR: No HTTP request methods available. Enable cURL or allow_url_fopen.</p></body></html>");
}

// Function to make HTTP request 
function httpRequest($url, $options = []) {
    global $curlAvailable;
    
    $method = $options['method'] ?? 'GET';
    $headers = $options['headers'] ?? [];
    $data = $options['data'] ?? null;
    
    echo "<p class='note'>Making $method request to $url</p>";
    
    // Try cURL first if available
    if ($curlAvailable) {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            
            $verboseOutput = fopen('php://temp', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $verboseOutput);
            
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
                }
            }
            
            if (!empty($headers)) {
                $headersList = [];
                foreach ($headers as $key => $value) {
                    $headersList[] = "$key: $value";
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headersList);
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            rewind($verboseOutput);
            $verboseLog = stream_get_contents($verboseOutput);
            
            curl_close($ch);
            
            if ($error) {
                printInfo("cURL Error", $error, 'error');
                printInfo("cURL Verbose Log", $verboseLog);
                return false;
            }
            
            return [
                'body' => $response,
                'status' => $httpCode,
                'method_used' => 'curl',
                'debug' => $verboseLog
            ];
        } catch (Exception $e) {
            printInfo("cURL Exception", $e->getMessage(), 'error');
            return false;
        }
    }
    
    // Fallback to file_get_contents
    try {
        $context = [];
        
        $context['http'] = [
            'method' => $method,
            'ignore_errors' => true,
            'follow_location' => true,
            'max_redirects' => 10,
            'timeout' => 30,
            'user_agent' => 'ICD11PHP/1.0'
        ];
        
        if ($method === 'POST') {
            $context['http']['header'] = 'Content-Type: application/x-www-form-urlencoded';
            $context['http']['content'] = is_array($data) ? http_build_query($data) : $data;
        }
        
        if (!empty($headers)) {
            $headerStr = isset($context['http']['header']) ? $context['http']['header'] . "\r\n" : '';
            foreach ($headers as $key => $value) {
                $headerStr .= "$key: $value\r\n";
            }
            $context['http']['header'] = $headerStr;
        }
        
        $context['ssl'] = [
            'verify_peer' => true,
            'verify_peer_name' => true
        ];
        
        $ctx = stream_context_create($context);
        $response = file_get_contents($url, false, $ctx);
        
        if ($response === false) {
            printInfo("file_get_contents Error", "Failed to get content", 'error');
            return false;
        }
        
        $status = 0;
        foreach ($http_response_header as $header) {
            if (preg_match('#HTTP/[0-9\.]+\s+([0-9]+)#', $header, $matches)) {
                $status = intval($matches[1]);
                break;
            }
        }
        
        return [
            'body' => $response,
            'status' => $status,
            'method_used' => 'file_get_contents',
            'headers' => $http_response_header
        ];
    } catch (Exception $e) {
        printInfo("file_get_contents Exception", $e->getMessage(), 'error');
        return false;
    }
}

// Get access token
echo "<h2>Access Token Test</h2>";

$postData = http_build_query([
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'scope' => 'icdapi_access',
    'grant_type' => 'client_credentials'
]);

$tokenResponse = httpRequest($tokenUrl, [
    'method' => 'POST',
    'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Accept' => 'application/json'
    ],
    'data' => $postData
]);

if (!$tokenResponse || $tokenResponse['status'] != 200) {
    printInfo("Token Request Failed", $tokenResponse ?? 'No response', 'error');
    die("<p class='error'>Failed to get access token. Cannot continue.</p></body></html>");
}

$tokenData = json_decode($tokenResponse['body'], true);
if (!isset($tokenData['access_token'])) {
    printInfo("Invalid Token Response", $tokenData, 'error');
    die("<p class='error'>Invalid token response. Cannot continue.</p></body></html>");
}

$accessToken = $tokenData['access_token'];
printInfo("Token Response", [
    'access_token' => substr($accessToken, 0, 10) . '...',
    'expires_in' => $tokenData['expires_in'] ?? 'unknown',
    'token_type' => $tokenData['token_type'] ?? 'unknown'
], 'success');

// Test search by code
echo "<h2>Search by Code Test</h2>";

$testCode = 'MD12'; // The specific code that's causing issues

// Array of endpoints to try
$endpoints = [
    [
        'url' => $apiBaseUrl . '/mms/lookup?q=' . urlencode($testCode),
        'name' => 'Lookup API'
    ],
    [
        'url' => $apiBaseUrl . '/mms/search?q=' . urlencode($testCode) . '&useFlexisearch=true&preferredLanguage=es',
        'name' => 'Search API'
    ],
    [
        'url' => $apiBaseUrl . '/mms/search?q=code:' . urlencode($testCode) . '&useFlexisearch=true&preferredLanguage=es',
        'name' => 'Search API with code: prefix'
    ]
];

echo "<div class='accordion'>";
foreach ($endpoints as $index => $endpoint) {
    echo "<div class='accordion-item mb-3'>";
    echo "<h3>" . ($index + 1) . ". " . $endpoint['name'] . "</h3>";
    echo "<div class='p-3 border'>";
    
    echo "<p class='note'>Testing endpoint: " . $endpoint['url'] . "</p>";
    
    $codeResponse = httpRequest($endpoint['url'], [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
            'Accept-Language' => 'es, en',
            'API-Version' => 'v2' // Requerido por la API ICD-11
        ]
    ]);

    if (!$codeResponse) {
        printInfo("Code Search Request Failed", "No response from API", 'error');
    } else {
        printInfo("Response Status", $codeResponse['status'] . " (" . ($codeResponse['status'] == 200 ? 'Success' : 'Error') . ")");
        
        if (isset($codeResponse['debug'])) {
            printInfo("Debug Information", $codeResponse['debug']);
        }
        
        if ($codeResponse['status'] == 200) {
            $codeData = json_decode($codeResponse['body'], true);
            
            if (!empty($codeData['destinationEntities']) && count($codeData['destinationEntities']) > 0) {
                printInfo("✅ SUCCESS! Found " . count($codeData['destinationEntities']) . " results", array_slice($codeData['destinationEntities'], 0, 2), 'success');
            } else {
                printInfo("Search returned 200 but no entities found", $codeData, 'error');
            }
        } else {
            printInfo("Code Search Error", $codeResponse['body'], 'error');
        }
    }
    
    echo "</div></div>";
}
echo "</div>";

// Show a working example with known good code
echo "<h2>Test with Known Working Code</h2>";
$workingCode = 'MB36'; // Known working ICD-11 code for testing
echo "<p>Testing with known working ICD-11 code: <strong>$workingCode</strong> (should succeed)</p>";
$workingUrl = $apiBaseUrl . '/mms/search?q=' . urlencode($workingCode) . '&useFlexisearch=true&preferredLanguage=es';
$workingResponse = httpRequest($workingUrl, [
    'headers' => [
        'Authorization' => 'Bearer ' . $accessToken,
        'Accept' => 'application/json',
        'Accept-Language' => 'es, en',
        'API-Version' => 'v2'
    ]
]);

if (!$workingResponse) {
    printInfo("Working Code Test Failed", "No response from API", 'error');
} else if ($workingResponse['status'] == 200) {
    $workingData = json_decode($workingResponse['body'], true);
    if (!empty($workingData['destinationEntities'])) {
        printInfo("✅ Reference Test Successful", [
            'status' => $workingResponse['status'],
            'entities_found' => count($workingData['destinationEntities']),
            'first_result' => $workingData['destinationEntities'][0] ?? 'No entities'
        ], 'success');
    } else {
        printInfo("Reference Test Returned 200 but no results", $workingData, 'warning');
    }
} else {
    printInfo("Reference Test Failed", [
        'status' => $workingResponse['status'],
        'response' => $workingResponse['body']
    ], 'error');
}

// Test search by term
echo "<h2>Search by Term Test</h2>";

$testTerm = 'diabetes';
$termUrl = $apiBaseUrl . '/mms/search?q=' . urlencode($testTerm) . '&useFlexisearch=true&preferredLanguage=es';

$termResponse = httpRequest($termUrl, [
    'headers' => [
        'Authorization' => 'Bearer ' . $accessToken,
        'Accept' => 'application/json',
        'Accept-Language' => 'es, en',
        'API-Version' => 'v2' // Requerido por la API ICD-11
    ]
]);

if (!$termResponse) {
    printInfo("Term Search Request Failed", "No response from API", 'error');
} else {
    printInfo("Response Status", $termResponse['status'] . " (" . ($termResponse['status'] == 200 ? 'Success' : 'Error') . ")");
    
    if (isset($termResponse['debug'])) {
        printInfo("Debug Information", $termResponse['debug']);
    }
    
    if ($termResponse['status'] == 200) {
        $termData = json_decode($termResponse['body'], true);
        printInfo("Term Search Result (Showing first 2 results)", array_slice($termData['destinationEntities'] ?? [], 0, 2), 'success');
    } else {
        printInfo("Term Search Error", $termResponse['body'], 'error');
    }
}

// Try alternative API endpoints
echo "<h2>Alternative Endpoint Test</h2>";

$alternativeCode = 'MB36';  
$alternativeUrl = 'https://id.who.int/icd/entity/1435254666'; // Example direct entity URL for reference

printInfo("Note", "Trying different known working code (MB36) and direct entity access");

$altCodeUrl = $apiBaseUrl . '/mms/lookup?q=' . urlencode($alternativeCode);
$altCodeResponse = httpRequest($altCodeUrl, [
    'headers' => [
        'Authorization' => 'Bearer ' . $accessToken,
        'Accept' => 'application/json',
        'Accept-Language' => 'es, en',
        'API-Version' => 'v2' // Requerido por la API ICD-11
    ]
]);

if ($altCodeResponse && $altCodeResponse['status'] == 200) {
    printInfo("Alternative Code Success", [
        'code' => $alternativeCode,
        'status' => $altCodeResponse['status'],
        'data_sample' => json_decode($altCodeResponse['body'], true)
    ], 'success');
} else {
    printInfo("Alternative Code Failed", $altCodeResponse ?? 'No response', 'error');
}

echo "<h2>Conclusions</h2>";
echo "<p>This diagnostic tool tested direct access to the ICD-11 API. Check the results above to identify any issues.</p>";

if (isset($codeResponse) && $codeResponse['status'] == 400) {
    echo "<p class='note'><strong>Recommendation:</strong> It appears that the WHO API is returning a 400 error for the code 'MD12'. This may indicate:</p>";
    echo "<ul>";
    echo "<li>The code format might be incorrect or not exist in the ICD-11 database</li>";
    echo "<li>The API endpoint or implementation may have changed</li>";
    echo "<li>There might be additional parameters required for this specific lookup</li>";
    echo "</ul>";
    echo "<p>Try using this tool with different codes or search terms to isolate the issue.</p>";
}

echo "</body></html>";
?>
