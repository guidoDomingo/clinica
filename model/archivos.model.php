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
        // Log para depuración
        $log_dir = "logs";
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        
        // Si tenemos el ID almacenado en la variable estática, lo devolvemos directamente
        if (isset(self::$ultimoIdArchivo)) {
            error_log(date('Y-m-d H:i:s') . " - Usando ID de archivo almacenado en variable: " . self::$ultimoIdArchivo . "\n", 3, "$log_dir/archivos.log");
            return self::$ultimoIdArchivo;
        }
        
        // Si no, lo buscamos en la base de datos como antes
        try {
            error_log(date('Y-m-d H:i:s') . " - Buscando último ID de archivo en la base de datos\n", 3, "$log_dir/archivos.log");
            
            $db = Conexion::conectar();
            if (!$db) {
                error_log(date('Y-m-d H:i:s') . " - Error: No se pudo conectar a la base de datos\n", 3, "$log_dir/archivos.log");
                return null;
            }
            
            $stmt = $db->prepare(
                "SELECT id_archivo FROM archivos ORDER BY id_archivo DESC LIMIT 1"
            );
            
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado && isset($resultado['id_archivo'])) {
                error_log(date('Y-m-d H:i:s') . " - Último ID de archivo encontrado: " . $resultado['id_archivo'] . "\n", 3, "$log_dir/archivos.log");
                // Actualizar la variable estática para futuros usos
                self::$ultimoIdArchivo = $resultado['id_archivo'];
                return $resultado['id_archivo'];
            }
            
            error_log(date('Y-m-d H:i:s') . " - No se encontró ningún archivo en la base de datos\n", 3, "$log_dir/archivos.log");
            return null;
        } catch (PDOException $e) {
            error_log(date('Y-m-d H:i:s') . " - Error en mdlGetUltimoIdArchivo: " . $e->getMessage() . "\n", 3, "$log_dir/archivos.log");
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
        // Log para depuración
        $log_dir = "logs";
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        error_log(date('Y-m-d H:i:s') . " - Intentando relacionar archivo ID: $idArchivo con consulta ID: $idConsulta\n", 3, "$log_dir/archivos.log");
        
        // Validación de entrada
        if (empty($idConsulta) || empty($idArchivo)) {
            error_log(date('Y-m-d H:i:s') . " - Error: ID de consulta o ID de archivo vacío\n", 3, "$log_dir/archivos.log");
            return "error: ID de consulta o ID de archivo vacío";
        }
        
        try {
            // Verificar si la consulta existe
            $db = Conexion::conectar();
            $checkStmt = $db->prepare("SELECT id_consulta FROM consultas WHERE id_consulta = :id_consulta");
            $checkStmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() == 0) {
                error_log(date('Y-m-d H:i:s') . " - Error: La consulta con ID: $idConsulta no existe\n", 3, "$log_dir/archivos.log");
                return "error: La consulta no existe";
            }
            
            // Verificar si el archivo existe
            $checkStmt = $db->prepare("SELECT id_archivo FROM archivos WHERE id_archivo = :id_archivo");
            $checkStmt->bindParam(":id_archivo", $idArchivo, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() == 0) {
                error_log(date('Y-m-d H:i:s') . " - Error: El archivo con ID: $idArchivo no existe\n", 3, "$log_dir/archivos.log");
                return "error: El archivo no existe";
            }
            
            // Verificar si la relación ya existe para evitar duplicados
            $checkStmt = $db->prepare(
                "SELECT id_archivo_consulta FROM archivos_consulta 
                 WHERE id_consulta = :id_consulta AND id_archivo = :id_archivo"
            );
            $checkStmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $checkStmt->bindParam(":id_archivo", $idArchivo, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                error_log(date('Y-m-d H:i:s') . " - Aviso: La relación entre archivo ID: $idArchivo y consulta ID: $idConsulta ya existe\n", 3, "$log_dir/archivos.log");
                return "ok"; // Ya existe, se considera exitoso
            }
            
            // Crear la relación
            $stmt = $db->prepare(
                "INSERT INTO archivos_consulta(id_consulta, id_archivo) VALUES(:id_consulta, :id_archivo)"
            );
            
            $stmt->bindParam(":id_consulta", $idConsulta, PDO::PARAM_INT);
            $stmt->bindParam(":id_archivo", $idArchivo, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                error_log(date('Y-m-d H:i:s') . " - Éxito: Archivo ID: $idArchivo relacionado correctamente con consulta ID: $idConsulta\n", 3, "$log_dir/archivos.log");
                return "ok";
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log(date('Y-m-d H:i:s') . " - Error SQL: " . $errorInfo[2] . "\n", 3, "$log_dir/archivos.log");
                return "error: " . $errorInfo[2];
            }
        } catch (PDOException $e) {
            error_log(date('Y-m-d H:i:s') . " - Excepción: " . $e->getMessage() . "\n", 3, "$log_dir/archivos.log");
            return "error: " . $e->getMessage();
        }
    }
}