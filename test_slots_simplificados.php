<?php
/**
 * Script para probar la generación simplificada de slots disponibles
 * enfocado solo en crear slots por intervalos
 */

// Configurar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir los archivos necesarios
require_once "model/conexion.php";
require_once "model/servicios_slots_simplificado.model.php";

// Parámetros de prueba
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 14;
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d', strtotime('+1 day')); // Por defecto mañana
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Slots Simplificados</title>
    <link rel="stylesheet" href="view/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="view/dist/css/adminlte.min.css">
    <style>
        .card-slots {
            max-height: 500px;
            overflow-y: auto;
        }
        .slot-item {
            margin-bottom: 5px;
            padding: 10px;
            border-radius: 5px;
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
        }
        .slot-time {
            font-weight: bold;
            color: #007bff;
        }
        .doctor-info {
            margin-top: 20px;
        }
    </style>
</head>
<body class="hold-transition sidebar-collapse layout-top-nav">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
            <div class="container">
                <a href="index.php" class="navbar-brand">
                    <img src="view/dist/img/AdminLTELogo.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                    <span class="brand-text font-weight-light">Test Slots Simplificados</span>
                </a>
            </div>
        </nav>

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Generación de Slots Simplificada</h1>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Parámetros de prueba</h3>
                                </div>
                                <div class="card-body">
                                    <form method="GET" class="form-inline">
                                        <div class="form-group mb-2 mr-2">
                                            <label for="doctor_id" class="mr-2">Doctor ID:</label>
                                            <input type="number" class="form-control" id="doctor_id" name="doctor_id" value="<?php echo $doctorId; ?>">
                                        </div>
                                        <div class="form-group mb-2 mr-2">
                                            <label for="fecha" class="mr-2">Fecha:</label>
                                            <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>">
                                        </div>
                                        <button type="submit" class="btn btn-primary mb-2">Generar Slots</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Información de solicitud</h3>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-4">Doctor ID:</dt>
                                        <dd class="col-sm-8"><?php echo $doctorId; ?></dd>
                                        <dt class="col-sm-4">Fecha:</dt>
                                        <dd class="col-sm-8"><?php echo $fecha; ?></dd>
                                        <dt class="col-sm-4">Día de la semana:</dt>
                                        <dd class="col-sm-8"><?php 
                                            $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
                                            $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                                            echo $diasSemana[$fechaObj->format('w')]; 
                                        ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Información del Doctor</h3>
                                </div>
                                <div class="card-body">
                                    <?php
                                    try {
                                        $stmt = Conexion::conectar()->prepare(
                                            "SELECT 
                                                d.doctor_id,
                                                p.first_name || ' ' || p.last_name AS nombre_completo,
                                                d.especialidad
                                            FROM 
                                                rh_doctors d
                                            INNER JOIN 
                                                rh_person p ON d.person_id = p.person_id
                                            WHERE 
                                                d.doctor_id = :doctor_id"
                                        );
                                        $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                                        $stmt->execute();
                                        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

                                        if ($doctor) {
                                            echo "<dl class='row'>";
                                            echo "<dt class='col-sm-4'>Doctor:</dt>";
                                            echo "<dd class='col-sm-8'>" . $doctor['nombre_completo'] . "</dd>";
                                            echo "<dt class='col-sm-4'>Especialidad:</dt>";
                                            echo "<dd class='col-sm-8'>" . $doctor['especialidad'] . "</dd>";
                                            echo "</dl>";
                                        } else {
                                            echo "<div class='alert alert-warning'>No se encontró información del doctor</div>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>Error al obtener información del doctor: " . $e->getMessage() . "</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Slots Disponibles</h3>
                                </div>
                                <div class="card-body card-slots">
                                    <?php
                                    $tiempoInicio = microtime(true);
                                    $slots = ModelServiciosSimplificado::mdlGenerarSlotsSimple($doctorId, $fecha);
                                    $tiempoFin = microtime(true);
                                    $tiempoEjecucion = round(($tiempoFin - $tiempoInicio) * 1000, 2); // en milisegundos

                                    echo "<p>Tiempo de ejecución: {$tiempoEjecucion} ms</p>";
                                    echo "<p>Total de slots generados: " . count($slots) . "</p>";

                                    if (count($slots) > 0) {
                                        echo "<div class='row'>";
                                        foreach ($slots as $slot) {
                                            echo "<div class='col-md-3 mb-2'>";
                                            echo "<div class='slot-item'>";
                                            echo "<div class='slot-time'>" . substr($slot['hora_inicio'], 0, 5) . " - " . substr($slot['hora_fin'], 0, 5) . "</div>";
                                            echo "<div class='slot-info'>";
                                            echo "<small>Sala: " . $slot['sala_nombre'] . "</small><br>";
                                            echo "<small>Turno: " . $slot['turno_nombre'] . "</small>";
                                            echo "</div>";
                                            echo "</div>";
                                            echo "</div>";
                                        }
                                        echo "</div>";
                                    } else {
                                        echo "<div class='alert alert-info'>No hay slots disponibles para esta fecha y doctor</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Horarios Base encontrados</h3>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
                                    $diaSemanaNum = (int)$fechaObj->format('N');
                                    $diasSemanaTexto = [1 => 'LUNES', 2 => 'MARTES', 3 => 'MIERCOLES', 4 => 'JUEVES', 5 => 'VIERNES', 6 => 'SABADO', 7 => 'DOMINGO'];
                                    $diaSemana = $diasSemanaTexto[$diaSemanaNum];

                                    try {
                                        $stmt = Conexion::conectar()->prepare(
                                            "SELECT 
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
                                                ad.cupo_maximo
                                            FROM 
                                                agendas_detalle ad
                                            INNER JOIN 
                                                agendas_cabecera ac ON ad.agenda_id = ac.agenda_id
                                            INNER JOIN 
                                                turnos t ON ad.turno_id = t.turno_id
                                            INNER JOIN 
                                                salas s ON ad.sala_id = s.sala_id
                                            WHERE 
                                                ac.medico_id = :doctor_id
                                                AND ad.dia_semana = :dia_semana
                                                AND ad.detalle_estado = true
                                                AND ac.agenda_estado = true
                                            ORDER BY 
                                                ad.hora_inicio ASC"
                                        );
                                        $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                                        $stmt->bindParam(":dia_semana", $diaSemana, PDO::PARAM_STR);
                                        $stmt->execute();
                                        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        if (count($horarios) > 0) {
                                            echo "<div class='table-responsive'>";
                                            echo "<table class='table table-bordered table-striped'>";
                                            echo "<thead>";
                                            echo "<tr>";
                                            echo "<th>ID</th>";
                                            echo "<th>Día</th>";
                                            echo "<th>Hora Inicio</th>";
                                            echo "<th>Hora Fin</th>";
                                            echo "<th>Intervalo (min)</th>";
                                            echo "<th>Turno</th>";
                                            echo "<th>Sala</th>";
                                            echo "</tr>";
                                            echo "</thead>";
                                            echo "<tbody>";
                                            foreach ($horarios as $horario) {
                                                echo "<tr>";
                                                echo "<td>" . $horario['detalle_id'] . "</td>";
                                                echo "<td>" . $horario['dia_semana'] . "</td>";
                                                echo "<td>" . substr($horario['hora_inicio'], 0, 5) . "</td>";
                                                echo "<td>" . substr($horario['hora_fin'], 0, 5) . "</td>";
                                                echo "<td>" . $horario['intervalo_minutos'] . "</td>";
                                                echo "<td>" . $horario['turno_nombre'] . "</td>";
                                                echo "<td>" . $horario['sala_nombre'] . "</td>";
                                                echo "</tr>";
                                            }
                                            echo "</tbody>";
                                            echo "</table>";
                                            echo "</div>";
                                        } else {
                                            echo "<div class='alert alert-warning'>No se encontraron horarios base para este doctor en el día {$diaSemana}</div>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>Error al obtener horarios base: " . $e->getMessage() . "</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                v1.0
            </div>
            <strong>Test de slots simplificados</strong>
        </footer>
    </div>

    <script src="view/plugins/jquery/jquery.min.js"></script>
    <script src="view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="view/dist/js/adminlte.min.js"></script>
</body>
</html>
