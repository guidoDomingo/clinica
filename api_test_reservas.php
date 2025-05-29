<?php
/**
 * Script para probar directamente la API de reservas
 */
// Aseguramos que todas las rutas sean relativas al directorio raíz
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";

// Activar visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para formatear salida JSON
function prettyJson($data) {
    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

echo "<h1>Probar API de Reservas</h1>";

// Obtener la fecha de hoy o la fecha proporcionada
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;

echo "<p>Fecha: <strong>$fecha</strong></p>";
echo "<p>Doctor ID: <strong>" . ($doctorId ?? "null") . "</strong></p>";

echo "<h2>Resultado ctrObtenerReservasPorFecha</h2>";
try {
    $reservas = ControladorServicios::ctrObtenerReservasPorFecha($fecha, $doctorId);
    
    echo "<p>Se encontraron: <strong>" . count($reservas) . "</strong> reservas</p>";
    
    if (count($reservas) > 0) {
        echo "<h3>Datos de las reservas:</h3>";
        echo "<pre>" . htmlspecialchars(prettyJson($reservas)) . "</pre>";
        
        echo "<h3>Propiedades de la primera reserva:</h3>";
        echo "<ul>";
        foreach ($reservas[0] as $key => $value) {
            echo "<li><strong>$key:</strong> " . htmlspecialchars(print_r($value, true)) . "</li>";
        }
        echo "</ul>";
        
        echo "<h3>Reservas en formato tabla:</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr>";
        foreach (array_keys($reservas[0]) as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr>";
        
        foreach ($reservas as $reserva) {
            echo "<tr>";
            foreach ($reserva as $valor) {
                echo "<td>" . htmlspecialchars(is_null($valor) ? 'NULL' : $valor) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red'>No hay reservas para esta fecha.</p>";
        
        echo "<h3>Crear una reserva de prueba:</h3>";
        echo "<a href='crear_reserva_hoy.php?fecha=$fecha' style='padding:10px; background-color:#007bff; color:white; text-decoration:none; border-radius:5px;'>Crear Reserva de Prueba</a>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    echo "<p>En archivo: " . $e->getFile() . ", línea " . $e->getLine() . "</p>";
}

// Probar también la función del modelo directamente
echo "<h2>Resultado mdlObtenerReservasPorFecha</h2>";
try {
    $reservasModelo = ModelServicios::mdlObtenerReservasPorFecha($fecha, $doctorId);
    
    echo "<p>Se encontraron: <strong>" . count($reservasModelo) . "</strong> reservas (desde el modelo)</p>";
    
    if (count($reservasModelo) > 0) {
        echo "<h3>Datos de las reservas (desde modelo):</h3>";
        echo "<pre>" . htmlspecialchars(prettyJson($reservasModelo)) . "</pre>";
    } else {
        echo "<p style='color:red'>No hay reservas para esta fecha (desde modelo).</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error en modelo: " . $e->getMessage() . "</p>";
}

// Crear formulario para probar con otras fechas
echo "<h2>Probar con otras fechas</h2>";
echo "<form method='get'>";
echo "  <input type='date' name='fecha' value='$fecha'>";
echo "  <input type='number' name='doctor_id' placeholder='Doctor ID' value='" . ($doctorId ?? "") . "'>";
echo "  <button type='submit'>Probar</button>";
echo "</form>";

// Verificar también los datos de la BD directamente
echo "<h2>Consulta directa a la base de datos</h2>";
try {
    $db = Conexion::conectar();
    
    if ($db !== null) {
        $sql = "SELECT COUNT(*) FROM servicios_reservas WHERE fecha_reserva = :fecha";
        $params = [':fecha' => $fecha];
        
        if ($doctorId !== null) {
            $sql .= " AND doctor_id = :doctor_id";
            $params[':doctor_id'] = $doctorId;
        }
        
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        echo "<p>Total de reservas en la BD para $fecha: <strong>$count</strong></p>";
        
        if ($count > 0) {
            $sql = "SELECT * FROM servicios_reservas WHERE fecha_reserva = :fecha";
            if ($doctorId !== null) {
                $sql .= " AND doctor_id = :doctor_id";
            }
            
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $reservasBD = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Datos directos de la BD:</h3>";
            echo "<pre>" . htmlspecialchars(prettyJson($reservasBD)) . "</pre>";
        }
    } else {
        echo "<p style='color:red'>No se pudo conectar a la BD para la consulta directa.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error en consulta directa: " . $e->getMessage() . "</p>";
}
?>
