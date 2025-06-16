/**
 * icd11-integration.js
 * Funciones para la integración de ICD-11 con el formulario de consulta
 */

// Función global para encontrar el textarea de la consulta usando diferentes estrategias
function findConsultaTextarea() {
    console.log('Buscando el textarea de consulta...');

    // Estrategia 1: Verificar si existe un elemento Summernote para el textarea
    if (typeof $ !== 'undefined' && $('#consulta-textarea').length > 0) {
        if ($('#consulta-textarea').data('summernote')) {
            console.log('Editor Summernote encontrado para #consulta-textarea');
            return {
                element: document.getElementById('consulta-textarea'),
                isSummernote: true,
                setValue: function(text) {
                    // Para Summernote usamos la API de jQuery para establecer el contenido HTML
                    $('#consulta-textarea').summernote('code', text);
                    // También actualizamos el textarea subyacente para asegurar consistencia
                    document.getElementById('consulta-textarea').value = text;
                    console.log('Texto insertado en editor Summernote');
                },
                getValue: function() {
                    // Recuperar el contenido HTML actual del editor
                    return $('#consulta-textarea').summernote('code');
                },
                focus: function() {
                    $('#consulta-textarea').summernote('focus');
                }
            };
        }
    }
    
    // Estrategia 2: Buscar directamente por ID (textarea normal)
    let textarea = document.getElementById('consulta-textarea');
    if (textarea) {
        console.log('Textarea estándar encontrado por ID');
        return {
            element: textarea,
            isSummernote: false,
            setValue: function(text) {
                textarea.value = text;
                console.log('Texto insertado en textarea estándar');
            },
            getValue: function() {
                return textarea.value;
            },
            focus: function() {
                textarea.focus();
            }
        };
    }
    
    // Estrategia 3: Buscar en la pestaña activa
    const activeTab = document.querySelector('.tab-pane.active');
    if (activeTab) {
        textarea = activeTab.querySelector('#consulta-textarea, [name="consulta-textarea"]');
        if (textarea) {
            console.log('Textarea encontrado en pestaña activa');
            return {
                element: textarea,
                isSummernote: false,
                setValue: function(text) {
                    textarea.value = text;
                },
                getValue: function() {
                    return textarea.value;
                },
                focus: function() {
                    textarea.focus();
                }
            };
        }
    }
    
    // Estrategia 4: Cambiar a la pestaña de registro y luego buscar el editor
    try {
        const registroTabLink = document.querySelector('a[href="#activity"]');
        if (registroTabLink) {
            console.log('Intentando cambiar a pestaña de registro...');
            // Intentar activar la pestaña de registro
            if (typeof $ !== 'undefined') {
                $(registroTabLink).tab('show');
                
                // Esperar un momento para que la pestaña se active y luego buscar el editor
                return {
                    pending: true,
                    resolve: function(callback) {
                        setTimeout(() => {
                            console.log('Pestaña de registro activada, buscando editor...');
                            
                            // Verificar nuevamente el editor Summernote después del cambio de pestaña
                            if (typeof $ !== 'undefined' && $('#consulta-textarea').data('summernote')) {
                                console.log('Editor Summernote encontrado después de cambio de pestaña');
                                const editorWrapper = {
                                    element: document.getElementById('consulta-textarea'),
                                    isSummernote: true,
                                    setValue: function(text) {
                                        $('#consulta-textarea').summernote('code', text);
                                        document.getElementById('consulta-textarea').value = text;
                                        console.log('Texto insertado en editor Summernote después de cambio de pestaña');
                                    },
                                    getValue: function() {
                                        return $('#consulta-textarea').summernote('code');
                                    },
                                    focus: function() {
                                        $('#consulta-textarea').summernote('focus');
                                    }
                                };
                                callback(editorWrapper);
                            } else {
                                console.log('No se encontró un editor después del cambio de pestaña');
                                callback(null);
                            }
                        }, 500); // Esperar 500ms para que el cambio de pestaña se complete
                    }
                };
            }
        }
    } catch (e) {
        console.error('Error al intentar cambiar a la pestaña de registro:', e);
    }
    
    console.log('No se encontró el textarea ni editor');
    return null;
}

// Función para seleccionar un código cuando fallan los detalles
function selectCodeWithoutDetails(code, title) {
    try {
        console.log('Usando código sin detalles:', code, title);
        
        // Cerrar el modal de detalles
        $('#icd-details-modal').modal('hide');
        
        // Crear datos simplificados
        const simpleData = {
            code: code,
            title: title,
            description: "No se pudieron obtener detalles adicionales para este código.",
            uri: ""
        };
        
        // Intentar actualizar el textarea de consulta directamente
        const textareaResult = findConsultaTextarea();
        
        function insertSimpleDiagnosis(textareaWrapper) {
            if (textareaWrapper) {
                console.log('Editor/textarea encontrado, insertando diagnóstico simplificado...');
                
                // Formatear el diagnóstico simplificado
                let diagnosisText = '';
                
                if (textareaWrapper.isSummernote) {
                    // Formateo para Summernote (HTML)
                    diagnosisText = `<p><strong>[Diagnóstico ICD-11: ${code} - ${title}]</strong></p>
                    <p>No se pudieron obtener detalles adicionales para este código.</p>
                    <p>&nbsp;</p>`;
                } else {
                    // Formateo para textarea normal
                    diagnosisText = `[Diagnóstico ICD-11: ${code} - ${title}]\nNo se pudieron obtener detalles adicionales para este código.\n\n`;
                }
                
                // Obtener el contenido actual
                let currentContent = textareaWrapper.getValue() || '';
                
                // Insertar contenido
                if ((textareaWrapper.isSummernote && !currentContent.includes('[Diagnóstico ICD-11:')) || 
                    (!textareaWrapper.isSummernote && !currentContent.startsWith('[Diagnóstico ICD-11:'))) {
                    textareaWrapper.setValue(diagnosisText + currentContent);
                } else {
                    textareaWrapper.setValue(diagnosisText);
                }
                
                // Intentar enfocar
                try {
                    textareaWrapper.focus();
                } catch (e) {
                    console.error('Error al enfocar elemento después de insertar diagnóstico simplificado:', e);
                }
                
                console.log('Diagnóstico simplificado insertado correctamente en el editor/textarea');
                
                // Activar pestaña de registro
                try {
                    const registroTabLink = document.querySelector('a[href="#activity"]');
                    if (registroTabLink && typeof $ !== 'undefined') {
                        $(registroTabLink).tab('show');
                    }
                } catch (e) {
                    console.error('Error al cambiar a pestaña de registro:', e);
                }
                
                return true;
            } else {
                console.warn('No se encontró el editor/textarea para inserción simplificada');
                return false;
            }
        }
        
        // Si tenemos un resultado pendiente (cambio de pestaña), esperamos la resolución
        if (textareaResult && textareaResult.pending) {
            textareaResult.resolve(insertSimpleDiagnosis);
        } else {
            insertSimpleDiagnosis(textareaResult);
        }
        
        // Actualizar los campos de diagnóstico
        if (window.icd11Client && typeof window.icd11Client.dispatchCodeSelected === 'function') {
            window.icd11Client.dispatchCodeSelected(simpleData);
            
            // Mostrar mensaje de confirmación
            showAlert('success', `Diagnóstico seleccionado: ${code} - ${title}`);
        } else {
            // Como respaldo, también actualizar directamente los campos
            const codeField = document.getElementById('selected-code');
            const diagnosisField = document.getElementById('selected-diagnosis');
            
            if (codeField) codeField.value = code || '';
            if (diagnosisField) diagnosisField.value = title || '';
            
            showAlert('success', `Código ${code} seleccionado`);
        }
    } catch (err) {
        console.error('Error al seleccionar código sin detalles:', err);
        showAlert('danger', 'Error al seleccionar el código: ' + err.message);
    }
}

// Setup de navegación entre pestañas para facilitar uso de ICD-11
function setupTabNavigation() {
    try {
        // Encontrar todos los enlaces de navegación de pestañas
        const tabLinks = document.querySelectorAll('.nav-link');
        
        // Configurar manejador de eventos para cada enlace
        tabLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                // Identificar la pestaña actual y destino
                const currentTab = link.getAttribute('href');
                console.log('Cambio de pestaña a:', currentTab);
                
                // Si se está yendo a la pestaña de registro después de ICD, preparar la transición
                if (currentTab === '#activity' && document.querySelector('.nav-link.active')?.getAttribute('href') === '#icd') {
                    console.log('Transición de ICD a Registro, verificando textarea...');
                    
                    // Esperar a que cambie la pestaña y luego verificar el textarea
                    setTimeout(() => {
                        const textarea = findConsultaTextarea();
                        if (textarea) {
                            console.log('Textarea de consulta encontrado después del cambio de pestaña');
                        } else {
                            console.warn('No se encontró el textarea después del cambio de pestaña');
                        }
                    }, 300);
                }
            });
        });
        
        console.log('Configurada navegación entre pestañas para ICD-11');
    } catch (e) {
        console.error('Error al configurar navegación entre pestañas:', e);
    }
}

// Exponer las funciones principales como globales para acceso desde otros scripts
window.findConsultaTextarea = findConsultaTextarea;
window.selectCodeWithoutDetails = selectCodeWithoutDetails;
window.setupTabNavigation = setupTabNavigation;

// Inicializar cuando el documento está listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('ICD-11 Integration: Inicializando...');
    setupTabNavigation();
});
