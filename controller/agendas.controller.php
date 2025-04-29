<?php
/**
 * Archivo: agendas.controller.php
 * Descripción: Controlador para el módulo de agendas médicas
 */

class AgendasController {
    
    /**
     * Método para listar todas las agendas
     */
    public static function ctrListarAgendas() {
        $respuesta = AgendasModel::mdlListarAgendas();
        return $respuesta;
    }
    
    /**
     * Método para listar todos los bloqueos
     */
    public static function ctrListarBloqueos() {
        $respuesta = AgendasModel::mdlListarBloqueos();
        return $respuesta;
    }
    
    /**
     * Método para cargar médicos
     */
    public static function ctrCargarMedicos() {
        $respuesta = AgendasModel::mdlCargarMedicos();
        return $respuesta;
    }
    
    /**
     * Método para cargar consultorios
     */
    public static function ctrCargarConsultorios() {
        $respuesta = AgendasModel::mdlCargarConsultorios();
        return $respuesta;
    }
    
    /**
     * Método para cargar turnos
     */
    public static function ctrCargarTurnos() {
        $respuesta = AgendasModel::mdlCargarTurnos();
        return $respuesta;
    }
    
    /**
     * Método para cargar salas
     */
    public static function ctrCargarSalas() {
        $respuesta = AgendasModel::mdlCargarSalas();
        return $respuesta;
    }
    
    /**
     * Método para guardar una agenda
     */
    public static function ctrGuardarAgenda($datos) {
        // Validar datos
        if (empty($datos["medico_id"]) || empty($datos["dias"]) || 
            empty($datos["hora_inicio"]) || empty($datos["hora_fin"]) || 
            empty($datos["duracion_turno"]) || empty($datos["consultorio_id"])) {
            
            return array(
                "ok" => false,
                "mensaje" => "Todos los campos son obligatorios"
            );
        }
        
        // Validar que la hora de inicio sea menor a la hora de fin
        if (strtotime($datos["hora_inicio"]) >= strtotime($datos["hora_fin"])) {
            return array(
                "ok" => false,
                "mensaje" => "La hora de inicio debe ser menor a la hora de fin"
            );
        }
        
        // Validar que la duración del turno sea mayor a 0
        if (intval($datos["duracion_turno"]) <= 0) {
            return array(
                "ok" => false,
                "mensaje" => "La duración del turno debe ser mayor a 0"
            );
        }
        
        // Verificar si ya existe una agenda para el médico en los mismos días y horario
        $verificar = AgendasModel::mdlVerificarAgendaExistente($datos);
        
        if ($verificar) {
            return array(
                "ok" => false,
                "mensaje" => "Ya existe una agenda para este médico en los mismos días y horario"
            );
        }
        
        // Guardar agenda
        $respuesta = AgendasModel::mdlGuardarAgenda($datos);
        
        if ($respuesta == "ok") {
            return array(
                "ok" => true,
                "mensaje" => "Agenda guardada correctamente"
            );
        } else {
            // Extraer el mensaje de error si existe
            $mensaje = "Error al guardar agenda";
            if (strpos($respuesta, "error:") === 0) {
                $mensaje = substr($respuesta, 7); // Eliminar "error: " del inicio
            }
            
            return array(
                "ok" => false,
                "mensaje" => $mensaje
            );
        }
    }
    
    /**
     * Método para actualizar una agenda
     */
    public static function ctrActualizarAgenda($datos) {
        // Validar datos
        if (empty($datos["id"]) || empty($datos["medico_id"]) || empty($datos["dias"]) || 
            empty($datos["hora_inicio"]) || empty($datos["hora_fin"]) || 
            empty($datos["duracion_turno"]) || empty($datos["consultorio_id"])) {
            
            return array(
                "ok" => false,
                "mensaje" => "Todos los campos son obligatorios"
            );
        }
        
        // Validar que la hora de inicio sea menor a la hora de fin
        if (strtotime($datos["hora_inicio"]) >= strtotime($datos["hora_fin"])) {
            return array(
                "ok" => false,
                "mensaje" => "La hora de inicio debe ser menor a la hora de fin"
            );
        }
        
        // Validar que la duración del turno sea mayor a 0
        if (intval($datos["duracion_turno"]) <= 0) {
            return array(
                "ok" => false,
                "mensaje" => "La duración del turno debe ser mayor a 0"
            );
        }
        
        // Verificar si ya existe una agenda para el médico en los mismos días y horario (excluyendo la agenda actual)
        $verificar = AgendasModel::mdlVerificarAgendaExistente($datos, true);
        
        if ($verificar) {
            return array(
                "ok" => false,
                "mensaje" => "Ya existe una agenda para este médico en los mismos días y horario"
            );
        }
        
        // Actualizar agenda
        $respuesta = AgendasModel::mdlActualizarAgenda($datos);
        
        if ($respuesta == "ok") {
            return array(
                "ok" => true,
                "mensaje" => "Agenda actualizada correctamente"
            );
        } else {
            // Extraer el mensaje de error si existe
            $mensaje = "Error al actualizar agenda";
            if (strpos($respuesta, "error:") === 0) {
                $mensaje = substr($respuesta, 7); // Eliminar "error: " del inicio
            }
            
            return array(
                "ok" => false,
                "mensaje" => $mensaje
            );
        }
    }
    
    /**
     * Método para guardar un bloqueo
     */
    public static function ctrGuardarBloqueo($datos) {
        // Validar datos
        if (empty($datos["medico_id"]) || empty($datos["fecha_inicio"]) || 
            empty($datos["fecha_fin"]) || empty($datos["motivo"])) {
            
            return array(
                "ok" => false,
                "mensaje" => "Todos los campos son obligatorios"
            );
        }
        
        // Validar que la fecha de inicio sea menor o igual a la fecha de fin
        if (strtotime($datos["fecha_inicio"]) > strtotime($datos["fecha_fin"])) {
            return array(
                "ok" => false,
                "mensaje" => "La fecha de inicio debe ser menor o igual a la fecha de fin"
            );
        }
        
        // Guardar bloqueo
        $respuesta = AgendasModel::mdlGuardarBloqueo($datos);
        
        if ($respuesta == "ok") {
            return array(
                "ok" => true,
                "mensaje" => "Bloqueo guardado correctamente"
            );
        } else {
            return array(
                "ok" => false,
                "mensaje" => "Error al guardar bloqueo"
            );
        }
    }
    
    /**
     * Método para actualizar un bloqueo
     */
    public static function ctrActualizarBloqueo($datos) {
        // Validar datos
        if (empty($datos["id"]) || empty($datos["medico_id"]) || empty($datos["fecha_inicio"]) || 
            empty($datos["fecha_fin"]) || empty($datos["motivo"])) {
            
            return array(
                "ok" => false,
                "mensaje" => "Todos los campos son obligatorios"
            );
        }
        
        // Validar que la fecha de inicio sea menor o igual a la fecha de fin
        if (strtotime($datos["fecha_inicio"]) > strtotime($datos["fecha_fin"])) {
            return array(
                "ok" => false,
                "mensaje" => "La fecha de inicio debe ser menor o igual a la fecha de fin"
            );
        }
        
        // Actualizar bloqueo
        $respuesta = AgendasModel::mdlActualizarBloqueo($datos);
        
        if ($respuesta == "ok") {
            return array(
                "ok" => true,
                "mensaje" => "Bloqueo actualizado correctamente"
            );
        } else {
            return array(
                "ok" => false,
                "mensaje" => "Error al actualizar bloqueo"
            );
        }
    }
    
    /**
     * Método para obtener una agenda
     */
    public static function ctrObtenerAgenda($id) {
        if (empty($id)) {
            return array(
                "ok" => false,
                "mensaje" => "ID no válido"
            );
        }
        
        $respuesta = AgendasModel::mdlObtenerAgenda($id);
        
        if ($respuesta) {
            return array(
                "ok" => true,
                "datos" => $respuesta
            );
        } else {
            return array(
                "ok" => false,
                "mensaje" => "Agenda no encontrada"
            );
        }
    }
    
    /**
     * Método para obtener un bloqueo
     */
    public static function ctrObtenerBloqueo($id) {
        if (empty($id)) {
            return array(
                "ok" => false,
                "mensaje" => "ID no válido"
            );
        }
        
        $respuesta = AgendasModel::mdlObtenerBloqueo($id);
        
        if ($respuesta) {
            return array(
                "ok" => true,
                "datos" => $respuesta
            );
        } else {
            return array(
                "ok" => false,
                "mensaje" => "Bloqueo no encontrado"
            );
        }
    }
    
    /**
     * Método para eliminar una agenda
     */
    public static function ctrEliminarAgenda($id) {
        if (empty($id)) {
            return array(
                "ok" => false,
                "mensaje" => "ID no válido"
            );
        }
        
        // Verificar si la agenda tiene citas asociadas
        $tieneCitas = AgendasModel::mdlVerificarCitasAgenda($id);
        
        if ($tieneCitas) {
            return array(
                "ok" => false,
                "mensaje" => "No se puede eliminar la agenda porque tiene citas asociadas"
            );
        }
        
        $respuesta = AgendasModel::mdlEliminarAgenda($id);
        
        if ($respuesta == "ok") {
            return array(
                "ok" => true,
                "mensaje" => "Agenda eliminada correctamente"
            );
        } else {
            return array(
                "ok" => false,
                "mensaje" => "Error al eliminar agenda"
            );
        }
    }
    
    /**
     * Método para eliminar un bloqueo
     */
    public static function ctrEliminarBloqueo($id) {
        if (empty($id)) {
            return array(
                "ok" => false,
                "mensaje" => "ID no válido"
            );
        }
        
        $respuesta = AgendasModel::mdlEliminarBloqueo($id);
        
        if ($respuesta == "ok") {
            return array(
                "ok" => true,
                "mensaje" => "Bloqueo eliminado correctamente"
            );
        } else {
            return array(
                "ok" => false,
                "mensaje" => "Error al eliminar bloqueo"
            );
        }
    }
}