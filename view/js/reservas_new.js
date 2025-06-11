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
    
    // Cargar los seguros de salud
    cargarSeguros();
    
    // Cargar algunos servicios predeterminados iniciales
    cargarServiciosIniciales();
    
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
        buscarPaciente();    });
    
    // Search for available doctors on date change (step 2)
    $('#fechaReservaNew').change(function() {
        buscarMedicosDisponibles();
        
        // Si hay un médico seleccionado, actualizar sus servicios para la nueva fecha
        const medicoSeleccionado = $('#selectMedicoNew').val();
        if (medicoSeleccionado) {
            const fecha = $(this).val();
            cargarServiciosPorFechaMedico(fecha, medicoSeleccionado);
        }
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
        $(this).closest('tr').addClass('selected');        // Update summary
        $('#resumenMedicoNew').text(medicoNombre);
        
        // Load doctor's services for the selected date
        const fecha = $('#fechaReservaNew').val();
        if (fecha) {
            cargarServiciosPorFechaMedico(fecha, medicoId);
        }
        
        // Remove any existing time slot rows
        $('.horario-row').remove();
        
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
        }    });
    
    // Insurance selection
    $('#seguroSelect').change(function() {
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
                // Agregar planes demo
                $('#planSelect').html('<option value="0">Sin plan</option>');
                $('#planSelect').append('<option value="1">Plan Básico</option>');
                $('#planSelect').append('<option value="2">Plan Premium</option>');
                $('#planSelect').prop('disabled', false);
            }
        } else {
            $('#resumenSeguroNew').text('Sin seguro');
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
        success: function(respuesta) {
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
                servicios.forEach(function(servicio) {
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
                // Si no hay servicios, mostrar servicios demo
                console.warn('No se encontraron servicios. Mostrando servicios de demostración.');
                const serviciosDemo = [
                    { id: 1, nombre: 'Consulta General', precio: 150000 },
                    { id: 2, nombre: 'Examen de Rutina', precio: 200000 },
                    { id: 3, nombre: 'Diagnóstico Especializado', precio: 350000 }
                ];
                
                serviciosDemo.forEach(function(servicio) {
                    $('#servicioSelect').append(`
                        <option value="${servicio.id}" data-precio="${servicio.precio}">
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
    
    // Reset search field
    $('#buscarPacienteNew').val('');
    
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
    setTimeout(function() {
        $('#buscarPacienteNew').focus();
    }, 300);
}

/**
 * Utility function to show alerts
 */
function mostrarAlerta(tipo, mensaje) {
    let icon = 'info';
    let title = 'Información';
    
    switch(tipo) {
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
        beforeSend: function() {
            $('#seguroSelect').html('<option value="0">Cargando seguros médicos...</option>');
        },
        success: function(respuesta) {
            console.log("Respuesta de proveedores de seguro:", respuesta);
            
            $('#seguroSelect').html('<option value="0">Sin seguro</option>');
            
            if (respuesta.data && respuesta.data.length > 0) {
                respuesta.data.forEach(function(proveedor) {
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
        error: function(xhr) {
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
        beforeSend: function() {
            $('#servicioSelect').html('<option value="">Cargando servicios...</option>');
        },
        success: function(respuesta) {
            console.log('Respuesta servicios iniciales:', respuesta);
            
            $('#servicioSelect').html('<option value="">Seleccione un servicio</option>');
            
            if (respuesta.data && respuesta.data.length > 0) {
                respuesta.data.forEach(function(servicio) {
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
        error: function(xhr, status, error) {
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
        beforeSend: function() {
            $('#servicioSelect').html('<option value="">Cargando servicios...</option>');
        },
        success: function(respuesta) {
            console.log("Respuesta de servicios por fecha y médico:", respuesta);
            
            $('#servicioSelect').html('<option value="">Seleccione un servicio</option>');
            
            if (respuesta.data && respuesta.data.length > 0) {
                respuesta.data.forEach(function(servicio) {
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
        error: function(xhr) {
            console.error("Error al cargar servicios por fecha y médico:", xhr);
            $('#servicioSelect').html('<option value="">Error al cargar servicios</option>');
        }
    });
}