/**
 * Script para manejar el envío de confirmaciones de reservas
 * Este archivo contiene funciones para generar y enviar PDFs de reservas a pacientes
 */

/**
 * Genera y envía un PDF de confirmación de reserva al paciente
 * @param {Object} reservaData - Datos de la reserva
 * @returns {Promise} - Promesa que resuelve cuando se completa el envío
 */
function enviarConfirmacionReserva(reservaData) {
    console.log("Generando confirmación para reserva:", reservaData);
    
    if (!reservaData || !reservaData.paciente || !reservaData.telefono) {
        alertify.error("Datos de reserva incompletos");
        return Promise.reject(new Error("Datos incompletos"));
    }
    
    // URL del PDF (esta URL debería ser generada dinámicamente en un entorno real)
    // En este ejemplo, usamos una URL fija para demostración
    const pdfUrl = `https://clinica.test/generar_pdf_reserva.php?id=${reservaData.id}`;
    
    // Texto descriptivo para el PDF
    const descripcion = `Confirmación de su cita médica - ${reservaData.servicio} - ${formatearFecha(reservaData.fecha)} ${reservaData.hora}`;
    
    // Llamar a la función de envío de PDF
    return enviarPDFPaciente(
        reservaData.telefono,
        pdfUrl,
        descripcion
    ).then(response => {
        console.log("PDF de reserva enviado correctamente:", response);
        // Actualizar el estado de la reserva para marcar que se ha enviado la confirmación
        return actualizarEstadoReserva(reservaData.id, 'confirmacion_enviada');
    }).catch(error => {
        console.error("Error al enviar confirmación de reserva:", error);
        throw error;
    });
}

/**
 * Actualiza el estado de una reserva en el servidor
 * @param {number|string} reservaId - ID de la reserva
 * @param {string} nuevoEstado - Nuevo estado de la reserva
 * @returns {Promise} - Promesa que resuelve cuando se completa la actualización
 */
function actualizarEstadoReserva(reservaId, nuevoEstado) {
    return fetch("ajax/reservas.ajax.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `accion=actualizar_estado&id=${reservaId}&estado=${nuevoEstado}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.exito) {
            console.log("Estado de reserva actualizado:", data);
            return data;
        } else {
            throw new Error(data.mensaje || "Error al actualizar estado de la reserva");
        }
    });
}

/**
 * Formatea una fecha en formato legible
 * @param {string} fechaStr - Fecha en formato ISO o similar
 * @returns {string} - Fecha formateada
 */
function formatearFecha(fechaStr) {
    const fecha = new Date(fechaStr);
    if (isNaN(fecha.getTime())) {
        return fechaStr; // Retornar el string original si no es una fecha válida
    }
    
    return fecha.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// Exportar funciones para uso en otros archivos
// En un entorno modular, esto permitiría importar estas funciones
// En este caso, al incluir este script, las funciones estarán disponibles globalmente
window.enviarConfirmacionReserva = enviarConfirmacionReserva;
