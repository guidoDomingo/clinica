<?php
require_once "conexion.php"; // Archivo para la conexión a la base de datos

class ModelArchivos {
    public static function mdlSetArchivo($datos) {   
        $obs = ""; 
        // Preparar la consulta SQL
        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO public.archivos(nombre_archivo, ruta_archivo,   id_usuario, id_persona, origen,  observaciones, tamano_archivo, tipo_archivo, checksum)	
            VALUES (:nombre_archivo, :ruta_archivo,   :id_usuario, :id_persona, :origen,  :observaciones, :tamano_archivo, :tipo_archivo, :checksum)"
        );
        // Bind de parámetros
        $stmt->bindParam(":id_persona", $datos["id_persona"], PDO::PARAM_STR);
        $stmt->bindParam(":nombre_archivo", $datos["nombre_archivo"], PDO::PARAM_STR);
        $stmt->bindParam(":tamano_archivo", $datos["tamano_archivo"], PDO::PARAM_STR);
        $stmt->bindParam(":ruta_archivo", $datos["ruta_archivo"], PDO::PARAM_STR);
        $stmt->bindParam(":origen", $datos["origen"], PDO::PARAM_STR);
        $stmt->bindParam(":id_usuario", $datos["id_usuario"], PDO::PARAM_STR);
        $stmt->bindParam(":checksum", $datos["checksum"], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_archivo", $datos["tipo_archivo"], PDO::PARAM_STR);
        $stmt->bindParam(":observaciones", $obs, PDO::PARAM_STR);
        // Ejecutar la consulta
        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
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
}       

