/**
 * Script para inicialización de slots en la interfaz compacta
 */

$(document).ready(function() {
    // Si existe la función para paginar slots, conectarla con la función de carga
    if (typeof inicializarSlotsPaginados === 'function') {
        // Sobrescribir la función de cargar horarios para usar paginación
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
                },                success: function(respuesta) {
                    console.log("Respuesta de slots (paginados):", respuesta); // Log para depuración
                    
                    if (respuesta.data && respuesta.data.length > 0) {
                        // Inicializar con paginación
                        inicializarSlotsPaginados(respuesta.data);
                        
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
                        
                        // Limpiar la paginación
                        $('#slotsPaginados').html('');
                        $('#slotsPagination').hide();
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
                    
                    // Limpiar la paginación
                    $('#slotsPaginados').html('');
                    $('#slotsPagination').hide();
                }
            });
        };
    }
});
