<?php
/**
 * Script para probar directamente la consulta de servicios por doctor
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

// Función para imprimir resultados en formato legible
function printResult($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Directo - Servicios por Doctor</title>
    <link rel="stylesheet" href="view/plugins/bootstrap/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .card { margin-bottom: 20px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test Directo - Servicios por Doctor</h1>
        
        <div class="card">
            <div class="card-header">
                <h5>Parámetros</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="doctorId" class="mr-2">ID Doctor:</label>
                        <input type="number" class="form-control" id="doctorId" name="doctor_id" value="<?php echo $doctorId; ?>">
                    </div>
                    <div class="form-group mr-3">
                        <label for="fecha" class="mr-2">Fecha:</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Ejecutar</button>
                </form>
            </div>
        </div>

        <?php
        try {
            // Verificar existencia de tablas
            $conn = Conexion::conectar();
            
            echo '<div class="card">';
            echo '<div class="card-header"><h5>1. Verificación de tablas</h5></div>';
            echo '<div class="card-body">';
            
            // Revisar tabla servicios_medicos
            $stmtCheck = $conn->prepare("SELECT to_regclass('public.servicios_medicos')");
            $stmtCheck->execute();
            $tablaServiciosMedicosExiste = $stmtCheck->fetchColumn();
            
            echo '<p><strong>Tabla servicios_medicos:</strong> ' . ($tablaServiciosMedicosExiste ? 'Existe' : 'No existe') . '</p>';
            
            // Revisar tabla rs_servicios
            $stmtCheck = $conn->prepare("SELECT to_regclass('public.rs_servicios')");
            $stmtCheck->execute();
            $tablaRsServiciosExiste = $stmtCheck->fetchColumn();
            
            echo '<p><strong>Tabla rs_servicios:</strong> ' . ($tablaRsServiciosExiste ? 'Existe' : 'No existe') . '</p>';
            
            // Revisar tabla relación
            $stmtCheck = $conn->prepare("SELECT to_regclass('public.rs_medico_servicio')");
            $stmtCheck->execute();
            $tablaMedicoServicioExiste = $stmtCheck->fetchColumn();
            
            echo '<p><strong>Tabla rs_medico_servicio:</strong> ' . ($tablaMedicoServicioExiste ? 'Existe' : 'No existe') . '</p>';
            
            echo '</div>';
            echo '</div>';
            
            // Ejecutar la consulta directamente a la tabla rs_servicios
            if ($tablaRsServiciosExiste) {
                echo '<div class="card">';
                echo '<div class="card-header"><h5>2. Servicios en rs_servicios</h5></div>';
                echo '<div class="card-body">';
                
                $stmtRs = $conn->prepare("
                    SELECT 
                        serv_id, 
                        serv_codigo, 
                        serv_descripcion, 
                        serv_monto, 
                        is_active 
                    FROM rs_servicios 
                    WHERE is_active = true
                ");
                $stmtRs->execute();
                $serviciosRs = $stmtRs->fetchAll(PDO::FETCH_ASSOC);
                
                echo '<p>Encontrados: ' . count($serviciosRs) . ' servicios</p>';
                
                if (count($serviciosRs) > 0) {
                    echo '<table class="table table-bordered">';
                    echo '<thead class="thead-light">';
                    echo '<tr><th>ID</th><th>Código</th><th>Descripción</th><th>Precio</th></tr>';
                    echo '</thead><tbody>';
                    
                    foreach ($serviciosRs as $s) {
                        echo '<tr>';
                        echo '<td>' . $s['serv_id'] . '</td>';
                        echo '<td>' . $s['serv_codigo'] . '</td>';
                        echo '<td>' . $s['serv_descripcion'] . '</td>';
                        echo '<td>' . $s['serv_monto'] . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                }
                
                echo '</div>';
                echo '</div>';
            }
            
            // Llamar al método del modelo
            echo '<div class="card">';
            echo '<div class="card-header"><h5>3. Resultado del método mdlObtenerServiciosPorFechaMedico</h5></div>';
            echo '<div class="card-body">';
            
            $servicios = ModelServicios::mdlObtenerServiciosPorFechaMedico($fecha, $doctorId);
            
            echo '<p>Servicios encontrados: ' . count($servicios) . '</p>';
            
            if (count($servicios) > 0) {
                echo '<table class="table table-bordered">';
                echo '<thead class="thead-light">';
                echo '<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Duración</th><th>Precio</th><th>Categoría</th></tr>';
                echo '</thead><tbody>';
                
                foreach ($servicios as $s) {
                    echo '<tr>';
                    echo '<td>' . $s['servicio_id'] . '</td>';
                    echo '<td>' . $s['servicio_codigo'] . '</td>';
                    echo '<td>' . $s['servicio_nombre'] . '</td>';
                    echo '<td>' . $s['duracion_minutos'] . ' min</td>';
                    echo '<td>$' . number_format($s['precio_base'], 2) . '</td>';
                    echo '<td>' . $s['categoria_nombre'] . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<div class="alert alert-warning">No se encontraron servicios.</div>';
            }
            
            echo '</div>';
            echo '</div>';
            
            // Mostrar formato JSON para debug
            echo '<div class="card">';
            echo '<div class="card-header"><h5>4. Resultado en formato JSON</h5></div>';
            echo '<div class="card-body">';
            
            echo '<pre>' . json_encode($servicios, JSON_PRETTY_PRINT) . '</pre>';
            
            echo '</div>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">';
            echo 'Error: ' . $e->getMessage();
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
