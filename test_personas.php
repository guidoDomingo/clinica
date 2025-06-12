<?php
require_once "model/conexion.php";

try {
    $pdo = Conexion::conectar();
    
    echo "<h3>Listado de Pacientes (Primeros 10)</h3>";
    
    $stmt = $pdo->prepare("
        SELECT 
            person_id as id_persona,
            document_number as documento,
            record_number as ficha,
            first_name as nombre,
            last_name as apellido,
            EXTRACT(YEAR FROM AGE(CURRENT_DATE, birth_date)) as edad,
            phone as telefono
        FROM 
            public.rh_person 
        ORDER BY person_id ASC 
        LIMIT 10
    ");
    
    $stmt->execute();
    $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($personas)) {
        echo "<p style='color: red;'>No se encontraron personas en la base de datos.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px;'>";
        echo "<tr>";
        echo "<th>ID</th><th>Documento</th><th>Ficha</th><th>Nombre</th><th>Apellido</th><th>Edad</th><th>Teléfono</th>";
        echo "</tr>";
        
        foreach ($personas as $persona) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($persona['id_persona']) . "</td>";
            echo "<td>" . htmlspecialchars($persona['documento']) . "</td>";
            echo "<td>" . htmlspecialchars($persona['ficha']) . "</td>";
            echo "<td>" . htmlspecialchars($persona['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($persona['apellido']) . "</td>";
            echo "<td>" . htmlspecialchars($persona['edad']) . "</td>";
            echo "<td>" . htmlspecialchars($persona['telefono']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Ahora buscar específicamente el ID 45
    echo "<h3>Búsqueda específica para ID 45</h3>";
    
    $stmt = $pdo->prepare("
        SELECT 
            person_id as id_persona,
            document_number as documento,
            record_number as ficha,
            first_name as nombre,
            last_name as apellido,
            EXTRACT(YEAR FROM AGE(CURRENT_DATE, birth_date)) as edad,
            phone as telefono
        FROM 
            public.rh_person 
        WHERE 
            person_id = :id_persona
    ");
    
    $stmt->bindParam(":id_persona", $idPersona = 45, PDO::PARAM_INT);
    $stmt->execute();
    $persona45 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($persona45) {
        echo "<p style='color: green;'>✅ Persona con ID 45 encontrada:</p>";
        echo "<pre>" . print_r($persona45, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ No se encontró persona con ID 45</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
