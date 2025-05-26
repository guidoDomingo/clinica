<?php
/**
 * Script para reparar problemas de relación entre agendas y médicos
 * 
 * Este script analiza y repara automáticamente los siguientes problemas:
 * 1. Agendas sin médico asignado o con médico inexistente
 * 2. Agendas sin detalles de horarios
 */

// Configurar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir los archivos necesarios
require_once "model/conexion.php";

// Inicializar variables para el reporte
$erroresEncontrados = 0;
$erroresReparados = 0;
$logReparacion = [];

// Verificar si se solicitó la reparación
$repararSolicitado = isset($_POST['repair']) && $_POST['repair'] == 1;

// Función de registro para seguimiento
function registrarAccion($mensaje, $tipo = 'info') {
    global $logReparacion;
    $logReparacion[] = [
        'mensaje' => $mensaje,
        'tipo' => $tipo,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reparación de Agendas y Médicos</title>
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
        .log-info {
            color: #0c5460;
            background-color: #d1ecf1;
            padding: 5px 10px;
            border-radius: 3px;
            margin: 2px 0;
        }
        .log-warning {
            color: #856404;
            background-color: #fff3cd;
            padding: 5px 10px;
            border-radius: 3px;
            margin: 2px 0;
        }
        .log-error {
            color: #721c24;
            background-color: #f8d7da;
            padding: 5px 10px;
            border-radius: 3px;
            margin: 2px 0;
        }
        .log-success {
            color: #155724;
            background-color: #d4edda;
            padding: 5px 10px;
            border-radius: 3px;
            margin: 2px 0;
        }
    </style>
</head>
<body class="hold-transition">
    <div class="wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Reparación de Agendas y Médicos</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="servicios">Volver a Servicios</a></li>
                            <li class="breadcrumb-item"><a href="diagnostico_agenda_medico.php">Volver a Diagnóstico</a></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resultado de la Reparación</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!$repararSolicitado): ?>
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle"></i> Acción no autorizada</h5>
                                <p>Esta página debe ser accedida desde el diagnóstico.</p>
                                <a href="diagnostico_agenda_medico.php" class="btn btn-info"><i class="fas fa-arrow-left"></i> Volver al diagnóstico</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-cogs"></i> Ejecutando reparación automática...
                            </div>
                            
                            <?php                            try {
                                $conn = Conexion::conectar();
                                $conn->beginTransaction();
                                
                                registrarAccion("Iniciando proceso de reparación");
                                
                                // Verificar la estructura de las tablas primero
                                registrarAccion("Verificando estructura de tablas necesarias", 'info');
                                
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
                                    registrarAccion("Advertencia: La tabla rh_person no existe en la base de datos", 'warning');
                                } else {
                                    registrarAccion("Tabla rh_person encontrada", 'info');
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
                                    registrarAccion("Advertencia: La tabla rh_doctors no existe en la base de datos", 'warning');
                                } else {
                                    registrarAccion("Tabla rh_doctors encontrada", 'info');
                                }
                                  // 1. Identificar agendas sin médico válido
                                $stmt = $conn->prepare("
                                    SELECT ac.agenda_id, ac.medico_id
                                    FROM agendas_cabecera ac
                                    LEFT JOIN rh_doctors d ON ac.medico_id = d.doctor_id
                                    WHERE d.doctor_id IS NULL
                                ");
                                $stmt->execute();
                                $agendasSinMedico = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                registrarAccion("Se encontraron " . count($agendasSinMedico) . " agendas sin médico válido", 
                                    count($agendasSinMedico) > 0 ? 'warning' : 'info');
                                
                                // 2. Reparar agendas sin médico válido (deshabilitar la agenda)
                                foreach ($agendasSinMedico as $agenda) {
                                    $erroresEncontrados++;
                                    registrarAccion("Agenda ID {$agenda['agenda_id']} vinculada a médico inexistente ID {$agenda['medico_id']}", 'error');
                                    
                                    // Opción 1: Deshabilitar la agenda
                                    $stmtUpdate = $conn->prepare("
                                        UPDATE agendas_cabecera
                                        SET agenda_estado = false
                                        WHERE agenda_id = :agenda_id
                                    ");
                                    $stmtUpdate->bindParam(":agenda_id", $agenda['agenda_id'], PDO::PARAM_INT);
                                    $stmtUpdate->execute();
                                    
                                    registrarAccion("Agenda ID {$agenda['agenda_id']} deshabilitada correctamente", 'success');
                                    $erroresReparados++;
                                }
                                  // 3. Identificar agendas sin detalles de horarios
                                $stmt = $conn->prepare("
                                    SELECT ac.agenda_id, ac.medico_id, COUNT(ad.detalle_id) as num_detalles
                                    FROM agendas_cabecera ac
                                    LEFT JOIN agendas_detalle ad ON ac.agenda_id = ad.agenda_id AND ad.detalle_estado = true
                                    GROUP BY ac.agenda_id, ac.medico_id
                                    HAVING COUNT(ad.detalle_id) = 0
                                ");
                                $stmt->execute();
                                $agendasSinHorarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                registrarAccion("Se encontraron " . count($agendasSinHorarios) . " agendas sin horarios definidos", 
                                    count($agendasSinHorarios) > 0 ? 'warning' : 'info');
                                
                                // 4. Reparar agendas sin horarios (deshabilitar la agenda)
                                foreach ($agendasSinHorarios as $agenda) {
                                    $erroresEncontrados++;
                                    registrarAccion("Agenda ID {$agenda['agenda_id']} no tiene horarios definidos", 'error');
                                    
                                    // Opción 1: Deshabilitar la agenda
                                    $stmtUpdate = $conn->prepare("
                                        UPDATE agendas_cabecera
                                        SET agenda_estado = false
                                        WHERE agenda_id = :agenda_id
                                    ");
                                    $stmtUpdate->bindParam(":agenda_id", $agenda['agenda_id'], PDO::PARAM_INT);
                                    $stmtUpdate->execute();
                                    
                                    registrarAccion("Agenda ID {$agenda['agenda_id']} deshabilitada correctamente", 'success');
                                    $erroresReparados++;
                                }
                                
                                $conn->commit();
                                registrarAccion("Proceso de reparación completado con éxito", 'success');
                                
                            } catch (Exception $e) {
                                if ($conn) {
                                    $conn->rollBack();
                                }
                                registrarAccion("Error en el proceso: " . $e->getMessage(), 'error');
                            }
                            
                            // Mostrar resumen de reparación
                            echo '<div class="card mb-4">';
                            echo '<div class="card-header bg-primary">';
                            echo '<h3 class="card-title text-white">Resumen de la reparación</h3>';
                            echo '</div>';
                            echo '<div class="card-body">';
                            
                            if ($erroresEncontrados > 0) {
                                echo '<div class="alert alert-' . ($erroresReparados == $erroresEncontrados ? 'success' : 'warning') . '">';
                                echo '<h5><i class="fas fa-' . ($erroresReparados == $erroresEncontrados ? 'check-circle' : 'exclamation-triangle') . '"></i> Reparación Completada</h5>';
                                echo "<p>Se encontraron <strong>{$erroresEncontrados}</strong> problemas.</p>";
                                echo "<p>Se repararon <strong>{$erroresReparados}</strong> problemas.</p>";
                                if ($erroresReparados < $erroresEncontrados) {
                                    echo "<p>Algunos problemas no pudieron ser reparados automáticamente y requieren intervención manual.</p>";
                                }
                                echo '</div>';
                            } else {
                                echo '<div class="alert alert-info">';
                                echo '<h5><i class="fas fa-info-circle"></i> No se encontraron problemas</h5>';
                                echo "<p>No se encontraron problemas que requieran reparación.</p>";
                                echo '</div>';
                            }
                            
                            // Mostrar log de acciones
                            echo '<h4>Registro de acciones</h4>';
                            echo '<div class="log-container">';
                            foreach ($logReparacion as $log) {
                                echo '<div class="log-' . $log['tipo'] . '">';
                                echo '<i class="fas fa-' . ($log['tipo'] == 'success' ? 'check-circle' : ($log['tipo'] == 'error' ? 'times-circle' : ($log['tipo'] == 'warning' ? 'exclamation-triangle' : 'info-circle'))) . '"></i> ';
                                echo '<small>' . $log['timestamp'] . '</small> ';
                                echo $log['mensaje'];
                                echo '</div>';
                            }
                            echo '</div>';
                            
                            echo '</div>'; // card-body
                            echo '</div>'; // card
                            
                            echo '<div class="text-center mb-4">';
                            echo '<a href="diagnostico_agenda_medico.php" class="btn btn-info mr-2"><i class="fas fa-sync-alt"></i> Volver a diagnosticar</a>';
                            echo '<a href="servicios" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Volver a Servicios</a>';
                            echo '</div>';
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
