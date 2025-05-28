<?php
// Fix for servicios.model.php
// Temporary fix that should be placed in the model file

// Backup the old function
function create_fix_for_model() {
    require_once 'config/config.php';
    require_once 'model/conexion.php';
    
    // Simplest possible implementation to get basic data
    $fixContent = '
    static public function mdlObtenerReservasPorFecha($fecha, $doctorId = null, $estado = null) {
        try {
            error_log("mdlObtenerReservasPorFecha: Fecha=$fecha, DoctorID=" . ($doctorId ?? "null") . ", Estado=" . ($estado ?? "null"), 3, "c:/laragon/www/clinica/logs/reservas.log");
            
            // Verificar si existe la tabla de reservas
            $stmtCheck = Conexion::conectar()->prepare("SELECT to_regclass(\'public.servicios_reservas\')");
            $stmtCheck->execute();
            $tablaReservasExiste = $stmtCheck->fetchColumn();
            
            if (!$tablaReservasExiste) {
                error_log("mdlObtenerReservasPorFecha: La tabla servicios_reservas no existe", 3, "c:/laragon/www/clinica/logs/reservas.log");
                return [];
            }
            
            // Consulta básica sin joins complejos
            $sql = "SELECT 
                r.reserva_id,
                r.servicio_id,
                r.doctor_id,
                r.paciente_id,
                r.fecha_reserva,
                r.hora_inicio,
                r.hora_fin,
                r.reserva_estado as estado,
                r.observaciones,
                r.business_id,
                r.created_at,
                r.updated_at,
                r.agenda_id,
                r.sala_id,
                r.tarifa_id,
                \'Doctor \' || r.doctor_id as doctor_nombre,
                \'Paciente \' || r.paciente_id as paciente_nombre,
                \'Servicio \' || r.servicio_id as servicio_nombre
            FROM servicios_reservas r
            WHERE r.fecha_reserva = :fecha";
            
            if ($doctorId !== null) {
                $sql .= " AND r.doctor_id = :doctor_id";
            }
            
            if ($estado !== null) {
                $sql .= " AND r.reserva_estado = :estado";
            }
            
            $sql .= " ORDER BY r.hora_inicio ASC";
            
            error_log("mdlObtenerReservasPorFecha: SQL=$sql", 3, "c:/laragon/www/clinica/logs/reservas.log");
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            
            if ($doctorId !== null) {
                $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            }
            
            if ($estado !== null) {
                $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("mdlObtenerReservasPorFecha: Se encontraron " . count($reservas) . " reservas", 3, "c:/laragon/www/clinica/logs/reservas.log");
            if (count($reservas) > 0) {
                error_log("mdlObtenerReservasPorFecha: Primera reserva: " . json_encode($reservas[0]), 3, "c:/laragon/www/clinica/logs/reservas.log");
            }
            
            return $reservas;
        } catch (PDOException $e) {
            error_log("Error al obtener reservas por fecha: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/reservas.log");
            return [];
        }
    }';

    return $fixContent;
}

$modelFile = 'c:/laragon/www/clinica/model/servicios.model.php';

// Read the file content
$content = file_get_contents($modelFile);

// Find the function
$pattern = '/static\s+public\s+function\s+mdlObtenerReservasPorFecha\s*\([^\)]*\)\s*{[^}]+}/s';
if (preg_match($pattern, $content, $matches)) {
    $oldFunction = $matches[0];
    
    // Create new content
    $newContent = str_replace($oldFunction, create_fix_for_model(), $content);
    
    // Write the file
    if (file_put_contents($modelFile, $newContent)) {
        echo "¡Éxito! La función mdlObtenerReservasPorFecha ha sido actualizada.<br>";
    } else {
        echo "Error al escribir el archivo.<br>";
    }
} else {
    echo "No se pudo encontrar la función mdlObtenerReservasPorFecha en el archivo.<br>";
}

// Redirect to test page
echo "<p>Redirigiendo a la página de prueba...</p>";
echo '<script>setTimeout(function() { window.location.href = "depurador_reservas.php?verificar=1&fecha=2025-05-29"; }, 2000);</script>';
