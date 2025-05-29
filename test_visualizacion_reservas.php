<?php
/**
 * Script para probar la visualización de reservas
 * Este script verifica que las reservas muestren nombres de doctores, pacientes y servicios, no sólo IDs
 */
require_once "model/conexion.php";
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";

// Cabecera HTML
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Visualización de Reservas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Test de Visualización de Reservas</h1>';

// Obtener la fecha de hoy o la fecha proporcionada
$fecha = $_GET['fecha'] ?? date('Y-m-d');

echo '<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h2>Reservas para la fecha: ' . $fecha . '</h2>
        <form method="GET" class="mt-2">
            <div class="input-group">
                <input type="date" name="fecha" class="form-control" value="' . $fecha . '">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-light">Cambiar fecha</button>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body">';

try {
    // Obtener reservas para la fecha seleccionada
    $reservas = ControladorServicios::ctrObtenerReservasPorFecha($fecha);
    
    if (empty($reservas)) {
        echo '<div class="alert alert-warning">No se encontraron reservas para esta fecha.</div>';
    } else {
        echo '<h3>Se encontraron ' . count($reservas) . ' reservas:</h3>';
        
        echo '<table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Horario</th>
                    <th>Doctor</th>
                    <th>Paciente</th>
                    <th>Servicio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($reservas as $reserva) {
            // Formatear la hora para mostrar (HH:MM)
            $horaInicio = $reserva['hora_inicio'] ? substr($reserva['hora_inicio'], 0, 5) : '';
            $horaFin = $reserva['hora_fin'] ? substr($reserva['hora_fin'], 0, 5) : '';
            
            // Determinar clase para el estado
            $claseEstado = '';
            $estado = strtoupper($reserva['estado'] ?? 'PENDIENTE');
            
            switch ($estado) {
                case 'PENDIENTE': $claseEstado = 'badge-warning'; break;
                case 'CONFIRMADA': $claseEstado = 'badge-success'; break;
                case 'CANCELADA': $claseEstado = 'badge-danger'; break;
                case 'COMPLETADA': $claseEstado = 'badge-info'; break;
                default: $claseEstado = 'badge-secondary';
            }
            
            echo '<tr>
                <td>' . $reserva['reserva_id'] . '</td>
                <td>' . $horaInicio . ' - ' . $horaFin . '</td>
                <td>' . htmlspecialchars($reserva['doctor_nombre'] ?? 'No disponible') . '</td>
                <td>' . htmlspecialchars($reserva['paciente_nombre'] ?? 'No disponible') . '</td>
                <td>' . htmlspecialchars($reserva['servicio_nombre'] ?? 'No disponible') . '</td>
                <td><span class="badge ' . $claseEstado . '">' . $estado . '</span></td>
            </tr>';
        }
        
        echo '</tbody></table>';
        
        // Mostrar detalle de la primera reserva para debug
        echo '<h4>Detalle técnico de la primera reserva:</h4>';
        echo '<pre class="bg-light p-3">' . json_encode($reservas[0], JSON_PRETTY_PRINT) . '</pre>';
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
}

echo '</div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h2>Diagnóstico de tablas</h2>
        </div>
        <div class="card-body">';

// Verificar las tablas relacionadas
try {
    $db = Conexion::conectar();
    
    $tablas = ['servicios_reservas', 'servicios_medicos', 'pacientes', 'rh_doctors', 'rh_person'];
    
    echo '<h3>Estado de tablas:</h3>';
    echo '<ul class="list-group">';
    
    foreach ($tablas as $tabla) {
        $stmt = $db->prepare("SELECT to_regclass('public." . $tabla . "')");
        $stmt->execute();
        $tablaExiste = $stmt->fetchColumn();
        
        if ($tablaExiste) {
            // Contar registros
            $stmt = $db->prepare("SELECT COUNT(*) FROM " . $tabla);
            $stmt->execute();
            $conteo = $stmt->fetchColumn();
            
            echo '<li class="list-group-item list-group-item-success">
                <strong>' . $tabla . '</strong>: Existe (' . $conteo . ' registros)
            </li>';
        } else {
            echo '<li class="list-group-item list-group-item-danger">
                <strong>' . $tabla . '</strong>: No existe
            </li>';
        }
    }
    
    echo '</ul>';
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error al verificar tablas: ' . $e->getMessage() . '</div>';
}

echo '</div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
