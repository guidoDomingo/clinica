<?php
try {
    $dsn = "pgsql:host=localhost;port=5432;dbname=clinica;user=postgres;password=postgres";
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'rs_servicios'");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Columns in rs_servicios: " . implode(", ", $columns);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
