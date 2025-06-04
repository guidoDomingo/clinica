<?php
/**
 * Archivo para probar la generación de slots de horarios simulando una fecha futura
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
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '2025-06-02';
$horaActual = date('H:i:s');

// Sobreescribir la función date() para las pruebas
function override_date($format, $timestamp = null) {
    if ($format === 'Y-m-d') {
        return '2025-06-01'; // Una fecha anterior a la de prueba
    }
    return \date($format, $timestamp);
}

// Generar slots
echo "<h2>Generando Slots para:</h2>";
echo "<ul>";
echo "<li><strong>Servicio ID:</strong> $servicioId</li>";
echo "<li><strong>Doctor ID:</strong> $doctorId</li>";
echo "<li><strong>Fecha:</strong> $fecha</li>";
echo "<li><strong>Hora actual del sistema (real):</strong> $horaActual</li>";
echo "<li><strong>Fecha simulada para pruebas:</strong> 2025-06-01</li>";
echo "</ul>";

// Clase de prueba que hereda de ModelServicios para sobreescribir funciones críticas
class TestModelServicios extends ModelServicios {
    // Sobreescribe la función mdlGenerarSlotsDisponibles para simular una fecha actual diferente
    static public function mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fecha) {
        try {
            error_log("Test Future: Generando slots disponibles para - ServicioID: {$servicioId}, DoctorID: {$doctorId}, Fecha: {$fecha}", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            
            // Obtener el servicio para conocer su duración
            $servicio = self::mdlObtenerServicioPorId($servicioId);
            
            if (!$servicio) {
                error_log("Test Future: ADVERTENCIA: Servicio ID {$servicioId} no encontrado. Usando duración predeterminada.", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            }
            
            // Duración predeterminada de 30 minutos si no se especifica o si el servicio no existe
            $duracionServicio = 30;
            if ($servicio && isset($servicio['duracion_minutos']) && $servicio['duracion_minutos'] > 0) {
                $duracionServicio = $servicio['duracion_minutos'];
            }
            
            error_log("Test Future: Servicio ID {$servicioId}: " . ($servicio ? "Encontrado" : "No encontrado") . 
                      ", Duración: {$duracionServicio} minutos", 3, 'c:/laragon/www/clinica/logs/servicios.log');
            
            // Para pruebas, simulamos que la fecha actual es 2025-06-01
            $fechaHoySimulada = '2025-06-01';
            
            // Verificar que el formato de la fecha sea correcto
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                error_log("Test Future: Formato de fecha incorrecto: " . $fecha, 3, 'c:/laragon/www/clinica/logs/database.log');
                return [];
            }
            
            // Asegurarse de que la fecha sea válida
            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
                error_log("Test Future: Fecha inválida: " . $fecha, 3, 'c:/laragon/www/clinica/logs/database.log');
                return [];
            }
            
            // El resto del código es igual que en la función original...
            // Determinar el día de la semana para la fecha
            $diaSemanaNum = (int)$fechaObj->format('N'); // 1 (lunes) a 7 (domingo) según ISO-8601
            
            // Convertir de formato ISO-8601 (1=lunes, 7=domingo) a formato de array (0=LUNES, 6=DOMINGO)
            $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
            $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
            
            // Obtener los horarios del médico consultando la base de datos
            $stmt = Conexion::conectar()->prepare(
                "SELECT 
                    ad.detalle_id AS horario_id,
                    ac.agenda_id,
                    ad.turno_id,
                    t.turno_nombre,
                    ad.sala_id,
                    s.sala_nombre,
                    ac.medico_id AS doctor_id,
                    COALESCE(p.first_name, '') || ' ' || COALESCE(p.last_name, '') AS nombre_doctor,
                    ad.dia_semana,
                    ad.hora_inicio,
                    ad.hora_fin,
                    ad.intervalo_minutos,
                    ad.cupo_maximo
                FROM 
                    agendas_detalle ad
                INNER JOIN 
                    agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                INNER JOIN 
                    turnos t ON ad.turno_id = t.turno_id
                INNER JOIN 
                    salas s ON ad.sala_id = s.sala_id
                INNER JOIN
                    rh_doctors rd ON rd.doctor_id = ac.medico_id 
                INNER JOIN 
                    rh_person p ON p.person_id = rd.person_id
                WHERE 
                    ac.medico_id = :doctor_id
                    AND ad.dia_semana = :dia_semana
                    AND ad.detalle_estado = true
                    AND ac.agenda_estado = true
                ORDER BY 
                    ad.hora_inicio ASC"
            );
            
            $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmt->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
            $stmt->execute();
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Test Future: Horarios encontrados para doctor_id={$doctorId}, dia={$diaSemanaTexto}: " . count($horarios), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            
            if (empty($horarios)) {
                error_log("Test Future: No se encontraron horarios para el día " . $diaSemanaTexto, 3, 'c:/laragon/www/clinica/logs/servicios.log');
                return [];
            }
            
            // Obtener reservas existentes
            $reservasExistentes = [];
            try {
                $stmt = Conexion::conectar()->prepare(
                    "SELECT 
                        reserva_id,
                        servicio_id,
                        agenda_id,
                        doctor_id,
                        fecha_reserva,
                        hora_inicio,
                        hora_fin
                    FROM 
                        servicios_reservas
                    WHERE 
                        doctor_id = :doctor_id
                        AND fecha_reserva = :fecha_reserva
                        AND reserva_estado IN ('CONFIRMADA', 'PENDIENTE')
                    ORDER BY 
                        hora_inicio ASC"
                );
                $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                $stmt->bindParam(":fecha_reserva", $fecha, PDO::PARAM_STR);
                $stmt->execute();
                $reservasExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Test Future: Reservas existentes para Doctor ID {$doctorId} en fecha {$fecha}: " . count($reservasExistentes), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            } catch (PDOException $e) {
                error_log("Test Future: Advertencia al obtener reservas existentes: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            }
            
            // Generar slots disponibles para cada horario base
            $slotsDisponibles = [];
            foreach ($horarios as $horario) {
                error_log("Test Future: Procesando horario: " . json_encode($horario), 3, 'c:/laragon/www/clinica/logs/servicios.log');
                
                // Convertir las cadenas de hora a objetos DateTime para manipulación
                $horaInicio = new DateTime($horario['hora_inicio']);
                $horaFin = new DateTime($horario['hora_fin']);
                $intervaloMinutos = $horario['intervalo_minutos'] ?? 30;
                
                // Ajustar el intervalo según duración del servicio
                $intervaloEfectivo = max($intervaloMinutos, $duracionServicio);
                
                // Crear una variable para llevar el seguimiento de la hora actual mientras generamos slots
                $horaActual = clone $horaInicio;
                
                error_log("Test Future: Generando slots desde " . $horaInicio->format('H:i:s') . " hasta " . $horaFin->format('H:i:s'), 3, 'c:/laragon/www/clinica/logs/servicios.log');
                
                // Generar slots mientras haya tiempo disponible
                while ($horaActual < $horaFin) {
                    $slotInicio = clone $horaActual;
                    $slotFin = clone $horaActual;
                    $slotFin->add(new DateInterval('PT' . $duracionServicio . 'M'));
                    
                    $slotDisponible = true;
                    
                    // IMPORTANTE: Para pruebas, simulamos que la fecha actual es diferente
                    if ($fecha === $fechaHoySimulada) {
                        $horaSistema = new DateTime('10:00:00'); // Simulamos que son las 10 AM
                        error_log("Test Future: Comparando slot " . $slotInicio->format('H:i:s') . " con hora simulada " . $horaSistema->format('H:i:s'), 3, 'c:/laragon/www/clinica/logs/servicios.log');
                        
                        if ($slotInicio < $horaSistema) {
                            error_log("Test Future: Slot eliminado por estar en el pasado de la fecha simulada", 3, 'c:/laragon/www/clinica/logs/servicios.log');
                            $slotDisponible = false;
                        }
                    } else {
                        error_log("Test Future: Fecha futura (" . $fecha . " vs " . $fechaHoySimulada . "), no se aplica restricción por hora", 3, 'c:/laragon/www/clinica/logs/servicios.log');
                    }
                    
                    // Verificar que el slot termine antes o a la misma hora que termina el horario
                    if ($slotFin > $horaFin) {
                        error_log("Test Future: Slot eliminado por terminar después del fin del horario", 3, 'c:/laragon/www/clinica/logs/servicios.log');
                        $slotDisponible = false;
                    }
                    
                    // Verificar colisiones con otras reservas
                    if ($slotDisponible && !empty($reservasExistentes)) {
                        foreach ($reservasExistentes as $reserva) {
                            $reservaInicio = new DateTime($reserva['hora_inicio']);
                            $reservaFin = new DateTime($reserva['hora_fin']);
                            
                            if (
                                ($slotInicio >= $reservaInicio && $slotInicio < $reservaFin) ||
                                ($slotFin > $reservaInicio && $slotFin <= $reservaFin) ||
                                ($slotInicio <= $reservaInicio && $slotFin >= $reservaFin) ||
                                ($slotInicio >= $reservaInicio && $slotFin <= $reservaFin)
                            ) {
                                error_log("Test Future: Slot eliminado por colisión con reserva existente", 3, 'c:/laragon/www/clinica/logs/servicios.log');
                                $slotDisponible = false;
                                break;
                            }
                        }
                    }
                    
                    // Si el slot está disponible, agregarlo a la lista
                    if ($slotDisponible) {
                        error_log("Test Future: Slot añadido: " . $slotInicio->format('H:i:s') . " - " . $slotFin->format('H:i:s'), 3, 'c:/laragon/www/clinica/logs/servicios.log');
                        
                        $slotsDisponibles[] = [
                            'agenda_id' => $horario['agenda_id'],
                            'horario_id' => $horario['horario_id'],
                            'turno_id' => $horario['turno_id'],
                            'turno_nombre' => $horario['turno_nombre'],
                            'sala_id' => $horario['sala_id'],
                            'sala_nombre' => $horario['sala_nombre'],
                            'doctor_id' => $horario['doctor_id'],
                            'nombre_doctor' => $horario['nombre_doctor'],
                            'hora_inicio' => $slotInicio->format('H:i:s'),
                            'hora_fin' => $slotFin->format('H:i:s'),
                            'duracion_minutos' => $duracionServicio,
                            'tipo_horario' => 'NORMAL',
                        ];
                    }
                    
                    // Avanzar al siguiente slot
                    $horaActual->add(new DateInterval('PT' . $intervaloEfectivo . 'M'));
                    error_log("Test Future: Avanzando al slot " . $horaActual->format('H:i:s'), 3, 'c:/laragon/www/clinica/logs/servicios.log');
                }
            }
            
            error_log("Test Future: Total de slots disponibles generados: " . count($slotsDisponibles), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            return $slotsDisponibles;
            
        } catch (Exception $e) {
            error_log("Test Future: Error al generar slots: " . $e->getMessage(), 3, 'c:/laragon/www/clinica/logs/servicios.log');
            return [];
        }
    }
}

// Obtener servicio
$servicio = ModelServicios::mdlObtenerServicioPorId($servicioId);
echo "<h3>Información del Servicio:</h3>";
echo "<pre>";
print_r($servicio);
echo "</pre>";

// Obtener horarios usando la clase de prueba
echo "<h3>Horarios Disponibles (con fecha simulada):</h3>";
$horarios = TestModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
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
        $logs = implode("<br>", array_slice(explode("\n", $logs), -40));
        echo "<div style='background: #f7f7f7; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto;'>";
        echo $logs;
        echo "</div>";
    } else {
        echo "<p>Log no disponible</p>";
    }
}
