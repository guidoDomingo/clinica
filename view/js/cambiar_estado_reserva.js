/**
 * Cambia el estado de una reserva y actualiza la interfaz
 * @param {number} reservaId ID de la reserva
 * @param {string} nuevoEstado Nuevo estado a asignar
 */
function cambiarEstadoReservaTab(reservaId, nuevoEstado) {
    // Verificar que tengamos datos válidos
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
            // Mostrar indicador de carga
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
                // Cerrar el diálogo de carga
                Swal.close();
                
                // Mostrar mensaje de éxito con SweetAlert2
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Estado actualizado correctamente',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000,
                    timerProgressBar: true
                });
                
                // Recargar las tablas de reservas
                const fechaActual = $('#fechaReservaNew').val();
                if (fechaActual) {
                    cargarReservasPorFecha(fechaActual);
                }
                
                // Si existe la función para recargar la tabla de reservas general, también la llamamos
                if (typeof cargarReservas === 'function') {
                    cargarReservas();
                }

                // Mostrar animación de confirmación exitosa en la fila de la tabla
                animarConfirmacionExitosa(reservaId);
                
                // Registrar log
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
            Swal.fire({
                title: 'Error',
                text: 'Error al actualizar: ' + error,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
}
