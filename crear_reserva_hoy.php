<?php
/**
 * Script para crear una reserva de prueba para hoy
 */
require_once "model/conexion.php";

// Activar visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Crear Reserva de Prueba para HOY</h1>";

// Obtener la fecha de hoy
$fecha = date('Y-m-d');
echo "<p>Fecha objetivo: $fecha</p>";

try {
    // Verificar conexión a la base de datos
    $db = Conexion::conectar();
    
    if ($db === null) {
        echo "<p>Error: No se pudo conectar a la base de datos</p>";
        exit;
    }
    
    echo "<p>Conexión a la base de datos: OK</p>";
    
    // 1. Verificar si existe la tabla servicios_reservas
    $sql = "SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'servicios_reservas')";
    $existe = $db->query($sql)->fetchColumn();
    
    if (!$existe) {
        echo "<p>Error: La tabla servicios_reservas no existe</p>";
        exit;
    }
    
    echo "<p>Tabla servicios_reservas: OK</p>";
    
    // 2. Obtener un doctor_id válido
    $sql = "SELECT d.doctor_id, p.first_name, p.last_name FROM rh_doctors d 
            JOIN rh_person p ON d.person_id = p.person_id LIMIT 1";
    $stmt = $db->query($sql);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doctor) {
        echo "<p>Error: No se encontró ningún médico en la base de datos</p>";
        // Usar un ID predeterminado en caso de no encontrar
        $doctorId = 1;
        $doctorName = "Doctor de prueba";
    } else {
        $doctorId = $doctor['doctor_id'];
        $doctorName = $doctor['first_name'] . ' ' . $doctor['last_name'];
        echo "<p>Doctor seleccionado: $doctorName (ID: $doctorId)</p>";
    }
    
    // 3. Obtener un paciente_id válido
    $sql = "SELECT person_id, first_name, last_name FROM rh_person LIMIT 1";
    $stmt = $db->query($sql);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$paciente) {
        echo "<p>Error: No se encontró ningún paciente en la base de datos</p>";
        // Usar un ID predeterminado
        $pacienteId = 1;
        $pacienteName = "Paciente de prueba";
    } else {
        $pacienteId = $paciente['person_id'];
        $pacienteName = $paciente['first_name'] . ' ' . $paciente['last_name'];
        echo "<p>Paciente seleccionado: $pacienteName (ID: $pacienteId)</p>";
    }
    
    // 4. Obtener un servicio_id válido
    $sql = "SELECT servicio_id, nombre FROM rs_servicios LIMIT 1";
    $stmt = $db->query($sql);
    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$servicio) {
        echo "<p>Error: No se encontró ningún servicio en la base de datos</p>";
        // Usar un ID predeterminado
        $servicioId = 1;
        $servicioName = "Servicio de prueba";
    } else {
        $servicioId = $servicio['servicio_id'];
        $servicioName = $servicio['nombre'];
        echo "<p>Servicio seleccionado: $servicioName (ID: $servicioId)</p>";
    }
    
    // 5. Verificar si ya existe una reserva para esta combinación
    $sql = "SELECT COUNT(*) FROM servicios_reservas 
            WHERE fecha_reserva = :fecha 
            AND doctor_id = :doctor_id 
            AND paciente_id = :paciente_id 
            AND servicio_id = :servicio_id";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':doctor_id', $doctorId);
    $stmt->bindParam(':paciente_id', $pacienteId);
    $stmt->bindParam(':servicio_id', $servicioId);
    $stmt->execute();
    
    $existeReserva = $stmt->fetchColumn();
    
    if ($existeReserva) {
        echo "<p>Ya existe una reserva para este doctor, paciente y servicio en esta fecha.</p>";
        
        // Mostrar la reserva existente
        $sql = "SELECT * FROM servicios_reservas 
                WHERE fecha_reserva = :fecha 
                AND doctor_id = :doctor_id 
                AND paciente_id = :paciente_id 
                AND servicio_id = :servicio_id";
        
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':doctor_id', $doctorId);
        $stmt->bindParam(':paciente_id', $pacienteId);
        $stmt->bindParam(':servicio_id', $servicioId);
        $stmt->execute();
        
        $reservaExistente = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Información de la reserva existente:</p>";
        echo "<pre>";
        print_r($reservaExistente);
        echo "</pre>";
    } else {
        // 6. Crear la nueva reserva
        $horaInicio = '09:00:00';
        $horaFin = '09:30:00';
        $estado = 'CONFIRMADA';
        
        // Mostrar los datos que se van a insertar
        echo "<h2>Datos a insertar</h2>";
        echo "<ul>";
        echo "<li><strong>Fecha:</strong> $fecha</li>";
        echo "<li><strong>Hora inicio:</strong> $horaInicio</li>";
        echo "<li><strong>Hora fin:</strong> $horaFin</li>";
        echo "<li><strong>Doctor ID:</strong> $doctorId</li>";
        echo "<li><strong>Paciente ID:</strong> $pacienteId</li>";
        echo "<li><strong>Servicio ID:</strong> $servicioId</li>";
        echo "<li><strong>Estado:</strong> $estado</li>";
        echo "</ul>";
        
        // Columnas de la tabla
        $sql = "SELECT column_name FROM information_schema.columns 
                WHERE table_name = 'servicios_reservas' 
                ORDER BY ordinal_position";
        $columnas = $db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Columnas en la tabla servicios_reservas:</p>";
        echo "<pre>";
        print_r($columnas);
        echo "</pre>";
        
        try {
            // Intentar insertar con un conjunto mínimo de columnas
            $sql = "INSERT INTO servicios_reservas 
                    (doctor_id, paciente_id, servicio_id, fecha_reserva, hora_inicio, hora_fin, estado_reserva) 
                    VALUES (:doctor_id, :paciente_id, :servicio_id, :fecha, :hora_inicio, :hora_fin, :estado)";
            
            echo "<p>SQL a ejecutar: " . htmlspecialchars($sql) . "</p>";
            
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':doctor_id', $doctorId);
            $stmt->bindParam(':paciente_id', $pacienteId);
            $stmt->bindParam(':servicio_id', $servicioId);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':hora_inicio', $horaInicio);
            $stmt->bindParam(':hora_fin', $horaFin);
            $stmt->bindParam(':estado', $estado);
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $reservaId = $db->lastInsertId();
                echo "<p style='color:green;font-weight:bold;'>¡Reserva creada con éxito! ID: $reservaId</p>";
                
                // Mostrar la nueva reserva
                $sql = "SELECT * FROM servicios_reservas WHERE reserva_id = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':id', $reservaId);
                $stmt->execute();
                $nuevaReserva = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<p>Detalles de la nueva reserva:</p>";
                echo "<pre>";
                print_r($nuevaReserva);
                echo "</pre>";
                
                echo "<h3>Ver todas las reservas:</h3>";
                echo "<p><a href='listar_todas_reservas.php' style='padding:10px;background:#007bff;color:white;text-decoration:none;border-radius:5px;'>Ver todas las reservas</a></p>";
                
            } else {
                echo "<p style='color:red;font-weight:bold;'>Error al crear la reserva</p>";
                echo "<p>Error info: ";
                print_r($stmt->errorInfo());
                echo "</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color:red;font-weight:bold;'>Error de PDO al crear la reserva: " . $e->getMessage() . "</p>";
            echo "<p>Traza: " . $e->getTraceAsString() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;font-weight:bold;'>Error general: " . $e->getMessage() . "</p>";
    echo "<p>En archivo: " . $e->getFile() . ", línea " . $e->getLine() . "</p>";
    echo "<p>Traza: " . $e->getTraceAsString() . "</p>";
}
?>
