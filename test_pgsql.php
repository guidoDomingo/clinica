<?php
/**
 * Simple script to test PostgreSQL connectivity
 * This file should display minimal output for quick testing
 */

// Function to test DB connection
function test_db_connection() {
    try {
        $contrasena = "admin";
        $usuario = "postgres";
        $nombreBaseDeDatos = "clinica";
        $rutaServidor = "localhost";
        $puerto = "5432";
        
        if (!extension_loaded('pdo_pgsql')) {
            return "Error: PDO PostgreSQL extension not loaded";
        }
        
        $link = new PDO("pgsql:host=$rutaServidor;port=$puerto;dbname=$nombreBaseDeDatos", $usuario, $contrasena);
        $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test a simple query
        $result = $link->query("SELECT current_database() AS db_name")->fetch(PDO::FETCH_ASSOC);
        return "Connected successfully to database: " . $result['db_name'];
    }
    catch(PDOException $e) {
        return "Connection failed: " . $e->getMessage();
    }
}

// Output
header('Content-Type: text/plain');
echo "PostgreSQL Connection Test\n";
echo "=========================\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PDO PostgreSQL Extension: " . (extension_loaded('pdo_pgsql') ? "Loaded ✓" : "Not Loaded ✗") . "\n";
echo "PostgreSQL Extension: " . (extension_loaded('pgsql') ? "Loaded ✓" : "Not Loaded ✗") . "\n\n";
echo "Connection Test: " . test_db_connection() . "\n";
?>
