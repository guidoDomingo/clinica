<?php
/**
 * Script para probar la llamada AJAX de obtenerReservas 
 * simulando una petición AJAX POST y mostrando la respuesta
 */

// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Prueba directa de la llamada AJAX obtenerReservas</h1>";

// Obtener la fecha para probar
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;

echo "<p>Fecha a probar: <strong>$fecha</strong></p>";
echo "<p>Doctor ID: <strong>" . ($doctorId ?? "null") . "</strong></p>";

// Función para realizar POST con curl
function post_curl($url, $post_data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'info' => $info,
        'error' => $error
    ];
}

// URL de la petición AJAX
$url = 'http://localhost/clinica/ajax/servicios.ajax.php';

// Datos a enviar
$postData = [
    'action' => 'obtenerReservas',
    'fecha' => $fecha
];

if ($doctorId !== null) {
    $postData['doctor_id'] = $doctorId;
}

echo "<h2>Petición POST</h2>";
echo "<pre>" . print_r($postData, true) . "</pre>";

echo "<h2>Respuesta</h2>";

// Realizar la petición
$result = post_curl($url, $postData);

if ($result['error']) {
    echo "<div style='color: red; margin-bottom: 10px;'>";
    echo "<strong>Error CURL:</strong> " . htmlspecialchars($result['error']);
    echo "</div>";
}

echo "<p><strong>Código de estado HTTP:</strong> " . $result['info']['http_code'] . "</p>";
echo "<p><strong>Tiempo de respuesta:</strong> " . $result['info']['total_time'] . " segundos</p>";

// Mostrar respuesta cruda
echo "<h3>Respuesta JSON cruda</h3>";
echo "<pre>" . htmlspecialchars($result['response']) . "</pre>";

// Intentar decodificar JSON
$decoded = json_decode($result['response'], true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "<h3>Respuesta JSON decodificada</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    
    // Si hay datos, mostrarlos en tabla
    if (isset($decoded['data']) && is_array($decoded['data']) && count($decoded['data']) > 0) {
        echo "<h3>Reservas en formato tabla</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        
        // Cabeceras
        echo "<tr>";
        foreach (array_keys($decoded['data'][0]) as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr>";
        
        // Filas
        foreach ($decoded['data'] as $reserva) {
            echo "<tr>";
            foreach ($reserva as $key => $value) {
                echo "<td>" . htmlspecialchars(is_null($value) ? 'NULL' : $value) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>No hay reservas en la respuesta o el formato es diferente al esperado.</p>";
    }
} else {
    echo "<p style='color: red;'>Error al decodificar JSON: " . json_last_error_msg() . "</p>";
}

// Formulario para probar con otra fecha
echo "<h2>Probar con otra fecha</h2>";
echo "<form method='get'>";
echo "  <input type='date' name='fecha' value='$fecha'>";
echo "  <input type='number' name='doctor_id' placeholder='Doctor ID (opcional)' value='" . ($doctorId ?? "") . "'>";
echo "  <button type='submit'>Probar</button>";
echo "</form>";

// Botones para otras acciones
echo "<h2>Otras acciones</h2>";
echo "<div style='margin-top: 20px;'>";
echo "<a href='crear_reserva_hoy.php' style='margin-right: 10px; padding: 10px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Crear Reserva de Prueba</a>";
echo "<a href='listar_todas_reservas.php' style='padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Ver Listado de Reservas</a>";
echo "</div>";
?>
