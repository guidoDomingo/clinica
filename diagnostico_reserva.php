<?php
/**
 * Script para diagnosticar problemas con la obtención de una reserva específica
 */

// Incluir clases necesarias
require_once 'model/conexion.php';
require_once 'model/reservas.model.php';

// ID a verificar (usar el que está fallando)
$reservaId = isset($_GET['id']) ? intval($_GET['id']) : 20;

echo "<h1>Diagnóstico de Reserva ID: $reservaId</h1>";
echo "<pre>";

try {    // 1. Verificar si la reserva existe directamente en la tabla servicios_reservas
    $conn = Conexion::conectar();
    $query = "SELECT * FROM servicios_reservas WHERE reserva_id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
    $stmt->execute();
    
    echo "1. Buscando reserva directamente en tabla servicios_reservas:\n";
    if ($stmt->rowCount() > 0) {
        $reservaDirecta = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✓ Reserva encontrada en tabla servicios_reservas\n";
        print_r($reservaDirecta);
    } else {
        echo "✗ No se encontró ninguna reserva con ID $reservaId en la tabla servicios_reservas\n";
          // Si la reserva no existe, buscar los IDs disponibles
        $queryIds = "SELECT reserva_id FROM servicios_reservas ORDER BY reserva_id LIMIT 10";
        $stmtIds = $conn->prepare($queryIds);
        $stmtIds->execute();
        $ids = $stmtIds->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($ids)) {
            echo "\nIDs de reservas disponibles: " . implode(", ", $ids) . "\n";
            echo "\nIntente usar uno de estos IDs en lugar de $reservaId\n";
        } else {
            echo "\nNo hay reservas en la base de datos.\n";
        }
        
        exit;
    }
    
    // 2. Intentar con el método del modelo
    echo "\n2. Usando el método obtenerReservaPorId del modelo:\n";
    $modelo = new ReservasModel();
    $reserva = $modelo->obtenerReservaPorId($reservaId);
    
    if ($reserva) {
        echo "✓ Reserva obtenida correctamente mediante el modelo\n";
        print_r($reserva);
    } else {
        echo "✗ El método del modelo no pudo obtener la reserva. Verificando posibles causas:\n";
        
        // 3. Verificar si la consulta del modelo tiene algún problema de JOIN
        echo "\n3. Diagnosticando posibles problemas en los JOINs:\n";
        
        // Verificar paciente
        $queryPaciente = "SELECT * FROM pacientes WHERE paciente_id = :id";
        $stmtPaciente = $conn->prepare($queryPaciente);
        $stmtPaciente->bindParam(":id", $reservaDirecta['paciente_id'], PDO::PARAM_INT);
        $stmtPaciente->execute();
        
        if ($stmtPaciente->rowCount() > 0) {
            echo "✓ Paciente encontrado en tabla pacientes\n";
        } else {
            echo "✗ No se encontró el paciente con ID " . $reservaDirecta['paciente_id'] . "\n";
        }
        
        // Verificar servicio
        $queryServicio = "SELECT * FROM rs_servicios WHERE serv_id = :id";
        $stmtServicio = $conn->prepare($queryServicio);
        $stmtServicio->bindParam(":id", $reservaDirecta['servicio_id'], PDO::PARAM_INT);
        $stmtServicio->execute();
        
        if ($stmtServicio->rowCount() > 0) {
            echo "✓ Servicio encontrado en tabla rs_servicios\n";
        } else {
            echo "✗ No se encontró el servicio con ID " . $reservaDirecta['servicio_id'] . "\n";
        }
        
        // Verificar médico
        $queryMedico = "SELECT * FROM medicos WHERE medico_id = :id";
        $stmtMedico = $conn->prepare($queryMedico);
        $stmtMedico->bindParam(":id", $reservaDirecta['medico_id'], PDO::PARAM_INT);
        $stmtMedico->execute();
        
        if ($stmtMedico->rowCount() > 0) {
            echo "✓ Médico encontrado en tabla medicos\n";
        } else {
            echo "✗ No se encontró el médico con ID " . $reservaDirecta['medico_id'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "</pre>";
echo "<p><a href='listar_reservas.php'>Ver lista de todas las reservas</a></p>";
?>
