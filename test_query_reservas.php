<?php
/**
 * Test para verificar la función de obtención de reservas
 * Este archivo prueba la consulta actualizada para mostrar nombres de doctores, pacientes y servicios
 */

require_once 'controller/servicios.controller.php';
require_once 'model/servicios.model.php';

// Establecer zona horaria
date_default_timezone_set('America/Asuncion');

// Obtener la fecha actual si no se proporciona una
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$doctorId = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : null;

// Función para mostrar resultados de manera formateada
function mostrarReservas($reservas) {
    if (empty($reservas)) {
        echo "<div class='alert alert-warning'>No se encontraron reservas para la fecha seleccionada.</div>";
        return;
    }
    
    echo "<table class='table table-striped table-bordered'>";
    echo "<thead class='thead-dark'>";
    echo "<tr>
            <th>ID</th>
            <th>Hora Inicio</th>
            <th>Hora Fin</th>
            <th>Doctor</th>
            <th>Paciente</th>
            <th>Servicio</th>
            <th>Estado</th>
        </tr>";
    echo "</thead>";
    echo "<tbody>";
    
    foreach ($reservas as $reserva) {
        echo "<tr>";
        echo "<td>" . $reserva['reserva_id'] . "</td>";
        echo "<td>" . $reserva['hora_inicio'] . "</td>";
        echo "<td>" . $reserva['hora_fin'] . "</td>";
        echo "<td>" . $reserva['doctor'] . "</td>";
        echo "<td>" . $reserva['paciente'] . "</td>";
        echo "<td>" . $reserva['serv_descripcion'] . "</td>";
        echo "<td>" . $reserva['reserva_estado'] . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
}

// Obtener la SQL usada por PHP para debugging
function obtenerSQLReservas($fecha, $doctorId = null) {
    $fechaInicio = $fecha . " 00:00:00";
    $fechaFin = $fecha . " 23:59:59";
    
    $sql = "SELECT 
        sr.hora_inicio,
        sr.hora_fin,
        rp.first_name ||' - ' || rp.last_name as doctor,
        rp2.first_name ||' - ' || rp2.last_name as paciente,
        rs.serv_descripcion,
        sr.reserva_estado 
    FROM servicios_reservas sr 
    INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
    INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
    INNER JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id 
    INNER JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id 
    WHERE sr.fecha_reserva BETWEEN '$fechaInicio' AND '$fechaFin'";
    
    if ($doctorId !== null) {
        $sql .= " AND sr.doctor_id = $doctorId";
    }
    
    $sql .= " ORDER BY sr.hora_inicio ASC";
    
    return $sql;
}

// HTML para la página
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Reservas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .alert { margin-top: 20px; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test de Reservas - <?php echo $fecha; ?></h1>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Formulario de Búsqueda</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="fecha" class="mr-2">Fecha:</label>
                        <input type="date" id="fecha" name="fecha" value="<?php echo $fecha; ?>" class="form-control">
                    </div>
                    <div class="form-group mr-3">
                        <label for="doctor_id" class="mr-2">Doctor ID:</label>
                        <input type="number" id="doctor_id" name="doctor_id" value="<?php echo $doctorId; ?>" class="form-control" placeholder="opcional">
                    </div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Consulta SQL</h3>
            </div>
            <div class="card-body">
                <pre><?php echo obtenerSQLReservas($fecha, $doctorId); ?></pre>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">Resultados de la Consulta</h3>
            </div>
            <div class="card-body">
                <?php 
                // Obtener reservas usando la función del modelo
                $reservas = ModelServicios::mdlObtenerReservasPorFecha($fecha, $doctorId);
                
                // Mostrar los resultados
                mostrarReservas($reservas);
                
                // Mostrar datos completos para debugging
                echo "<h4 class='mt-4'>Datos JSON Completos:</h4>";
                echo "<pre>";
                echo json_encode($reservas, JSON_PRETTY_PRINT);
                echo "</pre>";
                ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h3 class="card-title">Prueba API (Controlador)</h3>
            </div>
            <div class="card-body">
                <?php                // Probar la función del controlador
                $reservasAPI = ControladorServicios::ctrObtenerReservasPorFecha($fecha, $doctorId);
                
                echo "<pre>";
                echo json_encode($reservasAPI, JSON_PRETTY_PRINT);
                echo "</pre>";
                ?>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
