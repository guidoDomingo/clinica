<?php
require_once "conexion.php"; // Archivo para la conexión a la base de datos

class ModelConsulta {
    public static function mdlSetConsulta($datos) {
        // Verificar si es una actualización o una nueva consulta
        if (isset($datos["id_consulta"]) && !empty($datos["id_consulta"])) {
            return self::mdlActualizarConsulta($datos);
        }
        
        // Preparar la consulta SQL para inserción
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO consultas (
                motivoscomunes, txtmotivo, visionod, visionoi, tensionod, tensionoi,
                consulta_textarea, receta_textarea, txtnota, proximaconsulta,
                whatsapptxt, email, id_user, id_reserva, id_persona
            ) VALUES (
                :motivoscomunes, :txtmotivo, :visionod, :visionoi, :tensionod, :tensionoi,
                :consulta_textarea, :receta_textarea, :txtnota, :proximaconsulta,
                :whatsapptxt, :email, :id_user, :id_reserva, :id_persona
            )"
        );

        // Bind de parámetros
        $stmt->bindParam(":motivoscomunes", $datos["motivoscomunes"], PDO::PARAM_STR);
        $stmt->bindParam(":txtmotivo", $datos["txtmotivo"], PDO::PARAM_STR);
        $stmt->bindParam(":visionod", $datos["visionod"], PDO::PARAM_STR);
        $stmt->bindParam(":visionoi", $datos["visionoi"], PDO::PARAM_STR);
        $stmt->bindParam(":tensionod", $datos["tensionod"], PDO::PARAM_STR);
        $stmt->bindParam(":tensionoi", $datos["tensionoi"], PDO::PARAM_STR);
        $stmt->bindParam(":consulta_textarea", $datos["consulta-textarea"], PDO::PARAM_STR);
        $stmt->bindParam(":receta_textarea", $datos["receta-textarea"], PDO::PARAM_STR);
        $stmt->bindParam(":txtnota", $datos["txtnota"], PDO::PARAM_STR);
        // Manejo de proximaconsulta (puede ser NULL)
        if (empty($datos["proximaconsulta"])) {
            $stmt->bindValue(":proximaconsulta", null, PDO::PARAM_NULL); // Asignar NULL
        } else {
            $stmt->bindParam(":proximaconsulta", $datos["proximaconsulta"], PDO::PARAM_STR);
        }
        $stmt->bindParam(":whatsapptxt", $datos["whatsapptxt"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
        $stmt->bindParam(":id_user", $datos["id_user"], PDO::PARAM_INT);
        $stmt->bindParam(":id_reserva", $datos["id_reserva"], PDO::PARAM_INT);
        $stmt->bindParam(":id_persona", $datos["idPersona"], PDO::PARAM_INT);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
    
    public static function mdlActualizarConsulta($datos) {
        // Preparar la consulta SQL para actualización
        $stmt = Conexion::conectar()->prepare(
            "UPDATE consultas SET 
                motivoscomunes = :motivoscomunes, 
                txtmotivo = :txtmotivo, 
                visionod = :visionod, 
                visionoi = :visionoi, 
                tensionod = :tensionod, 
                tensionoi = :tensionoi,
                consulta_textarea = :consulta_textarea, 
                receta_textarea = :receta_textarea, 
                txtnota = :txtnota, 
                proximaconsulta = :proximaconsulta,
                whatsapptxt = :whatsapptxt, 
                email = :email, 
                ultima_modificacion = NOW() 
            WHERE id_consulta = :id_consulta"
        );

        // Bind de parámetros
        $stmt->bindParam(":motivoscomunes", $datos["motivoscomunes"], PDO::PARAM_STR);
        $stmt->bindParam(":txtmotivo", $datos["txtmotivo"], PDO::PARAM_STR);
        $stmt->bindParam(":visionod", $datos["visionod"], PDO::PARAM_STR);
        $stmt->bindParam(":visionoi", $datos["visionoi"], PDO::PARAM_STR);
        $stmt->bindParam(":tensionod", $datos["tensionod"], PDO::PARAM_STR);
        $stmt->bindParam(":tensionoi", $datos["tensionoi"], PDO::PARAM_STR);
        $stmt->bindParam(":consulta_textarea", $datos["consulta-textarea"], PDO::PARAM_STR);
        $stmt->bindParam(":receta_textarea", $datos["receta-textarea"], PDO::PARAM_STR);
        $stmt->bindParam(":txtnota", $datos["txtnota"], PDO::PARAM_STR);
        // Manejo de proximaconsulta (puede ser NULL)
        if (empty($datos["proximaconsulta"])) {
            $stmt->bindValue(":proximaconsulta", null, PDO::PARAM_NULL); // Asignar NULL
        } else {
            $stmt->bindParam(":proximaconsulta", $datos["proximaconsulta"], PDO::PARAM_STR);
        }
        $stmt->bindParam(":whatsapptxt", $datos["whatsapptxt"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
        $stmt->bindParam(":id_consulta", $datos["id_consulta"], PDO::PARAM_INT);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            return "actualizado";
        } else {
            return "error";
        }
    }

    public static function mdlEliminarConsulta($id) {
        $stmt = Conexion::conectar()->prepare("DELETE FROM consultas WHERE id_consulta = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
    }
    public static function mdlGetConsultaResumen($datos) {
        $operacion = $datos["operacion"];
        $sql = "SELECT COUNT(id_consulta) AS cantidad_consultas,  to_char(MAX(fecha_registro::date),'dd/mm/yyyy') AS maxima_fecha_registro FROM   public.consultas where id_persona = :id_persona";
        if ($operacion === "resumenConsulta") {
            $stmt = Conexion::conectar()->prepare($sql);
        }
        
        $stmt->bindParam(":id_persona", $datos["id_persona"], PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function mdlGetConsultaPersona($persona) {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT id_consulta, motivoscomunes, txtmotivo, visionod, visionoi, tensionod, tensionoi, consulta_textarea, receta_textarea, txtnota, proximaconsulta, whatsapptxt, email, id_user, id_reserva, fecha_registro, ultima_modificacion, id_persona 
                FROM public.consultas WHERE id_persona = :id_persona ");
            $stmt->bindParam(":id_persona", $persona, PDO::PARAM_INT);
            $stmt->execute();
    
            $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (empty($consultas)) {
                return json_encode([
                    'status' => 'warning',
                    'message' => 'No se encontraron consultas para esta persona.'
                ]);
            } else {
                return json_encode([
                    'status' => 'success',
                    'data' => $consultas
                ]);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 'error',
                'message' => 'Error al obtener las consultas: ' . $e->getMessage()
            ]);
        }
    }
    
    public static function mdlGetDetalleConsulta($idConsulta) {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT id_consulta, motivoscomunes as motivo, txtmotivo, visionod, visionoi, tensionod, tensionoi, 
                consulta_textarea as diagnostico, receta_textarea, txtnota as observaciones, proximaconsulta, 
                whatsapptxt, email, id_user, id_reserva, fecha_registro, ultima_modificacion, id_persona 
                FROM public.consultas WHERE id_consulta = :id_consulta");
            $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $stmt->execute();
            
            $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (empty($consulta)) {
                return json_encode([
                    'status' => 'warning',
                    'message' => 'No se encontró la consulta solicitada.'
                ]);
            } else {
                // Devolver directamente los datos de la consulta para que el frontend pueda procesarlos
                return json_encode($consulta);
            }
        } catch (PDOException $e) {
            return json_encode([
                'status' => 'error',
                'message' => 'Error al obtener el detalle de la consulta: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Método para obtener todas las consultas con datos básicos del paciente
     * 
     * @return array Arreglo con todas las consultas y datos básicos de pacientes
     */
    public static function mdlGetAllConsultas() {
        try {
            // Logging para depuración
            $startTime = microtime(true);
            error_log("Iniciando consulta mdlGetAllConsultas");
            
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    c.id_consulta,
                    c.id_persona,
                    rh.document_number AS documento,
                    rh.first_name AS nombre,
                    rh.last_name AS apellido,
                    c.motivoscomunes,
                    c.consulta_textarea,
                    c.fecha_registro
                FROM public.consultas c
                JOIN public.rh_person rh ON c.id_persona = rh.person_id
                ORDER BY c.fecha_registro DESC
            ");
            
            $stmt->execute();
            $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000; // convertir a milisegundos
            error_log("Consulta mdlGetAllConsultas completada en {$executionTime}ms. Registros encontrados: " . count($consultas));
            
            // Si no hay datos, loguear para depuración
            if (empty($consultas)) {
                error_log("No se encontraron consultas en la base de datos");
            } else {
                // Mostrar un ejemplo del primer registro para verificación
                error_log("Muestra del primer registro: " . print_r($consultas[0], true));
            }
            
            return $consultas;
        } catch (PDOException $e) {
            error_log("Error en mdlGetAllConsultas: " . $e->getMessage() . " - SQL: " . $e->getCode());
            // Cambiado para evitar salida no JSON en la respuesta AJAX
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Método para obtener las consultas de un paciente específico con datos básicos
     * 
     * @param int $idPersona ID de la persona/paciente
     * @return array Arreglo con las consultas y datos básicos del paciente
     */
    public static function mdlGetConsultasByPaciente($idPersona) {
        try {
            // Logging para depuración
            $startTime = microtime(true);
            error_log("Iniciando consulta mdlGetConsultasByPaciente para ID: " . $idPersona);
            
            $stmt = Conexion::conectar()->prepare("
                SELECT 
                    c.id_consulta,
                    c.id_persona,
                    rh.document_number AS documento,
                    rh.first_name AS nombre,
                    rh.last_name AS apellido,
                    c.motivoscomunes,
                    c.consulta_textarea,
                    c.fecha_registro
                FROM public.consultas c
                JOIN public.rh_person rh ON c.id_persona = rh.person_id
                WHERE c.id_persona = :id_persona
                ORDER BY c.fecha_registro DESC
            ");
            
            $stmt->bindParam(":id_persona", $idPersona, PDO::PARAM_INT);
            $stmt->execute();
            $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000; // convertir a milisegundos
            error_log("Consulta mdlGetConsultasByPaciente completada en {$executionTime}ms. Registros encontrados: " . count($consultas));
            
            // Si no hay datos, loguear para depuración
            if (empty($consultas)) {
                error_log("No se encontraron consultas para el paciente con ID: " . $idPersona);
            } else {
                // Mostrar un ejemplo del primer registro para verificación
                error_log("Muestra del primer registro: " . print_r($consultas[0], true));
            }
            
            return $consultas;
        } catch (PDOException $e) {
            error_log("Error en mdlGetConsultasByPaciente: " . $e->getMessage() . " - SQL: " . $e->getCode());
            // Devolver error en formato consistente
            return ['error' => $e->getMessage()];
        }
    }
}
?>