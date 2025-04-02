<?php
require_once "conexion.php";
class ModelRegister {
    public static function mdlSetRegister($datos) {
        // Preparar la consulta SQL
          $stmt = Conexion::conectar()->prepare(
            "INSERT INTO  usuario_registrados(nombres, email, telefono, pass_user, passsecure,  email_send, estado)	VALUES (:nombres, :email, :telefono, :pass_user, :passsecure,  :email_send, :estado)"
        );

        // Bind de parámetros
        $stmt->bindParam(":nombres", $datos["regName"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["regEmail"], PDO::PARAM_STR);
        $stmt->bindParam(":telefono", $datos["regTel"], PDO::PARAM_STR);
        $stmt->bindParam(":pass_user", $datos["regconfirmPassword"], PDO::PARAM_STR);
        $stmt->bindParam(":passsecure", $datos["securepass"], PDO::PARAM_STR);
        $stmt->bindParam(":email_send", $datos["sendemail"],  PDO::PARAM_BOOL);
        $stmt->bindParam(":estado", $datos["estado"], PDO::PARAM_STR);
        // Ejecutar la consulta
        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
        
    }
    public static function mdlUpdateRegister($datos) {
        // Preparar la consulta SQL
          $stmt = Conexion::conectar()->prepare(
            "UPDATE  usuario_registrados SET email_send = :email_send WHERE email = :email"
        );

        // Bind de parámetros
        
        $stmt->bindParam(":email", $datos["regEmail"], PDO::PARAM_STR); 
        $stmt->bindParam(":email_send", $datos["sendemail"],  PDO::PARAM_BOOL); 
        // Ejecutar la consulta
        if ($stmt->execute()) {
            return "ok";
        } else {
            return "error";
        }
        
    }
}