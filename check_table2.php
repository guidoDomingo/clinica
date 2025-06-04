<?php
// Check rs_servicios table structure
try {
    $pdo = new PDO('mysql:host=localhost;dbname=clinica', 'root', '');
    $stmt = $pdo->query('SHOW COLUMNS FROM rs_servicios');
    echo "Structure of rs_servicios table:\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
