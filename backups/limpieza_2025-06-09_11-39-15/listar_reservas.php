<?php
/**
 * Script para listar las reservas disponibles
 * Ayuda a diagnosticar problemas con generar_pdf_reserva.php
 */

// Incluir clases necesarias
require_once 'model/conexion.php';

// Intentar obtener reservas directamente de la base de datos
try {
    $conn = Conexion::conectar();
      // Consulta directa para obtener IDs de reservas
    $query = "SELECT 
                sr.reserva_id,
                rp2.first_name || ' - ' || rp2.last_name as paciente,
                sr.fecha_reserva,
                sr.hora_inicio,
                sr.reserva_estado
              FROM servicios_reservas sr 
              INNER JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id
              ORDER BY sr.reserva_id LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    echo "<h1>Reservas disponibles</h1>";
    echo "<pre>";
    
    if ($stmt->rowCount() > 0) {
        echo "Se encontraron " . $stmt->rowCount() . " reservas:\n\n";
        
        echo "ID | Paciente | Fecha | Hora | Estado\n";
        echo "----------------------------------------------------\n";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {        echo $row['reserva_id'] . " | " . 
                 $row['paciente'] . " | " . 
                 $row['fecha_reserva'] . " | " . 
                 $row['hora_inicio'] . " | " . 
                 $row['reserva_estado'] . "\n";
        }
    } else {
        echo "No se encontraron reservas en la base de datos.";
    }
    
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage();
}
?>
