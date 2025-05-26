<?php
/*
 * Módulo: Gestión de Servicios Médicos
 * Descripción: Vista para administración de servicios médicos y reservas
 */

// Verificar sesión activa
if (!isset($_SESSION['iniciarSesion']) || $_SESSION['iniciarSesion'] != "ok") {
    echo '<script>window.location = "login";</script>';
    exit;
}

// Asignar un perfil por defecto si no existe
if (!isset($_SESSION['perfil'])) {
    $_SESSION['perfil'] = 'General';
}
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">                <div class="col-sm-6">
                    <h1>Gestión de Servicios Médicos <a href="../README_SERVICIOS.md" target="_blank" title="Ver documentación" class="btn btn-sm btn-outline-info"><i class="fas fa-question-circle"></i></a></h1>
                </div>                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
                        <li class="breadcrumb-item active">Servicios Médicos</li>
                        <li class="breadcrumb-item"><a href="crear_tabla_reservas_ui.php" target="_blank" class="text-danger">Crear Tablas Reservas</a></li>
                        <li class="breadcrumb-item"><a href="test_reserva.php" target="_blank">Probar Reservas</a></li>
                        <li class="breadcrumb-item"><a href="diagnostico_agenda_medico.php" target="_blank" class="text-primary"><i class="fas fa-stethoscope"></i> Diagnóstico</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <!-- Navegación con pestañas -->
                    <div class="card card-primary card-outline card-outline-tabs">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="custom-tabs" role="tablist">
                                <!-- <li class="nav-item">
                                    <a class="nav-link active" id="tab-servicios-tab" data-toggle="pill" href="#tabServicios" role="tab" aria-controls="tabServicios" aria-selected="true">
                                        <i class="fas fa-clipboard-list mr-1"></i>Servicios
                                    </a>
                                </li> -->
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-reserva-tab" data-toggle="pill" href="#tabReserva" role="tab" aria-controls="tabReserva" aria-selected="false">
                                        <i class="fas fa-calendar-plus mr-1"></i>Nueva Reserva
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-reservas-tab" data-toggle="pill" href="#tabReservas" role="tab" aria-controls="tabReservas" aria-selected="false">
                                        <i class="fas fa-calendar-alt mr-1"></i>Reservas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-calendario-tab" data-toggle="pill" href="#tabCalendario" role="tab" aria-controls="tabCalendario" aria-selected="false">
                                        <i class="fas fa-calendar-week mr-1"></i>Calendario
                                    </a>
                                </li>
                                <?php if (in_array($_SESSION['perfil'], ['Administrador', 'Director Médico'])): ?>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-admin-tab" data-toggle="pill" href="#tabAdmin" role="tab" aria-controls="tabAdmin" aria-selected="false">
                                        <i class="fas fa-cogs mr-1"></i>Administración
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        
                        <div class="card-body">
                            <div class="tab-content" id="custom-tabs-content">
                            
                                <!-- PESTAÑA: LISTA DE SERVICIOS -->
                                <div class="tab-pane fade show " id="tabServicios" role="tabpanel" aria-labelledby="tab-servicios-tab">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="selectCategoria">Filtrar por categoría:</label>
                                                <select class="form-control" id="selectCategoria">
                                                    <!-- Las categorías se cargan con JavaScript -->
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-right" style="display: flex; align-items: flex-end; justify-content: flex-end;">
                                            <?php if (in_array($_SESSION['perfil'], ['Administrador', 'Director Médico'])): ?>
                                            <button type="button" class="btn btn-primary" id="btnNuevoServicio">
                                                <i class="fas fa-plus"></i> Nuevo Servicio
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped dt-responsive" id="tablaServicios">
                                            <thead>
                                                <tr>
                                                    <th style="width: 10%">Código</th>
                                                    <th style="width: 30%">Servicio</th>
                                                    <th style="width: 20%">Categoría</th>
                                                    <th style="width: 15%">Duración</th>
                                                    <th style="width: 15%">Precio Base</th>
                                                    <th style="width: 10%">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- La tabla se llena con JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- PESTAÑA: CREAR NUEVA RESERVA -->
                                <div class="tab-pane fade active" id="tabReserva" role="tabpanel" aria-labelledby="tab-reserva-tab">
                                    <div class="row">
                                        <div class="col-12">
                                            <!-- Barra de progreso -->
                                            <div class="progress mb-4">
                                                <div id="progresoReserva" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>                                            <!-- Paso 1: Selección de fecha -->
                                            <div class="paso-reserva" id="paso1">
                                                <h4 class="mb-3">Paso 1: Seleccione una fecha para la consulta</h4>
                                                <div class="form-group">
                                                    <label for="fechaReserva"><i class="fas fa-calendar-alt"></i> Fecha para la consulta:</label>
                                                    <input type="date" class="form-control" id="fechaReserva" min="<?php echo date('Y-m-d'); ?>">
                                                    <small class="form-text text-muted">Seleccione una fecha para ver los doctores disponibles.</small>
                                                </div>
                                                <div class="text-right">
                                                    <button type="button" class="btn btn-primary" id="btnBuscarDisponibilidad">Buscar disponibilidad <i class="fas fa-search"></i></button>
                                                </div>
                                                
                                                <!-- Tabla de reservas existentes para la fecha seleccionada -->
                                                <div class="card mt-4">
                                                    <div class="card-header">
                                                        <h5 class="mb-0">Reservas existentes para la fecha seleccionada</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-striped" id="tablaReservasExistentes">
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
                                                                        <td colspan="5" class="text-center">Seleccione una fecha para ver las reservas existentes</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                              <!-- Paso 2: Selección de doctor -->
                                            <div class="paso-reserva" id="paso2" style="display: none;">
                                                <h4 class="mb-3">Paso 2: Seleccione un doctor disponible</h4>
                                                
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> Doctores disponibles para la fecha: <strong><span id="fechaSeleccionadaTexto"></span></strong>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="selectProveedor"><i class="fas fa-user-md"></i> Doctor:</label>
                                                    <select class="form-control" id="selectProveedor">
                                                        <option value="">Seleccione un doctor</option>
                                                        <!-- Las opciones se cargan con JavaScript -->
                                                    </select>
                                                </div>
                                                
                                                <div class="text-right">
                                                    <button type="button" class="btn btn-secondary mr-2" onclick="mostrarPasoReserva(1)"><i class="fas fa-arrow-left"></i> Anterior</button>
                                                    <button type="button" class="btn btn-primary" id="btnContinuarDoctor">Siguiente <i class="fas fa-arrow-right"></i></button>
                                                </div>
                                            </div>                                            <!-- Paso 3: Selección de servicio -->
                                            <div class="paso-reserva" id="paso3" style="display: none;">
                                                <h4 class="mb-3">Paso 3: Seleccione un servicio disponible</h4>
                                                
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> Servicios disponibles para el doctor <strong><span id="doctorSeleccionadoTexto"></span></strong> en la fecha: <strong><span id="fechaSeleccionadaDoctorTexto"></span></strong>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="selectServicio"><i class="fas fa-stethoscope"></i> Servicio:</label>
                                                    <select class="form-control" id="selectServicio">
                                                        <option value="">Seleccione un servicio</option>
                                                        <!-- Las opciones se cargan con JavaScript -->
                                                    </select>
                                                </div>
                                                
                                                <div class="text-right">
                                                    <button type="button" class="btn btn-secondary mr-2" onclick="mostrarPasoReserva(2)"><i class="fas fa-arrow-left"></i> Anterior</button>
                                                    <button type="button" class="btn btn-primary" id="btnContinuarServicio">Siguiente <i class="fas fa-arrow-right"></i></button>
                                                </div>
                                            </div>
                                              <!-- Paso 4: Selección de horario -->
                                            <div class="paso-reserva" id="paso4" style="display: none;">
                                                <h4 class="mb-3">Paso 4: Seleccione un horario disponible</h4>
                                                
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i> Seleccione un horario disponible para la fecha elegida. Los horarios en gris no están disponibles por reservas existentes.
                                                </div>
                                                  <div id="contenedorHorarios" class="mb-3">
                                                    <!-- Los horarios disponibles se cargan con JavaScript -->
                                                </div>
                                                
                                                <!-- Contenedor para slots paginados -->
                                                <div id="slotsPaginados" class="row mb-3">
                                                    <!-- Los slots paginados se cargan con JavaScript -->
                                                </div>
                                                
                                                <!-- Paginación de slots -->
                                                <div id="slotsPagination" class="mb-3" style="display: none;">
                                                    <!-- La paginación se carga dinámicamente -->
                                                </div>
                                                
                                                <!-- Resumen de selección -->
                                                <div class="card mt-3" id="resumenSeleccion" style="display: none;">
                                                    <div class="card-header bg-success text-white">
                                                        <h5 class="mb-0"><i class="fas fa-check-circle"></i> Horario seleccionado</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p><strong><i class="fas fa-stethoscope"></i> Servicio:</strong> <span id="resumenServicio"></span></p>
                                                                <p><strong><i class="fas fa-user-md"></i> Doctor:</strong> <span id="resumenMedico"></span></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong><i class="fas fa-calendar-day"></i> Fecha:</strong> <span id="resumenFecha"></span></p>
                                                                <p><strong><i class="fas fa-clock"></i> Hora:</strong> <span id="resumenHora"></span></p>
                                                                <p><strong><i class="fas fa-door-open"></i> Sala:</strong> <span id="resumenSala"></span></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Campos ocultos para almacenar hora seleccionada -->
                                                <input type="hidden" id="horaInicio">
                                                <input type="hidden" id="horaFin">
                                                
                                                <div class="text-right mt-3">
                                                    <button type="button" class="btn btn-secondary mr-2" onclick="mostrarPasoReserva(3)">
                                                        <i class="fas fa-arrow-left"></i> Anterior
                                                    </button>
                                                    <button type="button" class="btn btn-primary" onclick="mostrarPasoReserva(5)" id="btnConfirmarReserva" disabled>
                                                        Siguiente <i class="fas fa-arrow-right"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Paso 5: Selección de paciente y confirmación -->
                                            <div class="paso-reserva" id="paso5" style="display: none;">
                                                <h4 class="mb-3">Paso 5: Seleccione un paciente y confirme</h4>
                                                
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="buscarPaciente" placeholder="Buscar paciente por nombre o documento">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-primary" type="button" id="btnBuscarPaciente">
                                                                <i class="fas fa-search"></i> Buscar
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Ingrese al menos 3 caracteres para buscar</small>
                                                </div>
                                                
                                                <div id="resultadosPacientes" class="mb-3">
                                                    <!-- Los resultados de búsqueda se cargan con JavaScript -->
                                                </div>
                                                
                                                <!-- Formulario final de reserva -->
                                                <form id="formReserva">
                                                    <input type="hidden" id="pacienteSeleccionado" class="form-control" readonly>
                                                    
                                                    <div class="form-group">
                                                        <label for="observaciones">Observaciones:</label>
                                                        <textarea class="form-control" id="observaciones" rows="3" placeholder="Observaciones adicionales para la reserva"></textarea>
                                                    </div>
                                                    
                                                    <!-- Campos ocultos para agenda y tarifa (si aplica) -->
                                                    <input type="hidden" id="agendaId">
                                                    <input type="hidden" id="tarifaId">
                                                    
                                                    <div class="text-right">
                                                        <button type="button" class="btn btn-secondary mr-2" onclick="mostrarPasoReserva(4)"><i class="fas fa-arrow-left"></i> Anterior</button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="fas fa-save"></i> Guardar Reserva
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- PESTAÑA: RESERVAS ACTUALES -->
                                <div class="tab-pane fade" id="tabReservas" role="tabpanel" aria-labelledby="tab-reservas-tab">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="fechaReservas">Fecha:</label>
                                                <input type="date" class="form-control" id="fechaReservas" value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="selectMedicoReservas">Médico:</label>
                                                <select class="form-control" id="selectMedicoReservas">
                                                    <option value="0">Todos los médicos</option>                                                    <!-- Las opciones se cargan con JavaScript -->
                                                    
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="selectEstadoReserva">Estado:</label>
                                                <select class="form-control" id="selectEstadoReserva">
                                                    <option value="0">Todos los estados</option>
                                                    <option value="PENDIENTE">Pendientes</option>
                                                    <option value="CONFIRMADA">Confirmadas</option>
                                                    <option value="COMPLETADA">Completadas</option>
                                                    <option value="CANCELADA">Canceladas</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="tablaReservas">
                                            <thead>
                                                <tr>
                                                    <th style="width: 15%">Horario</th>
                                                    <th style="width: 20%">Servicio</th>
                                                    <th style="width: 20%">Paciente</th>
                                                    <th style="width: 20%">Doctor</th>
                                                    <th style="width: 15%">Estado</th>
                                                    <th style="width: 10%">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- La tabla se llena con JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- PESTAÑA: CALENDARIO -->
                                <div class="tab-pane fade" id="tabCalendario" role="tabpanel" aria-labelledby="tab-calendario-tab">
                                    <div id="calendar"></div>
                                </div>
                                
                                <!-- PESTAÑA: ADMINISTRACIÓN -->
                                <?php if (in_array($_SESSION['perfil'], ['Administrador', 'Director Médico'])): ?>
                                <div class="tab-pane fade" id="tabAdmin" role="tabpanel" aria-labelledby="tab-admin-tab">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h3 class="card-title">Gestión de Categorías</h3>
                                                </div>
                                                <div class="card-body">
                                                    <button class="btn btn-primary mb-3" id="btnNuevaCategoria">
                                                        <i class="fas fa-plus"></i> Nueva Categoría
                                                    </button>
                                                    
                                                    <table class="table table-bordered table-striped" id="tablaCategorias">
                                                        <thead>
                                                            <tr>
                                                                <th>Nombre</th>
                                                                <th>Descripción</th>
                                                                <th>Acciones</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Las categorías se cargan con JavaScript -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h3 class="card-title">Importar Servicios Anteriores</h3>
                                                </div>
                                                <div class="card-body">
                                                    <button class="btn btn-warning" id="btnImportarServiciosAntiguos">
                                                        <i class="fas fa-file-import"></i> Importar servicios
                                                    </button>
                                                    <p class="text-muted mt-2">
                                                        Esta opción importará los servicios médicos registrados en el sistema anterior.
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="card mt-4">
                                                <div class="card-header">
                                                    <h3 class="card-title">Configuración de Horarios</h3>
                                                </div>
                                                <div class="card-body">
                                                    <button class="btn btn-info" id="btnGestionHorarios">
                                                        <i class="fas fa-clock"></i> Gestionar Horarios
                                                    </button>
                                                    <p class="text-muted mt-2">
                                                        Configure los horarios disponibles para cada servicio y doctor.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- MODAL PARA GESTIÓN DE SERVICIOS -->
<div class="modal fade" id="modalServicio">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title" id="tituloModalServicio">Nuevo Servicio</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formServicio">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="servicioCodigo">Código:</label>
                                <input type="text" class="form-control" id="servicioCodigo" placeholder="Código del servicio" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="servicioCategoria">Categoría:</label>
                                <select class="form-control" id="servicioCategoria" required>
                                    <!-- Las categorías se cargan con JavaScript -->
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="servicioNombre">Nombre del servicio:</label>
                        <input type="text" class="form-control" id="servicioNombre" placeholder="Nombre del servicio" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="servicioDescripcion">Descripción:</label>
                        <textarea class="form-control" id="servicioDescripcion" rows="3" placeholder="Descripción detallada del servicio"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="servicioDuracion">Duración (minutos):</label>
                                <input type="number" class="form-control" id="servicioDuracion" value="30" min="5" max="480" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="servicioPrecioBase">Precio base:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" class="form-control" id="servicioPrecioBase" value="0" min="0" step="0.01" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Campo oculto para el ID del servicio en edición -->
                    <input type="hidden" id="servicioId" value="0">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Estilos adicionales -->
<style>
.slot-horario {
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
    background-color: #f8f9fa;
    transition: all 0.2s;
    height: 100%;
}

.slot-horario:hover {
    background-color: #e2e6ea;
    border-color: #dae0e5;
}

.slot-horario.selected {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.slot-horario.no-disponible {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    cursor: not-allowed;
    opacity: 0.7;
}
</style>

<!-- MODAL PARA DETALLES DE UNA RESERVA -->
<div class="modal fade" id="modalDetalleReserva">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title">Detalles de la Reserva</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center" id="loaderDetalles">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p>Cargando detalles...</p>
                </div>
                
                <div id="contenidoDetalles" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha:</label>
                                <p id="detalleFecha" class="text-muted"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Horario:</label>
                                <p id="detalleHorario" class="text-muted"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Servicio:</label>
                        <p id="detalleServicio" class="text-muted"></p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Paciente:</label>
                                <p id="detallePaciente" class="text-muted"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Doctor:</label>
                                <p id="detalleDoctor" class="text-muted"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Estado:</label>
                        <p id="detalleEstado" class="badge"></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Observaciones:</label>
                        <p id="detalleObservaciones" class="text-muted"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <div class="btn-group" id="accionesReserva">
                    <button type="button" class="btn btn-success" id="btnConfirmarReserva">
                        <i class="fas fa-check"></i> Confirmar
                    </button>
                    <button type="button" class="btn btn-danger" id="btnCancelarReserva">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA GESTIÓN DE CATEGORÍAS -->
<div class="modal fade" id="modalCategoria">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title" id="tituloModalCategoria">Nueva Categoría</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formCategoria">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="categoriaNombre">Nombre:</label>
                        <input type="text" class="form-control" id="categoriaNombre" placeholder="Nombre de la categoría" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoriaDescripcion">Descripción:</label>
                        <textarea class="form-control" id="categoriaDescripcion" rows="3" placeholder="Descripción de la categoría"></textarea>
                    </div>
                    
                    <!-- Campo oculto para el ID de la categoría en edición -->
                    <input type="hidden" id="categoriaId" value="0">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Incluir FullCalendar -->
<link href="view/plugins/fullcalendar/main.min.css" rel="stylesheet">
<script src="view/plugins/fullcalendar/main.min.js"></script>
<script src="view/plugins/fullcalendar/locales/es.js"></script>

<!-- Incluir estilos -->
<link href="view/css/servicios.css" rel="stylesheet">
<link href="view/css/slots_horario.css" rel="stylesheet">

<!-- Incluir JavaScript personalizado -->
<script src="view/js/servicios.js"></script>
<script src="view/js/slots_init.js"></script>
<script src="view/js/slots_fallback.js"></script>
<script src="view/js/slots_pagination.js"></script>
