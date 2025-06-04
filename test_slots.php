<?php
/**
 * Archivo para probar la generación de slots de horarios
 */

// Aseguramos que todas las rutas sean relativas al directorio raíz
$rutaBase = dirname(__FILE__);
require_once $rutaBase . "/controller/servicios.controller.php";
require_once $rutaBase . "/model/servicios.model.php";

// Configurar zona horaria
date_default_timezone_set('America/Caracas');

// Parámetros de prueba
$servicioId = isset($_GET['servicio_id']) ? $_GET['servicio_id'] : 2;
$doctorId = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : 13;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$horaActual = date('H:i:s');

// Opciones para simular otra hora del día (solo para pruebas)
$simulateTime = isset($_GET['time']) ? $_GET['time'] : false;
if ($simulateTime) {
    echo "<li><strong>Hora simulada:</strong> $simulateTime</li>";
    echo "<li style='color:red'>SIMULACIÓN ACTIVA - Usando hora simulada para pruebas</li>";
}

// Generar slots
echo "<h2>Generando Slots para:</h2>";
echo "<ul>";
echo "<li><strong>Servicio ID:</strong> $servicioId</li>";
echo "<li><strong>Doctor ID:</strong> $doctorId</li>";
echo "<li><strong>Fecha:</strong> $fecha</li>";
echo "<li><strong>Hora actual del sistema:</strong> $horaActual</li>";
echo "</ul>";

// Obtener servicio
$servicio = ModelServicios::mdlObtenerServicioPorId($servicioId);
echo "<h3>Información del Servicio:</h3>";
echo "<pre>";
print_r($servicio);
echo "</pre>";

// Obtener horarios
echo "<h3>Horarios Disponibles:</h3>";
$horarios = ControladorServicios::ctrGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
echo "<p>Total de slots generados: " . count($horarios) . "</p>";

if (count($horarios) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr>
            <th>Slot</th>
            <th>Agenda ID</th>
            <th>Turno</th>
            <th>Sala</th>
            <th>Hora Inicio</th>
            <th>Hora Fin</th>
            <th>Duración</th>
          </tr>";
    
    foreach ($horarios as $index => $slot) {
        echo "<tr>";
        echo "<td>" . ($index + 1) . "</td>";
        echo "<td>" . ($slot['agenda_id'] ?? 'N/A') . "</td>";
        echo "<td>" . ($slot['turno_nombre'] ?? 'N/A') . "</td>";
        echo "<td>" . ($slot['sala_nombre'] ?? 'N/A') . "</td>";
        echo "<td>" . ($slot['hora_inicio'] ?? 'N/A') . "</td>";
        echo "<td>" . ($slot['hora_fin'] ?? 'N/A') . "</td>";
        echo "<td>" . ($slot['duracion_minutos'] ?? 'N/A') . " min</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color:red;'>No se generaron slots. Verificar logs para más detalles.</p>";
    
    // Mostrar logs recientes
    echo "<h3>Últimas Entradas de Log:</h3>";
    $logFile = 'c:/laragon/www/clinica/logs/servicios.log';
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $logs = implode("<br>", array_slice(explode("\n", $logs), -20));
        echo "<div style='background: #f7f7f7; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto;'>";
        echo $logs;
        echo "</div>";
    } else {
        echo "<p>Log no disponible</p>";
    }
}
