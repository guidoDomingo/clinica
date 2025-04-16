<?php
require_once "conexion.php";

class ModelPreformatos {
    /**
     * Obtiene todos los motivos comunes activos
     * @return array Arreglo con los motivos comunes
     */
    public static function mdlGetMotivosComunes() {
        try {
            error_log("[" . date('Y-m-d H:i:s') . "] Ejecutando consulta para obtener motivos comunes", 3, "c:/laragon/www/clinica/logs/database.log");
            $stmt = Conexion::conectar()->prepare(
                "SELECT id_motivo, nombre, descripcion 
                FROM motivos_comunes 
                WHERE activo = true 
                ORDER BY nombre ASC"
            );
            
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("[" . date('Y-m-d H:i:s') . "] Resultado de motivos comunes: " . json_encode($resultado), 3, "c:/laragon/www/clinica/logs/database.log");
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al obtener motivos comunes: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/database.log");
            return [];
        }
    }
    
    /**
     * Obtiene todos los preformatos activos de un tipo específico
     * @param string $tipo Tipo de preformato ('consulta' o 'receta')
     * @return array Arreglo con los preformatos
     */
    public static function mdlGetPreformatos($tipo) {
        try {
            error_log("[" . date('Y-m-d H:i:s') . "] Ejecutando consulta para obtener preformatos de tipo: " . $tipo, 3, "c:/laragon/www/clinica/logs/database.log");
            $stmt = Conexion::conectar()->prepare(
                "SELECT id_preformato, nombre, contenido 
                FROM preformatos 
                WHERE activo = true AND tipo = :tipo 
                ORDER BY nombre ASC"
            );
            
            $stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("[" . date('Y-m-d H:i:s') . "] Resultado de preformatos de tipo " . $tipo . ": " . json_encode($resultado), 3, "c:/laragon/www/clinica/logs/database.log");
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al obtener preformatos: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/database.log");
            return [];
        }
    }
    
    /**
     * Crea un nuevo motivo común
     * @param array $datos Datos del motivo común
     * @return string 'ok' si se creó correctamente, 'error' en caso contrario
     */
    public static function mdlCrearMotivoComun($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO motivos_comunes (nombre, descripcion, creado_por) 
                VALUES (:nombre, :descripcion, :creado_por)"
            );
            
            $stmt->bindParam(":nombre", $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos['descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(":creado_por", $datos['creado_por'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error al crear motivo común: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/database.log");
            return "error";
        }
    }
    
    /**
     * Crea un nuevo preformato
     * @param array $datos Datos del preformato
     * @return string 'ok' si se creó correctamente, 'error' en caso contrario
     */
    public static function mdlCrearPreformato($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO preformatos (nombre, contenido, tipo, creado_por) 
                VALUES (:nombre, :contenido, :tipo, :creado_por)"
            );
            
            $stmt->bindParam(":nombre", $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(":contenido", $datos['contenido'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo", $datos['tipo'], PDO::PARAM_STR);
            $stmt->bindParam(":creado_por", $datos['creado_por'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error al crear preformato: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/database.log");
            return "error";
        }
    }
}