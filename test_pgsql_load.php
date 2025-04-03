<?php
// Test if we can dynamically load PostgreSQL extensions
echo "<h1>Testing PostgreSQL Extension Loading</h1>";

// Check if extensions are already loaded
echo "<p>Initial status:</p>";
echo "<ul>";
echo "<li>pgsql extension: " . (extension_loaded('pgsql') ? 'Loaded' : 'Not loaded') . "</li>";
echo "<li>pdo_pgsql extension: " . (extension_loaded('pdo_pgsql') ? 'Loaded' : 'Not loaded') . "</li>";
echo "</ul>";

// Try to dynamically load extensions
echo "<p>Attempting to load extensions:</p>";

// Try to load pgsql extension
if (!extension_loaded('pgsql')) {
    if (function_exists('dl')) {
        $result = @dl('php_pgsql.dll'); // Windows
        echo "<p>Loading pgsql extension: " . ($result ? 'Success' : 'Failed') . "</p>";
    } else {
        echo "<p>Cannot dynamically load extensions - dl() function is disabled</p>";
    }
}

// Try to load pdo_pgsql extension
if (!extension_loaded('pdo_pgsql')) {
    if (function_exists('dl')) {
        $result = @dl('php_pdo_pgsql.dll'); // Windows
        echo "<p>Loading pdo_pgsql extension: " . ($result ? 'Success' : 'Failed') . "</p>";
    }
}

// Check status after loading attempt
echo "<p>Status after loading attempt:</p>";
echo "<ul>";
echo "<li>pgsql extension: " . (extension_loaded('pgsql') ? 'Loaded' : 'Not loaded') . "</li>";
echo "<li>pdo_pgsql extension: " . (extension_loaded('pdo_pgsql') ? 'Loaded' : 'Not loaded') . "</li>";
echo "</ul>";

// Show loaded extensions
echo "<p>All loaded extensions:</p>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";
?>