/**
 * Archivo: agendas.js
 * Descripción: Gestión de agendas médicas y horarios
 */

$(document).ready(function() {
    // Variables globales
    let medicoSeleccionado = 0;
    let agendaSeleccionada = 0;
    let detalleSeleccionado = 0;
    let calendar = null;
    
    // Inicialización
    cargarMedicos();
    cargarAgendas();
    inicializarEventos();
    
    /**
     * Inicializa los eventos de la interfaz
     */
    function inicializarEventos() {

        // Remover eventos primero para prevenir duplicación
        $(document).off('change', '#selectMedico');
        $(document).off('click', '#btnNuevaAgenda');
        $(document).off('click', '.btnEditarAgenda');
        $(document).off('click', '.btnEliminarAgenda');
        $(document).off('submit', '#formAgenda');
        $(document).off('click', '.btnVerDetalles');
        $(document).off('click', '#btnNuevoDetalle');
        $(document).off('click', '.btnEditarDetalle');
        $(document).off('click', '.btnEliminarDetalle');
        $(document).off('submit', '#formDetalle');
        $('a[data-toggle="tab"]').off('shown.bs.tab');
        
        // Evento para seleccionar médico
        $(document).on('change', '#selectMedico', function() {
            medicoSeleccionado = $(this).val();
            if (medicoSeleccionado > 0) {
                cargarAgendasPorMedico(medicoSeleccionado);
            } else {
                cargarAgendas();
            }
        });
        
        // Evento para abrir modal de nueva agenda
        $(document).on('click', '#btnNuevaAgenda', function() {
            limpiarFormularioAgenda();
            $('#modalAgenda').modal('show');
            cargarMedicos();
        });
        
        // Evento para editar agenda
        $(document).on('click', '.btnEditarAgenda', function() {
            const agendaId = $(this).data('id');
            cargarDatosAgenda(agendaId);
        });
        
        // Evento para eliminar agenda
        $(document).on('click', '.btnEliminarAgenda', function() {
            const agendaId = $(this).data('id');
            confirmarEliminarAgenda(agendaId);
        });
        
        // Evento para guardar agenda
        $(document).on('submit', '#formAgenda', function(e) {
            e.preventDefault();
            guardarAgenda();
        });
        
        // Evento para ver detalles de agenda
        $(document).on('click', '.btnVerDetalles', function() {
            agendaSeleccionada = $(this).data('id');
            cargarDetallesAgenda(agendaSeleccionada);
            $('#tabDetalles').tab('show');
        });
        
        // Evento para abrir modal de nuevo detalle
        $(document).on('click', '#btnNuevoDetalle', function() {
            if (agendaSeleccionada > 0) {
                limpiarFormularioDetalle();
                cargarTurnos();
                cargarSalas();
                $('#modalDetalle').modal('show');
            } else {
                Swal.fire('Atención', 'Debe seleccionar una agenda primero', 'warning');
            }
        });
        
        // Evento para editar detalle
        $(document).on('click', '.btnEditarDetalle', function() {
            const detalleId = $(this).data('id');
            cargarDatosDetalle(detalleId);
        });
        
        // Evento para eliminar detalle
        $(document).on('click', '.btnEliminarDetalle', function() {
            const detalleId = $(this).data('id');
            confirmarEliminarDetalle(detalleId);
        });
        
        // Evento para guardar detalle
        $(document).on('submit', '#formDetalle', function(e) {
            e.preventDefault();
            guardarDetalleAgenda();
        });
        
        // Evento para cambiar entre pestañas
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            const target = $(e.target).attr("href");
            if (target === "#tabCalendario" && agendaSeleccionada > 0) {
                inicializarCalendario();
            }
        });
    }
    
    /**
     * Carga la lista de médicos disponibles
     */
    function cargarMedicos() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "obtenerMedicos" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    console.log("medicos",respuesta.data);
                    let options = '<option value="0">Seleccione un médico</option>';
                    respuesta.data.forEach(medico => {
                        options += `<option value="${medico.doctor_id}">${medico.nombre_completo}</option>`;
                    });
                    $('#selectMedico').html(options);
                    $('#medicoIdModal').html(options);
                }
            },
            error: function(xhr) {
                console.error("Error al cargar médicos:", xhr.responseText);
                Swal.fire('Error', 'No se pudieron cargar los médicos', 'error');
            }
        });
    }
    
    /**
     * Carga todas las agendas médicas
     */
    function cargarAgendas() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "obtenerAgendas" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    mostrarTablaAgendas(respuesta.data);
                }
            },
            error: function(xhr) {
                console.error("Error al cargar agendas:", xhr.responseText);
                Swal.fire('Error', 'No se pudieron cargar las agendas', 'error');
            }
        });
    }
    
    /**
     * Carga agendas por médico seleccionado
     */
    function cargarAgendasPorMedico(medicoId) {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "obtenerAgendasPorMedico",
                medico_id: medicoId 
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    mostrarTablaAgendas(respuesta.data);
                }
            },
            error: function(xhr) {
                console.error("Error al cargar agendas por médico:", xhr.responseText);
                Swal.fire('Error', 'No se pudieron cargar las agendas del médico', 'error');
            }
        });
    }
    
    /**
     * Muestra la tabla de agendas con los datos recibidos
     */
    function mostrarTablaAgendas(agendas) {
        let html = '';
        
        if (agendas.length === 0) {
            html = `<tr><td colspan="5" class="text-center">No hay agendas registradas</td></tr>`;
        } else {
            agendas.forEach(agenda => {
                const estado = agenda.agenda_estado == 1 ? 
                    '<span class="badge badge-success">Activa</span>' : 
                    '<span class="badge badge-danger">Inactiva</span>';
                
                html += `
                <tr>
                    <td>${agenda.agenda_id}</td>
                    <td>${agenda.nombre_medico}</td>
                    <td>${agenda.agenda_descripcion}</td>
                    <td>${estado}</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-info btn-sm btnVerDetalles" data-id="${agenda.agenda_id}" title="Ver detalles">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                            <button class="btn btn-warning btn-sm btnEditarAgenda" data-id="${agenda.agenda_id}" title="Editar agenda">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btnEliminarAgenda" data-id="${agenda.agenda_id}" title="Eliminar agenda">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                `;
            });
        }
        
        $('#tablaAgendas tbody').html(html);
    }
    
    /**
     * Carga los datos de una agenda para edición
     */
    function cargarDatosAgenda(agendaId) {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "obtenerAgendaPorId",
                agenda_id: agendaId 
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    const agenda = respuesta.data;
                    console.log("agenda",agenda);
                    $('#agendaId').val(agenda.agenda_id);
                    $('#medicoIdModal').val(agenda.medico_id);
                    $('#descripcionAgenda').val(agenda.agenda_descripcion);
                    $('#estadoAgenda').prop('checked', agenda.agenda_estado == 1);
                    $('#modalAgenda').modal('show');
                }
            },
            error: function(xhr) {
                console.error("Error al cargar datos de agenda:", xhr.responseText);
                Swal.fire('Error', 'No se pudieron cargar los datos de la agenda', 'error');
            }
        });
    }
    
    /**
     * Guarda una agenda (crear o actualizar)
     */
    function guardarAgenda() {
        const datos = {
            action: "guardarAgenda",
            agenda_id: $('#agendaId').val(),
            medico_id: $('#medicoIdModal').val(),
            agenda_descripcion: $('#descripcionAgenda').val(),
            agenda_estado: $('#estadoAgenda').is(':checked')
        };
        
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: datos,
            dataType: "json",
            success: function(respuesta) {
                if (!respuesta.error) {
                    Swal.fire('Éxito', respuesta.mensaje, 'success');
                    $('#modalAgenda').modal('hide');
                    if (medicoSeleccionado > 0) {
                        cargarAgendasPorMedico(medicoSeleccionado);
                    } else {
                        cargarAgendas();
                    }
                } else {
                    Swal.fire('Error', respuesta.mensaje, 'error');
                }
            },
            error: function(xhr) {
                console.error("Error al guardar agenda:", xhr.responseText);
                Swal.fire('Error', 'No se pudo guardar la agenda', 'error');
            }
        });
    }
    
    /**
     * Confirma la eliminación de una agenda
     */
    function confirmarEliminarAgenda(agendaId) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción eliminará la agenda y todos sus horarios asociados",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarAgenda(agendaId);
            }
        });
    }
    
    /**
     * Elimina una agenda
     */
    function eliminarAgenda(agendaId) {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "eliminarAgenda",
                agenda_id: agendaId 
            },
            dataType: "json",
            success: function(respuesta) {
                if (!respuesta.error) {
                    Swal.fire('Eliminada', respuesta.mensaje, 'success');
                    if (medicoSeleccionado > 0) {
                        cargarAgendasPorMedico(medicoSeleccionado);
                    } else {
                        cargarAgendas();
                    }
                } else {
                    Swal.fire('Error', respuesta.mensaje, 'error');
                }
            },
            error: function(xhr) {
                console.error("Error al eliminar agenda:", xhr.responseText);
                Swal.fire('Error', 'No se pudo eliminar la agenda', 'error');
            }
        });
    }
    
    /**
     * Limpia el formulario de agenda
     */
    function limpiarFormularioAgenda() {
        $('#agendaId').val('');
        $('#medicoId').val(medicoSeleccionado);
        $('#descripcionAgenda').val('');
        $('#estadoAgenda').prop('checked', true);
    }
    
    /**
     * Carga los detalles de horarios de una agenda
     */
    function cargarDetallesAgenda(agendaId) {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "obtenerDetallesAgenda",
                agenda_id: agendaId 
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    mostrarTablaDetalles(respuesta.data);
                }
            },
            error: function(xhr) {
                console.error("Error al cargar detalles de agenda:", xhr.responseText);
                Swal.fire('Error', 'No se pudieron cargar los detalles de la agenda', 'error');
            }
        });
    }
    
    /**
     * Muestra la tabla de detalles de horarios
     */
    function mostrarTablaDetalles(detalles) {
        let html = '';
        
        if (detalles.length === 0) {
            html = `<tr><td colspan="7" class="text-center">No hay horarios registrados para esta agenda</td></tr>`;
        } else {
            const diasSemana = ['DOMINGO', 'LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];
            
            detalles.forEach(detalle => {
                const estado = detalle.detalle_estado == 1 ? 
                    '<span class="badge badge-success">Activo</span>' : 
                    '<span class="badge badge-danger">Inactivo</span>';
                
                html += `
                <tr>
                    <td>${detalle.dia_semana}</td>
                    <td>${detalle.nombre_turno}</td>
                    <td>${detalle.nombre_sala}</td>
                    <td>${detalle.hora_inicio}</td>
                    <td>${detalle.hora_fin}</td>
                    <td>${detalle.intervalo_minutos}</td>
                    <td>${estado}</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-warning btn-sm btnEditarDetalle" data-id="${detalle.detalle_id}" title="Editar horario">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btnEliminarDetalle" data-id="${detalle.detalle_id}" title="Eliminar horario">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                `;
            });
        }
        
        $('#tablaDetalles tbody').html(html);
    }
    
    /**
     * Carga la lista de turnos disponibles
     */
    function cargarTurnos() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "obtenerTurnos" },
            dataType: "json",
            success: function(respuesta) {
                console.log("turnos",respuesta.data);
                if (respuesta.status === "success") {
                    let options = '<option value="">Seleccione un turno</option>';
                    respuesta.data.forEach(turno => {
                        options += `<option value="${turno.turno_id}">${turno.turno_nombre}</option>`;
                    });
                    $('#turnoId').html(options);
                }
            },
            error: function(xhr) {
                console.error("Error al cargar turnos:", xhr.responseText);
            }
        });
    }
    
    /**
     * Carga la lista de salas disponibles
     */
    function cargarSalas() {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { action: "obtenerSalas" },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    let options = '<option value="">Seleccione una sala</option>';
                    respuesta.data.forEach(sala => {
                        options += `<option value="${sala.sala_id}">${sala.sala_nombre}</option>`;
                    });
                    $('#salaId').html(options);
                }
            },
            error: function(xhr) {
                console.error("Error al cargar salas:", xhr.responseText);
            }
        });
    }
    
    /**
     * Carga los datos de un detalle para edición
     */
    function cargarDatosDetalle(detalleId) {
        detalleSeleccionado = detalleId;
        
        // Primero cargar turnos y salas
        cargarTurnos();
        cargarSalas();
        
        // Luego cargar los datos del detalle
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "obtenerDetalleAgenda",
                detalle_id: detalleId 
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    const detalle = respuesta.data;
                    // Convertir el día de texto a número
                    const numeroDia = convertirDiaSemanaANumero(detalle.dia_semana);
                    $('#detalleId').val(detalle.detalle_id);
                    $('#agendaIdDetalle').val(detalle.agenda_id);
                    $('#diaSemana').val(numeroDia);
                    $('#turnoId').val(detalle.turno_id);
                    $('#salaId').val(detalle.sala_id);
                    $('#horaInicio').val(detalle.hora_inicio);
                    $('#horaFin').val(detalle.hora_fin);
                    $('#intervaloMinutos').val(detalle.intervalo_minutos);
                    $('#cupoMaximo').val(detalle.cupo_maximo);
                    $('#estadoDetalle').prop('checked', detalle.detalle_estado == 1);
                    $('#modalDetalle').modal('show');
                }
            },
            error: function(xhr) {
                console.error("Error al cargar datos del detalle:", xhr.responseText);
                Swal.fire('Error', 'No se pudieron cargar los datos del horario', 'error');
            }
        });
    }



    function convertirDiaSemanaANumero(diaSemana) {
        const dias = {
            'DOMINGO': 0,
            'LUNES': 1,
            'MARTES': 2,
            'MIERCOLES': 3,
            'MIÉRCOLES': 3,
            'JUEVES': 4,
            'VIERNES': 5,
            'SABADO': 6,
            'SÁBADO': 6
        };
        
        // Convertir a mayúsculas y eliminar acentos para hacer la comparación más robusta
        const diaFormateado = diaSemana.toUpperCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
        
        return dias[diaFormateado] !== undefined ? dias[diaFormateado] : -1;
    }
    
    /**
     * Guarda un detalle de horario (crear o actualizar)
     */
    function guardarDetalleAgenda() {
        const datos = {
            action: "guardarDetalleAgenda",
            detalle_id: $('#detalleId').val(),
            agenda_id: agendaSeleccionada,
            dia_semana: $('#diaSemana').val(),
            turno_id: $('#turnoId').val(),
            sala_id: $('#salaId').val(),
            hora_inicio: $('#horaInicio').val(),
            hora_fin: $('#horaFin').val(),
            intervalo_minutos: $('#intervaloMinutos').val(),
            cupo_maximo: $('#cupoMaximo').val(),
            detalle_estado: $('#estadoDetalle').is(':checked')
        };
        
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: datos,
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.error == false) {
                    Swal.fire('Éxito', "Se creo de forma exitosa", 'success');
                    $('#modalDetalle').modal('hide');
                    cargarDetallesAgenda(agendaSeleccionada);
                } else {
                    Swal.fire('Error', "error al crear el detallees", 'error al crear el detallee');
                }
            },
            error: function(xhr) {
                console.error("Error al guardar detalle:", xhr.responseText);
                Swal.fire('Error', 'No se pudo guardar el horario', 'error');
            }
        });
    }
    
    /**
     * Confirma la eliminación de un detalle
     */
    function confirmarEliminarDetalle(detalleId) {
        Swal.fire({
            title: '¿Está seguro?',
            text: "Esta acción eliminará el horario seleccionado",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarDetalleAgenda(detalleId);
            }
        });
    }
    
    /**
     * Elimina un detalle de horario
     */
    function eliminarDetalleAgenda(detalleId) {
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "eliminarDetalleAgenda",
                detalle_id: detalleId 
            },
            dataType: "json",
            success: function(respuesta) {
                if (!respuesta.error) {
                    Swal.fire('Eliminado', respuesta.mensaje, 'success');
                    cargarDetallesAgenda(agendaSeleccionada);
                } else {
                    Swal.fire('Error', respuesta.mensaje, 'error');
                }
            },
            error: function(xhr) {
                console.error("Error al eliminar detalle:", xhr.responseText);
                Swal.fire('Error', 'No se pudo eliminar el horario', 'error');
            }
        });
    }
    
    /**
     * Limpia el formulario de detalle
     */
    function limpiarFormularioDetalle() {
        $('#detalleId').val('');
        $('#agendaIdDetalle').val(agendaSeleccionada);
        $('#diaSemana').val('1'); // Lunes por defecto
        $('#turnoId').val('');
        $('#salaId').val('');
        $('#horaInicio').val('08:00');
        $('#horaFin').val('12:00');
        $('#intervaloMinutos').val('15');
        $('#cupoMaximo').val('1');
        $('#estadoDetalle').prop('checked', true);
    }
    
    /**
     * Inicializa el calendario con los horarios de la agenda
     */
    function inicializarCalendario() {
        if (calendar) {
            calendar.destroy();
        }
        
        const calendarEl = document.getElementById('calendar');
        
        // Obtener los detalles de la agenda para mostrar en el calendario
        $.ajax({
            url: "ajax/agendas.ajax.php",
            method: "POST",
            data: { 
                action: "obtenerDetallesAgenda",
                agenda_id: agendaSeleccionada 
            },
            dataType: "json",
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    const eventos = generarEventosCalendario(respuesta.data);
                    
                    calendar = new FullCalendar.Calendar(calendarEl, {
                        locale: 'es',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        initialView: 'timeGridWeek',
                        slotMinTime: '07:00:00',
                        slotMaxTime: '20:00:00',
                        events: eventos,
                        eventTimeFormat: {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false
                        }
                    });
                    
                    calendar.render();
                }
            },
            error: function(xhr) {
                console.error("Error al cargar detalles para calendario:", xhr.responseText);
            }
        });
    }
    
    /**
     * Genera los eventos para el calendario a partir de los detalles de la agenda
     */
    function generarEventosCalendario(detalles) {
        const eventos = [];
        const colores = [
            '#3788d8', '#28a745', '#dc3545', '#ffc107', '#17a2b8',
            '#6610f2', '#fd7e14', '#20c997', '#e83e8c', '#6f42c1'
        ];
        
        detalles.forEach((detalle, index) => {
            if (detalle.detalle_estado == 1) {
                // Obtener el color según el turno o sala
                const colorIndex = (detalle.turno_id % colores.length);
                const color = colores[colorIndex];
                
                // Crear evento recurrente para cada día de la semana
                eventos.push({
                    title: `${detalle.nombre_turno} - ${detalle.nombre_sala}`,
                    startTime: detalle.hora_inicio,
                    endTime: detalle.hora_fin,
                    daysOfWeek: [detalle.dia_semana],
                    backgroundColor: color,
                    borderColor: color,
                    extendedProps: {
                        detalle_id: detalle.detalle_id,
                        intervalo_minutos: detalle.intervalo_minutos,
                        cupo_maximo: detalle.cupo_maximo
                    }
                });
            }
        });
        
        return eventos;
    }
});