<?php
// Simple diagnostic script for reservation listing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once 'config/config.php';
require_once 'model/conexion.php';
require_once 'model/servicios.model.php';
require_once 'controller/servicios.controller.php';

// Test date
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '2025-05-28';
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;

// Create a logfile
$logFile = 'c:/laragon/www/clinica/logs/test_reservas.log';
file_put_contents($logFile, "Starting reservation test at " . date('Y-m-d H:i:s') . "\n");

function log_message($message) {
    global $logFile;
    file_put_contents($logFile, $message . "\n", FILE_APPEND);
    echo $message . "<br>";
}

// Test direct database query
try {
    $conn = Conexion::conectar();
    log_message("Database connection successful");
    
    // Check if table exists
    $stmt = $conn->prepare("SELECT to_regclass('public.servicios_reservas')");
    $stmt->execute();
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        log_message("ERROR: Table servicios_reservas does not exist!");
    } else {
        log_message("Table servicios_reservas exists");
        
        // Direct query for reservations
        $sql = "SELECT * FROM servicios_reservas WHERE fecha_reserva = :fecha";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
        $stmt->execute();
        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        log_message("Direct query found " . count($reservas) . " reservations for date $fecha");
        if (count($reservas) > 0) {
            log_message("First reservation: " . json_encode($reservas[0]));
        }
        
        // Test controller method
        $controllerReservas = ControladorServicios::ctrObtenerReservasPorFecha($fecha, $doctorId);
        log_message("Controller method found " . count($controllerReservas) . " reservations");
        if (count($controllerReservas) > 0) {
            log_message("First reservation from controller: " . json_encode($controllerReservas[0]));
        }
        
        // Check fields that might be missing
        if (count($reservas) > 0) {
            log_message("Raw database fields available:");
            foreach ($reservas[0] as $key => $value) {
                log_message("- $key: $value");
            }
        }
        
        // Check if Estado/reserva_estado field exists and is populated
        if (count($reservas) > 0) {
            $hasEstado = array_key_exists('estado', $reservas[0]);
            $hasReservaEstado = array_key_exists('reserva_estado', $reservas[0]);
            
            log_message("Estado field exists: " . ($hasEstado ? 'Yes' : 'No'));
            log_message("reserva_estado field exists: " . ($hasReservaEstado ? 'Yes' : 'No'));
            
            if ($hasReservaEstado) {
                log_message("Sample reserva_estado value: " . $reservas[0]['reserva_estado']);
            }
        }
    }
} catch (PDOException $e) {
    log_message("DATABASE ERROR: " . $e->getMessage());
}

// Display the complete table of reservations
echo "<h2>Reservations for date: $fecha</h2>";
if (isset($reservas) && count($reservas) > 0) {
    echo "<table border='1'>";
    echo "<tr>";
    foreach (array_keys($reservas[0]) as $key) {
        echo "<th>$key</th>";
    }
    echo "</tr>";
    
    foreach ($reservas as $r) {
        echo "<tr>";
        foreach ($r as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No reservations found for this date.</p>";
}

// Display complete controller result
if (isset($controllerReservas) && count($controllerReservas) > 0) {
    echo "<h2>Controller Results</h2>";
    echo "<table border='1'>";
    echo "<tr>";
    foreach (array_keys($controllerReservas[0]) as $key) {
        echo "<th>$key</th>";
    }
    echo "</tr>";
    
    foreach ($controllerReservas as $r) {
        echo "<tr>";
        foreach ($r as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Controller found no reservations.</p>";
}
?>
