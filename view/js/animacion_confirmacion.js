/**
 * Mostrar animación de confirmación exitosa en la fila de la tabla
 * @param {number} reservaId ID de la reserva confirmada
 */
function animarConfirmacionExitosa(reservaId) {
    // Buscar la fila de la reserva en la tabla de reservas por fecha
    const $fila = $(`#tablaReservasPorFecha tr[data-reserva-id="${reservaId}"]`);
    
    if ($fila.length) {
        // Añadir clase para animar
        $fila.addClass('recien-confirmada');
        
        // Actualizar el estilo visual de la fila a "confirmada"
        $fila.removeClass('estado-pendiente').addClass('estado-confirmada');
        
        // Actualizar el texto y estilo de la celda de estado
        const $celdaEstado = $fila.find('td:nth-child(5)');
        $celdaEstado.html('<span class="badge badge-success estado-confirmada"><i class="fas fa-check-circle mr-1"></i>CONFIRMADA</span>');
        
        // Reemplazar el botón de confirmar con un ícono de verificación
        const $celdaAccion = $fila.find('td:nth-child(6)');
        $celdaAccion.html('<i class="fas fa-check-double text-success" title="Reserva confirmada"></i>');
        
        // Remover la animación después de completada para permitir re-animación si es necesario
        setTimeout(() => {
            $fila.removeClass('recien-confirmada');
        }, 1500);
    } else {
        console.log(`No se encontró fila para la reserva ID ${reservaId} en tablaReservasPorFecha`);
    }
    
    // También buscar la fila en la tabla principal de reservas (por si está visible)
    const $filaMain = $(`#tablaReservas tr[data-reserva-id="${reservaId}"]`);
    if ($filaMain.length) {
        // Aplicar los mismos cambios a la tabla principal
        $filaMain.addClass('recien-confirmada');
        $filaMain.removeClass('estado-pendiente').addClass('estado-confirmada');
        
        // Actualizar la celda de estado (puede tener diferente estructura)
        const $celdaEstadoMain = $filaMain.find('td.estado-reserva');
        if ($celdaEstadoMain.length) {
            $celdaEstadoMain.html('<span class="badge badge-success estado-confirmada"><i class="fas fa-check-circle mr-1"></i>CONFIRMADA</span>');
        }
        
        // Actualizar la celda de acciones
        const $celdaAccionMain = $filaMain.find('td.acciones-reserva');
        if ($celdaAccionMain.length) {
            $celdaAccionMain.html('<i class="fas fa-check-double text-success" title="Reserva confirmada"></i>');
        }
        
        // Remover la animación después de completada
        setTimeout(() => {
            $filaMain.removeClass('recien-confirmada');
        }, 1500);
    }
}
