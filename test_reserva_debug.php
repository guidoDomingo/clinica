<?php
/**
 * Herramienta de diagnóstico para probar reservas de servicios
 */
require_once "model/conexion.php";
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";

// Utilizar el mismo modelo y controlador que usa la aplicación real

// Función para limpiar salida de errores
function limpiarBuffer() {
    ob_end_clean();
    ob_start();
}

// Iniciar buffer de salida para capturar errores
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Reservas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .response-box {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
        }
        pre {
            margin: 0;
            white-space: pre-wrap;
        }
        .logs-container {
            height: 300px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 14px;
        }
        .log-entry {
            margin: 0;
            padding: 3px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .form-section {
            background-color: #e9ecef;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        h2 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="container py-4">
    <h1 class="mb-4">Test de Reservas de Servicios</h1>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-section">
                <h2>Prueba de creación de reserva</h2>
                <form id="testForm" method="post" action="">
                    <div class="form-group">
                        <label for="doctor_id">Doctor ID:</label>
                        <input type="number" class="form-control" id="doctor_id" name="doctor_id" required value="<?= $_POST['doctor_id'] ?? 14 ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="servicio_id">Servicio ID:</label>
                        <input type="number" class="form-control" id="servicio_id" name="servicio_id" required value="<?= $_POST['servicio_id'] ?? 1 ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="paciente_id">Paciente ID:</label>
                        <input type="number" class="form-control" id="paciente_id" name="paciente_id" required value="<?= $_POST['paciente_id'] ?? 11 ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_reserva">Fecha de Reserva:</label>
                        <input type="date" class="form-control" id="fecha_reserva" name="fecha_reserva" required value="<?= $_POST['fecha_reserva'] ?? date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="hora_inicio">Hora Inicio:</label>
                        <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required value="<?= $_POST['hora_inicio'] ?? '10:00' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="hora_fin">Hora Fin:</label>
                        <input type="time" class="form-control" id="hora_fin" name="hora_fin" required value="<?= $_POST['hora_fin'] ?? '10:30' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones">Observaciones:</label>
                        <textarea class="form-control" id="observaciones" name="observaciones"><?= $_POST['observaciones'] ?? 'Test de reserva' ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="agenda_id">Agenda ID (opcional):</label>
                        <input type="number" class="form-control" id="agenda_id" name="agenda_id" value="<?= $_POST['agenda_id'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tarifa_id">Tarifa ID (opcional):</label>
                        <input type="number" class="form-control" id="tarifa_id" name="tarifa_id" value="<?= $_POST['tarifa_id'] ?? '' ?>">
                    </div>
                    
                    <button type="submit" name="test_reserva" class="btn btn-primary">Probar Reserva</button>
                </form>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">Resultado de la prueba</h2>
                </div>
                <div class="card-body">
                    <?php
                    if (isset($_POST['test_reserva'])) {
                        // Recolectar datos del formulario
                        $datos = array(
                            'doctor_id' => intval($_POST['doctor_id']),
                            'servicio_id' => intval($_POST['servicio_id']),
                            'paciente_id' => intval($_POST['paciente_id']),
                            'fecha_reserva' => $_POST['fecha_reserva'],
                            'hora_inicio' => $_POST['hora_inicio'] . ':00',
                            'hora_fin' => $_POST['hora_fin'] . ':00',
                            'observaciones' => $_POST['observaciones'] ?? ''
                        );
                        
                        // Agregar campos opcionales si tienen valor
                        if (!empty($_POST['agenda_id'])) {
                            $datos['agenda_id'] = intval($_POST['agenda_id']);
                        }
                        
                        if (!empty($_POST['tarifa_id'])) {
                            $datos['tarifa_id'] = intval($_POST['tarifa_id']);
                        }
                        
                        echo "<h3 class='mb-3'>Datos enviados:</h3>";
                        echo "<div class='response-box mb-4'><pre>" . json_encode($datos, JSON_PRETTY_PRINT) . "</pre></div>";
                        
                        // Intentar guardar la reserva
                        try {
                            $resultado = ControladorServicios::ctrGuardarReserva($datos);
                            
                            echo "<h3 class='mb-3'>Respuesta:</h3>";
                            echo "<div class='response-box'>";
                            if ($resultado !== false && is_numeric($resultado)) {
                                echo "<div class='alert alert-success'>
                                    <strong>¡Éxito!</strong> Reserva creada con ID: {$resultado}
                                </div>";
                            } else {
                                echo "<div class='alert alert-danger'>
                                    <strong>Error:</strong> No se pudo crear la reserva.
                                </div>";
                            }
                            echo "</div>";
                        } catch (Exception $e) {
                            echo "<div class='alert alert-danger'>
                                <strong>Excepción:</strong> " . $e->getMessage() . "
                            </div>";
                        }
                    } else {
                        echo "<div class='alert alert-info'>
                            Complete el formulario y haga clic en \"Probar Reserva\" para ver los resultados.
                        </div>";
                    }
                    ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Logs recientes</h2>
                </div>
                <div class="card-body p-0">
                    <div class="logs-container">
                        <?php
                        $logFile = 'c:/laragon/www/clinica/logs/reservas.log';
                        if (file_exists($logFile)) {
                            $logs = file($logFile);
                            $logs = array_reverse($logs); // Mostrar los más recientes primero
                            $count = 0;
                            foreach ($logs as $log) {
                                if ($count++ < 100) { // Limitar a 100 entradas
                                    echo "<p class='log-entry'>" . htmlspecialchars($log) . "</p>";
                                }
                            }
                        } else {
                            echo "<p>No se encontró el archivo de logs.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h2 class="h5 mb-0">Verificación de estructura de base de datos</h2>
        </div>
        <div class="card-body">
            <?php
            try {
                $db = Conexion::conectar();
                
                // Verificar estructura de la tabla servicios_reservas
                $stmt = $db->prepare("
                    SELECT column_name, data_type, is_nullable
                    FROM information_schema.columns
                    WHERE table_name = 'servicios_reservas'
                    ORDER BY ordinal_position
                ");
                $stmt->execute();
                $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($columnas) > 0) {
                    echo "<h3>Estructura de la tabla servicios_reservas:</h3>";
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-sm table-bordered table-striped'>";
                    echo "<thead class='thead-light'><tr><th>Columna</th><th>Tipo</th><th>Nullable</th></tr></thead><tbody>";
                    
                    foreach ($columnas as $columna) {
                        echo "<tr>";
                        echo "<td>{$columna['column_name']}</td>";
                        echo "<td>{$columna['data_type']}</td>";
                        echo "<td>" . ($columna['is_nullable'] == 'YES' ? 'Sí' : 'No') . "</td>";
                        echo "</tr>";
                    }
                    
                    echo "</tbody></table>";
                    echo "</div>";
                } else {
                    echo "<div class='alert alert-warning'>
                        No se encontraron columnas para la tabla servicios_reservas.
                    </div>";
                }
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>
                    Error al verificar la estructura de la base de datos: " . $e->getMessage() . "
                </div>";
            }
            ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Limpiar cualquier error o advertencia capturada
$output = ob_get_clean();
echo $output;
?>
