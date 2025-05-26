<?php
/**
 * Script para diagnosticar problemas en la búsqueda de doctores por fecha
 */

// Configurar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir los archivos necesarios
require_once "model/conexion.php";
require_once "controller/servicios.controller.php";
require_once "model/servicios.model.php";

// Definir la fecha para diagnóstico (usar fecha actual por defecto)
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Determinar el día de la semana
$fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
if ($fechaObj) {
    $diaSemanaNum = (int)$fechaObj->format('N'); // 1-7 (ISO format: 1=lunes, 7=domingo)
    $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
    $diaSemana = $diasSemanaTexto[$diaSemanaNum];
} else {
    $diaSemana = "FORMATO INCORRECTO";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Médicos por Fecha</title>
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
                        <h1>Diagnóstico de Médicos por Fecha</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="servicios">Volver a Servicios</a></li>
                            <li class="breadcrumb-item"><a href="test_reserva.php">Probar Reservas</a></li>
                            <li class="breadcrumb-item"><a href="diagnostico_agenda_medico.php">Diagnóstico de Agendas</a></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Diagnóstico para fecha: <?php echo $fecha; ?> (<?php echo $diaSemana; ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Esta herramienta analiza paso a paso el proceso de búsqueda de médicos disponibles para una fecha específica.
                        </div>
                        
                        <div class="mb-4">
                            <form method="GET" class="form-inline">
                                <div class="form-group mr-2">
                                    <label for="fechaDiag" class="mr-2">Seleccione otra fecha:</label>
                                    <input type="date" class="form-control" id="fechaDiag" name="fecha" value="<?php echo $fecha; ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Diagnosticar
                                </button>
                            </form>                        </div>
                        
<?php
try {
    $conn = Conexion::conectar();
    if (!$conn) {
        echo '<div class="alert alert-danger">';
        echo '<i class="fas fa-exclamation-triangle"></i> Error de conexión a la base de datos: No se pudo establecer la conexión. Verifique los logs para más información.';
        echo '</div>';
        
        // Mostrar información de ayuda
        echo '<div class="card mb-4">';
        echo '<div class="card-header bg-warning">';
        echo '<h3 class="card-title">Sugerencias para resolver problemas de conexión</h3>';
        echo '</div>';
        echo '<div class="card-body">';
        echo '<ol>';
        echo '<li>Verifique que el servicio de PostgreSQL esté activo.</li>';
        echo '<li>Verifique que los datos de conexión sean correctos en el archivo model/conexion.php.</li>';
        echo '<li>Revise el archivo de log en: c:/laragon/www/clinica/logs/database.log</li>';
        echo '</ol>';
        echo '</div>';
        echo '</div>';
        
        echo '</div></div></div></div>';
        echo '<script src="view/plugins/jquery/jquery.min.js"></script>';
        echo '<script src="view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>';
        echo '<script src="view/dist/js/adminlte.min.js"></script>';
        echo '</body></html>';
        exit;
    }
    
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-primary text-white">';
    echo '<h3 class="card-title">1. Información de la fecha</h3>';
    echo '</div>';
    echo '<div class="card-body">';
    echo '<ul>';
    echo '<li><strong>Fecha seleccionada:</strong> ' . $fecha . '</li>';
    echo '<li><strong>Día de semana (numérico):</strong> ' . $diaSemanaNum . ' (1=Lunes, 7=Domingo)</li>';
    echo '<li><strong>Día de semana (texto):</strong> ' . $diaSemana . '</li>';
    echo '</ul>';
    echo '</div>';
    echo '</div>';
    
    // Paso 2: Verificar días disponibles en la tabla agendas_detalle
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-primary text-white">';
    echo '<h3 class="card-title">2. Días disponibles en agendas_detalle</h3>';
    echo '</div>';
    echo '<div class="card-body">';
    
    $stmt = $conn->prepare("SELECT DISTINCT dia_semana FROM agendas_detalle ORDER BY dia_semana");
    $stmt->execute();
    $diasDisponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($diasDisponibles) > 0) {
        echo '<div class="alert alert-success">';
        echo '<i class="fas fa-check-circle"></i> Se encontraron los siguientes días configurados en la tabla:';
        echo '</div>';
        echo '<ul>';
        foreach ($diasDisponibles as $dia) {
            echo '<li>' . $dia . ($dia == $diaSemana ? ' <span class="badge badge-success">Coincide con la fecha seleccionada</span>' : '') . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<div class="alert alert-danger">';
        echo '<i class="fas fa-times-circle"></i> No se encontraron días configurados en la tabla agendas_detalle.';
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
    
    // Paso 3: Verificar agendas para el día seleccionado
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-primary text-white">';    echo '<h3 class="card-title">3. Agendas configuradas para ' . $diaSemana . '</h3>';
    echo '</div>';
    echo '<div class="card-body">';
    
    $stmt = $conn->prepare("
        SELECT ad.detalle_id, ad.agenda_id, ad.dia_semana, ad.hora_inicio, ad.hora_fin, 
               ac.agenda_descripcion, ac.medico_id 
        FROM agendas_detalle ad 
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
        WHERE ad.dia_semana = :dia_semana AND ad.detalle_estado = true
        ORDER BY ad.hora_inicio
    ");
    $stmt->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
    $stmt->execute();
    $agendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($agendas) > 0) {
        echo '<div class="alert alert-success">';
        echo '<i class="fas fa-check-circle"></i> Se encontraron ' . count($agendas) . ' agendas para el día ' . $diaSemana;
        echo '</div>';
        
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-hover">';
        echo '<thead class="thead-light">';
        echo '<tr><th>ID Agenda</th><th>Médico ID</th><th>Descripción</th><th>Horario</th></tr>';
        echo '</thead><tbody>';
        
        foreach ($agendas as $agenda) {
            echo '<tr>';
            echo '<td>' . $agenda['agenda_id'] . '</td>';
            echo '<td>' . $agenda['medico_id'] . '</td>';
            echo '<td>' . $agenda['agenda_descripcion'] . '</td>';
            echo '<td>' . $agenda['hora_inicio'] . ' - ' . $agenda['hora_fin'] . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger">';
        echo '<i class="fas fa-times-circle"></i> No se encontraron agendas configuradas para el día ' . $diaSemana;
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
    
    // Paso 4: Verificar médicos asociados a esas agendas
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-primary text-white">';
    echo '<h3 class="card-title">4. Médicos asociados a las agendas</h3>';
    echo '</div>';
    echo '<div class="card-body">';
    
    if (count($agendas) > 0) {
        $medicoIds = array_column($agendas, 'medico_id');
        $medicoIds = array_unique($medicoIds);
        
        echo '<div class="alert alert-info">';
        echo '<i class="fas fa-info-circle"></i> IDs de médicos encontrados en las agendas: ' . implode(', ', $medicoIds);
        echo '</div>';
        
        // Verificar cuáles médicos existen en rh_doctors
        $placeholders = implode(',', array_fill(0, count($medicoIds), '?'));
        $stmt = $conn->prepare("
            SELECT d.doctor_id, p.first_name, p.last_name, d.doctor_estado,
                   p.first_name || ' ' || p.last_name AS nombre_completo
            FROM rh_doctors d
            LEFT JOIN rh_person p ON d.person_id = p.person_id
            WHERE d.doctor_id IN ($placeholders)
        ");
        
        foreach ($medicoIds as $index => $id) {
            $stmt->bindValue($index + 1, $id);
        }
        
        $stmt->execute();
        $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($medicos) > 0) {
            echo '<div class="alert alert-success">';
            echo '<i class="fas fa-check-circle"></i> Se encontraron ' . count($medicos) . ' médicos asociados a las agendas';
            echo '</div>';
            
            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered table-hover">';
            echo '<thead class="thead-light">';
            echo '<tr><th>ID Doctor</th><th>Nombre</th><th>Estado</th></tr>';
            echo '</thead><tbody>';
            
            foreach ($medicos as $medico) {
                echo '<tr>';
                echo '<td>' . $medico['doctor_id'] . '</td>';
                echo '<td>' . $medico['nombre_completo'] . '</td>';
                echo '<td>' . ($medico['doctor_estado'] == 'ACTIVO' ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>') . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            echo '</div>';
            
            // Verificar si todos los médicos fueron encontrados
            $medicosEncontradosIds = array_column($medicos, 'doctor_id');
            $medicosFaltantes = array_diff($medicoIds, $medicosEncontradosIds);
            
            if (count($medicosFaltantes) > 0) {
                echo '<div class="alert alert-warning">';
                echo '<i class="fas fa-exclamation-triangle"></i> Hay ' . count($medicosFaltantes) . ' médicos referenciados en agendas que no existen en la tabla rh_doctors: ' . implode(', ', $medicosFaltantes);
                echo '</div>';
            }
        } else {
            echo '<div class="alert alert-danger">';
            echo '<i class="fas fa-times-circle"></i> No se encontraron médicos asociados a las agendas en la tabla rh_doctors';
            echo '</div>';
        }
    } else {
        echo '<div class="alert alert-warning">';
        echo '<i class="fas fa-exclamation-triangle"></i> No hay agendas para verificar médicos';
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
    
    // Paso 5: Ejecutar la consulta final que se usaría para obtener médicos por fecha
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-primary text-white">';
    echo '<h3 class="card-title">5. Ejecución de la consulta para obtenerMedicosPorFecha</h3>';
    echo '</div>';
    echo '<div class="card-body">';
    
    // Mostrar la consulta SQL que se ejecutaría
    $sqlMuestra = "
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
    AND (ac.agenda_estado IS NULL OR ac.agenda_estado = true)    AND (d.doctor_estado IS NULL OR d.doctor_estado = 'ACTIVO')
ORDER BY
    nombre_doctor";
    
    echo '<div class="code-sql">';
    echo str_replace(
        ['SELECT', 'FROM', 'LEFT JOIN', 'WHERE', 'AND', 'ORDER BY', 'DISTINCT', 'ON', 'OR', 'IS NULL'], 
        ['<span class="sql-keyword">SELECT</span>', '<span class="sql-keyword">FROM</span>', '<span class="sql-keyword">LEFT JOIN</span>', '<span class="sql-keyword">WHERE</span>', '<span class="sql-keyword">AND</span>', '<span class="sql-keyword">ORDER BY</span>', '<span class="sql-keyword">DISTINCT</span>', '<span class="sql-keyword">ON</span>', '<span class="sql-keyword">OR</span>', '<span class="sql-keyword">IS NULL</span>'], 
        htmlspecialchars($sqlMuestra)
    );
    echo '</div>';
    
    // Ejecutar la consulta real
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
            rh_person p ON d.person_id = p.person_id        WHERE 
            ad.dia_semana = :dia_semana
            AND ad.detalle_estado = true            AND (ac.agenda_estado IS NULL OR ac.agenda_estado = true)
            AND (d.doctor_estado IS NULL OR d.doctor_estado = 'ACTIVO')
        ORDER BY
            nombre_doctor
    ");
    $stmt->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($resultados) > 0) {
        echo '<div class="alert alert-success mt-4">';
        echo '<i class="fas fa-check-circle"></i> La consulta devolvió ' . count($resultados) . ' resultados';
        echo '</div>';
        
        echo '<div class="table-responsive">';
        echo '<table class="table table-bordered table-hover">';
        echo '<thead class="thead-light">';
        echo '<tr><th>ID Doctor</th><th>ID Persona</th><th>Nombre</th><th>Estado</th></tr>';
        echo '</thead><tbody>';
        
        foreach ($resultados as $resultado) {
            echo '<tr>';
            echo '<td>' . $resultado['doctor_id'] . '</td>';
            echo '<td>' . $resultado['person_id'] . '</td>';
            echo '<td>' . $resultado['nombre_doctor'] . '</td>';
            echo '<td>' . ($resultado['doctor_estado'] == 'ACTIVO' ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger mt-4">';
        echo '<i class="fas fa-times-circle"></i> La consulta no devolvió resultados';
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
    
    // Paso 6: Resultado del método original
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-primary text-white">';
    echo '<h3 class="card-title">6. Resultado del método mdlObtenerMedicosDisponiblesPorFecha</h3>';
    echo '</div>';
    echo '<div class="card-body">';
    
    $medicosDisponibles = ModelServicios::mdlObtenerMedicosDisponiblesPorFecha($fecha);
    
    echo '<pre>';
    print_r($medicosDisponibles);
    echo '</pre>';
    
    echo '</div>';
    echo '</div>';
    
    // Paso 7: Conclusiones y recomendaciones
    echo '<div class="card mb-4">';
    echo '<div class="card-header bg-success text-white">';
    echo '<h3 class="card-title">7. Conclusiones y recomendaciones</h3>';
    echo '</div>';
    echo '<div class="card-body">';
    
    echo '<div class="alert alert-info">';
    echo '<h5><i class="fas fa-info-circle"></i> Resumen del diagnóstico:</h5>';
    echo '<ul>';
    
    if (in_array($diaSemana, $diasDisponibles)) {
        echo '<li>El día de la semana (' . $diaSemana . ') está configurado en la tabla agendas_detalle.</li>';
    } else {
        echo '<li class="text-danger">El día de la semana (' . $diaSemana . ') NO está configurado en la tabla agendas_detalle.</li>';
    }
    
    if (count($agendas) > 0) {
        echo '<li>Hay ' . count($agendas) . ' agendas configuradas para ' . $diaSemana . '.</li>';
    } else {
        echo '<li class="text-danger">No hay agendas configuradas para ' . $diaSemana . '.</li>';
    }
    
    if (isset($medicos) && count($medicos) > 0) {
        echo '<li>Hay ' . count($medicos) . ' médicos asociados a las agendas de ' . $diaSemana . '.</li>';
    } else {
        echo '<li class="text-danger">No hay médicos asociados a las agendas de ' . $diaSemana . '.</li>';
    }
    
    if (count($resultados) > 0) {
        echo '<li>La consulta devuelve ' . count($resultados) . ' médicos disponibles.</li>';
    } else {
        echo '<li class="text-danger">La consulta no devuelve médicos disponibles.</li>';
    }
    
    echo '</ul>';
    echo '</div>';
    
    // Recomendaciones
    echo '<h5>Recomendaciones:</h5>';
    echo '<ol>';
    
    if (!in_array($diaSemana, $diasDisponibles)) {
        echo '<li>Configure agendas para el día ' . $diaSemana . '.</li>';
    }
    
    if (count($agendas) == 0) {
        echo '<li>Crear agendas para el día ' . $diaSemana . '.</li>';
    }
    
    if (isset($medicosFaltantes) && count($medicosFaltantes) > 0) {
        echo '<li>Corregir las referencias a médicos inexistentes (IDs: ' . implode(', ', $medicosFaltantes) . ').</li>';
    }
    
    echo '</ol>';
    
    echo '</div>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">';
    echo '<i class="fas fa-exclamation-triangle"></i> Error: ' . $e->getMessage();
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
