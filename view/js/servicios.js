/**
 * Script para manejar el flujo de trabajo de reserva de servicios médicos
 * 
 * IMPORTANTE: Este archivo solo debe incluirse UNA VEZ en la aplicación.
 * Actualmente se incluye en view/modules/servicios.php
 * NO debe incluirse en template.php ni en ningún otro lugar.
 */

// Variables globales para el proceso de reserva
let fechaSeleccionada = '';
let proveedorSeleccionado = '';
let servicioSeleccionado = '';
let horarioSeleccionado = '';

// Inicializar cuando el documento esté listo
$(document).ready(function() {
    inicializarFechas();
    inicializarEventosCompactos();
});

/**
 * Inicializa los campos de fecha con la configuración adecuada
 */
function inicializarFechas() {
    // Inicializar datepicker para fecha de reserva
    $('#fechaReserva').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: 0,
        maxDate: '+3M',
        showOtherMonths: true,
        selectOtherMonths: true,
        changeMonth: true,
        changeYear: true
    });
}

/**
 * Inicializa los event handlers para los botones y controles en la interfaz compacta
 */
function inicializarEventosCompactos() {
    // Limpiar event handlers existentes para evitar duplicación
    $(document).off('click', '#btnBuscarDisponibilidad');
    $(document).off('click', '#btnCargarServicios');
    $(document).off('click', '#btnCargarHorarios');
    $(document).off('click', '#btnBuscarPaciente');
    $(document).off('click', '.slot-horario');
    $(document).off('submit', '#formReserva');

    // Evento para botón de búsqueda de disponibilidad (Paso 1)
    $(document).on('click', '#btnBuscarDisponibilidad', function() {
        fechaSeleccionada = $('#fechaReserva').val();
        
        if (!fechaSeleccionada) {
            mostrarAlerta('warning', 'Por favor, seleccione una fecha para continuar.');
            return;
        }
        
        // Validar que la fecha no sea anterior a hoy
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        const fechaSeleccionadaObj = new Date(fechaSeleccionada);
        fechaSeleccionadaObj.setHours(0, 0, 0, 0);
        
        if (fechaSeleccionadaObj < hoy) {
            mostrarAlerta('error', 'No puede seleccionar una fecha en el pasado.');
            $('#fechaReserva').val('');
            fechaSeleccionada = '';
            return;
        }
        
        // Mostrar la fecha seleccionada en el resumen
        $('#resumenFecha').text(formatearFechaParaMostrar(fechaSeleccionada));
        
        // Actualizar las reservas existentes para esta fecha
        cargarReservasDelDia(fechaSeleccionada);
        
        // Cargar médicos disponibles para esta fecha
        cargarMedicosDisponiblesPorFecha(fechaSeleccionada);
    });

    // Evento para cargar servicios cuando se selecciona un médico (Paso 2)
    $(document).on('click', '#btnCargarServicios', function() {
        proveedorSeleccionado = $('#selectProveedor').val();
        
        if (!proveedorSeleccionado) {
            mostrarAlerta('warning', 'Por favor, seleccione un médico para continuar.');
            return;
        }
        
        // Actualizar texto del médico seleccionado en el resumen
        const doctorTexto = $('#selectProveedor option:selected').text();
        $('#resumenMedico').text(doctorTexto);
        
        // Cargar servicios disponibles para este médico y fecha
        cargarServiciosPorFechaMedico(fechaSeleccionada, proveedorSeleccionado);
    });

    // Evento para cargar horarios cuando se selecciona un servicio (Paso 3)
    $(document).on('click', '#btnCargarHorarios', function() {
        servicioSeleccionado = $('#selectServicio').val();
        
        if (!servicioSeleccionado) {
            mostrarAlerta('warning', 'Por favor, seleccione un servicio para continuar.');
            return;
        }
        
        // Actualizar texto del servicio seleccionado en el resumen
        const servicioTexto = $('#selectServicio option:selected').text();
        $('#resumenServicio').text(servicioTexto);
        
        // Cargar horarios disponibles para este servicio, médico y fecha
        cargarHorariosDisponibles(servicioSeleccionado, proveedorSeleccionado, fechaSeleccionada);
    });

    // Evento para seleccionar un slot horario (Paso 4)
    $(document).on('click', '.slot-horario', function() {
        // Verificar si el slot está disponible
        if ($(this).hasClass('no-disponible')) {
            mostrarAlerta('warning', 'Este horario no está disponible.');
            return;
        }
        
        // Quitar selección anterior
        $('.slot-horario').removeClass('selected');
        
        // Seleccionar este slot
        $(this).addClass('selected');
        
        // Guardar los datos del horario seleccionado
        const slotId = $(this).data('id');
        const horaInicio = $(this).data('inicio');
        const horaFin = $(this).data('fin');
        const textoHorario = $(this).data('texto');
        const nombreSala = $(this).data('sala') || 'Sin sala asignada';
        
        console.log("Slot seleccionado - ID:", slotId, "Inicio:", horaInicio, "Fin:", horaFin); // Debug log
        
        horarioSeleccionado = slotId;
        $('#horaInicio').val(horaInicio);
        $('#horaFin').val(horaFin);
        
        // Actualizar el resumen
        $('#resumenHora').text(textoHorario);
        $('#resumenSala').text(nombreSala);
        
        // Habilitar el botón de búsqueda de paciente
        $('#btnBuscarPaciente').removeAttr('disabled');
    });

    // Evento para buscar pacientes
    $(document).on('click', '#btnBuscarPaciente', function() {
        const termino = $('#buscarPaciente').val();
        
        if (termino.length < 3) {
            mostrarAlerta('warning', 'Por favor, ingrese al menos 3 caracteres para buscar.');
            return;
        }
        
        buscarPacientes(termino);
    });
    
    // También buscar cuando se presiona Enter en el input
    $(document).on('keypress', '#buscarPaciente', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btnBuscarPaciente').click();
        }
    });
    
    // Evento para seleccionar un paciente
    $(document).on('click', '.btn-seleccionar-paciente', function() {
        const pacienteId = $(this).data('id');
        const pacienteNombre = $(this).data('nombre');
        
        $('#pacienteSeleccionado').val(pacienteId);
        $('#resultadosPacientes').html(`
            <div class="alert alert-success">
                <h5><i class="fas fa-user-check"></i> Paciente seleccionado:</h5>
                <p>${pacienteNombre}</p>
            </div>
        `);
    });
    
    // Evento para enviar el formulario de reserva
    $(document).on('submit', '#formReserva', function(e) {
        e.preventDefault();
        
        // Verificar que se hayan completado todos los pasos
        if (!fechaSeleccionada || !proveedorSeleccionado || !servicioSeleccionado || !$('#horaInicio').val() || !$('#pacienteSeleccionado').val()) {
            mostrarAlerta('error', 'Por favor, complete todos los pasos antes de guardar la reserva.');
            return;
        }
        
        // Recopilar los datos para la reserva
        const datos = {
            doctor_id: proveedorSeleccionado,
            servicio_id: servicioSeleccionado,
            paciente_id: $('#pacienteSeleccionado').val(),
            fecha_reserva: fechaSeleccionada,
            hora_inicio: $('#horaInicio').val(),
            hora_fin: $('#horaFin').val(),
            observaciones: $('#observaciones').val(),
            agenda_id: $('#agendaId').val() || null,
            tarifa_id: $('#tarifaId').val() || null
        };
        
        // Enviar la solicitud para guardar la reserva
        $.ajax({
            url: "ajax/servicios.ajax.php",
            method: "POST",
            data: { 
                action: "guardarReserva",
                ...datos
            },
            dataType: "json",
            beforeSend: function() {
                $('#btnGuardarReserva').html('<i class="fas fa-spinner fa-spin"></i> Guardando...').attr('disabled', true);
            },
            success: function(respuesta) {
                if (respuesta.status === "success") {
                    mostrarAlerta('success', 'Reserva guardada exitosamente');
                    
                    // Recargar la tabla de reservas
                    cargarReservasDelDia(fechaSeleccionada);
                    
                    // Limpiar el formulario
                    resetearFormularioReserva();
                } else {
                    mostrarAlerta('error', respuesta.message || 'Error al guardar la reserva');
                }
                
                $('#btnGuardarReserva').html('<i class="fas fa-save"></i> Guardar Reserva').attr('disabled', false);
            },
            error: function(xhr) {
                console.error(xhr);
                mostrarAlerta('error', 'Error al conectarse con el servidor');
                $('#btnGuardarReserva').html('<i class="fas fa-save"></i> Guardar Reserva').attr('disabled', false);
            }
        });
    });
}

/**
 * Resetea el formulario de reserva
 */
function resetearFormularioReserva() {
    // Limpiar variables globales
    fechaSeleccionada = '';
    proveedorSeleccionado = '';
    servicioSeleccionado = '';
    horarioSeleccionado = '';
    
    // Limpiar campos
    $('#fechaReserva').val('');
    $('#selectProveedor').html('<option value="">Seleccione un médico disponible</option>');
    $('#selectServicio').html('<option value="">Seleccione un servicio</option>');
    $('#contenedorHorarios').html('<p class="text-center text-muted">Seleccione fecha, médico y servicio para ver horarios disponibles</p>');
    $('#buscarPaciente').val('');
    $('#pacienteSeleccionado').val('');
    $('#observaciones').val('');
    $('#horaInicio').val('');
    $('#horaFin').val('');
    $('#agendaId').val('');
    $('#tarifaId').val('');
    
    // Resetear resumen
    $('#resumenFecha').text('-');
    $('#resumenMedico').text('-');
    $('#resumenServicio').text('-');
    $('#resumenHora').text('-');
    $('#resumenSala').text('-');
    
    // Limpiar resultados de pacientes
    $('#resultadosPacientes').html('');
}

/**
 * Cambia la visualización para mostrar un paso específico del proceso de reserva
 * @param {number} paso Número del paso a mostrar (1-4)
 */
function mostrarPasoReserva(paso) {
    // Ocultar todos los pasos
    $('.paso-reserva').hide();
    
    // Mostrar solo el paso indicado
    $('#paso' + paso).show();
    
    // Actualizar indicador de paso activo
    $('.step').removeClass('active');
    $('.step-' + paso).addClass('active');
}

/**
 * Carga los médicos disponibles para una fecha específica
 * @param {string} fecha Fecha en formato YYYY-MM-DD
 */
function cargarMedicosDisponiblesPorFecha(fecha) {
    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: { 
            action: "obtenerMedicosPorFecha",
            fecha: fecha
        },
        dataType: "json",
        beforeSend: function() {
            $('#selectProveedor').html('<option value="">Cargando médicos...</option>');
        },
        success: function(respuesta) {
            console.log("Respuesta de médicos:", respuesta); // Log para depuración
            
            if (respuesta.data && respuesta.data.length > 0) {
                let options = '<option value="">Seleccione un médico</option>';
                
                respuesta.data.forEach(function(medico) {
                    // Verificar qué propiedades trae el objeto médico
                    if (medico.doctor_id) {
                        // Si viene con doctor_id y nombre_doctor (formato de la API)
                        options += `<option value="${medico.doctor_id}">${medico.nombre_doctor}</option>`;
                    } else if (medico.id) {
                        // Si viene con id y nombre (formato antiguo)
                        options += `<option value="${medico.id}">${medico.nombre}</option>`;
                    } else if (medico.message) {
                        // Si es un mensaje de error/advertencia
                        console.warn("Mensaje desde API:", medico.message);
                        mostrarAlerta('warning', medico.message);
                    }
                });
                
                $('#selectProveedor').html(options);
            } else {
                $('#selectProveedor').html('<option value="">No hay médicos disponibles</option>');
                mostrarAlerta('warning', 'No hay médicos disponibles para la fecha seleccionada.');
            }
        },
        error: function(xhr) {
            console.error(xhr);
            $('#selectProveedor').html('<option value="">Error al cargar médicos</option>');
            mostrarAlerta('error', 'Error al cargar médicos disponibles.');
        }
    });
}

/**
 * Carga servicios disponibles para una fecha y médico específicos
 * @param {string} fecha Fecha en formato YYYY-MM-DD
 * @param {number} doctorId ID del médico
 */
function cargarServiciosPorFechaMedico(fecha, doctorId) {
    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: { 
            action: "obtenerServiciosPorFechaMedico",
            fecha: fecha,
            doctor_id: doctorId
        },
        dataType: "json",
        beforeSend: function() {
            $('#selectServicio').html('<option value="">Cargando servicios...</option>');
        },
        success: function(respuesta) {
            console.log("Respuesta de servicios:", respuesta); // Log para depuración
            
            if (respuesta.data && respuesta.data.length > 0) {
                let options = '<option value="">Seleccione un servicio</option>';
                
                respuesta.data.forEach(function(servicio) {
                    // Verificar qué propiedades trae el objeto servicio
                    if (servicio.servicio_id) {
                        // Si viene con servicio_id y servicio_nombre (formato de la API)
                        options += `<option value="${servicio.servicio_id}">${servicio.servicio_nombre}</option>`;
                    } else if (servicio.id) {
                        // Si viene con id y nombre (formato antiguo)
                        options += `<option value="${servicio.id}">${servicio.nombre}</option>`;
                    } else if (servicio.message) {
                        // Si es un mensaje de error/advertencia
                        console.warn("Mensaje desde API:", servicio.message);
                    }
                });
                
                $('#selectServicio').html(options);
            } else {
                $('#selectServicio').html('<option value="">No hay servicios disponibles</option>');
                mostrarAlerta('warning', 'El médico seleccionado no tiene servicios disponibles para esta fecha.');
            }
        },
        error: function(xhr) {
            console.error(xhr);
            $('#selectServicio').html('<option value="">Error al cargar servicios</option>');
            mostrarAlerta('error', 'Error al cargar servicios disponibles.');
        }
    });
}

/**
 * Carga las reservas existentes para una fecha específica
 * @param {string} fecha Fecha en formato YYYY-MM-DD
 */
function cargarReservasDelDia(fecha) {
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
            if (respuesta.data && respuesta.data.length > 0) {
                let filas = '';
                
                respuesta.data.forEach(function(reserva) {
                    // Determinar color según estado
                    let claseEstado = '';
                    switch (reserva.estado.toUpperCase()) {
                        case 'PENDIENTE': claseEstado = 'badge-warning'; break;
                        case 'CONFIRMADA': claseEstado = 'badge-success'; break;
                        case 'CANCELADA': claseEstado = 'badge-danger'; break;
                        case 'COMPLETADA': claseEstado = 'badge-info'; break;
                        default: claseEstado = 'badge-secondary';
                    }
                    
                    filas += `
                        <tr>
                            <td>${reserva.hora_inicio} - ${reserva.hora_fin}</td>
                            <td>${reserva.doctor_nombre}</td>
                            <td>${reserva.paciente_nombre}</td>
                            <td>${reserva.servicio_nombre}</td>
                            <td><span class="badge ${claseEstado}">${reserva.estado}</span></td>
                        </tr>
                    `;
                });
                
                $('#tablaReservasExistentes tbody').html(filas);
            } else {
                $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center">No hay reservas para esta fecha</td></tr>');
            }
        },
        error: function(xhr) {
            console.error(xhr);
            $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar reservas</td></tr>');
        }
    });
}

/**
 * Carga los horarios disponibles para un servicio, médico y fecha específicos
 * @param {number} servicioId ID del servicio
 * @param {number} doctorId ID del médico
 * @param {string} fecha Fecha en formato YYYY-MM-DD
 */
function cargarHorariosDisponibles(servicioId, doctorId, fecha) {
    console.log("Solicitando horarios - ServicioID:", servicioId, "DoctorID:", doctorId, "Fecha:", fecha);
    
    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: { 
            action: "generarSlotsDisponibles",
            servicio_id: servicioId,
            doctor_id: doctorId,
            fecha: fecha
        },
        dataType: "json",
        beforeSend: function() {
            $('#contenedorHorarios').html(`
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Cargando horarios disponibles...</p>
                </div>
            `);
        },
        success: function(respuesta) {
            console.log("Respuesta de slots:", respuesta); // Log para depuración
            
            if (respuesta.data && respuesta.data.length > 0) {
                // Construir la rejilla de slots horarios
                const slots = respuesta.data;
                let htmlSlots = '<div class="row">';
                
                slots.forEach(function(slot) {
                    // Determinar si el slot está disponible
                    const disponible = slot.disponible !== false; // Por defecto, asumimos disponible
                    const claseDisponibilidad = disponible ? '' : 'no-disponible';
                    
                    // Formatear las horas para mostrar (HH:MM)
                    // Manejar diferentes formatos de respuesta de la API
                    let horaInicio = '??:??';
                    let horaFin = '??:??';
                    
                    if (slot.hora_inicio) {
                        horaInicio = slot.hora_inicio.substring(0, 5); // Formato original
                    } else if (slot.inicio) {
                        horaInicio = slot.inicio.substring(0, 5); // Formato alternativo
                    } else if (slot.start_time) {
                        horaInicio = slot.start_time.substring(0, 5); // Otro formato posible
                    }
                    
                    if (slot.hora_fin) {
                        horaFin = slot.hora_fin.substring(0, 5); // Formato original
                    } else if (slot.fin) {
                        horaFin = slot.fin.substring(0, 5); // Formato alternativo
                    } else if (slot.end_time) {
                        horaFin = slot.end_time.substring(0, 5); // Otro formato posible
                    }
                    
                    // Nombre de la sala
                    const nombreSala = slot.sala_nombre || 'Sin sala asignada';
                    
                    htmlSlots += `
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="slot-horario ${claseDisponibilidad}" 
                                 data-id="${slot.horario_id || slot.id || ''}"
                                 data-inicio="${slot.hora_inicio || slot.inicio || slot.start_time || ''}"
                                 data-fin="${slot.hora_fin || slot.fin || slot.end_time || ''}"
                                 data-texto="${horaInicio} - ${horaFin}"
                                 data-sala="${nombreSala}">
                                <p class="mb-1 text-center"><strong>${horaInicio} - ${horaFin}</strong></p>
                                <p class="mb-0 text-center"><small>${nombreSala}</small></p>
                            </div>
                        </div>
                    `;
                });
                
                htmlSlots += '</div>';
                $('#contenedorHorarios').html(htmlSlots);
            } else {
                $('#contenedorHorarios').html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No hay horarios disponibles para la combinación seleccionada.
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error("Error al cargar horarios:", xhr);
            $('#contenedorHorarios').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Error al cargar horarios. Por favor, intente nuevamente.
                </div>
            `);
        }
    });
}

/**
 * Busca pacientes según los criterios ingresados en el formulario
 * @param {string} termino Término de búsqueda (nombre o documento)
 */
function buscarPacientes(termino) {
    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: { 
            action: "buscarPaciente",
            termino: termino
        },
        dataType: "json",
        beforeSend: function() {
            $('#resultadosPacientes').html(`
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Buscando pacientes...</p>
                </div>
            `);
        },
        success: function(respuesta) {
            if (respuesta.data && respuesta.data.length > 0) {
                let html = '<div class="list-group">';
                
                respuesta.data.forEach(function(paciente) {
                    html += `
                        <a href="#" class="list-group-item list-group-item-action btn-seleccionar-paciente" 
                           data-id="${paciente.id}" 
                           data-nombre="${paciente.nombre}">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">${paciente.nombre}</h5>
                                <small>${paciente.documento}</small>
                            </div>
                            <p class="mb-1">${paciente.email || 'Sin email'}</p>
                            <small>${paciente.telefono || 'Sin teléfono'}</small>
                        </a>
                    `;
                });
                
                html += '</div>';
                $('#resultadosPacientes').html(html);
            } else {
                $('#resultadosPacientes').html(`
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No se encontraron pacientes con el criterio de búsqueda "${termino}".
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error(xhr);
            $('#resultadosPacientes').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Error al buscar pacientes. Por favor, intente nuevamente.
                </div>
            `);
        }
    });
}

/**
 * Formatea una fecha YYYY-MM-DD para mostrarla en formato más amigable
 * @param {string} fecha Fecha en formato YYYY-MM-DD
 * @returns {string} Fecha formateada como "Día de Mes de Año"
 */
function formatearFechaParaMostrar(fecha) {
    if (!fecha) return '';
    
    const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    const diasSemana = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    
    const fechaObj = new Date(fecha);
    const diaSemana = diasSemana[fechaObj.getDay()];
    const dia = fechaObj.getDate();
    const mes = meses[fechaObj.getMonth()];
    const anio = fechaObj.getFullYear();
    
    return `${diaSemana} ${dia} de ${mes} de ${anio}`;
}

/**
 * Muestra una alerta usando Toastr o SweetAlert2
 * @param {string} tipo Tipo de alerta: 'success', 'info', 'warning', 'error'
 * @param {string} mensaje Mensaje a mostrar
 */
function mostrarAlerta(tipo, mensaje) {
    // Si existe Toastr
    if (typeof toastr !== 'undefined') {
        toastr[tipo](mensaje);
        return;
    }
    
    // Si existe SweetAlert2
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: tipo,
            title: tipo === 'success' ? 'Éxito' : tipo === 'info' ? 'Información' : tipo === 'warning' ? 'Advertencia' : 'Error',
            text: mensaje,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
        return;
    }
    
    // Fallback a alert básico
    alert(mensaje);
}
