<?php
require_once "conexion.php"; // Archivo para la conexión a la base de datos

class ModelArchivos {
    // Variable estática para almacenar el último ID de archivo insertado
    private static $ultimoIdArchivo = null;
    public static function mdlSetArchivo($datos) {   
        $obs = ""; 
        try {
            // Verificar que id_persona sea válido
            if (!isset($datos["id_persona"]) || empty($datos["id_persona"])) {
                return "error: El ID de persona es requerido";
            }
            
            // Preparar la consulta SQL
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO public.archivos(nombre_archivo, ruta_archivo, id_usuario, id_persona, origen, observaciones, tamano_archivo, tipo_archivo, checksum)	
                VALUES (:nombre_archivo, :ruta_archivo, :id_usuario, :id_persona, :origen, :observaciones, :tamano_archivo, :tipo_archivo, :checksum) RETURNING id_archivo"
            );
            
            // Bind de parámetros
            $stmt->bindParam(":id_persona", $datos["id_persona"], PDO::PARAM_INT);
            $stmt->bindParam(":nombre_archivo", $datos["nombre_archivo"], PDO::PARAM_STR);
            $stmt->bindParam(":tamano_archivo", $datos["tamano_archivo"], PDO::PARAM_STR);
            $stmt->bindParam(":ruta_archivo", $datos["ruta_archivo"], PDO::PARAM_STR);
            $stmt->bindParam(":origen", $datos["origen"], PDO::PARAM_STR);
            $stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_INT);
            $stmt->bindParam(":checksum", $datos["checksum"], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_archivo", $datos["tipo_archivo"], PDO::PARAM_STR);
            $stmt->bindParam(":observaciones", $obs, PDO::PARAM_STR);
            
            // Ejecutar la consulta
            if ($stmt->execute()) {
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($resultado && isset($resultado['id_archivo'])) {
                    // Almacenar el ID en una variable estática de la clase para que pueda ser recuperado después
                    self::$ultimoIdArchivo = $resultado['id_archivo'];
                }
                return "ok";
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error SQL: " . $errorInfo[2]);
                return "error: " . $errorInfo[2];
            }
        } catch (PDOException $e) {
            error_log("Error en mdlSetArchivo: " . $e->getMessage());
            return "error: " . $e->getMessage();
        }
    }  
    public static function mdlGetArchivosMega($datos) {   
        $tipo = ""; 
        // Preparar la consulta SQL
        $stmt = Conexion::conectar()->prepare("SELECT  sum(ROUND((tamano_archivo / 1024.0) / 1024.0, 2)) AS utilizado, sum(ROUND((tamano_archivo / 1024.0) / 1024.0, 2))||' Mb de 100 Mb' as cuota FROM public.archivos  ");
        // Bind de parámetros
        // $stmt->bindParam(":id_persona", $datos["id_persona"], PDO::PARAM_STR);
        // Ejecutar la consulta
        $stmt -> execute();
		return $stmt -> fetch(PDO::FETCH_ASSOC);
        $stmt -> close();
        $stmt = null; 
    }
    
    /**
     * Obtiene los archivos asociados a una consulta específica
     * @param int $idConsulta ID de la consulta
     * @return array Arreglo con los datos de los archivos
     */
    public static function mdlGetArchivosPorConsulta($idConsulta) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT a.id_archivo, a.nombre_archivo, a.ruta_archivo, a.tamano_archivo, 
                a.tipo_archivo, a.fecha_creacion, ROUND((a.tamano_archivo / 1024.0) / 1024.0, 2) as tamano_mb 
                FROM archivos a 
                INNER JOIN archivos_consulta ac ON a.id_archivo = ac.id_archivo 
                WHERE ac.id_consulta = :id_consulta 
                ORDER BY a.fecha_creacion DESC"
            );
            
            $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }  
    
    /**
     * Obtiene el ID del último archivo insertado
     * @return int|null ID del archivo o null si hay error
     */
    public static function mdlGetUltimoIdArchivo() {
        // Si tenemos el ID almacenado en la variable estática, lo devolvemos directamente
        if (isset(self::$ultimoIdArchivo)) {
            return self::$ultimoIdArchivo;
        }
        
        // Si no, lo buscamos en la base de datos como antes
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT id_archivo FROM archivos ORDER BY id_archivo DESC LIMIT 1"
            );
            
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado && isset($resultado['id_archivo'])) {
                return $resultado['id_archivo'];
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error en mdlGetUltimoIdArchivo: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Relaciona un archivo con una consulta en la tabla archivos_consulta
     * @param int $idConsulta ID de la consulta
     * @param int $idArchivo ID del archivo
     * @return string "ok" si se realizó correctamente, "error" en caso contrario
     */
    public static function mdlRelacionarArchivoConsulta($idConsulta, $idArchivo) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO archivos_consulta(id_consulta, id_archivo) VALUES(:id_consulta, :id_archivo)"
            );
            
            $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $stmt->bindParam(":id_archivo", $idArchivo, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return "ok";
            } else {
                return "error";
            }
        } catch (PDOException $e) {
            return "error: " . $e->getMessage();
        }
    }
}