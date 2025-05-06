<?php
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
    echo '<script>window.location.href = "login";</script>';
    exit();
}
/**
 * Módulo de Gestión de Agendas Médicas
 */
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Administrar Agendas Médicas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Gestión de Agendas</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="card">
            <div class="card-header">
                <div class="form-row">
                    <!-- Botón para agregar agenda -->
                    <div class="col-md-2">
                        <button type="button" class="btn btn-info" id="btnNuevaAgenda">
                            <i class="fas fa-calendar-plus"></i> Nueva Agenda
                        </button>
                    </div>

                    <!-- Filtro por médico -->
                    <div class="col-md-4">
                        <select class="form-control select2" id="selectMedico" style="width: 100%;">
                            <option value="0">Todos los médicos</option>
                            <!-- Las opciones se cargarán dinámicamente -->
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Pestañas para navegación -->
                <div class="card card-primary card-outline card-tabs">
                    <div class="card-header p-0 pt-1 border-bottom-0">
                        <ul class="nav nav-tabs" id="agendaTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-agendas-tab" data-toggle="pill" href="#tabAgendas"
                                    role="tab" aria-controls="tabAgendas" aria-selected="true">Agendas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-detalles-tab" data-toggle="pill" href="#tabDetalles"
                                    role="tab" aria-controls="tabDetalles" aria-selected="false">Detalles de
                                    Horarios</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-calendario-tab" data-toggle="pill" href="#tabCalendario"
                                    role="tab" aria-controls="tabCalendario" aria-selected="false">Vista Calendario</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="agendaTabsContent">
                            <!-- Pestaña de Agendas -->
                            <div class="tab-pane fade show active" id="tabAgendas" role="tabpanel"
                                aria-labelledby="tab-agendas-tab">
                                <div class="table-responsive">
                                    <table id="tablaAgendas" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Médico</th>
                                                <th>Descripción</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Datos cargados dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Pestaña de Detalles de Horarios -->
                            <div class="tab-pane fade" id="tabDetalles" role="tabpanel"
                                aria-labelledby="tab-detalles-tab">
                                <div class="d-flex justify-content-between mb-3">
                                    <h4>Detalles de Horarios</h4>
                                    <button id="btnNuevoDetalle" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Nuevo Horario
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table id="tablaDetalles" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Día</th>
                                                <th>Turno</th>
                                                <th>Sala</th>
                                                <th>Hora Inicio</th>
                                                <th>Hora Fin</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Datos cargados dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Pestaña de Vista Calendario -->
                            <div class="tab-pane fade" id="tabCalendario" role="tabpanel"
                                aria-labelledby="tab-calendario-tab">
                                <div id="calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para Agenda -->
<div class="modal fade" id="modalAgenda" tabindex="-1" role="dialog" aria-labelledby="modalAgendaLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="modalAgendaLabel">Gestión de Agenda</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAgenda">
                <div class="modal-body">
                    <input type="hidden" id="agendaId" name="agendaId">

                    <div class="form-group">
                        <label for="medicoId">Médico:</label>
                        <select class="form-control" id="medicoId" name="medicoId" required>
                            <!-- Opciones cargadas dinámicamente -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="descripcionAgenda">Descripción:</label>
                        <input type="text" class="form-control" id="descripcionAgenda" name="descripcionAgenda"
                            required>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="estadoAgenda" name="estadoAgenda"
                                checked>
                            <label class="custom-control-label" for="estadoAgenda">Agenda Activa</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Detalle de Horario -->
<div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog" aria-labelledby="modalDetalleLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="modalDetalleLabel">Gestión de Horario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formDetalle">
                <div class="modal-body">
                    <!-- Hidden fields -->
                    <input type="hidden" id="detalleId" name="detalleId">
                    <input type="hidden" id="agendaIdDetalle" name="agendaIdDetalle">

                    <div class="form-group">
                        <label for="diaSemana">Día de la Semana:</label>
                        <select class="form-control" id="diaSemana" name="diaSemana" required>
                            <option value="1">Lunes</option>
                            <option value="2">Martes</option>
                            <option value="3">Miércoles</option>
                            <option value="4">Jueves</option>
                            <option value="5">Viernes</option>
                            <option value="6">Sábado</option>
                            <option value="0">Domingo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="turnoId">Turno:</label>
                        <select class="form-control" id="turnoId" name="turnoId" required>
                            <option value="">Seleccionar</option>
                            <!-- Las opciones se cargarán dinámicamente -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="salaId">Sala:</label>
                        <select class="form-control" id="salaId" name="salaId" required>
                            <option value="">Seleccionar</option>
                            <!-- Las opciones se cargarán dinámicamente -->
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horaInicio">Hora Inicio:</label>
                                <input type="time" class="form-control" id="horaInicio" name="horaInicio" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horaFin">Hora Fin:</label>
                                <input type="time" class="form-control" id="horaFin" name="horaFin" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="intervaloMinutos">Intervalo (minutos):</label>
                                <input type="number" class="form-control" id="intervaloMinutos" name="intervaloMinutos"
                                    min="5" max="60" value="15" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cupoMaximo">Cupo Máximo:</label>
                                <input type="number" class="form-control" id="cupoMaximo" name="cupoMaximo" min="1" max="10"
                                    value="1" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="estadoDetalle" name="estadoDetalle"
                                checked>
                            <label class="custom-control-label" for="estadoDetalle">Horario Activo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts específicos para este módulo -->
<script src="view/js/agendas.js"></script>

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/es.js"></script>