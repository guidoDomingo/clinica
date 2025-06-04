<?php
/**
 * Archivo para probar la generación de slots de horarios usando un ejemplo de día real
 */

// Aseguramos que todas las rutas sean relativas al directorio raíz
$rutaBase = dirname(__FILE__);
require_once $rutaBase . "/controller/servicios.controller.php";
require_once $rutaBase . "/model/servicios.model.php";

// Configurar zona horaria
date_default_timezone_set('America/Caracas');

// Fecha actual para crear una fecha real y una de mañana
$fechaHoy = date('Y-m-d');
$fechaManana = date('Y-m-d', strtotime('+1 day'));

// Parámetros de prueba
$servicioId = isset($_GET['servicio_id']) ? $_GET['servicio_id'] : 2;
$doctorId = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : 13;
// Por defecto usamos la fecha de mañana
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : $fechaManana;

$horaActual = date('H:i:s');

// Generar slots
echo "<h2>Prueba de Slots con Fecha Real</h2>";
echo "<ul>";
echo "<li><strong>Servicio ID:</strong> $servicioId</li>";
echo "<li><strong>Doctor ID:</strong> $doctorId</li>";
echo "<li><strong>Fecha seleccionada:</strong> $fecha</li>";
echo "<li><strong>Fecha actual del sistema:</strong> $fechaHoy</li>";
echo "<li><strong>Hora actual del sistema:</strong> $horaActual</li>";
echo "</ul>";

// Links para probar diferentes fechas
echo "<h3>Seleccionar una fecha para probar:</h3>";
echo "<div style='margin-bottom: 20px;'>";
echo "<a href='?fecha=$fechaHoy' class='btn " . ($fecha == $fechaHoy ? 'btn-primary' : 'btn-outline-primary') . "' style='margin-right:10px; padding:5px 10px; text-decoration:none; display:inline-block; border:1px solid #007bff; border-radius:5px; color:" . ($fecha == $fechaHoy ? '#fff' : '#007bff') . "; background:" . ($fecha == $fechaHoy ? '#007bff' : 'transparent') . ";'>Hoy ($fechaHoy)</a> ";
echo "<a href='?fecha=$fechaManana' class='btn " . ($fecha == $fechaManana ? 'btn-primary' : 'btn-outline-primary') . "' style='margin-right:10px; padding:5px 10px; text-decoration:none; display:inline-block; border:1px solid #007bff; border-radius:5px; color:" . ($fecha == $fechaManana ? '#fff' : '#007bff') . "; background:" . ($fecha == $fechaManana ? '#007bff' : 'transparent') . ";'>Mañana ($fechaManana)</a> ";

// Generar links para los próximos 5 días
for ($i = 2; $i <= 5; $i++) {
    $futureDate = date('Y-m-d', strtotime("+$i day"));
    $dayName = date('l', strtotime($futureDate));
    echo "<a href='?fecha=$futureDate' class='btn " . ($fecha == $futureDate ? 'btn-primary' : 'btn-outline-primary') . "' style='margin-right:10px; padding:5px 10px; text-decoration:none; display:inline-block; border:1px solid #007bff; border-radius:5px; color:" . ($fecha == $futureDate ? '#fff' : '#007bff') . "; background:" . ($fecha == $futureDate ? '#007bff' : 'transparent') . ";'>$dayName ($futureDate)</a> ";
}
echo "</div>";

// Obtener servicio
$servicio = ModelServicios::mdlObtenerServicioPorId($servicioId);
echo "<h3>Información del Servicio:</h3>";
echo "<pre style='background:#f5f5f5; padding:10px; border:1px solid #ddd;'>";
print_r($servicio);
echo "</pre>";

// Obtener horarios
echo "<h3>Horarios Disponibles:</h3>";
$horarios = ModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
echo "<p>Total de slots generados: <strong>" . count($horarios) . "</strong></p>";

if (count($horarios) > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>Slot</th>
            <th>Agenda ID</th>
            <th>Turno</th>
            <th>Sala</th>
            <th>Hora Inicio</th>
            <th>Hora Fin</th>
            <th>Duración</th>
          </tr>";
    
    foreach ($horarios as $index => $slot) {
        echo "<tr" . ($index % 2 == 0 ? " style='background: #f9f9f9;'" : "") . ">";
        echo "<td>" . ($index + 1) . "</td>";
        echo "<td>" . ($slot['agenda_id'] ?? 'N/A') . "</td>";
        echo "<td>" . ($slot['turno_nombre'] ?? 'N/A') . "</td>";
        echo "<td>" . ($slot['sala_nombre'] ?? 'N/A') . "</td>";
        echo "<td><strong>" . ($slot['hora_inicio'] ?? 'N/A') . "</strong></td>";
        echo "<td><strong>" . ($slot['hora_fin'] ?? 'N/A') . "</strong></td>";
        echo "<td>" . ($slot['duracion_minutos'] ?? 'N/A') . " min</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color:red; padding:10px; background:#fff0f0; border:1px solid #ffccc7;'>No se generaron slots. Verificar logs para más detalles.</p>";
}

// Mostrar logs recientes
echo "<h3>Últimas Entradas de Log:</h3>";
$logFile = 'c:/laragon/www/clinica/logs/servicios.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $logs = implode("<br>", array_slice(explode("\n", $logs), -40));
    echo "<div style='background: #f7f7f7; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;'>";
    echo $logs;
    echo "</div>";
} else {
    echo "<p>Log no disponible</p>";
}
