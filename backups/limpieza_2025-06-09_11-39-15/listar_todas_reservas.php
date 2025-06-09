<?php
/**
 * Script simple para listar todas las reservas en la BD
 */
require_once "model/conexion.php";
require_once "model/servicios.model.php";

// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Lista de Todas las Reservas</h1>";

// Botones para diferentes acciones
echo '<div style="margin-bottom: 20px;">
    <a href="crear_reserva_hoy.php" style="margin-right: 10px; padding: 8px 12px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px;">Crear Reserva HOY</a>
    <a href="api_test_reservas.php" style="margin-right: 10px; padding: 8px 12px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">Probar API</a>
    <a href="view/modules/servicios.php" style="padding: 8px 12px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px;">Ver Interfaz de Servicios</a>
</div>';

try {
    $db = Conexion::conectar();
    
    if ($db === null) {
        echo "<p>Error de conexión a la base de datos</p>";
        exit;
    }
    
    echo "<p>Conexión a la base de datos exitosa</p>";
    
    // Consulta para obtener todas las reservas con información relacionada
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
    ORDER BY r.fecha_reserva DESC, r.hora_inicio ASC";
    
    $stmt = $db->query($sql);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($reservas)) {
        echo "<p>No se encontraron reservas en la base de datos</p>";
    } else {
        echo "<p>Se encontraron " . count($reservas) . " reservas</p>";
        
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr>
                <th>ID</th>
                <th>Doctor</th>
                <th>Paciente</th>
                <th>Servicio</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estado</th>
              </tr>";
        
        foreach ($reservas as $r) {
            echo "<tr>";
            echo "<td>" . ($r['reserva_id'] ?? 'N/A') . "</td>";
            echo "<td>" . ($r['doctor_nombre'] ?? 'Doctor ' . ($r['doctor_id'] ?? '?')) . "</td>";
            echo "<td>" . ($r['paciente_nombre'] ?? 'Paciente ' . ($r['paciente_id'] ?? '?')) . "</td>";
            echo "<td>" . ($r['servicio_nombre'] ?? 'Servicio ' . ($r['servicio_id'] ?? '?')) . "</td>";
            echo "<td>" . ($r['fecha_reserva'] ?? 'N/A') . "</td>";
            echo "<td>" . ($r['hora_inicio'] ?? 'N/A') . "</td>";
            echo "<td>" . ($r['estado_reserva'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // Verificar también la estructura de la tabla
    echo "<h2>Estructura de la tabla servicios_reservas</h2>";
    
    $sql = "SELECT column_name, data_type, is_nullable 
            FROM information_schema.columns 
            WHERE table_name = 'servicios_reservas' 
            ORDER BY ordinal_position";
    
    $stmt = $db->query($sql);
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr>
            <th>Columna</th>
            <th>Tipo de Dato</th>
            <th>Puede ser Nulo</th>
          </tr>";
    
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td>" . $col['column_name'] . "</td>";
        echo "<td>" . $col['data_type'] . "</td>";
        echo "<td>" . $col['is_nullable'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Contar registros para la fecha actual
    $hoy = date('Y-m-d');
    
    echo "<h2>Reservas para hoy ({$hoy})</h2>";
    
    $sql = "SELECT COUNT(*) as total FROM servicios_reservas WHERE fecha_reserva = :fecha";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":fecha", $hoy, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Número de reservas para hoy: " . $count['total'] . "</p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>En el archivo: " . $e->getFile() . " línea " . $e->getLine() . "</p>";
}
?>
