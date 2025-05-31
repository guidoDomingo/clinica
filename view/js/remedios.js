/**
 * Script para manejar la búsqueda de remedios
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar la funcionalidad de búsqueda de remedios
    inicializarBuscadorRemedios();
});

/**
 * Inicializa el buscador de remedios
 */
function inicializarBuscadorRemedios() {
    const btnBuscarRemedio = document.getElementById('btnBuscarRemedio');
    const inputBuscarRemedio = document.getElementById('txtBuscarRemedio');
    
    if (btnBuscarRemedio && inputBuscarRemedio) {
        // Manejar clic en el botón de búsqueda
        btnBuscarRemedio.addEventListener('click', function() {
            const terminoBusqueda = inputBuscarRemedio.value.trim();
            if (terminoBusqueda) {
                buscarRemedios(terminoBusqueda);
            } else {
                mostrarMensaje('warning', 'Por favor ingrese un término de búsqueda');
            }
        });
        
        // Manejar tecla Enter en el campo de búsqueda
        inputBuscarRemedio.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnBuscarRemedio.click();
            }
        });
    }
}

/**
 * Realiza la búsqueda de remedios en la API
 * @param {string} termino - Término de búsqueda
 */
function buscarRemedios(termino) {
    // Mostrar indicador de carga
    const resultadosContainer = document.getElementById('resultadosBusquedaRemedios');
    resultadosContainer.innerHTML = `
        <div class="text-center p-3">
            <i class="fas fa-spinner fa-pulse fa-2x"></i>
            <p class="mt-2">Buscando medicamentos...</p>
        </div>
    `;
    
    // Realizar la petición AJAX
    $.ajax({
        url: 'ajax/remedios.ajax.php',
        method: 'GET',
        data: { query: termino },
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta de API de remedios:', respuesta);
            
            if (respuesta.status === 'success' && respuesta.data) {
                mostrarResultadosRemedios(respuesta.data);
            } else {
                resultadosContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        No se encontraron resultados para "${termino}"
                    </div>
                `;
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en búsqueda de remedios:', error);
            resultadosContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    Error al buscar medicamentos: ${error}
                </div>
            `;
        }
    });
}

/**
 * Muestra los resultados de la búsqueda de remedios
 * @param {Array|Object} datos - Datos de los medicamentos encontrados
 */
function mostrarResultadosRemedios(datos) {
    const resultadosContainer = document.getElementById('resultadosBusquedaRemedios');
    
    // Si no hay datos o es un objeto vacío
    if (!datos || (Array.isArray(datos) && datos.length === 0) || 
        (typeof datos === 'object' && Object.keys(datos).length === 0)) {
        resultadosContainer.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                No se encontraron medicamentos que coincidan con su búsqueda
            </div>
        `;
        return;
    }
    
    // Convertir a array si es un objeto
    const items = Array.isArray(datos) ? datos : [datos];
    
    // Crear la estructura HTML para los resultados
    let html = `
        <div class="list-group">
    `;
    
    // Iterar sobre los resultados
    items.forEach(item => {
        // Verificar qué campos tiene el objeto
        const nombre = item.nombre || item.name || 'Nombre no disponible';
        const descripcion = item.similar || item.similar || '';
        const presentacion = item.fabricante || item.fabricante || '';
        const laboratorio = item.laboratorio || item.laboratory || '';
        const principioActivo = item.principio_activo || item.active_ingredient || '';
        
        html += `
            <div class="list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">${nombre}</h5>
                    ${laboratorio ? `<small class="text-muted">${laboratorio}</small>` : ''}
                </div>
                ${presentacion ? `<p class="mb-1"><strong>Presentación:</strong> ${presentacion}</p>` : ''}
                ${principioActivo ? `<p class="mb-1"><strong>Principio activo:</strong> ${principioActivo}</p>` : ''}
                ${descripcion ? `<p class="mb-1">${descripcion}</p>` : ''}
                <button class="btn btn-sm btn-outline-primary mt-2 btn-seleccionar-remedio" 
                        data-nombre="${nombre}" 
                        data-presentacion="${presentacion}">
                    <i class="fas fa-plus-circle"></i> Agregar a receta
                </button>
            </div>
        `;
    });
    
    html += `</div>`;
    
    // Actualizar el contenedor de resultados
    resultadosContainer.innerHTML = html;
    
    // Agregar evento para seleccionar un medicamento
    document.querySelectorAll('.btn-seleccionar-remedio').forEach(btn => {
        btn.addEventListener('click', function() {
            const nombreMedicamento = this.getAttribute('data-nombre');
            const presentacion = this.getAttribute('data-presentacion');
            
            agregarMedicamentoAReceta(nombreMedicamento, presentacion);
        });
    });
}

/**
 * Agrega un medicamento al área de receta
 * @param {string} nombre - Nombre del medicamento
 * @param {string} presentacion - Presentación del medicamento
 */
function agregarMedicamentoAReceta(nombre, presentacion) {
    // Verificar si existe el editor de texto para la receta
    const recetaTextarea = document.getElementById('receta-textarea');
    
    if (recetaTextarea) {
        // Si es un editor Summernote
        if ($(recetaTextarea).data('summernote')) {
            const contenidoActual = $(recetaTextarea).summernote('code');
            const nuevoContenido = contenidoActual + 
                `<p><strong>${nombre}</strong>${presentacion ? ` (${presentacion})` : ''}</p>` +
                `<p>Indicaciones: </p>`;
            
            $(recetaTextarea).summernote('code', nuevoContenido);
        } else {
            // Si es un textarea normal
            recetaTextarea.value += `\n${nombre}${presentacion ? ` (${presentacion})` : ''}\nIndicaciones: \n`;
        }
        
        // Mostrar mensaje de confirmación
        mostrarMensaje('success', `Medicamento "${nombre}" agregado a la receta`);
        
        // Cambiar a la pestaña de registro
        $('a[href="#activity"]').tab('show');
    } else {
        mostrarMensaje('warning', 'No se pudo encontrar el campo de receta');
    }
}

/**
 * Muestra un mensaje utilizando SweetAlert2
 * @param {string} tipo - Tipo de mensaje (success, error, warning, info)
 * @param {string} mensaje - Texto del mensaje
 */
function mostrarMensaje(tipo, mensaje) {
    Swal.fire({
        icon: tipo,
        title: tipo === 'success' ? 'Éxito' : 'Atención',
        text: mensaje,
        timer: 3000,
        timerProgressBar: true
    });
}
