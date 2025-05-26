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
    inicializarEventos();
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
 * Inicializa los event handlers para los botones y controles
 */
function inicializarEventos() {
    // Limpiar event handlers existentes para evitar duplicación
    $(document).off('click', '#btnBuscarDisponibilidad');
    $(document).off('click', '#btnContinuarDoctor');
    $(document).off('click', '#btnContinuarServicio');
    $(document).off('click', '.btn-volver-paso');
    $(document).off('click', '#btnBuscarPaciente');
    $(document).off('keypress', '#buscarPaciente');

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
        
        // Mostrar la fecha seleccionada en el formato adecuado
        $('#fechaSeleccionadaTexto').text(formatearFechaParaMostrar(fechaSeleccionada));
        $('#fechaSeleccionadaDoctorTexto').text(formatearFechaParaMostrar(fechaSeleccionada));
        
        // Cargar las reservas existentes para esta fecha
        cargarReservasDelDia(fechaSeleccionada);
        
        // Cargar médicos disponibles para esta fecha
        cargarMedicosDisponiblesPorFecha(fechaSeleccionada);
        
        // Avanzar al paso 2 (selección de médico)
        mostrarPasoReserva(2);
    });

    // Evento para seleccionar médico en paso 2 y continuar
    $(document).on('click', '#btnContinuarDoctor', function() {
        proveedorSeleccionado = $('#selectProveedor').val();
        
        if (!proveedorSeleccionado) {
            mostrarAlerta('warning', 'Por favor, seleccione un doctor para continuar.');
            return;
        }
        
        // Actualizar texto del médico seleccionado
        const doctorTexto = $('#selectProveedor option:selected').text();
        $('#doctorSeleccionadoTexto').text(doctorTexto);
        
        // Cargar servicios disponibles para este médico y fecha
        cargarServiciosPorFechaMedico(fechaSeleccionada, proveedorSeleccionado);
        
        // Avanzar al paso 3 (selección de servicio)
        mostrarPasoReserva(3);
    });

    // Evento para seleccionar servicio en paso 3 y continuar
    $(document).on('click', '#btnContinuarServicio', function() {
        servicioSeleccionado = $('#selectServicio').val();
        
        if (!servicioSeleccionado) {
            mostrarAlerta('warning', 'Por favor, seleccione un servicio para continuar.');
            return;
        }
        
        // Cargar horarios disponibles para esta combinación
        cargarHorariosDisponibles(servicioSeleccionado, proveedorSeleccionado, fechaSeleccionada);
        
        // Avanzar al paso 4 (selección de horario)
        mostrarPasoReserva(4);
    });

    // Botones para volver al paso anterior
    $(document).on('click', '.btn-volver-paso', function() {
        const pasoAnterior = $(this).data('paso-anterior');
        mostrarPasoReserva(pasoAnterior);
    });

    // Evento para buscar pacientes (Paso 5)
    $(document).on('click', '#btnBuscarPaciente', function() {
        buscarPacientes();
    });
    
    // También buscar cuando se presiona Enter en el input
    $(document).on('keypress', '#buscarPaciente', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarPacientes();
        }
    });
      // Evento para seleccionar un paciente
    $(document).on('click', '.btn-seleccionar-paciente', function() {
        let pacienteId = $(this).data('id');
        let pacienteNombre = $(this).data('nombre');
        
        // Guardar paciente seleccionado
        $('#pacienteSeleccionado').val(pacienteId);
        
        // Añadir información del paciente a la sección de resultados
        $('#resultadosPacientes').html(`<div class="alert alert-success">
            <i class="fas fa-user-check"></i> Paciente seleccionado: <strong>${pacienteNombre}</strong>
            <p>ID: ${pacienteId}</p>
        </div>`);
        
        // Habilitar botón de guardar reserva
        $('#formReserva button[type="submit"]').prop('disabled', false);
    });
    
    // Evento para confirmar horario y avanzar a la selección de paciente (Paso 5)
    $(document).on('click', '#btnConfirmarReserva', function() {
        if (!horarioSeleccionado) {
            mostrarAlerta('warning', 'Por favor, seleccione un horario para continuar.');
            return;
        }
        
        // Mostrar la información del horario seleccionado
        const horaInicio = horarioSeleccionado.inicio.substring(0, 5);
        const horaFin = horarioSeleccionado.fin.substring(0, 5);
        
        $('#resumenReserva').html(`
            <div class="alert alert-info">
                <h5>Resumen de la reserva:</h5>
                <p><strong>Fecha:</strong> ${formatearFechaParaMostrar(fechaSeleccionada)}</p>
                <p><strong>Médico:</strong> ${$('#selectProveedor option:selected').text()}</p>
                <p><strong>Servicio:</strong> ${$('#selectServicio option:selected').text()}</p>
                <p><strong>Horario:</strong> ${horaInicio} - ${horaFin}</p>
            </div>
        `);
        
        // Avanzar al paso 5 (selección de paciente)
        mostrarPasoReserva(5);
    });
    
    // Evento para enviar el formulario de reserva
    $(document).on('submit', '#formReserva', function(e) {
        e.preventDefault();
        
        const pacienteId = $('#pacienteSeleccionado').val();
        
        if (!pacienteId) {
            mostrarAlerta('warning', 'Por favor, seleccione un paciente para continuar.');
            return;
        }
        
        // Obtener todos los datos para la reserva
        const datos = {
            action: "guardarReserva",
            doctor_id: proveedorSeleccionado,
            servicio_id: servicioSeleccionado, 
            paciente_id: pacienteId,
            fecha: fechaSeleccionada,
            hora_inicio: horarioSeleccionado.inicio,
            hora_fin: horarioSeleccionado.fin,
            observaciones: $('#observaciones').val()
        };
        
        // Mostrar spinner de carga
        Swal.fire({
            title: 'Guardando reserva...',
            text: 'Por favor espere un momento',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Enviar datos al servidor
        $.ajax({
            url: "ajax/servicios.ajax.php",
            method: "POST",
            data: datos,
            dataType: "json",
            success: function(respuesta) {
                Swal.close();
                
                if (respuesta.status === "success") {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Reserva guardada!',
                        text: 'La reserva ha sido guardada exitosamente',
                        confirmButtonText: 'Aceptar'
                    }).then((result) => {
                        // Redirigir a la página de reservas o recargar
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: respuesta.message || 'No se pudo guardar la reserva',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                console.error("Error al guardar reserva:", xhr.responseText);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al intentar guardar la reserva',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });
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
            if (respuesta.status === "success") {
                let options = '<option value="">Seleccione un servicio</option>';
                
                if (respuesta.data.length > 0) {
                    respuesta.data.forEach(servicio => {
                        options += `<option value="${servicio.servicio_id}">${servicio.servicio_nombre} (${servicio.duracion_minutos} min - $${parseFloat(servicio.precio_base).toFixed(2)})</option>`;
                    });
                } else {
                    options = '<option value="">No hay servicios disponibles para este médico</option>';
                }
                
                $('#selectServicio').html(options);
            }
        },
        error: function(xhr) {
            console.error("Error al cargar servicios por fecha y médico:", xhr.responseText);
            mostrarAlerta('error', 'No se pudieron cargar los servicios disponibles.');
            $('#selectServicio').html('<option value="">Error al cargar servicios</option>');
        }
    });
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
            if (respuesta.status === "success") {
                let options = '<option value="">Seleccione un médico</option>';
                  if (respuesta.data.length > 0) {
                    // Check if we have data with message property (error message)
                    if (respuesta.data[0].hasOwnProperty('message')) {
                        options = `<option value="">${respuesta.data[0].message}</option>`;
                    } else {
                        respuesta.data.forEach(medico => {
                            options += `<option value="${medico.doctor_id}">${medico.nombre_doctor}</option>`;
                        });
                    }
                } else {
                    options = '<option value="">No hay médicos disponibles para esta fecha</option>';
                }
                
                $('#selectProveedor').html(options);
            }
        },
        error: function(xhr) {
            console.error("Error al cargar médicos por fecha:", xhr.responseText);
            mostrarAlerta('error', 'No se pudieron cargar los médicos disponibles.');
            $('#selectProveedor').html('<option value="">Error al cargar médicos</option>');
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
            $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center">Cargando reservas...</td></tr>');
        },
        success: function(respuesta) {
            if (respuesta.status === "success") {
                let filas = '';
                
                if (respuesta.data.length > 0) {
                    respuesta.data.forEach(reserva => {
                        filas += `
                            <tr>
                                <td>${reserva.hora_inicio} - ${reserva.hora_fin}</td>
                                <td>${reserva.doctor_nombre}</td>
                                <td>${reserva.paciente_nombre}</td>
                                <td>${reserva.servicio_nombre}</td>
                                <td>${reserva.estado}</td>
                            </tr>
                        `;
                    });
                } else {
                    filas = '<tr><td colspan="5" class="text-center">No hay reservas para esta fecha</td></tr>';
                }
                
                $('#tablaReservasExistentes tbody').html(filas);
            }
        },
        error: function(xhr) {
            console.error("Error al cargar reservas:", xhr.responseText);
            $('#tablaReservasExistentes tbody').html('<tr><td colspan="5" class="text-center">Error al cargar reservas</td></tr>');
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
    
    // Mostrar parámetros en la interfaz para depuración
    $('#contenedorHorarios').html(`
        <div class="alert alert-info">
            <p><strong>Parámetros de búsqueda:</strong></p>
            <p>ServicioID: ${servicioId}</p>
            <p>DoctorID: ${doctorId}</p>
            <p>Fecha: ${fecha}</p>
        </div>
        <p class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando horarios disponibles...</p>
    `);
    
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
            // Ya estamos mostrando el spinner
        },
        success: function(respuesta) {
            console.log("Respuesta de slots:", respuesta);
            
            // La respuesta puede tener status='success' o tener directamente la data (para mantener compatibilidad)
            let contenidoHorarios = '';
            
            if ((respuesta.status === "success" && respuesta.data && respuesta.data.length > 0) || 
                (respuesta.data && respuesta.data.length > 0)) {
                // Si hay slots disponibles
                contenidoHorarios = `
                    <div class="alert alert-success">
                        Se encontraron ${respuesta.data.length} horarios disponibles.
                    </div>
                    <div class="row">
                `;
                
                respuesta.data.forEach(slot => {
                    // Formatear la hora para mostrar solo HH:MM
                    const horaInicio = slot.hora_inicio.substring(0, 5);
                    const horaFin = slot.hora_fin.substring(0, 5);
                    
                    contenidoHorarios += `
                        <div class="col-md-3 col-6 mb-3">
                            <button class="btn btn-outline-primary btn-block btn-horario" 
                                    data-hora-inicio="${slot.hora_inicio}" 
                                    data-hora-fin="${slot.hora_fin}">
                                ${horaInicio} - ${horaFin}
                            </button>
                        </div>
                    `;
                });
                
                contenidoHorarios += '</div>';
                
                // Actualizar el contenido HTML
                $('#contenedorHorarios').html(contenidoHorarios);
                
                // Agregar información detallada de depuración
                $('#debugInfoContainer').remove(); // Eliminar información de debug previa
                
                const debugInfo = `
                    <div id="debugInfoContainer" class="mt-4 p-3 border bg-light">
                        <h5>Información de depuración:</h5>
                        <p><strong>Servicio ID:</strong> ${servicioId}</p>
                        <p><strong>Doctor ID:</strong> ${doctorId}</p>
                        <p><strong>Fecha:</strong> ${fecha}</p>
                        <p><strong>Slots encontrados:</strong> ${respuesta.data ? respuesta.data.length : 0}</p>
                        <p><button class="btn btn-sm btn-info" id="btnTestDirecto">Probar directamente</button></p>
                    </div>
                `;
                
                $('#contenedorHorarios').append(debugInfo);
                
                // Evento para probar la generación de slots directamente
                $('#btnTestDirecto').on('click', function() {
                    window.open(`test_slots_simplificados.php?doctor_id=${doctorId}&fecha=${fecha}`, '_blank');
                });
                
                // Agregar evento para selección de horario
                $('.btn-horario').click(function() {
                    $('.btn-horario').removeClass('active');
                    $(this).addClass('active');
                    
                    horarioSeleccionado = {
                        inicio: $(this).data('hora-inicio'),
                        fin: $(this).data('hora-fin')
                    };
                    
                    // Habilitar botón para confirmar reserva
                    $('#btnConfirmarReserva').prop('disabled', false);
                });
            } else {
                // Si no hay datos o hay un error, mostrar un mensaje
                let mensaje = '<div class="alert alert-warning">No hay horarios disponibles para esta combinación de médico, servicio y fecha.</div>';
                if (respuesta.status === "error") {
                    mensaje = `<div class="alert alert-danger">Error: ${respuesta.message || 'No se pudieron cargar los horarios'}</div>`;
                }
                $('#contenedorHorarios').html(mensaje);
            }
        },
        error: function(xhr) {
            console.error("Error al cargar horarios:", xhr.responseText);
            $('#contenedorHorarios').html('<div class="alert alert-danger">Error al cargar los horarios disponibles.</div>');
        }
    });
}

/**
 * Formatea una fecha YYYY-MM-DD para mostrarla en formato más amigable
 * @param {string} fecha Fecha en formato YYYY-MM-DD
 * @returns {string} Fecha formateada como "Día de Mes de Año"
 */
function formatearFechaParaMostrar(fecha) {
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(fecha).toLocaleDateString('es-ES', opciones);
}

/**
 * Muestra una alerta usando Toastr o SweetAlert2
 * @param {string} tipo Tipo de alerta: 'success', 'info', 'warning', 'error'
 * @param {string} mensaje Mensaje a mostrar
 */
function mostrarAlerta(tipo, mensaje) {
    // Si existe toastr, usarlo
    if (typeof toastr !== 'undefined') {
        toastr[tipo](mensaje);
    } else if (typeof Swal !== 'undefined') {
        // Si no, usar SweetAlert2 si está disponible
        Swal.fire({
            icon: tipo,
            title: tipo === 'error' ? 'Error' : 'Atención',
            text: mensaje,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        // Como última opción, usar alert
        alert(mensaje);
    }
}

/**
 * Busca un paciente por su DNI y muestra sus datos en el formulario
 * @param {string} dni DNI del paciente a buscar
 */
function buscarPacientePorDNI(dni) {
    $.ajax({
        url: "ajax/pacientes.ajax.php",
        method: "POST",
        data: { 
            action: "buscarPacientePorDNI",
            dni: dni
        },
        dataType: "json",
        beforeSend: function() {
            // Limpiar datos anteriores
            $('#nombrePaciente').val('');
            $('#telefonoPaciente').val('');
            $('#emailPaciente').val('');
            $('#direccionPaciente').val('');
            
            $('#resultadoBusquedaPaciente').html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando paciente...</p>');
        },
        success: function(respuesta) {
            if (respuesta.status === "success") {
                // Llenar campos con datos del paciente
                $('#nombrePaciente').val(respuesta.data.nombre);
                $('#telefonoPaciente').val(respuesta.data.telefono);
                $('#emailPaciente').val(respuesta.data.email);
                $('#direccionPaciente').val(respuesta.data.direccion);
                
                $('#resultadoBusquedaPaciente').html('<p class="text-success">Paciente encontrado.</p>');
            } else {
                $('#resultadoBusquedaPaciente').html(`<p class="text-danger">${respuesta.message}</p>`);
            }
        },
        error: function(xhr) {
            console.error("Error al buscar paciente:", xhr.responseText);
            $('#resultadoBusquedaPaciente').html('<p class="text-danger">Error al buscar paciente.</p>');
        }
    });
}

/**
 * Busca pacientes según los criterios ingresados en el formulario
 */
function buscarPacientes() {
    const termino = $('#buscarPaciente').val().trim();
    
    if (termino.length < 3) {
        mostrarAlerta('warning', 'Por favor, ingrese al menos 3 caracteres para buscar (nombre o documento).');
        return;
    }
    
    $.ajax({
        url: "ajax/servicios.ajax.php",
        method: "POST",
        data: { 
            action: "buscarPaciente",
            termino: termino
        },
        dataType: "json",
        beforeSend: function() {
            $('#resultadosPacientes').html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Buscando pacientes...</p>');
        },
        success: function(respuesta) {
            // Debug output
            console.log("Respuesta de búsqueda de pacientes:", respuesta);
            
            if (respuesta.status === "success") {
                let html = '';
                
                if (respuesta.data && respuesta.data.length > 0) {
                    html = '<div class="row">';
                    respuesta.data.forEach(paciente => {
                        const nombreCompleto = `${paciente.first_name} ${paciente.last_name}`;
                        html += `
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">${nombreCompleto}</h5>
                                        <p class="card-text">
                                            <strong>Documento:</strong> ${paciente.document_number || 'No registrado'}<br>
                                            <strong>Teléfono:</strong> ${paciente.phone_number || 'No registrado'}<br>
                                            <strong>Email:</strong> ${paciente.email || 'No registrado'}
                                        </p>
                                        <button class="btn btn-primary btn-seleccionar-paciente" 
                                                data-id="${paciente.person_id}" 
                                                data-nombre="${nombreCompleto}">
                                            <i class="fas fa-check"></i> Seleccionar paciente
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>'; // Close the row div
                } else {
                    html = '<div class="alert alert-info">No se encontraron pacientes con ese criterio de búsqueda.</div>';
                }
                
                $('#resultadosPacientes').html(html);
            } else {
                $('#resultadosPacientes').html(`<div class="alert alert-danger">${respuesta.message || 'Error en la búsqueda de pacientes'}</div>`);
            }
        },
        error: function(xhr) {
            console.error("Error al buscar pacientes:", xhr.responseText);
            $('#resultadosPacientes').html('<div class="alert alert-danger">Error al buscar pacientes.</div>');
        }
    });
}
