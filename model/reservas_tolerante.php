<?php
/**
 * Versión modificada del método obtenerReservaPorId que es más tolerante a datos faltantes
 * Esto es útil para solucionar el problema con reservas que no se pueden encontrar
 */

// Incluir la clase de conexión
require_once 'model/conexion.php';

/**
 * Obtiene una reserva por su ID de forma más tolerante
 * Utiliza LEFT JOIN para permitir datos faltantes en las tablas relacionadas
 */
function obtenerReservaPorIdTolerante($id) {
    try {
        $stmt = Conexion::conectar()->prepare(
            "SELECT 
                sr.reserva_id as id,
                sr.paciente_id,
                '' as nombre_paciente_directo,
                COALESCE(rp2.first_name || ' - ' || rp2.last_name, 'Paciente #' || sr.paciente_id) as nombre_paciente,
                'No disponible' as documento_paciente,
                '' as telefono,
                COALESCE(rs.serv_descripcion, 'No especificado') as servicio,
                COALESCE(rp.first_name || ' - ' || rp.last_name, 'Doctor #' || sr.doctor_id) as nombre_medico,
                sr.fecha_reserva as fecha,
                sr.hora_inicio as hora,
                sr.reserva_estado as estado,
                COALESCE(s.sala_nombre, 'No asignada') as sala_nombre,
                COALESCE(rs.serv_monto, 0) as serv_monto
            FROM 
                servicios_reservas sr
            LEFT JOIN 
                rh_doctors rd ON sr.doctor_id = rd.doctor_id
            LEFT JOIN 
                rh_person rp ON rd.person_id = rp.person_id
            LEFT JOIN 
                rh_person rp2 ON sr.paciente_id = rp2.person_id
            LEFT JOIN 
                rs_servicios rs ON sr.servicio_id = rs.serv_id
            LEFT JOIN 
                agendas_detalle ad ON sr.agenda_id = ad.detalle_id
            LEFT JOIN 
                salas s ON sr.sala_id = s.sala_id
            WHERE 
                sr.reserva_id = :id"
        );
        
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ?: null;
    } catch (PDOException $e) {
        error_log("Error al obtener reserva tolerante: " . $e->getMessage());
        return null;
    }
}
?>
