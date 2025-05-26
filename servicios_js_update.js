/**
 * Script para actualizar el flujo de trabajo en servicios.js
 * Copie este código y realice las siguientes modificaciones en servicios.js
 */

// 1. Asegúrese de tener los siguientes event handlers en la función inicializarEventos():

$(document).off('click', '#btnContinuarDoctor');
$(document).off('click', '#btnContinuarServicio');

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

// 2. Asegúrese de tener la siguiente función para cargar servicios por fecha y médico

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

// 3. Modifique la función de botón de búsqueda de disponibilidad

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
