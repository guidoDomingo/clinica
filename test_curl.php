<?php
// Test if cURL is enabled
if (function_exists('curl_version')) {
    echo "cURL is enabled\n";
    $version = curl_version();
    echo "cURL version: " . $version['version'] . "\n";
} else {
    echo "cURL is NOT enabled\n";
}
