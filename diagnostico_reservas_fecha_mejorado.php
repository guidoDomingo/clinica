<?php
/**
 * Script mejorado para diagnosticar reservas por fecha
 */
// Activar visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        .json-data {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #28a745;
            font-family: monospace;
            white-space: pre-wrap;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Diagnóstico de Reservas por Fecha</h1>';

// Obtener la fecha de hoy o la fecha proporcionada
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

echo '<div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h2 class="mb-0">Reservas para: ' . htmlspecialchars($fecha) . '</h2>
        <form method="GET" class="form-inline">
            <div class="input-group">
                <input type="date" name="fecha" class="form-control" value="' . htmlspecialchars($fecha) . '">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-light">Buscar</button>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body">';

try {
    // Verificar si la extensión PostgreSQL está habilitada
    if (!extension_loaded('pdo_pgsql')) {
        echo '<div class="alert alert-danger">
            <h4>La extensión pdo_pgsql no está habilitada</h4>
            <p>Esta es necesaria para conectar con PostgreSQL. <a href="check_and_enable_pgsql.php">Ir a la página de verificación</a></p>
        </div>';
        echo '</div></div></div></body></html>';
        exit;
    }
    
    // Verificar conexión a la base de datos
    $db = Conexion::conectar();
    
    if ($db === null) {
        echo '<div class="alert alert-danger">
            <h4>Error de conexión a la base de datos</h4>
            <p>No se pudo establecer conexión con la base de datos. Verifica que el servidor PostgreSQL esté en funcionamiento.</p>
            <p><a href="check_and_enable_pgsql.php" class="btn btn-primary">Verificar Configuración</a></p>
        </div>';
        echo '</div></div></div></body></html>';
        exit;
    }
    
    echo '<div class="alert alert-success">
        <h4>Conexión a la base de datos establecida correctamente</h4>
    </div>';
    
    // 1. Verificar si existen reservas para esta fecha con una consulta directa
    echo '<div class="test-section">
        <h3>1. Consulta Directa a la Base de Datos</h3>';
    
    $sql = "SELECT COUNT(*) as total FROM servicios_reservas WHERE fecha_reserva = :fecha";
    echo '<pre class="sql">' . htmlspecialchars($sql) . '</pre>';
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($count['total'] > 0) {
        echo '<div class="alert alert-success">
            <h4>Se encontraron ' . $count['total'] . ' reservas para el ' . htmlspecialchars($fecha) . '</h4>
        </div>';
    } else {
        echo '<div class="alert alert-warning">
            <h4>No se encontraron reservas para el ' . htmlspecialchars($fecha) . '</h4>
        </div>';
    }
    
    // 2. Validar formato de fecha
    echo '</div><div class="test-section">
        <h3>2. Validación de Formato de Fecha</h3>';
    
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $fechaFormateada = date('Y-m-d', strtotime($fecha));
        echo '<div class="alert alert-warning">
            <h4>El formato de fecha proporcionado no es YYYY-MM-DD</h4>
            <p>Fecha original: ' . htmlspecialchars($fecha) . '</p>
            <p>Fecha reformateada: ' . htmlspecialchars($fechaFormateada) . '</p>
        </div>';
        $fecha = $fechaFormateada;
    } else {
        echo '<div class="alert alert-success">
            <h4>El formato de fecha es correcto: ' . htmlspecialchars($fecha) . '</h4>
        </div>';
    }    // 3. Usar el método del modelo para obtener reservas
    echo '</div><div class="test-section">
        <h3>3. Consulta mediante el Modelo (mdlObtenerReservasPorFecha)</h3>';
    
    // Verificar que la clase del modelo exista
    if (class_exists('ModelServicios')) {
        $reservas = ModelServicios::mdlObtenerReservasPorFecha($fecha);
    } else {
        echo '<div class="alert alert-danger">
            <h4>Error: Clase ModelServicios no encontrada</h4>
            <p>Asegúrate de que el archivo model/servicios.model.php esté correctamente incluido.</p>
        </div>';
        $reservas = false;
    }
    echo '<h4>Resultado de ModeloServicios::mdlObtenerReservasPorFecha</h4>';
    if ($reservas === false) {
        echo '<div class="alert alert-danger">
            <h4>La función del modelo devolvió FALSE</h4>
            <p>Ocurrió un error al ejecutar la consulta.</p>
        </div>';
    } else if (empty($reservas)) {
        echo '<div class="alert alert-warning">
            <h4>La función del modelo devolvió un array vacío</h4>
            <p>No se encontraron reservas para la fecha ' . htmlspecialchars($fecha) . '</p>
        </div>';
    } else {
        echo '<div class="alert alert-success">
            <h4>La función del modelo devolvió ' . count($reservas) . ' reservas</h4>
        </div>';
        
        // Mostrar en formato JSON para depuración
        echo '<div class="json-data">' . json_encode($reservas, JSON_PRETTY_PRINT) . '</div>';
        
        // Mostrar en tabla para facilitar la lectura
        echo '<div class="table-responsive">
            <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Doctor</th>
                    <th>Paciente</th>
                    <th>Servicio</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($reservas as $reserva) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($reserva['reserva_id'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($reserva['doctor_nombre'] ?? $reserva['nombre_doctor'] ?? ('Dr. ' . ($reserva['doctor_id'] ?? '?'))) . '</td>';
            echo '<td>' . htmlspecialchars($reserva['paciente_nombre'] ?? ('Paciente ' . ($reserva['paciente_id'] ?? '?'))) . '</td>';
            echo '<td>' . htmlspecialchars($reserva['servicio_nombre'] ?? $reserva['nombre_servicio'] ?? ('Servicio ' . ($reserva['servicio_id'] ?? '?'))) . '</td>';
            echo '<td>' . htmlspecialchars($reserva['fecha_reserva'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($reserva['hora_inicio'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($reserva['estado_reserva'] ?? 'N/A') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table></div>';
    }
    
    // 4. Examinar la estructura y relaciones de las tablas
    echo '</div><div class="test-section">
        <h3>4. Estructura de la Tabla servicios_reservas</h3>';
    
    $sql = "SELECT column_name, data_type, is_nullable 
            FROM information_schema.columns 
            WHERE table_name = 'servicios_reservas' 
            ORDER BY ordinal_position";
    
    $stmt = $db->query($sql);
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<div class="table-responsive">
        <table class="table table-sm">
        <thead>
            <tr>
                <th>Columna</th>
                <th>Tipo de Dato</th>
                <th>¿Puede ser Nulo?</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($columnas as $col) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($col['column_name']) . '</td>';
        echo '<td>' . htmlspecialchars($col['data_type']) . '</td>';
        echo '<td>' . htmlspecialchars($col['is_nullable']) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table></div>';
    
    // 5. Consultar directamente servicios_reservas para la fecha especificada
    echo '</div><div class="test-section">
        <h3>5. Consulta Directa de servicios_reservas</h3>';
    
    $sql = "SELECT * FROM servicios_reservas WHERE fecha_reserva = :fecha";
    echo '<pre class="sql">' . htmlspecialchars($sql) . '</pre>';
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
    $stmt->execute();
    $reservasDirectas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($reservasDirectas)) {
        echo '<div class="alert alert-warning">
            <h4>No se encontraron registros directamente en la tabla servicios_reservas</h4>
        </div>';
    } else {
        echo '<div class="alert alert-success">
            <h4>Se encontraron ' . count($reservasDirectas) . ' registros directamente en la tabla</h4>
        </div>';
        
        // Mostrar en formato JSON para depuración
        echo '<div class="json-data">' . json_encode($reservasDirectas, JSON_PRETTY_PRINT) . '</div>';
    }
    
    // 6. Verificar relaciones con otras tablas
    echo '</div><div class="test-section">
        <h3>6. Verificación de Relaciones con Otras Tablas</h3>';
    
    // a. Verificar doctores
    if (!empty($reservasDirectas)) {
        $doctorIds = array_map(function($r) { return $r['doctor_id']; }, $reservasDirectas);
        $doctorIds = array_unique($doctorIds);
        $placeholders = implode(',', array_fill(0, count($doctorIds), '?'));
        
        $sql = "SELECT d.doctor_id, d.person_id, p.first_name, p.last_name 
                FROM rh_doctors d
                LEFT JOIN rh_person p ON d.person_id = p.person_id
                WHERE d.doctor_id IN ($placeholders)";
        
        $stmt = $db->prepare($sql);
        foreach ($doctorIds as $i => $id) {
            $stmt->bindValue($i + 1, $id);
        }
        $stmt->execute();
        $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<h4>Información de Doctores</h4>';
        if (empty($doctores)) {
            echo '<div class="alert alert-warning">
                <h4>No se encontró información de doctores relacionada</h4>
            </div>';
        } else {
            echo '<div class="table-responsive">
                <table class="table table-sm">
                <thead>
                    <tr>
                        <th>ID Doctor</th>
                        <th>ID Persona</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($doctores as $doc) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($doc['doctor_id']) . '</td>';
                echo '<td>' . htmlspecialchars($doc['person_id']) . '</td>';
                echo '<td>' . htmlspecialchars($doc['first_name']) . '</td>';
                echo '<td>' . htmlspecialchars($doc['last_name']) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table></div>';
        }
    }

    // 7. Ejemplos de reservas manuales
    echo '</div><div class="test-section">
        <h3>7. Ejemplo de Consulta SQL con JOIN Completo</h3>';
    
    $sql = "SELECT 
       r.reserva_id,
       r.doctor_id,
       r.paciente_id,
       r.servicio_id,
       r.fecha_reserva,
       r.hora_inicio,
       r.estado_reserva,
       COALESCE(doc_person.first_name || ' ' || doc_person.last_name, 'Doctor ' || r.doctor_id) as doctor_nombre,
       COALESCE(pac_person.first_name || ' ' || pac_person.last_name, 'Paciente ' || r.paciente_id) as paciente_nombre,
       COALESCE(s.nombre, 'Servicio ' || r.servicio_id) as servicio_nombre
    FROM servicios_reservas r
    LEFT JOIN rh_doctors d ON r.doctor_id = d.doctor_id
    LEFT JOIN rh_person doc_person ON d.person_id = doc_person.person_id
    LEFT JOIN rh_person pac_person ON r.paciente_id = pac_person.person_id
    LEFT JOIN rs_servicios s ON r.servicio_id = s.servicio_id
    WHERE r.fecha_reserva = :fecha";
    
    echo '<pre class="sql">' . htmlspecialchars($sql) . '</pre>';
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
    $stmt->execute();
    $reservasCompletas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($reservasCompletas)) {
        echo '<div class="alert alert-warning">
            <h4>La consulta JOIN completa no devolvió resultados</h4>
        </div>';
    } else {
        echo '<div class="alert alert-success">
            <h4>La consulta JOIN completa devolvió ' . count($reservasCompletas) . ' resultados</h4>
        </div>';
        
        // Mostrar en formato JSON para depuración
        echo '<div class="json-data">' . json_encode($reservasCompletas, JSON_PRETTY_PRINT) . '</div>';
        
        // Mostrar en tabla para facilitar la lectura
        echo '<div class="table-responsive">
            <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Doctor</th>
                    <th>Paciente</th>
                    <th>Servicio</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($reservasCompletas as $reserva) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($reserva['reserva_id'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($reserva['doctor_nombre'] ?? ('Doctor ' . ($reserva['doctor_id'] ?? '?'))) . '</td>';
            echo '<td>' . htmlspecialchars($reserva['paciente_nombre'] ?? ('Paciente ' . ($reserva['paciente_id'] ?? '?'))) . '</td>';
            echo '<td>' . htmlspecialchars($reserva['servicio_nombre'] ?? ('Servicio ' . ($reserva['servicio_id'] ?? '?'))) . '</td>';
            echo '<td>' . htmlspecialchars($reserva['fecha_reserva'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($reserva['hora_inicio'] ?? 'N/A') . '</td>';
            echo '<td>' . htmlspecialchars($reserva['estado_reserva'] ?? 'N/A') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table></div>';
    }
    
    // 8. Crear datos de ejemplo si no hay reservas
    if (isset($_GET['crear_ejemplo']) && $_GET['crear_ejemplo'] == '1') {
        echo '</div><div class="test-section">
            <h3>8. Creación de Datos de Ejemplo</h3>';
        
        // Verificar si ya existen reservas para esta fecha
        if (!empty($reservasDirectas)) {
            echo '<div class="alert alert-warning">
                <h4>Ya existen reservas para la fecha ' . htmlspecialchars($fecha) . '</h4>
                <p>No se crearon ejemplos para evitar duplicados.</p>
            </div>';
        } else {
            // Obtener doctor_id de la tabla rh_doctors
            $stmt = $db->query("SELECT doctor_id FROM rh_doctors LIMIT 1");
            $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
            $doctorId = $doctor ? $doctor['doctor_id'] : 1;
            
            // Obtener person_id de la tabla rh_person
            $stmt = $db->query("SELECT person_id FROM rh_person LIMIT 1");
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);
            $personId = $persona ? $persona['person_id'] : 1;
            
            // Obtener servicio_id de la tabla rs_servicios
            $stmt = $db->query("SELECT servicio_id FROM rs_servicios LIMIT 1");
            $servicio = $stmt->fetch(PDO::FETCH_ASSOC);
            $servicioId = $servicio ? $servicio['servicio_id'] : 1;
            
            // Crear ejemplo de reserva
            $sql = "INSERT INTO servicios_reservas 
                    (doctor_id, paciente_id, servicio_id, fecha_reserva, hora_inicio, estado_reserva) 
                    VALUES (:doctor_id, :paciente_id, :servicio_id, :fecha, :hora, 'CONFIRMADA')";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':doctor_id', $doctorId);
            $stmt->bindParam(':paciente_id', $personId);
            $stmt->bindParam(':servicio_id', $servicioId);
            $stmt->bindParam(':fecha', $fecha);
            $hora = '09:00:00';
            $stmt->bindParam(':hora', $hora);
            
            if ($stmt->execute()) {
                $reservaId = $db->lastInsertId();
                echo '<div class="alert alert-success">
                    <h4>¡Ejemplo creado exitosamente!</h4>
                    <p>Se ha creado una reserva de ejemplo con ID: ' . $reservaId . '</p>
                    <p><a href="?fecha=' . htmlspecialchars($fecha) . '" class="btn btn-info">Refrescar página para ver la reserva</a></p>
                </div>';
            } else {
                echo '<div class="alert alert-danger">
                    <h4>Error al crear ejemplo</h4>
                    <p>No se pudo crear la reserva de ejemplo.</p>
                </div>';
            }
        }
    } else {
        echo '</div><div class="test-section">
            <h3>8. Crear Datos de Ejemplo</h3>
            <p>Si no hay reservas para esta fecha, puedes crear un ejemplo para realizar pruebas:</p>
            <p><a href="?fecha=' . htmlspecialchars($fecha) . '&crear_ejemplo=1" class="btn btn-warning">Crear Reserva de Ejemplo</a></p>';
    }

} catch (Exception $e) {
    echo '<div class="alert alert-danger">
        <h4>Error</h4>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
        <p>En el archivo: ' . htmlspecialchars($e->getFile()) . ' línea ' . $e->getLine() . '</p>
    </div>';
}

echo '</div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
?>
