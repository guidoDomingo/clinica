<?php
require_once "../model/agendas.model.php";

class ControllerAgendas {
    /**
     * Obtiene todas las agendas médicas
     * @return array Listado de agendas
     */
    static public function ctrObtenerAgendas() {
        return ModelAgendas::mdlObtenerAgendas();
    }

    /**
     * Obtiene una agenda específica por ID
     * @param int $agendaId ID de la agenda
     * @return array Datos de la agenda
     */
    static public function ctrObtenerAgendaPorId($agendaId) {
        return ModelAgendas::mdlObtenerAgendaPorId($agendaId);
    }

    /**
     * Obtiene agendas por médico
     * @param int $medicoId ID del médico
     * @return array Listado de agendas del médico
     */
    static public function ctrObtenerAgendasPorMedico($medicoId) {
        return ModelAgendas::mdlObtenerAgendasPorMedico($medicoId);
    }

    /**
     * Crea o actualiza una agenda
     * @param array $datos Datos de la agenda
     * @return mixed ID de la agenda o false en caso de error
     */
    static public function ctrGuardarAgenda($datos) {
        // Validar datos obligatorios
        if (empty($datos["medico_id"])) {
            return ["error" => true, "mensaje" => "El médico es obligatorio"];
        }

        // Preparar datos para guardar
        $datosAgenda = [
            "medico_id" => $datos["medico_id"],
            "agenda_descripcion" => $datos["agenda_descripcion"] ?? "",
            "agenda_estado" => isset($datos["agenda_estado"]) ? $datos["agenda_estado"] : true
        ];

        // Si tiene ID, actualizar, sino crear
        if (!empty($datos["agenda_id"])) {
            $datosAgenda["agenda_id"] = $datos["agenda_id"];
            $resultado = ModelAgendas::mdlActualizarAgenda($datosAgenda);
            
            if ($resultado) {
                return ["error" => false, "agenda_id" => $datos["agenda_id"], "mensaje" => "Agenda actualizada correctamente"];
            } else {
                return ["error" => true, "mensaje" => "Error al actualizar la agenda"];
            }
        } else {
            $agendaId = ModelAgendas::mdlCrearAgenda($datosAgenda);
            
            if ($agendaId) {
                return ["error" => false, "agenda_id" => $agendaId, "mensaje" => "Agenda creada correctamente"];
            } else {
                return ["error" => true, "mensaje" => "Error al crear la agenda"];
            }
        }
    }

    /**
     * Elimina una agenda
     * @param int $agendaId ID de la agenda
     * @return array Resultado de la operación
     */
    static public function ctrEliminarAgenda($agendaId) {
        $resultado = ModelAgendas::mdlEliminarAgenda($agendaId);
        
        if ($resultado) {
            return ["error" => false, "mensaje" => "Agenda eliminada correctamente"];
        } else {
            return ["error" => true, "mensaje" => "Error al eliminar la agenda"];
        }
    }

    /**
     * Obtiene los detalles de horarios de una agenda
     * @param int $agendaId ID de la agenda
     * @return array Listado de horarios
     */
    static public function ctrObtenerDetallesAgenda($agendaId) {
        return ModelAgendas::mdlObtenerDetallesAgenda($agendaId);
    }
    
    /**
     * Obtiene un detalle específico de agenda por ID
     * @param int $detalleId ID del detalle
     * @return array Datos del detalle
     */
    static public function ctrObtenerDetalleAgenda($detalleId) {
        return ModelAgendas::mdlObtenerDetalleAgenda($detalleId);
    }

    public static function obtenerDiaSemana($numeroDia) {
        $dias = [
            1 => 'LUNES',
            2 => 'MARTES',
            3 => 'MIERCOLES',
            4 => 'JUEVES',
            5 => 'VIERNES',
            6 => 'SABADO',
            7 => 'DOMINGO',
        ];
    
        return $dias[$numeroDia] ?? 'DIA INVALIDO';
    }
    

    /**
     * Crea o actualiza un detalle de horario
     * @param array $datos Datos del horario
     * @return array Resultado de la operación
     */
    static public function ctrGuardarDetalleAgenda($datos) {
        // Validar datos obligatorios
        if (empty($datos["agenda_id"]) || empty($datos["turno_id"]) || 
            empty($datos["sala_id"]) || empty($datos["dia_semana"]) || 
            empty($datos["hora_inicio"]) || empty($datos["hora_fin"])) {
            return ["error" => true, "mensaje" => "Todos los campos marcados con * son obligatorios"];
        }

        // Validar que hora fin sea mayor a hora inicio
        if ($datos["hora_inicio"] >= $datos["hora_fin"]) {
            return ["error" => true, "mensaje" => "La hora de fin debe ser posterior a la hora de inicio"];
        }

        $dia_semana = ControllerAgendas::obtenerDiaSemana($datos["dia_semana"]);

        // Preparar datos para guardar
        $datosDetalle = [
            "agenda_id" => $datos["agenda_id"],
            "turno_id" => $datos["turno_id"],
            "sala_id" => $datos["sala_id"],
            "dia_semana" => $dia_semana,
            "hora_inicio" => $datos["hora_inicio"],
            "hora_fin" => $datos["hora_fin"],
            "intervalo_minutos" => isset($datos["intervalo_minutos"]) ? $datos["intervalo_minutos"] : 15,
            "cupo_maximo" => isset($datos["cupo_maximo"]) ? $datos["cupo_maximo"] : 1,
            "detalle_estado" => isset($datos["detalle_estado"]) ? $datos["detalle_estado"] : true
        ];

        error_log(json_encode($datosDetalle), 3, "c:/laragon/www/clinica/logs/database.log");

        // Si tiene ID, actualizar, sino crear
        if (!empty($datos["detalle_id"])) {
            $datosDetalle["detalle_id"] = $datos["detalle_id"];
            $resultado = ModelAgendas::mdlActualizarDetalleAgenda($datosDetalle);
            
            if ($resultado) {
                return ["error" => false, "detalle_id" => $datos["detalle_id"], "mensaje" => "Horario actualizado correctamente"];
            } else {
                return ["error" => true, "mensaje" => "Error al actualizar el horario"];
            }
        } else {
            $detalleId = ModelAgendas::mdlCrearDetalleAgenda($datosDetalle);
            
            if ($detalleId) {
                return ["error" => false, "detalle_id" => $detalleId, "mensaje" => "Horario creado correctamente"];
            } else {
                return ["error" => true, "mensaje" => "Error al crear el horario"];
            }
        }
    }

    /**
     * Elimina un detalle de horario
     * @param int $detalleId ID del detalle
     * @return array Resultado de la operación
     */
    static public function ctrEliminarDetalleAgenda($detalleId) {
        $resultado = ModelAgendas::mdlEliminarDetalleAgenda($detalleId);
        
        if ($resultado) {
            return ["error" => false, "mensaje" => "Horario eliminado correctamente"];
        } else {
            return ["error" => true, "mensaje" => "Error al eliminar el horario"];
        }
    }

    /**
     * Obtiene todos los médicos disponibles
     * @return array Listado de médicos
     */
    static public function ctrObtenerMedicos() {
        return ModelAgendas::mdlObtenerMedicos();
    }

    /**
     * Obtiene todos los turnos disponibles
     * @return array Listado de turnos
     */
    static public function ctrObtenerTurnos() {
        return ModelAgendas::mdlObtenerTurnos();
    }

    /**
     * Obtiene todas las salas disponibles
     * @return array Listado de salas
     */
    static public function ctrObtenerSalas() {
        return ModelAgendas::mdlObtenerSalas();
    }

    /**
     * Verifica si existe un horario duplicado
     * @param int $detalleId ID del detalle actual (para excluirlo de la verificación)
     * @param string $diaSemana Día de la semana
     * @param int $turnoId ID del turno
     * @param int $salaId ID de la sala
     * @param string $horaInicio Hora de inicio
     * @param string $horaFin Hora de fin
     * @return array Información del duplicado o false si no existe
     */
    static public function ctrVerificarHorarioDuplicado($detalleId, $diaSemana, $turnoId, $salaId, $horaInicio, $horaFin) {
        return ModelAgendas::mdlVerificarHorarioDuplicado($detalleId, $diaSemana, $turnoId, $salaId, $horaInicio, $horaFin);
    }
}