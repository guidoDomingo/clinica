<?php
/**
 * Reparador de visualización de reservas
 * Este script ayuda a diagnosticar y reparar problemas con la visualización de reservas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Encabezado HTML
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Depurador de Reservas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h1>Depurador de Visualización de Reservas</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Seleccionar fecha para verificar reservas</h5>
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="doctorId">ID del Doctor (opcional):</label>
                    <input type="number" class="form-control" id="doctorId" name="doctorId" value="<?php echo isset($_GET['doctor_id']) ? $_GET['doctor_id'] : ''; ?>">
                </div>
                
                <button id="btnVerificar" class="btn btn-primary">Verificar Reservas</button>
                <button id="btnSimularAjax" class="btn btn-secondary">Simular AJAX</button>
            </div>
        </div>
        
        <div id="resultados" class="mb-4">
            <!-- Aquí se mostrarán los resultados -->
            <div class="spinner-border text-primary d-none" role="status" id="spinner">
                <span class="sr-only">Cargando...</span>
            </div>
        </div>
        
        <?php
        // Cargar los archivos del sistema solo si se ha solicitado verificar
        if (isset($_GET['verificar'])) {
            require_once 'config/config.php';
            require_once 'model/conexion.php';
            require_once 'model/servicios.model.php';
            require_once 'controller/servicios.controller.php';
            
            $fecha = $_GET['fecha'] ?? date('Y-m-d');
            $doctorId = !empty($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;
            
            echo '<div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0">Resultados de Verificación</h5>
                </div>
                <div class="card-body">';
            
            // Verificar la conexión a la base de datos
            try {
                $conn = Conexion::conectar();
                echo '<div class="alert alert-success">Conexión a base de datos exitosa</div>';
                
                // Verificar si la tabla existe
                $stmtCheck = $conn->prepare("SELECT to_regclass('public.servicios_reservas')");
                $stmtCheck->execute();
                $tablaReservasExiste = $stmtCheck->fetchColumn();
                
                if (!$tablaReservasExiste) {
                    echo '<div class="alert alert-danger">ERROR: La tabla servicios_reservas no existe</div>';
                } else {
                    echo '<div class="alert alert-success">La tabla servicios_reservas existe</div>';
                    
                    // Consulta directa a la base de datos
                    $sql = "SELECT * FROM servicios_reservas WHERE fecha_reserva = :fecha";
                    if ($doctorId !== null) {
                        $sql .= " AND doctor_id = :doctor_id";
                    }
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
                    if ($doctorId !== null) {
                        $stmt->bindParam(':doctor_id', $doctorId, PDO::PARAM_INT);
                    }
                    
                    $stmt->execute();
                    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<div class="alert alert-info">Se encontraron ' . count($reservas) . ' reservas mediante consulta directa</div>';
                    
                    if (count($reservas) > 0) {
                        echo '<h5>Campos disponibles en la tabla:</h5>';
                        echo '<ul>';
                        foreach (array_keys($reservas[0]) as $campo) {
                            echo '<li>' . htmlspecialchars($campo) . '</li>';
                        }
                        echo '</ul>';
                        
                        echo '<h5>Reservas encontradas en la base de datos:</h5>';
                        echo '<table class="table table-striped table-bordered">';
                        echo '<thead><tr>';
                        echo '<th>ID</th><th>Doctor ID</th><th>Fecha</th><th>Hora Inicio</th><th>Hora Fin</th><th>Estado</th>';
                        echo '</tr></thead>';
                        echo '<tbody>';
                        
                        foreach ($reservas as $r) {
                            echo '<tr>';
                            echo '<td>' . $r['reserva_id'] . '</td>';
                            echo '<td>' . $r['doctor_id'] . '</td>';
                            echo '<td>' . $r['fecha_reserva'] . '</td>';
                            echo '<td>' . $r['hora_inicio'] . '</td>';
                            echo '<td>' . $r['hora_fin'] . '</td>';
                            echo '<td>' . ($r['reserva_estado'] ?? 'N/A') . '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody></table>';
                    }
                    
                    // Probar el controlador
                    $reservasControlador = ControladorServicios::ctrObtenerReservasPorFecha($fecha, $doctorId);
                    echo '<div class="alert alert-info">Se encontraron ' . count($reservasControlador) . ' reservas a través del controlador</div>';
                    
                    if (count($reservasControlador) > 0) {
                        echo '<h5>Campos disponibles en la respuesta del controlador:</h5>';
                        echo '<ul>';
                        foreach (array_keys($reservasControlador[0]) as $campo) {
                            echo '<li>' . htmlspecialchars($campo) . '</li>';
                        }
                        echo '</ul>';
                        
                        echo '<h5>Reservas devueltas por el controlador:</h5>';
                        echo '<table class="table table-striped table-bordered">';
                        echo '<thead><tr>';
                        foreach (array_keys($reservasControlador[0]) as $campo) {
                            echo '<th>' . htmlspecialchars($campo) . '</th>';
                        }
                        echo '</tr></thead>';
                        echo '<tbody>';
                        
                        foreach ($reservasControlador as $r) {
                            echo '<tr>';
                            foreach ($r as $value) {
                                echo '<td>' . htmlspecialchars($value ?? 'NULL') . '</td>';
                            }
                            echo '</tr>';
                        }
                        
                        echo '</tbody></table>';
                    }
                }
                
            } catch (PDOException $e) {
                echo '<div class="alert alert-danger">ERROR de base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            
            echo '</div></div>';
        }
        ?>
        
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="m-0">Probar visualización de reservas</h5>
            </div>
            <div class="card-body">
                <h5>Simulación de la tabla de reservas</h5>
                <table id="tablaReservasExistentes" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Horario</th>
                            <th>Doctor</th>
                            <th>Paciente</th>
                            <th>Servicio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center">Seleccione una fecha y haga clic en "Verificar Reservas"</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            $("#btnVerificar").click(function() {
                const fecha = $("#fecha").val();
                const doctorId = $("#doctorId").val();
                
                // Redireccionar con los parámetros
                window.location.href = `?verificar=1&fecha=${fecha}${doctorId ? '&doctor_id=' + doctorId : ''}`;
            });
            
            $("#btnSimularAjax").click(function() {
                const fecha = $("#fecha").val();
                const doctorId = $("#doctorId").val();
                
                $("#spinner").removeClass("d-none");
                
                $.ajax({
                    url: "ajax/servicios.ajax.php",
                    method: "POST",
                    data: { 
                        action: "obtenerReservas",
                        fecha: fecha,
                        doctor_id: doctorId || null
                    },
                    dataType: "json",
                    success: function(respuesta) {
                        $("#spinner").addClass("d-none");
                        
                        // Mostrar respuesta cruda
                        $("#resultados").html(`
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="m-0">Respuesta AJAX</h5>
                                </div>
                                <div class="card-body">
                                    <pre>${JSON.stringify(respuesta, null, 2)}</pre>
                                </div>
                            </div>
                        `);
                        
                        // Poblar la tabla
                        if (respuesta.status === "success" && respuesta.data && respuesta.data.length > 0) {
                            let filas = '';
                            
                            respuesta.data.forEach(function(reserva) {
                                // Formatear hora
                                const horaInicio = reserva.hora_inicio ? reserva.hora_inicio.substring(0, 5) : '';
                                const horaFin = reserva.hora_fin ? reserva.hora_fin.substring(0, 5) : '';
                                
                                // Estado
                                let claseEstado = '';
                                const estado = (reserva.estado || reserva.reserva_estado || '').toUpperCase();
                                
                                switch (estado) {
                                    case 'PENDIENTE': claseEstado = 'badge-warning'; break;
                                    case 'CONFIRMADA': claseEstado = 'badge-success'; break;
                                    case 'CANCELADA': claseEstado = 'badge-danger'; break;
                                    case 'COMPLETADA': claseEstado = 'badge-info'; break;
                                    default: claseEstado = 'badge-secondary';
                                }
                                
                                const doctorNombre = reserva.doctor_nombre || reserva.nombre_doctor || 'Sin doctor';
                                const pacienteNombre = reserva.paciente_nombre || reserva.nombre_paciente || 'Sin paciente';
                                const servicioNombre = reserva.servicio_nombre || 'Sin servicio';
                                
                                filas += `
                                    <tr>
                                        <td>${horaInicio} - ${horaFin}</td>
                                        <td>${doctorNombre}</td>
                                        <td>${pacienteNombre}</td>
                                        <td>${servicioNombre}</td>
                                        <td><span class="badge ${claseEstado}">${estado}</span></td>
                                    </tr>
                                `;
                            });
                            
                            $('#tablaReservasExistentes tbody').html(filas);
                        } else {
                            $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center">No hay reservas para esta fecha</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $("#spinner").addClass("d-none");
                        $("#resultados").html(`
                            <div class="alert alert-danger">
                                Error en la solicitud AJAX: ${error}
                            </div>
                        `);
                        $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar reservas</td></tr>');
                    }
                });
            });
        });
    </script>
</body>
</html>
