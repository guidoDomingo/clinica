<?php
/**
 * Script para probar la consulta específica que obtiene médicos por fecha
 */

// Configurar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir los archivos necesarios
require_once "model/conexion.php";

// Definir la fecha para la prueba (usar fecha actual por defecto)
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Procesar el formulario de prueba
$ejecutar = isset($_GET['ejecutar']) && $_GET['ejecutar'] == '1';

// Cabecera para mostrar los resultados como HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Directa de Consulta SQL - Médicos por Fecha</title>
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
                        <h1>Prueba Directa de Consulta SQL</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="servicios">Volver a Servicios</a></li>
                            <li class="breadcrumb-item"><a href="test_reserva.php">Probar Reservas</a></li>
                            <li class="breadcrumb-item"><a href="diagnostico_doctores_fecha.php">Diagnóstico</a></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Prueba de Consulta SQL para Obtener Médicos por Fecha</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Esta herramienta ejecuta directamente la consulta SQL que obtiene los médicos disponibles por fecha.
                        </div>
                        
                        <div class="mb-4">
                            <form method="GET" class="form-inline">
                                <div class="form-group mr-2">
                                    <label for="fechaPrueba" class="mr-2">Fecha a probar:</label>
                                    <input type="date" class="form-control" id="fechaPrueba" name="fecha" value="<?php echo $fecha; ?>">
                                </div>
                                <input type="hidden" name="ejecutar" value="1">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Ejecutar consulta
                                </button>
                            </form>
                        </div>
                        
                        <?php if ($ejecutar): ?>
                        <?php
                            // Determinar el día de la semana
                            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
                            if (!$fechaObj) {
                                echo '<div class="alert alert-danger">Formato de fecha inválido</div>';
                                exit();
                            }
                            $diaSemanaNum = (int)$fechaObj->format('N'); // 1-7 (ISO format: 1=lunes, 7=domingo)
                            $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
                            $diaSemana = $diasSemanaTexto[$diaSemanaNum];
                            
                            echo '<h4>Información de fecha</h4>';
                            echo '<ul>';
                            echo '<li><strong>Fecha:</strong> ' . $fecha . '</li>';
                            echo '<li><strong>Día de semana (número):</strong> ' . $diaSemanaNum . '</li>';
                            echo '<li><strong>Día de semana (texto):</strong> ' . $diaSemana . '</li>';
                            echo '</ul>';
                            
                            // Mostrar la consulta SQL 
                            echo '<h4>Consulta SQL</h4>';
                            $sql = "
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
    ad.dia_semana = '$diaSemana'
    AND ad.detalle_estado = true
    AND (ac.agenda_estado IS NULL OR ac.agenda_estado = true)
    AND (d.doctor_estado IS NULL OR d.doctor_estado = 'ACTIVO')
ORDER BY
    nombre_doctor";
                            
                            echo '<div class="code-sql">';
                            echo str_replace(
                                ['SELECT', 'FROM', 'LEFT JOIN', 'WHERE', 'AND', 'ORDER BY', 'DISTINCT', 'ON', 'OR', 'IS NULL'], 
                                ['<span class="sql-keyword">SELECT</span>', '<span class="sql-keyword">FROM</span>', '<span class="sql-keyword">LEFT JOIN</span>', '<span class="sql-keyword">WHERE</span>', '<span class="sql-keyword">AND</span>', '<span class="sql-keyword">ORDER BY</span>', '<span class="sql-keyword">DISTINCT</span>', '<span class="sql-keyword">ON</span>', '<span class="sql-keyword">OR</span>', '<span class="sql-keyword">IS NULL</span>'], 
                                htmlspecialchars($sql)
                            );
                            echo '</div>';
                            
                            echo '<h4>Resultados de la ejecución</h4>';
                            
                            try {
                                $conn = Conexion::conectar();
                                
                                // Verificar días disponibles
                                $stmtDias = $conn->prepare("SELECT DISTINCT dia_semana FROM agendas_detalle ORDER BY dia_semana");
                                $stmtDias->execute();
                                $diasDisponibles = $stmtDias->fetchAll(PDO::FETCH_COLUMN);
                                
                                echo '<div class="mb-3">';
                                echo '<strong>Días disponibles en la base de datos:</strong> ' . implode(', ', $diasDisponibles);
                                echo '</div>';
                                
                                // Ejecutar la consulta que usamos en el modelo
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (count($resultados) > 0) {
                                    echo '<div class="alert alert-success">';
                                    echo '<i class="fas fa-check-circle"></i> La consulta devolvió ' . count($resultados) . ' resultados.';
                                    echo '</div>';
                                    
                                    echo '<div class="table-responsive">';
                                    echo '<table class="table table-bordered table-hover">';
                                    echo '<thead class="thead-light">';
                                    echo '<tr><th>ID Doctor</th><th>ID Persona</th><th>Nombre</th><th>Estado</th></tr>';
                                    echo '</thead><tbody>';
                                    
                                    foreach ($resultados as $resultado) {                                        echo '<tr>';
                                        echo '<td>' . $resultado['doctor_id'] . '</td>';
                                        echo '<td>' . $resultado['person_id'] . '</td>';
                                        echo '<td>' . $resultado['nombre_doctor'] . '</td>';
                                        echo '<td>' . ($resultado['doctor_estado'] == 'ACTIVO' ? 'Activo' : 'Inactivo') . '</td>';
                                        echo '</tr>';
                                    }
                                    
                                    echo '</tbody></table>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="alert alert-warning">';
                                    echo '<i class="fas fa-exclamation-triangle"></i> La consulta no devolvió resultados.';
                                    echo '</div>';
                                    
                                    // Verificar si hay agendas para ese día
                                    $stmtAgendas = $conn->prepare("
                                        SELECT COUNT(*) 
                                        FROM agendas_detalle 
                                        WHERE dia_semana = :dia_semana AND detalle_estado = true
                                    ");
                                    $stmtAgendas->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
                                    $stmtAgendas->execute();
                                    $conteoAgendas = $stmtAgendas->fetchColumn();
                                    
                                    echo '<div class="mb-3">';
                                    echo '<strong>Cantidad de agendas para ' . $diaSemana . ':</strong> ' . $conteoAgendas;
                                    echo '</div>';
                                    
                                    if ($conteoAgendas > 0) {
                                        // Verificar los detalles de las agendas
                                        $stmtAgendaDetalle = $conn->prepare("
                                            SELECT ad.*, ac.medico_id
                                            FROM agendas_detalle ad
                                            INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                                            WHERE ad.dia_semana = :dia_semana AND ad.detalle_estado = true
                                        ");
                                        $stmtAgendaDetalle->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
                                        $stmtAgendaDetalle->execute();
                                        $agendaDetalles = $stmtAgendaDetalle->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        echo '<div class="mb-3">';
                                        echo '<strong>Detalles de agendas para ' . $diaSemana . ':</strong>';
                                        echo '<pre>' . print_r($agendaDetalles, true) . '</pre>';
                                        echo '</div>';
                                        
                                        // Verificar los médicos de esas agendas
                                        $medicoIds = array_column($agendaDetalles, 'medico_id');
                                        $medicoIds = array_unique($medicoIds);
                                        
                                        if (!empty($medicoIds)) {
                                            $placeholders = implode(',', array_fill(0, count($medicoIds), '?'));
                                            $stmtMedicos = $conn->prepare("
                                                SELECT d.doctor_id, p.person_id, p.first_name, p.last_name
                                                FROM rh_doctors d
                                                LEFT JOIN rh_person p ON d.person_id = p.person_id
                                                WHERE d.doctor_id IN ($placeholders)
                                            ");
                                            
                                            foreach ($medicoIds as $i => $id) {
                                                $stmtMedicos->bindValue($i + 1, $id);
                                            }
                                            
                                            $stmtMedicos->execute();
                                            $medicosInfo = $stmtMedicos->fetchAll(PDO::FETCH_ASSOC);
                                            
                                            echo '<div class="mb-3">';
                                            echo '<strong>Información de médicos asociados:</strong>';
                                            echo '<pre>' . print_r($medicosInfo, true) . '</pre>';
                                            echo '</div>';
                                        } else {
                                            echo '<div class="alert alert-danger">No se encontraron IDs de médicos en las agendas.</div>';
                                        }
                                    } else {
                                        echo '<div class="alert alert-danger">No hay agendas configuradas para el día ' . $diaSemana . '.</div>';
                                    }
                                }
                                
                            } catch (Exception $e) {
                                echo '<div class="alert alert-danger">';
                                echo 'Error al ejecutar la consulta: ' . $e->getMessage();
                                echo '</div>';
                            }
                        ?>
                        <?php endif; ?>
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
