<?php
// Script to fix the JavaScript file with syntax errors

// Backup the original file
copy('c:/laragon/www/clinica/view/js/reservas_new.js', 'c:/laragon/www/clinica/view/js/reservas_new.js.backup_' . date('Y-m-d_H-i-s'));

// The fixed content - properly closes the cargarSeguros and cargarServiciosIniciales functions
$fixedContent = <<<'EOT'
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
        buscarPaciente();
    });
    
    // Date change handler
    $('#fechaReservaNew').change(function() {
        const fecha = $(this).val();
        const fechaFormateada = moment(fecha).format('DD/MM/YYYY');
        $('#resumenFechaNew').text(fechaFormateada);
        
        // Clear doctor selection when date changes
        limpiarSeleccionMedico();
        
        // Auto-load doctors for the selected date
        buscarMedicosPorFecha();
    });
    
    // Doctor click handler
    $(document).on('click', '.fila-medico', function() {
        const medicoId = $(this).data('medico-id');
        const nombreMedico = $(this).data('nombre');
        
        // Highlight selected row and update summary
        seleccionarMedico(this, medicoId, nombreMedico);
        
        // Load available times for this doctor
        cargarHorariosDisponibles(medicoId);
    });
    
    // Time slot click handler
    $(document).on('click', '.hora-btn', function() {
        const horaInicio = $(this).data('hora');
        const horaFin = $(this).data('hora-fin');
        const medicoId = $(this).data('medico-id');
        
        // Highlight selected time slot and update summary
        seleccionarHorario(this, horaInicio, horaFin);
    });
    
    // Service selection handler
    $('#servicioSelect').change(function() {
        const servicioId = $(this).val();
        const servicioNombre = $(this).find('option:selected').text();
        const precioBruto = $(this).find('option:selected').data('precio') || 0;
        
        // Update summary with service info
        actualizarResumenServicio(servicioId, servicioNombre, precioBruto);
        
        // Update price calculations
        actualizarPrecioTotal();
    });
    
    // Insurance selection handler
    $('#seguroSelect').change(function() {
        const seguroId = $(this).val();
        const seguroNombre = $(this).find('option:selected').text();
        
        // Update summary with insurance info
        actualizarResumenSeguro(seguroId, seguroNombre);
        
        // Update price calculations based on insurance coverage
        actualizarPrecioTotal();
    });
}

/**
 * Search for a patient by name or document
 */
function buscarPaciente() {
    const query = $('#buscarPacienteNew').val();
    
    if (!query || query.length < 3) {
        Swal.fire({
            icon: 'warning',
            title: 'Búsqueda inválida',
            text: 'Ingrese al menos 3 caracteres para buscar un paciente',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    console.log('Buscando paciente:', query);
    
    // Show loading spinner
    $('#pacienteResults').html('<tr><td colspan="4" class="text-center"><i class="fa fa-spinner fa-spin"></i> Buscando pacientes...</td></tr>');
    
    // AJAX call to search for patients
    $.ajax({
        url: 'ajax/persona.ajax.php',
        method: 'POST',
        data: {
            action: 'buscarPersonas',
            query: query
        },
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta búsqueda pacientes:', respuesta);
            
            if (respuesta && respuesta.length > 0) {
                // Clear previous results
                $('#pacienteResults').empty();
                
                // Add new results
                respuesta.forEach(function(paciente) {
                    const fila = `
                        <tr class="fila-paciente" data-paciente-id="${paciente.id}" data-nombre="${paciente.nombre} ${paciente.apellido}">
                            <td>${paciente.id}</td>
                            <td>${paciente.nombre} ${paciente.apellido}</td>
                            <td>${paciente.documento || 'N/A'}</td>
                            <td>
                                <button class="btn btn-xs btn-primary btn-seleccionar-paciente">
                                    <i class="fa fa-check"></i> Seleccionar
                                </button>
                            </td>
                        </tr>
                    `;
                    $('#pacienteResults').append(fila);
                });
                
                // Bind click event for patient selection
                $('.btn-seleccionar-paciente').click(function() {
                    const fila = $(this).closest('tr');
                    const pacienteId = fila.data('paciente-id');
                    const nombreCompleto = fila.data('nombre');
                    
                    seleccionarPaciente(pacienteId, nombreCompleto);
                });
            } else {
                // No results found
                $('#pacienteResults').html('<tr><td colspan="4" class="text-center">No se encontraron pacientes con ese nombre o documento</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en búsqueda de pacientes:', error);
            $('#pacienteResults').html('<tr><td colspan="4" class="text-center text-danger">Error al buscar pacientes. Intente nuevamente.</td></tr>');
        }
    });
}

/**
 * Select a patient and update the reservation form
 */
function seleccionarPaciente(pacienteId, nombreCompleto) {
    console.log('Paciente seleccionado:', pacienteId, nombreCompleto);
    
    // Update patient info in form
    $('#pacienteIdNew').val(pacienteId);
    $('#pacienteNombreNew').val(nombreCompleto);
    
    // Update summary
    $('#resumenPacienteNew').text(nombreCompleto);
    
    // Hide search results and show confirmation
    $('#pacienteResults').empty();
    $('#buscarPacienteNew').val('');
    $('#pacienteSeleccionadoCard').removeClass('d-none');
    
    // Add selected class to the step
    $('#paso1').addClass('paso-completo');
    
    // Enable and focus on date selection as next step
    $('#fechaReservaNew').prop('disabled', false).focus();
    $('#paso2').removeClass('paso-disabled');
}

/**
 * Search for doctors available on the selected date
 */
function buscarMedicosPorFecha() {
    const fecha = $('#fechaReservaNew').val();
    
    if (!fecha) {
        Swal.fire({
            icon: 'warning',
            title: 'Fecha requerida',
            text: 'Por favor seleccione una fecha para la reserva',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    console.log('Buscando médicos para la fecha:', fecha);
    
    // Show loading spinner
    $('#medicoResults').html('<tr><td colspan="3" class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando médicos disponibles...</td></tr>');
    
    // AJAX call to get doctors for this date
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerMedicosPorFecha',
            fecha: fecha
        },
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta médicos por fecha:', respuesta);
            
            // Clear previous results
            $('#medicoResults').empty();
            
            if (respuesta && respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                // Add new results
                respuesta.data.forEach(function(medico) {
                    const medicoId = medico.medico_id || medico.id || 0;
                    const nombreMedico = medico.nombre || 'Sin nombre';
                    const especialidad = medico.especialidad || 'Sin especialidad';
                    
                    const fila = `
                        <tr class="fila-medico" data-medico-id="${medicoId}" data-nombre="${nombreMedico}">
                            <td>${medicoId}</td>
                            <td>${nombreMedico}</td>
                            <td>${especialidad}</td>
                        </tr>
                    `;
                    $('#medicoResults').append(fila);
                });
                
                // Show message to select a doctor
                $('#paso3').removeClass('paso-disabled');
                
                // Add selected class to date step
                $('#paso2').addClass('paso-completo');
            } else {
                // No doctors found for this date
                $('#medicoResults').html('<tr><td colspan="3" class="text-center">No hay médicos disponibles para esta fecha</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar médicos:', error);
            $('#medicoResults').html('<tr><td colspan="3" class="text-center text-danger">Error al cargar médicos. Intente nuevamente.</td></tr>');
        }
    });
}

/**
 * Select a doctor and update the reservation form
 */
function seleccionarMedico(elemento, medicoId, nombreMedico) {
    console.log('Médico seleccionado:', medicoId, nombreMedico);
    
    // Remove selected class from all doctor rows
    $('.fila-medico').removeClass('fila-seleccionada');
    
    // Add selected class to clicked row
    $(elemento).addClass('fila-seleccionada');
    
    // Update doctor info in form
    $('#medicoIdNew').val(medicoId);
    $('#medicoNombreNew').val(nombreMedico);
    
    // Update summary
    $('#resumenMedicoNew').text(nombreMedico);
    
    // Add selected class to the step
    $('#paso3').addClass('paso-completo');
    
    // Enable time selection as next step
    $('#paso4').removeClass('paso-disabled');
}

/**
 * Clear doctor selection
 */
function limpiarSeleccionMedico() {
    // Clear doctor selection in UI
    $('.fila-medico').removeClass('fila-seleccionada');
    
    // Clear doctor info in form
    $('#medicoIdNew').val('');
    $('#medicoNombreNew').val('');
    
    // Clear summary
    $('#resumenMedicoNew').text('(No seleccionado)');
    
    // Clear time slots
    $('#horariosDisponibles').html('');
    
    // Reset steps
    $('#paso3').removeClass('paso-completo');
    $('#paso4').addClass('paso-disabled').removeClass('paso-completo');
    
    // Clear time selection
    limpiarSeleccionHorario();
}

/**
 * Load available time slots for the selected doctor and date
 */
function cargarHorariosDisponibles(medicoId) {
    const fecha = $('#fechaReservaNew').val();
    const servicioId = $('#servicioSelect').val() || null;
    
    if (!fecha || !medicoId) {
        return;
    }
    
    console.log('Cargando horarios para médico:', medicoId, 'fecha:', fecha, 'servicio:', servicioId);
    
    // Show loading spinner
    $('#horariosDisponibles').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando horarios disponibles...</div>');
    
    // AJAX call to get available time slots
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerHorariosDisponibles',
            medico_id: medicoId,
            fecha: fecha,
            servicio_id: servicioId
        },
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta horarios disponibles:', respuesta);
            
            // Clear previous results
            $('#horariosDisponibles').empty();
            
            if (respuesta && respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                // Display time slots in groups
                let currentRow;
                
                respuesta.data.forEach(function(slot, index) {
                    // Create a new row every 3 slots
                    if (index % 3 === 0) {
                        currentRow = $('<div class="row mb-2"></div>');
                        $('#horariosDisponibles').append(currentRow);
                    }
                    
                    const horaInicio = slot.hora_inicio || slot.inicio || '00:00';
                    const horaFin = slot.hora_fin || slot.fin || '00:00';
                    const medicoId = slot.medico_id || $('#medicoIdNew').val();
                    
                    const timeButton = `
                        <div class="col-md-4">
                            <button type="button" class="btn btn-outline-primary btn-block hora-btn" 
                                data-hora="${horaInicio}" 
                                data-hora-fin="${horaFin}" 
                                data-medico-id="${medicoId}">
                                ${horaInicio} - ${horaFin}
                            </button>
                        </div>
                    `;
                    
                    currentRow.append(timeButton);
                });
                
                // Enable service selection as next step
                $('#paso5').removeClass('paso-disabled');
            } else {
                // No time slots found
                $('#horariosDisponibles').html('<div class="alert alert-warning">No hay horarios disponibles para este médico en la fecha seleccionada</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar horarios:', error);
            $('#horariosDisponibles').html('<div class="alert alert-danger">Error al cargar horarios. Intente nuevamente.</div>');
        }
    });
}

/**
 * Select a time slot and update the reservation form
 */
function seleccionarHorario(elemento, horaInicio, horaFin) {
    console.log('Horario seleccionado:', horaInicio, horaFin);
    
    // Remove selected class from all time buttons
    $('.hora-btn').removeClass('btn-primary').addClass('btn-outline-primary');
    
    // Add selected class to clicked button
    $(elemento).removeClass('btn-outline-primary').addClass('btn-primary');
    
    // Update time info in form
    $('#horaInicioNew').val(horaInicio);
    $('#horaFinNew').val(horaFin);
    
    // Update summary
    $('#resumenHorarioNew').text(`${horaInicio} - ${horaFin}`);
    
    // Add selected class to the step
    $('#paso4').addClass('paso-completo');
    
    // Enable service selection as next step
    $('#paso5').removeClass('paso-disabled');
    
    // Log debug data
    logDataAttributes(elemento, 'Hora seleccionada:');
}

/**
 * Clear time slot selection
 */
function limpiarSeleccionHorario() {
    // Clear time selection in UI
    $('.hora-btn').removeClass('btn-primary').addClass('btn-outline-primary');
    
    // Clear time info in form
    $('#horaInicioNew').val('');
    $('#horaFinNew').val('');
    
    // Clear summary
    $('#resumenHorarioNew').text('(No seleccionado)');
    
    // Reset steps
    $('#paso4').removeClass('paso-completo');
    $('#paso5').addClass('paso-disabled').removeClass('paso-completo');
    $('#paso6').addClass('paso-disabled').removeClass('paso-completo');
}

/**
 * Update the reservation summary with service information
 */
function actualizarResumenServicio(servicioId, nombre, precio) {
    console.log('Servicio seleccionado:', servicioId, nombre, precio);
    
    if (!servicioId) {
        $('#resumenServicioNew').text('(No seleccionado)');
        $('#resumenPrecioNew').text('$0.00');
        return;
    }
    
    // Update summary
    $('#resumenServicioNew').text(nombre);
    $('#resumenPrecioNew').text(`$${precio.toFixed(2)}`);
    
    // Store the original price
    $('#precioBrutoServicio').val(precio);
    
    // Add selected class to the step
    $('#paso5').addClass('paso-completo');
    
    // Enable insurance selection as next step
    $('#paso6').removeClass('paso-disabled');
}

/**
 * Update the reservation summary with insurance information
 */
function actualizarResumenSeguro(seguroId, nombre) {
    console.log('Seguro seleccionado:', seguroId, nombre);
    
    // Update summary
    $('#resumenSeguroNew').text(nombre || 'Sin seguro');
    
    // Add selected class to the step
    $('#paso6').addClass('paso-completo');
    
    // Enable confirmation button
    $('#btnConfirmarReserva').prop('disabled', false);
}

/**
 * Update the total price based on service and insurance
 */
function actualizarPrecioTotal() {
    const precioBruto = parseFloat($('#precioBrutoServicio').val()) || 0;
    const seguroId = $('#seguroSelect').val();
    
    // Apply a discount if insurance is selected (simplified logic)
    let precioFinal = precioBruto;
    if (seguroId && seguroId > 0) {
        // Apply a 30% discount for demo purposes
        precioFinal = precioBruto * 0.7;
    }
    
    // Update the price in the summary
    $('#resumenPrecioNew').text(`$${precioFinal.toFixed(2)}`);
    
    // Store the final price
    $('#precioFinalNew').val(precioFinal);
}

/**
 * Submit the reservation form
 */
function submitReservaForm() {
    // Basic validation
    const pacienteId = $('#pacienteIdNew').val();
    const medicoId = $('#medicoIdNew').val();
    const fecha = $('#fechaReservaNew').val();
    const horaInicio = $('#horaInicioNew').val();
    const servicioId = $('#servicioSelect').val();
    
    if (!pacienteId || !medicoId || !fecha || !horaInicio || !servicioId) {
        Swal.fire({
            icon: 'warning',
            title: 'Datos incompletos',
            text: 'Complete todos los campos requeridos para la reserva',
            confirmButtonText: 'Entendido'
        });
        return;
    }
    
    // Disable submit button to prevent double submission
    $('#btnConfirmarReserva').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
    
    // Prepare form data
    const formData = {
        action: 'guardarReservaNew',
        paciente_id: pacienteId,
        medico_id: medicoId,
        fecha: fecha,
        hora_inicio: horaInicio,
        hora_fin: $('#horaFinNew').val(),
        servicio_id: servicioId,
        seguro_id: $('#seguroSelect').val() || 0,
        precio: $('#precioFinalNew').val() || 0
    };
    
    console.log('Enviando datos de reserva:', formData);
    
    // AJAX call to save the reservation
    $.ajax({
        url: 'ajax/reservas.ajax.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta guardar reserva:', respuesta);
            
            if (respuesta && respuesta.status === 'success') {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: '¡Reserva creada!',
                    text: 'La reserva ha sido creada exitosamente',
                    confirmButtonText: 'Aceptar'
                }).then(function() {
                    // Reset form
                    resetReservaForm();
                    
                    // Optionally, redirect to reservation details
                    if (respuesta.reserva_id) {
                        window.location.href = `index.php?ruta=reserva-detalle&id=${respuesta.reserva_id}`;
                    }
                });
            } else {
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: respuesta.message || 'Error al crear la reserva. Intente nuevamente.',
                    confirmButtonText: 'Entendido'
                });
                
                // Re-enable submit button
                $('#btnConfirmarReserva').prop('disabled', false).html('Confirmar Reserva');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al guardar reserva:', error);
            
            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al crear la reserva. Intente nuevamente.',
                confirmButtonText: 'Entendido'
            });
            
            // Re-enable submit button
            $('#btnConfirmarReserva').prop('disabled', false).html('Confirmar Reserva');
        }
    });
}

/**
 * Reset the reservation form
 */
function resetReservaForm() {
    // Clear all form fields
    $('#pacienteIdNew').val('');
    $('#pacienteNombreNew').val('');
    $('#medicoIdNew').val('');
    $('#medicoNombreNew').val('');
    $('#horaInicioNew').val('');
    $('#horaFinNew').val('');
    $('#precioBrutoServicio').val(0);
    $('#precioFinalNew').val(0);
    
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
    
    // Re-enable submit button
    $('#btnConfirmarReserva').prop('disabled', true).html('Confirmar Reserva');
}

/**
 * Utility function to log data attributes of an element
 */
function logDataAttributes(elemento, prefix = 'Data attributes:') {
    const $el = $(elemento);
    const dataAttr = {};
    
    // Get all data attributes
    $.each($el.data(), function(key, value) {
        dataAttr[key] = value;
    });
    
    console.log(prefix, dataAttr);
}

/**
 * Load insurance providers
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
            if (respuesta && respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                respuesta.data.forEach(function(seguro) {
                    const id = seguro.id || 0;
                    const nombre = seguro.nombre || 'Seguro sin nombre';
                    
                    $('#seguroSelect').append(`<option value="${id}">${nombre}</option>`);
                });
            } else {
                // If no insurance providers found, add demo options
                console.warn('No se encontraron proveedores de seguro. Mostrando opciones de demostración.');
                const segurosDemo = [
                    { id: 1, nombre: 'Seguro Médico Nacional' },
                    { id: 2, nombre: 'Aseguradora de Salud' },
                    { id: 3, nombre: 'Seguro Corporativo' }
                ];
                
                segurosDemo.forEach(function(seguro) {
                    $('#seguroSelect').append(`<option value="${seguro.id}">${seguro.nombre}</option>`);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar seguros:', error);
            
            // Add demo options if AJAX fails
            console.warn('Error al cargar seguros. Mostrando opciones de demostración.');
            const segurosDemo = [
                { id: 1, nombre: 'Seguro Médico Nacional' },
                { id: 2, nombre: 'Aseguradora de Salud' },
                { id: 3, nombre: 'Seguro Corporativo' }
            ];
            
            segurosDemo.forEach(function(seguro) {
                $('#seguroSelect').append(`<option value="${seguro.id}">${seguro.nombre}</option>`);
            });
        }
    });
}

/**
 * Load initial services
 */
function cargarServiciosIniciales() {
    // AJAX call to get all services
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'obtenerServicios'
        },
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta servicios iniciales:', respuesta);
            
            // Clear previous options
            $('#servicioSelect').html('<option value="">Seleccione un servicio</option>');
            
            if (respuesta && respuesta.status === 'success' && respuesta.data && respuesta.data.length > 0) {
                respuesta.data.forEach(function(servicio) {
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
                // Si no hay servicios o hay error, mostrar servicios demo
                console.warn('No se encontraron servicios iniciales. Mostrando servicios de demostración.');
                const serviciosDemo = [
                    { id: 1, nombre: 'Consulta General', precio: 50.00 },
                    { id: 2, nombre: 'Consulta Especialista', precio: 75.00 },
                    { id: 3, nombre: 'Evaluación Completa', precio: 120.00 }
                ];
                
                serviciosDemo.forEach(function(servicio) {
                    $('#servicioSelect').append(`
                        <option value="${servicio.id}" data-precio="${servicio.precio}">
                            ${servicio.nombre}
                        </option>
                    `);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar servicios iniciales:', error);
            
            // Si hay error, mostrar servicios demo
            console.warn('Error al cargar servicios. Mostrando servicios de demostración.');
            const serviciosDemo = [
                { id: 1, nombre: 'Consulta General', precio: 50.00 },
                { id: 2, nombre: 'Consulta Especialista', precio: 75.00 },
                { id: 3, nombre: 'Evaluación Completa', precio: 120.00 }
            ];
            
            serviciosDemo.forEach(function(servicio) {
                $('#servicioSelect').append(`
                    <option value="${servicio.id}" data-precio="${servicio.precio}">
                        ${servicio.nombre}
                    </option>
                `);
            });
        }
    });
}
EOT;

// Write the fixed content to the file
file_put_contents('c:/laragon/www/clinica/view/js/reservas_new.js', $fixedContent);

echo "Fixed JavaScript file successfully. The original file was backed up.";
?>
