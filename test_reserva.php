<?php
/**
 * Archivo para probar el sistema de reservas
 * Este script permite probar las funciones AJAX del sistema de reservas
 */

// Obtener la ruta raíz
$rutaRaiz = __DIR__;

// Incluir los archivos necesarios
require_once $rutaRaiz . "/model/conexion.php";
require_once $rutaRaiz . "/controller/servicios.controller.php";
require_once $rutaRaiz . "/model/servicios.model.php";

// Cabecera para mostrar los resultados como HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Reservas Médicas</title>
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
        .result-box {
            margin-bottom: 20px;
        }
        .json-key {
            color: #0066cc;
        }
        .json-string {
            color: #cb7832;
        }
        .json-number {
            color: #009688;
        }
        .json-boolean {
            color: #2e7d32;
        }
        .json-null {
            color: #f44336;
        }
    </style>
</head>
<body class="hold-transition">
    <div class="wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Prueba de Sistema de Reservas Médicas</h1>
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
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Prueba de API de Reservas</h3>
                            </div>                <div class="card-body">                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Este script prueba las funciones del sistema de reservas médicas.
                                    <div class="float-right">
                                        <a href="diagnostico_doctores_fecha.php" target="_blank" class="btn btn-primary btn-sm mr-2">
                                            <i class="fas fa-calendar-check"></i> Diagnosticar Médicos por Fecha
                                        </a>
                                        <a href="diagnostico_agenda_medico.php" target="_blank" class="btn btn-info btn-sm">
                                            <i class="fas fa-stethoscope"></i> Diagnosticar Agendas/Médicos
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fechaPrueba">Seleccione una fecha:</label>
                                            <input type="date" class="form-control" id="fechaPrueba" value="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="metodoPrueba">Seleccione método a probar:</label>
                                            <select class="form-control" id="metodoPrueba">
                                                <option value="obtenerMedicosPorFecha">Obtener médicos por fecha</option>
                                                <option value="obtenerServiciosPorFechaMedico">Obtener servicios por fecha y médico</option>
                                                <option value="obtenerReservas">Obtener reservas por fecha</option>
                                                <option value="generarSlotsDisponibles">Generar slots disponibles</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row" id="campoMedico" style="display:none;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="medicoId">ID del médico:</label>
                                            <input type="number" class="form-control" id="medicoId" placeholder="Ingrese ID del médico">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row" id="campoServicio" style="display:none;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="servicioId">ID del servicio:</label>
                                            <input type="number" class="form-control" id="servicioId" placeholder="Ingrese ID del servicio">
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="button" class="btn btn-primary" id="btnProbar">
                                    <i class="fas fa-play"></i> Ejecutar Prueba
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div id="resultadoContainer" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Resultado de la Prueba</h3>
                                </div>
                                <div class="card-body">
                                    <div class="result-box">
                                        <h5>Parámetros enviados:</h5>
                                        <pre id="parametrosPrueba"></pre>
                                    </div>
                                    <div class="result-box">
                                        <h5>Respuesta:</h5>
                                        <pre id="resultadoPrueba"></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="view/plugins/jquery/jquery.min.js"></script>
    <script src="view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="view/dist/js/adminlte.min.js"></script>
    <script>
        $(document).ready(function() {
            // Mostrar/ocultar campos adicionales según el método seleccionado
            $('#metodoPrueba').change(function() {
                const metodo = $(this).val();
                
                // Ocultar todos los campos adicionales
                $('#campoMedico, #campoServicio').hide();
                
                // Mostrar campos según el método
                if (metodo === 'obtenerServiciosPorFechaMedico') {
                    $('#campoMedico').show();
                } else if (metodo === 'generarSlotsDisponibles') {
                    $('#campoMedico, #campoServicio').show();
                }
            });
            
            // Ejecutar prueba
            $('#btnProbar').click(function() {                const metodo = $('#metodoPrueba').val();
                const fecha = $('#fechaPrueba').val();
                const medicoId = $('#medicoId').val();
                const servicioId = $('#servicioId').val();
                
                // Mostrar el día de la semana para la fecha seleccionada para depuración
                const fechaObj = new Date(fecha);
                const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                const diaSemana = diasSemana[fechaObj.getDay()];
                
                // Añadir información del día de la semana para depuración
                $('#parametrosPrueba').html(`<div class="mb-2">Fecha seleccionada: ${fecha} (${diaSemana})</div>`);
                
                // Preparar datos según el método
                let datos = {
                    action: metodo,
                    fecha: fecha
                };
                
                if (metodo === 'obtenerServiciosPorFechaMedico' && medicoId) {
                    datos.doctor_id = medicoId;
                } else if (metodo === 'generarSlotsDisponibles' && medicoId && servicioId) {
                    datos.doctor_id = medicoId;
                    datos.servicio_id = servicioId;
                }
                
                // Mostrar parámetros
                $('#parametrosPrueba').text(JSON.stringify(datos, null, 2));
                $('#resultadoContainer').show();
                  // Realizar petición AJAX
                $.ajax({
                    url: 'ajax/servicios.ajax.php', // Ruta relativa a la raíz del sitio
                    type: 'POST',
                    data: datos,
                    dataType: 'json',                    success: function(respuesta) {
                        // Verificar si hay mensajes de información
                        let infoMessages = '';
                        if (respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                            // Verificar si hay mensajes informativos
                            if (respuesta.data[0].message) {
                                infoMessages = `<div class="alert alert-warning mb-3">
                                    <i class="fas fa-info-circle"></i> ${respuesta.data[0].message}
                                </div>`;
                            }
                        }
                        
                        const jsonStr = JSON.stringify(respuesta, null, 2);
                        const coloredJson = jsonStr.replace(
                            /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g,
                            function (match) {
                                let cls = 'json-number';
                                if (/^"/.test(match)) {
                                    if (/:$/.test(match)) {
                                        cls = 'json-key';
                                    } else {
                                        cls = 'json-string';
                                    }
                                } else if (/true|false/.test(match)) {
                                    cls = 'json-boolean';
                                } else if (/null/.test(match)) {
                                    cls = 'json-null';
                                }
                                return '<span class="' + cls + '">' + match + '</span>';
                            }
                        );
                        
                        $('#resultadoPrueba').html(infoMessages + coloredJson);
                    },
                    error: function(xhr, status, error) {
                        $('#resultadoPrueba').text('Error: ' + error + '\n\nRespuesta: ' + xhr.responseText);
                    }
                });
            });
        });
    </script>
</body>
</html>
