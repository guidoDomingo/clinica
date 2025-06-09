<?php
/**
 * Script temporal para probar la conexión a la base de datos
 * y diagnosticar problemas de consultas SQL
 */

// Incluir el archivo de conexión
require_once 'config/config.php';
require_once 'model/conexion.php';
require_once 'logs/debug_guardar.php';

echo "<h1>Prueba de Conexión a la Base de Datos</h1>";

try {
    // Intentar obtener una conexión
    $conexion = Conexion::conectar();
    
    if ($conexion !== null) {
        echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos</p>";
        
        // Obtener información del servidor
        $info = [
            'BASE DE DATOS' => $conexion->getAttribute(PDO::ATTR_SERVER_INFO),
            'VERSIÓN' => $conexion->getAttribute(PDO::ATTR_SERVER_VERSION),
            'ESTADO' => $conexion->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            'DRIVER' => $conexion->getAttribute(PDO::ATTR_DRIVER_NAME),
        ];
        
        echo "<h2>Información de la Conexión:</h2>";
        echo "<ul>";
        foreach ($info as $key => $value) {
            if ($value) {
                echo "<li><strong>$key:</strong> $value</li>";
            }
        }
        echo "</ul>";
        
        // Probar consulta simple
        $stmt = $conexion->prepare("SELECT current_timestamp");
        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Consulta de prueba exitosa: Tiempo del servidor = " . $result['current_timestamp'] . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al ejecutar consulta simple</p>";
            print_r($stmt->errorInfo());
        }
        
        // Probar consulta a la tabla consultas
        echo "<h2>Probando consulta a la tabla 'consultas'</h2>";
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM consultas");
        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Consulta exitosa: Total de registros = " . $result['total'] . "</p>";
            
            // Obtener el último registro
            $stmt = $conexion->prepare("SELECT * FROM consultas ORDER BY id_consulta DESC LIMIT 1");
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                echo "<h3>Último registro de consultas:</h3>";
                $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<pre>";
                print_r($consulta);
                echo "</pre>";
            }
        } else {
            echo "<p style='color: red;'>❌ Error al consultar tabla consultas</p>";
            print_r($stmt->errorInfo());
        }
        
        // Probar consulta a la tabla rh_person
        echo "<h2>Probando consulta a la tabla 'rh_person'</h2>";
        $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM rh_person");
        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Consulta exitosa: Total de personas = " . $result['total'] . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Error al consultar tabla rh_person</p>";
            print_r($stmt->errorInfo());
        }
        
    } else {
        echo "<p style='color: red;'>❌ Error: La conexión devolvió NULL</p>";
        echo "<p>Revise los logs para más detalles.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de PDO: " . $e->getMessage() . "</p>";
    debug_log("Error de PDO en test_conexion.php", [
        "mensaje" => $e->getMessage(),
        "codigo" => $e->getCode(),
        "archivo" => $e->getFile(),
        "linea" => $e->getLine()
    ], "[TEST_DB]");
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>";
    debug_log("Error general en test_conexion.php", [
        "mensaje" => $e->getMessage(),
        "codigo" => $e->getCode(),
        "archivo" => $e->getFile(),
        "linea" => $e->getLine()
    ], "[TEST_DB]");
}

echo "<h2>Prueba de inserción simulada</h2>";
echo "<p>Vamos a simular una inserción sin ejecutarla realmente:</p>";

try {
    $conexion = Conexion::conectar();
    
    if ($conexion !== null) {
        // Construir consulta de prueba similar a la de guardar consulta
        $query = "
            INSERT INTO consultas (
                motivoscomunes, txtmotivo, visionod, visionoi, tensionod, tensionoi,
                consulta_textarea, receta_textarea, txtnota, proximaconsulta,
                whatsapptxt, email, id_user, id_reserva, id_persona
            ) VALUES (
                :motivoscomunes, :txtmotivo, :visionod, :visionoi, :tensionod, :tensionoi,
                :consulta_textarea, :receta_textarea, :txtnota, :proximaconsulta,
                :whatsapptxt, :email, :id_user, :id_reserva, :id_persona
            )";
            
        echo "<pre>" . htmlspecialchars($query) . "</pre>";
        
        // Preparar la consulta pero no ejecutarla
        $stmt = $conexion->prepare($query);
        
        echo "<p style='color: green;'>✅ Consulta preparada exitosamente</p>";
        
        // Verificar la estructura de la tabla consultas
        $stmt = $conexion->query("SELECT column_name, data_type, character_maximum_length 
                                 FROM information_schema.columns 
                                 WHERE table_name = 'consultas' 
                                 ORDER BY ordinal_position");
        
        echo "<h3>Estructura de la tabla consultas:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Columna</th><th>Tipo</th><th>Longitud</th></tr>";
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['column_name'] . "</td>";
            echo "<td>" . $row['data_type'] . "</td>";
            echo "<td>" . $row['character_maximum_length'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en prueba de inserción: " . $e->getMessage() . "</p>";
}
