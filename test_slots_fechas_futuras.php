<?php
/**
 * Archivo para probar la generación de slots en fechas futuras vs. fecha actual
 */

// Aseguramos que todas las rutas sean relativas al directorio raíz
$rutaBase = dirname(__FILE__);
require_once $rutaBase . "/controller/servicios.controller.php";
require_once $rutaBase . "/model/servicios.model.php";

// Configurar zona horaria
date_default_timezone_set('America/Caracas');

// Obtener parámetros y preparar datos
$servicioId = isset($_GET['servicio_id']) ? $_GET['servicio_id'] : 2;
$doctorId = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : 13;

// Obtener fechas para probar
$fechaHoy = date('Y-m-d');
$fechaManana = date('Y-m-d', strtotime('+1 day'));
$fechaProximaSemana = date('Y-m-d', strtotime('+7 days'));
$horaActual = date('H:i:s');

// Estilo para la página
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Slots para Diferentes Fechas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2, h3 { color: #333; }
        .panel { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 4px; }
        .card { background-color: #f9f9f9; padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .btn { display: inline-block; padding: 6px 12px; margin: 5px 2px; text-decoration: none; color: white; background-color: #007bff; border-radius: 4px; }
        .btn:hover { background-color: #0056b3; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 4px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Prueba de Generación de Slots para Diferentes Fechas</h1>';

// Información general
echo '<div class="panel">
    <h2>Información del Sistema</h2>
    <div class="card">
        <p><strong>Fecha actual:</strong> ' . $fechaHoy . '</p>
        <p><strong>Hora actual:</strong> ' . $horaActual . '</p>
        <p><strong>Doctor ID:</strong> ' . $doctorId . ' | <strong>Servicio ID:</strong> ' . $servicioId . '</p>
        <p>
            <a href="?servicio_id=' . $servicioId . '&doctor_id=13" class="btn">Doctor ID: 13</a>
            <a href="?servicio_id=' . $servicioId . '&doctor_id=14" class="btn">Doctor ID: 14</a>
            <a href="?servicio_id=1&doctor_id=' . $doctorId . '" class="btn">Servicio ID: 1</a>
            <a href="?servicio_id=2&doctor_id=' . $doctorId . '" class="btn">Servicio ID: 2</a>
        </p>
    </div>
</div>';

// Función para mostrar los slots disponibles
function mostrarSlots($fecha, $servicioId, $doctorId) {
    echo '<div class="panel">';
    echo '<h2>Slots para: ' . $fecha . '</h2>';
    
    // Determinar si es fecha actual, pasada o futura
    $fechaObj = new DateTime($fecha);
    $hoyObj = new DateTime(date('Y-m-d'));
    
    if ($fechaObj < $hoyObj) {
        echo '<p class="error">Esta es una fecha pasada</p>';
    } elseif ($fechaObj == $hoyObj) {
        echo '<p class="info">Esta es la fecha actual</p>';
    } else {
        echo '<p class="success">Esta es una fecha futura</p>';
    }
    
    // Obtener los slots
    $slots = ModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
    
    echo '<p>Total de slots generados: <strong>' . count($slots) . '</strong></p>';
    
    if (count($slots) > 0) {
        echo '<table>
                <tr>
                    <th>#</th>
                    <th>Doctor</th>
                    <th>Turno</th>
                    <th>Fecha</th>
                    <th>Hora Inicio</th>
                    <th>Hora Fin</th>
                </tr>';
        
        foreach ($slots as $index => $slot) {
            echo '<tr>
                    <td>' . ($index + 1) . '</td>
                    <td>' . $slot['nombre_doctor'] . '</td>
                    <td>' . $slot['turno_nombre'] . '</td>
                    <td>' . $slot['fecha_reserva'] . '</td>
                    <td>' . $slot['hora_inicio'] . '</td>
                    <td>' . $slot['hora_fin'] . '</td>
                </tr>';
        }
        
        echo '</table>';
    } else {
        echo '<p class="error">No se encontraron slots disponibles para esta fecha.</p>';
    }
    
    echo '</div>';
}

// Mostrar slots para hoy, mañana y la próxima semana
mostrarSlots($fechaHoy, $servicioId, $doctorId);
mostrarSlots($fechaManana, $servicioId, $doctorId);
mostrarSlots($fechaProximaSemana, $servicioId, $doctorId);

// Mostrar los últimos registros del log
echo '<div class="panel">
    <h2>Últimas Entradas de Log</h2>';
$logFile = 'c:/laragon/www/clinica/logs/servicios.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $logs = array_slice(explode("\n", $logs), -20);
    echo '<pre>' . implode("\n", $logs) . '</pre>';
} else {
    echo '<p class="error">No se puede acceder al archivo de log.</p>';
}
echo '</div>';

echo '</body>
</html>';
