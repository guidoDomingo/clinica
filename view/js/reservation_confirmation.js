/**
 * Confirmation workflow for reservations - standalone script
 * This script handles reservation confirmation functionality
 */

$(document).ready(function() {
    console.log('Reservation confirmation script loaded');
    
    // Initialize confirmation buttons when document is ready
    initializeConfirmationButtons();
});

/**
 * Initialize confirmation button event handlers
 */
function initializeConfirmationButtons() {
    // Remove any existing event handlers to prevent duplicates
    $(document).off('click', '.btnConfirmarReservaTab');
    $(document).off('click', '.btnConfirmarReserva');
    
    // Event handler for confirmation buttons in the "Nueva reserva" tab
    $(document).on('click', '.btnConfirmarReservaTab', function() {
        const reservaId = $(this).data('id');
        console.log(`Confirmando reserva ${reservaId} desde nueva reserva tab`);
        
        if (!reservaId) {
            console.error('ID de reserva no encontrado');
            return;
        }
        
        // Show confirmation dialog
        Swal.fire({
            title: '¿Confirmar esta reserva?',
            text: "La reserva se marcará como CONFIRMADA",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                changeReservationStatus(reservaId, 'CONFIRMADA');
            }
        });
    });
    
    // Event handler for confirmation buttons in the "Reservas" tab
    $(document).on('click', '.btnConfirmarReserva', function() {
        const reservaId = $(this).data('id');
        console.log(`Confirmando reserva ${reservaId} desde reservas tab`);
        
        if (!reservaId) {
            console.error('ID de reserva no encontrado');
            return;
        }
        
        // Show confirmation dialog
        Swal.fire({
            title: '¿Confirmar esta reserva?',
            text: "La reserva se marcará como CONFIRMADA",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                changeReservationStatus(reservaId, 'CONFIRMADA');
            }
        });
    });
}

/**
 * Change reservation status via AJAX
 * @param {number} reservaId - Reservation ID
 * @param {string} nuevoEstado - New status
 */
function changeReservationStatus(reservaId, nuevoEstado) {
    if (!reservaId) {
        console.error("ID de reserva no proporcionado");
        Swal.fire({
            title: 'Error',
            text: 'No se pudo identificar la reserva',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

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
            // Show loading indicator
            Swal.fire({
                title: 'Procesando...',
                text: 'Actualizando estado de la reserva',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(respuesta) {
            console.log("Respuesta de cambio de estado:", respuesta);
            
            if (respuesta.status === "success") {
                // Show success message
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Estado actualizado correctamente',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000,
                    timerProgressBar: true
                });
                
                // Reload reservation tables
                reloadReservationTables();
                
                // Show confirmation animation
                animateConfirmationSuccess(reservaId);
                
                console.log(`Reserva ${reservaId} actualizada a estado: ${nuevoEstado}`);
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Error al cambiar estado: ' + (respuesta.mensaje || 'Error desconocido'),
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cambiar estado de reserva:", error);
            
            let errorMessage = "Error al actualizar: " + error;
            try {
                const responseJson = JSON.parse(xhr.responseText);
                if (responseJson && responseJson.mensaje) {
                    errorMessage = responseJson.mensaje;
                }
            } catch (e) {
                console.error("Respuesta de error (no es JSON):", xhr.responseText);
            }
            
            Swal.fire({
                title: 'Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
}

/**
 * Reload reservation tables after status change
 */
function reloadReservationTables() {
    // Reload reservations for current date in "Nueva reserva" tab
    const fechaActual = $('#fechaReservaNew').val();
    if (fechaActual && typeof cargarReservasPorFecha === 'function') {
        cargarReservasPorFecha(fechaActual);
    }
    
    // Reload main reservations table if function exists
    if (typeof cargarReservas === 'function') {
        cargarReservas();
    }
    
    // Reload reservations with current filters if function exists
    if (typeof buscarReservas === 'function') {
        buscarReservas();
    }
}

/**
 * Animate confirmation success in table rows
 * @param {number} reservaId - Reservation ID
 */
function animateConfirmationSuccess(reservaId) {
    // Find row in "Nueva reserva" table
    const $filaNuevaReserva = $(`#tablaReservasPorFecha tr[data-reserva-id="${reservaId}"]`);
    if ($filaNuevaReserva.length) {
        updateReservationRowStatus($filaNuevaReserva, 'CONFIRMADA');
    }
    
    // Find row in main reservations table
    const $filaReservas = $(`#tablaReservas tr[data-reserva-id="${reservaId}"]`);
    if ($filaReservas.length) {
        updateReservationRowStatus($filaReservas, 'CONFIRMADA');
    }
}

/**
 * Update a reservation row's visual status
 * @param {jQuery} $fila - Row element
 * @param {string} estado - New status
 */
function updateReservationRowStatus($fila, estado) {
    // Add animation class
    $fila.addClass('recien-confirmada');
    
    // Update row classes
    $fila.removeClass('estado-pendiente').addClass('estado-confirmada');
    
    // Update status cell
    const $celdaEstado = $fila.find('td:nth-child(5), td.estado-reserva');
    $celdaEstado.html('<span class="badge badge-success estado-confirmada"><i class="fas fa-check-circle mr-1"></i>CONFIRMADA</span>');
    
    // Update action cell
    const $celdaAccion = $fila.find('td:nth-child(6), td.acciones-reserva');
    $celdaAccion.html('<i class="fas fa-check-double text-success" title="Reserva confirmada"></i>');
    
    // Remove animation class after delay
    setTimeout(() => {
        $fila.removeClass('recien-confirmada');
    }, 1500);
}
