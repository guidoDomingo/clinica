<?php
/**
 * Archivo: agendas.model.php
 * Descripción: Modelo para el módulo de agendas médicas
 */

require_once "conexion.php";

class AgendasModel {
    
    /**
     * Método para listar todas las agendas
     */
    public static function mdlListarAgendas() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT a.id_sch_medical as id, a.doctor_id, a.office_id, a.shift_id, 
                    a.dia_semana, a.hora_inicio, a.hora_fin, 
                    CONCAT(p.first_name, ' ', p.last_name) as medico_nombre, 
                    o.office_name as consultorio_nombre,
                    s.shift_name as turno_nombre,
                    a.intervalo,
                    a.estado
             FROM sch_medical_hs a 
             INNER JOIN rh_doctors d ON a.doctor_id = d.doctor_id
             INNER JOIN rh_person p ON d.person_id = p.person_id 
             INNER JOIN sch_medical_offices o ON a.office_id = o.office_id 
             INNER JOIN sch_shifts s ON a.shift_id = s.shift_id
             ORDER BY p.last_name, p.first_name"
        );
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Método para listar todos los bloqueos
     */
    public static function mdlListarBloqueos() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT b.id, b.medico_id, b.fecha_inicio, b.fecha_fin, b.motivo, 
                    CONCAT(p.first_name, ' ', p.last_name) as medico_nombre 
             FROM medico_bloqueos b 
             INNER JOIN rh_person p ON b.medico_id = p.person_id 
             ORDER BY b.fecha_inicio DESC"
        );
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Método para cargar médicos
     */
    public static function mdlCargarMedicos() {
        $stmt = Conexion::conectar()->prepare(
            "select
                d.doctor_id as id,
                CONCAT(p.first_name, ' ', p.last_name) as nombre,
                e.nombre as especialidad
            from
                rh_doctors d
            inner join rh_person p on
                d.person_id = p.person_id
            inner join persona_especialidad pe  on
                p.person_id = pe.persona_id 
            inner join especialidades e on 
                pe.especialidad_id = e.especialidad_id 
            order by
                p.last_name,
                p.first_name"
        );
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Método para cargar consultorios
     */
    public static function mdlCargarConsultorios() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT office_id as id, office_name as nombre, office_floor as piso, office_number as numero 
             FROM sch_medical_offices 
             ORDER BY office_name"
        );
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Método para verificar si ya existe una agenda para el médico en los mismos días y horario
     */
    public static function mdlVerificarAgendaExistente($datos, $esActualizacion = false) {
        try {
            $conexion = Conexion::conectar();

            error_log("Datos recibidos: ". print_r($datos, true), 3, "c:/laragon/www/clinica/logs/database.log");
            
            // Verificar si la conexión fue exitosa
            if ($conexion === null) {
                error_log("Error en mdlVerificarAgendaExistente: No se pudo conectar a la base de datos", 3, "c:/laragon/www/clinica/logs/database.log");
                return false; // Devolver false para permitir continuar el proceso
            }
            
            $condicion = "";
            
            if ($esActualizacion) {
                $condicion = " AND id_sch_medical != :id";
            }
            
            $stmt = $conexion->prepare(
                "SELECT COUNT(*) as total 
                FROM sch_medical_hs 
                WHERE doctor_id = :medico_id 
                AND shift_id::text = ANY(string_to_array(:dias, ',')) 
                AND hora_inicio <= :hora_fin 
                AND hora_fin >= :hora_inicio " . $condicion
            );
            
            $stmt->bindParam(":medico_id", $datos["medico_id"], PDO::PARAM_INT);
            $stmt->bindParam(":dias", $datos["dias"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_inicio", $datos["hora_inicio"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $datos["hora_fin"], PDO::PARAM_STR);
            
            if ($esActualizacion) {
                $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return ($resultado["total"] > 0);
        } catch (PDOException $e) {
            // Registrar el error en el log
            error_log("Error en mdlVerificarAgendaExistente: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/database.log");
            return false; // Devolver false para permitir continuar el proceso
        } catch (Exception $e) {
            // Capturar cualquier otra excepción
            error_log("Excepción en mdlVerificarAgendaExistente: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/database.log");
            return false; // Devolver false para permitir continuar el proceso
        }
    }
    
    /**
     * Método para guardar una agenda
     */
    public static function mdlGuardarAgenda($datos) {
        try {
            $conexion = Conexion::conectar();

            error_log("Datos recibidos: " . print_r($datos, true), 3, "c:/laragon/www/clinica/logs/database.log");
            
            // Verificar si la conexión fue exitosa
            if ($conexion === null) {
                return "error: No se pudo conectar a la base de datos";
            }
            
            $stmt = $conexion->prepare(
                "INSERT INTO sch_medical_hs
                    (doctor_id, office_id, shift_id, dia_semana, hora_inicio, hora_fin, intervalo, estado) 
                 VALUES (:doctor_id, :office_id, :shift_id, :dia_semana, :hora_inicio, :hora_fin, :intervalo, :estado)"
            );
            
            $stmt->bindParam(":doctor_id", $datos["medico_id"], PDO::PARAM_INT);
            $stmt->bindParam(":office_id", $datos["consultorio_id"], PDO::PARAM_INT);
            $stmt->bindParam(":shift_id", $datos["turno_id"], PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $datos["dias"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_inicio", $datos["hora_inicio"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $datos["hora_fin"], PDO::PARAM_STR);
            $stmt->bindParam(":intervalo", $datos["duracion_turno"], PDO::PARAM_INT);
            $stmt->bindValue(":estado", $datos["estado"], PDO::PARAM_STR);
           
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            // Registrar el error en el log pero devolver un mensaje genérico
            error_log("Error en mdlGuardarAgenda: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/database.log");
            return "error: Error al guardar en la base de datos";
        } catch (Exception $e) {
            // Capturar cualquier otra excepción
            error_log("Excepción en mdlGuardarAgenda: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/database.log");
            return "error: Error inesperado al procesar la solicitud";
        }
    }
    
    /**
     * Método para actualizar una agenda
     */
    public static function mdlActualizarAgenda($datos) {
        try {
            $conexion = Conexion::conectar();
            
            // Verificar si la conexión fue exitosa
            if ($conexion === null) {
                return "error: No se pudo conectar a la base de datos";
            }
            
            $stmt = $conexion->prepare(
                "UPDATE sch_medical_hs 
                 SET doctor_id = :doctor_id, 
                     office_id = :office_id, 
                     shift_id = :shift_id, 
                     dia_semana = :dia_semana, 
                     hora_inicio = :hora_inicio, 
                     hora_fin = :hora_fin,
                     intervalo = :intervalo,    
                     estado = :estado
                 WHERE id_sch_medical = :id"
            );
            
            $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
            $stmt->bindParam(":doctor_id", $datos["medico_id"], PDO::PARAM_INT);
            $stmt->bindParam(":office_id", $datos["consultorio_id"], PDO::PARAM_INT);
            $stmt->bindParam(":shift_id", $datos["turno_id"], PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $datos["dias"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_inicio", $datos["hora_inicio"], PDO::PARAM_STR);
            $stmt->bindParam(":hora_fin", $datos["hora_fin"], PDO::PARAM_STR);
            $stmt->bindParam(":intervalo", $datos["duracion_turno"], PDO::PARAM_INT);
            $stmt->bindValue(":estado", $datos["estado"], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            // Registrar el error en el log pero devolver un mensaje genérico
            error_log("Error en mdlActualizarAgenda: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/database.log");
            return "error: Error al actualizar en la base de datos";
        } catch (Exception $e) {
            // Capturar cualquier otra excepción
            error_log("Excepción en mdlActualizarAgenda: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/database.log");
            return "error: Error inesperado al procesar la solicitud";
        }
    }
    
    /**
     * Método para guardar un bloqueo
     */
    public static function mdlGuardarBloqueo($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO medico_bloqueos (medico_id, fecha_inicio, fecha_fin, motivo) 
                 VALUES (:medico_id, :fecha_inicio, :fecha_fin, :motivo)"
            );
            
            $stmt->bindParam(":medico_id", $datos["medico_id"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_inicio", $datos["fecha_inicio"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_fin", $datos["fecha_fin"], PDO::PARAM_STR);
            $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            return "error: " . $e->getMessage();
        }
    }
    
    /**
     * Método para actualizar un bloqueo
     */
    public static function mdlActualizarBloqueo($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE medico_bloqueos 
                 SET medico_id = :medico_id, 
                     fecha_inicio = :fecha_inicio, 
                     fecha_fin = :fecha_fin, 
                     motivo = :motivo 
                 WHERE id = :id"
            );
            
            $stmt->bindParam(":id", $datos["id"], PDO::PARAM_INT);
            $stmt->bindParam(":medico_id", $datos["medico_id"], PDO::PARAM_INT);
            $stmt->bindParam(":fecha_inicio", $datos["fecha_inicio"], PDO::PARAM_STR);
            $stmt->bindParam(":fecha_fin", $datos["fecha_fin"], PDO::PARAM_STR);
            $stmt->bindParam(":motivo", $datos["motivo"], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            return "error: " . $e->getMessage();
        }
    }
    
    /**
     * Método para obtener una agenda
     */
    public static function mdlObtenerAgenda($id) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT a.id_sch_medical as id, a.doctor_id, a.office_id, a.shift_id, 
                    a.dia_semana, a.hora_inicio, a.hora_fin,
                    d.doctor_id as medico_id, o.office_id as consultorio_id, s.shift_id as turno_id,
                    CONCAT(p.first_name, ' ', p.last_name) as medico_nombre,
                    o.office_name as consultorio_nombre,
                    s.shift_name as turno_nombre,
                    a.intervalo,
                    a.estado
             FROM sch_medical_hs a
             INNER JOIN rh_doctors d ON a.doctor_id = d.doctor_id
             INNER JOIN rh_person p ON d.person_id = p.person_id
             INNER JOIN sch_medical_offices o ON a.office_id = o.office_id
             INNER JOIN sch_shifts s ON a.shift_id = s.shift_id
             WHERE a.id_sch_medical = :id"
        );
        
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Método para obtener un bloqueo
     */
    public static function mdlObtenerBloqueo($id) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM medico_bloqueos WHERE id = :id"
        );
        
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Método para verificar si una agenda tiene citas asociadas
     */
    public static function mdlVerificarCitasAgenda($id) {
        $stmt = Conexion::conectar()->prepare(
            "SELECT COUNT(*) as total 
             FROM sch_shifts 
             WHERE agenda_id = :id"
        );
        
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($resultado["total"] > 0);
    }
    
    /**
     * Método para eliminar una agenda
     */
    public static function mdlEliminarAgenda($id) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "DELETE FROM sch_medical_hs WHERE id_sch_medical = :id"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            return "error: " . $e->getMessage();
        }
    }
    
    /**
     * Método para eliminar un bloqueo
     */
    public static function mdlEliminarBloqueo($id) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "DELETE FROM medico_bloqueos WHERE id = :id"
            );
            
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            return "error: " . $e->getMessage();
        }
    }
    
    /**
     * Método para cargar turnos disponibles
     */
    public static function mdlCargarTurnos() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT shift_id as id, shift_name as nombre, business_id,
                    CURRENT_DATE as fecha, 
                    CURRENT_TIME as hora_inicio,
                    CURRENT_DATE as fecha_fallback,
                    CURRENT_TIME as hora_fallback
             FROM sch_shifts 
             ORDER BY shift_name"
        );
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Método para cargar salas disponibles
     */
    public static function mdlCargarSalas() {
        $stmt = Conexion::conectar()->prepare(
            "SELECT office_id as id, office_name as nombre, CONCAT(office_floor, ' - ', office_number) as descripcion 
             FROM sch_medical_offices 
             ORDER BY office_name"
        );
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}