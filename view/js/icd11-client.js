/**
 * ICD11ApiClient.js
 * Cliente JavaScript para interactuar con el endpoint AJAX de ICD-11
 */

class ICD11ApiClient {
    /**
     * Constructor
     */    constructor() {
        this.endpoint = 'ajax/icd11.ajax.php';
        this.initialized = false;
        
        // Evento personalizado para cuando se selecciona un código
        this.codeSelectedEvent = new CustomEvent('icd11:codeSelected', {
            bubbles: true,
            cancelable: true,
            detail: null
        });
    }
    
    /**
     * Inicializa el cliente
     * @returns {Promise} Promesa que se resuelve cuando la inicialización está completa
     */    async initialize() {
        if (this.initialized) {
            return Promise.resolve(true);
        }
        
        try {
            // Probar conectividad con el endpoint
            try {
                // Usar FormData para mayor compatibilidad
                const formData = new FormData();
                formData.append('action', 'searchByCode');
                formData.append('code', 'MD12'); // Código de prueba
                
                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.warn('Error al probar API:', errorText);
                    throw new Error(`Error al conectar con la API: ${errorText}`);
                } else {
                    const jsonResponse = await response.json();
                    if (!jsonResponse.success) {
                        console.warn('API respondió con error:', jsonResponse.message);
                        throw new Error(`Error en la API: ${jsonResponse.message}`);
                    } else {
                        console.log('API funcionando correctamente');
                    }
                }
            } catch (testError) {
                console.error('No se pudo conectar a la API:', testError);
                // Propagamos el error para que la inicialización falle
                throw testError;
            }
            
            this.initialized = true;
            console.log('ICD11ApiClient inicializado correctamente');
            return Promise.resolve(true);
        } catch (error) {
            console.error('Error al inicializar ICD11ApiClient:', error);
            return Promise.reject(error);
        }
    }
    
    /**
     * Busca un código ICD-11
     * @param {string} code - Código a buscar (ej: MD12)
     * @returns {Promise} Promesa con los resultados
     */
    async searchByCode(code) {
        if (!code) {
            return Promise.reject(new Error('El código es requerido'));
        }
        
        return this._makeRequest({
            action: 'searchByCode',
            code: code
        });
    }
    
    /**
     * Busca términos en ICD-11
     * @param {string} term - Término a buscar
     * @param {string} language - Idioma (es, en)
     * @returns {Promise} Promesa con los resultados
     */
    async searchByTerm(term, language = 'es') {
        if (!term) {
            return Promise.reject(new Error('El término es requerido'));
        }
        
        return this._makeRequest({
            action: 'searchByTerm',
            term: term,
            language: language
        });
    }
    
    /**
     * Obtiene detalles de una entidad
     * @param {string} uri - URI de la entidad
     * @returns {Promise} Promesa con los detalles
     */
    async getEntityDetails(uri) {
        if (!uri) {
            return Promise.reject(new Error('El URI es requerido'));
        }
        
        return this._makeRequest({
            action: 'getEntityDetails',
            uri: uri
        });
    }
    
    /**
     * Dispara el evento de código seleccionado
     * @param {Object} data - Datos del código seleccionado
     */
    dispatchCodeSelected(data) {
        // Actualizar los detalles del evento
        this.codeSelectedEvent = new CustomEvent('icd11:codeSelected', {
            bubbles: true,
            cancelable: true,
            detail: data
        });
        
        // Disparar el evento en el documento
        document.dispatchEvent(this.codeSelectedEvent);
        
        console.log('Evento icd11:codeSelected disparado con datos:', data);
    }
      /**
     * Realiza una solicitud al endpoint
     * @param {Object} data - Datos a enviar
     * @returns {Promise} Promesa con la respuesta
     * @private
     */    async _makeRequest(data) {
        try {
            console.log('Enviando solicitud ICD-11:', data);
            
            // Usar FormData para mayor compatibilidad
            const formData = new FormData();
            for (const key in data) {
                formData.append(key, typeof data[key] === 'object' ? 
                    JSON.stringify(data[key]) : data[key]);
            }
              // Realizar la solicitud
            const response = await fetch(this.endpoint, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            // Verificar errores HTTP
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Error en respuesta HTTP:', errorText);
                
                // Comprobar si es un error de PHP
                if (errorText.includes('Fatal error') || errorText.includes('Warning')) {
                    throw new Error(`Error del servidor: ${this._extractPHPError(errorText)}`);
                } else {
                    throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
                }
            }
            
            // Intentar procesar como JSON
            let jsonResponse;
            try {
                jsonResponse = await response.json();
            } catch (parseError) {
                console.error('Error al parsear respuesta JSON:', parseError);
                
                // Obtener el texto original para ver qué falló
                const responseText = await response.text();
                throw new Error(`Respuesta del servidor inválida: ${responseText.substring(0, 100)}...`);
            }
            
            // Verificar si la respuesta indica error
            if (!jsonResponse.success) {
                throw new Error(jsonResponse.message || 'Error desconocido en la respuesta del servidor');
            }            return jsonResponse.data;
        } catch (error) {
            console.error('Error en solicitud ICD-11:', error);
            throw error;
        }
    }    }
    
    /**
     * Extrae mensaje de error de PHP de una respuesta HTML
     * @param {string} htmlText - Texto HTML que contiene un error de PHP
     * @returns {string} Mensaje de error extraído
     * @private
     */
    _extractPHPError(htmlText) {
        // Buscar mensajes de error comunes en PHP
        const errorPatterns = [
            /<b>Fatal error<\/b>:\s*(.+?)<br>/,
            /<b>Warning<\/b>:\s*(.+?)<br>/,
            /<b>Notice<\/b>:\s*(.+?)<br>/,
            /PHP (?:Fatal )?Error:\s*(.+?)</,
            /Call to undefined function\s+(.+?)</
        ];
        
        for (const pattern of errorPatterns) {
            const match = htmlText.match(pattern);
            if (match && match[1]) {
                return match[1].trim();
            }
        }
        
        // Si no se encuentra un patrón específico, devolver un fragmento del HTML
        return htmlText.substring(0, 150).replace(/<[^>]*>/g, ' ').trim() + '...';
    }
}

// Crear e inicializar la instancia global
window.icd11Client = new ICD11ApiClient();

// Inicializar cuando el documento esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.icd11Client.initialize()
        .catch(error => {
            console.error('No se pudo inicializar el cliente ICD-11:', error);
        });
        
    // Escuchar eventos de la herramienta ICD-11
    document.addEventListener('icd11:codeSelected', (event) => {
        const code = event.detail.code;
        const description = event.detail.title || event.detail.description || '';
        
        // Actualizar campos del formulario
        updateICDFields(code, description);
        
        // Mostrar notificación
        showICDNotification(code, description);
    });
});

/**
 * Actualiza los campos del formulario con el código ICD-11
 * @param {string} code - Código ICD-11
 * @param {string} description - Descripción del código
 */
function updateICDFields(code, description) {
    // Elementos del formulario
    const codeFields = ['codigo_diagnostico', 'icd_code', 'diagnostico_codigo'];
    const descFields = ['descripcion_diagnostico', 'icd_description', 'diagnostico_descripcion'];
    
    let codeUpdated = false;
    let descUpdated = false;
    
    // Actualizar campos de código
    for (const fieldId of codeFields) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = code;
            codeUpdated = true;
        }
    }
    
    // Actualizar campos de descripción
    for (const fieldId of descFields) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = description;
            descUpdated = true;
        }
    }
    
    // Si no se actualizó ningún campo específico, usar el campo de motivo
    if (!codeUpdated || !descUpdated) {
        const motivoField = document.getElementById('txtmotivo');
        if (motivoField) {
            const currentValue = motivoField.value.trim();
            const newValue = `${code} - ${description}`;
            
            // Evitar duplicados
            if (!currentValue.includes(newValue)) {
                motivoField.value = currentValue ? `${currentValue} | ${newValue}` : newValue;
            }
        }
    }
    
    // Actualizar elemento visual si existe
    const displayElement = document.getElementById('diagnostico_seleccionado');
    const textElement = document.getElementById('diagnostico_codigo_texto');
    
    if (displayElement) {
        if (textElement) {
            textElement.textContent = `${code} - ${description}`;
        } else {
            displayElement.textContent = `${code} - ${description}`;
        }
        displayElement.classList.remove('d-none');
    }
    
    // Si existe un editor Summernote, añadir el diagnóstico
    if (typeof $ !== 'undefined' && $('#consulta-textarea').length > 0 && 
        $('#consulta-textarea').data('summernote')) {
        try {
            const currentContent = $('#consulta-textarea').summernote('code');
            const diagnosisHtml = `<p><strong>Diagnóstico:</strong> ${code} - ${description}</p>`;
            
            if (!currentContent.includes(code)) {
                $('#consulta-textarea').summernote('code', currentContent + diagnosisHtml);
            }
        } catch (err) {
            console.warn('Error al actualizar editor:', err);
        }
    }
    
    console.log(`Campos actualizados con: Código=${code}, Descripción=${description}`);
}

/**
 * Muestra una notificación
 * @param {string} code - Código ICD-11
 * @param {string} description - Descripción del código
 */
function showICDNotification(code, description) {
    // Crear un contenedor para la notificación si no existe
    let notification = document.getElementById('icd-capture-notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'icd-capture-notification';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 10000;
            max-width: 350px;
            opacity: 0;
            transition: opacity 0.3s;
            font-family: Arial, sans-serif;
            font-size: 14px;
        `;
        document.body.appendChild(notification);
    }
    
    // Actualizar contenido
    notification.innerHTML = `
        <strong>Código ICD-11 capturado</strong><br>
        <b>Código:</b> ${code}<br>
        <b>Descripción:</b> ${description}
    `;
    
    // Mostrar y luego ocultar
    notification.style.opacity = '1';
    setTimeout(() => {
        notification.style.opacity = '0';
    }, 3000);
}

// Exportar cliente para uso global
window.ICD11ApiClient = ICD11ApiClient;
