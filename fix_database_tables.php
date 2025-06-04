<?php
// Fix for the rs_servicios CRUD issues

// 1. Fix the duracion field in mdlCrearRsServicio
require_once "model/servicios.model.php";

class ServiceFixer {
    public static function fixCrearRsServicio() {
        // Check if the function needs fixing
        $modelCode = file_get_contents('model/servicios.model.php');
        
        // Fix the insert query to include serv_tte
        $pattern = '/INSERT INTO rs_servicios \(\s*serv_codigo\s*,\s*serv_descripcion\s*,\s*serv_descripcion_factura\s*,\s*serv_monto\s*,\s*tserv_cod/';
        $replacement = 'INSERT INTO rs_servicios (
                serv_codigo, 
                serv_descripcion, 
                serv_descripcion_factura,
                serv_monto,
                serv_tte,
                tserv_cod';
        $modelCode = preg_replace($pattern, $replacement, $modelCode);
        
        // Fix the values to include :duracion
        $pattern = '/VALUES \(\s*:codigo\s*,\s*:descripcion\s*,\s*:descripcion_factura\s*,\s*:monto\s*,\s*:tipo_servicio/';
        $replacement = 'VALUES (
                :codigo,
                :descripcion,
                :descripcion_factura,
                :monto,
                :duracion,
                :tipo_servicio';
        $modelCode = preg_replace($pattern, $replacement, $modelCode);
        
        // Add the bindParam for serv_tte
        $pattern = '/\$stmt->bindParam\(":monto", \$datos\[\'serv_monto\'\], PDO::PARAM_STR\);\s*\$stmt->bindParam\(":tipo_servicio"/';
        $replacement = '$stmt->bindParam(":monto", $datos[\'serv_monto\'], PDO::PARAM_STR);
            $stmt->bindParam(":duracion", $datos[\'serv_tte\'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_servicio"';
        $modelCode = preg_replace($pattern, $replacement, $modelCode);
        
        // Fix the UPDATE query in mdlActualizarRsServicio
        $pattern = '/UPDATE rs_servicios SET\s*serv_codigo = :codigo\s*,\s*serv_descripcion = :descripcion\s*,\s*serv_descripcion_factura = :descripcion_factura\s*,\s*serv_monto = :monto\s*,\s*tserv_cod/';
        $replacement = 'UPDATE rs_servicios SET
                serv_codigo = :codigo,
                serv_descripcion = :descripcion,
                serv_descripcion_factura = :descripcion_factura,
                serv_monto = :monto,
                serv_tte = :duracion,
                tserv_cod';
        $modelCode = preg_replace($pattern, $replacement, $modelCode);
        
        // Add the bindParam for duracion in update function
        $pattern = '/\$stmt->bindParam\(":monto", \$datos\[\'serv_monto\'\], PDO::PARAM_STR\);\s*\$stmt->bindParam\(":tipo_servicio", \$datos\[\'tserv_cod\'\], PDO::PARAM_INT\);/';
        $replacement = '$stmt->bindParam(":monto", $datos[\'serv_monto\'], PDO::PARAM_STR);
            $stmt->bindParam(":duracion", $datos[\'serv_tte\'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_servicio", $datos[\'tserv_cod\'], PDO::PARAM_INT);';
        $modelCode = preg_replace($pattern, $replacement, $modelCode);
        
        // Write the updated code back to the file
        file_put_contents('model/servicios.model.php', $modelCode);
        
        return "Fixed model methods for rs_servicios";
    }
}

// Run the fixer
echo ServiceFixer::fixCrearRsServicio();
?>
