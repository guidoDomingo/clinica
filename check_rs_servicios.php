<?php
/**
 * Script para verificar la estructura de la tabla rs_servicios
 */
require_once "model/conexion.php";

// Activar visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Estructura de la tabla rs_servicios</h1>";

try {
    $db = Conexion::conectar();
    
    if ($db === null) {
        echo "<p>Error: No se pudo conectar a la base de datos</p>";
        exit;
    }
    
    echo "<p>Conexión a la base de datos: OK</p>";
    
    // Verificar si existe la tabla
    $sqlCheck = "SELECT EXISTS (
        SELECT 1 
        FROM information_schema.tables 
        WHERE table_name = 'rs_servicios'
    )";
    
    $stmtCheck = $db->query($sqlCheck);
    $existeTabla = $stmtCheck->fetchColumn();
    
    if (!$existeTabla) {
        echo "<p>La tabla rs_servicios no existe en la base de datos</p>";
        exit;
    }
    
    echo "<p>La tabla rs_servicios existe: OK</p>";
    
    // Obtener estructura de la tabla
    $sqlColumns = "SELECT 
        column_name, 
        data_type,
        is_nullable,
        column_default
    FROM 
        information_schema.columns 
    WHERE 
        table_name = 'rs_servicios'
    ORDER BY 
        ordinal_position";
    
    $stmtColumns = $db->query($sqlColumns);
    $columnas = $stmtColumns->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Columnas de la tabla rs_servicios</h2>";
    
    if (count($columnas) > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Nombre de columna</th><th>Tipo de dato</th><th>Puede ser NULL</th><th>Valor por defecto</th></tr>";
        
        foreach ($columnas as $columna) {
            echo "<tr>";
            echo "<td>" . $columna['column_name'] . "</td>";
            echo "<td>" . $columna['data_type'] . "</td>";
            echo "<td>" . $columna['is_nullable'] . "</td>";
            echo "<td>" . ($columna['column_default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No se encontraron columnas en la tabla rs_servicios</p>";
    }
    
    // Obtener algunos datos de ejemplo
    $sqlData = "SELECT * FROM rs_servicios LIMIT 5";
    $stmtData = $db->query($sqlData);
    $datos = $stmtData->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Datos de ejemplo de rs_servicios</h2>";
    
    if (count($datos) > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        
        // Encabezados de tabla
        echo "<tr>";
        foreach (array_keys($datos[0]) as $key) {
            echo "<th>" . $key . "</th>";
        }
        echo "</tr>";
        
        // Filas de datos
        foreach ($datos as $fila) {
            echo "<tr>";
            foreach ($fila as $valor) {
                echo "<td>" . ($valor ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No se encontraron datos en la tabla rs_servicios</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
