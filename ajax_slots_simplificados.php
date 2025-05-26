<?php
/**
 * Endpoint AJAX para probar la generación simplificada de slots
 */

// Configurar encabezados y errores
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Incluir archivos necesarios
require_once "model/conexion.php";
require_once "model/servicios_slots_simplificado.model.php";

// Obtener parámetros
$doctorId = isset($_REQUEST['doctor_id']) ? intval($_REQUEST['doctor_id']) : 14;
$fecha = isset($_REQUEST['fecha']) ? $_REQUEST['fecha'] : date('Y-m-d', strtotime('+1 day'));

// Registrar la solicitud en los logs
error_log("Solicitud de slots simplificados: doctor_id={$doctorId}, fecha={$fecha}", 3, 'c:/laragon/www/clinica/logs/servicios.log');

try {
    // Obtener los slots disponibles
    $slots = ModelServiciosSimplificado::mdlGenerarSlotsSimple($doctorId, $fecha);
    
    // Devolver respuesta
    echo json_encode([
        "status" => "success",
        "message" => "Se generaron " . count($slots) . " slots disponibles",
        "data" => $slots,
        "count" => count($slots)
    ]);
} catch (Exception $e) {
    // En caso de error
    error_log("Error en AJAX slots simplificados: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/servicios.log');
    
    echo json_encode([
        "status" => "error",
        "message" => "Error al generar slots: " . $e->getMessage(),
        "data" => [],
        "count" => 0
    ]);
}
