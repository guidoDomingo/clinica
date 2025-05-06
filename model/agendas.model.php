<?php
require_once "conexion.php";

class ModelAgendas {
    /**
     * Obtiene todas las agendas médicas
     * @return array Listado de agendas
     */
    static public function mdlObtenerAgendas() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT a.agenda_id, a.medico_id, a.agenda_descripcion, a.agenda_estado, 
                    d.first_name || ' ' || d.last_name AS nombre_medico
             FROM agendas_cabecera a
             INNER JOIN rh_doctors rd ON a.medico_id = rd.doctor_id
             INNER JOIN rh_person d ON rd.person_id = d.person_id
             ORDER BY a.agenda_id DESC"
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una agenda específica por ID
     * @param int $agendaId ID de la agenda
     * @return array Datos de la agenda
     */
    static public function mdlObtenerAgendaPorId($agendaId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT a.agenda_id, a.medico_id, a.agenda_descripcion, a.agenda_estado, 
                    d.first_name || ' ' || d.last_name AS nombre_medico
             FROM agendas_cabecera a
             INNER JOIN rh_doctors rd ON a.medico_id = rd.doctor_id
             INNER JOIN rh_person d ON rd.person_id = d.person_id
             WHERE a.agenda_id = :agenda_id"
        );

        $stmt->bindParam(":agenda_id", $agendaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene agendas por médico
     * @param int $medicoId ID del médico
     * @return array Listado de agendas del médico
     */
    static public function mdlObtenerAgendasPorMedico($medicoId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT a.agenda_id, a.medico_id, a.agenda_descripcion, a.agenda_estado, 
                    d.first_name || ' ' || d.last_name AS nombre_medico
             FROM agendas_cabecera a
             INNER JOIN rh_doctors rd ON a.medico_id = rd.doctor_id
             INNER JOIN rh_person d ON rd.person_id = d.person_id
             WHERE a.medico_id = :medico_id
             ORDER BY a.agenda_id DESC"
        );

        $stmt->bindParam(":medico_id", $medicoId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una nueva agenda
     * @param array $datos Datos de la agenda
     * @return mixed ID de la agenda creada o false en caso de error
     */
    static public function mdlCrearAgenda($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO agendas_cabecera (medico_id, agenda_descripcion, agenda_estado) 
                 VALUES (:medico_id, :agenda_descripcion, :agenda_estado) 
                 RETURNING agenda_id"
            );

            $stmt->bindParam(":medico_id", $datos["medico_id"], PDO::PARAM_INT);
            $stmt->bindParam(":agenda_descripcion", $datos["agenda_descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":agenda_estado", $datos["agenda_estado"], PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                return $resultado["agenda_id"];
            } else {
                return false;
            }
        } catch (Exception $e) {
            // Registrar el error
            error_log("Error al crear agenda: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Actualiza una agenda existente
     * @param array $datos Datos de la agenda
     * @return bool Resultado de la operación
     */
    static public function mdlActualizarAgenda($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE agendas_cabecera 
                 SET medico_id = :medico_id, 
                     agenda_descripcion = :agenda_descripcion, 
                     agenda_estado = :agenda_estado,
                     fecha_modificacion = CURRENT_TIMESTAMP
                 WHERE agenda_id = :agenda_id"
            );

            $stmt->bindParam(":agenda_id", $datos["agenda_id"], PDO::PARAM_INT);
            $stmt->bindParam(":medico_id", $datos["medico_id"], PDO::PARAM_INT);
            $stmt->bindParam(":agenda_descripcion", $datos["agenda_descripcion"], PDO::PARAM_STR);
            $stmt->bindParam(":agenda_estado", $datos["agenda_estado"], PDO::PARAM_BOOL);

            return $stmt->execute();
        } catch (Exception $e) {
            // Registrar el error
            error_log("Error al actualizar agenda: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Elimina una agenda
     * @param int $agendaId ID de la agenda
     * @return bool Resultado de la operación
     */
    static public function mdlEliminarAgenda($agendaId) {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM agendas_cabecera WHERE agenda_id = :agenda_id");
            $stmt->bindParam(":agenda_id", $agendaId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            // Registrar el error
            error_log("Error al eliminar agenda: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Obtiene los detalles de horarios de una agenda
     * @param int $agendaId ID de la agenda
     * @return array Listado de detalles de horarios
     */
    static public function mdlObtenerDetallesAgenda($agendaId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT d.detalle_id, d.agenda_id, d.turno_id, d.sala_id, d.dia_semana, 
                    d.hora_inicio, d.hora_fin, d.intervalo_minutos, d.cupo_maximo, d.detalle_estado,
                    t.turno_nombre AS nombre_turno, s.sala_nombre AS nombre_sala
             FROM agendas_detalle d
             INNER JOIN turnos t ON d.turno_id = t.turno_id
             INNER JOIN salas s ON d.sala_id = s.sala_id
             WHERE d.agenda_id = :agenda_id
             ORDER BY CASE d.dia_semana 
                        WHEN 'LUNES'::dia_semana_enum THEN 1
                        WHEN 'MARTES'::dia_semana_enum THEN 2
                        WHEN 'MIERCOLES'::dia_semana_enum THEN 3
                        WHEN 'JUEVES'::dia_semana_enum THEN 4
                        WHEN 'VIERNES'::dia_semana_enum THEN 5
                        WHEN 'SABADO'::dia_semana_enum THEN 6
                        WHEN 'DOMINGO'::dia_semana_enum THEN 7
                      END, d.hora_inicio"
        );

        $stmt->bindParam(":agenda_id", $agendaId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene un detalle específico de agenda por ID
     * @param int $detalleId ID del detalle
     * @return array Datos del detalle
     */
    static public function mdlObtenerDetalleAgenda($detalleId) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT d.detalle_id, d.agenda_id, d.turno_id, d.sala_id, d.dia_semana, 
                    d.hora_inicio, d.hora_fin, d.intervalo_minutos, d.cupo_maximo, d.detalle_estado,
                    t.turno_nombre AS nombre_turno, s.sala_nombre AS nombre_sala
             FROM agendas_detalle d
             INNER JOIN turnos t ON d.turno_id = t.turno_id
             INNER JOIN salas s ON d.sala_id = s.sala_id
             WHERE d.detalle_id = :detalle_id"
        );

        $stmt->bindParam(":detalle_id", $detalleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo detalle de horario para una agenda
     * @param array $datos Datos del horario
     * @return mixed ID del detalle creado o false en caso de error
     */
    static public function mdlCrearDetalleAgenda($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO agendas_detalle 
                 (agenda_id, turno_id, sala_id, dia_semana, hora_inicio, hora_fin, 
                  intervalo_minutos, cupo_maximo, detalle_estado) 
                 VALUES 
                 (:agenda_id, :turno_id, :sala_id, :dia_semana, :hora_inicio, :hora_fin, 
                  :intervalo_minutos, :cupo_maximo, :detalle_estado) 
                 RETURNING detalle_id"
            );

            $stmt->bindParam(":agenda_id", $datos["agenda_id"], PDO::PARAM_INT);
            $stmt->bindParam(":turno_id", $datos["turno_id"], PDO::PARAM_INT);
            $stmt->bindParam(":sala_id", $datos["sala_id"], PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $datos["dia_semana"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_inicio", $datos["hora_inicio"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $datos["hora_fin"], PDO::PARAM_STR);
            $stmt->bindParam(":intervalo_minutos", $datos["intervalo_minutos"], PDO::PARAM_INT);
            $stmt->bindParam(":cupo_maximo", $datos["cupo_maximo"], PDO::PARAM_INT);
            $stmt->bindParam(":detalle_estado", $datos["detalle_estado"], PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                return $resultado["detalle_id"];
            } else {
                return false;
            }
        } catch (Exception $e) {
            // Registrar el error
            error_log("Error al crear detalle de agenda: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Actualiza un detalle de horario existente
     * @param array $datos Datos del horario
     * @return bool Resultado de la operación
     */
    static public function mdlActualizarDetalleAgenda($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE agendas_detalle 
                 SET turno_id = :turno_id, 
                     sala_id = :sala_id, 
                     dia_semana = :dia_semana, 
                     hora_inicio = :hora_inicio, 
                     hora_fin = :hora_fin, 
                     intervalo_minutos = :intervalo_minutos, 
                     cupo_maximo = :cupo_maximo, 
                     detalle_estado = :detalle_estado,
                     fecha_modificacion = CURRENT_TIMESTAMP
                 WHERE detalle_id = :detalle_id"
            );

            $stmt->bindParam(":detalle_id", $datos["detalle_id"], PDO::PARAM_INT);
            $stmt->bindParam(":turno_id", $datos["turno_id"], PDO::PARAM_INT);
            $stmt->bindParam(":sala_id", $datos["sala_id"], PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $datos["dia_semana"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_inicio", $datos["hora_inicio"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $datos["hora_fin"], PDO::PARAM_STR);
            $stmt->bindParam(":intervalo_minutos", $datos["intervalo_minutos"], PDO::PARAM_INT);
            $stmt->bindParam(":cupo_maximo", $datos["cupo_maximo"], PDO::PARAM_INT);
            $stmt->bindParam(":detalle_estado", $datos["detalle_estado"], PDO::PARAM_BOOL);

            return $stmt->execute();
        } catch (Exception $e) {
            // Registrar el error
            error_log("Error al actualizar detalle de agenda: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Elimina un detalle de horario
     * @param int $detalleId ID del detalle
     * @return bool Resultado de la operación
     */
    static public function mdlEliminarDetalleAgenda($detalleId) {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM agendas_detalle WHERE detalle_id = :detalle_id");
            $stmt->bindParam(":detalle_id", $detalleId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            // Registrar el error
            error_log("Error al eliminar detalle de agenda: " . $e->getMessage(), 0);
            return false;
        }
    }

    /**
     * Obtiene todos los médicos disponibles
     * @return array Listado de médicos
     */
    static public function mdlObtenerMedicos() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT d.doctor_id, p.person_id, p.first_name || ' ' || p.last_name AS nombre_completo
             FROM rh_doctors d
             INNER JOIN rh_person p ON d.person_id = p.person_id
             ORDER BY p.first_name, p.last_name"
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los turnos disponibles
     * @return array Listado de turnos
     */
    static public function mdlObtenerTurnos() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT turno_id, turno_nombre, turno_descripcion
             FROM turnos
             WHERE turno_estado = true
             ORDER BY turno_nombre"
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las salas disponibles
     * @return array Listado de salas
     */
    static public function mdlObtenerSalas() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT sala_id, sala_codigo, sala_nombre, sala_descripcion
             FROM salas
             WHERE sala_estado = true
             ORDER BY sala_nombre"
        );

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}