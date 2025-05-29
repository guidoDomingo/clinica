<?php
/**
 * Script para crear reservas de prueba para fechas específicas
 * Este script nos ayudará a verificar si el sistema de visualización funciona correctamente
 */
require_once "model/conexion.php";

// Cabecera HTML
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Reservas de Prueba</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Crear Reservas de Prueba</h1>
        <p class="lead">Use este formulario para crear reservas de prueba en fechas específicas</p>';

try {
    $db = Conexion::conectar();
    
    // Verificar si existen las tablas necesarias
    $tablasNecesarias = ['servicios_reservas', 'rh_doctors', 'rh_person', 'rs_servicios'];
    $tablasNoExistentes = [];
    
    foreach ($tablasNecesarias as $tabla) {
        $stmt = $db->prepare("SELECT to_regclass('public.$tabla')");
        $stmt->execute();
        $existe = $stmt->fetchColumn();
        
        if (!$existe) {
            $tablasNoExistentes[] = $tabla;
        }
    }
    
    if (!empty($tablasNoExistentes)) {
        echo '<div class="alert alert-danger">
            <strong>Error:</strong> Las siguientes tablas necesarias no existen: ' . implode(', ', $tablasNoExistentes) . '
        </div>';
        exit;
    }
    
    // Obtener doctores disponibles
    $stmt = $db->prepare("
        SELECT d.doctor_id, p.first_name || ' ' || p.last_name AS nombre
        FROM rh_doctors d
        JOIN rh_person p ON d.person_id = p.person_id
    ");
    $stmt->execute();
    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener pacientes disponibles (primeros 10)
    $stmt = $db->prepare("
        SELECT person_id, first_name || ' ' || last_name AS nombre
        FROM rh_person
        LIMIT 10
    ");
    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener servicios disponibles
    try {
        $stmt = $db->prepare("SELECT servicio_id, nombre FROM rs_servicios");
        $stmt->execute();
        $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $servicios = [];
    }
    
    // Si no hay servicios, crear algunos
    if (empty($servicios)) {
        echo '<div class="alert alert-warning">
            No se encontraron servicios. Creando servicios de prueba...
        </div>';
        
        try {
            // Verificar si existe la tabla rs_servicios
            $stmt = $db->prepare("SELECT to_regclass('public.rs_servicios')");
            $stmt->execute();
            $tablaServiciosExiste = $stmt->fetchColumn();
            
            if (!$tablaServiciosExiste) {
                // Crear la tabla rs_servicios
                $db->exec("
                    CREATE TABLE rs_servicios (
                        servicio_id SERIAL PRIMARY KEY,
                        nombre VARCHAR(255) NOT NULL,
                        descripcion TEXT,
                        duracion INTEGER DEFAULT 30,
                        precio NUMERIC(10,2),
                        is_active BOOLEAN DEFAULT TRUE,
                        business_id INTEGER DEFAULT 1,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            }
            
            // Insertar algunos servicios de prueba
            $serviciosPrueba = [
                ['nombre' => 'Consulta General', 'descripcion' => 'Consulta médica general', 'duracion' => 30, 'precio' => 50.00],
                ['nombre' => 'Consulta Especializada', 'descripcion' => 'Consulta con especialista', 'duracion' => 45, 'precio' => 80.00],
                ['nombre' => 'Control de Rutina', 'descripcion' => 'Control médico periódico', 'duracion' => 20, 'precio' => 40.00]
            ];
            
            foreach ($serviciosPrueba as $servicio) {
                $stmt = $db->prepare("
                    INSERT INTO rs_servicios (nombre, descripcion, duracion, precio) 
                    VALUES (:nombre, :descripcion, :duracion, :precio)
                    RETURNING servicio_id
                ");
                $stmt->bindParam(':nombre', $servicio['nombre']);
                $stmt->bindParam(':descripcion', $servicio['descripcion']);
                $stmt->bindParam(':duracion', $servicio['duracion']);
                $stmt->bindParam(':precio', $servicio['precio']);
                $stmt->execute();
            }
            
            // Cargar los servicios recién creados
            $stmt = $db->prepare("SELECT servicio_id, nombre FROM rs_servicios");
            $stmt->execute();
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">
                <strong>Error al crear servicios:</strong> ' . $e->getMessage() . '
            </div>';
        }
    }
    
    // Formulario para crear reserva
    echo '<div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h2>Crear Nueva Reserva de Prueba</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="fecha_reserva">Fecha de la Reserva:</label>
                    <input type="date" class="form-control" id="fecha_reserva" name="fecha_reserva" value="' . date('Y-m-d') . '" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="doctor_id">Doctor:</label>
                        <select class="form-control" id="doctor_id" name="doctor_id" required>';
                        
    if (!empty($doctores)) {
        foreach ($doctores as $doctor) {
            echo '<option value="' . $doctor['doctor_id'] . '">' . htmlspecialchars($doctor['nombre']) . ' (ID: ' . $doctor['doctor_id'] . ')</option>';
        }
    } else {
        echo '<option value="">No hay doctores disponibles</option>';
    }
                        
    echo '</select>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="paciente_id">Paciente:</label>
                        <select class="form-control" id="paciente_id" name="paciente_id" required>';
                        
    if (!empty($pacientes)) {
        foreach ($pacientes as $paciente) {
            echo '<option value="' . $paciente['person_id'] . '">' . htmlspecialchars($paciente['nombre']) . ' (ID: ' . $paciente['person_id'] . ')</option>';
        }
    } else {
        echo '<option value="">No hay pacientes disponibles</option>';
    }
                        
    echo '</select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="servicio_id">Servicio:</label>
                    <select class="form-control" id="servicio_id" name="servicio_id" required>';
                    
    if (!empty($servicios)) {
        foreach ($servicios as $servicio) {
            echo '<option value="' . $servicio['servicio_id'] . '">' . htmlspecialchars($servicio['nombre']) . ' (ID: ' . $servicio['servicio_id'] . ')</option>';
        }
    } else {
        echo '<option value="">No hay servicios disponibles</option>';
    }
                    
    echo '</select>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="hora_inicio">Hora de Inicio:</label>
                        <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" value="09:00" required>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="hora_fin">Hora de Fin:</label>
                        <input type="time" class="form-control" id="hora_fin" name="hora_fin" value="09:30" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <select class="form-control" id="estado" name="estado">
                        <option value="PENDIENTE">PENDIENTE</option>
                        <option value="CONFIRMADA">CONFIRMADA</option>
                        <option value="CANCELADA">CANCELADA</option>
                        <option value="COMPLETADA">COMPLETADA</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="observaciones">Observaciones:</label>
                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                </div>
                
                <button type="submit" name="crear_reserva" class="btn btn-primary">Crear Reserva</button>
                <a href="diagnostico_reservas_fecha.php" class="btn btn-info ml-2">Ver Reservas</a>
            </form>
        </div>
    </div>';
    
    // Procesar el formulario de creación
    if (isset($_POST['crear_reserva'])) {
        $fechaReserva = $_POST['fecha_reserva'];
        $doctorId = intval($_POST['doctor_id']);
        $pacienteId = intval($_POST['paciente_id']);
        $servicioId = intval($_POST['servicio_id']);
        $horaInicio = $_POST['hora_inicio'];
        $horaFin = $_POST['hora_fin'];
        $estado = $_POST['estado'];
        $observaciones = $_POST['observaciones'];
        
        try {
            $sql = "INSERT INTO servicios_reservas (
                    doctor_id, paciente_id, servicio_id, fecha_reserva, 
                    hora_inicio, hora_fin, reserva_estado, observaciones, 
                    business_id, created_by, created_at
                ) VALUES (
                    :doctor_id, :paciente_id, :servicio_id, :fecha_reserva,
                    :hora_inicio, :hora_fin, :estado, :observaciones,
                    1, 1, CURRENT_TIMESTAMP
                ) RETURNING reserva_id";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':doctor_id', $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(':paciente_id', $pacienteId, PDO::PARAM_INT);
            $stmt->bindParam(':servicio_id', $servicioId, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_reserva', $fechaReserva, PDO::PARAM_STR);
            $stmt->bindParam(':hora_inicio', $horaInicio, PDO::PARAM_STR);
            $stmt->bindParam(':hora_fin', $horaFin, PDO::PARAM_STR);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
            
            $stmt->execute();
            $reservaId = $stmt->fetchColumn();
            
            echo '<div class="alert alert-success">
                <strong>¡Éxito!</strong> Se ha creado la reserva #' . $reservaId . ' para la fecha ' . $fechaReserva . '.
                <a href="diagnostico_reservas_fecha.php?fecha=' . $fechaReserva . '" class="btn btn-sm btn-info ml-2">Ver Reservas</a>
            </div>';
            
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">
                <strong>Error al crear la reserva:</strong> ' . $e->getMessage() . '
            </div>';
        }
    }
    
    // Obtener últimas reservas creadas para mostrar
    $stmt = $db->prepare("
        SELECT 
            r.reserva_id,
            r.fecha_reserva,
            r.hora_inicio,
            r.hora_fin,
            r.reserva_estado,
            dp.first_name || ' ' || dp.last_name AS doctor_nombre,
            pp.first_name || ' ' || pp.last_name AS paciente_nombre,
            s.nombre AS servicio_nombre
        FROM 
            servicios_reservas r
        LEFT JOIN rh_doctors d ON r.doctor_id = d.doctor_id
        LEFT JOIN rh_person dp ON d.person_id = dp.person_id
        LEFT JOIN rh_person pp ON r.paciente_id = pp.person_id
        LEFT JOIN rs_servicios s ON r.servicio_id = s.servicio_id
        ORDER BY 
            r.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $ultimasReservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($ultimasReservas)) {
        echo '<div class="card">
            <div class="card-header bg-success text-white">
                <h2>Últimas Reservas Creadas</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Doctor</th>
                                <th>Paciente</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        foreach ($ultimasReservas as $reserva) {
            echo '<tr>
                <td>' . $reserva['reserva_id'] . '</td>
                <td>' . $reserva['fecha_reserva'] . '</td>
                <td>' . substr($reserva['hora_inicio'], 0, 5) . ' - ' . substr($reserva['hora_fin'], 0, 5) . '</td>
                <td>' . htmlspecialchars($reserva['doctor_nombre'] ?? 'Sin nombre') . '</td>
                <td>' . htmlspecialchars($reserva['paciente_nombre'] ?? 'Sin nombre') . '</td>
                <td>' . htmlspecialchars($reserva['servicio_nombre'] ?? 'Sin nombre') . '</td>
                <td>' . $reserva['reserva_estado'] . '</td>
            </tr>';
        }
        
        echo '</tbody>
                    </table>
                </div>
            </div>
        </div>';
    }
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">
        <strong>Error de conexión:</strong> ' . $e->getMessage() . '
    </div>';
}

echo '</div>
</body>
</html>';
