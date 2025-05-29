<?php
/**
 * Test directo para la consulta SQL de reservas
 */

// Activar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "model/conexion.php";

echo "<h1>Test directo de consulta SQL para reservas</h1>";

try {
    // Conectar a la base de datos
    $db = Conexion::conectar();
    
    if (!$db) {
        echo "<p style='color:red;'>Error: No se pudo conectar a la base de datos</p>";
        exit;
    }
    
    echo "<p style='color:green;'>Conexión a la base de datos correcta</p>";
    
    // Fecha para la consulta (hoy)
    $fechaHoy = date('Y-m-d');
    $fechaInicio = $fechaHoy . ' 00:00:00';
    $fechaFin = date('Y-m-d', strtotime('+1 day')) . ' 23:59:00';
    
    echo "<p>Consultando reservas desde $fechaInicio hasta $fechaFin</p>";
    
    // Consulta exactamente como la proporcionó el usuario
    $sql = "SELECT 
        sr.hora_inicio,
        sr.hora_fin,
        rp.first_name ||' - ' || rp.last_name as doctor,
        rp2.first_name ||' - ' || rp2.last_name as paciente,
        rs.serv_descripcion,
        sr.reserva_estado 
    FROM servicios_reservas sr 
    INNER JOIN rh_doctors rd 
    ON sr.doctor_id = rd.doctor_id 
    INNER JOIN rh_person rp 
    ON rd.person_id = rp.person_id 
    INNER JOIN rh_person rp2 
    ON sr.paciente_id = rp2.person_id 
    INNER JOIN rs_servicios rs 
    ON sr.servicio_id = rs.serv_id 
    WHERE sr.fecha_reserva BETWEEN :fecha_inicio AND :fecha_fin";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':fecha_inicio', $fechaInicio);
    $stmt->bindParam(':fecha_fin', $fechaFin);
    $stmt->execute();
    
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Resultados</h2>";
    
    if (count($reservas) > 0) {
        echo "<p style='color:green;'>Se encontraron " . count($reservas) . " reservas</p>";
        
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse;'>";
        echo "<tr style='background-color:#f0f0f0;'>";
        
        // Headers
        foreach (array_keys($reservas[0]) as $key) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        
        // Rows
        foreach ($reservas as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value !== null ? $value : 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color:orange;'>No se encontraron reservas en el período seleccionado</p>";
    }
    
    // También mostrar la estructura de la tabla rs_servicios para verificar
    echo "<h2>Estructura de la tabla rs_servicios</h2>";
    
    $sql = "SELECT column_name, data_type, is_nullable 
            FROM information_schema.columns 
            WHERE table_name = 'rs_servicios' 
            ORDER BY ordinal_position";
    
    $stmt = $db->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($columns) > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse;'>";
        echo "<tr style='background-color:#f0f0f0;'><th>Columna</th><th>Tipo</th><th>Nullable</th></tr>";
        
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($col['column_name']) . "</td>";
            echo "<td>" . htmlspecialchars($col['data_type']) . "</td>";
            echo "<td>" . htmlspecialchars($col['is_nullable']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color:orange;'>No se pudo obtener la estructura de la tabla rs_servicios</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>En el archivo: " . $e->getFile() . " línea " . $e->getLine() . "</p>";
}
?>
