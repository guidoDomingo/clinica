<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Depurador de Reservas en JavaScript</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI para datepicker -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <style>
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            max-height: 400px;
            overflow-y: auto;
        }
        .json-key { color: #ff6b6b; }
        .json-string { color: #28a745; }
        .json-number { color: #17a2b8; }
        .json-boolean { color: #6f42c1; }
        .json-null { color: #6c757d; }
        .event-log {
            height: 200px;
            overflow-y: auto;
            background-color: #f8f9fa;
            padding: 10px;
            border: 1px solid #ddd;
            font-family: monospace;
        }
        .event-item {
            margin-bottom: 5px;
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .nav-tabs {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Depurador de Reservas en JavaScript</h1>
        
        <div class="alert alert-info">
            Esta herramienta permite probar y depurar el funcionamiento de las reservas en JavaScript.
            Se ejecutan diferentes pruebas sobre los datos y visualización de las reservas.
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="direct-tab" data-bs-toggle="tab" href="#direct" role="tab">Prueba Directa API</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="js-tab" data-bs-toggle="tab" href="#js-implementation" role="tab">Implementación JS</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="monitor-tab" data-bs-toggle="tab" href="#monitor" role="tab">Monitor de Eventos</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Prueba Directa API -->
                    <div class="tab-pane fade show active" id="direct" role="tabpanel">
                        <h3>Prueba Directa al API</h3>
                        <p>Esta prueba hace una llamada directa al endpoint de la API para obtener reservas.</p>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">Fecha</span>
                                    <input type="text" id="fechaApi" class="form-control datepicker" placeholder="YYYY-MM-DD" value="<?php echo date('Y-m-d'); ?>">
                                    <button class="btn btn-primary" id="probarApi">Probar API</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h5>Respuesta Raw:</h5>
                            <pre id="apiResponse" class="mb-3">Esperando respuesta...</pre>
                            
                            <h5>Datos procesados:</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="apiTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Doctor</th>
                                            <th>Paciente</th>
                                            <th>Servicio</th>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center">No hay datos</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Implementación JS -->
                    <div class="tab-pane fade" id="js-implementation" role="tabpanel">
                        <h3>Simulación de la implementación de JavaScript</h3>
                        <p>Esta sección simula el funcionamiento de la función cargarReservasDelDia del archivo servicios.js.</p>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">Fecha</span>
                                    <input type="text" id="fechaJs" class="form-control datepicker" placeholder="YYYY-MM-DD" value="<?php echo date('Y-m-d'); ?>">
                                    <button class="btn btn-primary" id="probarJs">Probar implementación JS</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h5>Tabla de reservas (como se ve en la interfaz):</h5>
                            <table class="table table-bordered" id="tablaReservasExistentes">
                                <thead class="table-dark">
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
                                        <td colspan="5" class="text-center">No hay reservas para esta fecha</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Monitor de Eventos -->
                    <div class="tab-pane fade" id="monitor" role="tabpanel">
                        <h3>Monitor de Eventos JavaScript</h3>
                        <p>Este monitor registra todos los eventos relacionados con las reservas para ayudar a depurar problemas.</p>
                        
                        <div class="mb-3">
                            <button class="btn btn-danger btn-sm" id="clearLog">Limpiar Log</button>
                            <button class="btn btn-secondary btn-sm" id="startMonitoring">Iniciar Monitoreo</button>
                        </div>
                        
                        <div class="event-log" id="eventLog">
                            <div class="event-item">Monitor de eventos inicializado.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Implementación de cargarReservasDelDia (Copia del original) -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Implementación actual de cargarReservasDelDia</h3>
            </div>
            <div class="card-body">
                <pre class="language-javascript">
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
            $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando reservas...</td></tr>');
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
                        case 'PENDIENTE': claseEstado = 'badge-warning'; break;
                        case 'CONFIRMADA': claseEstado = 'badge-success'; break;
                        case 'CANCELADA': claseEstado = 'badge-danger'; break;
                        case 'COMPLETADA': claseEstado = 'badge-info'; break;
                        default: claseEstado = 'badge-secondary';
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
                            <td><span class="badge ${claseEstado}">${estado}</span></td>
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
            $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar reservas: ' + error + '</td></tr>');
        }
    });
}
                </pre>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Funciones de utilidad
        function formatJSON(jsonObj) {
            let json = JSON.stringify(jsonObj, null, 2);
            // Colorear JSON
            json = json.replace(/"(\w+)"\s*:/g, '<span class="json-key">"$1"</span>:');
            json = json.replace(/"([^"]+)"/g, function(match) {
                if (match.includes('json-key')) return match;
                return '<span class="json-string">' + match + '</span>';
            });
            json = json.replace(/\b(\d+)(?![^<]*>)/g, '<span class="json-number">$1</span>');
            json = json.replace(/\b(true|false)\b(?![^<]*>)/g, '<span class="json-boolean">$1</span>');
            json = json.replace(/\bnull\b(?![^<]*>)/g, '<span class="json-null">null</span>');
            return json;
        }
        
        function logEvent(message, type = 'info') {
            const now = new Date();
            const timeStr = now.toLocaleTimeString() + '.' + now.getMilliseconds();
            const typeClass = type === 'error' ? 'text-danger' : type === 'warning' ? 'text-warning' : 'text-info';
            
            $('#eventLog').prepend(`
                <div class="event-item">
                    <span class="text-secondary">[${timeStr}]</span> <span class="${typeClass}">${message}</span>
                </div>
            `);
        }

        // Inicializar datepicker
        $(document).ready(function() {
            $('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true
            });
            
            // Probar API directamente
            $('#probarApi').on('click', function() {
                const fecha = $('#fechaApi').val();
                logEvent(`Probando API para fecha ${fecha}`);
                
                $('#apiResponse').html('<i class="fas fa-spinner fa-spin"></i> Cargando...');
                $('#apiTable tbody').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando datos...</td></tr>');
                
                $.ajax({
                    url: "ajax/servicios.ajax.php",
                    method: "POST",
                    data: { 
                        action: "obtenerReservas",
                        fecha: fecha
                    },
                    dataType: "json",
                    success: function(respuesta) {
                        logEvent(`API respondió con ${respuesta.data ? respuesta.data.length : 0} reserva(s)`);
                        $('#apiResponse').html(formatJSON(respuesta));
                        
                        if (respuesta.status === "success" && respuesta.data && respuesta.data.length > 0) {
                            let filas = '';
                            respuesta.data.forEach(function(reserva) {
                                filas += `
                                    <tr>
                                        <td>${reserva.reserva_id}</td>
                                        <td>${reserva.doctor_nombre || reserva.nombre_doctor || 'Dr. ' + (reserva.doctor_id || '?')}</td>
                                        <td>${reserva.paciente_nombre || reserva.nombre_paciente || 'Paciente ' + (reserva.paciente_id || '?')}</td>
                                        <td>${reserva.servicio_nombre || reserva.nombre || 'Servicio ' + (reserva.servicio_id || '?')}</td>
                                        <td>${reserva.fecha_reserva}</td>
                                        <td>${reserva.hora_inicio ? reserva.hora_inicio.substring(0, 5) : ''}</td>
                                        <td>${reserva.estado_reserva || reserva.estado || reserva.reserva_estado || 'PENDIENTE'}</td>
                                    </tr>
                                `;
                            });
                            $('#apiTable tbody').html(filas);
                        } else {
                            $('#apiTable tbody').html('<tr><td colspan="7" class="text-center">No hay reservas para esta fecha</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        logEvent(`Error en API: ${error}`, 'error');
                        $('#apiResponse').html('<span class="text-danger">Error: ' + error + '</span>');
                        $('#apiTable tbody').html('<tr><td colspan="7" class="text-center text-danger">Error al cargar datos: ' + error + '</td></tr>');
                    }
                });
            });
            
            // Probar implementación JS
            $('#probarJs').on('click', function() {
                const fecha = $('#fechaJs').val();
                logEvent(`Probando implementación JS para fecha ${fecha}`);
                cargarReservasDelDia(fecha);
            });
            
            // Botones de monitor
            $('#clearLog').on('click', function() {
                $('#eventLog').html('<div class="event-item">Log limpiado.</div>');
            });
            
            $('#startMonitoring').on('click', function() {
                logEvent('Iniciando monitoreo avanzado de eventos JS...');
                
                // Sobrescribir console.log para capturar todos los logs relacionados con reservas
                const originalConsoleLog = console.log;
                console.log = function() {
                    const args = Array.from(arguments);
                    originalConsoleLog.apply(console, args);
                    
                    // Filtramos solo los logs relacionados con reservas
                    const logStr = args.map(arg => 
                        typeof arg === 'object' ? JSON.stringify(arg) : String(arg)
                    ).join(' ');
                    
                    if (logStr.toLowerCase().includes('reserva')) {
                        logEvent('Console.log: ' + logStr);
                    }
                };
                
                // Sobrescribir console.error
                const originalConsoleError = console.error;
                console.error = function() {
                    const args = Array.from(arguments);
                    originalConsoleError.apply(console, args);
                    
                    const logStr = args.map(arg => 
                        typeof arg === 'object' ? JSON.stringify(arg) : String(arg)
                    ).join(' ');
                    
                    logEvent('Console.error: ' + logStr, 'error');
                };
                
                // Monitorear peticiones AJAX
                $(document).ajaxSend(function(event, jqXHR, ajaxOptions) {
                    if (ajaxOptions.url.includes('servicios.ajax.php')) {
                        logEvent(`AJAX enviado a ${ajaxOptions.url}: ${JSON.stringify(ajaxOptions.data)}`);
                    }
                });
                
                $(document).ajaxSuccess(function(event, jqXHR, ajaxOptions, data) {
                    if (ajaxOptions.url.includes('servicios.ajax.php')) {
                        logEvent(`AJAX exitoso: ${JSON.stringify(data).substring(0, 100)}...`);
                    }
                });
                
                $(document).ajaxError(function(event, jqXHR, ajaxOptions, thrownError) {
                    if (ajaxOptions.url.includes('servicios.ajax.php')) {
                        logEvent(`AJAX error: ${thrownError}`, 'error');
                    }
                });
                
                // Deshabilitar el botón para evitar múltiples sobrescrituras
                $('#startMonitoring').prop('disabled', true).text('Monitoreo Activo');
            });
            
            // Cargar datos iniciales
            const fechaInicial = $('#fechaApi').val();
            $('#probarApi').trigger('click');
        });
        
        // Implementación exacta de cargarReservasDelDia del archivo servicios.js
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
                    $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando reservas...</td></tr>');
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
                                case 'PENDIENTE': claseEstado = 'badge-warning'; break;
                                case 'CONFIRMADA': claseEstado = 'badge-success'; break;
                                case 'CANCELADA': claseEstado = 'badge-danger'; break;
                                case 'COMPLETADA': claseEstado = 'badge-info'; break;
                                default: claseEstado = 'badge-secondary';
                            }
                            
                            // Maneja diferentes nombres de propiedades para cada campo
                            // Obtener nombres con mejor manejo de posibles valores nulos o indefinidos
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
                                    <td><span class="badge ${claseEstado}">${estado}</span></td>
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
                    $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar reservas: ' + error + '</td></tr>');
                }
            });
        }
    </script>
</body>
</html>
