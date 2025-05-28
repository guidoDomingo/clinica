<?php
// Script diagnóstico para verificar el problema de visualización de reservas

// Definir ruta del archivo de log
$logFile = 'c:/laragon/www/clinica/logs/diagnostico_reservas.log';

// Función para registrar mensajes
function log_message($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . ": " . $message . PHP_EOL, FILE_APPEND);
}

log_message("Iniciando diagnóstico de visualización de reservas");

// Incluir archivos necesarios
require_once 'config/config.php';
require_once 'model/conexion.php';
require_once 'model/servicios.model.php';
require_once 'controller/servicios.controller.php';

// Parámetros
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;

log_message("Parámetros: fecha=$fecha, doctor_id=" . ($doctorId ?? "null"));

// Probar conexión a la base de datos
try {
    $conn = Conexion::conectar();
    log_message("Conexión a base de datos establecida correctamente");
} catch (Exception $e) {
    log_message("ERROR: No se pudo conectar a la base de datos: " . $e->getMessage());
    die("Error de conexión");
}

// Verificar si la tabla servicios_reservas existe
try {
    $stmtCheck = $conn->prepare("SELECT to_regclass('public.servicios_reservas')");
    $stmtCheck->execute();
    $tablaReservasExiste = $stmtCheck->fetchColumn();
    
    if (!$tablaReservasExiste) {
        log_message("ERROR: La tabla servicios_reservas no existe");
    } else {
        log_message("La tabla servicios_reservas existe en la base de datos");
    }
} catch (Exception $e) {
    log_message("ERROR al verificar tabla: " . $e->getMessage());
}

// Obtener reservas directamente de la tabla
try {
    $sqlDirect = "SELECT * FROM servicios_reservas WHERE fecha_reserva = :fecha";
    if ($doctorId !== null) {
        $sqlDirect .= " AND doctor_id = :doctor_id";
    }
    
    $stmtDirect = $conn->prepare($sqlDirect);
    $stmtDirect->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    
    if ($doctorId !== null) {
        $stmtDirect->bindParam(':doctor_id', $doctorId, PDO::PARAM_INT);
    }
    
    $stmtDirect->execute();
    $reservasDirect = $stmtDirect->fetchAll(PDO::PARAM_STR);
    
    log_message("Consulta directa a la tabla: Encontradas " . count($reservasDirect) . " reservas");
    if (count($reservasDirect) > 0) {
        log_message("Primera reserva (consulta directa): " . json_encode($reservasDirect[0]));
    }
} catch (Exception $e) {
    log_message("ERROR en consulta directa: " . $e->getMessage());
}

// Probar el modelo a través del controlador
try {
    log_message("Probando el modelo a través del controlador...");
    $reservas = ControladorServicios::ctrObtenerReservasPorFecha($fecha, $doctorId);
    log_message("Resultado del controlador: " . count($reservas) . " reservas encontradas");
    
    if (count($reservas) > 0) {
        log_message("Primera reserva (controlador): " . json_encode($reservas[0]));
    } else {
        log_message("El controlador no encontró reservas para la fecha");
    }
} catch (Exception $e) {
    log_message("ERROR en el controlador: " . $e->getMessage());
}

// Mostrar resultados del diagnóstico
echo "<h1>Diagnóstico de Visualización de Reservas</h1>";
echo "<p>Fecha: $fecha" . ($doctorId ? ", Doctor ID: $doctorId" : "") . "</p>";

// Mostrar resultados de consulta directa
echo "<h2>Reservas (Consulta Directa)</h2>";
if (!empty($reservasDirect)) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Doctor</th><th>Fecha</th><th>Hora</th><th>Estado</th></tr>";
    
    foreach ($reservasDirect as $r) {
        echo "<tr>";
        echo "<td>" . $r['reserva_id'] . "</td>";
        echo "<td>" . $r['doctor_id'] . "</td>";
        echo "<td>" . $r['fecha_reserva'] . "</td>";
        echo "<td>" . $r['hora_inicio'] . " - " . $r['hora_fin'] . "</td>";
        echo "<td>" . ($r['reserva_estado'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No se encontraron reservas mediante consulta directa</p>";
}

// Mostrar resultados del controlador
echo "<h2>Reservas (Controlador)</h2>";
if (!empty($reservas)) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Doctor</th><th>Paciente</th><th>Fecha</th><th>Hora</th><th>Estado</th></tr>";
    
    foreach ($reservas as $r) {
        echo "<tr>";
        echo "<td>" . $r['reserva_id'] . "</td>";
        echo "<td>" . ($r['doctor_nombre'] ?? $r['doctor_id']) . "</td>";
        echo "<td>" . ($r['paciente_nombre'] ?? $r['paciente_id']) . "</td>";
        echo "<td>" . $r['fecha_reserva'] . "</td>";
        echo "<td>" . $r['hora_inicio'] . " - " . $r['hora_fin'] . "</td>";
        echo "<td>" . ($r['estado'] ?? $r['reserva_estado'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No se encontraron reservas mediante el controlador</p>";
}

// Mostrar diagnóstico completo
echo "<h2>Log de Diagnóstico</h2>";
echo "<pre>" . file_get_contents($logFile) . "</pre>";
?>
