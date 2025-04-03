<?php 
class Conexion{

    static public function conectar(){
        $contrasena = "admin";
        $usuario = "postgres";
        $nombreBaseDeDatos = "clinica";
        $rutaServidor = "localhost";
        $puerto = "5432";

        try {
            $link = new PDO("pgsql:host=$rutaServidor;port=$puerto;dbname=$nombreBaseDeDatos", $usuario, $contrasena);
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $link;
        } catch (Exception $e) {
            // Log the error with timestamp and details
            $errorMessage = "[" . date('Y-m-d H:i:s') . "] Database Error: " . $e->getMessage() . 
                          "\nFile: " . $e->getFile() . 
                          "\nLine: " . $e->getLine() . 
                          "\nTrace: " . $e->getTraceAsString();
            
            error_log($errorMessage, 3, "c:/laragon/www/clinica/logs/database.log");
            
            // You can customize the user-facing error message
            throw new Exception("Database connection error. Please try again later.");
        }
    }
}