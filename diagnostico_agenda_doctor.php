<?php
/**
 * Herramienta para diagnosticar problemas con las agendas de médicos
 * específicamente para verificar si hay datos en las tablas de agendas
 */

// Incluir archivos necesarios
require_once "model/conexion.php";

// Configuración de visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Parámetros de consulta
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 14;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '2025-05-28';

// Determinar el día de la semana para la fecha
$fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
$diaSemanaNum = (int)$fechaObj->format('N'); // 1 (lunes) a 7 (domingo) según ISO-8601
$diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
$diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Agendas - Doctor ID <?php echo $doctorId; ?></title>
    <link rel="stylesheet" href="view/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/plugins/fontawesome-free/css/all.min.css">
    <style>
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        .card { margin-bottom: 20px; }
        .query-box { background: #272822; color: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; }
        .sql-keyword { color: #f92672; }
        .sql-string { color: #a6e22e; }
        .sql-table { color: #66d9ef; }
    </style>
</head>
<body class="hold-transition">
    <div class="container-fluid py-4">
        <h1>Diagnóstico de Agendas - Doctor ID <?php echo $doctorId; ?></h1>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Parámetros de consulta</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="form-inline">
                            <div class="form-group mr-2">
                                <label for="doctorId" class="mr-2">Doctor ID:</label>
                                <input type="number" class="form-control" id="doctorId" name="doctor_id" value="<?php echo $doctorId; ?>">
                            </div>
                            <div class="form-group mr-2">
                                <label for="fecha" class="mr-2">Fecha:</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Consultar</button>
                        </form>
                        
                        <div class="mt-3">
                            <p><strong>Fecha:</strong> <?php echo $fecha; ?></p>
                            <p><strong>Día de la semana:</strong> <?php echo $diaSemanaTexto; ?> (<?php echo $diaSemanaNum; ?>)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        try {
            $conn = Conexion::conectar();
            
            // 1. Verificar si el doctor existe
            echo '<div class="row mb-4"><div class="col-12"><div class="card">';
            echo '<div class="card-header bg-secondary text-white"><h3 class="card-title">1. Información del doctor</h3></div>';
            echo '<div class="card-body">';
            
            $stmtDoctor = $conn->prepare("
                SELECT d.doctor_id, p.person_id, p.first_name, p.last_name, 
                       p.first_name || ' ' || p.last_name AS nombre_completo, d.doctor_estado
                FROM rh_doctors d
                JOIN rh_person p ON d.person_id = p.person_id
                WHERE d.doctor_id = :doctor_id
            ");
            $stmtDoctor->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmtDoctor->execute();
            $doctor = $stmtDoctor->fetch(PDO::FETCH_ASSOC);
            
            if ($doctor) {
                echo '<div class="alert alert-success"><strong>Doctor encontrado:</strong> ' . $doctor['nombre_completo'] . ' (ID: ' . $doctor['doctor_id'] . ')</div>';
                echo '<ul>';
                echo '<li><strong>Doctor ID:</strong> ' . $doctor['doctor_id'] . '</li>';
                echo '<li><strong>Person ID:</strong> ' . $doctor['person_id'] . '</li>';
                echo '<li><strong>Nombre:</strong> ' . $doctor['first_name'] . ' ' . $doctor['last_name'] . '</li>';
                echo '<li><strong>Estado:</strong> ' . $doctor['doctor_estado'] . '</li>';
                echo '</ul>';
            } else {
                echo '<div class="alert alert-danger"><strong>Error:</strong> No se encontró el doctor con ID ' . $doctorId . '</div>';
            }
            
            echo '</div></div></div></div>';
            
            // 2. Verificar agendas cabecera asociadas al doctor
            echo '<div class="row mb-4"><div class="col-12"><div class="card">';
            echo '<div class="card-header bg-secondary text-white"><h3 class="card-title">2. Agendas cabecera del doctor</h3></div>';
            echo '<div class="card-body">';
            
            $stmtAgendas = $conn->prepare("
                SELECT agenda_id, agenda_descripcion, medico_id, agenda_estado, created_at
                FROM agendas_cabecera
                WHERE medico_id = :doctor_id
                ORDER BY agenda_id
            ");
            $stmtAgendas->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmtAgendas->execute();
            $agendas = $stmtAgendas->fetchAll(PDO::FETCH_ASSOC);
            
            if ($agendas) {
                echo '<div class="alert alert-success"><strong>Agendas encontradas:</strong> ' . count($agendas) . '</div>';
                
                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered table-striped">';
                echo '<thead><tr><th>Agenda ID</th><th>Descripción</th><th>Médico ID</th><th>Estado</th><th>Creada</th></tr></thead>';
                echo '<tbody>';
                
                foreach ($agendas as $agenda) {
                    echo '<tr>';
                    echo '<td>' . $agenda['agenda_id'] . '</td>';
                    echo '<td>' . $agenda['agenda_descripcion'] . '</td>';
                    echo '<td>' . $agenda['medico_id'] . '</td>';
                    echo '<td>' . ($agenda['agenda_estado'] ? 'Activo' : 'Inactivo') . '</td>';
                    echo '<td>' . $agenda['created_at'] . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table></div>';
            } else {
                echo '<div class="alert alert-danger"><strong>Error:</strong> No se encontraron agendas para el doctor con ID ' . $doctorId . '</div>';
            }
            
            echo '</div></div></div></div>';
            
            // 3. Verificar detalles de agenda para el día específico
            echo '<div class="row mb-4"><div class="col-12"><div class="card">';
            echo '<div class="card-header bg-secondary text-white"><h3 class="card-title">3. Detalles de agenda para el día ' . $diaSemanaTexto . '</h3></div>';
            echo '<div class="card-body">';
            
            // Mostrar la consulta SQL que estamos ejecutando
            $sqlDetalle = "
                SELECT 
                    ad.detalle_id, ad.agenda_id, ad.dia_semana, ad.hora_inicio, ad.hora_fin, 
                    ad.intervalo_minutos, ad.cupo_maximo, ad.detalle_estado,
                    ad.turno_id, t.turno_nombre, ad.sala_id, s.sala_nombre,
                    ac.medico_id, p.first_name || ' ' || p.last_name AS nombre_doctor
                FROM 
                    agendas_detalle ad
                INNER JOIN 
                    agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                INNER JOIN 
                    turnos t ON ad.turno_id = t.turno_id
                LEFT JOIN 
                    salas s ON ad.sala_id = s.sala_id
                LEFT JOIN
                    rh_doctors rd ON rd.doctor_id = ac.medico_id 
                LEFT JOIN 
                    rh_person p ON p.person_id = rd.person_id
                WHERE 
                    ac.medico_id = :doctor_id
                    AND ad.dia_semana = :dia_semana
                ORDER BY 
                    ad.hora_inicio ASC
            ";
            
            echo '<div class="query-box mb-4">';
            echo str_replace(
                ['SELECT', 'FROM', 'INNER JOIN', 'LEFT JOIN', 'WHERE', 'AND', 'ORDER BY'],
                ['<span class="sql-keyword">SELECT</span>', '<span class="sql-keyword">FROM</span>', '<span class="sql-keyword">INNER JOIN</span>', '<span class="sql-keyword">LEFT JOIN</span>', '<span class="sql-keyword">WHERE</span>', '<span class="sql-keyword">AND</span>', '<span class="sql-keyword">ORDER BY</span>'],
                htmlspecialchars($sqlDetalle)
            );
            echo '</div>';
            
            $stmtDetalles = $conn->prepare($sqlDetalle);
            $stmtDetalles->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
            $stmtDetalles->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
            $stmtDetalles->execute();
            $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
            
            if ($detalles) {
                echo '<div class="alert alert-success"><strong>Detalles de agenda encontrados:</strong> ' . count($detalles) . '</div>';
                
                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered table-striped">';
                echo '<thead><tr><th>Detalle ID</th><th>Agenda ID</th><th>Día</th><th>Turno</th><th>Sala</th><th>Hora Inicio</th><th>Hora Fin</th><th>Intervalo</th><th>Cupo</th><th>Estado</th></tr></thead>';
                echo '<tbody>';
                
                foreach ($detalles as $detalle) {
                    echo '<tr>';
                    echo '<td>' . $detalle['detalle_id'] . '</td>';
                    echo '<td>' . $detalle['agenda_id'] . '</td>';
                    echo '<td>' . $detalle['dia_semana'] . '</td>';
                    echo '<td>' . $detalle['turno_nombre'] . '</td>';
                    echo '<td>' . $detalle['sala_nombre'] . '</td>';
                    echo '<td>' . $detalle['hora_inicio'] . '</td>';
                    echo '<td>' . $detalle['hora_fin'] . '</td>';
                    echo '<td>' . $detalle['intervalo_minutos'] . ' min</td>';
                    echo '<td>' . $detalle['cupo_maximo'] . '</td>';
                    echo '<td>' . ($detalle['detalle_estado'] ? 'Activo' : 'Inactivo') . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table></div>';
            } else {
                echo '<div class="alert alert-danger"><strong>Error:</strong> No se encontraron detalles de agenda para el doctor ' . $doctorId . ' en el día ' . $diaSemanaTexto . '</div>';
                
                // Verificar si hay datos para ese día específicamente
                $stmtCheckDay = $conn->prepare("
                    SELECT COUNT(*) as total
                    FROM agendas_detalle 
                    WHERE dia_semana = :dia_semana
                ");
                $stmtCheckDay->bindParam(":dia_semana", $diaSemanaTexto, PDO::PARAM_STR);
                $stmtCheckDay->execute();
                $hayAgendas = $stmtCheckDay->fetchColumn();
                
                if ($hayAgendas > 0) {
                    echo '<div class="alert alert-warning"><strong>Nota:</strong> Hay ' . $hayAgendas . ' registros para el día ' . $diaSemanaTexto . ', pero ninguno asociado al doctor ' . $doctorId . '</div>';
                } else {
                    echo '<div class="alert alert-warning"><strong>Nota:</strong> No hay registros en la tabla para el día ' . $diaSemanaTexto . ' con ningún doctor</div>';
                    
                    // Mostrar qué días están disponibles
                    $stmtDias = $conn->prepare("
                        SELECT DISTINCT dia_semana, COUNT(*) as total
                        FROM agendas_detalle
                        GROUP BY dia_semana
                        ORDER BY dia_semana
                    ");
                    $stmtDias->execute();
                    $dias = $stmtDias->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<div class="alert alert-info"><strong>Días disponibles en la base de datos:</strong></div>';
                    echo '<ul>';
                    foreach ($dias as $dia) {
                        echo '<li>' . $dia['dia_semana'] . ': ' . $dia['total'] . ' registros</li>';
                    }
                    echo '</ul>';
                }
            }
            
            echo '</div></div></div></div>';
            
            // 4. Verificar reservas existentes para esa fecha
            echo '<div class="row mb-4"><div class="col-12"><div class="card">';
            echo '<div class="card-header bg-secondary text-white"><h3 class="card-title">4. Reservas existentes para la fecha ' . $fecha . '</h3></div>';
            echo '<div class="card-body">';
            
            // Verificar si existe la tabla de reservas
            $stmtCheckTable = $conn->prepare("SELECT to_regclass('public.servicios_reservas')");
            $stmtCheckTable->execute();
            $tablaReservasExiste = $stmtCheckTable->fetchColumn();
            
            if ($tablaReservasExiste) {
                $stmtReservas = $conn->prepare("
                    SELECT 
                        reserva_id, servicio_id, agenda_id, doctor_id, 
                        fecha_reserva, hora_inicio, hora_fin, reserva_estado
                    FROM 
                        servicios_reservas
                    WHERE 
                        doctor_id = :doctor_id
                        AND fecha_reserva = :fecha_reserva
                    ORDER BY 
                        hora_inicio ASC
                ");
                $stmtReservas->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                $stmtReservas->bindParam(":fecha_reserva", $fecha, PDO::PARAM_STR);
                $stmtReservas->execute();
                $reservas = $stmtReservas->fetchAll(PDO::FETCH_ASSOC);
                
                if ($reservas) {
                    echo '<div class="alert alert-success"><strong>Reservas encontradas:</strong> ' . count($reservas) . '</div>';
                    
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-bordered table-striped">';
                    echo '<thead><tr><th>Reserva ID</th><th>Servicio ID</th><th>Agenda ID</th><th>Doctor ID</th><th>Fecha</th><th>Hora Inicio</th><th>Hora Fin</th><th>Estado</th></tr></thead>';
                    echo '<tbody>';
                    
                    foreach ($reservas as $reserva) {
                        echo '<tr>';
                        echo '<td>' . $reserva['reserva_id'] . '</td>';
                        echo '<td>' . $reserva['servicio_id'] . '</td>';
                        echo '<td>' . $reserva['agenda_id'] . '</td>';
                        echo '<td>' . $reserva['doctor_id'] . '</td>';
                        echo '<td>' . $reserva['fecha_reserva'] . '</td>';
                        echo '<td>' . $reserva['hora_inicio'] . '</td>';
                        echo '<td>' . $reserva['hora_fin'] . '</td>';
                        echo '<td>' . $reserva['reserva_estado'] . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table></div>';
                } else {
                    echo '<div class="alert alert-info"><strong>Nota:</strong> No hay reservas para el doctor ' . $doctorId . ' en la fecha ' . $fecha . '</div>';
                }
            } else {
                echo '<div class="alert alert-warning"><strong>Nota:</strong> La tabla servicios_reservas no existe todavía</div>';
            }
            
            echo '</div></div></div></div>';
            
            // 5. Simular generación de slots
            echo '<div class="row mb-4"><div class="col-12"><div class="card">';
            echo '<div class="card-header bg-secondary text-white"><h3 class="card-title">5. Simulación de generación de slots</h3></div>';
            echo '<div class="card-body">';
            
            if (!empty($detalles)) {
                $duracionServicio = 30; // Valor por defecto
                
                echo '<div class="alert alert-info"><strong>Simulando generación de slots usando duración de servicio:</strong> ' . $duracionServicio . ' minutos</div>';
                
                $slotsGenerados = [];
                
                foreach ($detalles as $horario) {
                    $horaInicio = new DateTime($horario['hora_inicio']);
                    $horaFin = new DateTime($horario['hora_fin']);
                    $intervaloMinutos = $horario['intervalo_minutos'] ?? 30;
                    
                    echo '<h5>Horario: ' . $horario['hora_inicio'] . ' - ' . $horario['hora_fin'] . ' (Intervalo: ' . $intervaloMinutos . ' min)</h5>';
                    
                    $intervaloEfectivo = max($intervaloMinutos, $duracionServicio);
                    $horaActual = clone $horaInicio;
                    
                    $slotsHorario = [];
                    
                    while ($horaActual < $horaFin) {
                        $slotInicio = clone $horaActual;
                        $slotFin = clone $horaActual;
                        $slotFin->add(new DateInterval('PT' . $duracionServicio . 'M'));
                        
                        if ($slotFin <= $horaFin) {
                            $slotsHorario[] = [
                                'hora_inicio' => $slotInicio->format('H:i:s'),
                                'hora_fin' => $slotFin->format('H:i:s'),
                            ];
                        }
                        
                        $horaActual = clone $slotInicio;
                        $horaActual->add(new DateInterval('PT' . $intervaloEfectivo . 'M'));
                    }
                    
                    $slotsGenerados = array_merge($slotsGenerados, $slotsHorario);
                    
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-bordered table-sm">';
                    echo '<thead><tr><th>Hora Inicio</th><th>Hora Fin</th></tr></thead>';
                    echo '<tbody>';
                    
                    foreach ($slotsHorario as $slot) {
                        echo '<tr>';
                        echo '<td>' . $slot['hora_inicio'] . '</td>';
                        echo '<td>' . $slot['hora_fin'] . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table></div>';
                }
                
                echo '<div class="alert alert-success"><strong>Total de slots generados:</strong> ' . count($slotsGenerados) . '</div>';
            } else {
                echo '<div class="alert alert-danger"><strong>No se pueden generar slots:</strong> No hay horarios disponibles</div>';
            }
            
            echo '</div></div></div></div>';
            
        } catch (PDOException $e) {
            echo '<div class="alert alert-danger"><strong>Error de base de datos:</strong> ' . $e->getMessage() . '</div>';
        } catch (Exception $e) {
            echo '<div class="alert alert-danger"><strong>Error general:</strong> ' . $e->getMessage() . '</div>';
        }
        ?>
        
    </div>
    
    <script src="view/plugins/jquery/jquery.min.js"></script>
    <script src="view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
