<?php
require_once "model/conexion.php";

try {
    $connection = Conexion::conectar();
    echo "<h2>Database Connection Test</h2>";
    echo "<p>Connection successful!</p>";
    
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
    echo "<h2>Database Connection Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>