<?php
/**
 * Reparador específico para la función mdlObtenerReservasPorFecha
 */

// Activar todos los errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Reparador de la función mdlObtenerReservasPorFecha</h1>";

$modeloFile = "model/servicios.model.php";
$newFunction = '    static public function mdlObtenerReservasPorFecha($fecha, $doctorId = null, $estado = null) {
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
            
            // Asegurarse de que la fecha esté en formato YYYY-MM-DD
            if (!preg_match(\'/^\d{4}-\d{2}-\d{2}$/\', $fecha)) {
                $fechaFormateada = date(\'Y-m-d\', strtotime($fecha));
                error_log("mdlObtenerReservasPorFecha: Formato de fecha incorrecto ($fecha), reformateando a $fechaFormateada", 3, "c:/laragon/www/clinica/logs/reservas.log");
                $fecha = $fechaFormateada;
            }
            
            // Construir rango de fechas para la consulta
            $fechaInicio = $fecha . " 00:00:00";
            $fechaFin = $fecha . " 23:59:59";
            
            // Consulta adaptada según la estructura correcta de la base de datos
            $sql = "SELECT 
                sr.reserva_id,
                sr.servicio_id,
                sr.doctor_id,
                sr.paciente_id,
                sr.fecha_reserva,
                sr.hora_inicio,
                sr.hora_fin,
                sr.reserva_estado,
                sr.observaciones,
                sr.business_id,
                sr.created_at,
                sr.updated_at,
                sr.agenda_id,
                sr.sala_id,
                sr.tarifa_id,
                rp.first_name ||\' - \' || rp.last_name as doctor_nombre,
                rp2.first_name ||\' - \' || rp2.last_name as paciente_nombre,
                rs.serv_descripcion as servicio_nombre
            FROM servicios_reservas sr 
            INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
            INNER JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id 
            INNER JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id 
            WHERE sr.fecha_reserva BETWEEN :fecha_inicio AND :fecha_fin";
            
            if ($doctorId !== null) {
                $sql .= " AND sr.doctor_id = :doctor_id";
            }
            
            if ($estado !== null) {
                $sql .= " AND sr.reserva_estado = :estado";
            }
            
            $sql .= " ORDER BY sr.hora_inicio ASC";
            
            error_log("mdlObtenerReservasPorFecha: SQL=$sql", 3, "c:/laragon/www/clinica/logs/reservas.log");
            
            $stmt = Conexion::conectar()->prepare($sql);
            $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
            $stmt->bindParam(":fecha_fin", $fechaFin, PDO::PARAM_STR);
            
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

// Leer el archivo
if (file_exists($modeloFile)) {
    $content = file_get_contents($modeloFile);
    
    if ($content !== false) {
        // Buscar la función y reemplazarla
        $pattern = '/static\s+public\s+function\s+mdlObtenerReservasPorFecha\s*\([^\)]*\)\s*\{.*?\}\s*\}/s';
        
        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, $newFunction, $content);
            
            // Guardar el archivo
            if (file_put_contents($modeloFile, $newContent)) {
                echo "<p style='color:green;'>La función mdlObtenerReservasPorFecha ha sido actualizada correctamente.</p>";
            } else {
                echo "<p style='color:red;'>No se pudo guardar el archivo actualizado.</p>";
            }
        } else {
            echo "<p style='color:red;'>No se encontró la función mdlObtenerReservasPorFecha en el archivo.</p>";
        }
    } else {
        echo "<p style='color:red;'>No se pudo leer el contenido del archivo.</p>";
    }
} else {
    echo "<p style='color:red;'>El archivo del modelo no existe: $modeloFile</p>";
}

echo "<p><a href='verificacion_final_reservas.php'>Ir a la verificación final</a></p>";
?>
