<?php
/**
 * Prueba de generación de slots para fechas futuras
 * Esta versión ignora la hora actual para fechas futuras
 */

// Aseguramos que todas las rutas sean relativas al directorio raíz
$rutaBase = dirname(__FILE__);
require_once $rutaBase . "/controller/servicios.controller.php";
require_once $rutaBase . "/model/servicios.model.php";

// Configurar zona horaria
date_default_timezone_set('America/Caracas');

// Obtener la fecha actual y fechas futuras para pruebas
$fechaHoy = date('Y-m-d');
$fechaManana = date('Y-m-d', strtotime('+1 day'));
$fechaPasadoManana = date('Y-m-d', strtotime('+2 day'));
$horaActual = date('H:i:s');

// Mostrar la interfaz de prueba
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Prueba de Slots Futuros</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 1200px; margin: 0 auto; }
        .box { background: #f5f5f5; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .log { font-family: monospace; background: #f0f0f0; padding: 10px; max-height: 400px; overflow: auto; white-space: pre-wrap; }
        .btn { display: inline-block; padding: 8px 15px; margin: 5px; text-decoration: none; background: #0275d8; color: white; border-radius: 4px; }
        .btn:hover { background: #0056b3; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Prueba de Generación de Slots para Fechas Futuras</h1>
        
        <div class='box'>
            <h2>Información del Sistema</h2>
            <p><strong>Fecha actual:</strong> $fechaHoy</p>
            <p><strong>Hora actual:</strong> $horaActual</p>
        </div>";

// Parámetros de prueba
$servicioId = isset($_GET['servicio_id']) ? $_GET['servicio_id'] : 2;
$doctorId = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : 13;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : $fechaManana;

// Crear los enlaces para seleccionar diferentes fechas
echo "<div class='box'>
    <h2>Seleccionar Fecha de Prueba</h2>
    <a href='?fecha=$fechaHoy&servicio_id=$servicioId&doctor_id=$doctorId' class='btn'>Hoy ($fechaHoy)</a>
    <a href='?fecha=$fechaManana&servicio_id=$servicioId&doctor_id=$doctorId' class='btn'>Mañana ($fechaManana)</a>
    <a href='?fecha=$fechaPasadoManana&servicio_id=$servicioId&doctor_id=$doctorId' class='btn'>Pasado Mañana ($fechaPasadoManana)</a>";

// También permitir cambiar el médico y servicio
echo "<h3>Seleccionar Médico y Servicio</h3>
    <form method='GET'>
        <input type='hidden' name='fecha' value='$fecha'>
        <label>Doctor ID: <input type='number' name='doctor_id' value='$doctorId' style='width: 60px;'></label>
        <label>Servicio ID: <input type='number' name='servicio_id' value='$servicioId' style='width: 60px;'></label>
        <button type='submit' style='padding: 5px 15px;'>Cambiar</button>
    </form>
</div>";

// Generar slots
echo "<div class='box'>
    <h2>Generando Slots para:</h2>
    <ul>
        <li><strong>Fecha seleccionada:</strong> $fecha</li>
        <li><strong>Servicio ID:</strong> $servicioId</li>
        <li><strong>Doctor ID:</strong> $doctorId</li>
    </ul>";

// Obtener servicio
$servicio = ModelServicios::mdlObtenerServicioPorId($servicioId);
echo "<h3>Información del Servicio:</h3>";
if ($servicio) {
    echo "<table>
        <tr><th>ID</th><th>Código</th><th>Nombre</th><th>Duración</th><th>Precio Base</th></tr>
        <tr>
            <td>{$servicio['servicio_id']}</td>
            <td>{$servicio['servicio_codigo']}</td>
            <td>{$servicio['servicio_nombre']}</td>
            <td>{$servicio['duracion_minutos']} minutos</td>
            <td>{$servicio['precio_base']}</td>
        </tr>
    </table>";
} else {
    echo "<p class='error'>No se encontró información del servicio ID: $servicioId</p>";
}

// Obtener horarios
$horarios = ModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
echo "<h3>Horarios Disponibles:</h3>";
echo "<p>Total de slots generados: <strong>" . count($horarios) . "</strong></p>";

if (count($horarios) > 0) {
    echo "<table>
        <tr>
            <th>#</th>
            <th>Agenda ID</th>
            <th>Turno</th>
            <th>Sala</th>
            <th>Fecha</th>
            <th>Hora Inicio</th>
            <th>Hora Fin</th>
            <th>Duración</th>
        </tr>";
    
    foreach ($horarios as $index => $slot) {
        echo "<tr>
            <td>" . ($index + 1) . "</td>
            <td>{$slot['agenda_id']}</td>
            <td>{$slot['turno_nombre']}</td>
            <td>{$slot['sala_nombre']}</td>
            <td>{$slot['fecha_reserva']}</td>
            <td><strong>{$slot['hora_inicio']}</strong></td>
            <td><strong>{$slot['hora_fin']}</strong></td>
            <td>{$slot['duracion_minutos']} min</td>
        </tr>";
    }
    
    echo "</table>";
} else {
    echo "<p class='error'>No se generaron slots disponibles para esta combinación.</p>";
}
echo "</div>";

// Mostrar logs recientes
echo "<div class='box'>
    <h3>Últimas Entradas de Log:</h3>";
$logFile = 'c:/laragon/www/clinica/logs/servicios.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $logs = implode("<br>", array_slice(explode("\n", $logs), -40));
    echo "<div class='log'>" . $logs . "</div>";
} else {
    echo "<p class='error'>Log no disponible</p>";
}
echo "</div>
    </div>
</body>
</html>";
