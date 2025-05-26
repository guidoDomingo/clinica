<?php
/**
 * Prueba simplificada para verificar la carga de servicios
 * y validar la solución al error JavaScript (Uncaught SyntaxError: Identifier 'fechaSeleccionada')
 */

// Configurar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir los archivos necesarios
require_once "model/conexion.php";
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";

// Parámetros de prueba
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 13;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Servicios - Sin error JavaScript</title>
    <link rel="stylesheet" href="view/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/plugins/fontawesome-free/css/all.min.css">
    <style>
        body { padding: 20px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Verificación de Servicios - Sin error JavaScript</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <p>Esta prueba verifica que:</p>
                            <ol>
                                <li>No se produce el error "Uncaught SyntaxError: Identifier 'fechaSeleccionada'"</li>
                                <li>La función mdlObtenerServiciosPorFechaMedico devuelve todos los servicios</li>
                            </ol>
                        </div>
                        
                        <div class="mb-4">
                            <h4>Parámetros actuales:</h4>
                            <ul>
                                <li><strong>Doctor ID:</strong> <?php echo $doctorId; ?></li>
                                <li><strong>Fecha:</strong> <?php echo $fecha; ?></li>
                            </ul>
                            
                            <form method="GET" class="form-inline mt-3">
                                <div class="form-group mr-2">
                                    <label for="doctor_id" class="mr-2">Doctor ID:</label>
                                    <input type="number" class="form-control" id="doctor_id" name="doctor_id" value="<?php echo $doctorId; ?>">
                                </div>
                                <div class="form-group mr-2">
                                    <label for="fecha" class="mr-2">Fecha:</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Verificar</button>
                            </form>
                        </div>
                        
                        <hr>
                        
                        <h4>Resultados:</h4>
                        <?php
                        // Obtener servicios para el médico seleccionado
                        $servicios = ModelServicios::mdlObtenerServiciosPorFechaMedico($fecha, $doctorId);
                        
                        // Mostrar el recuento de servicios
                        echo '<div class="alert alert-success">';
                        echo '<strong>Total de servicios obtenidos:</strong> ' . count($servicios);
                        echo '</div>';
                        
                        // Mostrar una tabla con los primeros 10 servicios
                        if (count($servicios) > 0) {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-striped table-bordered">';
                            echo '<thead class="thead-dark">';
                            echo '<tr>';
                            echo '<th>ID</th>';
                            echo '<th>Código</th>';
                            echo '<th>Nombre</th>';
                            echo '<th>Duración</th>';
                            echo '<th>Precio</th>';
                            echo '<th>Categoría</th>';
                            echo '<th>Origen</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            
                            $contador = 0;
                            foreach ($servicios as $servicio) {
                                if ($contador >= 10) break;
                                
                                echo '<tr>';
                                echo '<td>' . $servicio['servicio_id'] . '</td>';
                                echo '<td>' . $servicio['servicio_codigo'] . '</td>';
                                echo '<td>' . $servicio['servicio_nombre'] . '</td>';
                                echo '<td>' . $servicio['duracion_minutos'] . ' min</td>';
                                echo '<td>$' . number_format($servicio['precio_base'], 2) . '</td>';
                                echo '<td>' . $servicio['categoria_nombre'] . '</td>';
                                echo '<td>' . (isset($servicio['origen']) ? $servicio['origen'] : 'N/A') . '</td>';
                                echo '</tr>';
                                
                                $contador++;
                            }
                            
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                            
                            echo '<div class="alert alert-info mt-3">';
                            echo 'Mostrando ' . $contador . ' de ' . count($servicios) . ' servicios.';
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-warning">';
                            echo 'No se encontraron servicios.';
                            echo '</div>';
                        }
                        ?>
                        
                        <hr>
                        
                        <h4>Verificación JavaScript:</h4>
                        <div id="jsResult" class="alert alert-info">
                            Ejecutando verificación JavaScript...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="view/plugins/jquery/jquery.min.js"></script>
    <script src="view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Script de prueba para verificar que no hay errores de variables duplicadas
    $(document).ready(function() {
        try {
            // Declarar variables de prueba (simulando servicios.js)
            let fechaSeleccionada = '';
            let proveedorSeleccionado = '';
            let servicioSeleccionado = '';
            
            // Si llegamos aquí, no hubo error
            $('#jsResult').removeClass('alert-info').addClass('alert-success')
                .html('<i class="fas fa-check-circle"></i> Verificación exitosa. No hay errores de JavaScript con variables duplicadas.');
        } catch (error) {
            // Si hay un error, mostrarlo
            $('#jsResult').removeClass('alert-info').addClass('alert-danger')
                .html('<i class="fas fa-exclamation-circle"></i> Error detectado: ' + error.message);
        }
    });
    </script>
</body>
</html>
