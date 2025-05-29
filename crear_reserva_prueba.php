<?php
/**
 * Script para crear una reserva de prueba
 */
require_once "model/conexion.php";

// Obtener la fecha de hoy o la fecha proporcionada
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Crear directorio de logs si no existe
$logDir = "logs";
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

$logFile = "$logDir/crear_reserva_prueba.log";
$log = fopen($logFile, "w");

function log_message($message) {
    global $log;
    $timestamp = date('Y-m-d H:i:s');
    fwrite($log, "[$timestamp] $message" . PHP_EOL);
    echo "[$timestamp] $message" . PHP_EOL; // También mostrar en consola
}

log_message("=== CREANDO RESERVA DE PRUEBA PARA: $fecha ===");

try {
    // Verificar conexión a la base de datos
    $db = Conexion::conectar();
    
    if ($db === null) {
        log_message("ERROR: No se pudo establecer conexión con la base de datos");
        exit;
    }
    
    log_message("Conexión a la base de datos: EXITOSA");
    
    // Verificar si ya existen reservas para esta fecha
    $sql = "SELECT COUNT(*) as total FROM servicios_reservas WHERE fecha_reserva = :fecha";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($count['total'] > 0) {
        log_message("Ya existen {$count['total']} reservas para la fecha $fecha");
        log_message("No se creará una nueva reserva para evitar duplicados");
        exit;
    }
    
    // Obtener doctor_id de la tabla rh_doctors
    log_message("Buscando un doctor disponible...");
    $stmt = $db->query("SELECT doctor_id FROM rh_doctors LIMIT 1");
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($doctor) {
        $doctorId = $doctor['doctor_id'];
        log_message("Doctor encontrado con ID: $doctorId");
    } else {
        log_message("No se encontró ningún doctor en la tabla rh_doctors");
        log_message("Usando ID predeterminado: 1");
        $doctorId = 1;
    }
    
    // Obtener person_id de la tabla rh_person
    log_message("Buscando un paciente disponible...");
    $stmt = $db->query("SELECT person_id FROM rh_person LIMIT 1");
    $persona = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($persona) {
        $personId = $persona['person_id'];
        log_message("Paciente encontrado con ID: $personId");
    } else {
        log_message("No se encontró ninguna persona en la tabla rh_person");
        log_message("Usando ID predeterminado: 1");
        $personId = 1;
    }
    
    // Obtener servicio_id de la tabla rs_servicios
    log_message("Buscando un servicio disponible...");
    $stmt = $db->query("SELECT servicio_id FROM rs_servicios LIMIT 1");
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($servicio) {
        $servicioId = $servicio['servicio_id'];
        log_message("Servicio encontrado con ID: $servicioId");
    } else {
        log_message("No se encontró ningún servicio en la tabla rs_servicios");
        log_message("Usando ID predeterminado: 1");
        $servicioId = 1;
    }
    
    // Crear la reserva
    log_message("Creando reserva con los siguientes datos:");
    log_message("  Doctor ID: $doctorId");
    log_message("  Paciente ID: $personId");
    log_message("  Servicio ID: $servicioId");
    log_message("  Fecha: $fecha");
    log_message("  Hora: 09:00:00");
    log_message("  Estado: CONFIRMADA");
    
    $sql = "INSERT INTO servicios_reservas 
            (doctor_id, paciente_id, servicio_id, fecha_reserva, hora_inicio, estado_reserva) 
            VALUES (:doctor_id, :paciente_id, :servicio_id, :fecha, :hora, 'CONFIRMADA')";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':doctor_id', $doctorId);
    $stmt->bindParam(':paciente_id', $personId);
    $stmt->bindParam(':servicio_id', $servicioId);
    $stmt->bindParam(':fecha', $fecha);
    $hora = '09:00:00';
    $stmt->bindParam(':hora', $hora);
    
    if ($stmt->execute()) {
        $reservaId = $db->lastInsertId();
        log_message("¡Reserva creada exitosamente con ID: $reservaId!");
    } else {
        log_message("ERROR: No se pudo crear la reserva");
    }
    
    log_message("Revisa el archivo $logFile para más detalles.");
    
} catch (Exception $e) {
    log_message("ERROR: " . $e->getMessage());
    log_message("Archivo: " . $e->getFile() . " línea " . $e->getLine());
}

fclose($log);
echo "Proceso completado. Revisa el archivo $logFile para más detalles.";
?>
