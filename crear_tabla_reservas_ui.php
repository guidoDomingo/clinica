<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Tablas de Reservas Médicas</title>
    <link rel="stylesheet" href="view/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="view/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="view/dist/css/adminlte.min.css">
    <style>
        .result-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="hold-transition">
    <div class="wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Crear Tablas para Sistema de Reservas Médicas</h1>
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
                                <h3 class="card-title">Instrucciones</h3>
                            </div>
                            <div class="card-body">
                                <p>Esta herramienta creará las tablas necesarias para el sistema de reservas médicas, incluyendo:</p>
                                <ul>
                                    <li><strong>servicios_reservas</strong>: Tabla principal para almacenar las reservas de servicios médicos</li>
                                    <li><strong>agendas_bloqueos</strong>: Tabla para registrar bloqueos de agenda (vacaciones, permisos, etc.)</li>
                                </ul>
                                <p class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i> <strong>IMPORTANTE:</strong> Esta acción debe realizarse solo una vez. Si las tablas ya existen, el proceso podría fallar o modificar los datos existentes.
                                </p>
                                <button type="button" class="btn btn-primary" id="btnCrearTablas">
                                    <i class="fas fa-database"></i> Crear Tablas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div id="resultContainer" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Resultado de la Operación</h3>
                                </div>
                                <div class="card-body">
                                    <div id="resultBox" class="result-box">
                                        <h5 id="resultTitle"></h5>
                                        <pre id="resultContent"></pre>
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
            $("#btnCrearTablas").click(function() {
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
                
                $.ajax({
                    url: 'crear_tabla_reservas.php',
                    type: 'POST',
                    data: { action: 'create_tables' },
                    dataType: 'json',
                    success: function(response) {
                        $("#resultContainer").show();
                        
                        if (response.success) {
                            $("#resultBox").removeClass("error").addClass("success");
                            $("#resultTitle").html('<i class="fas fa-check-circle"></i> Operación Exitosa');
                        } else {
                            $("#resultBox").removeClass("success").addClass("error");
                            $("#resultTitle").html('<i class="fas fa-times-circle"></i> Error en la Operación');
                        }
                        
                        $("#resultContent").text(response.message);
                        $("#btnCrearTablas").prop('disabled', false).html('<i class="fas fa-database"></i> Crear Tablas');
                    },
                    error: function(xhr, status, error) {
                        $("#resultContainer").show();
                        $("#resultBox").removeClass("success").addClass("error");
                        $("#resultTitle").html('<i class="fas fa-times-circle"></i> Error en la Operación');
                        $("#resultContent").text('Error: ' + error + '\n\nRespuesta del servidor: ' + xhr.responseText);
                        $("#btnCrearTablas").prop('disabled', false).html('<i class="fas fa-database"></i> Crear Tablas');
                    }
                });
            });
        });
    </script>
</body>
</html>
