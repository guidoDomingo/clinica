/**
 * Archivo JavaScript para la gestión de preformatos y motivos comunes
 * en el formulario de consultas médicas
 */

// Cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Cargar los motivos comunes y preformatos
    cargarPreformatosConsulta();
    cargarPreformatosReceta();
    
    // Agregar event listeners a los selectores
    const selectMotivosComunes = document.getElementById('motivoscomunes');
    const selectFormatoConsulta = document.getElementById('formatoConsulta');
    const selectFormatoReceta = document.getElementById('formatoreceta');
    
    if (selectMotivosComunes) {
        selectMotivosComunes.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value !== 'Seleccionar') {
                document.getElementById('txtmotivo').value = selectedOption.text;
            }
        });
    }
    
    if (selectFormatoConsulta) {
        selectFormatoConsulta.addEventListener('change', function() {
            aplicarPreformato('consulta', this.value);
        });
    }
    
    if (selectFormatoReceta) {
        selectFormatoReceta.addEventListener('change', function() {
            aplicarPreformato('receta', this.value);
        });
    }
});

/**
 * Función para cargar los motivos comunes en el selector
 */
function cargarMotivosComunes() {
    const selectMotivosComunes = document.getElementById('motivoscomunes');
    if (!selectMotivosComunes) return;
    
    // Limpiar opciones existentes excepto la primera
    while (selectMotivosComunes.options.length > 1) {
        selectMotivosComunes.remove(1);
    }
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getMotivosComunes');
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success' && response.data.length > 0) {
                // Agregar opciones al selector
                response.data.forEach(function(motivo) {
                    const option = document.createElement('option');
                    option.value = motivo.id_motivo;
                    option.text = motivo.nombre;
                    option.setAttribute('data-descripcion', motivo.descripcion);
                    selectMotivosComunes.appendChild(option);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar motivos comunes:", error);
        }
    });
}

/**
 * Función para cargar los preformatos de consulta en el selector
 */
function cargarPreformatosConsulta() {
    console.log('Iniciando carga de preformatos de consulta...');
    const selectFormatoConsulta = document.getElementById('formatoConsulta');
    if (!selectFormatoConsulta) {
        console.log('Elemento formatoConsulta no encontrado en el DOM');
        return;
    }
    
    console.log('Elemento formatoConsulta encontrado, limpiando opciones...');
    // Limpiar opciones existentes excepto la primera
    while (selectFormatoConsulta.options.length > 1) {
        selectFormatoConsulta.remove(1);
    }
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getPreformatosConsulta');
    
    console.log('Enviando petición AJAX para obtener preformatos de consulta...');
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Respuesta recibida para preformatos de consulta:', response);
            if (response.status === 'success' && response.data.length > 0) {
                console.log('Preformatos de consulta obtenidos correctamente, cantidad:', response.data.length);
                // Agregar opciones al selector
                response.data.forEach(function(preformato) {
                    const option = document.createElement('option');
                    option.value = preformato.id_preformato;
                    option.text = preformato.nombre;
                    option.setAttribute('data-contenido', preformato.contenido);
                    selectFormatoConsulta.appendChild(option);
                });
                console.log('Preformatos de consulta agregados al selector');
            } else {
                console.log('No se encontraron preformatos de consulta o hubo un error:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar preformatos de consulta:", error);
            console.error("Estado de la petición:", status);
            console.error("Respuesta del servidor:", xhr.responseText);
        }
    });
}

/**
 * Función para cargar los preformatos de receta en el selector
 */
function cargarPreformatosReceta() {
    console.log('Iniciando carga de preformatos de receta...');
    const selectFormatoReceta = document.getElementById('formatoreceta');
    if (!selectFormatoReceta) {
        console.log('Elemento formatoreceta no encontrado en el DOM');
        return;
    }
    
    console.log('Elemento formatoreceta encontrado, limpiando opciones...');
    // Limpiar opciones existentes excepto la primera
    while (selectFormatoReceta.options.length > 1) {
        selectFormatoReceta.remove(1);
    }
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getPreformatosReceta');
    
    console.log('Enviando petición AJAX para obtener preformatos de receta...');
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Respuesta recibida para preformatos de receta:', response);
            if (response.status === 'success' && response.data.length > 0) {
                console.log('Preformatos de receta obtenidos correctamente, cantidad:', response.data.length);
                // Agregar opciones al selector
                response.data.forEach(function(preformato) {
                    const option = document.createElement('option');
                    option.value = preformato.id_preformato;
                    option.text = preformato.nombre;
                    option.setAttribute('data-contenido', preformato.contenido);
                    selectFormatoReceta.appendChild(option);
                });
                console.log('Preformatos de receta agregados al selector');
            } else {
                console.log('No se encontraron preformatos de receta o hubo un error:', response);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar preformatos de receta:", error);
            console.error("Estado de la petición:", status);
            console.error("Respuesta del servidor:", xhr.responseText);
        }
    });
}

/**
 * Función para aplicar un preformato seleccionado
 * @param {string} tipo - Tipo de preformato ('consulta' o 'receta')
 * @param {string} idPreformato - ID del preformato seleccionado
 */
function aplicarPreformato(tipo, idPreformato) {
    if (idPreformato === 'Seleccionar') return;
    
    let selector, textareaId;
    
    if (tipo === 'consulta') {
        selector = document.getElementById('formatoConsulta');
        textareaId = 'consulta-textarea';
    } else if (tipo === 'receta') {
        selector = document.getElementById('formatoreceta');
        textareaId = 'receta-textarea';
    } else {
        return;
    }
    
    // Obtener el contenido del preformato
    const selectedOption = selector.options[selector.selectedIndex];
    const contenido = selectedOption.getAttribute('data-contenido');
    
    if (contenido) {
        // Aplicar el contenido al textarea correspondiente
        document.getElementById(textareaId).value = contenido;
    }
}