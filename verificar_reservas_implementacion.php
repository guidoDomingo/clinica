<?php
/**
 * Script para comprobar la implementación de la función mdlObtenerReservasPorFecha
 * Este script implementa la consulta SQL corregida en la función del modelo
 */

// Inicializar parámetros de fecha
$fecha = '2025-05-28';
$fechaInicio = $fecha . ' 00:00:00';
$fechaFin = $fecha . ' 23:59:59';

// HTML Header
echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Verificar implementación de consulta de reservas</title>
    <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { padding: 20px; }
        .card { margin-bottom: 20px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Verificación de implementación de consulta de reservas</h1>
        <p>Este script verifica si la implementación de la función para obtener reservas funciona correctamente.</p>";

// Verificar si existe el archivo del modelo
$modeloFile = 'model/servicios.model.php';
if (!file_exists($modeloFile)) {
    echo "<div class='alert alert-danger'>El archivo del modelo no existe en la ruta esperada.</div>";
    exit;
}

// Cargar el modelo
require_once $modeloFile;
require_once 'model/conexion.php';
require_once 'controller/servicios.controller.php';

echo "<div class='card'>
    <div class='card-header bg-primary text-white'>
        <h3>1. Verificación de la consulta SQL directa</h3>
    </div>
    <div class='card-body'>";

// Consulta SQL a ejecutar directamente
$sql = "SELECT 
    sr.hora_inicio,
    sr.hora_fin,
    rp.first_name ||' - ' || rp.last_name as doctor,
    rp2.first_name ||' - ' || rp2.last_name as paciente,
    rs.serv_descripcion,
    sr.reserva_estado 
FROM servicios_reservas sr 
INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
INNER JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id 
INNER JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id 
WHERE sr.fecha_reserva BETWEEN :fecha_inicio AND :fecha_fin
ORDER BY sr.hora_inicio ASC";

echo "<h4>Consulta SQL a ejecutar:</h4>
<pre>$sql</pre>
<p>Parámetros: fecha_inicio = '$fechaInicio', fecha_fin = '$fechaFin'</p>";

try {
    // Ejecutar consulta SQL directamente
    $stmt = Conexion::conectar()->prepare($sql);
    $stmt->bindParam(":fecha_inicio", $fechaInicio, PDO::PARAM_STR);
    $stmt->bindParam(":fecha_fin", $fechaFin, PDO::PARAM_STR);
    $stmt->execute();
    $reservasDirectas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Resultados de consulta directa:</h4>";
    if (count($reservasDirectas) > 0) {
        echo "<p class='success'>Se encontraron " . count($reservasDirectas) . " reservas directamente.</p>";
        // Mostrar primeros resultados
        echo "<pre>" . json_encode(array_slice($reservasDirectas, 0, 3), JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p class='error'>No se encontraron reservas con la consulta directa.</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>Error en consulta directa: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Verificación del modelo
echo "<div class='card'>
    <div class='card-header bg-success text-white'>
        <h3>2. Verificación del método del modelo</h3>
    </div>
    <div class='card-body'>";

try {
    // Obtener reservas a través del modelo
    $reservasModelo = ModelServicios::mdlObtenerReservasPorFecha($fecha);
    
    echo "<h4>Resultados del método del modelo:</h4>";
    if (count($reservasModelo) > 0) {
        echo "<p class='success'>Se encontraron " . count($reservasModelo) . " reservas a través del modelo.</p>";
        
        // Mostrar primeros resultados
        echo "<pre>" . json_encode(array_slice($reservasModelo, 0, 3), JSON_PRETTY_PRINT) . "</pre>";
        
        // Verificar campos clave
        $camposEsperados = ['hora_inicio', 'hora_fin', 'doctor', 'paciente', 'serv_descripcion', 'reserva_estado'];
        $todosCamposPresentes = true;
        $camposFaltantes = [];
        
        foreach ($camposEsperados as $campo) {
            if (!isset($reservasModelo[0][$campo])) {
                $todosCamposPresentes = false;
                $camposFaltantes[] = $campo;
            }
        }
        
        if ($todosCamposPresentes) {
            echo "<p class='success'>Todos los campos esperados están presentes en los resultados del modelo.</p>";
        } else {
            echo "<p class='error'>Faltan campos esperados: " . implode(", ", $camposFaltantes) . "</p>";
        }
    } else {
        echo "<p class='error'>No se encontraron reservas a través del modelo.</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error en método del modelo: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Verificación del controlador
echo "<div class='card'>
    <div class='card-header bg-warning text-dark'>
        <h3>3. Verificación del método del controlador</h3>
    </div>
    <div class='card-body'>";

try {
    // Obtener reservas a través del controlador
    $reservasControlador = ControladorServicios::ctrObtenerReservasPorFecha($fecha);
    
    echo "<h4>Resultados del método del controlador:</h4>";
    if (count($reservasControlador) > 0) {
        echo "<p class='success'>Se encontraron " . count($reservasControlador) . " reservas a través del controlador.</p>";
        
        // Mostrar primeros resultados
        echo "<pre>" . json_encode(array_slice($reservasControlador, 0, 3), JSON_PRETTY_PRINT) . "</pre>";
        
        // Verificar estructura con consulta directa
        if (count($reservasDirectas) == count($reservasControlador)) {
            echo "<p class='success'>El número de reservas coincide con la consulta directa.</p>";
        } else {
            echo "<p class='error'>El número de reservas no coincide con la consulta directa.</p>";
            echo "<p>Consulta directa: " . count($reservasDirectas) . " reservas</p>";
            echo "<p>Controlador: " . count($reservasControlador) . " reservas</p>";
        }
    } else {
        echo "<p class='error'>No se encontraron reservas a través del controlador.</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error en método del controlador: " . $e->getMessage() . "</p>";
}

echo "</div></div>";

// Verificación completa
echo "<div class='card'>
    <div class='card-header bg-info text-white'>
        <h3>4. Resumen de la verificación</h3>
    </div>
    <div class='card-body'>";

$directa = isset($reservasDirectas) && count($reservasDirectas) > 0;
$modelo = isset($reservasModelo) && count($reservasModelo) > 0;
$controlador = isset($reservasControlador) && count($reservasControlador) > 0;

if ($directa && $modelo && $controlador) {
    echo "<div class='alert alert-success'>
        <h4>¡Implementación exitosa!</h4>
        <p>La consulta funciona correctamente en todos los niveles (SQL directo, modelo y controlador).</p>
    </div>";
} else {
    echo "<div class='alert alert-danger'>
        <h4>Implementación incompleta</h4>
        <ul>";
    if (!$directa) echo "<li>La consulta SQL directa no devuelve resultados.</li>";
    if (!$modelo) echo "<li>El método del modelo no devuelve resultados.</li>";
    if (!$controlador) echo "<li>El método del controlador no devuelve resultados.</li>";
    echo "</ul>
    </div>";
}

echo "</div></div>";

// Enlace para probar con otras fechas
echo "<div class='card'>
    <div class='card-header bg-secondary text-white'>
        <h3>5. Probar con otra fecha</h3>
    </div>
    <div class='card-body'>
        <form action='' method='GET' class='form-inline'>
            <div class='form-group mr-2'>
                <label for='fecha' class='mr-2'>Fecha:</label>
                <input type='date' id='fecha' name='fecha' class='form-control' value='$fecha'>
            </div>
            <button type='submit' class='btn btn-primary'>Verificar</button>
        </form>
    </div>
</div>";

echo "</div>
</body>
</html>";
