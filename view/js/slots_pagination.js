/**
 * Funciones específicas para manejo de slots de horario
 */

// Variables para la paginación de slots
let slotsPorPagina = 9;
let paginaActual = 1;
let totalPaginas = 1;
let todosLosSlots = [];

/**
 * Inicializa los slots paginados
 * @param {Array} slots Lista de slots de horario
 */
function inicializarSlotsPaginados(slots) {
    // Guardar todos los slots
    todosLosSlots = slots;
    
    // Calcular total de páginas
    totalPaginas = Math.ceil(slots.length / slotsPorPagina);
    
    // Inicializar en la primera página
    paginaActual = 1;
    
    // Mostrar los slots de la página actual
    mostrarSlotsPaginados();
    
    // Actualizar la navegación de páginas
    actualizarNavegacionPaginas();
}

/**
 * Muestra los slots de la página actual
 */
function mostrarSlotsPaginados() {
    // Calcular índices para la paginación
    const inicio = (paginaActual - 1) * slotsPorPagina;
    const fin = Math.min(inicio + slotsPorPagina, todosLosSlots.length);
    
    // Obtener los slots para esta página
    const slotsPagina = todosLosSlots.slice(inicio, fin);
    
    // Construir HTML
    let html = '<div class="row">';
    
    slotsPagina.forEach(function(slot) {        // Determinar si el slot está disponible
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

/**
 * Actualiza la navegación de páginas de slots
 */
function actualizarNavegacionPaginas() {
    if (totalPaginas <= 1) {
        // Si solo hay una página o menos, ocultar la paginación
        $('#slotsPagination').hide();
        return;
    }
    
    // Construir la estructura de paginación
    let html = `
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="cambiarPaginaSlots(${paginaActual - 1}); return false;">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
    `;
    
    // Agregar enlaces a las páginas
    for (let i = 1; i <= totalPaginas; i++) {
        html += `
            <li class="page-item ${i === paginaActual ? 'active' : ''}">
                <a class="page-link" href="#" onclick="cambiarPaginaSlots(${i}); return false;">${i}</a>
            </li>
        `;
    }
    
    html += `
                <li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="cambiarPaginaSlots(${paginaActual + 1}); return false;">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    `;
    
    // Insertar en el contenedor y mostrar
    $('#slotsPagination').html(html).show();
}

/**
 * Cambia la página actual de slots
 * @param {number} pagina Número de página a mostrar
 */
function cambiarPaginaSlots(pagina) {
    // Validar que la página esté dentro del rango
    if (pagina < 1 || pagina > totalPaginas) return;
    
    // Actualizar la página actual
    paginaActual = pagina;
    
    // Actualizar la visualización
    mostrarSlotsPaginados();
    actualizarNavegacionPaginas();
    
    // Scroll al inicio de los slots
    $('html, body').animate({
        scrollTop: $('#slotsPaginados').offset().top - 100
    }, 200);
}
