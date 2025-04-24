<?php
// Script de diagnóstico para la tabla de preformatos
require_once "model/conexion.php";

try {
    // Establecer la conexión a la base de datos
    $conn = Conexion::conectar();
    
    echo "<h2>Diagnóstico de la tabla preformatos</h2>";
    
    // 1. Verificar si la tabla existe
    $checkTable = $conn->query("SELECT to_regclass('public.preformatos') as tabla");
    $tableExists = $checkTable->fetch(PDO::FETCH_ASSOC);
    
    if (!$tableExists['tabla']) {
        echo "<p style='color:red'>ERROR: La tabla 'preformatos' no existe en la base de datos.</p>";
        exit();
    } else {
        echo "<p style='color:green'>✓ La tabla 'preformatos' existe en la base de datos.</p>";
    }
    
    // 2. Contar todos los registros
    $countAll = $conn->query("SELECT COUNT(*) as total FROM preformatos");
    $totalRegistros = $countAll->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Total de registros en la tabla preformatos: <strong>{$totalRegistros['total']}</strong></p>";
    
    // 3. Contar registros activos
    $countActive = $conn->query("SELECT COUNT(*) as activos FROM preformatos WHERE activo = true");
    $registrosActivos = $countActive->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Total de registros activos: <strong>{$registrosActivos['activos']}</strong></p>";
    
    // 4. Si hay registros, mostrar algunos como ejemplo
    if ($totalRegistros['total'] > 0) {
        $sample = $conn->query("SELECT id_preformato, nombre, tipo, activo FROM preformatos LIMIT 10");
        $registros = $sample->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Muestra de registros:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Activo</th></tr>";
        
        foreach ($registros as $reg) {
            $activoStr = $reg['activo'] ? 'Sí' : 'No';
            echo "<tr>";
            echo "<td>{$reg['id_preformato']}</td>";
            echo "<td>{$reg['nombre']}</td>";
            echo "<td>{$reg['tipo']}</td>";
            echo "<td>{$activoStr}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color:red'>No hay registros en la tabla 'preformatos'.</p>";
        echo "<h3>Posibles soluciones:</h3>";
        echo "<ol>";
        echo "<li>Insertar datos de ejemplo en la tabla 'preformatos'</li>";
        echo "<li>Verificar si hay algún script de inicialización de datos que no se ha ejecutado</li>";
        echo "</ol>";
    }
    
    // 5. Verificar la estructura de la tabla
    echo "<h3>Estructura de la tabla:</h3>";
    $estructura = $conn->query("
        SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'preformatos'
        ORDER BY ordinal_position
    ");
    
    $columnas = $estructura->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Columna</th><th>Tipo de datos</th><th>Permite NULL</th></tr>";
    
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td>{$col['column_name']}</td>";
        echo "<td>{$col['data_type']}</td>";
        echo "<td>{$col['is_nullable']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<h2>Error en la conexión o consulta</h2>";
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h2 { color: #333; }
    table { border-collapse: collapse; margin: 15px 0; }
    th { background-color: #f2f2f2; }
</style>