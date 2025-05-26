<?php
/**
 * Script para probar la respuesta AJAX de generación de slots
 * Este archivo simula una llamada AJAX y muestra la respuesta para diagnóstico
 */

// Configurar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir los archivos necesarios
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";
require_once "model/conexion.php";

// Parámetros de prueba
$servicioId = isset($_GET['servicio_id']) ? intval($_GET['servicio_id']) : 2;
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 14;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d', strtotime('+1 day'));

// Obtener los slots directamente del controlador
$tiempoInicio = microtime(true);
$slots = ControladorServicios::ctrGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
$tiempoFin = microtime(true);
$tiempoEjecucion = round(($tiempoFin - $tiempoInicio) * 1000, 2); // en milisegundos

// Crear la respuesta como se haría en el AJAX handler
$respuesta = [
    "status" => "success",
    "data" => $slots
];

// Mostrar información sobre la solicitud y la respuesta
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test AJAX Response</title>
    <link rel="stylesheet" href="view/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/dist/css/adminlte.min.css">
    <style>
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow: auto;
            max-height: 400px;
        }
        .json-key {
            color: #0000dd;
        }
        .json-string {
            color: #dd0000;
        }
        .json-number {
            color: #008800;
        }
    </style>
</head>
<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
            <div class="container">
                <a href="index.php" class="navbar-brand">
                    <span class="brand-text font-weight-light">Test AJAX Response</span>
                </a>
            </div>
        </nav>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Test de Respuesta AJAX para Slots</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Parámetros de prueba</h3>
                                </div>
                                <div class="card-body">
                                    <form method="GET" class="form-inline">
                                        <div class="form-group mb-2 mr-2">
                                            <label for="servicio_id" class="mr-2">Servicio ID:</label>
                                            <input type="number" class="form-control" id="servicio_id" name="servicio_id" value="<?php echo $servicioId; ?>">
                                        </div>
                                        <div class="form-group mb-2 mr-2">
                                            <label for="doctor_id" class="mr-2">Doctor ID:</label>
                                            <input type="number" class="form-control" id="doctor_id" name="doctor_id" value="<?php echo $doctorId; ?>">
                                        </div>
                                        <div class="form-group mb-2 mr-2">
                                            <label for="fecha" class="mr-2">Fecha:</label>
                                            <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>">
                                        </div>
                                        <button type="submit" class="btn btn-primary mb-2">Probar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Información de solicitud</h3>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-3">Servicio ID:</dt>
                                        <dd class="col-sm-9"><?php echo $servicioId; ?></dd>
                                        <dt class="col-sm-3">Doctor ID:</dt>
                                        <dd class="col-sm-9"><?php echo $doctorId; ?></dd>
                                        <dt class="col-sm-3">Fecha:</dt>
                                        <dd class="col-sm-9"><?php echo $fecha; ?></dd>
                                        <dt class="col-sm-3">Tiempo de ejecución:</dt>
                                        <dd class="col-sm-9"><?php echo $tiempoEjecucion; ?> ms</dd>
                                        <dt class="col-sm-3">Slots generados:</dt>
                                        <dd class="col-sm-9"><?php echo count($slots); ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Respuesta AJAX</h3>
                                </div>
                                <div class="card-body">
                                    <h4>Estructura de la respuesta:</h4>
                                    <pre class="language-json" id="jsonResponse"><?php echo htmlentities(json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">JavaScript de Diagnóstico</h3>
                                </div>
                                <div class="card-body">
                                    <p>Para depurar el problema en la interfaz principal, comprueba la siguiente información:</p>
                                    <div id="diagnosticoResultado"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                v1.0
            </div>
            <strong>Test de respuesta AJAX</strong>
        </footer>
    </div>

    <script src="view/plugins/jquery/jquery.min.js"></script>
    <script src="view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="view/dist/js/adminlte.min.js"></script>
    <script>
        // Código de JavaScript para simular la verificación
        $(document).ready(function() {
            var respuesta = <?php echo json_encode($respuesta); ?>;
            var diagnostico = '';
            
            // Simular la verificación del código en servicios.js
            diagnostico += '<h5>Análisis de la respuesta:</h5>';
            
            if (respuesta.hasOwnProperty('status')) {
                diagnostico += '<p class="text-success">✓ La respuesta tiene la propiedad "status": ' + respuesta.status + '</p>';
            } else {
                diagnostico += '<p class="text-danger">✗ La respuesta NO tiene la propiedad "status"</p>';
            }
            
            if (respuesta.hasOwnProperty('data')) {
                diagnostico += '<p class="text-success">✓ La respuesta tiene la propiedad "data" con ' + respuesta.data.length + ' elementos</p>';
            } else {
                diagnostico += '<p class="text-danger">✗ La respuesta NO tiene la propiedad "data"</p>';
            }
            
            // Verificar cómo funcionaría con el código original
            diagnostico += '<h5>Simulación de procesamiento:</h5>';
            
            if (respuesta.status === "success") {
                diagnostico += '<p class="text-success">✓ La condición "respuesta.status === success" es verdadera</p>';
                
                if (respuesta.data && respuesta.data.length > 0) {
                    diagnostico += '<p class="text-success">✓ La condición "respuesta.data && respuesta.data.length > 0" es verdadera</p>';
                } else {
                    diagnostico += '<p class="text-danger">✗ La condición "respuesta.data && respuesta.data.length > 0" es falsa</p>';
                }
            } else {
                diagnostico += '<p class="text-danger">✗ La condición "respuesta.status === success" es falsa</p>';
            }
            
            // Verificar cómo funcionaría con el código modificado
            diagnostico += '<h5>Simulación con código corregido:</h5>';
            
            if ((respuesta.status === "success" && respuesta.data && respuesta.data.length > 0) || 
                (respuesta.data && respuesta.data.length > 0)) {
                diagnostico += '<p class="text-success">✓ La condición modificada es verdadera, se mostrarían los horarios</p>';
            } else {
                diagnostico += '<p class="text-danger">✗ La condición modificada es falsa, no se mostrarían horarios</p>';
            }
            
            // Mostrar el diagnóstico
            $('#diagnosticoResultado').html(diagnostico);
            
            // Colorear el JSON
            prettyPrintJson();
        });
        
        function prettyPrintJson() {
            // Una función simple para colorear el JSON
            var jsonStr = $('#jsonResponse').text();
            jsonStr = jsonStr.replace(/"(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, function (match) {
                var cls = 'json-number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'json-key';
                    } else {
                        cls = 'json-string';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'json-boolean';
                } else if (/null/.test(match)) {
                    cls = 'json-null';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            });
            $('#jsonResponse').html(jsonStr);
        }
    </script>
</body>
</html>
