/**
 * Archivo JavaScript para la gestión de preformatos
 * en el sistema de consultas médicas
 */

// Cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes
    inicializarComponentes();
    
    // Cargar datos iniciales
    cargarPreformatos();
    
    // Configurar eventos
    configurarEventos();
});

/**
 * Inicializa los componentes de la página
 */
function inicializarComponentes() {
    // Inicializar editor de texto enriquecido Summernote si existe
    if (document.getElementById('obs-preformato')) {
        // Configuración e inicialización de Summernote (Editor de AdminLTE)
        $('#obs-preformato').summernote({
            placeholder: 'Escriba aquí el contenido del preformato...',
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear', 'strikethrough', 'superscript', 'subscript']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']],
            ],
            fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana'],
            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '24', '36', '48', '64', '82', '150']
        });
    }
    
    // Cargar usuarios para el selector de propietario
    cargarUsuarios();
    
    // Inicializar selectores
    const selectAplicarA = document.getElementById('aplicar-a');
    if (selectAplicarA) {
        selectAplicarA.addEventListener('change', function() {
            cargarPreformatosPorTipo(this.value);
        });
    }
    
    // Inicializar botones
    const btnLimpiar = document.getElementById('btn-limpiar-preformato');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', limpiarFormulario);
    }
}

/**
 * Configura los eventos de la página
 */
function configurarEventos() {
    // Evento para guardar preformato
    const formPreformato = document.getElementById('form-preformato-textarea');
    if (formPreformato) {
        formPreformato.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarPreformato();
        });
    }
    
    // Delegación de eventos para botones de editar, eliminar y ver
    const tbodyPreformatos = document.getElementById('tbody-preformatos');
    if (tbodyPreformatos) {
        tbodyPreformatos.addEventListener('click', function(e) {
            const target = e.target;
            
            // Botón editar
            if (target.classList.contains('btn-editar') || target.closest('.btn-editar')) {
                const btn = target.classList.contains('btn-editar') ? target : target.closest('.btn-editar');
                const idPreformato = btn.getAttribute('data-id');
                editarPreformato(idPreformato);
            }
            
            // Botón eliminar
            if (target.classList.contains('btn-eliminar') || target.closest('.btn-eliminar')) {
                const btn = target.classList.contains('btn-eliminar') ? target : target.closest('.btn-eliminar');
                const idPreformato = btn.getAttribute('data-id');
                eliminarPreformato(idPreformato);
            }
            
            // Botón ver
            if (target.classList.contains('btn-ver') || target.closest('.btn-ver')) {
                const btn = target.classList.contains('btn-ver') ? target : target.closest('.btn-ver');
                const idPreformato = btn.getAttribute('data-id');
                verPreformato(idPreformato);
            }
        });
    }
    
    // Inicializar el campo de tipo si existe
    const selectTipo = document.getElementById('tipo-preformato');
    if (selectTipo) {
        selectTipo.addEventListener('change', function() {
            // Aquí se podría implementar alguna lógica específica para el tipo seleccionado
        });
    }
}

/**
 * Carga los preformatos según el tipo seleccionado
 */
function cargarPreformatosPorTipo(tipo) {
    if (!tipo || tipo === '') return;
    
    const tbodyPreformatos = document.getElementById('tbody-preformatos');
    if (!tbodyPreformatos) return;
    
    // Limpiar tabla
    tbodyPreformatos.innerHTML = '<tr><td colspan="3" class="text-center">Cargando...</td></tr>';
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getPreformatos' + tipo.charAt(0).toUpperCase() + tipo.slice(1));
    
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
                // Limpiar tabla
                tbodyPreformatos.innerHTML = '';
                
                // Agregar filas a la tabla
                response.data.forEach(function(preformato, index) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${preformato.nombre}</td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-info btn-sm btn-editar" data-id="${preformato.id_preformato}" title="Editar preformato">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm btn-eliminar" data-id="${preformato.id_preformato}" title="Eliminar preformato">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn btn-warning btn-sm btn-ver" data-id="${preformato.id_preformato}" title="Ver preformato">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    `;
                    tbodyPreformatos.appendChild(row);
                });
            } else {
                tbodyPreformatos.innerHTML = '<tr><td colspan="3" class="text-center">No hay preformatos disponibles</td></tr>';
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar preformatos:", error);
            tbodyPreformatos.innerHTML = '<tr><td colspan="3" class="text-center">Error al cargar preformatos</td></tr>';
        }
    });
}

/**
 * Carga todos los preformatos disponibles
 */
function cargarPreformatos() {
    const selectAplicarA = document.getElementById('aplicar-a');
    if (selectAplicarA && selectAplicarA.value !== '') {
        cargarPreformatosPorTipo(selectAplicarA.value);
    }
}

/**
 * Guarda un nuevo preformato o actualiza uno existente
 */
function guardarPreformato() {
    const idPreformato = document.getElementById('id-preformato').value;
    const propietario = document.getElementById('propietario').value;
    const aplicarA = document.getElementById('aplicar-a').value;
    const titulo = document.getElementById('titulo-preformato').value;
    
    // Obtener contenido del editor si existe, o del textarea si no
    let observaciones = '';
    if ($('#obs-preformato').summernote) {
        observaciones = $('#obs-preformato').summernote('code');
    } else {
        observaciones = document.getElementById('obs-preformato').value;
    }
    
    // Validar campos obligatorios
    if (!propietario || !aplicarA || !titulo || !observaciones) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Todos los campos son obligatorios',
            confirmButtonText: 'Aceptar'
        });
        return;
    }
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', idPreformato ? 'actualizarPreformato' : 'crearPreformato');
    if (idPreformato) formData.append('id_preformato', idPreformato);
    formData.append('creado_por', propietario);
    formData.append('tipo', aplicarA);
    formData.append('nombre', titulo);
    formData.append('contenido', observaciones);
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Guardando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    limpiarFormulario();
                    cargarPreformatosPorTipo(aplicarA);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message,
                    confirmButtonText: 'Aceptar'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al guardar preformato:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al guardar el preformato',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

/**
 * Edita un preformato existente
 * @param {string} idPreformato ID del preformato a editar
 */
function editarPreformato(idPreformato) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getPreformatoById');
    formData.append('id_preformato', idPreformato);
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success' && response.data) {
                // Llenar formulario con datos del preformato
                document.getElementById('id-preformato').value = response.data.id_preformato;
                document.getElementById('propietario').value = response.data.creado_por;
                document.getElementById('aplicar-a').value = response.data.tipo;
                document.getElementById('titulo-preformato').value = response.data.nombre;
                
                // Actualizar el contenido en el editor si existe, o en el textarea si no
                if ($('#obs-preformato').summernote) {
                    $('#obs-preformato').summernote('code', response.data.contenido);
                } else {
                    document.getElementById('obs-preformato').value = response.data.contenido;
                }
                
                // Hacer scroll al formulario
                document.getElementById('form-preformato-textarea').scrollIntoView({ behavior: 'smooth' });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo obtener la información del preformato',
                    confirmButtonText: 'Aceptar'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener preformato:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al obtener la información del preformato',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

/**
 * Elimina un preformato
 * @param {string} idPreformato ID del preformato a eliminar
 */
function eliminarPreformato(idPreformato) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede revertir",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Crear objeto FormData para enviar los datos
            const formData = new FormData();
            formData.append('operacion', 'eliminarPreformato');
            formData.append('id_preformato', idPreformato);
            
            // Realizar petición AJAX
            $.ajax({
                type: 'POST',
                url: 'ajax/preformatos.ajax.php',
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            const aplicarA = document.getElementById('aplicar-a').value;
                            cargarPreformatosPorTipo(aplicarA);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al eliminar preformato:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al eliminar el preformato',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }
    });
}

/**
 * Limpia el formulario de preformatos
 */
function limpiarFormulario() {
    document.getElementById('id-preformato').value = '';
    document.getElementById('propietario').selectedIndex = 0;
    document.getElementById('aplicar-a').selectedIndex = 0;
    document.getElementById('titulo-preformato').value = '';
    
    // Limpiar el editor si existe
    if ($('#obs-preformato').summernote) {
        $('#obs-preformato').summernote('code', '');
    } else {
        document.getElementById('obs-preformato').value = '';
    }
}

/**
 * Carga los usuarios para el selector de propietario
 */
function cargarUsuarios() {
    const selectPropietario = document.getElementById('propietario');
    if (!selectPropietario) {
        console.error('No se encontró el elemento select de propietario');
        return;
    }
    
    console.log('Iniciando carga de usuarios...');
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getUsuarios');
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/usuarios.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Respuesta recibida:', response);
            if (response.status === 'success' && response.data && response.data.length > 0) {
                console.log('Usuarios obtenidos:', response.data.length);
                // Limpiar selector manteniendo la opción por defecto
                const defaultOption = selectPropietario.querySelector('option[value=""]');
                selectPropietario.innerHTML = '';
                if (defaultOption) {
                    selectPropietario.appendChild(defaultOption);
                }
                
                // Agregar opciones de usuarios
                response.data.forEach(function(usuario) {
                    const option = document.createElement('option');
                    option.value = usuario.id_usuario;
                    option.textContent = usuario.nombre + ' ' + usuario.apellido;
                    selectPropietario.appendChild(option);
                });
            } else {
                console.error('No se obtuvieron usuarios o la respuesta no tiene el formato esperado');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar usuarios:", error);
            console.error("Estado:", status);
            console.error("Respuesta:", xhr.responseText);
        }
    });
}

/**
 * Muestra un preformato en un modal
 * @param {string} idPreformato ID del preformato a mostrar
 */
function verPreformato(idPreformato) {
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getPreformatoById');
    formData.append('id_preformato', idPreformato);
    
    // Mostrar indicador de carga
    Swal.fire({
        title: 'Cargando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.close();
            if (response.status === 'success' && response.data) {
                // Mostrar datos en el modal
                document.getElementById('modal-titulo-preformato').textContent = response.data.nombre;
                document.getElementById('modal-contenido-preformato').innerHTML = response.data.contenido;
                
                // Mostrar modal
                $('#modalVerPreformato').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo obtener la información del preformato',
                    confirmButtonText: 'Aceptar'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            console.error("Error al obtener preformato:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al obtener la información del preformato',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}