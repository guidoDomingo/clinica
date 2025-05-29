<?php
/**
 * Diagnóstico completo para el sistema de reservas
 * Este script analiza todos los componentes relacionados con las reservas y genera un informe completo
 */

// Configuración de errores para mostrar todo durante el diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cabecera básica de HTML para mejor presentación
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Completo de Reservas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
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
        }
        .error {
            color: red;
        }
        .warning {
            color: orange;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        .nav {
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <h1>Diagnóstico Completo del Sistema de Reservas</h1>
    
    <div class="nav">
        <a href="listar_todas_reservas.php">Ver todas las reservas</a>
        <a href="api_test_reservas.php">Probar API</a>
        <a href="view/modules/servicios.php">Ver interfaz de servicios</a>
        <a href="crear_reserva_hoy.php">Crear reserva de prueba HOY</a>
    </div>

<?php
// Cargar los archivos necesarios
require_once "model/conexion.php";
require_once "model/servicios.model.php";
require_once "controller/servicios.controller.php";

// Función de utilidad para mostrar datos en formato legible
function mostrarDatos($datos, $titulo = null) {
    if ($titulo) echo "<h3>$titulo</h3>";
    
    if (empty($datos)) {
        echo "<p class='warning'>No hay datos para mostrar</p>";
        return;
    }
    
    if (is_array($datos)) {
        if (count($datos) > 0 && is_array($datos[0])) {
            // Es una lista de elementos, mostrar como tabla
            echo "<table>";
            // Cabeceras
            echo "<tr>";
            foreach (array_keys($datos[0]) as $key) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            
            // Datos
            foreach ($datos as $fila) {
                echo "<tr>";
                foreach ($fila as $valor) {
                    // Formatear valores según su tipo
                    if (is_array($valor) || is_object($valor)) {
                        $mostrar = "<pre>" . json_encode($valor, JSON_PRETTY_PRINT) . "</pre>";
                    } elseif (is_bool($valor)) {
                        $mostrar = $valor ? 'true' : 'false';
                    } elseif ($valor === null) {
                        $mostrar = "<em>null</em>";
                    } else {
                        $mostrar = htmlspecialchars((string)$valor);
                    }
                    echo "<td>$mostrar</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            // Es un único elemento asociativo
            echo "<pre>" . print_r($datos, true) . "</pre>";
        }
    } else {
        // No es un array
        echo "<pre>" . print_r($datos, true) . "</pre>";
    }
}

// 1. Verificar la conexión a la base de datos
echo "<h2>1. Verificación de la conexión a la base de datos</h2>";
try {
    $db = Conexion::conectar();
    if ($db instanceof PDO) {
        echo "<p class='success'>✓ Conexión a la base de datos establecida correctamente</p>";
        
        // Verificar la extensión pgsql
        if (extension_loaded('pgsql') && extension_loaded('pdo_pgsql')) {
            echo "<p class='success'>✓ Extensión PostgreSQL cargada correctamente</p>";
        } else {
            echo "<p class='error'>✗ Extensión PostgreSQL no está cargada</p>";
        }
    } else {
        echo "<p class='error'>✗ Error al conectar con la base de datos</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error de conexión: " . $e->getMessage() . "</p>";
}

// 2. Examinar la estructura de la tabla de reservas
echo "<h2>2. Estructura de la tabla de reservas</h2>";
try {
    $sql = "SELECT column_name, data_type, is_nullable 
            FROM information_schema.columns 
            WHERE table_name = 'servicios_reservas' 
            ORDER BY ordinal_position";
    $stmt = $db->query($sql);
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($columnas) > 0) {
        echo "<p class='success'>✓ Tabla servicios_reservas existe</p>";
        mostrarDatos($columnas, "Columnas de la tabla servicios_reservas");
        
        // Verificar columna específica de estado
        $estadoEncontrado = false;
        $nombreColumnaEstado = "";
        foreach ($columnas as $columna) {
            if ($columna['column_name'] === 'estado_reserva') {
                $estadoEncontrado = true;
                $nombreColumnaEstado = 'estado_reserva';
                break;
            }
            if ($columna['column_name'] === 'reserva_estado') {
                $estadoEncontrado = true;
                $nombreColumnaEstado = 'reserva_estado';
                break;
            }
        }
        
        if ($estadoEncontrado) {
            echo "<p class='success'>✓ Columna de estado encontrada: " . $nombreColumnaEstado . "</p>";
        } else {
            echo "<p class='error'>✗ No se encontró la columna de estado (estado_reserva o reserva_estado)</p>";
        }
    } else {
        echo "<p class='error'>✗ La tabla servicios_reservas no existe o está vacía</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error al consultar la estructura: " . $e->getMessage() . "</p>";
}

// 3. Contar reservas totales en la tabla
echo "<h2>3. Conteo total de reservas</h2>";
try {
    $sql = "SELECT COUNT(*) as total FROM servicios_reservas";
    $stmt = $db->query($sql);
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Total de reservas en la base de datos: <strong>" . $total['total'] . "</strong></p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Error al contar reservas: " . $e->getMessage() . "</p>";
}

// 4. Obtener reservas para hoy usando el modelo directamente
echo "<h2>4. Reservas para HOY usando el modelo</h2>";
$fechaHoy = date('Y-m-d');
echo "<p>Fecha de hoy: $fechaHoy</p>";

try {
    $reservasHoy = ModelServicios::mdlObtenerReservasPorFecha($fechaHoy);
    mostrarDatos($reservasHoy, "Reservas para hoy desde el modelo");
} catch (Exception $e) {
    echo "<p class='error'>✗ Error al obtener reservas de hoy desde el modelo: " . $e->getMessage() . "</p>";
}

// 5. Obtener reservas para hoy usando el controlador
echo "<h2>5. Reservas para HOY usando el controlador</h2>";
try {
    $reservasControlador = ControladorServicios::ctrObtenerReservasPorFecha($fechaHoy);
    mostrarDatos($reservasControlador, "Reservas para hoy desde el controlador");
} catch (Exception $e) {
    echo "<p class='error'>✗ Error al obtener reservas de hoy desde el controlador: " . $e->getMessage() . "</p>";
}

// 6. Crear una reserva para mañana y verificar que se guardó correctamente
echo "<h2>6. Crear una reserva de prueba para mañana</h2>";

// Fecha para mañana
$fechaManana = date('Y-m-d', strtotime('+1 day'));
echo "<p>Fecha para mañana: $fechaManana</p>";

// Conseguir un doctor y paciente para la prueba
try {
    // Obtener un doctor disponible
    $stmtDoctor = $db->query("SELECT doctor_id FROM rh_doctors LIMIT 1");
    $doctor = $stmtDoctor->fetch(PDO::FETCH_ASSOC);
    
    // Obtener un paciente disponible (puede ser de cualquier tabla según la estructura)
    $stmtPaciente = $db->query("SELECT person_id FROM rh_person LIMIT 1");
    $paciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC);
    
    // Obtener un servicio disponible
    $stmtServicio = $db->query("SELECT servicio_id FROM rs_servicios LIMIT 1");
    $servicio = $stmtServicio->fetch(PDO::FETCH_ASSOC);
    
    if ($doctor && $paciente && $servicio) {
        $doctorId = $doctor['doctor_id'];
        $pacienteId = $paciente['person_id']; 
        $servicioId = $servicio['servicio_id'];
        
        // Datos para la reserva
        $datosReserva = [
            'servicio_id' => $servicioId,
            'doctor_id' => $doctorId,
            'paciente_id' => $pacienteId,
            'fecha_reserva' => $fechaManana,
            'hora_inicio' => '10:00:00',
            'hora_fin' => '10:30:00',
            'observaciones' => 'Reserva de prueba creada por diagnóstico',
            'estado_reserva' => 'PENDIENTE',
            'business_id' => 1
        ];
        
        echo "<p>Intentando crear reserva con datos:</p>";
        echo "<pre>" . print_r($datosReserva, true) . "</pre>";
        
        // Crear la reserva usando el controlador
        $resultado = ControladorServicios::ctrCrearReserva($datosReserva);
        
        if (isset($resultado['error']) && $resultado['error']) {
            echo "<p class='error'>✗ Error al crear la reserva: " . $resultado['mensaje'] . "</p>";
        } else {
            echo "<p class='success'>✓ Reserva creada exitosamente</p>";
            
            // Verificar que la reserva se creó correctamente
            $reservasManana = ModelServicios::mdlObtenerReservasPorFecha($fechaManana);
            mostrarDatos($reservasManana, "Reservas para mañana (verificación)");
        }
    } else {
        echo "<p class='warning'>⚠ No se pudo crear la reserva porque faltan datos necesarios (doctor, paciente o servicio)</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error durante la creación de reserva: " . $e->getMessage() . "</p>";
}

// 7. Examinar el SQL en el modelo
echo "<h2>7. Análisis del SQL utilizado en el modelo</h2>";

// Extraer la consulta SQL del modelo
$modeloFile = file_get_contents("model/servicios.model.php");
if (preg_match('/mdlObtenerReservasPorFecha.*?\$sql\s*=\s*"(.*?)WHERE/s', $modeloFile, $matches)) {
    $sqlFragment = $matches[1];
    echo "<p>Fragmento de la consulta SQL encontrada en el modelo:</p>";
    echo "<pre>" . htmlspecialchars($sqlFragment) . "</pre>";
    
    // Verificar si la consulta incluye los JOIN correctos
    if (strpos($sqlFragment, 'LEFT JOIN rh_doctors') !== false && 
        strpos($sqlFragment, 'LEFT JOIN rh_person') !== false && 
        strpos($sqlFragment, 'LEFT JOIN rs_servicios') !== false) {
        echo "<p class='success'>✓ La consulta SQL incluye los JOIN correctos para rh_doctors, rh_person y rs_servicios</p>";
    } else {
        echo "<p class='error'>✗ La consulta SQL no incluye todos los JOIN necesarios</p>";
    }
    
    // Verificar si la consulta incluye COALESCE para los nombres
    if (strpos($sqlFragment, 'COALESCE') !== false) {
        echo "<p class='success'>✓ La consulta SQL utiliza COALESCE para manejar valores nulos</p>";
    } else {
        echo "<p class='error'>✗ La consulta SQL no utiliza COALESCE para los nombres</p>";
    }
} else {
    echo "<p class='error'>✗ No se pudo encontrar la consulta SQL en el modelo</p>";
}

// 8. Examinar el JavaScript
echo "<h2>8. Análisis del código JavaScript</h2>";

// Extraer el código de manejo de reservas en JavaScript
$jsFile = file_get_contents("view/js/servicios.js");
if (preg_match('/function cargarReservasDelDia.*?\}/s', $jsFile, $matches)) {
    echo "<p>Función cargarReservasDelDia encontrada en el JavaScript</p>";
    
    // Verificar si el código maneja diferentes nombres de campos
    if (strpos($jsFile, 'estado_reserva') !== false || 
        strpos($jsFile, 'reserva_estado') !== false) {
        echo "<p class='success'>✓ El JavaScript maneja diferentes nombres de campos para el estado</p>";
    } else {
        echo "<p class='error'>✗ El JavaScript no maneja diferentes nombres de campos para el estado</p>";
    }
    
    // Verificar si el código maneja valores nulos
    if (strpos($jsFile, 'reserva.doctor_nombre') !== false && 
        strpos($jsFile, 'reserva.paciente_nombre') !== false) {
        echo "<p class='success'>✓ El JavaScript maneja los nombres de doctores y pacientes</p>";
    } else {
        echo "<p class='error'>✗ El JavaScript no maneja correctamente los nombres</p>";
    }
} else {
    echo "<p class='error'>✗ No se encontró la función cargarReservasDelDia en el JavaScript</p>";
}

// 9. Simulación de la llamada AJAX
echo "<h2>9. Simulación de la llamada AJAX para obtener reservas</h2>";

// Crear un formulario que simule la llamada AJAX
?>
<form id="testAjaxForm" action="ajax/servicios.ajax.php" method="post" target="ajaxResult">
    <input type="hidden" name="action" value="obtenerReservas">
    <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 20px;">
        <div>
            <label for="fechaTest">Fecha:</label>
            <input type="date" id="fechaTest" name="fecha" value="<?= date('Y-m-d') ?>">
        </div>
        <button type="submit" style="background-color: #4CAF50; color: white; padding: 8px 16px; border: none; cursor: pointer; border-radius: 4px;">Probar API</button>
    </div>
</form>

<iframe name="ajaxResult" style="width: 100%; height: 300px; border: 1px solid #ddd;"></iframe>

<h2>Conclusión y recomendaciones</h2>
<div id="conclusiones">
    <p>Este diagnóstico ha revisado todos los componentes del sistema de reservas:</p>
    <ol>
        <li>Estructura de la base de datos</li>
        <li>Conexión a la base de datos</li>
        <li>Consultas SQL en el modelo</li>
        <li>Lógica en el controlador</li>
        <li>Manejo en JavaScript</li>
    </ol>
    
    <p>Si todas las verificaciones anteriores pasaron correctamente, el sistema debería estar funcionando. Si sigue habiendo problemas:</p>
    <ul>
        <li>Revise los logs en la carpeta <code>logs/</code></li>
        <li>Verifique las fechas utilizadas en las consultas y que tengan el formato correcto</li>
        <li>Compruebe que los datos existen en la base de datos para las fechas consultadas</li>
        <li>Verifique la consola del navegador para errores JavaScript</li>
    </ul>
</div>

</body>
</html>
