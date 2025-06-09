<?php
/**
 * Script de prueba para verificar la inserción directa en la tabla de consultas
 * Este script intenta insertar directamente en la base de datos sin pasar por el modelo ni controlador
 */

// Incluir el archivo de logs detallado
require_once "logs/debug_guardar_detallado.php";

// Incluir la conexión a la base de datos
require_once "model/conexion.php";

// Comenzar el log
debug_detallado('PRUEBA', "Iniciando prueba de inserción directa en tabla consultas", [], 'info');

// Verificar la conexión a la base de datos
try {
    $db = Conexion::conectar();
    
    if ($db === null) {
        debug_detallado('PRUEBA', "No se pudo establecer conexión con la base de datos", [], 'error');
        die("Error: No se pudo establecer conexión con la base de datos");
    }
    
    debug_detallado('PRUEBA', "Conexión establecida correctamente", [], 'success');
    
    // Verificar la estructura de la tabla consultas
    try {
        $stmt = $db->prepare("
            SELECT column_name, data_type, character_maximum_length, is_nullable 
            FROM information_schema.columns 
            WHERE table_name = 'consultas'
            ORDER BY ordinal_position
        ");
        $stmt->execute();
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        debug_detallado('PRUEBA', "Estructura de la tabla consultas", ['columnas' => $columnas], 'info');
    } catch (PDOException $e) {
        debug_detallado('PRUEBA', "Error al verificar estructura de tabla", ['error' => $e->getMessage()], 'error');
    }
    
    // Buscar un paciente existente para la prueba
    try {
        $stmt = $db->prepare("SELECT person_id, first_name, last_name FROM rh_person LIMIT 1");
        $stmt->execute();
        $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$paciente) {
            debug_detallado('PRUEBA', "No se encontraron pacientes en la base de datos", [], 'error');
            die("Error: No se encontraron pacientes para usar en la prueba");
        }
        
        debug_detallado('PRUEBA', "Paciente encontrado para la prueba", [
            'person_id' => $paciente['person_id'],
            'nombre' => $paciente['first_name'] . ' ' . $paciente['last_name']
        ], 'success');
        
        $id_persona = $paciente['person_id'];
    } catch (PDOException $e) {
        debug_detallado('PRUEBA', "Error al buscar paciente", ['error' => $e->getMessage()], 'error');
        die("Error: No se pudo encontrar un paciente para la prueba: " . $e->getMessage());
    }
    
    // Intentar insertar una consulta directamente
    try {
        // Preparar la consulta SQL para inserción
        $stmt = $db->prepare(
            "INSERT INTO consultas (
                motivoscomunes, txtmotivo, visionod, visionoi, tensionod, tensionoi,
                consulta_textarea, receta_textarea, txtnota, proximaconsulta,
                whatsapptxt, email, id_user, id_reserva, id_persona
            ) VALUES (
                :motivoscomunes, :txtmotivo, :visionod, :visionoi, :tensionod, :tensionoi,
                :consulta_textarea, :receta_textarea, :txtnota, :proximaconsulta,
                :whatsapptxt, :email, :id_user, :id_reserva, :id_persona
            )"
        );

        // Datos de prueba
        $motivoscomunes = "Prueba directa";
        $txtmotivo = "Motivo de prueba";
        $visionod = "OD prueba";
        $visionoi = "OI prueba";
        $tensionod = "TOD prueba";
        $tensionoi = "TOI prueba";
        $consulta_textarea = "Texto de diagnóstico de prueba";
        $receta_textarea = "Receta de prueba";
        $txtnota = "Nota de prueba";
        $proximaconsulta = date("Y-m-d", strtotime("+1 month"));
        $whatsapptxt = "595983123456";
        $email = "test@example.com";
        $id_user = 1;
        $id_reserva = 0;
        
        // Bind de parámetros
        $stmt->bindParam(":motivoscomunes", $motivoscomunes, PDO::PARAM_STR);
        $stmt->bindParam(":txtmotivo", $txtmotivo, PDO::PARAM_STR);
        $stmt->bindParam(":visionod", $visionod, PDO::PARAM_STR);
        $stmt->bindParam(":visionoi", $visionoi, PDO::PARAM_STR);
        $stmt->bindParam(":tensionod", $tensionod, PDO::PARAM_STR);
        $stmt->bindParam(":tensionoi", $tensionoi, PDO::PARAM_STR);
        $stmt->bindParam(":consulta_textarea", $consulta_textarea, PDO::PARAM_STR);
        $stmt->bindParam(":receta_textarea", $receta_textarea, PDO::PARAM_STR);
        $stmt->bindParam(":txtnota", $txtnota, PDO::PARAM_STR);
        $stmt->bindParam(":proximaconsulta", $proximaconsulta, PDO::PARAM_STR);
        $stmt->bindParam(":whatsapptxt", $whatsapptxt, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":id_user", $id_user, PDO::PARAM_INT);
        $stmt->bindParam(":id_reserva", $id_reserva, PDO::PARAM_INT);
        $stmt->bindParam(":id_persona", $id_persona, PDO::PARAM_INT);
        
        // Ejecutar la consulta
        debug_detallado('PRUEBA', "Intentando ejecutar INSERT directo", [
            'id_persona' => $id_persona,
            'motivoscomunes' => $motivoscomunes
        ], 'info');
        
        if ($stmt->execute()) {
            $id_consulta = $db->lastInsertId();
            debug_detallado('PRUEBA', "INSERT directo exitoso", ['id_consulta' => $id_consulta], 'success');
            
            echo "<h1>¡Prueba exitosa!</h1>";
            echo "<p>Se ha insertado correctamente una consulta de prueba con ID: $id_consulta</p>";
            echo "<p>Paciente: {$paciente['first_name']} {$paciente['last_name']} (ID: {$paciente['person_id']})</p>";
        } else {
            $errorInfo = $stmt->errorInfo();
            debug_detallado('PRUEBA', "Error en INSERT directo", [
                'error_code' => $errorInfo[0],
                'sql_state' => $errorInfo[1],
                'mensaje' => $errorInfo[2]
            ], 'error');
            
            echo "<h1>Error en la prueba</h1>";
            echo "<p>Error SQL: " . $errorInfo[2] . "</p>";
        }
    } catch (PDOException $e) {
        debug_detallado('PRUEBA', "Excepción PDO en INSERT directo", [
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ], 'error');
        
        echo "<h1>Error en la prueba</h1>";
        echo "<p>Excepción PDO: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    debug_detallado('PRUEBA', "Excepción general en prueba", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], 'error');
    
    echo "<h1>Error en la prueba</h1>";
    echo "<p>Excepción: " . $e->getMessage() . "</p>";
}
