<?php
/**
 * Script para verificar las reservas por fecha y guardar los resultados en un log
 * Esto nos permite examinar los resultados desde la terminal
 */
require_once "model/conexion.php";
require_once "model/servicios.model.php";

// Crear directorio de logs si no existe
$logDir = "logs";
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

$logFile = "$logDir/diagnostico_reservas.log";
$log = fopen($logFile, "w");

function log_message($message) {
    global $log;
    $timestamp = date('Y-m-d H:i:s');
    fwrite($log, "[$timestamp] $message" . PHP_EOL);
    echo "[$timestamp] $message" . PHP_EOL; // También mostrar en consola
}

// Obtener la fecha de hoy o la fecha proporcionada
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

log_message("=== DIAGNÓSTICO DE RESERVAS PARA: $fecha ===");

try {
    // Verificar si la extensión PostgreSQL está habilitada
    if (!extension_loaded('pdo_pgsql')) {
        log_message("ERROR: La extensión pdo_pgsql no está habilitada");
        exit;
    }
    
    log_message("Extensión pdo_pgsql: HABILITADA");
    
    // Verificar conexión a la base de datos
    $db = Conexion::conectar();
    
    if ($db === null) {
        log_message("ERROR: No se pudo establecer conexión con la base de datos");
        exit;
    }
    
    log_message("Conexión a la base de datos: EXITOSA");
    
    // 1. Verificar si existen reservas para esta fecha con una consulta directa
    log_message("=== 1. CONSULTA DIRECTA A LA BASE DE DATOS ===");
    
    $sql = "SELECT COUNT(*) as total FROM servicios_reservas WHERE fecha_reserva = :fecha";
    log_message("SQL: $sql");
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($count['total'] > 0) {
        log_message("Se encontraron {$count['total']} reservas para la fecha $fecha");
    } else {
        log_message("No se encontraron reservas para la fecha $fecha");
    }
    
    // 2. Validar formato de fecha
    log_message("=== 2. VALIDACIÓN DE FORMATO DE FECHA ===");
    
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $fechaFormateada = date('Y-m-d', strtotime($fecha));
        log_message("El formato de fecha proporcionado no es YYYY-MM-DD");
        log_message("Fecha original: $fecha");
        log_message("Fecha reformateada: $fechaFormateada");
        $fecha = $fechaFormateada;
    } else {
        log_message("El formato de fecha es correcto: $fecha");
    }
    
    // 3. Usar el método del modelo para obtener reservas
    log_message("=== 3. CONSULTA MEDIANTE EL MODELO (mdlObtenerReservasPorFecha) ===");
    
    // Verificar que la clase del modelo exista
    if (class_exists('ModelServicios')) {
        log_message("Clase ModelServicios encontrada, ejecutando mdlObtenerReservasPorFecha");
        $reservas = ModelServicios::mdlObtenerReservasPorFecha($fecha);
        
        if ($reservas === false) {
            log_message("ERROR: La función del modelo devolvió FALSE");
        } else if (empty($reservas)) {
            log_message("La función del modelo devolvió un array vacío");
        } else {
            log_message("La función del modelo devolvió " . count($reservas) . " reservas");
            log_message("Primera reserva: " . json_encode($reservas[0]));
        }
    } else {
        log_message("ERROR: Clase ModelServicios no encontrada");
    }
    
    // 4. Examinar la estructura de la tabla
    log_message("=== 4. ESTRUCTURA DE LA TABLA servicios_reservas ===");
    
    $sql = "SELECT column_name, data_type, is_nullable 
            FROM information_schema.columns 
            WHERE table_name = 'servicios_reservas' 
            ORDER BY ordinal_position";
    
    $stmt = $db->query($sql);
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    log_message("Columnas de la tabla servicios_reservas:");
    foreach ($columnas as $col) {
        log_message("  {$col['column_name']} ({$col['data_type']}) - Nullable: {$col['is_nullable']}");
    }
    
    // 5. Consultar directamente servicios_reservas para la fecha especificada
    log_message("=== 5. CONSULTA DIRECTA DE servicios_reservas ===");
    
    $sql = "SELECT * FROM servicios_reservas WHERE fecha_reserva = :fecha";
    log_message("SQL: $sql");
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
    $stmt->execute();
    $reservasDirectas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($reservasDirectas)) {
        log_message("No se encontraron registros directamente en la tabla servicios_reservas");
    } else {
        log_message("Se encontraron " . count($reservasDirectas) . " registros directamente en la tabla");
        log_message("Primera reserva: " . json_encode($reservasDirectas[0]));
    }
    
    // 6. Consulta SQL con JOIN completo
    log_message("=== 6. CONSULTA SQL CON JOIN COMPLETO ===");
    
    $sql = "SELECT 
       r.reserva_id,
       r.doctor_id,
       r.paciente_id,
       r.servicio_id,
       r.fecha_reserva,
       r.hora_inicio,
       r.estado_reserva,
       COALESCE(doc_person.first_name || ' ' || doc_person.last_name, 'Doctor ' || r.doctor_id) as doctor_nombre,
       COALESCE(pac_person.first_name || ' ' || pac_person.last_name, 'Paciente ' || r.paciente_id) as paciente_nombre,
       COALESCE(s.nombre, 'Servicio ' || r.servicio_id) as servicio_nombre
    FROM servicios_reservas r
    LEFT JOIN rh_doctors d ON r.doctor_id = d.doctor_id
    LEFT JOIN rh_person doc_person ON d.person_id = doc_person.person_id
    LEFT JOIN rh_person pac_person ON r.paciente_id = pac_person.person_id
    LEFT JOIN rs_servicios s ON r.servicio_id = s.servicio_id
    WHERE r.fecha_reserva = :fecha";
    
    log_message("SQL: JOIN Completo (resumido)");
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
    $stmt->execute();
    $reservasCompletas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($reservasCompletas)) {
        log_message("La consulta JOIN completa no devolvió resultados");
    } else {
        log_message("La consulta JOIN completa devolvió " . count($reservasCompletas) . " resultados");
        log_message("Primera reserva completa: " . json_encode($reservasCompletas[0]));
    }
    
    log_message("Diagnóstico completo. Revisa el archivo $logFile para más detalles.");
    
} catch (Exception $e) {
    log_message("ERROR: " . $e->getMessage());
    log_message("Archivo: " . $e->getFile() . " línea " . $e->getLine());
}

fclose($log);
echo "Diagnóstico completado. Revisa el archivo $logFile para más detalles.";
?>
