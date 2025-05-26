<?php
/**
 * Script para verificar que se están mostrando TODOS los servicios
 * independientemente del médico seleccionado
 */

// Configurar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir los archivos necesarios
require_once "model/conexion.php";
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";

// Limpiar cualquier buffer de salida anterior
ob_clean();

// Cabecera HTML
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Servicios - Todos los servicios</title>
    <link rel="stylesheet" href="view/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="view/dist/css/adminlte.min.css">
    <style>
        pre {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="hold-transition">
    <div class="wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Test de Servicios - Todos los servicios</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="servicios">Volver a Servicios</a></li>
                            <li class="breadcrumb-item active">Test Todos los Servicios</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Test de la función mdlObtenerServiciosPorFechaMedico</h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <p>Esta prueba verifica que la función <code>mdlObtenerServiciosPorFechaMedico</code> ahora devuelve todos los servicios disponibles, independientemente del médico seleccionado.</p>
                                </div>
                                
                                <?php
                                // Obtener todos los médicos para la prueba
                                try {
                                    $conn = Conexion::conectar();
                                    $stmtDoctores = $conn->prepare("
                                        SELECT d.doctor_id, p.first_name || ' ' || p.last_name AS nombre_doctor
                                        FROM rh_doctors d
                                        LEFT JOIN rh_person p ON d.person_id = p.person_id
                                        WHERE d.doctor_estado = 'ACTIVO'
                                        ORDER BY p.first_name, p.last_name
                                        LIMIT 5
                                    ");
                                    $stmtDoctores->execute();
                                    $doctores = $stmtDoctores->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    // Verificar si no hay médicos
                                    if (empty($doctores)) {
                                        echo '<div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> No se encontraron médicos activos para la prueba
                                        </div>';
                                        
                                        // Usar un ID de doctor predeterminado
                                        $doctores = [['doctor_id' => 13, 'nombre_doctor' => 'Doctor Predeterminado']];
                                    }
                                    
                                    // Realizar la prueba con cada médico
                                    $fecha = date('Y-m-d');
                                    
                                    // Verificar primero sin filtro para obtener todos los servicios
                                    echo '<h4>Todos los servicios en rs_servicios:</h4>';
                                    $stmtAllServices = $conn->prepare("
                                        SELECT COUNT(*) as total FROM rs_servicios WHERE is_active = true
                                    ");
                                    $stmtAllServices->execute();
                                    $totalRsServicios = $stmtAllServices->fetch(PDO::FETCH_ASSOC)['total'];
                                    
                                    echo '<div class="alert alert-secondary">
                                        <strong>Total de servicios activos en rs_servicios:</strong> ' . $totalRsServicios . '
                                    </div>';
                                    
                                    // Verificar todos los servicios en servicios_medicos
                                    echo '<h4>Todos los servicios en servicios_medicos:</h4>';
                                    $stmtCheck = $conn->prepare("SELECT to_regclass('public.servicios_medicos')");
                                    $stmtCheck->execute();
                                    $tablaServiciosMedicosExiste = $stmtCheck->fetchColumn();
                                    
                                    $totalServiciosMedicos = 0;
                                    if ($tablaServiciosMedicosExiste) {
                                        $stmtAllServicesMedicos = $conn->prepare("
                                            SELECT COUNT(*) as total FROM servicios_medicos WHERE servicio_estado = 'ACTIVO'
                                        ");
                                        $stmtAllServicesMedicos->execute();
                                        $totalServiciosMedicos = $stmtAllServicesMedicos->fetch(PDO::FETCH_ASSOC)['total'];
                                        
                                        echo '<div class="alert alert-secondary">
                                            <strong>Total de servicios activos en servicios_medicos:</strong> ' . $totalServiciosMedicos . '
                                        </div>';
                                    } else {
                                        echo '<div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> La tabla servicios_medicos no existe
                                        </div>';
                                    }
                                    
                                    // Mostrar el total teórico de servicios (sin duplicados)
                                    echo '<div class="alert alert-primary">
                                        <strong>Total teórico de servicios (nota: pueden haber duplicados):</strong> ' . 
                                        ($totalRsServicios + $totalServiciosMedicos) . '
                                    </div>';
                                    
                                    echo '<hr><h4>Resultados por médico:</h4>';
                                    
                                    // Probar con cada médico
                                    foreach ($doctores as $doctor) {
                                        echo '<div class="card mb-4">
                                            <div class="card-header bg-secondary">
                                                <h5 class="card-title">Médico: ' . $doctor['nombre_doctor'] . ' (ID: ' . $doctor['doctor_id'] . ')</h5>
                                            </div>
                                            <div class="card-body">';
                                        
                                        // Obtener servicios para este médico
                                        $servicios = ModelServicios::mdlObtenerServiciosPorFechaMedico($fecha, $doctor['doctor_id']);
                                        
                                        echo '<div class="alert alert-success">
                                            <strong>Total de servicios obtenidos:</strong> ' . count($servicios) . '
                                        </div>';
                                        
                                        // Mostrar algunos servicios como muestra
                                        echo '<h5>Muestra de servicios (primeros 5):</h5>';
                                        echo '<div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Código</th>
                                                        <th>Nombre</th>
                                                        <th>Duración</th>
                                                        <th>Precio</th>
                                                        <th>Categoría</th>
                                                        <th>Origen</th>
                                                    </tr>
                                                </thead>
                                                <tbody>';
                                        
                                        $contador = 0;
                                        foreach ($servicios as $servicio) {
                                            if ($contador >= 5) break;
                                            
                                            echo '<tr>
                                                <td>' . $servicio['servicio_id'] . '</td>
                                                <td>' . $servicio['servicio_codigo'] . '</td>
                                                <td>' . $servicio['servicio_nombre'] . '</td>
                                                <td>' . $servicio['duracion_minutos'] . ' min</td>
                                                <td>$' . number_format($servicio['precio_base'], 2) . '</td>
                                                <td>' . $servicio['categoria_nombre'] . '</td>
                                                <td>' . (isset($servicio['origen']) ? $servicio['origen'] : 'N/A') . '</td>
                                            </tr>';
                                            
                                            $contador++;
                                        }
                                        
                                        echo '</tbody>
                                            </table>
                                        </div>';
                                        
                                        echo '</div>
                                        </div>';
                                    }
                                    
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle"></i> Error: ' . $e->getMessage() . '
                                    </div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="view/plugins/jquery/jquery.min.js"></script>
    <script src="view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="view/dist/js/adminlte.min.js"></script>
</body>
</html>
