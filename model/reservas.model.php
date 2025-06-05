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
     */
    public function obtenerReservaPorId($id) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    r.reserva_id as id,
                    r.paciente_id,
                    p.paciente_nombre || ' ' || p.paciente_apellido as nombre_paciente,
                    p.paciente_documento as documento_paciente,
                    p.paciente_telefono as telefono,
                    s.serv_descripcion as servicio,
                    m.medico_nombre || ' ' || m.medico_apellido as nombre_medico,
                    r.fecha_reserva as fecha,
                    r.hora_inicio as hora,
                    r.estado
                FROM 
                    reservas r
                LEFT JOIN 
                    pacientes p ON r.paciente_id = p.paciente_id
                LEFT JOIN 
                    rs_servicios s ON r.servicio_id = s.serv_id
                LEFT JOIN 
                    medicos m ON r.medico_id = m.medico_id
                WHERE 
                    r.reserva_id = :id"
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
