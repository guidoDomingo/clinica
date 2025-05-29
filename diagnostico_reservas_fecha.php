<?php
/**
 * Script para verificar si existen reservas para una fecha específica
 * y diagnosticar posibles problemas en la consulta
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
    <title>Diagnóstico de Reservas por Fecha</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        pre.sql { 
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            font-family: monospace;
            white-space: pre-wrap;
            margin: 10px 0;
        }
        .test-section {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Diagnóstico de Reservas por Fecha</h1>';

// Obtener la fecha de hoy o la fecha proporcionada
$fecha = $_GET['fecha'] ?? date('Y-m-d');

echo '<div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h2 class="mb-0">Reservas para: ' . $fecha . '</h2>
        <form method="GET" class="form-inline">
            <div class="input-group">
                <input type="date" name="fecha" class="form-control" value="' . $fecha . '">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-light">Buscar</button>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body">';

try {
    // Verificar conexión a la base de datos
    $db = Conexion::conectar();
    
    if ($db === null) {
        // Si la conexión falla, redireccionamos al script de verificación
        echo '<div class="alert alert-danger">
            <h4>Error de conexión a la base de datos</h4>
            <p>No se pudo establecer conexión con la base de datos. El error más común es que la extensión pdo_pgsql no está habilitada.</p>
            <p><a href="check_and_enable_pgsql.php" class="btn btn-primary">Verificar y Solucionar</a></p>
        </div>';
        echo '</div></div></div></body></html>';
        exit;
    }
    
    // 1. Verificar si existen reservas para esta fecha con una consulta simple directa
    echo '<div class="test-section">
        <h3>1. Consulta Directa a la Base de Datos</h3>';
    
    $sql = "SELECT COUNT(*) as total FROM servicios_reservas WHERE fecha_reserva = :fecha";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
    $stmt->execute();
    $totalReservas = $stmt->fetchColumn();
    
    if ($totalReservas > 0) {
        echo '<div class="alert alert-success">
            <strong>✓ Éxito:</strong> Se encontraron ' . $totalReservas . ' reservas para la fecha ' . $fecha . ' en la tabla.
        </div>';
        
        // Mostrar estas reservas
        $sql = "SELECT * FROM servicios_reservas WHERE fecha_reserva = :fecha";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
        $stmt->execute();
        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<h4>Registros encontrados en la tabla:</h4>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped">
                <thead>
                    <tr>';
        
        foreach (array_keys($reservas[0]) as $columna) {
            echo '<th>' . $columna . '</th>';
        }
        
        echo '</tr>
                </thead>
                <tbody>';
        
        foreach ($reservas as $reserva) {
            echo '<tr>';
            foreach ($reserva as $key => $valor) {
                if ($key == 'observaciones') {
                    // Limitar texto largo
                    echo '<td>' . (strlen($valor) > 50 ? substr($valor, 0, 47) . '...' : $valor) . '</td>';
                } else {
                    echo '<td>' . (is_null($valor) ? '<em>NULL</em>' : htmlspecialchars((string)$valor)) . '</td>';
                }
            }
            echo '</tr>';
        }
        
        echo '</tbody>
            </table>
        </div>';
    } else {
        echo '<div class="alert alert-warning">
            <strong>⚠ Advertencia:</strong> No se encontraron reservas para la fecha ' . $fecha . ' en la tabla.
        </div>';
    }
    
    echo '</div>'; // Fin test-section
    
    // 2. Usar el controlador para obtener las reservas (como lo hace el AJAX)
    echo '<div class="test-section">
        <h3>2. Usando el Controlador (como AJAX)</h3>';
    
    $reservasController = ControladorServicios::ctrObtenerReservasPorFecha($fecha);
    
    if (count($reservasController) > 0) {
        echo '<div class="alert alert-success">
            <strong>✓ Éxito:</strong> El controlador encontró ' . count($reservasController) . ' reservas.
        </div>';
        
        echo '<h4>Datos obtenidos por el controlador:</h4>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Horario</th>
                        <th>Doctor ID</th>
                        <th>Doctor</th>
                        <th>Paciente ID</th>
                        <th>Paciente</th>
                        <th>Servicio ID</th>
                        <th>Servicio</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($reservasController as $reserva) {
            $horaInicio = isset($reserva['hora_inicio']) ? substr($reserva['hora_inicio'], 0, 5) : '-';
            $horaFin = isset($reserva['hora_fin']) ? substr($reserva['hora_fin'], 0, 5) : '-';
            
            echo '<tr>
                <td>' . $reserva['reserva_id'] . '</td>
                <td>' . $horaInicio . ' - ' . $horaFin . '</td>
                <td>' . $reserva['doctor_id'] . '</td>
                <td>' . htmlspecialchars($reserva['doctor_nombre'] ?? '-') . '</td>
                <td>' . $reserva['paciente_id'] . '</td>
                <td>' . htmlspecialchars($reserva['paciente_nombre'] ?? '-') . '</td>
                <td>' . $reserva['servicio_id'] . '</td>
                <td>' . htmlspecialchars($reserva['servicio_nombre'] ?? '-') . '</td>
                <td>' . ($reserva['estado'] ?? '-') . '</td>
            </tr>';
        }
        
        echo '</tbody>
            </table>
        </div>';
        
        // Mostrar la primera reserva en formato JSON
        echo '<h4>Detalle primera reserva (JSON):</h4>
        <pre>' . json_encode($reservasController[0], JSON_PRETTY_PRINT) . '</pre>';
        
    } else {
        echo '<div class="alert alert-warning">
            <strong>⚠ Advertencia:</strong> El controlador no encontró reservas para esta fecha.
        </div>';
    }
    
    // Comprobar si hay diferencias entre las dos consultas
    if ($totalReservas != count($reservasController)) {
        echo '<div class="alert alert-danger">
            <strong>❌ Error:</strong> Hay una discrepancia entre las reservas encontradas directamente en la tabla (' . $totalReservas . ') 
            y las devueltas por el controlador (' . count($reservasController) . ').
        </div>';
    }
    
    echo '</div>'; // Fin test-section
    
    // 3. Verificar las tablas relacionadas y datos de prueba
    echo '<div class="test-section">
        <h3>3. Verificación de Datos de Prueba</h3>';
    
    // Función para insertar un registro de prueba si no hay reservas
    if ($totalReservas == 0) {
        echo '<div class="alert alert-info">
            No hay reservas para la fecha seleccionada. ¿Desea crear un registro de prueba?
        </div>
        
        <form method="POST" class="mb-4">
            <input type="hidden" name="crear_reserva_prueba" value="1">
            <input type="hidden" name="fecha" value="' . $fecha . '">
            
            <div class="form-group">
                <label for="doctor_id">ID del Doctor:</label>
                <input type="number" class="form-control" id="doctor_id" name="doctor_id" value="14" required>
            </div>
            
            <div class="form-group">
                <label for="paciente_id">ID del Paciente:</label>
                <input type="number" class="form-control" id="paciente_id" name="paciente_id" value="11" required>
            </div>
            
            <div class="form-group">
                <label for="servicio_id">ID del Servicio:</label>
                <input type="number" class="form-control" id="servicio_id" name="servicio_id" value="1" required>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="hora_inicio">Hora inicio:</label>
                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" value="09:00" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="hora_fin">Hora fin:</label>
                    <input type="time" class="form-control" id="hora_fin" name="hora_fin" value="09:30" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Crear Reserva de Prueba</button>
        </form>';
    }
    
    // Procesar la creación de reserva de prueba
    if (isset($_POST['crear_reserva_prueba'])) {
        $fechaReserva = $_POST['fecha'];
        $doctorId = intval($_POST['doctor_id']);
        $pacienteId = intval($_POST['paciente_id']);
        $servicioId = intval($_POST['servicio_id']);
        $horaInicio = $_POST['hora_inicio'];
        $horaFin = $_POST['hora_fin'];
        
        try {
            $sql = "INSERT INTO servicios_reservas (doctor_id, paciente_id, servicio_id, fecha_reserva, hora_inicio, hora_fin, reserva_estado, business_id, created_by)
                    VALUES (:doctor_id, :paciente_id, :servicio_id, :fecha_reserva, :hora_inicio, :hora_fin, 'PENDIENTE', 1, 1)";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':doctor_id', $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(':paciente_id', $pacienteId, PDO::PARAM_INT);
            $stmt->bindParam(':servicio_id', $servicioId, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_reserva', $fechaReserva, PDO::PARAM_STR);
            $stmt->bindParam(':hora_inicio', $horaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':hora_fin', $horaFin, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $reservaId = $db->lastInsertId();
                echo '<div class="alert alert-success">
                    <strong>✓ Éxito:</strong> Se ha creado una reserva de prueba con ID ' . $reservaId . ' para la fecha ' . $fechaReserva . '.
                    <meta http-equiv="refresh" content="2;url=' . $_SERVER['PHP_SELF'] . '?fecha=' . $fechaReserva . '">
                </div>';
            } else {
                echo '<div class="alert alert-danger">
                    <strong>❌ Error:</strong> No se pudo insertar la reserva de prueba.
                </div>';
            }
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">
                <strong>❌ Error:</strong> ' . $e->getMessage() . '
            </div>';
        }
    }
    
    // Verificar datos de rh_doctors, rh_person y rs_servicios
    echo '<h4>Verificación de datos en tablas relacionadas:</h4>';
    
    // Verificar doctores
    $sql = "SELECT d.doctor_id, p.person_id, p.first_name, p.last_name 
            FROM rh_doctors d
            LEFT JOIN rh_person p ON d.person_id = p.person_id
            LIMIT 5";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<div class="card mb-3">
        <div class="card-header">Doctores disponibles (muestra)</div>
        <div class="card-body">';
    
    if (count($doctores) > 0) {
        echo '<table class="table table-sm">
            <thead>
                <tr>
                    <th>Doctor ID</th>
                    <th>Person ID</th>
                    <th>Nombre</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($doctores as $doctor) {
            echo '<tr>
                <td>' . $doctor['doctor_id'] . '</td>
                <td>' . ($doctor['person_id'] ?? '<span class="text-danger">NULL</span>') . '</td>
                <td>' . (($doctor['first_name'] && $doctor['last_name']) ? 
                    htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) : 
                    '<span class="text-danger">Sin nombre</span>') . '</td>
            </tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-warning mb-0">No se encontraron doctores.</div>';
    }
    
    echo '</div></div>';
    
    // Verificar pacientes (rh_person)
    $sql = "SELECT person_id, first_name, last_name FROM rh_person LIMIT 5";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<div class="card mb-3">
        <div class="card-header">Pacientes disponibles (muestra)</div>
        <div class="card-body">';
    
    if (count($personas) > 0) {
        echo '<table class="table table-sm">
            <thead>
                <tr>
                    <th>Person ID</th>
                    <th>Nombre</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($personas as $persona) {
            echo '<tr>
                <td>' . $persona['person_id'] . '</td>
                <td>' . htmlspecialchars($persona['first_name'] . ' ' . $persona['last_name']) . '</td>
            </tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-warning mb-0">No se encontraron pacientes.</div>';
    }
    
    echo '</div></div>';
    
    // Verificar servicios
    try {
        $sql = "SELECT * FROM rs_servicios LIMIT 5";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<div class="card mb-3">
            <div class="card-header">Servicios disponibles (muestra)</div>
            <div class="card-body">';
        
        if (count($servicios) > 0) {
            echo '<table class="table table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($servicios as $servicio) {
                echo '<tr>
                    <td>' . $servicio['servicio_id'] . '</td>
                    <td>' . htmlspecialchars($servicio['nombre'] ?? '-') . '</td>
                    <td>' . htmlspecialchars(substr($servicio['descripcion'] ?? '-', 0, 50)) . '</td>
                </tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-warning mb-0">No se encontraron servicios.</div>';
        }
        
        echo '</div></div>';
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">
            <strong>Error al verificar servicios:</strong> ' . $e->getMessage() . '
        </div>';
    }
    
    echo '</div>'; // Fin test-section
    
    // 4. Información sobre la consulta SQL utilizada
    echo '<div class="test-section">
        <h3>4. Consulta SQL utilizada en el modelo</h3>
        <p>Esta es la consulta SQL que está siendo utilizada por el modelo para obtener las reservas:</p>
        
        <pre class="sql">SELECT 
    r.reserva_id,
    r.servicio_id,
    r.doctor_id,
    r.paciente_id,
    r.fecha_reserva,
    r.hora_inicio,
    r.hora_fin,
    r.reserva_estado as estado,
    r.observaciones,
    r.business_id,
    r.created_at,
    r.updated_at,
    r.agenda_id,
    r.sala_id,
    r.tarifa_id,
    -- Información del doctor usando person_id de la tabla rh_doctors
    COALESCE(doc_person.first_name || \' \' || doc_person.last_name, \'Doctor \' || r.doctor_id) as doctor_nombre,
    -- Información del paciente directamente de rh_person
    COALESCE(pac_person.first_name || \' \' || pac_person.last_name, \'Paciente \' || r.paciente_id) as paciente_nombre,
    -- Información del servicio desde rs_servicios si existe
    COALESCE(s.nombre, \'Servicio \' || r.servicio_id) as servicio_nombre
FROM servicios_reservas r
-- Join para obtener información del doctor
LEFT JOIN rh_doctors d ON r.doctor_id = d.doctor_id
LEFT JOIN rh_person doc_person ON d.person_id = doc_person.person_id
-- Join para obtener información del paciente directamente de rh_person
LEFT JOIN rh_person pac_person ON r.paciente_id = pac_person.person_id
-- Join para obtener información del servicio (usando rs_servicios en lugar de servicios_medicos)
LEFT JOIN rs_servicios s ON r.servicio_id = s.servicio_id
WHERE r.fecha_reserva = :fecha</pre>
    </div>';
    
    // 5. Logs recientes
    echo '<div class="test-section">
        <h3>5. Logs recientes</h3>';
    
    $logFile = 'c:/laragon/www/clinica/logs/reservas.log';
    
    if (file_exists($logFile)) {
        $logs = file($logFile);
        $logs = array_slice($logs, -20); // Mostrar últimas 20 líneas
        
        echo '<h4>Últimas entradas del log (reservas.log):</h4>
        <pre style="max-height: 300px; overflow-y: auto;">';
        foreach ($logs as $log) {
            echo htmlspecialchars($log);
        }
        echo '</pre>';
    } else {
        echo '<div class="alert alert-warning">
            El archivo de log ' . $logFile . ' no existe.
        </div>';
    }
    
    echo '</div>'; // Fin test-section
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">
        <h3>Error</h3>
        <p>' . $e->getMessage() . '</p>
    </div>';
}

echo '</div></div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
