/**
 * JavaScript for the new Reservas New section
 */

$(document).ready(function () {
    console.log('Inicializando módulo Reservas New');
    inicializarReservasNew();

    // Submit button click handler
    $('#btnConfirmarReserva').on('click', function () {
        guardarReserva();
    });

    // Debug logging for hora-btn clicks
    $(document).on('click', '.hora-btn', function () {
        logDataAttributes(this, 'Hora Button Clicked:');
    });
});

/**
 * Initialize the Reservas New module
 */
function inicializarReservasNew() {
    console.log('Inicializando módulo Reservas New');

    // Cargar los seguros de salud
    cargarSeguros();

    // Cargar algunos servicios predeterminados iniciales
    cargarServiciosIniciales();

    // Configurar la fecha actual
    const fechaActual = moment().format('YYYY-MM-DD');
    $('#fechaReservaNew').val(fechaActual);

    // Cargar reservas para la fecha actual
    cargarReservasPorFecha(fechaActual);
    
    // Iniciar carga de médicos después de un breve retraso para asegurar que todo esté listo
    setTimeout(function() {
        console.log('Iniciando carga inicial de médicos...');
        depurarCargaMedicos();
        buscarMedicosDisponibles();
    }, 500);

    // Asegurarse de que el botón de cambiar médico esté oculto al inicio
    $('#btnCambiarMedicoNew').addClass('d-none');
    $('#btnBuscarMedicoNew').removeClass('d-none');
    $('#buscarMedicoNew').prop('readonly', false).removeClass('selected-doctor');

    // Ocultar el resumen de horarios al inicio
    $('#resumenHorariosNew').addClass('d-none');

    // Focus on patient search first as it's now step 1
    setTimeout(function () {
        $('#buscarPacienteNew').focus();
    }, 300);

    // Set up initial date
    $('#fechaReservaNew').val(moment().format('YYYY-MM-DD'));
    const fechaFormateada = moment().format('DD/MM/YYYY');
    $('#resumenFechaNew').text(fechaFormateada);

    // Patient search on Enter key (priority as first step)
    $('#buscarPacienteNew').keyup(function (e) {
        if (e.keyCode === 13) {
            buscarPaciente();
        }
    });

    // Patient search button click
    $('#btnBuscarPacienteNew').click(function () {
        buscarPaciente();
    });    // Search for available doctors on date change (step 2)
    $('#fechaReservaNew').change(function() {
        const fecha = $(this).val();
        console.log('Fecha seleccionada:', fecha);
        
        // Ejecutar la depuración primero
        depurarCargaMedicos();
        
        // Luego buscar médicos disponibles
        buscarMedicosDisponibles();

        // Si hay un médico seleccionado, actualizar sus servicios para la nueva fecha
        const medicoSeleccionado = $('#selectMedicoNew').val();
        if (medicoSeleccionado) {
            cargarServiciosPorFechaMedico(fecha, medicoSeleccionado);
        }

        // Cargar reservas existentes para la fecha seleccionada
        cargarReservasPorFecha(fecha);

        // Check if form is complete after changing date
        verificarFormularioCompleto();
    });

// Doctor search on Enter key
$('#buscarMedicoNew').keyup(function (e) {
    if (e.keyCode === 13) {
        buscarMedicos();
    }
});
// Doctor search button click
$('#btnBuscarMedicoNew').click(function () {
    buscarMedicos();
});

// Doctor selection event
$(document).on('click', '.btn-select-doctor', function () {
    const medicoId = $(this).data('medico-id');
    const medicoNombre = $(this).data('medico-nombre');

    // Update UI
    $('#selectMedicoNew').val(medicoId);
    $('#medicoNombreMostrar').text(medicoNombre);

    // Actualizar el campo de búsqueda de médico y deshabilitarlo
    $('#buscarMedicoNew').val(medicoNombre).prop('readonly', true).addClass('selected-doctor');
    // Highlight selected doctor
    $('#tablaMedicosNew tbody tr').removeClass('selected');
    $(this).closest('tr').addClass('selected');

    // Update summary
    $('#resumenMedicoNew').text(medicoNombre);    // Load doctor's services for the selected date
    const fecha = $('#fechaReservaNew').val();
    if (fecha) {
        cargarServiciosPorFechaMedico(fecha, medicoId);
        
        // También cargar los horarios disponibles
        console.log('Cargando horarios después de seleccionar médico');
        setTimeout(function() {
            cargarHorariosDisponibles();
        }, 300);
    }

    // Check if form is complete after selecting doctor
    verificarFormularioCompleto();

    // Remove any existing time slot rows
    $('.horario-row').remove();

    const servicioId = $('#servicioSelect').val() || 0; const doctorRow = $(this).closest('tr');

    // Mostrar loader para los horarios
    doctorRow.after(`
            <tr class="horario-row loading-slots">
                <td colspan="5" class="text-center py-3">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Cargando horarios disponibles...
                </td>
            </tr>
        `);

    // Asegurarse de que todas las filas anteriores de horarios sean eliminadas
    $('.horario-row:not(.loading-slots)').remove();

    // Cargar horarios disponibles a través de AJAX
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerHorariosDisponibles',
            doctor_id: medicoId,
            fecha: fecha,
            servicio_id: servicioId
        },
        dataType: 'json', success: function (respuesta) {
            console.log('Respuesta horarios:', respuesta);
            // Eliminar row de carga
            $('.loading-slots').remove();

            if (respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                console.log('Procesando horarios:', respuesta.data);

                // Registrar los datos de cada horario para depuración
                respuesta.data.forEach((horario, index) => {
                    console.log(`Horario ${index}:`, {
                        id: horario.horario_id,
                        inicio: horario.hora_inicio,
                        fin: horario.hora_fin,
                        turno: horario.turno_nombre
                    });
                });

                // Generar filas de horarios con datos reales
                const horariosHtml = generateTimeSlotRowsFromData(respuesta.data);
                doctorRow.after(horariosHtml);
            } else {
                console.warn('No se encontraron horarios disponibles:', respuesta);
                // No hay horarios disponibles
                doctorRow.after(`
                        <tr class="horario-row">
                            <td colspan="5" class="text-center py-3">
                                <i class="fas fa-calendar-times text-warning mr-2"></i>
                                No hay horarios disponibles para este médico en la fecha seleccionada
                            </td>
                        </tr>
                    `);
            }

            // Scroll to the time slots
            setTimeout(function () {
                $('html, body').animate({
                    scrollTop: doctorRow.next().offset().top - 100
                }, 300);
            }, 100);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar horarios:', error);
            $('.loading-slots').remove();
            doctorRow.after(`
                    <tr class="horario-row">
                        <td colspan="5" class="text-center py-3 text-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Error al cargar los horarios. Intente nuevamente.
                        </td>
                    </tr>
                `);
        }
    });
});

// Time slot selection
$(document).on('click', '.hora-slot', function () {
    if ($(this).hasClass('no-disponible')) {
        mostrarAlerta('warning', 'Este horario no está disponible');
        return;
    }

    const hora = $(this).data('hora');

    // Update UI
    $('.hora-slot').removeClass('selected'); $(this).addClass('selected');
    $('#horaSeleccionada').val(hora);

    // Update summary
    $('#resumenHoraNew').text(hora);
});

// Action for time slot selection from the table    
$(document).on('click', '.hora-btn', function () {
    console.log("Hora button clicked", this);
    // Get data attributes - SIGUIENDO PATRÓN DE SERVICIOS.JS
    const slotId = $(this).attr('data-id');
    const horaInicio = $(this).attr('data-inicio');
    const horaFin = $(this).attr('data-fin');
    const textoHorario = $(this).attr('data-texto');
    const agendaIdFromAttr = $(this).attr('data-agenda-id');

    // También intentar obtener desde el input hidden como fallback
    const agendaIdFromInput = $(this).find('.agenda-id-value').val();

    // El agenda_id principal (siguiendo lógica de servicios.js)
    const agendaId = agendaIdFromAttr || agendaIdFromInput || slotId;

    console.log("=== DATOS DEL SLOT SELECCIONADO (PATRÓN SERVICIOS.JS) ===");
    console.log("Slot ID:", slotId);
    console.log("Hora inicio:", horaInicio);
    console.log("Hora fin:", horaFin);
    console.log("Texto horario:", textoHorario);
    console.log("Agenda ID desde atributo:", agendaIdFromAttr);
    console.log("Agenda ID desde input:", agendaIdFromInput);
    console.log("Agenda ID final:", agendaId);
    console.log("Elemento HTML:", $(this)[0].outerHTML);
    console.log("================================");
    if (!horaInicio) {
        console.error("No se encontró el atributo data-inicio en el botón");
        alert("Error al seleccionar el horario. Por favor intente nuevamente.");
        return;
    }

    const horarioTexto = horaFin ? `${horaInicio} - ${horaFin}` : horaInicio;
    console.log("Horario seleccionado:", horarioTexto);

    // Visual feedback
    $('.hora-btn').removeClass('btn-success').addClass('btn-primary');
    $(this).removeClass('btn-primary').addClass('btn-success');
    // Store selected time in multiple fields for compatibility
    $('#horaInicioSeleccionada').val(horaInicio);
    $('#horaFinSeleccionada').val(horaFin);
    $('#horaSeleccionada').val(horaInicio); // Add this for backward compatibility
    $('#agendaId').val(agendaId); // Store the agenda_id (SIGUIENDO PATRÓN DE SERVICIOS.JS)

    console.log("Valor asignado al campo #agendaId:", $('#agendaId').val());

    console.log("Valores guardados en campos ocultos:", {
        horaInicioSeleccionada: $('#horaInicioSeleccionada').val(),
        horaFinSeleccionada: $('#horaFinSeleccionada').val(),
        horaSeleccionada: $('#horaSeleccionada').val(),
        agendaId: $('#agendaId').val()
    });
    // Update summary information
    $('#resumenHoraNew').text(horarioTexto);

    // Actualizar y mostrar el resumen en la sección de horarios
    $('#resumenHoraHorario').text(horarioTexto);
    $('#resumenMedicoHorario').text($('#medicoNombreMostrar').text());
    $('#resumenFechaHorario').text($('#resumenFechaNew').text());
    $('#resumenHorariosNew').removeClass('d-none');

    console.log("Resumen actualizado:", $('#resumenHoraNew').text());

    // Check if form is complete after selecting time
    verificarFormularioCompleto();

    // No hacer scroll automático al servicio, esperamos a que el usuario confirme el horario

    // Mostrar botón de confirmar horario
    $('#btnConfirmarHorario').show();
});

// Botón para confirmar horario y avanzar al siguiente paso
$('#btnConfirmarHorario').click(function () {
    // Ocultar el resumen en la sección de horarios después de confirmar
    $('#resumenHorariosNew').addClass('d-none');

    // Scroll al servicio como próximo paso
    $('html, body').animate({
        scrollTop: $('#servicioSelect').offset().top - 100
    }, 500);

    // Focus on service selection
    setTimeout(function () {
        $('#servicioSelect').focus();
    }, 600);

    // Mostrar una notificación de confirmación
    toastr.success('Horario seleccionado correctamente', 'Éxito', {
        timeOut: 2000,
        positionClass: 'toast-top-right',
        closeButton: true
    });
});

// Service selection
$('#servicioSelect').change(function () {
    const servicioId = $(this).val();
    const servicioNombre = $(this).find('option:selected').text();

    if (servicioId) {
        // Update summary
        $('#resumenServicioNew').text(servicioNombre);

        // Update price
        const precio = $(this).find('option:selected').data('precio');

        if (precio) {
            const precioFormateado = new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0
            }).format(precio);

            $('#resumenPrecioNew').text(precioFormateado);
        }

        // Check if form is complete
        verificarFormularioCompleto();
        if (precio) {
            $('#importeReservaNew').val(formatearPrecio(precio));
            $('#resumenImporteNew').text('S/ ' + formatearPrecio(precio));
        }

        // Reload available time slots with service duration
        cargarHorariosDisponibles();
    }
});

// Insurance selection
$('#seguroSelect').change(function () {
    const seguroId = $(this).val();
    const seguroNombre = $(this).find('option:selected').text();

    if (seguroId && seguroId != "0") {
        // Update summary
        $('#resumenSeguroNew').text(seguroNombre);
        // Si existe la función para cargar planes
        if (typeof cargarPlanesSeguro === 'function') {
            cargarPlanesSeguro(seguroId);
        } else {
            console.log('Función cargarPlanesSeguro no disponible');
            // Sin planes disponibles
            $('#planSelect').html('<option value="0">Sin plan disponible</option>').prop('disabled', true);
        }
    } else {
        $('#resumenSeguroNew').text('Sin seguro');
        $('#planSelect').html('<option value="0">Sin plan</option>').prop('disabled', true);
    }

    // Check if form is complete after selecting insurance
    verificarFormularioCompleto();
});

// New patient button
$('#btnNuevoPaciente').click(function () {
    // If there's a global new patient modal/function
    if (typeof abrirModalNuevoPaciente === 'function') {
        abrirModalNuevoPaciente();
    } else {
        // Redirect to patients module if no modal function exists
        window.open('pacientes', '_blank');
    }
});

// Save reservation button
$('#btnGuardarReservaNew').click(function () {
    guardarReserva();
});

// Load insurance options on page load
cargarSeguros();
// Initial doctors load
buscarMedicosDisponibles();

    // Additional functionality for Reservas New - Movido dentro de inicialización

    // Este evento ya está definido antes, así que lo comentamos para evitar duplicados
    /*
    // Handle doctor search functionality
    $('#btnBuscarMedicoNew').click(function() {
        buscarMedicos();
    });
    
    $('#buscarMedicoNew').keyup(function(e) {
        if (e.keyCode === 13) {
            buscarMedicos();
        }
    });
    */


// Estos eventos deberían estar dentro de la función $(document).ready()
$(document).ready(function () {
    // Refresh doctors list button
    $('#btnRefreshMedicos').click(function () {
        buscarMedicosDisponibles();
    });

    // Actualiza el resumen de fecha cuando cambia la fecha
    $('#fechaReservaNew').change(function () {
        const fecha = $(this).val();
        if (fecha) {
            const fechaFormateada = moment(fecha).format('DD/MM/YYYY');
            $('#resumenFechaNew').text(fechaFormateada);
        }
    });

    // Plan selection functionality
    $('#planSelect').change(function () {
        const planId = $(this).val();
        const planNombre = $(this).find('option:selected').text();

        if (planId && planId != "0") {
            actualizarPrecioPlan(planId);
        }
    });

    // Save reservation button
    $('#btnGuardarReservaNew').click(function () {
        guardarReserva();
    });
});

// Cuando se selecciona un médico, mostrar el botón para cambiarlo
$(document).on('click', '.btn-select-doctor', function () {
    $('#btnBuscarMedicoNew').addClass('d-none');
    $('#btnCambiarMedicoNew').removeClass('d-none');
});

// Botón para cambiar médico (limpiar selección)
$('#btnCambiarMedicoNew').click(function () {
    // Restaurar campo de búsqueda
    $('#buscarMedicoNew').val('').prop('readonly', false).removeClass('selected-doctor');

    // Mostrar botón de búsqueda y ocultar botón de cambio
    $('#btnBuscarMedicoNew').removeClass('d-none');
    $('#btnCambiarMedicoNew').addClass('d-none');

    // Limpiar selección de médico
    $('#selectMedicoNew').val('');
    $('#medicoNombreMostrar').text('Ningún médico seleccionado');
    $('#resumenMedicoNew').text('(No seleccionado)');

    // Eliminar todas las filas de horario
    $('.horario-row').remove();

    // Eliminar highlight de la tabla de médicos
    $('#tablaMedicosNew tbody tr').removeClass('selected');

    // Enfocar el campo de búsqueda
    $('#buscarMedicoNew').focus();
});

/**
 * Function to search for available doctors on a specific date
 */
function buscarMedicosDisponibles() {
    const fecha = $('#fechaReservaNew').val();
    
    console.log('Buscando médicos disponibles para fecha:', fecha);
    
    if (!fecha) {
        mostrarAlerta('warning', 'Por favor seleccione una fecha válida');
        return;
    }

    // Show loading
    $('#tablaMedicosNew tbody').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando médicos...</td></tr>');

    // Format date for display
    const fechaFormateada = moment(fecha).format('DD/MM/YYYY');
    $('#resumenFechaNew').text(fechaFormateada);
    
    // AJAX call to get available doctors
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerMedicosPorFecha',
            fecha: fecha
        },
        dataType: 'json',
        success: function (respuesta) {
            console.log('Respuesta médicos:', respuesta);
            cargarTablaMedicos(respuesta);
        },
        error: function (xhr, status, error) {
            console.error('Error al buscar médicos:', error);
            try {
                const respuestaTexto = xhr.responseText;
                console.log('Respuesta completa:', respuestaTexto);
                if (respuestaTexto) {
                    const respuestaJson = JSON.parse(respuestaTexto);
                    console.log('Respuesta JSON:', respuestaJson);
                }
            } catch (e) {
                console.log('No se pudo parsear la respuesta como JSON:', xhr.responseText);
            }            $('#tablaMedicosNew tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar médicos</td></tr>');
        }
    });
}

/**
 * Función para depurar problemas de carga de médicos
 */
function depurarCargaMedicos() {
    const fecha = $('#fechaReservaNew').val();
    console.log('*** DEPURACIÓN DE CARGA DE MÉDICOS ***');
    console.log('Fecha seleccionada:', fecha);
    
    // Verificar si la fecha tiene formato correcto
    const fechaObj = new Date(fecha);
    console.log('Fecha como objeto Date:', fechaObj);
    console.log('Fecha es válida:', !isNaN(fechaObj.getTime()));
    console.log('Día de la semana:', fechaObj.getDay()); // 0=domingo, 1=lunes, etc.
    
    // Verificar que la función moment() esté funcionando correctamente
    try {
        const fechaMoment = moment(fecha);
        console.log('Fecha en moment:', fechaMoment);
        console.log('Fecha formateada con moment:', fechaMoment.format('DD/MM/YYYY'));
        console.log('Día de semana con moment:', fechaMoment.format('dddd'));
    } catch (e) {
        console.error('Error con moment():', e);
    }
    
    // Ejecutar una llamada de prueba directa a la API
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerMedicosPorFecha',
            fecha: fecha
        },
        dataType: 'json',
        success: function(respuesta) {
            console.log('*** RESPUESTA DE PRUEBA ***');
            console.log('Respuesta completa:', respuesta);
            console.log('Status:', respuesta.status);
            console.log('Data:', respuesta.data);
            console.log('Tipo de data:', Array.isArray(respuesta.data) ? 'Array' : typeof respuesta.data);
            console.log('Longitud de data:', respuesta.data ? respuesta.data.length : 'N/A');
        },
        error: function(xhr, status, error) {
            console.error('*** ERROR EN LLAMADA DE PRUEBA ***');
            console.error('Status:', status);
            console.error('Error:', error);
            console.log('Respuesta texto:', xhr.responseText);
        }
    });
}

/**
 * Function to search for doctors by name
 */
function buscarMedicos() {
    const termino = $('#buscarMedicoNew').val();
    const fecha = $('#fechaReservaNew').val();

    if (!termino) {
        buscarMedicosDisponibles();
        return;
    }

    // Show loading
    $('#tablaMedicosNew tbody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando médicos...</td></tr>');

    // AJAX call to search doctors
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            accion: 'buscarMedicos',
            termino: termino,
            fecha: fecha
        },
        dataType: 'json',
        success: function (respuesta) {
            cargarTablaMedicos(respuesta);
        },
        error: function (xhr, status, error) {
            console.error('Error al buscar médicos:', error);
            $('#tablaMedicosNew tbody').html('<tr><td colspan="3" class="text-center text-danger">Error al buscar médicos</td></tr>');
        }
    });
}

/**
 * Load doctors into table with availability and time slots
 */
function cargarTablaMedicos(respuesta) {
    let html = '';
    let counter = 1;

    // Get the medicos array from the response (checking different possible formats)
    let medicos = [];
    if (respuesta && respuesta.data) {
        medicos = respuesta.data;
    } else if (Array.isArray(respuesta)) {
        medicos = respuesta;
    }

    console.log("Médicos para mostrar:", medicos);

    if (medicos && medicos.length > 0) {
        // Store doctors in global variable for later use
        window.medicosDisponibles = medicos;
        medicos.forEach(function (medico) {
            // Get doctor ID and name from response
            const medicoId = medico.doctor_id || medico.id || medico.person_id;
            const medicoNombre = medico.nombre_doctor || medico.nombre || medico.nombre_completo || '';

            // Use real data from backend
            const turno = medico.turno_nombre || medico.turno || 'No especificado';
            const disponibles = medico.cupo_disponible || medico.disponibles || 0;

            html += `
                <tr class="doctor-row" data-medico-id="${medicoId}">
                    <td>${counter}</td>
                    <td>${medicoNombre}</td>
                    <td>${turno}</td>
                    <td>${disponibles}</td>
                    <td>
                        <button class="btn btn-primary btn-circle btn-select-doctor" 
                                data-medico-id="${medicoId}" 
                                data-medico-nombre="${medicoNombre}">
                            <i class="fas fa-check"></i>
                        </button>
                    </td>
                </tr>
            `;
            counter++;
        });
    } else {
        html = '<tr><td colspan="5" class="text-center">No hay médicos disponibles para la fecha seleccionada</td></tr>';
    }

    $('#tablaMedicosNew tbody').html(html);

    // Remove any existing time slot rows
    $('.horario-row').remove();
}

/**
 * Generate time slot rows from API data
 * @param {Array} horarios - Array of available time slots from the server
 * @returns {string} HTML for time slot rows
 */
/**
 * Generate time slot rows from API data
 * Esta función genera filas de horarios a partir de los datos recibidos de la API
 * @param {Array} horarios - Array of available time slots from the server
 * @returns {string} HTML for time slot rows
 */
function generateTimeSlotRowsFromData(horarios) {
    console.log("Generando filas de horarios con datos:", horarios);

    // Validar que horarios sea un array
    if (!Array.isArray(horarios)) {
        console.error("Los horarios recibidos no son un array:", horarios);
        return '<tr class="horario-row"><td colspan="5" class="text-center text-danger">Error en el formato de los datos de horarios</td></tr>';
    }

    let html = '<tr class="horario-row"><td colspan="5" class="p-0">'; // Agregado p-0 para quitar padding
    html += '<div class="horarios-container">'; // Envolver la tabla en un div con la clase horarios-container
    html += '<table class="table table-horarios">';
    html += '<thead><tr><th>Hora</th><th class="text-center">Check</th></tr></thead>';
    html += '<tbody class="horarios-scroll">'; // Agregada clase para el scroll

    // Si no hay horarios, mostrar mensaje
    if (horarios.length === 0) {
        html += `
        <tr>
            <td colspan="2" class="text-center py-3">
                <i class="fas fa-calendar-times text-warning mr-2"></i>
                No hay horarios disponibles para este médico en la fecha seleccionada
            </td>
        </tr>`;
    } else {
        // Procesar cada horario
        horarios.forEach(function (horario) {
            // Debugging
            console.log("Procesando horario:", horario);            // La estructura de datos puede variar según el backend, adaptamos el código
            const horaInicio = horario.hora_inicio || '';
            const horaFin = horario.hora_fin || '';
            const horarioId = horario.horario_id || ''; // This is the main ID
            const agendaId = horario.agenda_id || horarioId || ''; // Use agenda_id if available, fallback to horario_id
            const disponible = horario.disponible !== false; // Asumimos disponible a menos que se indique lo contrario

            const disponibleClass = disponible ? '' : 'text-muted';
            const buttonClass = disponible ? 'btn-primary' : 'btn-secondary';
            const buttonDisabled = !disponible ? 'disabled' : '';
            const horarioTexto = horaFin ? `${horaInicio} - ${horaFin}` : horaInicio;

            html += `
            <tr class="horario-item ${disponibleClass}">
                <td>${horarioTexto}</td>
                <td class="text-center">
                    <button type="button" class="btn ${buttonClass} btn-sm btn-circle hora-btn" 
                            data-id="${horarioId}"
                            data-inicio="${horaInicio}" 
                            data-fin="${horaFin}" 
                            data-agenda-id="${agendaId}"
                            data-texto="${horarioTexto}"
                            ${buttonDisabled}>
                        <i class="fas fa-check"></i>
                        <input type="hidden" class="agenda-id-value" value="${agendaId}">
                    </button>
                </td>
            </tr>
            `;
        });
    }
    html += '</tbody></table>';
    html += '</div>'; // Cerrar el div horarios-container
    html += '</td></tr>';

    console.log("HTML generado para horarios:", html);
    return html;
}

/**
 * Log data attributes of an element for debugging
 */
function logDataAttributes(element, message = 'Data attributes') {
    const $el = $(element);
    const dataAttrs = {};

    // Get all data attributes
    $.each($el[0].attributes, function () {
        if (this.name.startsWith('data-')) {
            const key = this.name.replace('data-', '');
            dataAttrs[key] = this.value;
        }
    });

    console.log(message, dataAttrs, $el);
}

/**
 * Function to search for a patient
 */
function buscarPaciente() {
    const termino = $('#buscarPacienteNew').val();

    if (!termino) {
        mostrarAlerta('warning', 'Por favor ingrese un término de búsqueda');
        return;
    }

    // Show loading
    $('#tablaPacientesNew tbody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando pacientes...</td></tr>');

    // AJAX call to search patients - using the same endpoint as the original functionality
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'buscarPaciente',
            termino: termino
        },
        dataType: 'json',
        success: function (respuesta) {
            if (respuesta && respuesta.data) {
                cargarTablaPacientes(respuesta.data);
            } else {
                $('#tablaPacientesNew tbody').html('<tr><td colspan="3" class="text-center">No se encontraron pacientes</td></tr>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al buscar pacientes:', error);
            console.log('Respuesta:', xhr.responseText);
            $('#tablaPacientesNew tbody').html('<tr><td colspan="3" class="text-center text-danger">Error al buscar pacientes</td></tr>');
        }
    });
}

/**
 * Load patients into table
 */
function cargarTablaPacientes(pacientes) {
    let html = '';

    if (pacientes && pacientes.length > 0) {
        pacientes.forEach(function (paciente) {
            const nombreCompleto = `${paciente.first_name || ''} ${paciente.last_name || ''}`.trim();
            html += `
                <tr>
                    <td>${nombreCompleto}</td>
                    <td>${paciente.document_number || 'No especificado'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-select-paciente" 
                                data-paciente-id="${paciente.person_id}" 
                                data-paciente-nombre="${nombreCompleto}">
                            <i class="fas fa-check"></i> Seleccionar
                        </button>
                    </td>
                </tr>
            `;
        });
    } else {
        html = '<tr><td colspan="3" class="text-center">No se encontraron pacientes</td></tr>';
    }

    $('#tablaPacientesNew tbody').html(html);
}

// Action for patient selection
$(document).on('click', '.btn-select-paciente', function () {
    const pacienteId = $(this).data('paciente-id');
    const pacienteNombre = $(this).data('paciente-nombre');

    // Update UI
    $('#selectPacienteNew').val(pacienteId);

    // Highlight selected patient
    $('#tablaPacientesNew tbody tr').removeClass('selected');
    $(this).closest('tr').addClass('selected');
    // Update header info and summary
    $('#pacienteNombreMostrar').text(pacienteNombre);
    $('#resumenPacienteNew').text(pacienteNombre);

    // Check if form is complete after selecting patient
    verificarFormularioCompleto();

    // Focus on the fecha element to guide user to next step
    setTimeout(function () {
        $('#fechaReservaNew').focus();
    }, 300);
});

/**
 * Load available time slots for a specific doctor and service
 */
function cargarHorariosDisponibles() {
    const medicoId = $('#selectMedicoNew').val();
    const fecha = $('#fechaReservaNew').val();
    const servicioId = $('#servicioSelect').val();

    console.log('Cargando horarios para - Fecha:', fecha, 'Médico ID:', medicoId, 'Servicio ID:', servicioId);
    
    if (!medicoId || !fecha) {
        console.warn('No se puede cargar horarios, falta médico o fecha');
        $('#contenedorHorariosNew').html('<div class="text-center py-3 text-warning">Por favor seleccione un médico y una fecha</div>');
        return;
    }

    // Show loading
    $('#contenedorHorariosNew').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Cargando horarios disponibles...</div>');
    
    // AJAX call to get available time slots
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerHorariosDisponibles', // Cambiado de 'accion' a 'action' para coincidir con el backend
            doctor_id: medicoId,
            fecha: fecha,
            servicio_id: servicioId || 0
        },
        dataType: 'json',
        success: function (respuesta) {
            console.log('Respuesta horarios:', respuesta);
            if (respuesta && respuesta.status === 'success') {
                if (respuesta.data && respuesta.data.length > 0) {
                    mostrarHorariosDisponibles(respuesta.data);
                } else {
                    $('#contenedorHorariosNew').html('<div class="text-center py-3 text-info">No hay horarios disponibles para este médico en la fecha seleccionada</div>');
                }
            } else {
                console.error('Error al cargar horarios:', respuesta ? respuesta.message : 'Respuesta inválida');
                $('#contenedorHorariosNew').html('<div class="text-center py-3 text-danger">Error: ' + (respuesta && respuesta.message ? respuesta.message : 'No se pudo cargar los horarios') + '</div>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar horarios:', error);
            console.log('Respuesta completa:', xhr.responseText);
            try {
                const respuesta = JSON.parse(xhr.responseText);
                console.log('Respuesta JSON:', respuesta);
            } catch (e) {
                console.log('No se pudo parsear la respuesta como JSON');
            }            $('#contenedorHorariosNew').html('<div class="text-center py-3 text-danger">Error al cargar los horarios</div>');
        }
    });
}
}

/**
 * Display available time slots
 * @param {Array} horarios - Array of available time slots from the server
 */
function mostrarHorariosDisponibles(horarios) {
    let html = '';

    if (horarios && horarios.length > 0) {
        html = '<div class="horarios-grid">'; horarios.forEach(function (horario) {
            // La estructura de datos puede variar según el backend, adaptamos el código
            const horaInicio = horario.hora_inicio || horario.hora || '';
            const horaFin = horario.hora_fin || '';
            const horarioId = horario.horario_id || ''; // This is the main ID
            const agendaId = horario.agenda_id || horarioId || ''; // Use agenda_id if available, fallback to horario_id
            const disponible = horario.disponible !== false; // Asumimos disponible a menos que se indique lo contrario

            const disponibleClass = disponible ? '' : 'no-disponible';
            const horarioTexto = horaFin ? `${horaInicio} - ${horaFin}` : horaInicio;

            html += `
                <div class="hora-slot ${disponibleClass}" 
                     data-id="${horarioId}"
                     data-inicio="${horaInicio}"
                     data-fin="${horaFin}" 
                     data-agenda-id="${agendaId}"
                     data-texto="${horarioTexto}"
                     data-disponible="${disponible ? 'true' : 'false'}">
                    <span class="hora-texto">${horarioTexto}</span>
                    <button class="btn-select-horario" ${!disponible ? 'disabled' : ''}>
                        <i class="fas fa-check"></i>
                    </button>
                    <input type="hidden" class="agenda-id-value" value="${agendaId}">
                </div>
            `;
        });

        html += '</div>';
    } else {
        html = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                <p>No hay horarios disponibles para este médico en la fecha seleccionada</p>
            </div>
        `;
    }

    $('#contenedorHorariosNew').html(html);

    // Agregar evento click a los slots de horario
    $('.hora-slot').click(function () {
        if ($(this).data('disponible') === 'true') {
            $('.hora-slot').removeClass('selected');
            $(this).addClass('selected');

            // Actualizar el resumen de la reserva
            const horaInicio = $(this).data('hora-inicio');
            const horaFin = $(this).data('hora-fin');
            $('#resumenHorario').text(horaFin ? `${horaInicio} - ${horaFin}` : horaInicio);

            // Guardar valores en campos ocultos para el formulario
            $('#horaInicioSeleccionada').val(horaInicio);
            $('#horaFinSeleccionada').val(horaFin);

            // Habilitar el botón de confirmar si todos los datos están completos
            verificarFormularioCompleto();
        }
    });
}

/**
 * Load services for a doctor
 */
function cargarServiciosMedico(medicoId) {
    if (!medicoId) return;

    const fecha = $('#fechaReservaNew').val();
    if (!fecha) return;

    // Mostrar spinner o mensaje de carga
    $('#servicioSelect').html('<option value="">Cargando servicios...</option>');

    console.log(`Cargando servicios para médico ID: ${medicoId}, fecha: ${fecha}`);

    // AJAX call to get doctor's services
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerServiciosPorFechaMedico',
            fecha: fecha,
            doctor_id: medicoId
        },
        dataType: 'json',
        success: function (respuesta) {
            console.log('Respuesta servicios:', respuesta);

            // Clear previous options
            $('#servicioSelect').html('<option value="">Seleccione un servicio</option>');

            // Add new options
            let servicios = [];

            // Manejar diferentes formatos de respuesta
            if (respuesta && respuesta.status === 'success' && respuesta.data) {
                servicios = respuesta.data;
            } else if (Array.isArray(respuesta)) {
                servicios = respuesta;
            } else if (respuesta && typeof respuesta === 'object' && !respuesta.status) {
                servicios = [respuesta]; // Si es un solo objeto
            }

            console.log('Servicios procesados:', servicios);

            if (servicios && servicios.length > 0) {
                servicios.forEach(function (servicio) {
                    // Extraer propiedades con diferentes posibles nombres
                    const id = servicio.id || servicio.servicio_id || 0;
                    const nombre = servicio.nombre || servicio.servicio_nombre || servicio.name || 'Servicio sin nombre';
                    const precio = servicio.precio_base || servicio.precio || 0;

                    $('#servicioSelect').append(`
                        <option value="${id}" data-precio="${precio}">
                            ${nombre}
                        </option>
                    `);
                });
            } else {
                // Si no hay servicios, mostrar mensaje
                console.warn('No se encontraron servicios disponibles');
                $('#servicioSelect').append('<option value="">No hay servicios disponibles</option>');
            }

            // Update price if needed
            const precio = $('#servicioSelect option:selected').data('precio');
            if (precio) {
                $('#importeReservaNew').val(formatearPrecio(precio));
                $('#resumenImporteNew').text('S/ ' + formatearPrecio(precio));
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar servicios:', error);
            console.log('Respuesta servicios error:', xhr.responseText);
        }
    });
}

/**
 * Load insurance plans
 */
function cargarPlanesSeguro(seguroId) {
    if (!seguroId || seguroId == "0") {
        $('#planSelect').html('<option value="0">Sin plan</option>').prop('disabled', true);
        return;
    }

    // Since we don't have a specific action for plans in the API yet,
    // show no plans available message
    $('#planSelect').html('<option value="0">Sin planes disponibles</option>').prop('disabled', true);

    /* Uncomment this when the API endpoint is available
    // AJAX call to get insurance plans
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerPlanes',
            seguroId: seguroId
        },
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta planes:', respuesta);
            
            // Clear previous options
            $('#planSelect').html('<option value="0">Seleccione un plan</option>').prop('disabled', false);
            
            // Add new options
            if (respuesta && respuesta.length > 0) {
                respuesta.forEach(function(plan) {
                    $('#planSelect').append(`<option value="${plan.id}">${plan.nombre || plan.name}</option>`);
                });
            } else {
                $('#planSelect').html('<option value="0">No hay planes disponibles</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar planes:', error);
            console.log('Respuesta planes error:', xhr.responseText);
        }
    });
    */
}

/**
 * Update price based on insurance plan
 */
function actualizarPrecioPlan(planId) {
    const servicioId = $('#servicioSelect').val();
    if (!servicioId || !planId) return;

    // For now, we'll calculate a discount based on the plan
    // This is a placeholder until the API endpoint is available
    const precioBase = $('#servicioSelect option:selected').data('precio') || 0;

    // Apply discount based on plan
    let precioFinal = precioBase;
    if (planId == 1) {
        precioFinal = precioBase * 0.9; // 10% discount for Basic plan
    } else if (planId == 2) {
        precioFinal = precioBase * 0.8; // 20% discount for Standard plan
    } else if (planId == 3) {
        precioFinal = precioBase * 0.7; // 30% discount for Premium plan
    }

    // Update displayed price
    $('#importeReservaNew').val(formatearPrecio(precioFinal));
    $('#resumenImporteNew').text('S/ ' + formatearPrecio(precioFinal));

    /* Uncomment this when the API endpoint is available
    // AJAX call to get price for plan and service
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerPrecioPlan',
            planId: planId,
            servicioId: servicioId
        },
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta precio:', respuesta);
            if (respuesta && respuesta.precio) {
                $('#importeReservaNew').val(formatearPrecio(respuesta.precio));
                $('#resumenImporteNew').text('S/ ' + formatearPrecio(respuesta.precio));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al obtener precio:', error);
            console.log('Respuesta precio error:', xhr.responseText);
        }
    });
    */
}

/**
 * Format price for display
 */
function formatearPrecio(precio) {
    return parseFloat(precio).toFixed(2);
}

/**
 * Format a price for display
 */
function formatPrice(price) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(price);
}

/**
 * Show alert message
 */
function mostrarAlerta(tipo, mensaje) {
    Swal.fire({
        icon: tipo,
        title: mensaje,
        showConfirmButton: false,
        timer: 2000
    });
}

/**
 * Save reservation
 */
function guardarReserva() {
    // Get form data
    const medicoId = $('#selectMedicoNew').val();
    const pacienteId = $('#selectPacienteNew').val();
    const fecha = $('#fechaReservaNew').val();
    // Check multiple possible hour fields (same logic as verificarFormularioCompleto)
    const hora = $('#horaInicioSeleccionada').val() ||
        $('#horaSeleccionada').val() ||
        $('.hora-btn.btn-success').data('hora-inicio') ||
        $('.hora-slot.selected').data('hora') ||
        '';

    const horaFin = $('#horaFinSeleccionada').val() ||
        $('.hora-btn.btn-success').data('hora-fin') ||
        hora; // Use same as start if no end time
    const servicioId = $('#servicioSelect').val();
    const seguroId = $('#seguroSelect').val();
    const planId = $('#planSelect').val();
    const agendaId = $('#agendaId').val(); // Get the agenda_id
    const importe = $('#importeReservaNew').val().replace('S/ ', '');
    const observaciones = $('#observacionesNew').val(); console.log('Datos del formulario para guardar:', {
        medicoId: medicoId,
        pacienteId: pacienteId,
        fecha: fecha,
        hora: hora,
        horaFin: horaFin,
        agendaId: agendaId,
        servicioId: servicioId,
        seguroId: seguroId,
        planId: planId,
        horaInicioSeleccionada: $('#horaInicioSeleccionada').val(),
        horaFinSeleccionada: $('#horaFinSeleccionada').val(),
        horaSeleccionada: $('#horaSeleccionada').val(),
        btnSuccessCount: $('.hora-btn.btn-success').length,
        btnSuccessData: $('.hora-btn.btn-success').data('hora-inicio'),
        btnSuccessFinData: $('.hora-btn.btn-success').data('hora-fin'),
        btnSuccessHorarioId: $('.hora-btn.btn-success').data('horario-id')
    });

    // Validate required fields
    if (!medicoId) {
        mostrarAlerta('warning', 'Por favor seleccione un médico');
        return;
    }

    if (!pacienteId) {
        mostrarAlerta('warning', 'Por favor seleccione un paciente');
        return;
    }

    if (!fecha) {
        mostrarAlerta('warning', 'Por favor seleccione una fecha');
        return;
    }

    if (!hora) {
        mostrarAlerta('warning', 'Por favor seleccione un horario');
        return;
    }

    if (!servicioId) {
        mostrarAlerta('warning', 'Por favor seleccione un servicio');
        return;
    }    // Prepare data for AJAX - matching server parameter names
    const datos = {
        action: 'guardarReserva',  // Changed from 'accion' to 'action'
        doctor_id: medicoId,
        paciente_id: pacienteId,
        fecha_reserva: fecha,
        hora_inicio: hora,
        hora_fin: horaFin,
        servicio_id: servicioId,
        seguro_id: seguroId || 0,
        agenda_id: agendaId || 0, // Add agenda_id
        observaciones: observaciones
    };

    // Show confirmation dialog
    Swal.fire({
        title: '¿Guardar reserva?',
        text: 'Se creará una nueva reserva con los datos ingresados',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {            // AJAX call to save reservation
            $.ajax({
                url: 'ajax/servicios.ajax.php',
                method: 'POST',
                data: datos,
                dataType: 'json', success: function (respuesta) {
                    console.log('Respuesta del servidor:', respuesta);
                    if (respuesta.status === 'success') {
                        Swal.fire({
                            title: '¡Reserva guardada!',
                            text: respuesta.message || 'La reserva se ha guardado correctamente',
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {                            // Reset form and refresh data
                            limpiarFormularioReserva();

                            // Recargar las reservas por fecha
                            const fechaActual = $('#fechaReservaNew').val();
                            if (fechaActual) {
                                cargarReservasPorFecha(fechaActual);
                            }

                            // Reload reservations if we're showing them (for other tabs)
                            if (typeof cargarReservas === 'function') {
                                cargarReservas();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: respuesta.message || 'No se pudo guardar la reserva',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error al guardar reserva:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema al guardar la reserva',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }
    });
}

/**
 * Clear and reset the reservation form
 */
function limpiarFormularioReserva() {
    // Clear all form fields
    $('#pacienteIdNew').val('');
    $('#pacienteNombreNew').val('');
    $('#medicoIdNew').val('');
    $('#medicoNombreNew').val('');
    $('#selectMedicoNew').val('');
    $('#horaInicioNew').val('');
    $('#horaFinNew').val('');
    $('#horaInicioSeleccionada').val('');
    $('#horaFinSeleccionada').val('');
    $('#precioBrutoServicio').val(0);
    $('#precioFinalNew').val(0);
    $('#observacionesReserva').val('');

    // Reset select fields
    $('#servicioSelect').val('');
    $('#seguroSelect').val(0);
    // Reset search fields
    $('#buscarPacienteNew').val('');
    $('#buscarMedicoNew').val('').prop('readonly', false).removeClass('selected-doctor');

    // Restaurar botones de búsqueda
    $('#btnBuscarMedicoNew').removeClass('d-none');
    $('#btnCambiarMedicoNew').addClass('d-none');

    // Reset date to today
    $('#fechaReservaNew').val(moment().format('YYYY-MM-DD'));

    // Clear UI selections
    $('.fila-medico').removeClass('fila-seleccionada');
    $('.hora-btn').removeClass('btn-primary').addClass('btn-outline-primary');
    $('#pacienteSeleccionadoCard').addClass('d-none');
    // Reset results containers
    $('#pacienteResults').empty();
    $('#medicoResults').empty();
    $('#horariosDisponibles').empty();

    // Ocultar el resumen de horarios
    $('#resumenHorariosNew').addClass('d-none');

    // Reset summary
    $('#resumenPacienteNew').text('(No seleccionado)');
    $('#resumenFechaNew').text(moment().format('DD/MM/YYYY'));
    $('#resumenMedicoNew').text('(No seleccionado)');
    $('#resumenHorarioNew').text('(No seleccionado)');
    $('#resumenServicioNew').text('(No seleccionado)');
    $('#resumenSeguroNew').text('Sin seguro');
    $('#resumenPrecioNew').text('$0.00');

    // Reset steps
    $('.paso').removeClass('paso-completo');
    $('.paso:not(#paso1)').addClass('paso-disabled');

    // Re-enable submit button but keep it disabled until form is complete
    $('#btnConfirmarReserva').prop('disabled', true).html('Confirmar Reserva');

    // Focus back to patient search
    setTimeout(function () {
        $('#buscarPacienteNew').focus();
    }, 300);
}

/**
 * Utility function to show alerts
 */
function mostrarAlerta(tipo, mensaje) {
    let icon = 'info';
    let title = 'Información';

    switch (tipo) {
        case 'success':
            icon = 'success';
            title = 'Éxito';
            break;
        case 'error':
            icon = 'error';
            title = 'Error';
            break;
        case 'warning':
            icon = 'warning';
            title = 'Advertencia';
            break;
    }

    Swal.fire({
        icon: icon,
        title: title,
        text: mensaje
    });
}

/**
 * Load insurance providers
 */
function cargarSeguros() {
    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: {
            action: "obtenerProveedoresSeguro"
        },
        dataType: "json",
        beforeSend: function () {
            $('#seguroSelect').html('<option value="0">Cargando seguros médicos...</option>');
        },
        success: function (respuesta) {
            console.log("Respuesta de proveedores de seguro:", respuesta);

            $('#seguroSelect').html('<option value="0">Sin seguro</option>');

            if (respuesta.data && respuesta.data.length > 0) {
                respuesta.data.forEach(function (proveedor) {
                    const proveedorId = proveedor.prov_id || proveedor.id;
                    const proveedorNombre = proveedor.prov_razon ||
                        (proveedor.prov_name + ' ' + proveedor.prov_lastname) ||
                        proveedor.nombre ||
                        'Proveedor sin nombre';

                    $('#seguroSelect').append(`<option value="${proveedorId}">${proveedorNombre}</option>`);
                });
            } else {
                console.warn('No se encontraron proveedores de seguro.');
            }
        },
        error: function (xhr) {
            console.error("Error al cargar proveedores de seguro:", xhr);
            $('#seguroSelect').html('<option value="0">Sin seguro</option>');
        }
    });
}

/**
 * Load initial services
 */
function cargarServiciosIniciales() {
    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: {
            action: "obtenerServicios"
        },
        dataType: "json",
        beforeSend: function () {
            $('#servicioSelect').html('<option value="">Cargando servicios...</option>');
        },
        success: function (respuesta) {
            console.log('Respuesta servicios iniciales:', respuesta);

            $('#servicioSelect').html('<option value="">Seleccione un servicio</option>');

            if (respuesta.data && respuesta.data.length > 0) {
                respuesta.data.forEach(function (servicio) {
                    // Verificar las diferentes propiedades que puede tener el objeto servicio
                    const servicioId = servicio.servicio_id || servicio.id || 0;
                    const servicioNombre = servicio.servicio_nombre || servicio.nombre || servicio.name || 'Servicio sin nombre';
                    const precio = servicio.precio_base || servicio.precio || 0;

                    $('#servicioSelect').append(`
                        <option value="${servicioId}" data-precio="${precio}">
                            ${servicioNombre}
                        </option>
                    `);
                });
            } else {
                console.warn('No se encontraron servicios disponibles.');
                $('#servicioSelect').html('<option value="">No hay servicios disponibles</option>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar servicios iniciales:', xhr);
            $('#servicioSelect').html('<option value="">Error al cargar servicios</option>');
        }
    });
}

/**
 * Load services by doctor and date (more specific than cargarServiciosIniciales)
 */
function cargarServiciosPorFechaMedico(fecha, doctorId) {
    if (!fecha || !doctorId) {
        console.warn('cargarServiciosPorFechaMedico: Se requiere fecha y doctorId');
        return;
    }

    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: {
            action: "obtenerServiciosPorFechaMedico",
            fecha: fecha,
            doctor_id: doctorId
        },
        dataType: "json",
        beforeSend: function () {
            $('#servicioSelect').html('<option value="">Cargando servicios...</option>');
        },
        success: function (respuesta) {
            console.log("Respuesta de servicios por fecha y médico:", respuesta);

            $('#servicioSelect').html('<option value="">Seleccione un servicio</option>');

            if (respuesta.data && respuesta.data.length > 0) {
                respuesta.data.forEach(function (servicio) {
                    // Verificar qué propiedades trae el objeto servicio
                    if (servicio.servicio_id) {
                        // Si viene con servicio_id y servicio_nombre (formato de la API)
                        const precio = servicio.precio_base || servicio.precio || 0;
                        $('#servicioSelect').append(`<option value="${servicio.servicio_id}" data-precio="${precio}">${servicio.servicio_nombre}</option>`);
                    } else if (servicio.id) {
                        // Si viene con id y nombre (formato antiguo)
                        const precio = servicio.precio_base || servicio.precio || 0;
                        $('#servicioSelect').append(`<option value="${servicio.id}" data-precio="${precio}">${servicio.nombre}</option>`);
                    } else if (servicio.message) {
                        // Si es un mensaje de error/advertencia
                        console.warn("Mensaje desde API:", servicio.message);
                    }
                });
            } else {
                $('#servicioSelect').html('<option value="">No hay servicios disponibles</option>');
                console.warn('El médico seleccionado no tiene servicios disponibles para esta fecha.');
            }
        },
        error: function (xhr) {
            console.error("Error al cargar servicios por fecha y médico:", xhr);
            $('#servicioSelect').html('<option value="">Error al cargar servicios</option>');
        }
    });
}

/**
 * Cargar reservas por fecha seleccionada
 * @param {string} fecha - Fecha en formato YYYY-MM-DD
 */
function cargarReservasPorFecha(fecha) {
    console.log('Cargando reservas para la fecha:', fecha);

    if (!fecha) {
        console.error('No se proporcionó una fecha válida');
        $('#tablaReservasPorFecha tbody').html('<tr><td colspan="5" class="text-center">Seleccione una fecha para ver las reservas</td></tr>');
        return;
    }

    // Mostrar indicador de carga
    $('#tablaReservasPorFecha tbody').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando reservas...</td></tr>');

    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'buscarReservas',
            fecha: fecha
        },
        dataType: 'json',
        success: function (response) {
            console.log('Respuesta de reservas:', response);

            if (response.status === 'success' && response.data && response.data.length > 0) {
                let html = '';

                response.data.forEach(function (reserva) {
                    const horaFormateada = reserva.hora_inicio + ' - ' + reserva.hora_fin;
                    let estadoClass = '';

                    // Determinar la clase CSS para el estado
                    switch (reserva.reserva_estado) {
                        case 'PENDIENTE':
                            estadoClass = 'badge badge-warning';
                            break;
                        case 'CONFIRMADA':
                            estadoClass = 'badge badge-success';
                            break;
                        case 'COMPLETADA':
                            estadoClass = 'badge badge-info';
                            break;
                        case 'CANCELADA':
                            estadoClass = 'badge badge-danger';
                            break;
                        default:
                            estadoClass = 'badge badge-secondary';
                    }

                    html += `
                    <tr>
                        <td>${horaFormateada}</td>
                        <td>${reserva.doctor}</td>
                        <td>${reserva.paciente}</td>
                        <td>${reserva.serv_descripcion}</td>
                        <td><span class="${estadoClass}">${reserva.reserva_estado}</span></td>
                    </tr>`;
                });

                $('#tablaReservasPorFecha tbody').html(html);
            } else {
                $('#tablaReservasPorFecha tbody').html('<tr><td colspan="5" class="text-center">No hay reservas para esta fecha</td></tr>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar reservas:', error);
            $('#tablaReservasPorFecha tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar las reservas</td></tr>');
        }
    });
}

/**
 * Verify if the reservation form is complete
 * Enable or disable the submit button accordingly
 */
function verificarFormularioCompleto() {
    // Get form values
    const pacienteId = $('#pacienteIdNew').val() || $('#selectPacienteNew').val();
    const fecha = $('#fechaReservaNew').val();
    const medicoId = $('#selectMedicoNew').val();
    const servicioId = $('#servicioSelect').val();

    // Check multiple possible hour fields
    const horaInicio = $('#horaInicioSeleccionada').val() ||
        $('#horaSeleccionada').val() ||
        $('.hora-btn.btn-success').data('hora-inicio') ||
        $('.hora-slot.selected').data('hora') ||
        '';

    // Additional checks for UI state
    const horaSeleccionadaUI = $('.hora-btn.btn-success').length > 0 ||
        $('.hora-slot.selected').length > 0;

    // Check if all required fields have values
    const formularioCompleto = pacienteId && fecha && medicoId && servicioId && (horaInicio || horaSeleccionadaUI);
    console.log('Verificando formulario completo:', {
        pacienteId: !!pacienteId,
        fecha: !!fecha,
        medicoId: !!medicoId,
        servicioId: !!servicioId,
        horaInicio: horaInicio,
        horaSeleccionadaUI: horaSeleccionadaUI,
        detalleHora: {
            horaInicioSeleccionada: $('#horaInicioSeleccionada').val(),
            horaSeleccionada: $('#horaSeleccionada').val(),
            btnSuccessCount: $('.hora-btn.btn-success').length,
            slotSelectedCount: $('.hora-slot.selected').length,
            btnSuccessData: $('.hora-btn.btn-success').data('hora-inicio')
        },
        completo: formularioCompleto
    });

    // Enable or disable the submit button
    if (formularioCompleto) {
        $('#btnConfirmarReserva').prop('disabled', false);
        console.log('Formulario completo - habilitando botón de confirmación');
    } else {
        $('#btnConfirmarReserva').prop('disabled', true);
        console.log('Formulario incompleto - deshabilitando botón de confirmación');
    }

    return formularioCompleto;
}