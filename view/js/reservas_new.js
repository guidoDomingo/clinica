/**
 * JavaScript for the new Reservas New section
 */

$(document).ready(function() {
    console.log('Inicializando módulo Reservas New');
    inicializarReservasNew();
    
    // Submit button click handler
    $('#btnConfirmarReserva').on('click', function() {
        submitReservaForm();
    });
    
    // Debug logging for hora-btn clicks
    $(document).on('click', '.hora-btn', function() {
        logDataAttributes(this, 'Hora Button Clicked:');
    });
});

/**
 * Initialize the Reservas New module
 */
function inicializarReservasNew() {
    console.log('Inicializando módulo Reservas New');
    
    // Focus on patient search first as it's now step 1
    setTimeout(function() {
        $('#buscarPacienteNew').focus();
    }, 300);
    
    // Set up initial date
    $('#fechaReservaNew').val(moment().format('YYYY-MM-DD'));
    const fechaFormateada = moment().format('DD/MM/YYYY');
    $('#resumenFechaNew').text(fechaFormateada);
    
    // Patient search on Enter key (priority as first step)
    $('#buscarPacienteNew').keyup(function(e) {
        if (e.keyCode === 13) {
            buscarPaciente();
        }
    });
    
    // Patient search button click
    $('#btnBuscarPacienteNew').click(function() {
        buscarPaciente();
    });
    
    // Search for available doctors on date change (step 2)
    $('#fechaReservaNew').change(function() {
        buscarMedicosDisponibles();
    });
    
    // Doctor search on Enter key
    $('#buscarMedicoNew').keyup(function(e) {
        if (e.keyCode === 13) {
            buscarMedicos();
        }
    });
    
    // Doctor search button click
    $('#btnBuscarMedicoNew').click(function() {
        buscarMedicos();
    });    // Doctor selection event
    $(document).on('click', '.btn-select-doctor', function() {
        const medicoId = $(this).data('medico-id');
        const medicoNombre = $(this).data('medico-nombre');
        
        // Update UI
        $('#selectMedicoNew').val(medicoId);
        $('#medicoNombreMostrar').text(medicoNombre);
        
        // Highlight selected doctor
        $('#tablaMedicosNew tbody tr').removeClass('selected');
        $(this).closest('tr').addClass('selected');
        
        // Update summary
        $('#resumenMedicoNew').text(medicoNombre);
        
        // Load doctor's services        cargarServiciosMedico(medicoId);
        
        // Remove any existing time slot rows
        $('.horario-row').remove();
        
        const fecha = $('#fechaReservaNew').val();
        const servicioId = $('#servicioSelect').val() || 0;
        const doctorRow = $(this).closest('tr');
        
        // Mostrar loader para los horarios
        doctorRow.after(`
            <tr class="horario-row loading-slots">
                <td colspan="5" class="text-center py-3">
                    <i class="fas fa-spinner fa-spin mr-2"></i> Cargando horarios disponibles...
                </td>
            </tr>
        `);
        
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
            dataType: 'json',            success: function(respuesta) {
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
                setTimeout(function() {
                    $('html, body').animate({
                        scrollTop: doctorRow.next().offset().top - 100
                    }, 300);
                }, 100);
            },
            error: function(xhr, status, error) {
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
    $(document).on('click', '.hora-slot', function() {
        if ($(this).hasClass('no-disponible')) {
            mostrarAlerta('warning', 'Este horario no está disponible');
            return;
        }
        
        const hora = $(this).data('hora');
        
        // Update UI
        $('.hora-slot').removeClass('selected');
        $(this).addClass('selected');
        $('#horaSeleccionada').val(hora);
        
        // Update summary
        $('#resumenHoraNew').text(hora);
    });    // Action for time slot selection from the table
    $(document).on('click', '.hora-btn', function() {
        console.log("Hora button clicked", this);
        
        // Get data attributes
        const horaInicio = $(this).data('hora-inicio');
        const horaFin = $(this).data('hora-fin');
        
        console.log("Data attributes extracted:", {
            horaInicio: horaInicio,
            horaFin: horaFin
        });
        
        if (!horaInicio) {
            console.error("No se encontró el atributo data-hora-inicio en el botón");
            alert("Error al seleccionar el horario. Por favor intente nuevamente.");
            return;
        }
        
        const horarioTexto = horaFin ? `${horaInicio} - ${horaFin}` : horaInicio;
        console.log("Horario seleccionado:", horarioTexto);
        
        // Visual feedback
        $('.hora-btn').removeClass('btn-success').addClass('btn-primary');
        $(this).removeClass('btn-primary').addClass('btn-success');
        
        // Store selected time
        $('#horaInicioSeleccionada').val(horaInicio);
        $('#horaFinSeleccionada').val(horaFin);
        console.log("Valores guardados en campos ocultos:", {
            inicio: $('#horaInicioSeleccionada').val(),
            fin: $('#horaFinSeleccionada').val()
        });
        
        // Update summary information
        $('#resumenHoraNew').text(horarioTexto);
        console.log("Resumen actualizado:", $('#resumenHoraNew').text());
        
        // Scroll to service selection as next step
        $('html, body').animate({
            scrollTop: $('#servicioSelect').offset().top - 100
        }, 500);
        
        // Focus on service selection
        setTimeout(function() {
            $('#servicioSelect').focus();
        }, 600);
    });
    
    // Service selection
    $('#servicioSelect').change(function() {
        const servicioId = $(this).val();
        const servicioNombre = $(this).find('option:selected').text();
        
        if (servicioId) {
            // Update summary
            $('#resumenServicioNew').text(servicioNombre);
            
            // Update price
            const precio = $(this).find('option:selected').data('precio');
            if (precio) {
                $('#importeReservaNew').val(formatearPrecio(precio));
                $('#resumenImporteNew').text('S/ ' + formatearPrecio(precio));
            }
            
            // Reload available time slots with service duration
            cargarHorariosDisponibles();
        }
    });
    
    // Insurance selection
    $('#seguroSelect').change(function() {
        const seguroId = $(this).val();
        const seguroNombre = $(this).find('option:selected').text();
        
        if (seguroId && seguroId != "0") {
            // Update summary
            $('#resumenSeguroNew').text(seguroNombre);
            cargarPlanesSeguro(seguroId);
        } else {
            $('#resumenSeguroNew').text('-');
            $('#planSelect').html('<option value="0">Sin plan</option>').prop('disabled', true);
        }
    });
    
    // New patient button
    $('#btnNuevoPaciente').click(function() {
        // If there's a global new patient modal/function
        if (typeof abrirModalNuevoPaciente === 'function') {
            abrirModalNuevoPaciente();
        } else {
            // Redirect to patients module if no modal function exists
            window.open('pacientes', '_blank');
        }
    });
    
    // Save reservation button
    $('#btnGuardarReservaNew').click(function() {
        guardarReserva();
    });
    
    // Load insurance options on page load
    cargarSeguros();
    
    // Initial doctors load
    buscarMedicosDisponibles();
}

// Additional functionality for Reservas New

// Handle doctor search functionality
$('#btnBuscarMedicoNew').click(function() {
    buscarMedicos();
});

$('#buscarMedicoNew').keyup(function(e) {
    if (e.keyCode === 13) {
        buscarMedicos();
    }
});

// Refresh doctors list button
$('#btnRefreshMedicos').click(function() {
    buscarMedicosDisponibles();
});

// Actualiza el resumen de fecha cuando cambia la fecha
$('#fechaReservaNew').change(function() {
    const fecha = $(this).val();
    if (fecha) {
        const fechaFormateada = moment(fecha).format('DD/MM/YYYY');
        $('#resumenFechaNew').text(fechaFormateada);
    }
});

// Plan selection functionality
$('#planSelect').change(function() {
    const planId = $(this).val();
    const planNombre = $(this).find('option:selected').text();
    
    if (planId && planId != "0") {
        actualizarPrecioPlan(planId);
    }
});

// Save reservation button
$('#btnGuardarReservaNew').click(function() {
    guardarReserva();
});

/**
 * Function to search for available doctors on a specific date
 */
function buscarMedicosDisponibles() {
    const fecha = $('#fechaReservaNew').val();
    
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
        success: function(respuesta) {
            console.log('Respuesta médicos:', respuesta);
            cargarTablaMedicos(respuesta);
        },
        error: function(xhr, status, error) {
            console.error('Error al buscar médicos:', error);
            console.log('Respuesta:', xhr.responseText);
            $('#tablaMedicosNew tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar médicos</td></tr>');
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
        success: function(respuesta) {
            cargarTablaMedicos(respuesta);
        },
        error: function(xhr, status, error) {
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
        
        medicos.forEach(function(medico) {
            // Get doctor ID and name from response
            const medicoId = medico.doctor_id || medico.id || medico.person_id;
            const medicoNombre = medico.nombre_doctor || medico.nombre || medico.nombre_completo || '';
            
            // For demo purposes, assign turnos based on counter
            const turno = counter % 3 === 0 ? 'Noche' : (counter % 2 === 0 ? 'Tarde' : 'Mañana');
            
            // Random availability between 1-20
            const disponibles = Math.floor(Math.random() * 20) + 1;
            
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
 * Generate time slot rows for a doctor
 */
function generateTimeSlotRows(...horarios) {
    let rows = '';
    
    horarios.forEach(function(hora, index) {
        // Alternating background colors based on row position
        const bgClass = index % 2 === 0 ? 'bg-light' : '';
        
        // Success (green) button for 13:00, gray for others
        const btnClass = hora === '13:00' ? 'success' : 'secondary';
        
        rows += `
            <tr class="horario-row ${bgClass}">
                <td colspan="4" class="text-right hora-column">${hora}</td>
                <td class="check-column">
                    <button class="btn btn-${btnClass} btn-circle hora-btn" 
                            data-hora="${hora}">
                        <i class="fas fa-check"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    return rows;
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
    
    let html = '<tr class="horario-row"><td colspan="5">';
    html += '<table class="table table-horarios">';
    html += '<thead><tr><th>Hora</th><th class="text-center">Check</th></tr></thead>';
    html += '<tbody>';
    
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
        horarios.forEach(function(horario) {
            // Debugging
            console.log("Procesando horario:", horario);
            
            // La estructura de datos puede variar según el backend, adaptamos el código
            const horaInicio = horario.hora_inicio || '';
            const horaFin = horario.hora_fin || '';
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
                            data-hora-inicio="${horaInicio}" 
                            data-hora-fin="${horaFin}" 
                            ${buttonDisabled}>
                        <i class="fas fa-check"></i>
                    </button>
                </td>
            </tr>
            `;
        });
    }
    
    html += '</tbody></table>';
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
    $.each($el[0].attributes, function() {
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
        success: function(respuesta) {
            if (respuesta && respuesta.data) {
                cargarTablaPacientes(respuesta.data);
            } else {
                $('#tablaPacientesNew tbody').html('<tr><td colspan="3" class="text-center">No se encontraron pacientes</td></tr>');
            }
        },
        error: function(xhr, status, error) {
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
        pacientes.forEach(function(paciente) {
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
$(document).on('click', '.btn-select-paciente', function() {
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
    
    // Focus on the fecha element to guide user to next step
    setTimeout(function() {
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
    
    if (!medicoId || !fecha) {
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
        success: function(respuesta) {
            console.log('Respuesta horarios:', respuesta);
            if (respuesta.status === 'success') {
                mostrarHorariosDisponibles(respuesta.data);
            } else {
                console.error('Error al cargar horarios:', respuesta.message);
                $('#contenedorHorariosNew').html('<div class="text-center py-3 text-danger">Error: ' + respuesta.message + '</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar horarios:', error);
            $('#contenedorHorariosNew').html('<div class="text-center py-3 text-danger">Error al cargar los horarios</div>');
        }
    });
}

/**
 * Display available time slots
 * @param {Array} horarios - Array of available time slots from the server
 */
function mostrarHorariosDisponibles(horarios) {
    let html = '';
    
    if (horarios && horarios.length > 0) {
        html = '<div class="horarios-grid">';
        
        horarios.forEach(function(horario) {
            // La estructura de datos puede variar según el backend, adaptamos el código
            const horaInicio = horario.hora_inicio || horario.hora || '';
            const horaFin = horario.hora_fin || '';
            const disponible = horario.disponible !== false; // Asumimos disponible a menos que se indique lo contrario
            
            const disponibleClass = disponible ? '' : 'no-disponible';
            const horarioTexto = horaFin ? `${horaInicio} - ${horaFin}` : horaInicio;
            
            html += `
                <div class="hora-slot ${disponibleClass}" 
                     data-hora-inicio="${horaInicio}"
                     data-hora-fin="${horaFin}" 
                     data-disponible="${disponible ? 'true' : 'false'}">
                    <span class="hora-texto">${horarioTexto}</span>
                    <button class="btn-select-horario" ${!disponible ? 'disabled' : ''}>
                        <i class="fas fa-check"></i>
                    </button>
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
    $('.hora-slot').click(function() {
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
    
    // AJAX call to get doctor's services
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerServiciosPorFechaMedico',
            fecha: fecha,
            doctorId: medicoId
        },
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta servicios:', respuesta);
            
            // Clear previous options
            $('#servicioSelect').html('<option value="">Seleccione un servicio</option>');
            
            // Add new options
            if (respuesta && respuesta.length > 0) {
                respuesta.forEach(function(servicio) {
                    $('#servicioSelect').append(`
                        <option value="${servicio.id}" data-precio="${servicio.precio_base}">
                            ${servicio.nombre}
                        </option>
                    `);
                });
            }
            
            // Update price if needed
            const precio = $('#servicioSelect option:selected').data('precio');
            if (precio) {
                $('#importeReservaNew').val(formatearPrecio(precio));
                $('#resumenImporteNew').text('S/ ' + formatearPrecio(precio));
            }
        },
        error: function(xhr, status, error) {
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
    // we'll add placeholder plans for now
    $('#planSelect').html('<option value="0">Seleccione un plan</option>').prop('disabled', false);
    
    // Add some sample plans
    const planes = [
        { id: 1, nombre: 'Plan Básico' },
        { id: 2, nombre: 'Plan Estándar' },
        { id: 3, nombre: 'Plan Premium' }
    ];
    
    planes.forEach(function(plan) {
        $('#planSelect').append(`<option value="${plan.id}">${plan.nombre}</option>`);
    });
    
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
    const hora = $('#horaSeleccionada').val();
    const servicioId = $('#servicioSelect').val();
    const seguroId = $('#seguroSelect').val();
    const planId = $('#planSelect').val();
    const importe = $('#importeReservaNew').val().replace('S/ ', '');
    const observaciones = $('#observacionesNew').val();
    
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
    }
    
    // Prepare data for AJAX
    const datos = {
        accion: 'guardarReserva',
        medicoId: medicoId,
        pacienteId: pacienteId,
        fecha: fecha,
        hora: hora,
        servicioId: servicioId,
        seguroId: seguroId || 0,
        planId: planId || 0,
        importe: importe,
        observaciones: observaciones,
        estado: 'PENDIENTE'
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
        if (result.isConfirmed) {
            // AJAX call to save reservation
            $.ajax({
                url: 'ajax/reservas.ajax.php',
                method: 'POST',
                data: datos,
                dataType: 'json',
                success: function(respuesta) {
                    if (respuesta.ok) {
                        Swal.fire({
                            title: '¡Reserva guardada!',
                            text: respuesta.mensaje || 'La reserva se ha guardado correctamente',
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            // Reset form and refresh data
                            limpiarFormulario();
                            // Reload reservations if we're showing them
                            if (typeof cargarReservas === 'function') {
                                cargarReservas();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: respuesta.mensaje || 'No se pudo guardar la reserva',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function(xhr, status, error) {
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
 * Clean form and start over
 */
function limpiarFormulario() {
    // Reset form values
    $('#selectMedicoNew').val('');
    $('#selectPacienteNew').val('');
    $('#horaSeleccionada').val('');
    $('#servicioSelect').html('<option value="">Seleccione un servicio</option>');
    $('#seguroSelect').val('0');
    $('#planSelect').html('<option value="0">Seleccione un plan</option>');
    $('#importeReservaNew').val('');
    $('#observacionesNew').val('');
      // Reset tables
    $('#tablaMedicosNew tbody').html('<tr><td colspan="5" class="text-center">Seleccione una fecha para ver médicos disponibles</td></tr>');
    $('#tablaPacientesNew tbody').html('<tr><td colspan="3" class="text-center">Ingrese un término para buscar pacientes</td></tr>');
    
    // Reset time slots
    $('#contenedorHorariosNew').html(`
        <div class="text-center text-muted py-4">
            <i class="fas fa-calendar-day fa-3x mb-3"></i>
            <p>Complete los pasos 1 y 2 para ver horarios disponibles</p>
        </div>
    `);
    
    // Reset summary information
    $('#resumenMedicoNew').text('-');
    $('#resumenPacienteNew').text('-');
    $('#resumenFechaNew').text('-');
    $('#resumenHoraNew').text('-');
    $('#resumenServicioNew').text('-');
    $('#resumenSeguroNew').text('-');
    $('#resumenImporteNew').text('S/ 0.00');
    
    // Reset search fields
    $('#buscarMedicoNew').val('');
    $('#buscarPacienteNew').val('');
    
    // Reset display elements
    $('#pacienteNombreMostrar').text('Ningún paciente seleccionado');
    $('#medicoNombreMostrar').text('Ningún médico seleccionado');
    
    // Focus on patient search as it's the first step
    setTimeout(function() {
        $('#buscarPacienteNew').focus();
    }, 300);
}

/**
 * Load insurance options
 */
function cargarSeguros() {
    // AJAX call to get insurance options
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerProveedoresSeguro'
        },
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta seguros:', respuesta);
            
            // Clear previous options
            $('#seguroSelect').html('<option value="0">Sin seguro</option>');
            
            // Add new options
            if (respuesta && respuesta.length > 0) {
                respuesta.forEach(function(seguro) {
                    $('#seguroSelect').append(`<option value="${seguro.id}">${seguro.nombre || seguro.name}</option>`);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar seguros:', error);
            console.log('Respuesta seguros error:', xhr.responseText);
        }
    });
}

/**
 * Verify if the reservation form is complete
 * Enable or disable the submit button accordingly
 */
function verificarFormularioCompleto() {
    const pacienteId = $('#pacienteIdNew').val();
    const fecha = $('#fechaReservaNew').val();
    const medicoId = $('#selectMedicoNew').val();
    const servicioId = $('#servicioSelect').val();
    const horaInicio = $('#horaInicioSeleccionada').val();
    
    // Check if all required fields have values
    const formularioCompleto = pacienteId && fecha && medicoId && servicioId && horaInicio;
    
    // Enable or disable the submit button
    if (formularioCompleto) {
        $('#btnConfirmarReserva').prop('disabled', false);
    } else {
        $('#btnConfirmarReserva').prop('disabled', true);
    }
    
    return formularioCompleto;
}

/**
 * Handle reservation form submission
 */
function submitReservaForm() {
    if (!verificarFormularioCompleto()) {
        mostrarAlerta('warning', 'Por favor complete todos los campos requeridos');
        return;
    }
    
    // Mostrar spinner en botón
    const btnConfirmar = $('#btnConfirmarReserva');
    const btnText = btnConfirmar.html();
    btnConfirmar.html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
    btnConfirmar.prop('disabled', true);
    
    // Recopilar datos del formulario
    const datos = {
        action: 'guardarReserva',
        paciente_id: $('#pacienteIdNew').val(),
        doctor_id: $('#selectMedicoNew').val(),
        servicio_id: $('#servicioSelect').val(),
        fecha_reserva: $('#fechaReservaNew').val(),
        hora_inicio: $('#horaInicioSeleccionada').val(),
        hora_fin: $('#horaFinSeleccionada').val() || $('#horaInicioSeleccionada').val(), // Si no hay hora fin, usar hora inicio
        observaciones: $('#observacionesReserva').val() || '',
        seguro_id: $('#seguroSelect').val() || 0
    };
    
    console.log('Datos de reserva a enviar:', datos);
    
    // Enviar datos a través de AJAX
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: datos,
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta de guardar reserva:', respuesta);
            
            // Restaurar botón
            btnConfirmar.html(btnText);
            btnConfirmar.prop('disabled', false);
            
            if (respuesta.status === 'success') {
                // Mostrar mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Reserva guardada!',
                    text: 'La reserva se ha guardado exitosamente',
                    showConfirmButton: true
                }).then(() => {
                    // Reiniciar formulario o redireccionar
                    limpiarFormularioReserva();
                    
                    // Opcional: recargar la página o ir a listado de reservas
                    // window.location.reload();
                });
            } else {
                // Mostrar error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: respuesta.message || 'No se pudo guardar la reserva. Intente nuevamente.'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al guardar reserva:', error);
            
            // Restaurar botón
            btnConfirmar.html(btnText);
            btnConfirmar.prop('disabled', false);
            
            // Mostrar mensaje de error
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al intentar guardar la reserva. Por favor intente nuevamente.'
            });
        }
    });
}

/**
 * Clear reservation form and reset to initial state
 */
function limpiarFormularioReserva() {
    // Limpiar campos
    $('#pacienteSearchNew').val('');
    $('#pacienteIdNew').val('');
    $('#fechaReservaNew').val('');
    $('#selectMedicoNew').val('');
    $('#servicioSelect').val('');
    $('#horaSeleccionada').val('');
    $('#horaInicioSeleccionada').val('');
    $('#horaFinSeleccionada').val('');
    $('#observacionesReserva').val('');
    $('#seguroSelect').val('');
    
    // Limpiar resumen
    $('#resumenPaciente').text('No seleccionado');
    $('#resumenFecha').text('No seleccionada');
    $('#resumenDoctor').text('No seleccionado');
    $('#resumenServicio').text('No seleccionado');
    $('#resumenHoraNew').text('No seleccionada');
    
    // Limpiar resultados
    $('#resultadosDoctores').html('');
    $('#contenedorHorariosNew').html('<p>Complete los pasos 1 y 2 para ver horarios disponibles</p>');
    
    // Deshabilitar botón confirmar
    $('#btnConfirmarReserva').prop('disabled', true);
    
    // Scrollear arriba y enfocar en búsqueda de paciente
    setTimeout(function() {
        $('html, body').animate({
            scrollTop: $('#pacienteSearchNew').offset().top - 100
        }, 300);
        $('#pacienteSearchNew').focus();
    }, 100);
}
