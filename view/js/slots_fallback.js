/**
 * Fallback para slots en caso de que la paginación no esté disponible
 */

// Verificar si existe la función de paginación de slots
if (typeof inicializarSlotsPaginados !== 'function') {
    console.log('Utilizando visualización simple de slots (sin paginación)');
    
    /**
     * Función fallback para mostrar slots sin paginación
     * @param {Array} slots Lista de slots de horario
     */
    function mostrarSlotsSimples(slots) {
        // Construir HTML para mostrar todos los slots
        let html = '<div class="row">';
        
        slots.forEach(function(slot) {                    // Determinar si el slot está disponible
                    const disponible = slot.disponible !== false; // Por defecto, asumimos disponible
                    const claseDisponibilidad = disponible ? '' : 'no-disponible';
                    
                    // Formatear las horas para mostrar (HH:MM)
                    const horaInicio = slot.hora_inicio ? slot.hora_inicio.substring(0, 5) : '??:??';
                    const horaFin = slot.hora_fin ? slot.hora_fin.substring(0, 5) : '??:??';
                    
                    // Nombre de la sala
                    const nombreSala = slot.sala_nombre || 'Sin sala asignada';
                    
                    html += `
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="slot-horario ${claseDisponibilidad}" 
                                 data-id="${slot.horario_id || slot.id || ''}"
                                 data-inicio="${slot.hora_inicio || ''}"
                                 data-fin="${slot.hora_fin || ''}"
                                 data-texto="${horaInicio} - ${horaFin}"
                                 data-sala="${nombreSala}">
                                <p class="mb-1 text-center"><strong>${horaInicio} - ${horaFin}</strong></p>
                                <p class="mb-0 text-center"><small>${nombreSala}</small></p>
                            </div>
                        </div>
                    `;
        });
        
        html += '</div>';
        
        // Insertar en el contenedor
        $('#slotsPaginados').html(html);
    }
    
    // Sobrescribir la función de cargar horarios para usar visualización simple
    $(document).ready(function() {
        // Verificar si existe la función original
        if (typeof cargarHorariosDisponibles === 'function') {
            const cargarHorariosOriginal = cargarHorariosDisponibles;
            
            cargarHorariosDisponibles = function(servicioId, doctorId, fecha) {
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
                        $('#contenedorHorarios').html(`
                            <div class="text-center">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p>Cargando horarios disponibles...</p>
                            </div>
                        `);
                    },
                    success: function(respuesta) {
                        console.log("Respuesta de horarios:", respuesta);
                        if (respuesta.data && respuesta.data.length > 0) {
                            // Mostrar slots sin paginación
                            mostrarSlotsSimples(respuesta.data);
                            
                            // Mostrar mensaje de ayuda
                            $('#contenedorHorarios').html(`
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    Se han encontrado ${respuesta.data.length} horarios disponibles.
                                    Haga clic en un horario para seleccionarlo.
                                </div>
                            `);
                        } else {
                            $('#contenedorHorarios').html(`
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    No hay horarios disponibles para la combinación seleccionada.
                                </div>
                            `);
                            
                            // Limpiar la visualización
                            $('#slotsPaginados').html('');
                        }
                    },
                    error: function(xhr) {
                        console.error("Error al cargar horarios:", xhr);
                        $('#contenedorHorarios').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> 
                                Error al cargar horarios. Por favor, intente nuevamente.
                            </div>
                        `);
                        
                        // Limpiar la visualización
                        $('#slotsPaginados').html('');
                    }
                });
            };
        }
    });
}
