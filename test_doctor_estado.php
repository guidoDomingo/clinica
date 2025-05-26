<?php
/**
 * Test script to verify changes to doctor_estado handling
 */

// Configurar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir los archivos necesarios
require_once "model/conexion.php";
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";

// Crear un título para la página
echo '<html><head><title>Test de doctor_estado</title>';
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .container { max-width: 800px; margin: 0 auto; }
    h1 { color: #333; }
    pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; }
    .success { color: green; }
    .error { color: red; }
</style>';
echo '</head><body><div class="container">';
echo '<h1>Test de corrección para doctor_estado</h1>';

// Definir una fecha para la prueba (usar fecha actual por defecto)
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '2025-05-20';
echo "<p>Fecha de prueba: <strong>$fecha</strong></p>";

try {
    $conn = Conexion::conectar();
    if (!$conn) {
        throw new Exception("No se pudo establecer la conexión a la base de datos.");
    }
    
    echo '<div class="success">Conexión a la base de datos establecida correctamente.</div>';
    
    // Determinar el día de la semana
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj) {
        throw new Exception("Formato de fecha incorrecto: $fecha");
    }
    
    $diaSemanaNum = (int)$fechaObj->format('N'); // 1-7 (ISO format: 1=lunes, 7=domingo)
    $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
    $diaSemana = $diasSemanaTexto[$diaSemanaNum];
    
    echo "<p>Día de la semana: $diaSemana</p>";
    
    // Ejecutar la consulta corregida
    $stmt = $conn->prepare("
        SELECT DISTINCT 
            d.doctor_id,
            p.person_id,
            p.first_name || ' ' || p.last_name AS nombre_doctor,
            d.doctor_estado
        FROM 
            agendas_detalle ad
        LEFT JOIN
            agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
        LEFT JOIN
            rh_doctors d ON ac.medico_id = d.doctor_id
        LEFT JOIN
            rh_person p ON d.person_id = p.person_id
        WHERE 
            ad.dia_semana = :dia_semana
            AND ad.detalle_estado = true
            AND (ac.agenda_estado IS NULL OR ac.agenda_estado = true)
            AND (d.doctor_estado IS NULL OR d.doctor_estado = 'ACTIVO')
        ORDER BY
            p.first_name, p.last_name
    ");
    
    $stmt->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<h2>Resultados de la consulta corregida:</h2>';
    if (count($resultados) > 0) {
        echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">';
        echo '<tr><th>Doctor ID</th><th>Person ID</th><th>Nombre</th><th>Estado</th></tr>';
        
        foreach ($resultados as $resultado) {
            echo '<tr>';
            echo '<td>' . $resultado['doctor_id'] . '</td>';
            echo '<td>' . $resultado['person_id'] . '</td>';
            echo '<td>' . $resultado['nombre_doctor'] . '</td>';
            echo '<td>' . $resultado['doctor_estado'] . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        echo '<p class="success">Se encontraron ' . count($resultados) . ' médicos disponibles.</p>';
    } else {
        echo '<p class="error">No se encontraron médicos disponibles para esta fecha.</p>';
    }
    
    // Probar el método del modelo
    echo '<h2>Resultados del método mdlObtenerMedicosDisponiblesPorFecha:</h2>';
    $medicos = ModelServicios::mdlObtenerMedicosDisponiblesPorFecha($fecha);
    
    echo '<pre>';
    print_r($medicos);
    echo '</pre>';
    
} catch (Exception $e) {
    echo '<p class="error">Error: ' . $e->getMessage() . '</p>';
}

echo '</div></body></html>';
?>
