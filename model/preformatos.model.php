<?php
require_once "conexion.php";

class ModelPreformatos {
    /**
     * Obtiene todos los motivos comunes activos
     * @return array Arreglo con los motivos comunes
     */
    public static function mdlGetMotivosComunes() {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT id_motivo, nombre, descripcion 
                FROM motivos_comunes 
                WHERE activo = true 
                ORDER BY nombre ASC"
            );
            
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al obtener motivos comunes: " . $e->getMessage());
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
            $stmt = Conexion::conectar()->prepare(
                "SELECT id_preformato, nombre, contenido 
                FROM preformatos 
                WHERE activo = true AND tipo = :tipo 
                ORDER BY nombre ASC"
            );
            
            $stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al obtener preformatos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene un preformato por su ID
     * @param int $idPreformato ID del preformato a obtener
     * @return array|false Arreglo con los datos del preformato o false si no existe
     */
    public static function mdlGetPreformatoById($idPreformato) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT id_preformato, nombre, contenido, tipo, creado_por 
                FROM preformatos 
                WHERE id_preformato = :id_preformato AND activo = true"
            );
            
            $stmt->bindParam(":id_preformato", $idPreformato, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error al obtener preformato por ID: " . $e->getMessage());
            return false;
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
            error_log("Error al crear motivo común: " . $e->getMessage());
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
            error_log("Error al crear preformato: " . $e->getMessage());
            return "error";
        }
    }
    
    /**
     * Actualiza un preformato existente
     * @param array $datos Datos del preformato
     * @return string 'ok' si se actualizó correctamente, 'error' en caso contrario
     */
    public static function mdlActualizarPreformato($datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE preformatos 
                SET nombre = :nombre, contenido = :contenido, tipo = :tipo, creado_por = :creado_por 
                WHERE id_preformato = :id_preformato"
            );
            
            $stmt->bindParam(":nombre", $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(":contenido", $datos['contenido'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo", $datos['tipo'], PDO::PARAM_STR);
            $stmt->bindParam(":creado_por", $datos['creado_por'], PDO::PARAM_INT);
            $stmt->bindParam(":id_preformato", $datos['id_preformato'], PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error al actualizar preformato: " . $e->getMessage());
            return "error";
        }
    }
    
    /**
     * Elimina un preformato (inactivación lógica)
     * @param int $idPreformato ID del preformato a eliminar
     * @return string 'ok' si se eliminó correctamente, 'error' en caso contrario
     */
    public static function mdlEliminarPreformato($idPreformato) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE preformatos 
                SET activo = false 
                WHERE id_preformato = :id_preformato"
            );
            
            $stmt->bindParam(":id_preformato", $idPreformato, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            error_log("Error al eliminar preformato: " . $e->getMessage());
            return "error";
        }
    }
}