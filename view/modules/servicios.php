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
                                        <!-- Panel izquierdo: Selección de fecha y Médico -->
                                        <div class="col-md-4">
                                            <!-- Selección de fecha (Paso 1) -->
                                            <div class="card card-primary">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Paso 1: Seleccione una fecha</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label for="fechaReserva">Fecha para la consulta:</label>
                                                        <input type="date" class="form-control" id="fechaReserva" min="<?php echo date('Y-m-d'); ?>">
                                                        <small class="form-text text-muted">Seleccione una fecha para ver los médicos disponibles.</small>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-block" id="btnBuscarDisponibilidad">
                                                        <i class="fas fa-search"></i> Buscar disponibilidad
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Selección de médico (Paso 2) -->
                                            <div class="card card-info">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-user-md"></i> Paso 2: Seleccione un médico</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <select class="form-control" id="selectProveedor">
                                                            <option value="">Seleccione un médico disponible</option>
                                                            <!-- Se carga dinámicamente -->
                                                        </select>
                                                    </div>
                                                    <button type="button" class="btn btn-info btn-block" id="btnCargarServicios">
                                                        <i class="fas fa-stethoscope"></i> Cargar servicios
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Selección de servicio (Paso 3) -->
                                            <div class="card card-success">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-stethoscope"></i> Paso 3: Seleccione un servicio</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <select class="form-control" id="selectServicio">
                                                            <option value="">Seleccione un servicio</option>
                                                            <!-- Se carga dinámicamente -->
                                                        </select>
                                                    </div>
                                                    <button type="button" class="btn btn-success btn-block" id="btnCargarHorarios">
                                                        <i class="fas fa-clock"></i> Cargar horarios
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Panel central: Horarios disponibles y Reservas existentes -->
                                        <div class="col-md-5">
                                            <!-- Horarios disponibles (Paso 4) -->
                                            <div class="card card-warning">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-clock"></i> Paso 4: Seleccione un horario</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div id="contenedorHorarios" class="mb-3">
                                                        <p class="text-center text-muted">Seleccione fecha, médico y servicio para ver horarios disponibles</p>
                                                    </div>
                                                    
                                                    <!-- Contenedor para slots paginados -->
                                                    <div id="slotsPaginados" class="row">
                                                        <!-- Se carga dinámicamente -->
                                                    </div>
                                                    
                                                    <!-- Paginación de slots -->
                                                    <div id="slotsPagination" class="mt-3" style="display: none;">
                                                        <!-- Se carga dinámicamente -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Panel derecho: Resumen y datos de paciente -->
                                        <div class="col-md-3">
                                            <!-- Resumen de la selección -->
                                            <div class="card card-primary">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Resumen de reserva</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div id="resumenSeleccion">
                                                        <p><strong><i class="fas fa-calendar-day"></i> Fecha:</strong> <span id="resumenFecha">-</span></p>
                                                        <p><strong><i class="fas fa-user-md"></i> Médico:</strong> <span id="resumenMedico">-</span></p>
                                                        <p><strong><i class="fas fa-stethoscope"></i> Servicio:</strong> <span id="resumenServicio">-</span></p>
                                                        <p><strong><i class="fas fa-clock"></i> Hora:</strong> <span id="resumenHora">-</span></p>
                                                        <p><strong><i class="fas fa-door-open"></i> Sala:</strong> <span id="resumenSala">-</span></p>
                                                    </div>
                                                    
                                                    <!-- Campos ocultos para hora seleccionada -->
                                                    <input type="hidden" id="horaInicio">
                                                    <input type="hidden" id="horaFin">
                                                </div>                                            </div>

                                            <!-- Selección de paciente (Paso 5) -->
                                            <div class="card card-success">
                                                <div class="card-header">
                                                    <h3 class="card-title"><i class="fas fa-user"></i> Paso 5: Paciente</h3>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="buscarPaciente" placeholder="Buscar paciente">
                                                            <div class="input-group-append">
                                                                <button class="btn btn-primary" type="button" id="btnBuscarPaciente">
                                                                    <i class="fas fa-search"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div id="resultadosPacientes" class="mb-3">
                                                        <!-- Se carga dinámicamente -->
                                                    </div>
                                                    
                                                    <!-- Formulario final de reserva -->
                                                    <form id="formReserva">
                                                        <input type="hidden" id="pacienteSeleccionado">
                                                        
                                                        <div class="form-group">
                                                            <label for="observaciones">Observaciones:</label>
                                                            <textarea class="form-control" id="observaciones" rows="2"></textarea>
                                                        </div>
                                                        
                                                        <button type="submit" class="btn btn-success btn-block" id="btnGuardarReserva">
                                                            <i class="fas fa-save"></i> Guardar Reserva
                                                        </button>
                                                    </form>
                                                </div>                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reservas existentes para la fecha seleccionada -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h5 class="mb-0"><i class="fas fa-list"></i> Reservas existentes para la fecha seleccionada</h5>
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
                                                                    <td colspan="5" class="text-center">No hay reservas para esta fecha</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                    </div>
                                                <!-- Fin de la tabla de reservas -->                                <!-- Campos ocultos adicionales -->
                                <input type="hidden" id="agendaId">
                                <input type="hidden" id="tarifaId">
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
