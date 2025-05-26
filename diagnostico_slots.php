<?php
/**
 * Script de diagnóstico para verificar problemas con la generación de slots
 */

// Configuración de visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir archivos necesarios
require_once "model/conexion.php";

// Parámetros de diagnóstico
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 14;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Determinar el día de la semana para la fecha
$fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
$diaSemanaNum = (int)$fechaObj->format('N'); // 1 (lunes) a 7 (domingo) según ISO-8601
$diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
$diaSemanaTexto = $diasSemanaTexto[$diaSemanaNum];

// Función auxiliar para ejecutar consultas y mostrar resultados
function ejecutarConsulta($query, $params = [], $titulo = 'Resultados') {
    try {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare($query);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>{$titulo}</h3>";
        
        if (empty($resultado)) {
            echo "<div class='alert alert-warning'>No se encontraron resultados</div>";
        } else {
            echo "<div class='table-responsive'>";
            echo "<table class='table table-bordered table-striped'>";
            
            // Encabezados de tabla
            echo "<thead><tr>";
            foreach (array_keys($resultado[0]) as $columna) {
                echo "<th>{$columna}</th>";
            }
            echo "</tr></thead>";
            
            // Datos
            echo "<tbody>";
            foreach ($resultado as $fila) {
                echo "<tr>";
                foreach ($fila as $valor) {
                    echo "<td>" . (is_null($valor) ? "<em>NULL</em>" : htmlspecialchars($valor)) . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody>";
            
            echo "</table>";
            echo "</div>";
        }
        
        return $resultado;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        return [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Generación de Slots</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        h2 { margin-bottom: 20px; }
        pre { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h1>Diagnóstico de Generación de Slots</h1>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <form method="GET" class="form-inline">
                    <div class="form-group mr-2">
                        <label for="doctor_id" class="mr-2">Doctor ID:</label>
                        <input type="number" class="form-control" id="doctor_id" name="doctor_id" value="<?php echo $doctorId; ?>">
                    </div>
                    <div class="form-group mr-2">
                        <label for="fecha" class="mr-2">Fecha:</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Consultar</button>
                </form>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>Información de consulta:</strong><br>
                    Doctor ID: <?php echo $doctorId; ?><br>
                    Fecha: <?php echo $fecha; ?><br>
                    Día de la semana: <?php echo $diaSemanaTexto; ?> (<?php echo $diaSemanaNum; ?>)
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 section">
                <h2>1. Información del Doctor</h2>
                <?php
                $queryDoctor = "
                    SELECT 
                        rd.doctor_id,
                        rd.person_id,
                        p.first_name || ' ' || p.last_name AS nombre_completo,
                        rd.especialidad,
                        rd.doctor_estado
                    FROM 
                        rh_doctors rd
                    INNER JOIN 
                        rh_person p ON rd.person_id = p.person_id
                    WHERE 
                        rd.doctor_id = :doctor_id
                ";
                $doctorInfo = ejecutarConsulta($queryDoctor, [':doctor_id' => $doctorId], 'Información del Doctor');
                ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 section">
                <h2>2. Agendas Cabecera</h2>
                <?php                $queryAgendasCabecera = "
                    SELECT 
                        agenda_id,
                        medico_id, 
                        agenda_descripcion,
                        fecha_inicio,
                        fecha_fin,
                        agenda_estado
                    FROM 
                        agendas_cabecera
                    WHERE 
                        medico_id = :doctor_id
                        AND (:fecha BETWEEN fecha_inicio AND fecha_fin OR fecha_fin IS NULL)
                ";
                $agendasCabecera = ejecutarConsulta($queryAgendasCabecera, [':doctor_id' => $doctorId, ':fecha' => $fecha], 'Cabeceras de Agenda Activas');
                ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 section">
                <h2>3. Detalles de Agenda para <?php echo $diaSemanaTexto; ?></h2>
                <?php
                if (!empty($agendasCabecera)) {
                    $agendaIds = array_column($agendasCabecera, 'agenda_id');
                    $placeholders = implode(',', array_fill(0, count($agendaIds), '?'));
                    
                    $queryDetalles = "
                        SELECT 
                            ad.detalle_id,
                            ad.agenda_id,
                            ad.dia_semana,
                            ad.hora_inicio,
                            ad.hora_fin,
                            ad.intervalo_minutos,
                            ad.turno_id,
                            t.turno_nombre,
                            ad.sala_id,
                            s.sala_nombre,
                            ad.cupo_maximo,
                            ad.detalle_estado
                        FROM 
                            agendas_detalle ad
                        INNER JOIN 
                            turnos t ON ad.turno_id = t.turno_id
                        INNER JOIN 
                            salas s ON ad.sala_id = s.sala_id
                        WHERE 
                            ad.agenda_id IN ({$placeholders})
                            AND ad.dia_semana = :dia_semana
                            AND ad.detalle_estado = true
                        ORDER BY 
                            ad.hora_inicio ASC
                    ";
                    
                    $params = array_merge($agendaIds, [':dia_semana' => $diaSemanaTexto]);
                    $detallesAgenda = ejecutarConsulta($queryDetalles, $params, 'Detalles de Agenda para ' . $diaSemanaTexto);
                } else {
                    echo "<div class='alert alert-warning'>No hay agendas activas para mostrar detalles</div>";
                }
                ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 section">
                <h2>4. Reservas Existentes para la Fecha <?php echo $fecha; ?></h2>
                <?php
                $queryReservas = "
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
                        AND fecha_reserva = :fecha
                    ORDER BY 
                        hora_inicio ASC
                ";
                
                $reservasExistentes = ejecutarConsulta($queryReservas, [':doctor_id' => $doctorId, ':fecha' => $fecha], 'Reservas Existentes');
                ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 section">
                <h2>5. Verificación del formato del día de la semana</h2>
                <?php
                $queryDiasSemana = "
                    SELECT DISTINCT dia_semana 
                    FROM agendas_detalle 
                    ORDER BY dia_semana
                ";
                
                $diasSemana = ejecutarConsulta($queryDiasSemana, [], 'Días de Semana en Base de Datos');
                
                echo "<div class='alert alert-info mt-3'>";
                echo "<strong>Día consultado:</strong> {$diaSemanaTexto}<br>";
                echo "<strong>Días disponibles en la base de datos:</strong> " . implode(", ", array_column($diasSemana, 'dia_semana'));
                echo "</div>";
                ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 section">
                <h2>6. Consulta SQL del Problema</h2>
                <pre>
SELECT 
    ad.detalle_id AS horario_id,
    ac.agenda_id,
    ad.turno_id,
    t.turno_nombre,
    ad.sala_id,
    s.sala_nombre,
    ac.medico_id AS doctor_id,
    COALESCE(p.first_name, '') || ' ' || COALESCE(p.last_name, '') AS nombre_doctor,
    ad.dia_semana,
    ad.hora_inicio,
    ad.hora_fin,
    ad.intervalo_minutos,
    ad.cupo_maximo
FROM 
    agendas_detalle ad
INNER JOIN 
    agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
INNER JOIN 
    turnos t ON ad.turno_id = t.turno_id
INNER JOIN 
    salas s ON ad.sala_id = s.sala_id
INNER JOIN
    rh_doctors rd ON rd.doctor_id = ac.medico_id 
INNER JOIN 
    rh_person p ON p.person_id = rd.person_id
WHERE 
    ac.medico_id = <?php echo $doctorId; ?>
    AND ad.dia_semana = '<?php echo $diaSemanaTexto; ?>'
    AND ad.detalle_estado = true
    AND ac.agenda_estado = true
ORDER BY 
    ad.hora_inicio ASC
                </pre>
                <?php
                $queryDiagnostico = "
                    SELECT 
                        ad.detalle_id AS horario_id,
                        ac.agenda_id,
                        ad.turno_id,
                        t.turno_nombre,
                        ad.sala_id,
                        s.sala_nombre,
                        ac.medico_id AS doctor_id,
                        COALESCE(p.first_name, '') || ' ' || COALESCE(p.last_name, '') AS nombre_doctor,
                        ad.dia_semana,
                        ad.hora_inicio,
                        ad.hora_fin,
                        ad.intervalo_minutos,
                        ad.cupo_maximo
                    FROM 
                        agendas_detalle ad
                    INNER JOIN 
                        agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                    INNER JOIN 
                        turnos t ON ad.turno_id = t.turno_id
                    INNER JOIN 
                        salas s ON ad.sala_id = s.sala_id
                    INNER JOIN
                        rh_doctors rd ON rd.doctor_id = ac.medico_id 
                    INNER JOIN 
                        rh_person p ON p.person_id = rd.person_id
                    WHERE 
                        ac.medico_id = :doctor_id
                        AND ad.dia_semana = :dia_semana
                        AND ad.detalle_estado = true
                        AND ac.agenda_estado = true
                    ORDER BY 
                        ad.hora_inicio ASC
                ";
                
                ejecutarConsulta($queryDiagnostico, [':doctor_id' => $doctorId, ':dia_semana' => $diaSemanaTexto], 'Resultados de la Consulta');
                ?>
            </div>
            
            <div class="col-md-6 section">
                <h2>7. Verificación de Fechas de Vigencia</h2>
                <p>Verifica si la fecha seleccionada está dentro del rango de vigencia de alguna agenda.</p>
                <?php                $queryFechasVigencia = "
                    SELECT 
                        agenda_id,
                        medico_id,
                        agenda_descripcion,
                        fecha_inicio,
                        fecha_fin,
                        :fecha AS fecha_seleccionada,
                        CASE 
                            WHEN :fecha BETWEEN fecha_inicio AND COALESCE(fecha_fin, '9999-12-31') 
                            THEN 'SÍ' 
                            ELSE 'NO' 
                        END AS esta_vigente
                    FROM 
                        agendas_cabecera
                    WHERE 
                        medico_id = :doctor_id
                ";
                
                ejecutarConsulta($queryFechasVigencia, [':doctor_id' => $doctorId, ':fecha' => $fecha], 'Vigencia de Agendas');
                ?>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
