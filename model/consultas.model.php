<?php
require_once "conexion.php"; // Archivo para la conexión a la base de datos

class ModelConsulta {
    public static function mdlSetConsulta($datos) {
        // Preparar la consulta SQL
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
        // $stmt->bindParam(":proximaconsulta", $datos["proximaconsulta"], PDO::PARAM_STR);
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
}
?>