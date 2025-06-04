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
    console.log("Inicializando servicios.js");
    inicializarFechas();
    inicializarEventosCompactos();
    manejarSeleccionFechaDirecta();
      // Inicializar la pestaña de Reservas con un pequeño retraso para asegurar que el DOM esté listo
    setTimeout(function() {
        console.log("Inicializando tab Reservas");
        inicializarTabReservas();
        
        // Verificar si estamos en la pestaña de reservas
        if (window.location.hash === '#tabReservas' || $('.nav-link.active').attr('href') === '#tabReservas') {
            console.log("Estamos en la pestaña de reservas - inicializando inmediatamente");
            cargarMedicosParaFiltroReservas();
            buscarReservas();
        }
    }, 500);
    
    // Inicializar filtros si ya hay una fecha seleccionada
    if ($('#fechaReserva').val()) {
        const fechaActual = $('#fechaReserva').val();
        cargarDoctoresParaFiltro(fechaActual);
        cargarReservasDelDia(fechaActual);
    }
      // Manejar cambio de pestaña para cargar datos cuando se activa la pestaña de Reservas
    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
        const targetTab = $(e.target).attr('href');
        console.log("Cambio a pestaña:", targetTab);
        
        if (targetTab === '#tabReservas') {
            console.log("Recargando datos de reservas...");
            // Limpiamos selectores para evitar problemas de caché
            $('#selectMedicoReservas').empty();
            $('#selectEstadoReserva').val('0');
            
            setTimeout(function() {
                // Cargar los médicos primero
                $.ajax({
                    url: "ajax/servicios.ajax.php",
                    method: "POST",
                    data: { action: "obtenerMedicos" },
                    dataType: "json",
                    success: function(respuesta) {
                        console.log("Médicos cargados manualmente:", respuesta);
                        
                        if (respuesta.status === "success" && respuesta.data) {
                            let opciones = '<option value="0">Todos los médicos</option>';
                            
                            respuesta.data.forEach(function(medico) {
                                const nombre = medico.nombre_doctor || `Dr. ${medico.doctor_id}`;
                                opciones += `<option value="${medico.doctor_id}">${nombre}</option>`;
                            });
                            
                            $('#selectMedicoReservas').html(opciones);
                            
                            // Una vez cargados los médicos, ahora sí buscar reservas
                            buscarReservas();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error cargando médicos:", error);
                        buscarReservas(); // Intentar buscar reservas de todas formas
                    }
                });
            }, 200);
        }
    });
    
    // Debug: Mostrar información de fecha/hora en la consola para ayudar con la depuración
    console.log("Información de fecha/hora del sistema:");
    const ahora = new Date();
    console.log("- Date.now():", Date.now());
    console.log("- new Date():", ahora);
    console.log("- Fecha ISO:", ahora.toISOString());
    console.log("- Fecha local:", ahora.toLocaleString());
    console.log("- Timezone offset (minutos):", ahora.getTimezoneOffset());
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
        changeYear: true,
        onSelect: function(dateText) {
            // Asegurar formato correcto cuando se selecciona una fecha
            console.log("Fecha seleccionada desde datepicker:", dateText);
            // Trigger cambio para que se ejecuten los validadores
            $(this).change();
        }
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
    $(document).off('click', '#btnConfirmarSeguro');
    $(document).off('change', '#tieneSeguro');
    $(document).off('submit', '#formReserva');
    $(document).off('change', '#selectProveedor');
    $(document).off('change', '#selectServicio');

    // Evento para botón de búsqueda de disponibilidad (Paso 1)
    $(document).on('click', '#btnBuscarDisponibilidad', function() {
        fechaSeleccionada = $('#fechaReserva').val();
        
        if (!fechaSeleccionada) {
            mostrarAlerta('warning', 'Por favor, seleccione una fecha para continuar.');
            return;
        }
        
        console.log("Validando fecha seleccionada:", fechaSeleccionada);
        
        try {
            // Usar la fecha actual al inicio del día (00:00:00)
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            
            // Obtener año, mes y día de hoy
            const anioHoy = hoy.getFullYear();
            const mesHoy = hoy.getMonth();
            const diaHoy = hoy.getDate();
            
            // Parsear la fecha seleccionada
            // El formato datepicker es 'yy-mm-dd' (ej: 2025-05-29)
            const partesFecha = fechaSeleccionada.split('-');
            if (partesFecha.length !== 3) {
                console.error("Formato de fecha incorrecto:", fechaSeleccionada);
                mostrarAlerta('error', 'El formato de fecha no es válido (debe ser YYYY-MM-DD).');
                return;
            }
            
            const anioSeleccionado = parseInt(partesFecha[0], 10);
            const mesSeleccionado = parseInt(partesFecha[1], 10) - 1; // Meses en JS son 0-11
            const diaSeleccionado = parseInt(partesFecha[2], 10);
            
            console.log("Fecha actual:", anioHoy, mesHoy, diaHoy);
            console.log("Fecha seleccionada:", anioSeleccionado, mesSeleccionado, diaSeleccionado);
            
            // Verificar si la fecha es anterior a hoy
            if (anioSeleccionado < anioHoy || 
                (anioSeleccionado === anioHoy && mesSeleccionado < mesHoy) || 
                (anioSeleccionado === anioHoy && mesSeleccionado === mesHoy && diaSeleccionado < diaHoy)) {
                
                console.log("La fecha seleccionada es anterior a hoy");
                mostrarAlerta('error', 'No puede seleccionar una fecha en el pasado, intente de nuevo.');
                $('#fechaReserva').val('');
                fechaSeleccionada = '';
                return;
            }
            
            console.log("La fecha seleccionada es hoy o posterior");
            
            // Si llegamos aquí, la fecha es válida (es hoy o posterior)
        } catch (error) {
            console.error("Error al validar fecha:", error);
            mostrarAlerta('error', 'Error al validar la fecha seleccionada.');
            return;
        }
        
        // Mostrar la fecha seleccionada en el resumen
        $('#resumenFecha').text(formatearFechaParaMostrar(fechaSeleccionada));
        
        // Actualizar las reservas existentes para esta fecha
        cargarReservasDelDia(fechaSeleccionada);
        
        // Cargar médicos disponibles para esta fecha
        cargarMedicosDisponiblesPorFecha(fechaSeleccionada);
    });

    // Evento automático para cargar servicios cuando cambia la selección de médico
    $(document).on('change', '#selectProveedor', function() {
        proveedorSeleccionado = $(this).val();
        
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

    // Evento automático para cargar horarios cuando cambia la selección de servicio
    $(document).on('change', '#selectServicio', function() {
        servicioSeleccionado = $(this).val();
        
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

    // Evento para cargar servicios cuando se selecciona un médico (Paso 2) - Mantener por compatibilidad
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

    // Evento para cargar horarios cuando se selecciona un servicio (Paso 3) - Mantener por compatibilidad
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
    });    // Evento para seleccionar un slot horario (Paso 4)
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
        const slotId = $(this).attr('data-id');
        const horaInicio = $(this).attr('data-inicio');
        const horaFin = $(this).attr('data-fin');
        const textoHorario = $(this).attr('data-texto');
        const nombreSala = $(this).attr('data-sala') || 'Sin sala asignada';
        
        // El data-id es en realidad el agenda_id, según lo indicado por el usuario
        const agendaId = slotId;
        
        console.log("=== DATOS DEL SLOT SELECCIONADO ===");
        console.log("Slot ID:", slotId);
        console.log("Hora inicio:", horaInicio);
        console.log("Hora fin:", horaFin);
        console.log("Texto horario:", textoHorario);
        console.log("Sala:", nombreSala);
        console.log("Agenda ID (usando slotId como agenda_id):", agendaId);
        console.log("Elemento HTML:", $(this)[0].outerHTML);
        console.log("================================");
        
        horarioSeleccionado = slotId;
        $('#horaInicio').val(horaInicio);
        $('#horaFin').val(horaFin);
        $('#agendaId').val(agendaId); // Asignar el mismo valor de slotId a agendaId
        
        console.log("Valor asignado al campo #agendaId:", $('#agendaId').val());
        
        // Actualizar el resumen
        $('#resumenHora').text(textoHorario);
        $('#resumenSala').text(nombreSala);
        
        // Habilitar el botón de búsqueda de paciente
        $('#btnBuscarPaciente').removeAttr('disabled');
    });
    
    // Evento para mostrar/ocultar selector de seguro médico
    $(document).on('change', '#tieneSeguro', function() {
        const tieneSeguro = $(this).val();
          if (tieneSeguro === 'si') {
            $('#selectSeguroContainer').slideDown();
            // Cargar la lista de proveedores de seguro médico
            cargarProveedoresSeguro();
            // Limpiar el resumen
            $('#resumenSeguro').text('-');
        } else {
            $('#selectSeguroContainer').slideUp();
            // Limpiar selección de seguro
            $('#seguroSeleccionado').val('');
            $('#selectSeguro').val('');
            // Actualizar el resumen
            $('#resumenSeguro').text('No aplica');
            
            // Mostrar confirmación
            $('#selectSeguroContainer').hide();
            if (!$('#noSeguroMessage').length) {
                $('#tieneSeguro').after(
                    `<div id="noSeguroMessage" class="alert alert-info mt-2">
                        <i class="fas fa-info-circle"></i> El paciente no utilizará seguro médico.
                    </div>`
                );
            }
            
            // Desactivar el selector para evitar cambios
            $('#tieneSeguro').attr('disabled', true);
        }
    });
      // Evento para confirmar el seguro seleccionado
    $(document).on('click', '#btnConfirmarSeguro', function() {
        const seguroId = $('#selectSeguro').val();
        
        if (seguroId) {
            $('#seguroSeleccionado').val(seguroId);
            const seguroTexto = $('#selectSeguro option:selected').text();
            
            // Mostrar confirmación
            $('#selectSeguroContainer').append(
                `<div class="alert alert-success mt-2">
                    <i class="fas fa-check-circle"></i> Seguro médico seleccionado: <strong>${seguroTexto}</strong>
                </div>`
            );
            
            // Actualizar el resumen
            $('#resumenSeguro').text(seguroTexto);
            
            // Desactivar el selector y el botón para evitar cambios
            $('#tieneSeguro').attr('disabled', true);
            $('#selectSeguro').attr('disabled', true);
            $(this).attr('disabled', true);
            
        } else {
            mostrarAlerta('warning', 'Por favor, seleccione un seguro médico.');
        }
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
        
        // Verificar el valor de agendaId antes de crear el objeto de datos
        console.log("=== VALORES DE CAMPOS OCULTOS ANTES DE ENVIAR ===");
        console.log("agendaId field value:", $('#agendaId').val());
        console.log("tarifaId field value:", $('#tarifaId').val());
        console.log("==================================");
        
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
            tarifa_id: $('#tarifaId').val() || null,
            seguro_id: $('#seguroSeleccionado').val() || null
        };

        console.log("Datos de reserva a enviar:", datos); // Log para depuración
        
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
    $('#seguroSeleccionado').val('');
    $('#tieneSeguro').val('no').removeAttr('disabled');
    $('#selectSeguro').html('<option value="">Seleccione un seguro médico</option>').removeAttr('disabled');
    $('#selectSeguroContainer').hide().find('.alert').remove();
    $('#btnConfirmarSeguro').removeAttr('disabled');
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
    $('#resumenSeguro').text('-');
    
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
    console.log("Cargando reservas para la fecha: " + fecha); // Debug log
    
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
            console.log("Respuesta de reservas:", respuesta); // Debug log
            
            if (respuesta.status === "success" && respuesta.data && respuesta.data.length > 0) {
                let filas = '';
                
                respuesta.data.forEach(function(reserva) {
                    console.log("Procesando reserva:", reserva); // Debug log para cada reserva
                    
                    // Formatear la hora para mostrar (HH:MM)
                    const horaInicio = reserva.hora_inicio ? reserva.hora_inicio.substring(0, 5) : '';
                    const horaFin = reserva.hora_fin ? reserva.hora_fin.substring(0, 5) : '';                    // Determinar color según estado (usar el campo correcto reserva_estado)
                    let claseEstado = '';
                    const estado = (reserva.reserva_estado || reserva.estado || reserva.estado_reserva || 'PENDIENTE').toUpperCase();
                    
                    switch (estado) {
                        case 'PENDIENTE': claseEstado = 'badge-warning'; break;
                        case 'CONFIRMADA': claseEstado = 'badge-success'; break;
                        case 'CANCELADA': claseEstado = 'badge-danger'; break;
                        case 'COMPLETADA': claseEstado = 'badge-info'; break;
                        default: claseEstado = 'badge-secondary';
                    }
                      // Maneja diferentes nombres de propiedades para cada campo                    // Obtener nombres con mejor manejo de posibles valores nulos o indefinidos
                    // Adaptado a la estructura real de la BD (usando rh_person y rs_servicios)
                    let doctorNombre = '';
                    if (reserva.doctor && reserva.doctor.trim() !== '' && !reserva.doctor.startsWith('Doctor ')) {
                        doctorNombre = reserva.doctor;
                    } else if (reserva.doctor && reserva.doctor.trim() !== '') {
                        doctorNombre = reserva.doctor;
                    } else {
                        doctorNombre = 'Dr. ' + (reserva.doctor_id || '?');
                    }
                    
                    let pacienteNombre = '';
                    if (reserva.paciente && reserva.paciente.trim() !== '' && !reserva.paciente.startsWith('Paciente ')) {
                        pacienteNombre = reserva.paciente;
                    } else if (reserva.paciente && reserva.paciente.trim() !== '') {
                        pacienteNombre = reserva.paciente;
                    } else {
                        pacienteNombre = 'Paciente ' + (reserva.paciente_id || '?');
                    }
                    
                    let servicioNombre = '';
                    if (reserva.serv_descripcion && reserva.serv_descripcion.trim() !== '' && !reserva.serv_descripcion.startsWith('Servicio ')) {
                        servicioNombre = reserva.serv_descripcion;
                    } else if (reserva.serv_descripcion && reserva.serv_descripcion.trim() !== '') {
                        // Campo nombre de rs_servicios
                        servicioNombre = reserva.serv_descripcion;
                    } else {
                        servicioNombre = 'Servicio ' + (reserva.servicio_id || '?');
                    }
                    
                    filas += `
                        <tr>
                            <td>${horaInicio} - ${horaFin}</td>
                            <td>${doctorNombre}</td>
                            <td>${pacienteNombre}</td>
                            <td>${servicioNombre}</td>
                            <td><span class="badge ${claseEstado}">${estado}</span></td>
                        </tr>
                    `;
                });
                
                $('#tablaReservasExistentes tbody').html(filas);
            } else {
                console.log("No hay reservas o error en la respuesta:", respuesta);
                $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center">No hay reservas para esta fecha</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar reservas:", xhr, status, error);
            $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar reservas: ' + error + '</td></tr>');
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
            console.log("Respuesta de slots RAW:", JSON.stringify(respuesta)); // Log completo
            
            // Almacenar datos de slots globalmente para uso en el evento de clic
            window.slotsData = respuesta.data || [];
            
            // Debug para slots
            if (respuesta.data) {
                console.log(`Número de slots obtenidos: ${respuesta.data.length}`);
                if (respuesta.data.length === 1) {
                    console.log("Se ha detectado un único slot - aplicando estilo especial");
                    console.log("Datos del slot único:", respuesta.data[0]);
                }
                
                // Verificar si hay agenda_id en los slots
                if (respuesta.data.length > 0) {
                    const primerSlot = respuesta.data[0];
                    console.log("Primer slot datos:", primerSlot);
                    console.log("¿Tiene agenda_id?", primerSlot.hasOwnProperty('agenda_id'));
                    console.log("Valor de agenda_id:", primerSlot.agenda_id);
                }
            }
            if (respuesta.data && respuesta.data.length > 0) {
                // Construir la rejilla de slots horarios
                const slots = respuesta.data;
                const isSingleSlot = slots.length === 1; // Verificar si solo hay un slot
                
                // Aplicar clase especial si solo hay un slot
                let containerClass = isSingleSlot ? 'slot-container-single' : 'row';
                let htmlSlots = `<div class="${containerClass}">`;
                
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
                      // Si solo hay un slot, aplicamos una clase especial
                    const colClass = isSingleSlot ? 'col-md-6 col-sm-10 mx-auto' : 'col-md-4 col-sm-6';
                    
                    // Si es un solo slot, usamos un formato especial
                    if (isSingleSlot) {
                        console.log("Generando un solo slot con agenda_id:", slot.agenda_id);
                        htmlSlots += `
                            <div class="${colClass} mb-3">
                                <div class="slot-horario single-slot ${claseDisponibilidad}" 
                                     data-id="${slot.horario_id || slot.id || ''}"
                                     data-inicio="${slot.hora_inicio || slot.inicio || slot.start_time || ''}"
                                     data-fin="${slot.hora_fin || slot.fin || slot.end_time || ''}"
                                     data-texto="${horaInicio} - ${horaFin}"
                                     data-sala="${nombreSala}"
                                     data-agenda-id="${slot.agenda_id || ''}">
                                    <div class="slot-content">
                                        <h5 class="slot-time text-center">${horaInicio} - ${horaFin}</h5>
                                        <div class="slot-location text-center">${nombreSala}</div>
                                        <input type="hidden" class="agenda-id-value" value="${slot.agenda_id || ''}">
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        console.log("Generando un slot regular con agenda_id:", slot.agenda_id);
                        htmlSlots += `
                            <div class="${colClass} mb-3">
                                <div class="slot-horario ${claseDisponibilidad}" 
                                     data-id="${slot.horario_id || slot.id || ''}"
                                     data-inicio="${slot.hora_inicio || slot.inicio || slot.start_time || ''}"
                                     data-fin="${slot.hora_fin || slot.fin || slot.end_time || ''}"
                                     data-texto="${horaInicio} - ${horaFin}"
                                     data-sala="${nombreSala}"
                                     data-agenda-id="${slot.agenda_id || ''}">
                                    <div class="slot-content">
                                        <div class="slot-time text-center"><strong>${horaInicio} - ${horaFin}</strong></div>
                                        <div class="slot-location text-center"><small>${nombreSala}</small></div>
                                        <input type="hidden" class="agenda-id-value" value="${slot.agenda_id || ''}">
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });
                
                htmlSlots += '</div>';
                $('#contenedorHorarios').html(htmlSlots);
                
                // Debug: Extraer todos los data-attributes del primer slot
                console.log("=== INSPECCIÓN DE SLOTS GENERADOS ===");
                const $slots = $('.slot-horario');
                console.log(`Slots generados: ${$slots.length}`);
                
                if ($slots.length > 0) {
                    const $primerSlot = $slots.first();
                    console.log("Primer slot HTML:", $primerSlot[0].outerHTML);
                    console.log("Data attributes del primer slot:", $primerSlot.data());
                    
                    // Extraer todos los data attributes manualmente
                    console.log("Data attributes extraídos manualmente:");
                    console.log("- data-id:", $primerSlot.attr('data-id'));
                    console.log("- data-inicio:", $primerSlot.attr('data-inicio'));
                    console.log("- data-fin:", $primerSlot.attr('data-fin'));
                    console.log("- data-texto:", $primerSlot.attr('data-texto'));
                    console.log("- data-sala:", $primerSlot.attr('data-sala'));
                    console.log("- data-agenda:", $primerSlot.attr('data-agenda'));
                }
                console.log("=====================================");
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
                           data-id="${paciente.person_id}" 
                           data-nombre="${paciente.last_name}">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">${paciente.last_name}</h5>
                                <small>${paciente.document_number}</small>
                            </div>
                            <p class="mb-1">${paciente.email || 'Sin email'}</p>
                            <small>${paciente.phone_number || 'Sin teléfono'}</small>
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
 * Carga los proveedores de seguro médico
 */
function cargarProveedoresSeguro() {
    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: { 
            action: "obtenerProveedoresSeguro",
        },
        dataType: "json",
        beforeSend: function() {
            $('#selectSeguro').html('<option value="">Cargando seguros médicos...</option>');
        },
        success: function(respuesta) {
            console.log("Respuesta de proveedores de seguro:", respuesta); // Log para depuración
            
            if (respuesta.data && respuesta.data.length > 0) {
                let options = '<option value="">Seleccione un seguro médico</option>';
                
                respuesta.data.forEach(function(proveedor) {
                    options += `<option value="${proveedor.prov_id}">${proveedor.prov_razon || proveedor.prov_name + ' ' + proveedor.prov_lastname}</option>`;
                });
                
                $('#selectSeguro').html(options);
            } else {
                $('#selectSeguro').html('<option value="">No se encontraron seguros médicos</option>');
            }
        },
        error: function(xhr) {
            console.error("Error al cargar proveedores de seguro:", xhr);
            $('#selectSeguro').html('<option value="">Error al cargar seguros</option>');
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
    
    // Dividir la fecha en componentes para evitar problemas de zona horaria
    const [anio, mes, dia] = fecha.split('-').map(num => parseInt(num, 10));
    
    // Crear la fecha usando UTC para evitar ajustes de zona horaria
    // Restamos 1 del mes porque en JavaScript los meses van de 0 a 11
    const fechaObj = new Date(Date.UTC(anio, mes - 1, dia));
    
    const diaSemana = diasSemana[fechaObj.getUTCDay()];
    const diaNum = fechaObj.getUTCDate();
    const mesNombre = meses[fechaObj.getUTCMonth()];
    const anioNum = fechaObj.getUTCFullYear();
    
    return `${diaSemana} ${diaNum} de ${mesNombre} de ${anioNum}`;
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

/**
 * Valida si una fecha es anterior a otra fecha, comparando solo año, mes y día
 * Evita problemas con la hora, minutos, segundos y milisegundos
 * @param {Date|string} fechaA Primera fecha a comparar
 * @param {Date|string} fechaB Segunda fecha a comparar
 * @returns {boolean} true si fechaA es anterior a fechaB, false en caso contrario
 */
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

/**
 * Verifica si dos fechas son iguales (mismo día), ignorando la hora
 * @param {Date|string} fechaA Primera fecha
 * @param {Date|string} fechaB Segunda fecha
 * @returns {boolean} true si las fechas representan el mismo día
 */
function sonMismoDia(fechaA, fechaB) {
    // Convertir a objetos Date si son strings
    const dateA = fechaA instanceof Date ? fechaA : new Date(fechaA);
    const dateB = fechaB instanceof Date ? fechaB : new Date(fechaB);
    
    // Comparar año, mes y día
    return dateA.getFullYear() === dateB.getFullYear() &&
           dateA.getMonth() === dateB.getMonth() &&
           dateA.getDate() === dateB.getDate();
}

/**
 * Función de depuración para mostrar un mensaje detallado sobre validación de fechas
 * Ayuda a identificar problemas con la validación de fechas en producción
 * @param {string} titulo Título del mensaje
 * @param {string} resultado Resultado de la validación
 * @param {Date|string} fechaA Primera fecha (usualmente la seleccionada)
 * @param {Date|string} fechaB Segunda fecha (usualmente la actual)
 */
function debugFechas(titulo, resultado, fechaA, fechaB) {
    // Solo mostrar si estamos en modo desarrollo o hay un parámetro debug en la URL
    if (window.location.search.includes('debug=1') || window.location.hostname === 'localhost') {
        console.group('Debug Validación Fechas: ' + titulo);
        
        // Convertir a Date si son strings
        const dateA = fechaA instanceof Date ? fechaA : new Date(fechaA);
        const dateB = fechaB instanceof Date ? fechaB : new Date(fechaB);
        
        console.log('Resultado:', resultado);
        
        console.log('Fecha A (seleccionada):', dateA);
        console.log('- ISO:', dateA.toISOString());
        console.log('- Local:', dateA.toLocaleString());
        console.log('- Año/Mes/Día:', dateA.getFullYear() + '-' + (dateA.getMonth()+1) + '-' + dateA.getDate());
        
        console.log('Fecha B (actual):', dateB);
        console.log('- ISO:', dateB.toISOString());
        console.log('- Local:', dateB.toLocaleString());
        console.log('- Año/Mes/Día:', dateB.getFullYear() + '-' + (dateB.getMonth()+1) + '-' + dateB.getDate());
        
        console.log('Comparaciones:');
        console.log('- Son mismo día:', sonMismoDia(dateA, dateB));
        console.log('- A es anterior a B:', esAnterior(dateA, dateB));
        console.log('- B es anterior a A:', esAnterior(dateB, dateA));
        
        console.groupEnd();
    }
}

/**
 * Maneja la fecha con compatibilidad para diferentes tipos de selectores
 * Esta función adicional maneja los selectores de fecha nativos y los campos de entrada de fecha
 * que pueden ser utilizados en diferentes partes de la aplicación
 */
function manejarSeleccionFechaDirecta() {
    // 1. Agregar manejador para input type="date" (selector nativo)
    $(document).on('change', 'input[type="date"]', function() {

        // Determinar en qué sección estamos
        const esTabReservas = $('#tabReservas').hasClass('active') || 
                         $(this).closest('#tabReservas').length > 0 || 
                         $(this).attr('id') === 'fechaReservas';

            // Si estamos en la sección de Reservas, permitir cualquier fecha
        if (esTabReservas) {
            return; // No aplicar restricción de fecha
        }

        const fechaSeleccionada = $(this).val();
        console.log("Fecha seleccionada (input nativo):", fechaSeleccionada);
        
        if (!fechaSeleccionada) return;
        
        // Obtener fecha actual
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        
        // Convertir la fecha seleccionada en objeto Date
        // El formato estándar de input[type="date"] es yyyy-mm-dd
        const fechaSeleccionadaObj = new Date(fechaSeleccionada + 'T00:00:00');
        
        console.log("Validación fecha:", {
            fechaHoy: hoy.toISOString().split('T')[0],
            fechaSeleccionada: fechaSeleccionada,
            fechaSeleccionadaObj: fechaSeleccionadaObj.toISOString(),
            esAnterior: fechaSeleccionadaObj < hoy && !sonMismoDia(fechaSeleccionadaObj, hoy)
        });
        
        // Verificar si la fecha es anterior a hoy (pero no es hoy)
        if (fechaSeleccionadaObj < hoy && !sonMismoDia(fechaSeleccionadaObj, hoy)) {
            mostrarAlerta('error', 'No puede seleccionar una fecha en el pasado.');
            $(this).val(''); // Limpiar valor
        }
    });
    
    // 2. Verificar selección de fechas en click de botones que procesan fechas
    // Esto captura casos donde hay botones de confirmación de fecha después de seleccionar
    $(document).on('click', 'button[data-action="confirmar-fecha"], #btnConfirmarFecha', function() {
        // Buscar el input de fecha más cercano
        const $inputFecha = $(this).closest('.form-group, .date-container, .card-body').find('input[type="date"], #fechaReserva');
        
        if ($inputFecha.length > 0) {
            const fechaSeleccionada = $inputFecha.val();
            
            if (!fechaSeleccionada) {
                mostrarAlerta('warning', 'Por favor, seleccione una fecha para continuar.');
                return false;
            }
            
            // Revalidar la fecha
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            
            const fechaSeleccionadaObj = new Date(fechaSeleccionada + 'T00:00:00');
            
            if (fechaSeleccionadaObj < hoy && !sonMismoDia(fechaSeleccionadaObj, hoy)) {
                mostrarAlerta('error', 'No puede seleccionar una fecha en el pasado.');
                $inputFecha.val('');
                return false;
            }
        }
    });
}

/**
 * Inicializa la pestaña de Reservas
 */
function inicializarTabReservas() {
    // Cargar médicos para el filtro
    cargarMedicosParaFiltroReservas();
    
    // Inicializar la búsqueda de reservas con valores predeterminados
    buscarReservas();
    
    // Eliminar eventos para evitar duplicación
    $(document).off('click', '#btnBuscarReservas');
    $(document).off('click', '#btnLimpiarFiltrosReservas');
    $(document).off('click', '.btnCambiarEstadoReserva');
    $(document).off('change', '#fechaReservas');
    $(document).off('change', '#selectMedicoReservas');
    $(document).off('change', '#selectEstadoReserva');
    $(document).off('keyup', '#buscarPacienteReserva');
    
    // Eventos para botones y filtros
    $(document).on('click', '#btnBuscarReservas', function() {
        console.log('Botón Buscar Reservas clickeado');
        buscarReservas();
    });
    
    $(document).on('click', '#btnLimpiarFiltrosReservas', function() {
        console.log('Botón Limpiar Filtros clickeado');
        limpiarFiltrosReservas();
    });
    
    // Evento para cambiar estado de reserva
    $(document).on('click', '.btnCambiarEstadoReserva', function() {
        const reservaId = $(this).data('id');
        const nuevoEstado = $(this).data('estado');
        console.log(`Cambiando estado de reserva ${reservaId} a ${nuevoEstado}`);
        cambiarEstadoReservaTab(reservaId, nuevoEstado);
    });
      // Eventos de cambio para actualizar automáticamente
    $(document).on('change', '#fechaReservas', function() {
        console.log('Fecha cambiada a:', $(this).val());
        buscarReservas();
    });
    
    $(document).on('change', '#selectMedicoReservas', function() {
        console.log('Médico cambiado a:', $(this).val());
        buscarReservas();
    });
    
    $(document).on('change', '#selectEstadoReserva', function() {
        console.log('Estado cambiado a:', $(this).val());
        buscarReservas();
    });
    
    // Búsqueda por paciente con retraso para evitar demasiadas peticiones
    let timeoutPaciente = null;
    $(document).on('keyup', '#buscarPacienteReserva', function() {
        console.log('Buscando paciente:', $(this).val());
        clearTimeout(timeoutPaciente);
        timeoutPaciente = setTimeout(function() {
            buscarReservas();
        }, 600); // Esperar 600ms después de que el usuario deje de escribir
    });
}

/**
 * Carga la lista de médicos para el filtro de reservas
 */
function cargarMedicosParaFiltroReservas() {
    // Guardar el valor actual antes de la llamada AJAX
    const valorActual = $('#selectMedicoReservas').val();
    console.log("Valor actual del selector de médicos antes de AJAX:", valorActual);
    
    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: { 
            action: "obtenerMedicos" 
        },
        dataType: "json",
        success: function(respuesta) {
            console.log("Respuesta de médicos para filtro:", respuesta);
            
            // Debug de los IDs de los médicos disponibles
            if (respuesta.status === "success" && respuesta.data) {
                console.log("IDs de médicos disponibles:", respuesta.data.map(m => m.doctor_id));
            }
            
            if (respuesta.status === "success" && respuesta.data) {
                let opciones = '<option value="0">Todos los médicos</option>';
                  respuesta.data.forEach(function(medico) {
                    const doctorId = medico.doctor_id;
                    const nombreCompleto = medico.nombre_doctor || 
                                          (medico.first_name && medico.last_name ? 
                                           medico.first_name + ' - ' + medico.last_name : 
                                           'Dr. ' + medico.doctor_id);
                    
                    // Asegurar que doctorId sea un string para el value del option
                    const doctorIdValue = String(doctorId);
                    
                    opciones += `<option value="${doctorIdValue}">Dr. ${doctorIdValue} - ${nombreCompleto}</option>`;
                    
                    // Debug de la opción creada
                    console.log(`Creada opción para doctor: ID=${doctorIdValue}, Nombre=${nombreCompleto}`);
                });
                
                // Actualizar las opciones
                $('#selectMedicoReservas').html(opciones);
                
                // Intentar restaurar el valor seleccionado previamente
                if (valorActual && valorActual !== '0') {
                    $('#selectMedicoReservas').val(valorActual);
                    console.log("Intentando restaurar valor:", valorActual, "Valor actual:", $('#selectMedicoReservas').val());
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar médicos para filtro:", error);
        }
    });
}

/**
 * Busca reservas según los filtros seleccionados
 */
function buscarReservas() {
    // Capturar los valores directamente de los elementos DOM
    const fecha = $('#fechaReservas').val() || '';
    
    // Obtener doctorId correctamente y asegurar que sea un string
    const doctorId = $('#selectMedicoReservas').val() || '0';
      
    // Obtener estado directamente del elemento DOM para evitar problemas con el valor
    const estadoElem = document.getElementById('selectEstadoReserva');
    const estado = estadoElem ? estadoElem.value : '0';
    console.log('Estado seleccionado (directo del DOM):', estado);
    
    // Obtener paciente y asegurar que no sea un string vacío
    const paciente = $('#buscarPacienteReserva').val() ? $('#buscarPacienteReserva').val().trim() : '';
    
    console.log(`Buscando reservas - Fecha: ${fecha}, Doctor: ${doctorId}, Estado: ${estado}, Paciente: ${paciente}`);
    
    // Debug para verificar valores
    console.log('Elementos DOM:');
    console.log('- fechaReservas:', $('#fechaReservas').length ? 'Existe' : 'No existe', $('#fechaReservas').val());
    console.log('- selectMedicoReservas:', $('#selectMedicoReservas').length ? 'Existe' : 'No existe', doctorId);
    console.log('- selectEstadoReserva:', $('#selectEstadoReserva').length ? 'Existe' : 'No existe', estado);
    console.log('- buscarPacienteReserva:', $('#buscarPacienteReserva').length ? 'Existe' : 'No existe', $('#buscarPacienteReserva').val());
      
    // Preparar los datos para la solicitud
    const requestData = { 
        action: "buscarReservas",
    };

    // Añadir fecha solo si no está vacía
    if (fecha && fecha.trim() !== '') {
        requestData.fecha = fecha;
    }

    // Verificar tipos de datos para debugging
    console.log("Tipo de doctorId:", typeof doctorId);
    console.log("Valor numérico de doctorId:", Number(doctorId));

    // Añadir doctor_id solo si es diferente de 0 o "0"
    if (doctorId && doctorId !== '0') {
        requestData.doctor_id = doctorId;
    }

    // Añadir estado solo si es diferente de 0 o "0"
    if (estado && estado !== '0') {
        requestData.estado = estado;
    }

    // Añadir paciente solo si no está vacío
    if (paciente && paciente.trim() !== '') {
        requestData.paciente = paciente;
    }

    console.log('Enviando solicitud AJAX con datos:', requestData);

    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: requestData,
        dataType: "json",
        beforeSend: function() {
            $('#tablaReservas tbody').html('<tr><td colspan="10" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando reservas...</td></tr>');
        },
        success: function(respuesta) {
            console.log("Respuesta de búsqueda de reservas:", respuesta);
            
            // Verificar si hay información de depuración
            if (respuesta.filtros) {
                console.log("Filtros aplicados en el servidor:", respuesta.filtros);
            }
            
            if (respuesta.status === "success" && respuesta.data && respuesta.data.length > 0) {
                let filas = '';
                
                respuesta.data.forEach(function(reserva) {
                    // Formatear la fecha para mostrar
                    const fechaFormateada = formatearFechaParaMostrar(reserva.fecha_reserva);
                    
                    // Determinar color según estado
                    let claseFila = '';
                    let iconoEstado = '';
                    
                    switch (reserva.reserva_estado) {
                        case 'PENDIENTE':
                            claseFila = 'table-warning';
                            iconoEstado = '<i class="fas fa-clock text-warning"></i>';
                            break;
                        case 'CONFIRMADA':
                            claseFila = 'table-info';
                            iconoEstado = '<i class="fas fa-check-circle text-info"></i>';
                            break;
                        case 'COMPLETADA':
                            claseFila = 'table-success';
                            iconoEstado = '<i class="fas fa-check-double text-success"></i>';
                            break;
                        case 'CANCELADA':
                            claseFila = 'table-danger';
                            iconoEstado = '<i class="fas fa-times-circle text-danger"></i>';
                            break;
                        default:
                            claseFila = '';
                            iconoEstado = '<i class="fas fa-question-circle text-secondary"></i>';
                    }
                    
                    // Calcular día de la semana
                    // const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    // const fecha = new Date(reserva.fecha_reserva);
                    // const diaSemana = dias[fecha.getDay()];

                    // Calcular día de la semana correctamente
                    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

                    // Método 1: Crear la fecha con UTC específicamente
                    const fechaParts = reserva.fecha_reserva.split('-').map(Number);
                    const fecha = new Date(Date.UTC(fechaParts[0], fechaParts[1] - 1, fechaParts[2]));
                    const diaSemana = dias[fecha.getUTCDay()]; // Usar getUTCDay en lugar de getDay
                    
                    // Formato de horario
                    const horario = `${reserva.hora_inicio} - ${reserva.hora_fin}`;
                    
                    // Formatear monto si está disponible
                    const monto = reserva.monto ? `$${parseFloat(reserva.monto).toFixed(2)}` : 'N/A';
                    
                    // Nombre de la sala (si está disponible)
                    const sala = reserva.sala_nombre || 'Sin asignar';
                    
                    filas += `<tr class="${claseFila}">
                        <td>${fechaFormateada}</td>
                        <td>${diaSemana}</td>
                        <td>${horario}</td>
                        <td>${reserva.paciente}</td>
                        <td>${reserva.doctor}</td>
                        <td>${reserva.serv_descripcion || reserva.nombre_servicio || 'N/A'}</td>
                        <td>${reserva.sala_nombre || 'Sin asignar'}</td>
                        <td>${reserva.serv_monto ? `$${parseFloat(reserva.serv_monto).toFixed(2)}` : 'N/A'}</td>
                        <td>${iconoEstado} ${reserva.reserva_estado}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-info btn-sm btnVerReserva" data-id="${reserva.reserva_id}" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-warning btn-sm btnEditarReserva" data-id="${reserva.reserva_id}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm btnCancelarReserva" data-id="${reserva.reserva_id}" title="Cancelar">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                });
                
                $('#tablaReservas tbody').html(filas);
            } else {
                // Mostrar mensaje si no hay reservas
                $('#tablaReservas tbody').html('<tr><td colspan="10" class="text-center">No se encontraron reservas con los filtros seleccionados</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al buscar reservas:", error);
            
            // Mostrar mensaje de error
            $('#tablaReservas tbody').html('<tr><td colspan="10" class="text-center text-danger">Error al cargar reservas: ' + error + '</td></tr>');
            
            // Intentar obtener más detalles del error
            try {
                const respuesta = JSON.parse(xhr.responseText);
                console.error("Detalles del error:", respuesta);
            } catch (e) {
                console.error("No se pudo parsear la respuesta del error:", xhr.responseText);
            }
        }
    });
}

/**
 * Limpia todos los filtros de búsqueda de reservas
 */
function limpiarFiltrosReservas() {
    console.log('Limpiando todos los filtros de reservas');
    
    // Restablecer fecha a hoy
    $('#fechaReservas').val(moment().format('YYYY-MM-DD'));
    
    // Restablecer médico a "Todos"
    $('#selectMedicoReservas').val('0');
    
    // Restablecer estado a "Todos"
    $('#selectEstadoReserva').val('0');
    
    // Limpiar búsqueda de paciente
    $('#buscarPacienteReserva').val('');
    
    // Volver a cargar las reservas con los filtros limpios
    buscarReservas();
}

/**
 * Cambia el estado de una reserva desde la pestaña Reservas
 * @param {number} reservaId ID de la reserva
 * @param {string} nuevoEstado Nuevo estado a asignar
 */
function cambiarEstadoReservaTab(reservaId, nuevoEstado) {
    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: { 
            action: "cambiarEstadoReserva",
            reserva_id: reservaId,
            nuevo_estado: nuevoEstado
        },
        dataType: "json",
        beforeSend: function() {
            mostrarAlerta('info', 'Actualizando estado de la reserva...', 'center', false);
        },
        success: function(respuesta) {
            console.log("Respuesta de cambio de estado:", respuesta);
            
            if (respuesta.status === "success") {
                mostrarAlerta('success', 'Estado actualizado correctamente', 'center');
                // Recargar las reservas con los filtros actuales
                buscarReservas();
            } else {
                mostrarAlerta('error', 'Error al cambiar estado: ' + (respuesta.mensaje || 'Error desconocido'));
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cambiar estado de reserva:", error);
            mostrarAlerta('error', 'Error al actualizar: ' + error);
        }
    });
}
