<?php
// This script will attempt to enable PostgreSQL extensions and test the connection

// Check current extension status
echo "<h1>PostgreSQL Extension Status</h1>";

if (extension_loaded('pgsql') && extension_loaded('pdo_pgsql')) {
    echo "<p style='color: green;'>PostgreSQL extensions are already loaded.</p>";
} else {
    echo "<p style='color: red;'>PostgreSQL extensions are NOT loaded!</p>";
    
    // Try to dynamically load the extensions
    echo "<p>Attempting to load extensions dynamically...</p>";
    
    $success = false;
    
    // Try to load pgsql extension
    if (!extension_loaded('pgsql')) {
        if (function_exists('dl')) {
            @dl('php_pgsql.dll'); // Windows
            if (extension_loaded('pgsql')) {
                echo "<p style='color: green;'>Successfully loaded pgsql extension.</p>";
                $success = true;
            }
        }
    }
    
    // Try to load pdo_pgsql extension
    if (!extension_loaded('pdo_pgsql')) {
        if (function_exists('dl')) {
            @dl('php_pdo_pgsql.dll'); // Windows
            if (extension_loaded('pdo_pgsql')) {
                echo "<p style='color: green;'>Successfully loaded pdo_pgsql extension.</p>";
                $success = true;
            }
        }
    }
    
    if (!$success) {
        echo "<p>Could not load extensions dynamically. You need to enable them in php.ini:</p>";
        echo "<ul>";
        echo "<li>extension=pgsql</li>";
        echo "<li>extension=pdo_pgsql</li>";
        echo "</ul>";
        
        echo "<p>Steps to enable PostgreSQL in php.ini:</p>";
        echo "<ol>";
        echo "<li>Locate your php.ini file (usually in PHP installation directory)</li>";
        echo "<li>Open php.ini in a text editor</li>";
        echo "<li>Find the lines with extension=pgsql and extension=pdo_pgsql</li>";
        echo "<li>Remove the semicolon (;) at the beginning of these lines to uncomment them</li>";
        echo "<li>Save the file and restart your web server</li>";
        echo "</ol>";
    }
}

// Test connection with both methods
echo "<h2>Testing Database Connection</h2>";

// Method 1: Using conexion.php
echo "<h3>Method 1: Using model/conexion.php</h3>";
try {
    require_once "model/conexion.php";
    $connection = Conexion::conectar();
    echo "<p style='color: green;'>Connection successful using conexion.php!</p>";
    
    // Test if the usuario_registrados table exists
    $stmt = $connection->prepare("SELECT * FROM information_schema.tables WHERE table_name = 'usuario_registrados' LIMIT 1");
    $stmt->execute();
    $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tableExists) {
        echo "<p>Table 'usuario_registrados' exists.</p>";
    } else {
        echo "<p>Table 'usuario_registrados' does not exist!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error using conexion.php: " . $e->getMessage() . "</p>";
}

// Method 2: Using API\Core\Database
echo "<h3>Method 2: Using API\Core\Database</h3>";
try {
    require_once "config/config.php";
    $connection = \Api\Core\Database::getConnection();
    echo "<p style='color: green;'>Connection successful using API\Core\Database!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error using API\Core\Database: " . $e->getMessage() . "</p>";
}

// Show connection details from both files
echo "<h2>Connection Details</h2>";

echo "<h3>From model/conexion.php:</h3>";
echo "<pre>";
echo "Host: localhost\n";
echo "Port: 5432\n";
echo "Database: clinica\n";
echo "Username: postgres\n";
echo "Password: [hidden]\n";
echo "</pre>";

echo "<h3>From config/config.php:</h3>";
echo "<pre>";
echo "Host: 192.168.0.39\n";
echo "Port: 5432\n";
echo "Database: crm_clinic_db\n";
echo "Username: admindba\n";
echo "Password: [hidden]\n";
echo "</pre>";

echo "<p>Note: The connection details are different between the two files. This could be causing issues.</p>";
?>