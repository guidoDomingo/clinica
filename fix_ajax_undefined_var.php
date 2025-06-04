<?php
// Fix for the missing parameter in mdlCrearRsServicio
require_once "model/conexion.php";
require_once "model/servicios.model.php";

// Función detallada para ver exactamente qué está fallando
function debugServiciosFunction() {
    try {
        // Código actualizado para el modelo
        $modelCode = file_get_contents('model/servicios.model.php');
        
        // Buscar el patrón donde debería estar la línea bindParam para duracion
        $pattern = '$stmt->bindParam(":descripcion_factura", $datos[\'serv_descripcion\'], PDO::PARAM_STR); // Usamos la misma descripción
            $stmt->bindParam(":monto", $datos[\'serv_monto\'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_servicio", $datos[\'tserv_cod\'], PDO::PARAM_INT);';
        
        $replacement = '$stmt->bindParam(":descripcion_factura", $datos[\'serv_descripcion\'], PDO::PARAM_STR); // Usamos la misma descripción
            $stmt->bindParam(":monto", $datos[\'serv_monto\'], PDO::PARAM_STR);
            $stmt->bindParam(":duracion", $datos[\'serv_tte\'], PDO::PARAM_STR);
            $stmt->bindParam(":tipo_servicio", $datos[\'tserv_cod\'], PDO::PARAM_INT);';
        
        $modelCode = str_replace($pattern, $replacement, $modelCode);
        file_put_contents('model/servicios.model.php', $modelCode);
        
        echo "Se ha corregido la función mdlCrearRsServicio agregando el parámetro :duracion.";
    } catch(Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Ejecutar la función de depuración
debugServiciosFunction();
?>
