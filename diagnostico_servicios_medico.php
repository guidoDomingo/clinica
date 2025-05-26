<?php
/**
 * Script para diagnosticar problemas en la búsqueda de servicios por médico y fecha
 */

// Configurar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir los archivos necesarios
require_once "model/conexion.php";
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";

// Definir la fecha y doctor para diagnóstico (usar valores por defecto)
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 13; // Valor por defecto: primer doctor

// Cabecera HTML
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Servicios por Médico y Fecha</title>
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
        .code-sql {
            background-color: #272822;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            margin-bottom: 20px;
            white-space: pre-wrap;
        }
        .sql-keyword { color: #f92672; }
        .sql-string { color: #a6e22e; }
        .sql-number { color: #ae81ff; }
        .sql-comment { color: #75715e; }
    </style>
</head>
<body class="hold-transition">
    <div class="wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Diagnóstico de Servicios por Médico y Fecha</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="servicios">Volver a Servicios</a></li>
                            <li class="breadcrumb-item"><a href="diagnostico_doctores_fecha.php">Diagnóstico de Doctores</a></li>
                            <li class="breadcrumb-item"><a href="test_reserva.php">Probar Reservas</a></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Diagnóstico para fecha: <?php echo $fecha; ?>, Doctor ID: <?php echo $doctorId; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Esta herramienta analiza paso a paso el proceso de búsqueda de servicios para un médico y fecha específicos.
                        </div>
                        
                        <div class="mb-4">
                            <form method="GET" class="form">
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label for="fechaDiag">Seleccione fecha:</label>
                                        <input type="date" class="form-control" id="fechaDiag" name="fecha" value="<?php echo $fecha; ?>">
                                    </div>
                                    
                                    <div class="col-md-4 form-group">
                                        <label for="doctorDiag">ID del médico:</label>
                                        <input type="number" class="form-control" id="doctorDiag" name="doctor_id" value="<?php echo $doctorId; ?>">
                                    </div>
                                    
                                    <div class="col-md-4 form-group align-self-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Diagnosticar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
<?php
try {
    $conn = Conexion::conectar();
    if (!$conn) {
        echo '<div class="alert alert-danger">';
        echo '<i class="fas fa-exclamation-triangle"></i> Error de conexión a la base de datos';
        echo '</div>';
        exit;
    }
    
    // 1. Verificar información del médico
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-primary text-white">';
    echo '<h3 class="card-title">1. Información del Médico</h3>';
    echo '</div>';
    echo '<div class="card-body">';
    
    $stmtDoctor = $conn->prepare("
        SELECT d.doctor_id, p.person_id, p.first_name || ' ' || p.last_name AS nombre_doctor, d.doctor_estado
        FROM rh_doctors d
        LEFT JOIN rh_person p ON d.person_id = p.person_id
        WHERE d.doctor_id = :doctor_id
    ");
    $stmtDoctor->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
    $stmtDoctor->execute();
    $doctorInfo = $stmtDoctor->fetch(PDO::FETCH_ASSOC);
    
    if ($doctorInfo) {
        echo '<div class="alert alert-success">Médico encontrado correctamente.</div>';
        echo '<ul>';
        echo '<li><strong>Doctor ID:</strong> ' . $doctorInfo['doctor_id'] . '</li>';
        echo '<li><strong>Person ID:</strong> ' . $doctorInfo['person_id'] . '</li>';
        echo '<li><strong>Nombre:</strong> ' . $doctorInfo['nombre_doctor'] . '</li>';
        echo '<li><strong>Estado:</strong> ' . ($doctorInfo['doctor_estado'] == 'ACTIVO' ? 'Activo' : 'Inactivo') . '</li>';
        echo '</ul>';
    } else {
        echo '<div class="alert alert-danger">No se encontró el médico con ID ' . $doctorId . '</div>';
    }
    echo '</div>';
    echo '</div>';
    
    // 2. Verificar tabla de servicios tradicional
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-primary text-white">';
    echo '<h3 class="card-title">2. Verificación de tabla servicios_medicos</h3>';
    echo '</div>';
    echo '<div class="card-body">';
    
    // Verificar si existe la tabla
    $stmtCheckTable = $conn->prepare("SELECT to_regclass('public.servicios_medicos')");
    $stmtCheckTable->execute();
    $serviciosMedicosExiste = $stmtCheckTable->fetchColumn();
    
    if ($serviciosMedicosExiste) {
        echo '<div class="alert alert-success">La tabla servicios_medicos existe.</div>';
        
        // Consultar servicios tradicionales asignados al médico
        $stmtServiciosTradicionales = $conn->prepare("
            SELECT 
                sm.servicio_id,
                sm.servicio_codigo,
                sm.servicio_nombre,
                sm.duracion_minutos,
                sm.precio_base,
                c.categoria_nombre
            FROM 
                servicios_medicos sm
            INNER JOIN
                servicios_categorias c ON sm.categoria_id = c.categoria_id
            INNER JOIN
                servicios_proveedores sp ON sm.servicio_id = sp.servicio_id
            WHERE 
                sp.doctor_id = :doctor_id
                AND sm.servicio_estado = 'ACTIVO'
                AND sp.proveedor_estado = true
            ORDER BY
                sm.servicio_nombre
        ");
        
        $stmtServiciosTradicionales->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
        $stmtServiciosTradicionales->execute();
        $serviciosTradicionales = $stmtServiciosTradicionales->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($serviciosTradicionales) > 0) {
            echo '<div class="alert alert-success">Se encontraron ' . count($serviciosTradicionales) . ' servicios tradicionales asignados al médico.</div>';
            
            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered table-hover">';
            echo '<thead class="thead-light">';
            echo '<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Duración</th><th>Precio</th><th>Categoría</th></tr>';
            echo '</thead><tbody>';
            
            foreach ($serviciosTradicionales as $servicio) {
                echo '<tr>';
                echo '<td>' . $servicio['servicio_id'] . '</td>';
                echo '<td>' . $servicio['servicio_codigo'] . '</td>';
                echo '<td>' . $servicio['servicio_nombre'] . '</td>';
                echo '<td>' . $servicio['duracion_minutos'] . ' min</td>';
                echo '<td>$' . number_format($servicio['precio_base'], 2) . '</td>';
                echo '<td>' . $servicio['categoria_nombre'] . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning">No se encontraron servicios tradicionales asignados al médico.</div>';
        }
    } else {
        echo '<div class="alert alert-warning">La tabla servicios_medicos no existe.</div>';
    }
    
    echo '</div>';
    echo '</div>';
    
    // 3. Verificar tabla de servicios nueva (rs_servicios)
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-primary text-white">';
    echo '<h3 class="card-title">3. Verificación de tabla rs_servicios</h3>';
    echo '</div>';
    echo '<div class="card-body">';
    
    $stmtCheckTable = $conn->prepare("SELECT to_regclass('public.rs_servicios')");
    $stmtCheckTable->execute();
    $rsServiciosExiste = $stmtCheckTable->fetchColumn();
    
    if ($rsServiciosExiste) {
        echo '<div class="alert alert-success">La tabla rs_servicios existe.</div>';
        
        // Verificar tabla de relación médico-servicio
        $stmtCheckRel = $conn->prepare("SELECT to_regclass('public.rs_medico_servicio')");
        $stmtCheckRel->execute();
        $medicoServicioExiste = $stmtCheckRel->fetchColumn();
        
        if ($medicoServicioExiste) {
            echo '<div class="alert alert-success">La tabla de relación rs_medico_servicio existe.</div>';
            
            // Consultar servicios asignados al médico
            $stmtServiciosRs = $conn->prepare("
                SELECT 
                    rs.serv_id as servicio_id,
                    rs.serv_codigo as servicio_codigo,
                    rs.serv_descripcion as servicio_nombre,
                    30 as duracion_minutos,
                    rs.serv_monto as precio_base,
                    rst.servicio as categoria_nombre
                FROM 
                    rs_servicios rs
                INNER JOIN
                    rs_servicios_tipos rst ON rs.tserv_cod = rst.tserv_cod
                INNER JOIN
                    rs_medico_servicio ms ON rs.serv_id = ms.serv_id
                WHERE 
                    ms.doctor_id = :doctor_id
                    AND rs.is_active = true
                ORDER BY
                    rs.serv_descripcion
            ");
            
            $stmtServiciosRs->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmtServiciosRs->execute();
            $serviciosRs = $stmtServiciosRs->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($serviciosRs) > 0) {
                echo '<div class="alert alert-success">Se encontraron ' . count($serviciosRs) . ' servicios nuevos (rs_servicios) asignados al médico.</div>';
                
                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered table-hover">';
                echo '<thead class="thead-light">';
                echo '<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Duración</th><th>Precio</th><th>Categoría</th></tr>';
                echo '</thead><tbody>';
                
                foreach ($serviciosRs as $servicio) {
                    echo '<tr>';
                    echo '<td>' . $servicio['servicio_id'] . '</td>';
                    echo '<td>' . $servicio['servicio_codigo'] . '</td>';
                    echo '<td>' . $servicio['servicio_nombre'] . '</td>';
                    echo '<td>' . $servicio['duracion_minutos'] . ' min</td>';
                    echo '<td>$' . number_format($servicio['precio_base'], 2) . '</td>';
                    echo '<td>' . $servicio['categoria_nombre'] . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-warning">No se encontraron servicios nuevos asignados a este médico en rs_medico_servicio.</div>';
                
                // Mostrar cuántos servicios hay en total
                $stmtTotal = $conn->prepare("SELECT COUNT(*) FROM rs_servicios WHERE is_active = true");
                $stmtTotal->execute();
                $totalServicios = $stmtTotal->fetchColumn();
                
                echo '<div class="alert alert-info">Hay ' . $totalServicios . ' servicios activos en total en rs_servicios.</div>';
                
                // Mostrar los médicos que tienen servicios asignados
                $stmtMedicos = $conn->prepare("
                    SELECT DISTINCT doctor_id FROM rs_medico_servicio ORDER BY doctor_id
                ");
                $stmtMedicos->execute();
                $medicosConServicios = $stmtMedicos->fetchAll(PDO::FETCH_COLUMN);
                
                if (count($medicosConServicios) > 0) {
                    echo '<div class="alert alert-info">Médicos con servicios asignados: ' . implode(', ', $medicosConServicios) . '</div>';
                } else {
                    echo '<div class="alert alert-warning">No hay asignaciones de médicos a servicios en rs_medico_servicio.</div>';
                }
            }
        } else {
            echo '<div class="alert alert-warning">La tabla de relación rs_medico_servicio no existe.</div>';
            echo '<div class="alert alert-info">Se mostrarán todos los servicios activos, ya que no hay forma de filtrar por médico.</div>';
            
            // Consultar todos los servicios activos
            $stmtTodosServicios = $conn->prepare("
                SELECT 
                    rs.serv_id as servicio_id,
                    rs.serv_codigo as servicio_codigo,
                    rs.serv_descripcion as servicio_nombre,
                    30 as duracion_minutos,
                    rs.serv_monto as precio_base,
                    rst.servicio as categoria_nombre
                FROM 
                    rs_servicios rs
                INNER JOIN
                    rs_servicios_tipos rst ON rs.tserv_cod = rst.tserv_cod
                WHERE 
                    rs.is_active = true
                ORDER BY
                    rs.serv_descripcion
            ");
            
            $stmtTodosServicios->execute();
            $todosServicios = $stmtTodosServicios->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<div class="alert alert-info">Hay ' . count($todosServicios) . ' servicios activos en total.</div>';
            
            if (count($todosServicios) > 0) {
                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered table-hover">';
                echo '<thead class="thead-light">';
                echo '<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Duración</th><th>Precio</th><th>Categoría</th></tr>';
                echo '</thead><tbody>';
                
                foreach (array_slice($todosServicios, 0, 10) as $servicio) { // Mostrar solo los primeros 10
                    echo '<tr>';
                    echo '<td>' . $servicio['servicio_id'] . '</td>';
                    echo '<td>' . $servicio['servicio_codigo'] . '</td>';
                    echo '<td>' . $servicio['servicio_nombre'] . '</td>';
                    echo '<td>' . $servicio['duracion_minutos'] . ' min</td>';
                    echo '<td>$' . number_format($servicio['precio_base'], 2) . '</td>';
                    echo '<td>' . $servicio['categoria_nombre'] . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
                
                if (count($todosServicios) > 10) {
                    echo '<div class="alert alert-info">Mostrando solo los primeros 10 de ' . count($todosServicios) . ' servicios.</div>';
                }
                echo '</div>';
            }
        }
    } else {
        echo '<div class="alert alert-warning">La tabla rs_servicios no existe.</div>';
    }
    
    echo '</div>';
    echo '</div>';
    
    // 4. Resultado del método mdlObtenerServiciosPorFechaMedico
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-primary text-white">';
    echo '<h3 class="card-title">4. Resultado del método mdlObtenerServiciosPorFechaMedico</h3>';
    echo '</div>';
    echo '<div class="card-body">';
    
    $serviciosCombinados = ModelServicios::mdlObtenerServiciosPorFechaMedico($fecha, $doctorId);
    
    if (count($serviciosCombinados) > 0) {
        echo '<div class="alert alert-success">El método devolvió ' . count($serviciosCombinados) . ' servicios en total.</div>';
        
        echo '<pre>';
        print_r($serviciosCombinados);
        echo '</pre>';
    } else {
        echo '<div class="alert alert-warning">El método no devolvió ningún servicio.</div>';
    }
    
    echo '</div>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">';
    echo 'Error: ' . $e->getMessage();
    echo '</div>';
}
?>

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
