<?php
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = \Api\Core\Database::getConnection();
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/init_roles_permissions.sql');
    $pdo->exec($sql);
    
    echo "Successfully initialized roles and permissions.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}