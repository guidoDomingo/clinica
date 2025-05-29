<?php
/**
 * Herramienta de depuración para validación de fechas en reservas
 * Esta página permite probar la validación de fechas del sistema de reservas
 */
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug de Validación de Fechas - Sistema de Reservas</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <style>
        .debug-box {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .result-box {
            min-height: 150px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }
        .console-log {
            background-color: #000;
            color: #0f0;
            font-family: monospace;
            padding: 10px;
            border-radius: 5px;
            max-height: 400px;
            overflow-y: auto;
        }
        .warning { color: #ffc107; }
        .error { color: #dc3545; }
        .success { color: #28a745; }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Herramienta de Depuración para Validación de Fechas</h1>
        <p class="lead">Esta herramienta permite probar la validación de fechas usada en el sistema de reservas</p>
        
        <div class="alert alert-info">
            <strong>Nota:</strong> Esta herramienta es solo para fines de depuración y no afecta al sistema real de reservas.
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Información del Sistema</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h4>Fecha Actual del Servidor</h4>
                        <div class="debug-box">
                            <p><strong>Fecha:</strong> <?php echo date('Y-m-d'); ?></p>
                            <p><strong>Hora:</strong> <?php echo date('H:i:s'); ?></p>
                            <p><strong>Timezone:</strong> <?php echo date_default_timezone_get(); ?></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h4>Información del Navegador</h4>
                        <div class="debug-box" id="browserInfo">
                            <p>Cargando información del navegador...</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h4>Diferencias de Tiempo</h4>
                        <div class="debug-box" id="timeDifference">
                            <p>Calculando diferencias...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">Prueba de Validación de Fechas</h3>
            </div>
            <div class="card-body">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-5">
                        <label for="testFecha">Fecha a Validar:</label>
                        <input type="date" class="form-control" id="testFecha" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group col-md-5">
                        <label for="testReferencia">Fecha de Referencia (hoy):</label>
                        <input type="date" class="form-control" id="testReferencia" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <button class="btn btn-primary btn-block" id="btnValidar">Validar</button>
                    </div>
                </div>
                
                <div class="result-box">
                    <h4>Resultado:</h4>
                    <div id="resultadoValidacion">
                        <p class="text-muted">Presione "Validar" para ver el resultado...</p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h4>Consola:</h4>
                    <div id="consolaLog" class="console-log">
                        <p class="text-muted">// Los mensajes de consola aparecerán aquí</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h3 class="card-title">Funciones de Validación de Fechas</h3>
            </div>
            <div class="card-body">
                <pre id="codigoFunciones">// Cargando funciones de validación...</pre>
            </div>
        </div>
        
        <div class="text-center mb-5">
            <a href="verificar_reservas_implementacion.php" class="btn btn-primary">Ir a Verificación de Implementación</a>
            <a href="index.php" class="btn btn-secondary ml-2">Volver al Inicio</a>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Funciones de validación de fechas (copia de servicios.js)
    function esAnterior(fechaA, fechaB) {
        // Convertir a objetos Date si son strings
        const dateA = fechaA instanceof Date ? fechaA : new Date(fechaA);
        const dateB = fechaB instanceof Date ? fechaB : new Date(fechaB);
        
        // Extraer solo año, mes y día
        const yearA = dateA.getFullYear();
        const monthA = dateA.getMonth();
        const dayA = dateA.getDate();
        
        const yearB = dateB.getFullYear();
        const monthB = dateB.getMonth();
        const dayB = dateB.getDate();
        
        // Comparar por componentes
        if (yearA < yearB) return true;
        if (yearA > yearB) return false;
        // Si llegamos aquí, los años son iguales
        if (monthA < monthB) return true;
        if (monthA > monthB) return false;
        // Si llegamos aquí, los meses son iguales
        return dayA < dayB;
    }

    function sonMismoDia(fechaA, fechaB) {
        // Convertir a objetos Date si son strings
        const dateA = fechaA instanceof Date ? fechaA : new Date(fechaA);
        const dateB = fechaB instanceof Date ? fechaB : new Date(fechaB);
        
        // Comparar año, mes y día
        return dateA.getFullYear() === dateB.getFullYear() &&
               dateA.getMonth() === dateB.getMonth() &&
               dateA.getDate() === dateB.getDate();
    }

    // Reemplazar console.log para capturar en nuestro elemento
    const originalConsoleLog = console.log;
    console.log = function() {
        // Llamar al console.log original
        originalConsoleLog.apply(console, arguments);
        
        // Convertir los argumentos a string y mostrarlos en nuestro elemento
        const args = Array.from(arguments);
        let message = args.map(arg => {
            if (typeof arg === 'object') {
                return JSON.stringify(arg, null, 2);
            } else {
                return arg;
            }
        }).join(' ');
        
        // Agregar al contenedor
        const logLine = document.createElement('div');
        logLine.innerHTML = `<pre>${message}</pre>`;
        document.getElementById('consolaLog').appendChild(logLine);
        
        // Scroll al final
        document.getElementById('consolaLog').scrollTop = document.getElementById('consolaLog').scrollHeight;
    };

    // Cuando el documento esté listo
    $(document).ready(function() {
        // Mostrar información del navegador
        const browserInfo = `
            <p><strong>User Agent:</strong> ${navigator.userAgent}</p>
            <p><strong>Fecha local:</strong> ${new Date().toLocaleDateString()}</p>
            <p><strong>Hora local:</strong> ${new Date().toLocaleTimeString()}</p>
            <p><strong>Timezone:</strong> ${Intl.DateTimeFormat().resolvedOptions().timeZone}</p>
            <p><strong>Offset (minutos):</strong> ${new Date().getTimezoneOffset()}</p>
        `;
        $('#browserInfo').html(browserInfo);
        
        // Calcular diferencia entre servidor y cliente
        const serverTime = new Date("<?php echo date('Y-m-d H:i:s'); ?>");
        const clientTime = new Date();
        const diffMs = clientTime - serverTime;
        const diffMins = Math.round(diffMs / 60000);
        const diffHours = (diffMins / 60).toFixed(2);
        
        const timeDiff = `
            <p><strong>Servidor:</strong> ${serverTime.toLocaleString()}</p>
            <p><strong>Cliente:</strong> ${clientTime.toLocaleString()}</p>
            <p><strong>Diferencia:</strong> ${diffMins} minutos (${diffHours} horas)</p>
            <p class="${Math.abs(diffMins) > 10 ? 'error' : 'success'}">
                ${Math.abs(diffMins) > 10 ? '⚠️ Diferencia significativa' : '✓ Diferencia aceptable'}
            </p>
        `;
        $('#timeDifference').html(timeDiff);
        
        // Mostrar código de funciones de validación
        $('#codigoFunciones').text(esAnterior.toString() + '\n\n' + sonMismoDia.toString());
        
        // Manejar el botón de validación
        $('#btnValidar').click(function() {
            const fechaTest = $('#testFecha').val();
            const fechaRef = $('#testReferencia').val();
            
            const dateTest = new Date(fechaTest);
            const dateRef = new Date(fechaRef);
            
            console.log('---- INICIO PRUEBA DE VALIDACIÓN ----');
            console.log('Fecha a validar:', dateTest.toLocaleDateString());
            console.log('Fecha de referencia:', dateRef.toLocaleDateString());
            
            const esMismoDia = sonMismoDia(dateTest, dateRef);
            const esAnteriorARef = esAnterior(dateTest, dateRef);
            const esPosteriorARef = esAnterior(dateRef, dateTest);
            
            console.log('Son el mismo día:', esMismoDia);
            console.log('Es anterior a la referencia:', esAnteriorARef);
            console.log('Es posterior a la referencia:', esPosteriorARef);
            
            let resultado, clase, mensaje;
            if (esMismoDia) {
                resultado = "PERMITIDO";
                clase = "success";
                mensaje = "La fecha seleccionada corresponde a HOY.";
            } else if (esAnteriorARef) {
                resultado = "RECHAZADO";
                clase = "error";
                mensaje = "La fecha seleccionada es ANTERIOR a la fecha de referencia.";
            } else {
                resultado = "PERMITIDO";
                clase = "success";
                mensaje = "La fecha seleccionada es POSTERIOR a la fecha de referencia.";
            }
            
            console.log('Resultado:', resultado);
            console.log('---- FIN PRUEBA DE VALIDACIÓN ----');
            
            const htmlResultado = `
                <div class="alert alert-${clase === 'success' ? 'success' : 'danger'}">
                    <h5 class="alert-heading">${resultado}</h5>
                    <p>${mensaje}</p>
                </div>
                <table class="table table-bordered">
                    <tr>
                        <th>Comprobación</th>
                        <th>Resultado</th>
                    </tr>
                    <tr>
                        <td>Son el mismo día</td>
                        <td>${esMismoDia ? '✓ Sí' : '✗ No'}</td>
                    </tr>
                    <tr>
                        <td>Es anterior a la referencia</td>
                        <td>${esAnteriorARef ? '✓ Sí' : '✗ No'}</td>
                    </tr>
                    <tr>
                        <td>Es posterior a la referencia</td>
                        <td>${esPosteriorARef ? '✓ Sí' : '✗ No'}</td>
                    </tr>
                </table>
            `;
            
            $('#resultadoValidacion').html(htmlResultado);
        });
    });
    </script>
</body>
</html>
