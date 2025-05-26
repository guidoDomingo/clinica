<?php
/**
 * Script para probar la generación de slots disponibles
 * independientemente del servicio seleccionado
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
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 14;
$servicioId = isset($_GET['servicio_id']) ? intval($_GET['servicio_id']) : 2;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d', strtotime('+1 day')); // Por defecto mañana
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Slots Disponibles</title>
    <link rel="stylesheet" href="view/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="view/dist/css/adminlte.min.css">
    <style>
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body class="hold-transition">
    <div class="wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Test de Slots Disponibles</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="servicios">Volver a Servicios</a></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Parámetros de prueba</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="form-horizontal">
                            <div class="form-group row">
                                <label for="doctor_id" class="col-sm-2 col-form-label">ID del Doctor:</label>
                                <div class="col-sm-4">
                                    <input type="number" class="form-control" id="doctor_id" name="doctor_id" value="<?php echo $doctorId; ?>">
                                </div>
                                <label for="servicio_id" class="col-sm-2 col-form-label">ID del Servicio:</label>
                                <div class="col-sm-4">
                                    <input type="number" class="form-control" id="servicio_id" name="servicio_id" value="<?php echo $servicioId; ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="fecha" class="col-sm-2 col-form-label">Fecha:</label>
                                <div class="col-sm-4">
                                    <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>">
                                </div>
                                <div class="col-sm-6">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Probar con estos valores
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <?php
                // Verificar información del doctor
                try {
                    $conn = Conexion::conectar();
                    
                    echo '<div class="card">';
                    echo '<div class="card-header bg-primary text-white">';
                    echo '<h3 class="card-title">1. Información del Doctor</h3>';
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

                    // Verificar información del servicio
                    echo '<div class="card">';
                    echo '<div class="card-header bg-primary text-white">';
                    echo '<h3 class="card-title">2. Información del Servicio</h3>';
                    echo '</div>';
                    echo '<div class="card-body">';
                    
                    // Intentar obtener el servicio de ambas tablas
                    $servicio = null;
                    
                    // Intentar primero de servicios_medicos
                    $stmtCheck = $conn->prepare("SELECT to_regclass('public.servicios_medicos')");
                    $stmtCheck->execute();
                    $tablaServiciosMedicosExiste = $stmtCheck->fetchColumn();
                    
                    if ($tablaServiciosMedicosExiste) {
                        $stmtServicio = $conn->prepare("
                            SELECT 
                                sm.servicio_id,
                                sm.servicio_codigo,
                                sm.servicio_nombre,
                                sm.duracion_minutos,
                                sm.precio_base,
                                sm.servicio_estado,
                                c.categoria_nombre
                            FROM 
                                servicios_medicos sm
                            LEFT JOIN 
                                servicios_categorias c ON sm.categoria_id = c.categoria_id
                            WHERE 
                                sm.servicio_id = :servicio_id
                        ");
                        $stmtServicio->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
                        $stmtServicio->execute();
                        $servicio = $stmtServicio->fetch(PDO::FETCH_ASSOC);
                    }
                    
                    // Si no se encontró en servicios_medicos, buscar en rs_servicios
                    if (!$servicio) {
                        $stmtCheck = $conn->prepare("SELECT to_regclass('public.rs_servicios')");
                        $stmtCheck->execute();
                        $tablaRsServiciosExiste = $stmtCheck->fetchColumn();
                        
                        if ($tablaRsServiciosExiste) {
                            $stmtServicio = $conn->prepare("
                                SELECT 
                                    rs.serv_id as servicio_id,
                                    rs.serv_codigo as servicio_codigo,
                                    rs.serv_descripcion as servicio_nombre,
                                    30 as duracion_minutos,
                                    rs.serv_monto as precio_base,
                                    rs.is_active as servicio_estado,
                                    rst.servicio as categoria_nombre
                                FROM 
                                    rs_servicios rs
                                LEFT JOIN
                                    rs_servicios_tipos rst ON rs.tserv_cod = rst.tserv_cod
                                WHERE 
                                    rs.serv_id = :servicio_id
                            ");
                            $stmtServicio->bindParam(":servicio_id", $servicioId, PDO::PARAM_INT);
                            $stmtServicio->execute();
                            $servicio = $stmtServicio->fetch(PDO::FETCH_ASSOC);
                        }
                    }
                    
                    if ($servicio) {
                        echo '<div class="alert alert-success">Servicio encontrado correctamente.</div>';
                        echo '<ul>';
                        echo '<li><strong>Servicio ID:</strong> ' . $servicio['servicio_id'] . '</li>';
                        echo '<li><strong>Código:</strong> ' . $servicio['servicio_codigo'] . '</li>';
                        echo '<li><strong>Nombre:</strong> ' . $servicio['servicio_nombre'] . '</li>';
                        echo '<li><strong>Duración:</strong> ' . $servicio['duracion_minutos'] . ' minutos</li>';
                        echo '<li><strong>Precio:</strong> $' . number_format($servicio['precio_base'], 2) . '</li>';
                        echo '<li><strong>Categoría:</strong> ' . $servicio['categoria_nombre'] . '</li>';
                        echo '<li><strong>Estado:</strong> ' . ($servicio['servicio_estado'] ? 'Activo' : 'Inactivo') . '</li>';
                        echo '</ul>';
                    } else {
                        echo '<div class="alert alert-warning">No se encontró el servicio con ID ' . $servicioId . '</div>';
                        echo '<p>Continuando la prueba usando la duración predeterminada de 30 minutos.</p>';
                    }
                    echo '</div>';
                    echo '</div>';

                    // Verificar agendas del doctor para ese día
                    echo '<div class="card">';
                    echo '<div class="card-header bg-primary text-white">';
                    echo '<h3 class="card-title">3. Agendas del Doctor para el día</h3>';
                    echo '</div>';
                    echo '<div class="card-body">';
                    
                    // Determinar el día de la semana para la fecha
                    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
                    $diaSemanaNum = (int)$fechaObj->format('N'); // 1 (lunes) a 7 (domingo) según ISO-8601
                    $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
                    $diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
                    
                    echo '<div class="alert alert-info">';
                    echo '<strong>Fecha:</strong> ' . $fecha . ' | <strong>Día de la semana:</strong> ' . $diaSemanaTexto;
                    echo '</div>';
                    
                    // Buscar agendas del doctor para ese día
                    $stmtAgendas = $conn->prepare("
                        SELECT 
                            ad.detalle_id,
                            ac.agenda_id,
                            ad.turno_id,
                            t.turno_nombre,
                            ad.sala_id,
                            s.sala_nombre,
                            ad.dia_semana,
                            ad.hora_inicio,
                            ad.hora_fin,
                            ad.intervalo_minutos,
                            ad.cupo_maximo,
                            ad.detalle_estado
                        FROM 
                            agendas_detalle ad
                        INNER JOIN 
                            agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                        LEFT JOIN 
                            turnos t ON ad.turno_id = t.turno_id
                        LEFT JOIN 
                            salas s ON ad.sala_id = s.sala_id
                        WHERE 
                            ac.medico_id = :doctor_id
                            AND ad.dia_semana = :dia_semana
                            AND ad.detalle_estado = true
                            AND ac.agenda_estado = true
                        ORDER BY 
                            ad.hora_inicio ASC
                    ");
                    
                    $stmtAgendas->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                    $stmtAgendas->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
                    $stmtAgendas->execute();
                    $agendas = $stmtAgendas->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($agendas) > 0) {
                        echo '<div class="alert alert-success">';
                        echo 'Se encontraron ' . count($agendas) . ' agendas para el día ' . $diaSemanaTexto;
                        echo '</div>';
                        
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-bordered table-striped">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>ID Detalle</th>';
                        echo '<th>ID Agenda</th>';
                        echo '<th>Turno</th>';
                        echo '<th>Sala</th>';
                        echo '<th>Hora Inicio</th>';
                        echo '<th>Hora Fin</th>';
                        echo '<th>Intervalo</th>';
                        echo '<th>Cupo</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        foreach ($agendas as $agenda) {
                            echo '<tr>';
                            echo '<td>' . $agenda['detalle_id'] . '</td>';
                            echo '<td>' . $agenda['agenda_id'] . '</td>';
                            echo '<td>' . $agenda['turno_nombre'] . '</td>';
                            echo '<td>' . $agenda['sala_nombre'] . '</td>';
                            echo '<td>' . $agenda['hora_inicio'] . '</td>';
                            echo '<td>' . $agenda['hora_fin'] . '</td>';
                            echo '<td>' . $agenda['intervalo_minutos'] . ' min</td>';
                            echo '<td>' . $agenda['cupo_maximo'] . '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-danger">';
                        echo 'No se encontraron agendas para el doctor con ID ' . $doctorId . ' en el día ' . $diaSemanaTexto;
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';

                    // Verificar reservas existentes para ese día
                    echo '<div class="card">';
                    echo '<div class="card-header bg-primary text-white">';
                    echo '<h3 class="card-title">4. Reservas existentes para esta fecha</h3>';
                    echo '</div>';
                    echo '<div class="card-body">';
                    
                    $stmtCheck = $conn->prepare("SELECT to_regclass('public.servicios_reservas')");
                    $stmtCheck->execute();
                    $tablaReservasExiste = $stmtCheck->fetchColumn();
                    
                    if ($tablaReservasExiste) {
                        $stmtReservas = $conn->prepare("
                            SELECT 
                                reserva_id,
                                servicio_id,
                                agenda_id,
                                doctor_id,
                                fecha_reserva,
                                hora_inicio,
                                hora_fin,
                                reserva_estado
                            FROM 
                                servicios_reservas
                            WHERE 
                                doctor_id = :doctor_id
                                AND fecha_reserva = :fecha_reserva
                                AND reserva_estado IN ('CONFIRMADA', 'PENDIENTE')
                            ORDER BY 
                                hora_inicio ASC
                        ");
                        
                        $stmtReservas->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                        $stmtReservas->bindParam(":fecha_reserva", $fecha, PDO::PARAM_STR);
                        $stmtReservas->execute();
                        $reservas = $stmtReservas->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($reservas) > 0) {
                            echo '<div class="alert alert-info">';
                            echo 'Se encontraron ' . count($reservas) . ' reservas para el doctor en esta fecha.';
                            echo '</div>';
                            
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-bordered table-striped">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>ID Reserva</th>';
                            echo '<th>Servicio ID</th>';
                            echo '<th>Agenda ID</th>';
                            echo '<th>Hora Inicio</th>';
                            echo '<th>Hora Fin</th>';
                            echo '<th>Estado</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            
                            foreach ($reservas as $reserva) {
                                echo '<tr>';
                                echo '<td>' . $reserva['reserva_id'] . '</td>';
                                echo '<td>' . $reserva['servicio_id'] . '</td>';
                                echo '<td>' . $reserva['agenda_id'] . '</td>';
                                echo '<td>' . $reserva['hora_inicio'] . '</td>';
                                echo '<td>' . $reserva['hora_fin'] . '</td>';
                                echo '<td>' . $reserva['reserva_estado'] . '</td>';
                                echo '</tr>';
                            }
                            
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-success">';
                            echo 'No hay reservas para el doctor con ID ' . $doctorId . ' en la fecha ' . $fecha;
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="alert alert-warning">';
                        echo 'La tabla de reservas no existe todavía.';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';

                    // Generar slots disponibles
                    echo '<div class="card">';
                    echo '<div class="card-header bg-primary text-white">';
                    echo '<h3 class="card-title">5. Slots disponibles generados</h3>';
                    echo '</div>';
                    echo '<div class="card-body">';
                    
                    $slots = ModelServicios::mdlGenerarSlotsDisponibles($servicioId, $doctorId, $fecha);
                    
                    if (count($slots) > 0) {
                        echo '<div class="alert alert-success">';
                        echo 'Se generaron ' . count($slots) . ' slots disponibles para el doctor en esta fecha.';
                        echo '</div>';
                        
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-bordered table-striped">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Agenda ID</th>';
                        echo '<th>Turno</th>';
                        echo '<th>Sala</th>';
                        echo '<th>Hora Inicio</th>';
                        echo '<th>Hora Fin</th>';
                        echo '<th>Duración</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        foreach ($slots as $slot) {
                            echo '<tr>';
                            echo '<td>' . $slot['agenda_id'] . '</td>';
                            echo '<td>' . $slot['turno_nombre'] . '</td>';
                            echo '<td>' . $slot['sala_nombre'] . '</td>';
                            echo '<td>' . $slot['hora_inicio'] . '</td>';
                            echo '<td>' . $slot['hora_fin'] . '</td>';
                            echo '<td>' . $slot['duracion_minutos'] . ' min</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-danger">';
                        echo '<strong>No se generaron slots disponibles.</strong> Esto puede deberse a:';
                        echo '<ul>';
                        echo '<li>El doctor no tiene agendas configuradas para este día de la semana.</li>';
                        echo '<li>Todas las horas del doctor están reservadas.</li>';
                        echo '<li>El doctor no tiene horarios activos en esta fecha.</li>';
                        echo '</ul>';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';
                    
                    // Mostrar componente de integración en la interfaz
                    echo '<div class="card">';
                    echo '<div class="card-header bg-success text-white">';
                    echo '<h3 class="card-title">6. Slot Picker (como se vería en la interfaz)</h3>';
                    echo '</div>';
                    echo '<div class="card-body">';
                    
                    if (count($slots) > 0) {
                        echo '<div class="slot-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px;">';
                        
                        foreach ($slots as $index => $slot) {
                            echo '<div class="slot-item" style="border: 1px solid #ddd; border-radius: 5px; padding: 10px; text-align: center; background-color: #f8f9fa; cursor: pointer;" 
                                      onclick="selectSlot(' . $index . ')">';
                            echo '<div style="font-weight: bold;">' . substr($slot['hora_inicio'], 0, 5) . ' - ' . substr($slot['hora_fin'], 0, 5) . '</div>';
                            echo '<div style="font-size: 0.8rem; color: #666;">' . $slot['sala_nombre'] . '</div>';
                            echo '</div>';
                        }
                        
                        echo '</div>';
                        
                        echo '<div class="mt-3">';
                        echo '<div class="alert alert-info" id="selectedSlotInfo" style="display: none;"></div>';
                        echo '</div>';
                        
                        echo '<script>';
                        echo 'let slots = ' . json_encode($slots) . ';';
                        echo 'function selectSlot(index) {';
                        echo '  let slot = slots[index];';
                        echo '  let slotInfo = document.getElementById("selectedSlotInfo");';
                        echo '  slotInfo.style.display = "block";';
                        echo '  slotInfo.innerHTML = `<strong>Horario seleccionado:</strong> ${slot.hora_inicio.substr(0, 5)} - ${slot.hora_fin.substr(0, 5)}<br>`;';
                        echo '  slotInfo.innerHTML += `<strong>Sala:</strong> ${slot.sala_nombre}<br>`;';
                        echo '  slotInfo.innerHTML += `<strong>Turno:</strong> ${slot.turno_nombre}`;';
                        echo '  document.querySelectorAll(".slot-item").forEach(item => item.style.backgroundColor = "#f8f9fa");';
                        echo '  document.querySelectorAll(".slot-item")[index].style.backgroundColor = "#d4edda";';
                        echo '}';
                        echo '</script>';
                    } else {
                        echo '<div class="alert alert-warning">';
                        echo 'No hay horarios disponibles para mostrar en la interfaz.';
                        echo '</div>';
                    }
                    echo '</div>';
                    echo '</div>';

                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">';
                    echo '<strong>Error:</strong> ' . $e->getMessage();
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
    
    <script src="view/plugins/jquery/jquery.min.js"></script>
    <script src="view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
