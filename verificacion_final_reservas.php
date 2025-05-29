<?php
/**
 * Script para verificar el sistema completo de reservas
 */

// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "model/conexion.php";
require_once "model/servicios.model.php";
require_once "controller/servicios.controller.php";

// Función para registrar mensajes
function log_message($message) {
    echo date('H:i:s') . " - $message<br>\n";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Final del Sistema de Reservas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        h1, h2, h3 {
            color: #005;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .warning {
            color: orange;
            font-weight: bold;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            overflow: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
        }
        .column {
            flex: 1;
            min-width: 300px;
            padding: 10px;
        }
        .box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .nav {
            margin-bottom: 20px;
        }
        .nav a {
            display: inline-block;
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
    </style>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Verificación Final del Sistema de Reservas</h1>

    <div class="nav">
        <a href="listar_todas_reservas.php">Ver todas las reservas</a>
        <a href="debug_js_reservas.php">Depurador de JS</a>
        <a href="reparar_sistema_reservas.php">Reparar sistema</a>
        <a href="crear_reserva_hoy.php">Crear reserva HOY</a>
        <a href="view/modules/servicios.php">Ver interfaz de servicios</a>
    </div>

    <?php
    // Fecha actual para las pruebas
    $fecha = date('Y-m-d');
    $doctorId = null; // Para obtener todas las reservas sin filtro de médico
    
    log_message("Iniciando verificación del sistema para la fecha: $fecha");
    ?>

    <div class="container">
        <div class="column">
            <div class="box">
                <h2>1. Verificación de la Base de Datos</h2>
                <?php
                try {
                    $db = Conexion::conectar();
                    if ($db instanceof PDO) {
                        log_message("<span class='success'>✓ Conexión a la base de datos correcta</span>");
                        
                        // Verificar la tabla de reservas
                        $stmt = $db->prepare("SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'servicios_reservas')");
                        $stmt->execute();
                        $existeTabla = $stmt->fetchColumn();
                        
                        if ($existeTabla) {
                            log_message("<span class='success'>✓ Tabla servicios_reservas existe</span>");
                            
                            // Contar reservas para hoy
                            $stmt = $db->prepare("SELECT COUNT(*) FROM servicios_reservas WHERE fecha_reserva = :fecha");
                            $stmt->bindParam(":fecha", $fecha);
                            $stmt->execute();
                            $conteo = $stmt->fetchColumn();
                            
                            log_message("Para la fecha $fecha hay $conteo reservas");
                        } else {
                            log_message("<span class='error'>✗ Tabla servicios_reservas no existe</span>");
                        }
                    } else {
                        log_message("<span class='error'>✗ Error de conexión a la base de datos</span>");
                    }
                } catch (Exception $e) {
                    log_message("<span class='error'>✗ Error: " . $e->getMessage() . "</span>");
                }
                ?>
            </div>
            
            <div class="box">
                <h2>2. Verificación del Modelo</h2>
                <?php
                try {
                    log_message("Consultando reservas mediante el modelo...");
                    $reservas = ModelServicios::mdlObtenerReservasPorFecha($fecha, $doctorId);
                    
                    if (is_array($reservas)) {
                        log_message("<span class='success'>✓ El modelo devuelve un array</span>");
                        log_message("Número de reservas obtenidas: " . count($reservas));
                        
                        if (count($reservas) > 0) {
                            // Mostrar la primera reserva como ejemplo
                            log_message("Datos de la primera reserva:");
                            echo "<pre>";
                            print_r($reservas[0]);
                            echo "</pre>";
                            
                            // Verificar campos críticos
                            $camposEsperados = ['doctor_nombre', 'paciente_nombre', 'servicio_nombre'];
                            $faltantes = [];
                            
                            foreach ($camposEsperados as $campo) {
                                if (!isset($reservas[0][$campo])) {
                                    $faltantes[] = $campo;
                                }
                            }
                            
                            if (empty($faltantes)) {
                                log_message("<span class='success'>✓ Todos los campos necesarios están presentes</span>");
                            } else {
                                log_message("<span class='warning'>⚠ Faltan algunos campos: " . implode(', ', $faltantes) . "</span>");
                            }
                        } else {
                            log_message("<span class='warning'>⚠ No hay reservas para la fecha $fecha</span>");
                        }
                    } else {
                        log_message("<span class='error'>✗ El modelo no devuelve un array</span>");
                    }
                } catch (Exception $e) {
                    log_message("<span class='error'>✗ Error en el modelo: " . $e->getMessage() . "</span>");
                }
                ?>
            </div>
        </div>
        
        <div class="column">
            <div class="box">
                <h2>3. Verificación del Controlador</h2>
                <?php
                try {
                    log_message("Consultando reservas mediante el controlador...");
                    $reservasControlador = ControladorServicios::ctrObtenerReservasPorFecha($fecha, $doctorId);
                    
                    if (is_array($reservasControlador)) {
                        log_message("<span class='success'>✓ El controlador devuelve un array</span>");
                        log_message("Número de reservas obtenidas: " . count($reservasControlador));
                        
                        if (count($reservasControlador) > 0) {
                            // Solo verificar que devuelve datos
                            log_message("<span class='success'>✓ El controlador devuelve datos</span>");
                        } else {
                            log_message("<span class='warning'>⚠ No hay reservas para la fecha $fecha</span>");
                        }
                    } else {
                        log_message("<span class='error'>✗ El controlador no devuelve un array</span>");
                    }
                } catch (Exception $e) {
                    log_message("<span class='error'>✗ Error en el controlador: " . $e->getMessage() . "</span>");
                }
                ?>
            </div>
            
            <div class="box">
                <h2>4. Simulación de AJAX</h2>
                <?php
                log_message("Simulando llamada AJAX...");
                ?>
                
                <div id="ajaxResults">Cargando...</div>
                
                <script>
                    $(document).ready(function() {
                        console.log("Iniciando prueba de AJAX");
                        
                        $.ajax({
                            url: "ajax/servicios.ajax.php",
                            method: "POST",
                            data: { 
                                action: "obtenerReservas",
                                fecha: "<?php echo $fecha; ?>"
                            },
                            dataType: "json",
                            success: function(respuesta) {
                                console.log("Respuesta de AJAX:", respuesta);
                                
                                let html = "<h3>Resultados AJAX</h3>";
                                
                                if (respuesta.status === "success") {
                                    html += "<p class='success'>✓ La respuesta AJAX tiene estado success</p>";
                                    
                                    if (respuesta.data && respuesta.data.length > 0) {
                                        html += "<p>Número de reservas: " + respuesta.data.length + "</p>";
                                        
                                        html += "<h4>Campos presentes en la primera reserva:</h4>";
                                        html += "<ul>";
                                        
                                        const firstReserva = respuesta.data[0];
                                        for (const key in firstReserva) {
                                            html += "<li>" + key + ": " + (firstReserva[key] || 'vacío') + "</li>";
                                        }
                                        
                                        html += "</ul>";
                                        
                                        // Verificar campos críticos
                                        const camposEsperados = ['doctor_nombre', 'paciente_nombre', 'servicio_nombre', 'fecha_reserva', 'hora_inicio'];
                                        const faltantes = [];
                                        
                                        camposEsperados.forEach(campo => {
                                            if (firstReserva[campo] === undefined) {
                                                faltantes.push(campo);
                                            }
                                        });
                                        
                                        if (faltantes.length === 0) {
                                            html += "<p class='success'>✓ Todos los campos críticos están presentes</p>";
                                        } else {
                                            html += "<p class='warning'>⚠ Faltan algunos campos críticos: " + faltantes.join(', ') + "</p>";
                                        }
                                        
                                        // Mostrar una tabla con los datos
                                        html += "<h4>Todas las reservas:</h4>";
                                        html += "<table>";
                                        html += "<tr><th>Doctor</th><th>Paciente</th><th>Servicio</th><th>Hora</th></tr>";
                                        
                                        respuesta.data.forEach(reserva => {
                                            const doctorNombre = reserva.doctor_nombre || ('Doctor ' + reserva.doctor_id);
                                            const pacienteNombre = reserva.paciente_nombre || ('Paciente ' + reserva.paciente_id);
                                            const servicioNombre = reserva.servicio_nombre || ('Servicio ' + reserva.servicio_id);
                                            const hora = (reserva.hora_inicio ? reserva.hora_inicio.substring(0, 5) : '??:??') + 
                                                       ' - ' + 
                                                       (reserva.hora_fin ? reserva.hora_fin.substring(0, 5) : '??:??');
                                            
                                            html += "<tr>";
                                            html += "<td>" + doctorNombre + "</td>";
                                            html += "<td>" + pacienteNombre + "</td>";
                                            html += "<td>" + servicioNombre + "</td>";
                                            html += "<td>" + hora + "</td>";
                                            html += "</tr>";
                                        });
                                        
                                        html += "</table>";
                                    } else {
                                        html += "<p class='warning'>⚠ No hay reservas para la fecha seleccionada</p>";
                                    }
                                } else {
                                    html += "<p class='error'>✗ La respuesta AJAX no tiene estado success</p>";
                                    html += "<p>Estado: " + (respuesta.status || 'desconocido') + "</p>";
                                    html += "<p>Mensaje: " + (respuesta.message || 'No hay mensaje') + "</p>";
                                }
                                
                                $("#ajaxResults").html(html);
                            },
                            error: function(xhr, status, error) {
                                console.error("Error en AJAX:", xhr, status, error);
                                
                                $("#ajaxResults").html(
                                    "<h3>Error AJAX</h3>" +
                                    "<p class='error'>✗ Error: " + error + "</p>" +
                                    "<p>Estado: " + status + "</p>" +
                                    "<p>Detalles: Ver consola para más información</p>"
                                );
                            }
                        });
                    });
                </script>
            </div>
        </div>
    </div>
    
    <div class="box">
        <h2>5. Simulación de la Función cargarReservasDelDia</h2>
        <p>Esta sección prueba la función de JavaScript que se encarga de mostrar las reservas en la interfaz.</p>
        
        <h3>Tabla de Reservas</h3>
        <table id="tablaReservasExistentes">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Médico</th>
                    <th>Paciente</th>
                    <th>Servicio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="text-center">Cargando reservas...</td>
                </tr>
            </tbody>
        </table>
        
        <script>
            // Copiar la función exacta de servicios.js para probarla aquí
            function cargarReservasDelDia(fecha) {
                console.log("Cargando reservas para la fecha: " + fecha); // Debug log
                
                $.ajax({
                    url: "ajax/servicios.ajax.php",
                    method: "POST",
                    data: { 
                        action: "obtenerReservas",
                        fecha: fecha
                    },
                    dataType: "json",
                    beforeSend: function() {
                        $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center">Cargando reservas...</td></tr>');
                    },
                    success: function(respuesta) {
                        console.log("Respuesta de reservas:", respuesta); // Debug log
                        
                        if (respuesta.status === "success" && respuesta.data && respuesta.data.length > 0) {
                            let filas = '';
                            
                            respuesta.data.forEach(function(reserva) {
                                console.log("Procesando reserva:", reserva); // Debug log para cada reserva
                                
                                // Formatear la hora para mostrar (HH:MM)
                                const horaInicio = reserva.hora_inicio ? reserva.hora_inicio.substring(0, 5) : '';
                                const horaFin = reserva.hora_fin ? reserva.hora_fin.substring(0, 5) : '';
                                
                                // Determinar color según estado (usar el campo correcto estado_reserva)
                                let claseEstado = '';
                                const estado = (reserva.estado_reserva || reserva.estado || reserva.reserva_estado || 'PENDIENTE').toUpperCase();
                                
                                switch (estado) {
                                    case 'PENDIENTE': claseEstado = 'warning'; break;
                                    case 'CONFIRMADA': claseEstado = 'success'; break;
                                    case 'CANCELADA': claseEstado = 'error'; break;
                                    case 'COMPLETADA': claseEstado = 'success'; break;
                                    default: claseEstado = '';
                                }
                                
                                // Maneja diferentes nombres de propiedades para cada campo
                                // Obtener nombres con mejor manejo de posibles valores nulos o indefinidos
                                // Adaptado a la estructura real de la BD (usando rh_person y rs_servicios)
                                let doctorNombre = '';
                                if (reserva.doctor_nombre && reserva.doctor_nombre.trim() !== '' && !reserva.doctor_nombre.startsWith('Doctor ')) {
                                    doctorNombre = reserva.doctor_nombre;
                                } else if (reserva.nombre_doctor && reserva.nombre_doctor.trim() !== '') {
                                    doctorNombre = reserva.nombre_doctor;
                                } else {
                                    doctorNombre = 'Dr. ' + (reserva.doctor_id || '?');
                                }
                                
                                let pacienteNombre = '';
                                if (reserva.paciente_nombre && reserva.paciente_nombre.trim() !== '' && !reserva.paciente_nombre.startsWith('Paciente ')) {
                                    pacienteNombre = reserva.paciente_nombre;
                                } else if (reserva.nombre_paciente && reserva.nombre_paciente.trim() !== '') {
                                    pacienteNombre = reserva.nombre_paciente;
                                } else {
                                    pacienteNombre = 'Paciente ' + (reserva.paciente_id || '?');
                                }
                                
                                let servicioNombre = '';
                                if (reserva.servicio_nombre && reserva.servicio_nombre.trim() !== '' && !reserva.servicio_nombre.startsWith('Servicio ')) {
                                    servicioNombre = reserva.servicio_nombre;
                                } else if (reserva.nombre && reserva.nombre.trim() !== '') {
                                    // Campo nombre de rs_servicios
                                    servicioNombre = reserva.nombre;
                                } else {
                                    servicioNombre = 'Servicio ' + (reserva.servicio_id || '?');
                                }
                                
                                filas += `
                                    <tr>
                                        <td>${horaInicio} - ${horaFin}</td>
                                        <td>${doctorNombre}</td>
                                        <td>${pacienteNombre}</td>
                                        <td>${servicioNombre}</td>
                                        <td><span class="${claseEstado}">${estado}</span></td>
                                    </tr>
                                `;
                            });
                            
                            $('#tablaReservasExistentes tbody').html(filas);
                        } else {
                            console.log("No hay reservas o error en la respuesta:", respuesta);
                            $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center">No hay reservas para esta fecha</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al cargar reservas:", xhr, status, error);
                        $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center">Error al cargar reservas: ' + error + '</td></tr>');
                    }
                });
            }
            
            // Ejecutar la función cuando la página esté lista
            $(document).ready(function() {
                cargarReservasDelDia('<?php echo $fecha; ?>');
            });
        </script>
    </div>
    
    <div class="box">
        <h2>Conclusiones y Recomendaciones</h2>
        
        <p>Este script ha realizado las siguientes comprobaciones:</p>
        <ol>
            <li>Verificación de la conexión y estructura de la base de datos</li>
            <li>Prueba de la función del modelo para obtener reservas</li>
            <li>Prueba de la función del controlador</li>
            <li>Simulación de una llamada AJAX</li>
            <li>Prueba de la función JavaScript que muestra las reservas</li>
        </ol>
        
        <p>Si todas las comprobaciones han sido exitosas, el sistema debería estar funcionando correctamente. Si hay errores, revise el detalle de cada paso.</p>
        
        <h3>Próximos pasos:</h3>
        <ol>
            <li>Si es necesario, cree una reserva de prueba usando <a href="crear_reserva_hoy.php">crear_reserva_hoy.php</a></li>
            <li>Verifique la interfaz real en <a href="view/modules/servicios.php">servicios.php</a></li>
            <li>Si persisten los problemas, utilice el <a href="debug_js_reservas.php">depurador JS</a> para diagnósticos más avanzados</li>
            <li>Revise los logs en la carpeta logs/ para más detalles</li>
        </ol>
    </div>
</body>
</html>
