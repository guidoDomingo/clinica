<?php
/**
 * Script de diagnóstico para verificar que la corrección de la tabla servicios funcione
 */

// Configurar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Servicios - Clínica</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        h1 {
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        pre {
            background: #f8f8f8;
            padding: 15px;
            border-left: 4px solid #3498db;
            overflow-x: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        td, th {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e9f7fe;
        }
        .error {
            color: #e74c3c;
            background: #ffeaea;
            padding: 10px;
            border-left: 4px solid #e74c3c;
            margin: 10px 0;
        }
        .warning {
            color: #f39c12;
            background: #fff7e6;
            padding: 10px;
            border-left: 4px solid #f39c12;
            margin: 10px 0;
        }
        .success {
            color: #27ae60;
            background: #eafff2;
            padding: 10px;
            border-left: 4px solid #27ae60;
            margin: 10px 0;
        }
        .info {
            background: #e9f7fe;
            padding: 10px;
            border-left: 4px solid #3498db;
            margin: 10px 0;
        }
        details {
            background: #fff;
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        summary {
            cursor: pointer;
            font-weight: bold;
            color: #3498db;
        }
        .params {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .execution-time {
            text-align: right;
            font-style: italic;
            margin-top: 20px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <h1>Diagnóstico de Servicios Médicos</h1>
    <div class="params">
        <h3>Parámetros de prueba</h3>
        <form method="get" action="">
            <table>
                <tr>
                    <td><label for="servicio_id">ID de Servicio:</label></td>
                    <td><input type="number" id="servicio_id" name="servicio_id" value="<?php echo isset($_GET['servicio_id']) ? intval($_GET['servicio_id']) : 2; ?>"></td>
                </tr>
                <tr>
                    <td><label for="doctor_id">ID de Doctor:</label></td>
                    <td><input type="number" id="doctor_id" name="doctor_id" value="<?php echo isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 14; ?>"></td>
                </tr>
                <tr>
                    <td><label for="fecha">Fecha:</label></td>
                    <td><input type="date" id="fecha" name="fecha" value="<?php echo isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d', strtotime('+1 day')); ?>"></td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" value="Ejecutar prueba"></td>
                </tr>
            </table>
        </form>
    </div>

<?php

// Incluir los archivos necesarios
require_once "model/conexion.php";
require_once "model/servicios.model.php";

// Parámetros de prueba
$servicioId = isset($_GET['servicio_id']) ? intval($_GET['servicio_id']) : 2;
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 14;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d', strtotime('+1 day'));

// Hora de inicio para medir rendimiento
$startTime = microtime(true);

// PASO 1: Diagnosticar el servicio
echo "<h2>Paso 1: Obtener información del servicio</h2>";
try {
    $servicio = ModelServicios::mdlObtenerServicioPorId($servicioId);
    echo "<div>";
    echo "Información del servicio (ID: $servicioId):</div>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    
    // Añadir cada campo disponible
    if (is_array($servicio)) {
        foreach ($servicio as $campo => $valor) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($campo) . "</td>";
            echo "<td>" . (is_null($valor) ? "<em>null</em>" : htmlspecialchars($valor)) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='2'><em>El servicio no devolvió datos</em></td></tr>";
    }
    echo "</table>";
      // Mostrar si este servicio fue generado por el fallback o es real
    $isFallback = isset($servicio['servicio_nombre']) && 
                  strpos($servicio['servicio_nombre'], 'Servicio #') === 0;
    if ($isFallback) {
        echo "<div class='warning'><strong>Nota:</strong> Este servicio está usando valores predeterminados (fallback).</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>Error al obtener servicio: " . $e->getMessage() . "</div>";
}

// PASO 2: Generar los slots usando el método corregido
echo "<h2>Paso 2: Generar slots disponibles</h2>";
try {
    $slots = ModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
    $totalSlots = count($slots);
    
    echo "<div>Slots generados: <strong>{$totalSlots}</strong></div>";
    
    if ($totalSlots > 0) {
        echo "<h3>Listado de slots disponibles:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>
                <th>#</th>
                <th>Hora Inicio</th>
                <th>Hora Fin</th>
                <th>Disponible</th>
              </tr>";
        
        foreach ($slots as $index => $slot) {
            // Determinar si se muestra el slot completo (primero, último y algunos intermedios)
            $showDetails = ($index === 0 || $index === $totalSlots - 1 || $index % 3 === 0);
            
            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>" . (isset($slot['hora_inicio']) ? $slot['hora_inicio'] : '-') . "</td>";
            echo "<td>" . (isset($slot['hora_fin']) ? $slot['hora_fin'] : '-') . "</td>";
            
            // Mostrar un indicador visual de disponibilidad
            $disponible = isset($slot['disponible']) ? $slot['disponible'] : false;
            $colorDisp = $disponible ? 'green' : 'red';
            $textoDisp = $disponible ? 'Sí' : 'No';
            echo "<td style='color:{$colorDisp};font-weight:bold;'>{$textoDisp}</td>";
            
            echo "</tr>";
            
            // Para el primer y último slot, mostrar detalles completos
            if ($showDetails && isset($slot) && is_array($slot)) {
                echo "<tr>";
                echo "<td colspan='4'>";
                echo "<details>";
                echo "<summary>Ver detalles del slot #" . ($index + 1) . "</summary>";
                echo "<pre style='margin:10px;padding:10px;background:#f5f5f5;'>";
                foreach ($slot as $campo => $valor) {
                    echo htmlspecialchars($campo) . ": " . htmlspecialchars(print_r($valor, true)) . "\n";
                }
                echo "</pre>";
                echo "</details>";
                echo "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";    } else {
        echo "<div class='warning'>No se generaron slots para la fecha seleccionada.</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>Error al generar slots: " . $e->getMessage() . "</div>";
}

// PASO 3: Verificar si la tabla rs_servicios existe
echo "<h2>Paso 3: Verificar estructura de la base de datos</h2>";
try {
    $stmt = Conexion::conectar()->query("SELECT COUNT(*) as total FROM rs_servicios");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<div class='success'>La tabla rs_servicios existe y contiene {$result['total']} registros.</div>";
      echo "<h3>Servicios disponibles en rs_servicios:</h3>";
    try {
        // Get table structure first
        $stmt = Conexion::conectar()->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'rs_servicios'");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<div class='info'>";
        echo "<strong>Estructura de la tabla:</strong><br>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li><code>" . htmlspecialchars($column) . "</code></li>";
        }
        echo "</ul>";
        echo "</div>";
        
        // Now fetch data
        $stmt = Conexion::conectar()->query("SELECT * FROM rs_servicios LIMIT 10");
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
          echo "<details open>";
        echo "<summary>Primeros 10 registros de la tabla rs_servicios</summary>";
        
        if (count($servicios) > 0) {
            echo "<table>";
            
            // First row with column names
            echo "<tr>";
            foreach ($columns as $column) {
                echo "<th>" . htmlspecialchars($column) . "</th>";
            }
            echo "</tr>";
            
            // Data rows
            foreach ($servicios as $serv) {
                echo "<tr>";
                foreach ($columns as $column) {
                    $value = array_key_exists($column, $serv) ? $serv[$column] : null;
                    echo "<td>" . (is_null($value) ? "<em>null</em>" : htmlspecialchars($value)) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='warning'>No hay registros para mostrar en la tabla rs_servicios.</div>";
        }
        echo "</details>";    } catch (Exception $e) {
        echo "<div class='error'>Error al mostrar estructura de tabla: " . $e->getMessage() . "</div>";
    }    // Verificar si existe la tabla servicios_medicos
    try {
        $stmt = Conexion::conectar()->query("SELECT COUNT(*) as total FROM servicios_medicos");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<div class='success'>La tabla servicios_medicos existe y contiene {$result['total']} registros.</div>";
    } catch (PDOException $e) {
        echo "<div class='warning'>La tabla servicios_medicos NO existe en la base de datos.<br>Error: " . $e->getMessage() . "</div>";
    }
} catch (PDOException $e) {
    echo "<div class='error'>Error al verificar la estructura de la base de datos: " . $e->getMessage() . "</div>";
}

// Calcular tiempo de ejecución
$endTime = microtime(true);
$executionTime = round(($endTime - $startTime) * 1000, 2);
echo "<div class='execution-time'>Tiempo de ejecución: <strong>{$executionTime} ms</strong></div>";

// PASO 4: Verificar implementación del modelo
echo "<h2>Paso 4: Verificación de implementación del modelo</h2>";
try {
    // Extraer el código fuente de mdlObtenerServicioPorId
    $modelFile = file_get_contents("model/servicios.model.php");
    if ($modelFile) {
        // Buscar la función mdlObtenerServicioPorId
        preg_match('/static\s+public\s+function\s+mdlObtenerServicioPorId\s*\(\s*\$servicioId\s*\)\s*\{.*?try\s*\{.*?FROM\s+([a-zA-Z0-9_]+)/s', $modelFile, $matches);
        
        if (!empty($matches) && isset($matches[1])) {
            $tableUsed = $matches[1];
            echo "<div class='info'>";
            echo "<strong>Tabla usada en el modelo:</strong> <code>{$tableUsed}</code>";
            
            if ($tableUsed == 'rs_servicios') {
                echo "<div class='success'><strong>✓</strong> La función mdlObtenerServicioPorId está usando la tabla correcta.</div>";
            } else {
                echo "<div class='warning'><strong>⚠</strong> La función mdlObtenerServicioPorId podría estar usando una tabla incorrecta: <code>{$tableUsed}</code>. Debería usar <code>rs_servicios</code>.</div>";
            }
            echo "</div>";
        } else {
            echo "<div class='warning'>No se pudo determinar qué tabla está usando el modelo.</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>Error al analizar el código del modelo: " . $e->getMessage() . "</div>";
}

// Enlaces de diagnóstico adicionales
echo "<h2>Enlaces de diagnóstico adicionales</h2>";
echo "<div class='info'>";
echo "<ul>";
echo "<li><a href='test_slots_simplificados.php?doctor_id={$doctorId}&fecha={$fecha}' target='_blank'>Probar generación de slots simplificada</a></li>";
echo "<li><a href='test_ajax_response.php?servicio_id={$servicioId}&doctor_id={$doctorId}&fecha={$fecha}' target='_blank'>Probar respuesta AJAX</a></li>";
echo "<li><a href='diagnostico_agenda_doctor.php?doctor_id={$doctorId}' target='_blank'>Diagnóstico de agenda del doctor</a></li>";
echo "</ul>";
echo "</div>";
?>
</body>
</html>
