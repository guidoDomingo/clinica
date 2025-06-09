<?php
/**
 * Modelo para gestionar reservas en la clínica
 */
require_once "conexion.php";

class ReservasModel {
    
    /**
     * Obtiene una reserva por su ID
     * @param int $id ID de la reserva
     * @return array|null Datos de la reserva o null si no se encuentra
     */    public function obtenerReservaPorId($id) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                sr.reserva_id as id,
                sr.paciente_id,
                COALESCE(rp2.first_name || ' - ' || rp2.last_name, 'Paciente #' || sr.paciente_id) as nombre_paciente,
                rp2.document_number  as documento_paciente,
                rp2.phone_number  as telefono,
                COALESCE(rs.serv_descripcion, 'No especificado') as servicio,
                COALESCE(rp.first_name || ' - ' || rp.last_name, 'Doctor #' || sr.doctor_id) as nombre_medico,
                sr.fecha_reserva as fecha,
                sr.hora_inicio as hora,
                sr.reserva_estado as estado,
                COALESCE(s.sala_nombre , 'No asignada') as sala_nombre,
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
                salas s ON ad.sala_id  = s.sala_id
            WHERE 
                sr.reserva_id = :id"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener reserva: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Actualiza el estado de una reserva
     * @param int $id ID de la reserva
     * @param string $estado Nuevo estado de la reserva
     * @return bool Éxito de la operación
     */
    public function actualizarEstado($id, $estado) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE reservas 
                 SET estado = :estado, 
                     fecha_modificacion = NOW() 
                 WHERE reserva_id = :id"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar estado de reserva: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registra el envío de confirmación para una reserva
     * @param int $id ID de la reserva
     * @param string $metodo Método de envío (whatsapp, email, etc)
     * @return bool Éxito de la operación
     */
    public function registrarConfirmacion($id, $metodo = 'whatsapp') {
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE reservas 
                 SET confirmacion_enviada = TRUE,
                     metodo_confirmacion = :metodo,
                     fecha_confirmacion = NOW() 
                 WHERE reserva_id = :id"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->bindParam(":metodo", $metodo, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al registrar confirmación: " . $e->getMessage());
            return false;
        }
    }
}
?>
