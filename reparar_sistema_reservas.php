<?php
/**
 * Script para reparar la visualización de reservas
 * Este script corrige los problemas encontrados en la visualización de reservas:
 * 1. Corrige el SQL para usar los nombres correctos de las columnas
 * 2. Asegura que se usen los nombres correctos de las tablas y campos
 */

// Configuración de errores para mostrar todo durante la ejecución
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "model/conexion.php";

// Cabecera básica de HTML para mejor presentación
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reparación del Sistema de Reservas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .success {
            color: green;
            padding: 10px;
            background-color: #d4edda;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            color: red;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 4px;
            margin: 10px 0;
        }
        .warning {
            color: #856404;
            padding: 10px;
            background-color: #fff3cd;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            padding: 10px;
            background-color: #d1ecf1;
            border-radius: 4px;
            margin: 10px 0;
        }
        .nav {
            margin: 20px 0;
        }
        .nav a {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            margin-right: 10px;
            border-radius: 4px;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .step {
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .step h3 {
            margin-top: 0;
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <h1>Reparación del Sistema de Reservas</h1>
    
    <div class="nav">
        <a href="listar_todas_reservas.php">Ver todas las reservas</a>
        <a href="debug_js_reservas.php">Depurador de JS</a>
        <a href="diagnostico_reservas_completo.php">Diagnóstico completo</a>
        <a href="view/modules/servicios.php">Ver interfaz de servicios</a>
    </div>

<?php
// Función para mostrar mensajes de estado
function showStatus($message, $type = 'info') {
    echo "<div class='$type'>$message</div>";
}

// Paso 1: Verificar la conexión a la base de datos
echo "<div class='step'>";
echo "<h3>Paso 1: Verificar la conexión a la base de datos</h3>";

try {
    $db = Conexion::conectar();
    if ($db) {
        showStatus("Conexión a la base de datos exitosa.", "success");
    } else {
        showStatus("No se pudo conectar a la base de datos.", "error");
        exit;
    }
} catch (Exception $e) {
    showStatus("Error de conexión: " . $e->getMessage(), "error");
    exit;
}
echo "</div>";

// Paso 2: Verificar la tabla de servicios
echo "<div class='step'>";
echo "<h3>Paso 2: Verificar la tabla de servicios</h3>";

try {
    // Verificar si existe la tabla rs_servicios
    $stmtRs = $db->query("SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'rs_servicios')");
    $rsServiciosExists = $stmtRs->fetchColumn();
    
    // Verificar si existe la tabla servicios_medicos
    $stmtSm = $db->query("SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'servicios_medicos')");
    $serviciosMedicosExists = $stmtSm->fetchColumn();
    
    if ($rsServiciosExists) {
        showStatus("La tabla rs_servicios existe.", "success");
        
        // Obtener la estructura de rs_servicios
        $stmtCols = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'rs_servicios' ORDER BY ordinal_position");
        $columns = $stmtCols->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Columnas de rs_servicios:</p>";
        echo "<ul>";
        foreach ($columns as $col) {
            echo "<li>" . htmlspecialchars($col) . "</li>";
        }
        echo "</ul>";
        
        // Verificar columna id o servicio_id
        if (in_array('id', $columns)) {
            showStatus("La columna 'id' existe en rs_servicios.", "success");
            $idColumn = 'id';
        } elseif (in_array('servicio_id', $columns)) {
            showStatus("La columna 'servicio_id' existe en rs_servicios.", "success");
            $idColumn = 'servicio_id';
        } else {
            showStatus("No se encontró la columna id o servicio_id en rs_servicios.", "warning");
            $idColumn = 'id'; // Por defecto
        }
        
        // Verificar columna nombre
        if (in_array('nombre', $columns)) {
            showStatus("La columna 'nombre' existe en rs_servicios.", "success");
            $nombreColumn = 'nombre';
        } elseif (in_array('servicio_nombre', $columns)) {
            showStatus("La columna 'servicio_nombre' existe en rs_servicios.", "success");
            $nombreColumn = 'servicio_nombre';
        } else {
            showStatus("No se encontró la columna nombre o servicio_nombre en rs_servicios.", "warning");
            $nombreColumn = 'nombre'; // Por defecto
        }
    } else {
        showStatus("La tabla rs_servicios no existe.", "warning");
    }
    
    if ($serviciosMedicosExists) {
        showStatus("La tabla servicios_medicos existe.", "success");
        
        // Obtener la estructura de servicios_medicos
        $stmtCols = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'servicios_medicos' ORDER BY ordinal_position");
        $columns = $stmtCols->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Columnas de servicios_medicos:</p>";
        echo "<ul>";
        foreach ($columns as $col) {
            echo "<li>" . htmlspecialchars($col) . "</li>";
        }
        echo "</ul>";
        
        // Verificar columnas relevantes
        $smIdColumn = in_array('servicio_id', $columns) ? 'servicio_id' : 'id';
        $smNombreColumn = in_array('servicio_nombre', $columns) ? 'servicio_nombre' : 'nombre';
    } else {
        showStatus("La tabla servicios_medicos no existe.", "warning");
    }
    
    // Decidir qué tabla usar
    if ($rsServiciosExists) {
        $useTable = 'rs_servicios';
        $useIdColumn = $idColumn;
        $useNombreColumn = $nombreColumn;
    } elseif ($serviciosMedicosExists) {
        $useTable = 'servicios_medicos';
        $useIdColumn = $smIdColumn;
        $useNombreColumn = $smNombreColumn;
    } else {
        showStatus("No se encontró ninguna tabla de servicios.", "error");
        exit;
    }
    
    showStatus("Se utilizará la tabla $useTable con columna ID $useIdColumn y columna NOMBRE $useNombreColumn", "info");
} catch (Exception $e) {
    showStatus("Error al verificar tablas: " . $e->getMessage(), "error");
    exit;
}
echo "</div>";

// Paso 3: Reparar el SQL en el modelo
echo "<div class='step'>";
echo "<h3>Paso 3: Reparar el SQL en el modelo</h3>";

try {
    $modeloFile = "model/servicios.model.php";
    $modeloContent = file_get_contents($modeloFile);
    
    if ($modeloContent === false) {
        showStatus("No se pudo leer el archivo del modelo: $modeloFile", "error");
        exit;
    }
    
    // Crear SQL correcto basado en la estructura detectada
    $correctSql = "
            LEFT JOIN $useTable s ON r.servicio_id = s.$useIdColumn";
            
    // Reemplazar el SQL incorrecto
    $patterns = [
        "/LEFT JOIN rs_servicios s ON r\.servicio_id = s\.servicio_id/",
        "/LEFT JOIN rs_servicios s ON r\.servicio_id = s\.id/",
        "/LEFT JOIN servicios_medicos sm ON r\.servicio_id = sm\.servicio_id/"
    ];
    
    $newContent = $modeloContent;
    $replaced = false;
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $newContent)) {
            $newContent = preg_replace($pattern, $correctSql, $newContent);
            $replaced = true;
        }
    }
    
    if ($replaced) {
        // También ajustar referencia al nombre del servicio
        if ($useTable == 'rs_servicios') {
            $newContent = preg_replace("/COALESCE\(s\.nombre,/", "COALESCE(s.$useNombreColumn,", $newContent);
        } elseif ($useTable == 'servicios_medicos') {
            $newContent = preg_replace("/COALESCE\(s\.nombre,/", "COALESCE(sm.$useNombreColumn,", $newContent);
        }
        
        // Guardar el archivo
        if (file_put_contents($modeloFile, $newContent)) {
            showStatus("SQL actualizado exitosamente en $modeloFile", "success");
        } else {
            showStatus("No se pudo guardar el archivo $modeloFile", "error");
        }
    } else {
        showStatus("No se encontró el patrón de SQL para reemplazar. El modelo puede ya estar corregido.", "info");
    }
} catch (Exception $e) {
    showStatus("Error al reparar el SQL: " . $e->getMessage(), "error");
}
echo "</div>";

// Paso 4: Verificar la función JavaScript
echo "<div class='step'>";
echo "<h3>Paso 4: Verificar la función JavaScript en servicios.js</h3>";

try {
    $jsFile = "view/js/servicios.js";
    $jsContent = file_get_contents($jsFile);
    
    if ($jsContent === false) {
        showStatus("No se pudo leer el archivo JavaScript: $jsFile", "error");
    } else {
        // Verificar si contiene los patrones necesarios para manejar diferentes nombres de propiedades
        $patterns = [
            'reserva.estado_reserva',
            'reserva.doctor_nombre',
            'reserva.paciente_nombre',
            'reserva.servicio_nombre',
        ];
        
        $allFound = true;
        $missing = [];
        
        foreach ($patterns as $pattern) {
            if (strpos($jsContent, $pattern) === false) {
                $allFound = false;
                $missing[] = $pattern;
            }
        }
        
        if ($allFound) {
            showStatus("La función JavaScript maneja correctamente los nombres de campo.", "success");
        } else {
            showStatus("La función JavaScript no maneja algunos nombres de campo: " . implode(', ', $missing), "warning");
        }
    }
} catch (Exception $e) {
    showStatus("Error al verificar JavaScript: " . $e->getMessage(), "error");
}
echo "</div>";

// Paso 5: Probar la funcionalidad
echo "<div class='step'>";
echo "<h3>Paso 5: Verificar la función del modelo</h3>";

try {
    if (!class_exists('ModelServicios')) {
        require_once "model/servicios.model.php";
    }
    
    $fechaHoy = date('Y-m-d');
    $reservas = ModelServicios::mdlObtenerReservasPorFecha($fechaHoy);
    
    echo "<p>Probando la función mdlObtenerReservasPorFecha para la fecha: $fechaHoy</p>";
    
    if (is_array($reservas)) {
        if (count($reservas) > 0) {
            showStatus("La función devuelve " . count($reservas) . " reservas para la fecha actual.", "success");
            
            echo "<h4>Primera reserva:</h4>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valor</th></tr>";
            
            foreach ($reservas[0] as $key => $value) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($key) . "</td>";
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            showStatus("La función devuelve 0 reservas para la fecha actual.", "info");
            
            // Intentar con una fecha anterior para ver si hay datos
            $fechaAnterior = date('Y-m-d', strtotime('-1 day'));
            $reservasAnterior = ModelServicios::mdlObtenerReservasPorFecha($fechaAnterior);
            
            if (is_array($reservasAnterior) && count($reservasAnterior) > 0) {
                showStatus("Se encontraron " . count($reservasAnterior) . " reservas para la fecha $fechaAnterior", "success");
            } else {
                showStatus("No se encontraron reservas en fechas anteriores.", "info");
            }
        }
    } else {
        showStatus("La función no devuelve un array. Revise la implementación.", "error");
    }
} catch (Exception $e) {
    showStatus("Error al probar la función: " . $e->getMessage(), "error");
}
echo "</div>";

// Conclusión
echo "<div class='step'>";
echo "<h3>Conclusión y siguientes pasos</h3>";

echo "<p>La reparación ha sido completada. Los siguientes pasos son:</p>";
echo "<ol>";
echo "<li>Verifique que las reservas se muestren correctamente en la interfaz</li>";
echo "<li>Cree una reserva de prueba usando <a href='crear_reserva_hoy.php'>crear_reserva_hoy.php</a></li>";
echo "<li>Revise los logs en la carpeta logs/ para más detalles</li>";
echo "<li>Use el <a href='debug_js_reservas.php'>depurador JS</a> si sigue habiendo problemas</li>";
echo "</ol>";
echo "</div>";
?>
</body>
</html>
