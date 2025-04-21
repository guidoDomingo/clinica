/**
 * Archivo JavaScript para la funcionalidad de consultas médicas
 * Implementa la búsqueda de pacientes por documento o ficha y autocompletado de formularios
 */

// Cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Obtener referencias a los elementos del DOM
    const btnBuscarPersona = document.getElementById('btnBuscarPersona');
    const btnLimpiarPersona = document.getElementById('btnLimpiarPersona');
    const btnGuardarConsulta = document.getElementById('btnGuardarConsulta');
    const btnSubirArchivos = document.getElementById('btnSubirArchivos');
    
    // Agregar event listeners a los botones
    if (btnBuscarPersona) {
        btnBuscarPersona.addEventListener('click', buscarPersona);
    }
    
    if (btnLimpiarPersona) {
        btnLimpiarPersona.addEventListener('click', limpiarFormularioPersona);
    }
    
    if (btnGuardarConsulta) {
        btnGuardarConsulta.addEventListener('click', guardarConsulta);
    }
    
    if (btnSubirArchivos) {
        btnSubirArchivos.addEventListener('click', subirArchivos);
    }
});

/**
 * Función para buscar una persona por documento o ficha
 * y autocompletar los campos del formulario
 */
function buscarPersona() {
    // Obtener los valores de documento y ficha
    const documento = document.getElementById('txtdocumento').value.trim();
    const ficha = document.getElementById('txtficha').value.trim();
    
    // Validar que al menos uno de los campos tenga valor
    if (documento === '' && ficha === '') {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "Debe ingresar un documento o ficha para buscar",
            showConfirmButton: false,
            timer: 1500
        });
        return;
    }
    
    // Limpiar formulario antes de buscar
    limpiarFormularioConsulta();
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('documento', documento);
    formData.append('nro_ficha', ficha);
    formData.append('operacion', 'buscarparam');
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/persona.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success') {
                // Autocompletar los campos con los datos recibidos
                const persona = response.data;
                document.getElementById('paciente').value = persona.nombres + ' ' + persona.apellidos;
                document.getElementById('idPersona').value = persona.id_persona;
                
                // Actualizar información en el panel lateral
                document.getElementById('profile-username').textContent = persona.nombres + ' ' + persona.apellidos;
                document.getElementById('profile-ci').textContent = 'CI: ' + persona.documento;
                
                // Establecer el ID de persona para la subida de archivos
                document.getElementById('id_persona_file').value = persona.id_persona;
                
                // Obtener información adicional del paciente (cuota, consultas, etc.)
                obtenerResumenConsulta(persona.id_persona);
                obtenerCuota(persona.id_persona);
                
                // Cargar la última consulta del paciente si existe
                cargarUltimaConsulta(persona.id_persona);
                
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Paciente encontrado",
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                Swal.fire({
                    position: "center",
                    icon: "warning",
                    title: response.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al realizar la búsqueda",
                text: error,
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

/**
 * Función para obtener el resumen de consultas del paciente
 * @param {number} idPersona - ID de la persona
 */
function obtenerResumenConsulta(idPersona) {
    const formData = new FormData();
    formData.append('id_persona', idPersona);
    formData.append('operacion', 'resumenConsulta');
    
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response) {
                // Actualizar información de consultas
                const cantidadConsultas = response.cantidad_consultas || '0';
                const ultimaConsulta = response.maxima_fecha_registro || 'Sin consultas';
                
                // Mostrar la información en la interfaz
                document.getElementById('txtCantConsulta').textContent = cantidadConsultas;
                
                // Convertir el elemento de última consulta en un enlace clickeable
                const ultConsultaElement = document.getElementById('txtUltConsulta');
                ultConsultaElement.textContent = ultimaConsulta;
                
                // Si hay consultas, hacer que el elemento sea clickeable
                if (cantidadConsultas > 0) {
                    // Agregar clase para indicar que es clickeable
                    ultConsultaElement.classList.add('consulta-link');
                    
                    // Eliminar eventos previos si existen
                    ultConsultaElement.removeEventListener('click', mostrarHistorialConsultas);
                    
                    // Agregar evento de clic para mostrar todas las consultas
                    ultConsultaElement.addEventListener('click', function() {
                        mostrarHistorialConsultas(idPersona);
                    });
                } else {
                    // Si no hay consultas, quitar la clase y el evento
                    ultConsultaElement.classList.remove('consulta-link');
                }
            } else {
                document.getElementById('txtCantConsulta').textContent = '0';
                document.getElementById('txtUltConsulta').textContent = 'Sin consultas';
                document.getElementById('txtUltConsulta').classList.remove('consulta-link');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener resumen de consulta:", error);
            document.getElementById('txtCantConsulta').textContent = '0';
            document.getElementById('txtUltConsulta').textContent = 'Sin consultas';
            document.getElementById('txtUltConsulta').classList.remove('consulta-link');
        }
    });
}

/**
 * Función para obtener la cuota del paciente
 * @param {number} idPersona - ID de la persona
 */
function obtenerCuota(idPersona) {
    const formData = new FormData();
    formData.append('id_persona', idPersona);
    formData.append('operacion', 'mega');
    
    $.ajax({
        type: 'POST',
        url: 'ajax/archivos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            const cuotaValorElement = document.getElementById('cuota-valor');
            if (response && response.cuota) {
                cuotaValorElement.textContent = response.cuota;
            } else {
                cuotaValorElement.textContent = '0';
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener cuota:", error);
        }
    });
}

/**
 * Función para limpiar el formulario de persona
 */
function limpiarFormularioPersona() {
    // Limpiar campos de búsqueda
    document.getElementById('txtdocumento').value = '';
    document.getElementById('txtficha').value = '';
    document.getElementById('paciente').value = '';
    document.getElementById('idPersona').value = '';
    
    // Limpiar panel lateral
    document.getElementById('profile-username').textContent = '';
    document.getElementById('profile-ci').textContent = '';
    document.getElementById('cuota-valor').textContent = '0';
    document.getElementById('txtCantConsulta').textContent = '0';
    document.getElementById('txtUltConsulta').textContent = 'Sin consultas';
    
    // Limpiar ID para subida de archivos
    document.getElementById('id_persona_file').value = '';
    
    // Limpiar el formulario de consulta
    limpiarFormularioConsulta();
}

/**
 * Función para guardar o actualizar la consulta
 */
function guardarConsulta() {
    // Verificar que se haya seleccionado un paciente
    const idPersona = document.getElementById('idPersona').value;
    if (!idPersona) {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "Debe seleccionar un paciente",
            showConfirmButton: false,
            timer: 1500
        });
        return;
    }
    
    // Enviar el formulario
    const formData = new FormData(document.getElementById('tblConsulta'));
    
    // Verificar si es una actualización o una nueva consulta
    const idConsulta = document.getElementById('id_consulta') ? document.getElementById('id_consulta').value : '';
    const esActualizacion = idConsulta !== '';
    
    $.ajax({
        type: 'POST',
        url: 'ajax/guardar-consulta.ajax.php',
        data: formData,
        dataType: "text",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response === "ok" || response === "actualizado") {
                const mensaje = esActualizacion ? "Consulta actualizada correctamente" : "Consulta guardada correctamente";
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: mensaje,
                    showConfirmButton: false,
                    timer: 1500
                });
                
                // Actualizar información después de guardar
                obtenerResumenConsulta(idPersona);
                
                // Si fue una actualización, limpiar el formulario para una nueva consulta
                if (esActualizacion) {
                    limpiarFormularioConsulta();
                }
            } else {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error al guardar la consulta",
                    text: response,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al guardar la consulta",
                text: error,
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

/**
 * Función para mostrar el historial completo de consultas de un paciente
 * @param {number} idPersona - ID de la persona
 */
function mostrarHistorialConsultas(idPersona) {
    // Verificar que se tenga un ID de persona válido
    if (!idPersona) {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "No se ha seleccionado un paciente",
            showConfirmButton: false,
            timer: 1500
        });
        return;
    }
    
    // Mostrar indicador de carga
    const timelineContainer = document.getElementById('timeline');
    timelineContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Cargando historial de consultas...</p></div>';
    
    // Activar la pestaña de Timeline
    $('a[href="#timeline"]').tab('show');
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_persona', idPersona);
    formData.append('operacion', 'historialConsultas');
    
    // Realizar petición AJAX para obtener el historial de consultas
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response && response.length > 0) {
                // Construir el timeline con las consultas
                let timelineHTML = '<div class="timeline timeline-inverse">';
                
                response.forEach(consulta => {
                    const fecha = new Date(consulta.fecha_registro);
                    const fechaFormateada = fecha.toLocaleDateString('es-ES');
                    
                    timelineHTML += `
                    <div class="time-label">
                        <span class="bg-primary">${fechaFormateada}</span>
                    </div>
                    <div>
                        <i class="fas fa-stethoscope bg-info"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> ${fecha.toLocaleTimeString('es-ES')}</span>
                            <h3 class="timeline-header"><a href="#">Consulta médica</a></h3>
                            <div class="timeline-body">
                                <strong>Motivo:</strong> ${consulta.motivo || 'No especificado'}<br>
                                <strong>Diagnóstico:</strong> ${consulta.diagnostico || 'No especificado'}
                            </div>
                            <div class="timeline-footer">
                                <button class="btn btn-info btn-sm ver-detalle-consulta" data-id="${consulta.id_consulta}">Ver detalles</button>
                            </div>
                        </div>
                    </div>
                    `;
                });
                
                timelineHTML += '</div>';
                timelineContainer.innerHTML = timelineHTML;
                
                // Agregar eventos a los botones de ver detalle
                document.querySelectorAll('.ver-detalle-consulta').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idConsulta = this.getAttribute('data-id');
                        verDetalleConsulta(idConsulta);
                    });
                });
                
            } else {
                // Mostrar mensaje si no hay consultas
                timelineContainer.innerHTML = '<div class="alert alert-info">No hay consultas registradas para este paciente.</div>';
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener historial de consultas:", error);
            timelineContainer.innerHTML = `<div class="alert alert-danger">Error al cargar el historial de consultas: ${error}</div>`;
        }
    });
}

/**
 * Función para ver el detalle completo de una consulta
 * @param {number} idConsulta - ID de la consulta
 */
function verDetalleConsulta(idConsulta) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('operacion', 'detalleConsulta');
    
    // Realizar petición AJAX para obtener el detalle de la consulta
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response) {
                // Construir el contenido del modal con los detalles de la consulta
                // Mostrar en consola para depuración
                console.log('Datos de consulta recibidos:', response);
                
                // Obtener los archivos asociados a esta consulta
                obtenerArchivosConsulta(response.id_consulta, function(archivos) {
                    // Construir la sección de archivos
                    let archivosHTML = '';
                    if (archivos && archivos.length > 0) {
                        archivosHTML = `
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Archivos adjuntos:</strong></p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Tipo</th>
                                                <th>Tamaño</th>
                                                <th>Fecha</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                        `;
                        
                        archivos.forEach(archivo => {
                            const fecha = new Date(archivo.fecha_creacion).toLocaleDateString('es-ES');
                            archivosHTML += `
                            <tr>
                                <td>${archivo.nombre_archivo}</td>
                                <td>${archivo.tipo_archivo}</td>
                                <td>${archivo.tamano_mb} MB</td>
                                <td>${fecha}</td>
                                <td>
                                    <a href="${archivo.ruta_archivo}" class="btn btn-sm btn-info" target="_blank" download>
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                </td>
                            </tr>
                            `;
                        });
                        
                        archivosHTML += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        `;
                    } else {
                        archivosHTML = `
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Archivos adjuntos:</strong></p>
                                <div class="alert alert-info">No hay archivos adjuntos para esta consulta.</div>
                            </div>
                        </div>
                        `;
                    }
                    
                    let modalContent = `
                    <div class="modal-header">
                        <h5 class="modal-title">Detalle de Consulta</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Fecha:</strong> ${new Date(response.fecha_registro).toLocaleDateString('es-ES')}</p>
                                <p><strong>Motivo:</strong> ${response.motivo || 'No especificado'} - ${response.txtmotivo || ''}</p>
                                <p><strong>Diagnóstico:</strong> ${response.diagnostico || 'No especificado'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Visión OD:</strong> ${response.visionod || 'No especificado'}</p>
                                <p><strong>Visión OI:</strong> ${response.visionoi || 'No especificado'}</p>
                                <p><strong>Tensión OD:</strong> ${response.tensionod || 'No especificado'}</p>
                                <p><strong>Tensión OI:</strong> ${response.tensionoi || 'No especificado'}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Observaciones:</strong></p>
                                <div class="p-2 border rounded">${response.observaciones || 'Sin observaciones'}</div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Receta:</strong></p>
                                <div class="p-2 border rounded">${response.receta_textarea || 'Sin receta'}</div>
                            </div>
                        </div>
                        ${archivosHTML}
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>WhatsApp:</strong> ${response.whatsapptxt || 'No especificado'}</p>
                                <p><strong>Email:</strong> ${response.email || 'No especificado'}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Próxima consulta:</strong> ${response.proximaconsulta ? new Date(response.proximaconsulta).toLocaleDateString('es-ES') : 'No programada'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary cargar-consulta" data-id="${response.id_consulta}">Cargar en formulario</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                    `;
                    
                    // Crear o actualizar el modal
                    let modalElement = document.getElementById('detalleConsultaModal');
                    if (!modalElement) {
                        modalElement = document.createElement('div');
                        modalElement.id = 'detalleConsultaModal';
                        modalElement.className = 'modal fade';
                        modalElement.setAttribute('tabindex', '-1');
                        modalElement.setAttribute('role', 'dialog');
                        modalElement.setAttribute('aria-labelledby', 'detalleConsultaModalLabel');
                        modalElement.setAttribute('aria-hidden', 'true');
                        modalElement.innerHTML = `<div class="modal-dialog modal-lg" role="document"><div class="modal-content">${modalContent}</div></div>`;
                        document.body.appendChild(modalElement);
                    } else {
                        modalElement.querySelector('.modal-content').innerHTML = modalContent;
                    }
                    
                    // Mostrar el modal
                    $('#detalleConsultaModal').modal('show');
                    
                    // Agregar evento al botón de cargar consulta
                    document.querySelector('.cargar-consulta').addEventListener('click', function() {
                        cargarConsultaEnFormulario(response, archivos);
                        $('#detalleConsultaModal').modal('hide');
                    });
                });
            } else {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "No se encontró la consulta",
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener detalle de consulta:", error);
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al obtener detalle de consulta",
                text: error,
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

/**
 * Función para cargar la última consulta del paciente
 * @param {number} idPersona - ID de la persona
 */
function cargarUltimaConsulta(idPersona) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_persona', idPersona);
    formData.append('operacion', 'historialConsultas');
    
    // Realizar petición AJAX para obtener el historial de consultas
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response && response.length > 0) {
                // Ordenar las consultas por fecha (la más reciente primero)
                response.sort((a, b) => new Date(b.fecha_registro) - new Date(a.fecha_registro));
                
                // Obtener la consulta más reciente
                const ultimaConsulta = response[0];
                
                // Preguntar al usuario si desea cargar la última consulta
                Swal.fire({
                    title: '¿Cargar última consulta?',
                    text: `Se encontró una consulta del ${new Date(ultimaConsulta.fecha_registro).toLocaleDateString('es-ES')}. ¿Desea cargarla en el formulario?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, cargar',
                    cancelButtonText: 'No, consulta nueva'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Obtener el detalle completo de la consulta
                        obtenerYCargarConsulta(ultimaConsulta.id_consulta);
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener historial de consultas:", error);
        }
    });
}

/**
 * Función para obtener y cargar una consulta específica
 * @param {number} idConsulta - ID de la consulta
 */
function obtenerYCargarConsulta(idConsulta) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('operacion', 'detalleConsulta');
    
    // Realizar petición AJAX para obtener el detalle de la consulta
    $.ajax({
        type: 'POST',
        url: 'ajax/consultas.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response) {
                cargarConsultaEnFormulario(response);
            } else {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "No se encontró la consulta",
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener detalle de consulta:", error);
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al obtener detalle de consulta",
                text: error,
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

/**
 * Función para obtener los archivos asociados a una consulta
 * @param {number} idConsulta - ID de la consulta
 * @param {function} callback - Función de callback que recibe los archivos
 */
function obtenerArchivosConsulta(idConsulta, callback) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('id_consulta', idConsulta);
    formData.append('operacion', 'archivosPorConsulta');
    
    // Realizar petición AJAX para obtener los archivos
    $.ajax({
        type: 'POST',
        url: 'ajax/archivos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success' && response.data) {
                callback(response.data);
            } else {
                callback([]);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener archivos de la consulta:", error);
            callback([]);
        }
    });
}

/**
 * Función para cargar los datos de una consulta en el formulario
 * @param {Object} consulta - Datos de la consulta
 * @param {Array} archivos - Archivos asociados a la consulta (opcional)
 */
function cargarConsultaEnFormulario(consulta, archivos) {
    // Limpiar el formulario primero
    limpiarFormularioConsulta();
    
    // Crear un campo oculto para el ID de la consulta si no existe
    let idConsultaInput = document.getElementById('id_consulta');
    if (!idConsultaInput) {
        idConsultaInput = document.createElement('input');
        idConsultaInput.type = 'hidden';
        idConsultaInput.id = 'id_consulta';
        idConsultaInput.name = 'id_consulta';
        document.getElementById('tblConsulta').appendChild(idConsultaInput);
    }
    idConsultaInput.value = consulta.id_consulta;
    
    // Mapear los campos de la consulta a los campos del formulario
    const camposACargar = {
        'txtmotivo': 'txtmotivo',
        'visionod': 'visionod',
        'visionoi': 'visionoi',
        'tensionod': 'tensionod',
        'tensionoi': 'tensionoi',
        'diagnostico': 'consulta-textarea',
        'receta_textarea': 'receta-textarea',
        'observaciones': 'txtnota',
        'proximaconsulta': 'proximaconsulta',
        'whatsapptxt': 'whatsapptxt',
        'email': 'email'
    };
    
    // Cargar cada campo
    for (const [campoConsulta, campoFormulario] of Object.entries(camposACargar)) {
        const elemento = document.getElementById(campoFormulario);
        if (elemento && consulta[campoConsulta] !== undefined) {
            elemento.value = consulta[campoConsulta];
        }
    }
    
    // Seleccionar el motivo común correcto en el selector
    const selectMotivosComunes = document.getElementById('motivoscomunes');
    if (selectMotivosComunes && consulta.motivo) {
        // Primero asegurarse de que los motivos comunes estén cargados
        cargarMotivosComunes();
        
        // Esperar un momento para que se carguen las opciones
        setTimeout(() => {
            // Buscar la opción que coincida con el motivo de la consulta
            for (let i = 0; i < selectMotivosComunes.options.length; i++) {
                if (selectMotivosComunes.options[i].value === consulta.motivo) {
                    selectMotivosComunes.selectedIndex = i;
                    break;
                }
            }
            // Disparar evento change para actualizar cualquier listener
            const event = new Event('change');
            selectMotivosComunes.dispatchEvent(event);
        }, 500);
    }
    
    // Seleccionar el preformato correcto en el selector de consulta
    const selectFormatoConsulta = document.getElementById('formatoConsulta');
    if (selectFormatoConsulta && consulta.id_preformato_consulta) {
        // Verificar si los preformatos ya están cargados
        if (selectFormatoConsulta.options.length <= 1) {
            // Si no hay opciones cargadas, cargar los preformatos primero
            cargarPreformatosConsulta();
        }
        
        // Esperar un momento para que se carguen las opciones
        setTimeout(() => {
            // Buscar la opción que coincida con el preformato de la consulta
            let preformatoEncontrado = false;
            for (let i = 0; i < selectFormatoConsulta.options.length; i++) {
                if (selectFormatoConsulta.options[i].value === consulta.id_preformato_consulta) {
                    selectFormatoConsulta.selectedIndex = i;
                    preformatoEncontrado = true;
                    break;
                }
            }
            
            // Si se encontró el preformato, disparar evento change para aplicar el contenido
            if (preformatoEncontrado) {
                console.log('Preformato de consulta encontrado y seleccionado:', consulta.id_preformato_consulta);
                const event = new Event('change');
                selectFormatoConsulta.dispatchEvent(event);
            } else {
                console.log('No se encontró el preformato de consulta con ID:', consulta.id_preformato_consulta);
            }
        }, 800); // Aumentamos el tiempo de espera para asegurar que las opciones estén cargadas
    }
    
    // Seleccionar el preformato correcto en el selector de receta
    const selectFormatoReceta = document.getElementById('formatoreceta');
    if (selectFormatoReceta && consulta.id_preformato_receta) {
        // Verificar si los preformatos ya están cargados
        if (selectFormatoReceta.options.length <= 1) {
            // Si no hay opciones cargadas, cargar los preformatos primero
            cargarPreformatosReceta();
        }
        
        // Esperar un momento para que se carguen las opciones
        setTimeout(() => {
            // Buscar la opción que coincida con el preformato de la receta
            let preformatoEncontrado = false;
            for (let i = 0; i < selectFormatoReceta.options.length; i++) {
                if (selectFormatoReceta.options[i].value === consulta.id_preformato_receta) {
                    selectFormatoReceta.selectedIndex = i;
                    preformatoEncontrado = true;
                    break;
                }
            }
            
            // Si se encontró el preformato, disparar evento change para aplicar el contenido
            if (preformatoEncontrado) {
                console.log('Preformato de receta encontrado y seleccionado:', consulta.id_preformato_receta);
                const event = new Event('change');
                selectFormatoReceta.dispatchEvent(event);
            } else {
                console.log('No se encontró el preformato de receta con ID:', consulta.id_preformato_receta);
            }
        }, 800); // Aumentamos el tiempo de espera para asegurar que las opciones estén cargadas
    }
    
    // Si tenemos archivos, mostrarlos en la sección de archivos
    if (archivos && archivos.length > 0) {
        mostrarArchivosEnFormulario(archivos);
    } else if (consulta.id_consulta) {
        // Si no tenemos archivos pero sí tenemos ID de consulta, intentar obtenerlos
        obtenerArchivosConsulta(consulta.id_consulta, function(archivosObtenidos) {
            if (archivosObtenidos && archivosObtenidos.length > 0) {
                mostrarArchivosEnFormulario(archivosObtenidos);
            }
        });
    }
    
    // Notificar al usuario
    Swal.fire({
        position: "center",
        icon: "success",
        title: "Consulta cargada correctamente",
        text: "Puede modificar los datos y guardar para actualizar la consulta",
        showConfirmButton: false,
        timer: 2000
    });
}

/**
 * Función para mostrar los archivos en el formulario
 * @param {Array} archivos - Archivos a mostrar
 */
function mostrarArchivosEnFormulario(archivos) {
    const previewContainer = document.getElementById('filePreviewContainer');
    if (!previewContainer) return;
    
    // Limpiar previsualizaciones anteriores
    previewContainer.innerHTML = '';
    
    // Mostrar cada archivo
    archivos.forEach(archivo => {
        const preview = document.createElement('div');
        preview.className = 'file-preview';
        
        // Crear elemento para mostrar información del archivo
        const fileInfo = document.createElement('div');
        fileInfo.className = 'file-info';
        fileInfo.textContent = archivo.nombre_archivo;
        
        // Determinar el tipo de archivo y mostrar el icono correspondiente
        const fileTypeIcon = document.createElement('div');
        fileTypeIcon.className = 'file-type-icon';
        
        let iconClass = 'fas ';
        if (archivo.tipo_archivo.includes('image')) {
            iconClass += 'fa-image img-icon';
        } else if (archivo.tipo_archivo.includes('pdf')) {
            iconClass += 'fa-file-pdf pdf-icon';
        } else if (archivo.tipo_archivo.includes('word') || archivo.tipo_archivo.includes('document')) {
            iconClass += 'fa-file-word doc-icon';
        } else if (archivo.tipo_archivo.includes('excel') || archivo.tipo_archivo.includes('sheet')) {
            iconClass += 'fa-file-excel xls-icon';
        } else {
            iconClass += 'fa-file file-icon';
        }
        
        const icon = document.createElement('i');
        icon.className = iconClass;
        fileTypeIcon.appendChild(icon);
        
        // Crear botón de descarga
        const downloadBtn = document.createElement('a');
        downloadBtn.href = archivo.ruta_archivo;
        downloadBtn.className = 'btn btn-sm btn-info download-btn';
        downloadBtn.setAttribute('download', '');
        downloadBtn.setAttribute('target', '_blank');
        downloadBtn.innerHTML = '<i class="fas fa-download"></i>';
        downloadBtn.title = 'Descargar archivo';
        
        // Agregar elementos al contenedor de previsualización
        preview.appendChild(fileTypeIcon);
        preview.appendChild(fileInfo);
        preview.appendChild(downloadBtn);
        
        // Agregar al contenedor principal
        previewContainer.appendChild(preview);
    });
    
    // Mostrar mensaje informativo
    const infoMsg = document.createElement('div');
    infoMsg.className = 'alert alert-info mt-2';
    infoMsg.innerHTML = `<i class="fas fa-info-circle"></i> Se han cargado ${archivos.length} archivo(s) de la consulta anterior.`;
    previewContainer.appendChild(infoMsg);
}

/**
 * Función para limpiar el formulario de consulta
 */
function limpiarFormularioConsulta() {
    // Limpiar campos del formulario de consulta
    const formConsulta = document.getElementById('tblConsulta');
    
    // Limpiar campos de texto y selects, excepto los de búsqueda de paciente
    const camposALimpiar = [
        'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
        'consulta-textarea', 'receta-textarea', 'txtnota', 'proximaconsulta',
        'whatsapptxt', 'email'
    ];
    
    camposALimpiar.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (elemento) {
            elemento.value = '';
        }
    });
    
    // Resetear selects a su primera opción
    const selects = ['motivoscomunes', 'formatoConsulta', 'formatoreceta'];
    selects.forEach(select => {
        const elemento = document.getElementById(select);
        if (elemento && elemento.options.length > 0) {
            elemento.selectedIndex = 0;
        }
    });
    
    // Limpiar la sección de archivos
    const previewContainer = document.getElementById('filePreviewContainer');
    if (previewContainer) {
        previewContainer.innerHTML = '';
    }
    
    // Eliminar el campo id_consulta si existe
    const idConsultaInput = document.getElementById('id_consulta');
    if (idConsultaInput) {
        idConsultaInput.remove();
    }
}

/**
 * Función para inicializar la interfaz de carga de archivos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar la funcionalidad de arrastrar y soltar
    initFileUpload();
    
    // Sincronizar el id_persona con el id_persona_file cuando cambia
    const idPersonaInput = document.getElementById('idPersona');
    if (idPersonaInput) {
        idPersonaInput.addEventListener('change', function() {
            document.getElementById('id_persona_file').value = this.value;
        });
    }
});

/**
 * Inicializa la funcionalidad de arrastrar y soltar para la carga de archivos
 */
function initFileUpload() {
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('files');
    const previewContainer = document.getElementById('filePreviewContainer');
    
    if (!dropArea || !fileInput || !previewContainer) return;
    
    // Prevenir comportamiento predeterminado de arrastrar y soltar
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    // Resaltar área de soltar cuando se arrastra un archivo sobre ella
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropArea.classList.add('highlight');
    }
    
    function unhighlight() {
        dropArea.classList.remove('highlight');
    }
    
    // Manejar archivos soltados
    dropArea.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }
    
    // Manejar archivos seleccionados mediante el input
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });
    
    function handleFiles(files) {
        previewContainer.innerHTML = ''; // Limpiar previsualizaciones anteriores
        
        if (files.length > 0) {
            Array.from(files).forEach(file => {
                previewFile(file);
            });
        }
    }
    
    function previewFile(file) {
        const reader = new FileReader();
        const preview = document.createElement('div');
        preview.className = 'file-preview';
        
        // Crear elemento para mostrar información del archivo
        const fileInfo = document.createElement('div');
        fileInfo.className = 'file-info';
        fileInfo.textContent = file.name;
        
        // Determinar el tipo de archivo y mostrar el icono correspondiente
        const fileTypeIcon = document.createElement('div');
        fileTypeIcon.className = 'file-type-icon';
        
        let iconClass = 'fas ';
        if (file.type.match('image.*')) {
            iconClass += 'fa-image img-icon';
            reader.onloadend = function() {
                const img = document.createElement('img');
                img.src = reader.result;
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        } else if (file.type === 'application/pdf') {
            iconClass += 'fa-file-pdf pdf-icon';
            fileTypeIcon.innerHTML = '<i class="' + iconClass + '"></i>';
            preview.appendChild(fileTypeIcon);
        } else if (file.type.includes('word') || file.type === 'application/msword') {
            iconClass += 'fa-file-word doc-icon';
            fileTypeIcon.innerHTML = '<i class="' + iconClass + '"></i>';
            preview.appendChild(fileTypeIcon);
        } else if (file.type.includes('excel') || file.type === 'application/vnd.ms-excel') {
            iconClass += 'fa-file-excel xls-icon';
            fileTypeIcon.innerHTML = '<i class="' + iconClass + '"></i>';
            preview.appendChild(fileTypeIcon);
        } else if (file.type.includes('powerpoint') || file.type === 'application/vnd.ms-powerpoint') {
            iconClass += 'fa-file-powerpoint ppt-icon';
            fileTypeIcon.innerHTML = '<i class="' + iconClass + '"></i>';
            preview.appendChild(fileTypeIcon);
        } else {
            iconClass += 'fa-file txt-icon';
            fileTypeIcon.innerHTML = '<i class="' + iconClass + '"></i>';
            preview.appendChild(fileTypeIcon);
        }
        
        preview.appendChild(fileInfo);
        previewContainer.appendChild(preview);
    }
}

/**
 * Función para subir archivos
 */
function subirArchivos() {
    // Verificar que se haya seleccionado un paciente
    const idPersona = document.getElementById('id_persona_file').value;
    if (!idPersona) {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "Debe seleccionar un paciente",
            showConfirmButton: false,
            timer: 1500
        });
        return;
    }
    
    // Verificar que se hayan seleccionado archivos
    const files = document.getElementById('files').files;
    if (files.length === 0) {
        Swal.fire({
            position: "center",
            icon: "warning",
            title: "Debe seleccionar al menos un archivo",
            showConfirmButton: false,
            timer: 1500
        });
        return;
    }
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Subiendo archivos...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Enviar el formulario
    const formData = new FormData(document.getElementById('uploadForm'));
    
    // Añadir el ID de la consulta actual si existe
    const idConsulta = document.getElementById('id_consulta') ? document.getElementById('id_consulta').value : null;
    if (idConsulta) {
        formData.append('id_consulta', idConsulta);
    }
    
    $.ajax({
        type: 'POST',
        url: 'ajax/upload.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.close();
            
            if (response.status === "success") {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Archivos subidos correctamente",
                    showConfirmButton: false,
                    timer: 1500
                });
                
                // Limpiar el formulario de archivos
                document.getElementById('files').value = '';
                
                // Obtener el ID de la consulta actual si existe
                const idConsulta = document.getElementById('id_consulta') ? document.getElementById('id_consulta').value : null;
                
                if (idConsulta) {
                    // Si hay una consulta activa, obtener y mostrar sus archivos
                    obtenerArchivosConsulta(idConsulta, function(archivos) {
                        if (archivos && archivos.length > 0) {
                            mostrarArchivosEnFormulario(archivos);
                        }
                    });
                } else {
                    // Si no hay consulta activa, mostrar los archivos recién subidos
                    const archivosSubidos = [];
                    response.files.forEach(file => {
                        archivosSubidos.push({
                            nombre_archivo: file.name,
                            tipo_archivo: file.type,
                            tamano_mb: (file.size / (1024 * 1024)).toFixed(2),
                            fecha_creacion: new Date().toISOString(),
                            ruta_archivo: file.path
                        });
                    });
                    mostrarArchivosEnFormulario(archivosSubidos);
                }
            } else {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error al subir los archivos",
                    text: response.message,
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error al subir los archivos",
                text: error,
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}