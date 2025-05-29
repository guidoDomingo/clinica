<?php
/**
 * Script para generar diagnóstico directo y guardar resultado en un archivo
 */
ob_start(); // Iniciar buffer de salida

echo "<h1>Diagnóstico de Reservas</h1>";
echo "<p>Fecha y hora: " . date('Y-m-d H:i:s') . "</p>";

// Mostrar información de PHP
echo "<h2>Información de PHP</h2>";
echo "<p>Versión de PHP: " . phpversion() . "</p>";
echo "<p>Extensiones cargadas: " . implode(', ', get_loaded_extensions()) . "</p>";

try {
    // Cargar archivos necesarios
    require_once "model/conexion.php";
    
    echo "<h2>Conexión a la base de datos</h2>";
    
    if (!extension_loaded('pdo_pgsql')) {
        echo "<p>ERROR: La extensión pdo_pgsql no está habilitada</p>";
    } else {
        echo "<p>La extensión pdo_pgsql está habilitada</p>";
        
        $db = Conexion::conectar();
        
        if ($db === null) {
            echo "<p>ERROR: No se pudo establecer conexión con la base de datos</p>";
        } else {
            echo "<p>Conexión establecida correctamente</p>";
            
            // Listar tablas
            echo "<h2>Tablas en la base de datos</h2>";
            $tablas = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name")->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<ul>";
            foreach ($tablas as $tabla) {
                echo "<li>" . $tabla . "</li>";
            }
            echo "</ul>";
            
            // Verificar tabla servicios_reservas
            echo "<h2>Estructura de servicios_reservas</h2>";
            
            if (in_array('servicios_reservas', $tablas)) {
                echo "<p>La tabla servicios_reservas existe</p>";
                
                $sql = "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'servicios_reservas' ORDER BY ordinal_position";
                $columnas = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<ul>";
                foreach ($columnas as $col) {
                    echo "<li>" . $col['column_name'] . " (" . $col['data_type'] . ")</li>";
                }
                echo "</ul>";
                
                // Contar registros
                $sql = "SELECT COUNT(*) as total FROM servicios_reservas";
                $count = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
                
                echo "<p>Total de reservas en la tabla: " . $count['total'] . "</p>";
                
                // Listar algunas reservas
                if ($count['total'] > 0) {
                    echo "<h3>Últimas 5 reservas</h3>";
                    
                    $sql = "SELECT * FROM servicios_reservas ORDER BY reserva_id DESC LIMIT 5";
                    $reservas = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo "<table border='1'>";
                    echo "<tr>";
                    foreach ($columnas as $col) {
                        echo "<th>" . $col['column_name'] . "</th>";
                    }
                    echo "</tr>";
                    
                    foreach ($reservas as $r) {
                        echo "<tr>";
                        foreach ($columnas as $col) {
                            $colName = $col['column_name'];
                            echo "<td>" . (isset($r[$colName]) ? $r[$colName] : 'NULL') . "</td>";
                        }
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                    
                    // Comprobar reservas por fecha
                    echo "<h3>Reservas por fecha</h3>";
                    
                    $fechas = ['2025-05-28', '2025-05-29', '2025-06-01'];
                    
                    foreach ($fechas as $fecha) {
                        $sql = "SELECT COUNT(*) as total FROM servicios_reservas WHERE fecha_reserva = :fecha";
                        $stmt = $db->prepare($sql);
                        $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
                        $stmt->execute();
                        $count = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        echo "<p>Reservas para $fecha: " . $count['total'] . "</p>";
                    }
                }
            } else {
                echo "<p>ERROR: La tabla servicios_reservas no existe</p>";
            }
        }
    }
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>En el archivo: " . $e->getFile() . " línea " . $e->getLine() . "</p>";
}

$output = ob_get_clean(); // Obtener el contenido del buffer y limpiarlo

// Guardar en archivo
$file = 'diagnostico_resultado.html';
file_put_contents($file, $output);

echo "Diagnóstico completado. Resultados guardados en $file";
?>
