<?php
/**
 * Herramienta para probar directamente la llamada Ajax
 * que genera los slots disponibles para un médico, servicio y fecha
 */

// Incluir los archivos necesarios
require_once "model/conexion.php";
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";

// Configurar cabeceras
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Obtener los parámetros
$servicioId = isset($_REQUEST['servicio_id']) ? intval($_REQUEST['servicio_id']) : 2;
$doctorId = isset($_REQUEST['doctor_id']) ? intval($_REQUEST['doctor_id']) : 14;
$fecha = isset($_REQUEST['fecha']) ? $_REQUEST['fecha'] : date('Y-m-d', strtotime('+1 day'));

// Registrar la solicitud
error_log("Solicitud de slots: servicio_id={$servicioId}, doctor_id={$doctorId}, fecha={$fecha}", 3, 'c:/laragon/www/clinica/logs/servicios.log');

try {
    // Llamar a la función directamente
    $slots = ModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
    
    // Devolver la respuesta
    echo json_encode([
        "status" => "success",
        "data" => $slots,
        "params" => [
            "servicio_id" => $servicioId,
            "doctor_id" => $doctorId,
            "fecha" => $fecha
        ],
        "count" => count($slots)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage(),
        "params" => [
            "servicio_id" => $servicioId,
            "doctor_id" => $doctorId,
            "fecha" => $fecha
        ]
    ]);
}
