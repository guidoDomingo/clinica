<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API Reservas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow: auto;
            max-height: 400px;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .container {
            display: flex;
            gap: 20px;
        }
        .panel {
            flex: 1;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .panel h3 {
            margin-top: 0;
        }
        input, button {
            padding: 10px;
            margin: 5px 0;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Test API de Reservas</h1>
    <p>Esta herramienta permite probar directamente el endpoint de la API que obtiene las reservas.</p>

    <div class="container">
        <div class="panel">
            <h3>Parámetros de la solicitud</h3>
            <form id="apiForm">
                <label for="fecha">Fecha (YYYY-MM-DD):</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>">
                
                <button type="submit">Enviar Solicitud</button>
            </form>
        </div>
        
        <div class="panel">
            <h3>Ver SQL que se ejecutará</h3>
            <pre id="sqlQuery">
SELECT 
    sr.reserva_id,
    sr.servicio_id,
    sr.doctor_id,
    sr.paciente_id,
    sr.fecha_reserva,
    sr.hora_inicio,
    sr.hora_fin,
    sr.reserva_estado,
    sr.observaciones,
    sr.business_id,
    sr.created_at,
    sr.updated_at,
    sr.agenda_id,
    sr.sala_id,
    sr.tarifa_id,
    rp.first_name ||' - ' || rp.last_name as doctor_nombre,
    rp2.first_name ||' - ' || rp2.last_name as paciente_nombre,
    rs.serv_descripcion as servicio_nombre
FROM servicios_reservas sr 
INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id 
INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
INNER JOIN rh_person rp2 ON sr.paciente_id = rp2.person_id 
INNER JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id 
WHERE sr.fecha_reserva BETWEEN '[fecha] 00:00:00' AND '[fecha] 23:59:59'
ORDER BY sr.hora_inicio ASC
            </pre>
        </div>
    </div>

    <div class="panel">
        <h3>Respuesta JSON</h3>
        <pre id="responseJson">Esperando respuesta...</pre>
    </div>

    <div class="panel">
        <h3>Datos de Reservas</h3>
        <table id="reservasTable">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Doctor</th>
                    <th>Paciente</th>
                    <th>Servicio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="text-center">No hay datos</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('apiForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fecha = document.getElementById('fecha').value;
            
            // Actualizar el SQL con la fecha
            const sqlElement = document.getElementById('sqlQuery');
            sqlElement.textContent = sqlElement.textContent.replace(/\[fecha\]/g, fecha);
            
            // Mostrar mensaje de carga
            document.getElementById('responseJson').textContent = 'Cargando...';
            document.getElementById('reservasTable').getElementsByTagName('tbody')[0].innerHTML = 
                '<tr><td colspan="5" style="text-align:center;">Cargando datos...</td></tr>';
            
            // Hacer la solicitud AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'ajax/servicios.ajax.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    document.getElementById('responseJson').textContent = JSON.stringify(response, null, 2);
                    
                    // Procesar la respuesta para la tabla
                    const tbody = document.getElementById('reservasTable').getElementsByTagName('tbody')[0];
                    
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        let html = '';
                        
                        response.data.forEach(function(reserva) {
                            const horaInicio = reserva.hora_inicio ? reserva.hora_inicio.substring(0, 5) : '';
                            const horaFin = reserva.hora_fin ? reserva.hora_fin.substring(0, 5) : '';
                            const doctor = reserva.doctor_nombre || 'Sin doctor';
                            const paciente = reserva.paciente_nombre || 'Sin paciente';
                            const servicio = reserva.servicio_nombre || 'Sin servicio';
                            const estado = reserva.reserva_estado || reserva.estado || 'PENDIENTE';
                            
                            html += `
                                <tr>
                                    <td>${horaInicio} - ${horaFin}</td>
                                    <td>${doctor}</td>
                                    <td>${paciente}</td>
                                    <td>${servicio}</td>
                                    <td>${estado}</td>
                                </tr>
                            `;
                        });
                        
                        tbody.innerHTML = html;
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay reservas para esta fecha</td></tr>';
                    }
                } else {
                    document.getElementById('responseJson').textContent = 'Error: ' + xhr.status;
                    document.getElementById('reservasTable').getElementsByTagName('tbody')[0].innerHTML = 
                        '<tr><td colspan="5" style="text-align:center;color:red;">Error al cargar los datos</td></tr>';
                }
            };
            xhr.onerror = function() {
                document.getElementById('responseJson').textContent = 'Error de red';
                document.getElementById('reservasTable').getElementsByTagName('tbody')[0].innerHTML = 
                    '<tr><td colspan="5" style="text-align:center;color:red;">Error de red al intentar obtener los datos</td></tr>';
            };
            xhr.send('action=obtenerReservas&fecha=' + encodeURIComponent(fecha));
        });
    </script>
</body>
</html>
