<?php
/**
 * Script para diagnosticar problemas de relación entre agendas y médicos
 */

// Configurar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir los archivos necesarios
require_once "model/conexion.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Agendas y Médicos</title>
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
                        <h1>Diagnóstico de Agendas y Médicos</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="servicios">Volver a Servicios</a></li>
                            <li class="breadcrumb-item"><a href="test_reserva.php">Probar Reservas</a></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resultados del Diagnóstico</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Esta herramienta analiza las relaciones entre agendas y médicos para identificar posibles problemas.
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Actualización reciente:</strong> Se ha actualizado la referencia a la tabla de personas de 'people' a 'rh_person' para adaptarse a la estructura actual de la base de datos.
                        </div>

<?php
try {
    $conn = Conexion::conectar();
    
    // Verificar estructura de agendas_cabecera
    $stmt = $conn->prepare("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_name = 'agendas_cabecera'
    ");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Estructura de la tabla agendas_cabecera</h2>";
    echo "<pre>";
    print_r($columnas);
    echo "</pre>";
    
    // Mostrar datos de agendas_cabecera
    $stmt = $conn->prepare("SELECT * FROM agendas_cabecera");
    $stmt->execute();
    $agendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Datos de agendas_cabecera</h2>";
    echo "<pre>";
    print_r($agendas);
    echo "</pre>";
      // Verificar relaciones entre agendas y médicos
    echo '<div class="card mb-4">';
    echo '<div class="card-header">';
    echo '<h3 class="card-title">Relaciones entre agendas y médicos</h3>';
    echo '</div>';
    echo '<div class="card-body">';
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-hover">';
    echo '<thead class="thead-light">';
    echo "<tr><th>Agenda ID</th><th>Médico ID</th><th>Nombre Médico</th><th>Detalles de Agenda</th><th>Estado</th></tr>";
    echo '</thead><tbody>';
    
    $problemaEncontrado = false;
      foreach ($agendas as $agenda) {
        // Obtener información del médico
        $stmtMedico = $conn->prepare("
            SELECT d.doctor_id, p.first_name || ' ' || p.last_name AS nombre
            FROM rh_doctors d
            INNER JOIN rh_person p ON d.person_id = p.person_id
            WHERE d.doctor_id = :medico_id
        ");
        $stmtMedico->bindParam(":medico_id", $agenda['medico_id'], PDO::PARAM_INT);
        $stmtMedico->execute();
        $medico = $stmtMedico->fetch(PDO::FETCH_ASSOC);
        
        // Si no se encuentra el médico, verificar la existencia de la tabla rh_person y rh_doctors
        if (!$medico) {
            // Verificar si la tabla rh_person existe
            $stmtTableCheck = $conn->prepare("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = 'rh_person'
                ) AS table_exists
            ");
            $stmtTableCheck->execute();
            $tableExists = $stmtTableCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($tableExists['table_exists'] != 't') {
                echo '<div class="alert alert-danger">La tabla rh_person no existe en la base de datos.</div>';
            }
            
            // Verificar si la tabla rh_doctors existe
            $stmtDoctorsCheck = $conn->prepare("
                SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = 'rh_doctors'
                ) AS table_exists
            ");
            $stmtDoctorsCheck->execute();
            $doctorsExists = $stmtDoctorsCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($doctorsExists['table_exists'] != 't') {
                echo '<div class="alert alert-danger">La tabla rh_doctors no existe en la base de datos.</div>';
            }
            
            // Verificar si el médico existe en rh_doctors
            $stmtMedicoCheck = $conn->prepare("
                SELECT * FROM rh_doctors WHERE doctor_id = :medico_id
            ");
            $stmtMedicoCheck->bindParam(":medico_id", $agenda['medico_id'], PDO::PARAM_INT);
            $stmtMedicoCheck->execute();
            $medicoCheck = $stmtMedicoCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($medicoCheck) {
                echo '<div class="alert alert-warning">El médico ID ' . $agenda['medico_id'] . ' existe en rh_doctors pero no tiene una persona asociada o la relación es incorrecta.</div>';
            }
        }
        
        // Obtener detalles de agenda
        $stmtDetalles = $conn->prepare("
            SELECT ad.dia_semana, ad.hora_inicio, ad.hora_fin
            FROM agendas_detalle ad
            WHERE ad.agenda_id = :agenda_id AND ad.detalle_estado = true
        ");
        $stmtDetalles->bindParam(":agenda_id", $agenda['agenda_id'], PDO::PARAM_INT);
        $stmtDetalles->execute();
        $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
        
        $detallesStr = '';
        foreach ($detalles as $detalle) {
            $detallesStr .= '<span class="badge badge-info mr-1">' . $detalle['dia_semana'] . ': ' . $detalle['hora_inicio'] . ' - ' . $detalle['hora_fin'] . '</span> ';
        }
        
        // Verificar si hay problemas
        $estado = '';
        if (!$medico) {
            $problemaEncontrado = true;
            $estado = '<span class="badge badge-danger">Error: Médico no encontrado</span>';
        } elseif (count($detalles) == 0) {
            $problemaEncontrado = true;
            $estado = '<span class="badge badge-warning">Advertencia: No hay horarios definidos</span>';
        } else {
            $estado = '<span class="badge badge-success">OK</span>';
        }
        
        echo '<tr ' . (!$medico ? 'class="table-danger"' : '') . '>';
        echo "<td>{$agenda['agenda_id']}</td>";
        echo "<td>{$agenda['medico_id']}</td>";
        echo "<td>" . ($medico ? $medico['nombre'] : '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> MÉDICO NO ENCONTRADO</span>') . "</td>";
        echo "<td>{$detallesStr}</td>";
        echo "<td>{$estado}</td>";
        echo "</tr>";
    }
    
    echo '</tbody></table>';
    echo '</div>'; // table-responsive
    
    // Mostrar resumen y opciones de reparación si hay problemas
    if ($problemaEncontrado) {
        echo '<div class="alert alert-warning mt-3">';
        echo '<h5><i class="fas fa-exclamation-triangle"></i> Se encontraron problemas en la configuración</h5>';
        echo '<p>Se han detectado problemas en la relación entre médicos y agendas. Esto podría afectar al funcionamiento de las reservas.</p>';
        echo '<form action="reparar_agenda_medico.php" method="post" class="mt-3">';
        echo '<input type="hidden" name="repair" value="1">';
        echo '<button type="submit" class="btn btn-warning"><i class="fas fa-wrench"></i> Intentar reparación automática</button>';
        echo '</form>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-success mt-3">';
        echo '<i class="fas fa-check-circle"></i> No se encontraron problemas en la configuración. Las relaciones entre agendas y médicos parecen estar correctas.';
        echo '</div>';
    }
    
    echo '</div>'; // card-body
    echo '</div>'; // card
    
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
