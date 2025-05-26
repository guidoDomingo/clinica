<?php 
class Conexion{

    static public function conectar(){
        $contrasena = "admin";
        $usuario = "postgres";
        $nombreBaseDeDatos = "clinica";
        $rutaServidor = "localhost";
        $puerto = "5432";

        try {
            // Check if PostgreSQL extension is available
            if (!extension_loaded('pdo_pgsql')) {
                error_log("[" . date('Y-m-d H:i:s') . "] Error: PDO PostgreSQL extension not loaded", 
                          3, "c:/laragon/www/clinica/logs/database.log");
                return null;
            }
            
            $link = new PDO("pgsql:host=$rutaServidor;port=$puerto;dbname=$nombreBaseDeDatos", $usuario, $contrasena);
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $link;
        } catch (Exception $e) {
            // Log the error with timestamp and details
            $errorMessage = "[" . date('Y-m-d H:i:s') . "] Database Error: " . $e->getMessage() . 
                          "\nFile: " . $e->getFile() . 
                          "\nLine: " . $e->getLine() . 
                          "\nTrace: " . $e->getTraceAsString();
            
            // Asegurar que exista el directorio de logs
            $logDir = "c:/laragon/www/clinica/logs";
            if (!file_exists($logDir)) {
                mkdir($logDir, 0777, true);
            }
            
            error_log($errorMessage, 3, "$logDir/database.log");
            
            // En lugar de lanzar una excepción que podría generar HTML,
            // devolvemos null y manejamos el error en el modelo
            return null;
        }
    }
}