<div class="container icd11-container">
    <!-- Contenedor para alertas -->
    <div id="alerts-container"></div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Herramienta de Codificación ICD-11</h2>
                </div>
                <div class="card-body">                } finally {
                    // Restaurar el botón
                    searchButton.innerHTML = '<i class="fas fa-search"></i> Buscar';
                    searchButton.disabled = false;
                    
                    // Registrar estado del cliente para depuración
                    if (window.icd11Client) {
                        console.log('Cliente ICD-11 disponible. Estado:', 
                            window.icd11Client.initialized ? 'Inicializado' : 'No inicializado');
                    } else {
                        console.error('¡ALERTA! El cliente ICD-11 no está disponible en el objeto window');
                    }
                }<div class="mb-4">
                        <p>Esta página integra la herramienta oficial de codificación ICD-11 de la Organización Mundial de la Salud.</p>
                    </div>

                    <!-- Panel de código y diagnóstico seleccionados -->
                    <!-- <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Selección de código y diagnóstico</h5>
                            <p class="text-muted small mt-1 mb-0">Busque y seleccione un diagnóstico en la herramienta de codificación, luego copie el código y la descripción</p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="selected-code" class="form-label"><strong>Código ICD-11:</strong></label>
                                        <div class="input-group">
                                            <input type="text" id="selected-code" class="form-control" placeholder="Ej: MD12">
                                            <button class="btn btn-outline-secondary" type="button" id="search-code-btn" title="Buscar diagnóstico por código">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" type="button" id="clear-code-btn">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Introduzca el código y presione <i class="fas fa-search"></i> para buscar</div>
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label for="selected-diagnosis" class="form-label"><strong>Diagnóstico:</strong></label>
                                        <div class="input-group">
                                            <input type="text" id="selected-diagnosis" class="form-control" placeholder="Ej: Tos">
                                            <button class="btn btn-outline-secondary" type="button" id="clear-diagnosis-btn">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Introduzca o copie el diagnóstico desde la herramienta</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12 text-end">
                                    <div id="api-status" class="d-inline-block me-2"></div>
                                    <button id="save-selection-btn" class="btn btn-success">
                                        <i class="fas fa-save"></i> Guardar selección
                                    </button>
                                    <button id="copy-to-clipboard-btn" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Copiar al portapapeles">
                                        <i class="fas fa-clipboard"></i> Copiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div> -->                    <!-- Instrucciones de uso -->
                    <div class="alert alert-info mb-4" role="alert">
                        <h5><i class="fas fa-info-circle"></i> Instrucciones de uso:</h5>
                        <ol>
                            <li>Utilice la herramienta de búsqueda ICD-11 a continuación para encontrar el diagnóstico deseado</li>
                            <li>Cuando encuentre el código correcto, selecciónelo en la herramienta</li>
                            <li>Copie el código (ej: MD12) en el campo correspondiente y haga clic en <i class="fas fa-search"></i> para obtener el diagnóstico automáticamente</li>
                            <li>También puede ingresar manualmente el diagnóstico si lo prefiere</li>
                            <li>Haga clic en "Guardar selección" para utilizar estos valores</li>
                        </ol>
                        <p class="mb-0"><strong>Consejo:</strong> Puede ver el código seleccionado en la barra de estado de la herramienta donde dice "Seleccionado: XXX"</p>                        <div class="mt-2 border-top pt-2">
                            <p class="mb-0">
                                <i class="fas fa-shield-alt text-success"></i> 
                                <strong>IMPORTANTE:</strong> Este sistema solo utiliza datos oficiales de la API de ICD-11 de la OMS. 
                                <span class="badge bg-success">No hay datos locales, respuestas predefinidas o autocompletadas</span>
                            </p>
                            <small class="text-muted">Todos los resultados provienen directamente de la API oficial de la OMS.</small>
                              <!-- Panel de herramientas y diagnóstico -->
                            <div class="mt-3 d-flex flex-wrap gap-2">
                                <a href="icd11_reference_codes.php" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-list-ul"></i> Códigos de Referencia
                                </a>
                                <a href="debug_icd.php" target="_blank" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-bug"></i> Depurar Conexión
                                </a>
                                <a href="test_icd11_class.php" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-wrench"></i> Diagnóstico de API
                                </a>
                                <a href="icd11_check_requirements.php" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-check-circle"></i> Verificar Requisitos
                                </a>
                            </div><!-- Notas sobre posibles errores -->
                            <div class="mt-3 small">
                                <p class="mb-1"><i class="fas fa-info-circle text-primary"></i> <strong>Consejos para la búsqueda por código:</strong></p>
                                <ul class="mb-1">
                                    <li><strong>Códigos válidos conocidos:</strong> MB36 (Diabetes), BA00 (Hipertensión), CA20.Z (Gripe)</li>
                                    <li>Use códigos ICD-11 específicos y completos (ej. MB36.0 en lugar de M12)</li>
                                    <li>Si un código no funciona, intente con la búsqueda por término en su lugar</li>
                                </ul>
                                
                                <p class="mb-1 mt-2"><i class="fas fa-exclamation-triangle text-warning"></i> <strong>Solución de problemas:</strong></p>
                                <ul class="mb-1">
                                    <li><strong>Error 400:</strong> Falta el encabezado API-Version (corregido en esta versión)</li>
                                    <li><strong>Error 404:</strong> El código no existe en la base de datos ICD-11</li>
                                    <li><strong>Otros errores:</strong> Verificar conexión a Internet y usar las herramientas de diagnóstico</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Contenedor para la herramienta de codificación -->
                    <div class="coding-tool-container" style="height: 700px; margin-bottom: 20px; position: relative;">
                        <!-- Spinner de carga -->
                        <div id="loading-spinner" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; background-color: #f8f9fa; z-index: 1000;">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Cargando herramienta de codificación ICD-11...</p>
                            </div>
                        </div>

                        <!-- iframe con la herramienta oficial de codificación de la OMS -->
                        <iframe
                            id="coding-tool-iframe"
                            src="https://icd.who.int/ct/icd11_mms/es/2022-02"
                            style="width: 100%; height: 100%; border: 1px solid #ddd;"
                            onload="iframeLoaded()"
                            onerror="handleIframeError()">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Implementación interna del cliente ICD-11 para evitar problemas de carga -->
<script>
// Cliente simplificado para la API ICD-11
class ICD11ApiClient {
    constructor() {
        this.endpoint = 'ajax/icd11.ajax.php';
        this.initialized = false;
    }
    
    // Inicializa el cliente
    async initialize() {
        if (this.initialized) {
            return Promise.resolve(true);
        }
        
        try {
            // Probar conectividad con el endpoint
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
                throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
            }
            
            const jsonResponse = await response.json();
            if (!jsonResponse.success) {
                throw new Error(`Error en la API: ${jsonResponse.message || 'Desconocido'}`);
            }
            
            console.log('API ICD-11 funcionando correctamente');
            this.initialized = true;
            return true;
        } catch (error) {
            console.error('Error al inicializar el cliente ICD-11:', error);
            throw error;
        }
    }
    
    // Busca un término en la API
    async searchByTerm(term, language = 'es') {
        if (!term) {
            return Promise.reject(new Error('El término es requerido'));
        }
        
        if (!this.initialized) {
            await this.initialize();
        }
        
        const formData = new FormData();
        formData.append('action', 'searchByTerm');
        formData.append('term', term);
        formData.append('language', language);
        
        try {
            const response = await fetch(this.endpoint, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
            }
            
            const jsonResponse = await response.json();
            if (!jsonResponse.success) {
                throw new Error(jsonResponse.message || 'Error desconocido en la respuesta del servidor');
            }
            
            return jsonResponse.data;
        } catch (error) {
            console.error('Error en solicitud searchByTerm:', error);
            throw error;
        }
    }
    
    // Busca un código en la API
    async searchByCode(code) {
        if (!code) {
            return Promise.reject(new Error('El código es requerido'));
        }
        
        if (!this.initialized) {
            await this.initialize();
        }
        
        const formData = new FormData();
        formData.append('action', 'searchByCode');
        formData.append('code', code);
        
        try {
            const response = await fetch(this.endpoint, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
            }
            
            const jsonResponse = await response.json();
            if (!jsonResponse.success) {
                throw new Error(jsonResponse.message || 'Error desconocido en la respuesta del servidor');
            }
            
            return jsonResponse.data;
        } catch (error) {
            console.error('Error en solicitud searchByCode:', error);
            throw error;
        }
    }
}

// Crear la instancia global
window.icd11Client = new ICD11ApiClient();
console.log('Cliente ICD-11 creado. Se inicializará cuando sea necesario.');
</script>
<script>
    function handleIframeError() {
        document.getElementById('loading-spinner').innerHTML =
            '<div class="alert alert-danger m-3">' +
            '<h4 class="alert-heading">Error al cargar la herramienta de codificación</h4>' +
            '<p>No se pudo cargar la herramienta de codificación desde el servidor de la OMS.</p>' +
            '<hr>' +
            '<p class="mb-0">Sugerencias:' +
            '<ul>' +
            '<li>Verifique su conexión a Internet</li>' +
            '<li>Compruebe que los servidores de la OMS estén accesibles</li>' +
            '<li>Intente recargar la página</li>' +
            '</ul></p></div>';
    }

    function iframeLoaded() {
        // Ocultar el spinner de carga
        document.getElementById('loading-spinner').style.display = 'none';

        // Configurar eventos para los botones
        setupButtonEvents();
    }

    function setupButtonEvents() {
        // Botón para buscar código
        document.getElementById('search-code-btn').addEventListener('click', function() {
            searchCodeFromApi();
        });

        // Evento para buscar al presionar Enter en el campo de código
        document.getElementById('selected-code').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchCodeFromApi();
            }
        });

        // Botón para limpiar código
        document.getElementById('clear-code-btn').addEventListener('click', function() {
            document.getElementById('selected-code').value = '';
            document.getElementById('selected-code').focus();
        });

        // Botón para limpiar diagnóstico
        document.getElementById('clear-diagnosis-btn').addEventListener('click', function() {
            document.getElementById('selected-diagnosis').value = '';
            document.getElementById('selected-diagnosis').focus();
        });

        // Botón para guardar selección
        document.getElementById('save-selection-btn').addEventListener('click', function() {
            const code = document.getElementById('selected-code').value.trim();
            const diagnosis = document.getElementById('selected-diagnosis').value.trim();

            if (!code) {
                showAlert('warning', 'Por favor, introduzca un código ICD-11.');
                document.getElementById('selected-code').focus();
                return;
            }

            if (!diagnosis) {
                showAlert('warning', 'Por favor, introduzca un diagnóstico.');
                document.getElementById('selected-diagnosis').focus();
                return;
            }

            // Aquí puedes añadir el código para guardar o procesar la selección
            // Por ejemplo, enviarla a un servidor, mostrarla en otra parte, etc.

            // Por ahora, solo mostramos un mensaje de éxito
            showAlert('success', 'Selección guardada: ' + code + ' - ' + diagnosis);

            // Destacar los campos brevemente
            flashElement(document.getElementById('selected-code'));
            flashElement(document.getElementById('selected-diagnosis'));
        });

        // Botón para copiar al portapapeles
        document.getElementById('copy-to-clipboard-btn').addEventListener('click', function() {
            const code = document.getElementById('selected-code').value.trim();
            const diagnosis = document.getElementById('selected-diagnosis').value.trim();

            if (!code && !diagnosis) {
                showAlert('warning', 'No hay datos para copiar.');
                return;
            }

            const textToCopy = `${code} - ${diagnosis}`;

            // Copiar al portapapeles
            navigator.clipboard.writeText(textToCopy).then(function() {
                showAlert('success', 'Copiado al portapapeles: ' + textToCopy);
            }, function(err) {
                console.error('Error al copiar: ', err);
                showAlert('danger', 'No se pudo copiar al portapapeles. Por favor, copie manualmente.');
            });
        });
    }

    function searchCodeFromApi() {
        const code = document.getElementById('selected-code').value.trim();
        if (!code) {
            showAlert('warning', 'Por favor, introduzca un código ICD-11 para buscar.');
            document.getElementById('selected-code').focus();
            return;
        }

        // Mostrar que estamos buscando
        const apiStatus = document.getElementById('api-status');
        apiStatus.innerHTML = '<span class="badge bg-info"><i class="fas fa-spinner fa-spin"></i> Buscando...</span>';

        // Hacer la petición a la API
        fetch(`/api/icd11/code/${code}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    // Actualizar el campo de diagnóstico con el resultado
                    document.getElementById('selected-diagnosis').value = data.data.title || '';

                    // Guardar los datos completos en un atributo para uso posterior si es necesario
                    document.getElementById('selected-code').setAttribute('data-full-entity',
                                                                       JSON.stringify(data.data));

                    // Mostrar resultado positivo
                    apiStatus.innerHTML = '<span class="badge bg-success"><i class="fas fa-check"></i> Encontrado</span>';

                    // Destacar los campos brevemente
                    flashElement(document.getElementById('selected-code'));
                    flashElement(document.getElementById('selected-diagnosis'));

                    // Eliminar el estado después de un tiempo
                    setTimeout(() => {
                        apiStatus.innerHTML = '';
                    }, 3000);
                } else {
                    // Mostrar error
                    apiStatus.innerHTML = '<span class="badge bg-danger"><i class="fas fa-times"></i> No encontrado</span>';
                    document.getElementById('selected-diagnosis').value = '';
                    showAlert('warning', `No se encontró el código ICD-11 "${code}". Por favor, verifique e intente de nuevo.`);

                    // Eliminar el estado después de un tiempo
                    setTimeout(() => {
                        apiStatus.innerHTML = '';
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                apiStatus.innerHTML = '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Error</span>';
                showAlert('danger', 'Error al consultar la API. Por favor, intente más tarde.');

                // Eliminar el estado después de un tiempo
                setTimeout(() => {
                    apiStatus.innerHTML = '';
                }, 3000);
            });
    }

    function showAlert(type, message) {
        // Crear el elemento de alerta
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Encontrar el contenedor de alertas
        const container = document.getElementById('alerts-container');

        // Insertar la alerta al inicio del contenedor
        container.appendChild(alertDiv);

        // Eliminar la alerta después de 5 segundos
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => {
                if (container.contains(alertDiv)) {
                    container.removeChild(alertDiv);
                }
            }, 150);
        }, 5000);
    }

    function flashElement(element) {
        // Añadir clase para destacar brevemente el elemento
        element.classList.add('bg-success', 'text-white');

        // Eliminar la clase después de un breve período
        setTimeout(function() {
            element.classList.remove('bg-success', 'text-white');
        }, 1000);
    }

    // Verificar si el iframe se cargó correctamente después de un tiempo
    window.addEventListener('load', function() {
        setTimeout(function() {
            const iframe = document.getElementById('coding-tool-iframe');
            if (iframe && document.getElementById('loading-spinner').style.display !== 'none') {
                handleIframeError();
            }
        }, 15000); // Esperar 15 segundos como máximo

        // Inicializar tooltips de Bootstrap si está disponible
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });        }
    });
</script>

<script>
    // Código para integración directa con la API de ICD-11
    document.addEventListener('DOMContentLoaded', function() {
        // Agregar botón directo para búsqueda API
        const searchContainer = document.createElement('div');
        searchContainer.className = 'card mb-3';
        searchContainer.innerHTML = `
            <div class="card-header bg-light">
                <h5 class="mb-0">Búsqueda directa API ICD-11</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="api-search-term">Buscar término médico:</label>
                            <input type="text" id="api-search-term" class="form-control" placeholder="Ej: tos, diabetes, covid">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button id="btn-api-search" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="api-results" class="mt-3" style="display: none;">
                    <h6>Resultados de búsqueda:</h6>
                    <div class="list-group" id="api-results-list">
                    </div>
                </div>
            </div>
        `;
        
        // Insertar el contenedor de búsqueda antes del iframe
        const iframeContainer = document.querySelector('.coding-tool-container');
        if (iframeContainer) {
            iframeContainer.parentNode.insertBefore(searchContainer, iframeContainer);
        }
        
        // Configurar evento para el botón de búsqueda
        const searchButton = document.getElementById('btn-api-search');
        const searchInput = document.getElementById('api-search-term');
        const resultsContainer = document.getElementById('api-results');
        const resultsList = document.getElementById('api-results-list');
        
        if (searchButton && searchInput) {
            searchButton.addEventListener('click', async () => {
                const term = searchInput.value.trim();
                if (!term) return;
                
                // Mostrar spinner
                searchButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Buscando...';
                searchButton.disabled = true;                try {
                    // Verificar que existe el cliente (debería existir siempre con nuestra implementación interna)
                    if (!window.icd11Client) {
                        console.error('El cliente ICD-11 no está disponible');
                        throw new Error('Error interno: cliente ICD-11 no disponible. Por favor, recargue la página.');
                    }
                    
                    // Iniciar búsqueda (la inicialización se maneja dentro de searchByTerm)
                    console.log('Ejecutando búsqueda con término:', term);
                    const results = await window.icd11Client.searchByTerm(term);
                    
                    // Vaciar y mostrar contenedor de resultados
                    resultsList.innerHTML = '';
                    resultsContainer.style.display = 'block';
                      // Si no hay resultados válidos de la API
                    if (!results || !results.destinationEntities || results.destinationEntities.length === 0) {
                        resultsList.innerHTML = '<div class="alert alert-info">No se encontraron resultados para este término en la API oficial de ICD-11</div>';
                        return;
                    }
                    
                    // Mostrar los resultados
                    results.destinationEntities.slice(0, 10).forEach(entity => {
                        const code = entity.theCode || '';
                        const title = entity.title || '';
                        
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.innerHTML = `
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${code}</h6>
                            </div>
                            <p class="mb-1">${title}</p>
                        `;
                        
                        // Evento para seleccionar el código
                        item.addEventListener('click', (e) => {
                            e.preventDefault();
                            
                            // Disparar evento de código seleccionado
                            window.icd11Client.dispatchCodeSelected({
                                code: code,
                                title: title,
                                uri: entity.uri || ''
                            });
                        });
                        
                        resultsList.appendChild(item);
                    });                } catch (error) {
                    console.error('Error al buscar término:', error);                    // Mostrar mensaje de error adaptado al tipo
                    if (error.message.includes('no disponible') || error.message.includes('Error interno')) {
                        resultsList.innerHTML = `
                            <div class="alert alert-danger">
                                <h5>Error en el cliente ICD-11</h5>
                                <p>${error.message}</p>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-danger" onclick="location.reload()">
                                        <i class="fas fa-sync"></i> Recargar página
                                    </button>
                                </div>
                            </div>
                        `;
                    } else {
                        resultsList.innerHTML = `
                            <div class="alert alert-danger">
                                <h5>Error en la API de ICD-11</h5>
                                <p>${error.message || 'Error desconocido'}</p>
                                <small>Solo se utilizan datos oficiales de la API de ICD-11. No hay respuestas locales o autocompletadas.</small>
                            </div>
                        `;
                    }
                    
                    resultsContainer.style.display = 'block';
                } finally {
                    // Restaurar botón
                    searchButton.innerHTML = '<i class="fas fa-search"></i> Buscar';
                    searchButton.disabled = false;
                }
            });
            
            // Permitir buscar presionando Enter
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    searchButton.click();
                }
            });
        }
    });
</script>

<script>    // Función simplificada para manejar errores de API
    function handleApiError(error) {
        console.error('Error en la API de ICD-11:', error);
        showAlert('danger', 'Error en la API de ICD-11: ' + error.message);
    }
    
    // Inicializar el cliente cuando sea necesario
    document.addEventListener('DOMContentLoaded', () => {
        console.log('Documento cargado. El cliente ICD-11 se inicializará en la primera búsqueda.');
    });
</script>

