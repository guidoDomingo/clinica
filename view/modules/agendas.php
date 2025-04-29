<?php
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
    echo '<script>window.location.href = "login";</script>';
    exit();
}
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Administración de agendas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Agendas</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <button type="button" class="btn btn-primary btn-block mb-3" id="btnNuevaAgenda">
                    <i class="fas fa-plus"></i> Nueva Agenda
                </button>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Formulario</h3>

                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <form id="formAgenda">
                            <input type="hidden" id="idAgenda" name="idAgenda">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item active">
                                    <div class="nav-link">
                                        <div class="form-group">
                                            <label>Médico</label>
                                            <select class="form-control select2 select2-danger" id="medicoAgenda"
                                                name="medicoAgenda" data-dropdown-css-class="select2-danger"
                                                style="width: 100%;" required>
                                                <option value="">-- Seleccione --</option>
                                                <!-- Opciones de médicos se cargarán dinámicamente -->
                                            </select>
                                        </div>
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <div class="form-group clearfix nav-link">
                                        <div class="icheck-primary d-inline">
                                            <input type="checkbox" id="checkboxLunes" name="dia_semana" value="1">
                                            <label for="checkboxLunes">Lunes</label>
                                        </div>
                                        <div class="icheck-primary d-inline">
                                            <input type="checkbox" id="checkboxMartes" name="dia_semana" value="2">
                                            <label for="checkboxMartes">Martes</label>
                                        </div>
                                        <div class="icheck-primary d-inline">
                                            <input type="checkbox" id="checkboxMiercoles" name="dia_semana" value="3">
                                            <label for="checkboxMiercoles">Miércoles</label>
                                        </div>
                                        <div class="icheck-primary d-inline">
                                            <input type="checkbox" id="checkboxJueves" name="dia_semana" value="4">
                                            <label for="checkboxJueves">Jueves</label>
                                        </div>
                                    </div>

                                    <div class="form-group clearfix nav-link">
                                        <div class="icheck-primary d-inline">
                                            <input type="checkbox" id="checkboxViernes" name="dia_semana" value="5">
                                            <label for="checkboxViernes">Viernes</label>
                                        </div>
                                        <div class="icheck-primary d-inline">
                                            <input type="checkbox" id="checkboxSabado" name="dia_semana" value="6">
                                            <label for="checkboxSabado">Sábado</label>
                                        </div>
                                        <div class="icheck-primary d-inline">
                                            <input type="checkbox" id="checkboxDomingo" name="dia_semana" value="7">
                                            <label for="checkboxDomingo">Domingo</label>
                                        </div>
                                        <div class="icheck-primary d-inline">
                                            <input type="checkbox" id="checkboxTodos" name="dia_todos" value="todos">
                                            <label for="checkboxTodos">Lun/Vie</label>
                                        </div>
                                    </div>
                                </li>

                                <li class="nav-item">
                                    <div class="nav-link">
                                        <div class="form-group">
                                            <label>Turno</label>
                                            <select class="form-control select2" id="turnoAgenda" name="turnoAgenda"
                                                style="width: 100%;" required>
                                                <option value="">Seleccionar</option>
                                                <!-- Opciones de turnos se cargarán dinámicamente -->
                                            </select>
                                        </div>
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <div class="nav-link">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="horaInicio">Desde</label>
                                                <input type="time" class="form-control" id="horaInicio"
                                                    name="horaInicio" required>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="horaFin">Hasta</label>
                                                <input type="time" class="form-control" id="horaFin" name="horaFin"
                                                    required>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <div class="nav-link">
                                        <div class="form-group">
                                            <label>Sala</label>
                                            <select class="form-control select2" id="salaAgenda" name="salaAgenda"
                                                style="width: 100%;" required>
                                                <option value="">Seleccionar</option>
                                                <!-- Opciones de salas se cargarán dinámicamente -->
                                            </select>
                                        </div>
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <div class="nav-link">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="intervaloAgenda">Intervalo</label>
                                                <select class="form-control" id="intervaloAgenda" name="intervaloAgenda"
                                                    required>
                                                    <option value="10">10 minutos</option>
                                                    <option value="15">15 minutos</option>
                                                    <option value="20">20 minutos</option>
                                                    <option value="30">30 minutos</option>
                                                    <option value="60">60 minutos</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="estadoAgenda">Estado</label>
                                                <select class="form-control" id="estadoAgenda" name="estadoAgenda"
                                                    required>
                                                    <option value="Activo">Activo</option>
                                                    <option value="Inactivo">Inactivo</option>
                                                    <option value="Inactivo Temp">Inactivo Temp</option>
                                                    <option value="Vacaciones">Vacaciones</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="nav-item">
                                    <div class="nav-link">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-success" id="btnGuardarAgenda">
                                                <i class="far fa-save"></i> Guardar
                                            </button>
                                            <button type="button" class="btn btn-warning" id="btnLimpiarFormAgenda">
                                                <i class="fas fa-eraser"></i> Limpiar
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </form>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Novedades</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-pills flex-column" id="listaNovedades">
                            <li class="nav-item">
                                <a href="#" class="btn btn-block btn-outline-primary btn-sm" id="btnNuevaNovedad">
                                    <i class="fas fa-plus"></i> Agregar novedad
                                </a>
                            </li>
                            <!-- Las novedades se cargarán dinámicamente aquí -->
                        </ul>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->

                <!-- Calendario de Agenda -->
                <div class="card card-primary card-outline mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Calendario de Agenda</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Detalle de la agenda</h3>

                        <div class="card-tools">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="buscarAgenda" placeholder="Buscar agenda">
                                <div class="input-group-append">
                                    <div class="btn btn-primary" id="btnBuscarAgenda">
                                        <i class="fas fa-search"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-tools -->
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body p-0">
                        <div class="mailbox-controls">
                            <!-- Check all button -->
                            <button type="button" class="btn btn-default btn-sm checkbox-toggle"><i
                                    class="far fa-square"></i>
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm" id="btnEliminarAgendas">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                                <button type="button" class="btn btn-default btn-sm" id="btnExportarAgendas">
                                    <i class="fas fa-file-excel"></i>
                                </button>
                            </div>
                            <!-- /.btn-group -->
                            <button type="button" class="btn btn-default btn-sm" id="btnActualizarListaAgendas">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <div class="float-right">
                                <span id="paginacionInfo">1-50/200</span>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-sm" id="btnPaginaAnterior">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-default btn-sm" id="btnPaginaSiguiente">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <!-- /.btn-group -->
                            </div>
                            <!-- /.float-right -->
                        </div>
                        <div class="table-responsive mailbox-messages">
                            <table class="table table-hover table-striped" id="tablaAgendas">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Estado</th>
                                        <th>Médico</th>
                                        <th>Detalle</th>
                                        <th>Sala</th>
                                        <th>Última modificación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos de la agenda se cargarán dinámicamente aquí -->
                                </tbody>
                            </table>
                            <!-- /.table -->
                        </div>
                        <!-- /.mail-box-messages -->
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer p-0">
                        <div class="mailbox-controls">
                            <!-- Check all button -->
                            <button type="button" class="btn btn-default btn-sm checkbox-toggle">
                                <i class="far fa-square"></i>
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm" id="btnEliminarAgendasFooter">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                                <button type="button" class="btn btn-default btn-sm" id="btnExportarAgendasFooter">
                                    <i class="fas fa-file-excel"></i>
                                </button>
                            </div>
                            <!-- /.btn-group -->
                            <button type="button" class="btn btn-default btn-sm" id="btnActualizarListaAgendasFooter">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <div class="float-right">
                                <span id="paginacionInfoFooter">1-50/200</span>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-sm" id="btnPaginaAnteriorFooter">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-default btn-sm" id="btnPaginaSiguienteFooter">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <!-- /.btn-group -->
                            </div>
                            <!-- /.float-right -->
                        </div>
                    </div>
                </div>
                <!-- /.card -->

                <!-- Calendario de Agenda -->
                <div class="card card-primary card-outline mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Calendario de Agenda</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>

<!-- Modal para Nuevo Bloqueo -->
<div class="modal fade" id="modalNuevoBloqueo">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title">Nuevo Bloqueo de Agenda</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formNuevoBloqueo">
                    <div class="form-group">
                        <label for="bloqueoDoctor">Médico</label>
                        <select class="form-control select2" id="bloqueoDoctor" name="bloqueoDoctor"
                            style="width: 100%;" required>
                            <option value="">Seleccionar médico...</option>
                            <!-- Opciones de médicos se cargarán dinámicamente -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipoBloqueo">Tipo de Bloqueo</label>
                        <select class="form-control" id="tipoBloqueo" name="tipoBloqueo" required>
                            <option value="">Seleccionar tipo...</option>
                            <option value="1">Vacaciones</option>
                            <option value="2">Permiso</option>
                            <option value="3">Enfermedad</option>
                            <option value="4">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fechaBloqueo">Fecha</label>
                        <input type="text" class="form-control datepicker" id="fechaBloqueo" name="fechaBloqueo"
                            required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="horaInicioBloqueo">Hora Inicio</label>
                            <input type="text" class="form-control timepicker" id="horaInicioBloqueo"
                                name="horaInicioBloqueo" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="horaFinBloqueo">Hora Fin</label>
                            <input type="text" class="form-control timepicker" id="horaFinBloqueo" name="horaFinBloqueo"
                                required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnGuardarBloqueo">Guardar Bloqueo</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- Modal para Novedades -->
<div class="modal fade" id="modalNovedades" tabindex="-1" role="dialog" aria-labelledby="modalNovedadesLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovedadesLabel">Agregar Novedad</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formNovedades">
                    <input type="hidden" id="idNovedad" name="idNovedad">
                    <input type="hidden" id="idAgendaNovedad" name="idAgendaNovedad">

                    <div class="form-group">
                        <label for="tipoNovedad">Tipo de Novedad</label>
                        <select class="form-control" id="tipoNovedad" name="tipoNovedad" required>
                            <option value="">Seleccionar</option>
                            <option value="VACACIONES">Vacaciones</option>
                            <option value="REPOSO">Reposo</option>
                            <option value="REUNION">Reunión</option>
                            <option value="ALMUERZO">Almuerzo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="descripcionNovedad">Descripción</label>
                        <textarea class="form-control" id="descripcionNovedad" name="descripcionNovedad"
                            rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="fechaNovedad">Fecha</label>
                        <input type="date" class="form-control" id="fechaNovedad" name="fechaNovedad" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="horaInicioNovedad">Hora Inicio</label>
                            <input type="time" class="form-control" id="horaInicioNovedad" name="horaInicioNovedad"
                                required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="horaFinNovedad">Hora Fin</label>
                            <input type="time" class="form-control" id="horaFinNovedad" name="horaFinNovedad" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarNovedad">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- El script agendas.js ya se carga en template.php -->