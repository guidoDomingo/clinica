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
    
    // Inicializar Quill editor para preformatos en el modal si existe el contenedor
    window.editorPreformato = null;
    const editorContainer = document.getElementById('editor-preformato-container');
    if (editorContainer) {
        window.editorPreformato = new Quill('#editor-preformato-container', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'color': [] }, { 'background': [] }],
                    ['clean']
                ]
            }
        });
    }
    
    // Cargar médicos para el selector de propietario
    cargarMedicos();
    
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
                        <td>${preformato.tipo}</td>
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
 * Carga los preformatos en la tabla al inicializar la página
 */
function cargarPreformatos() {
    const tbodyPreformatos = document.getElementById('tbody-preformatos');
    const tablaPreformatos = document.getElementById('tabla-preformatos');
    
    if (!tbodyPreformatos || !tablaPreformatos) return;
    
    // Mostrar indicador de carga
    tbodyPreformatos.innerHTML = '<tr><td colspan="3" class="text-center">Cargando preformatos...</td></tr>';
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getAllPreformatos');
    
    // Realizar petición AJAX
    $.ajax({
        type: 'POST',
        url: 'ajax/preformatos.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
            // Limpiar tabla
            tbodyPreformatos.innerHTML = '';
            
            if (response.status === 'success' && response.data.length > 0) {
                console.log('Datos recibidos:', response.data);
                // Agregar filas a la tabla
                response.data.forEach(function(preformato, index) {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td>${preformato.nombre}</td>
                        <td>${preformato.tipo}</td>
                        <td>
                            <button class="btn btn-info btn-sm btn-editar" data-id="${preformato.id_preformato}" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-eliminar" data-id="${preformato.id_preformato}" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-warning btn-sm btn-ver" data-id="${preformato.id_preformato}" title="Ver">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    `;
                    tbodyPreformatos.appendChild(row);
                });
                
                // Inicializar DataTable si aún no se ha inicializado
                if (!$.fn.DataTable.isDataTable('#tabla-preformatos')) {
                    $('#tabla-preformatos').DataTable({
                        "responsive": true,
                        "lengthChange": true,
                        "autoWidth": false,
                        "pageLength": 10,
                        "language": {
                            "lengthMenu": "Mostrar _MENU_ registros por página",
                            "zeroRecords": "No se encontraron resultados",
                            "info": "Mostrando página _PAGE_ de _PAGES_",
                            "infoEmpty": "No hay registros disponibles",
                            "infoFiltered": "(filtrado de _MAX_ registros totales)",
                            "search": "Buscar:",
                            "paginate": {
                                "first": "Primero",
                                "last": "Último",
                                "next": "Siguiente",
                                "previous": "Anterior"
                            }
                        }
                    });
                } else {
                    // Si ya está inicializado, recargar los datos
                    $('#tabla-preformatos').DataTable().clear().draw();
                    $('#tabla-preformatos').DataTable().rows.add($(tbodyPreformatos).find('tr')).draw();
                }
            } else {
                // Mostrar mensaje si no hay preformatos
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
 * Guarda un nuevo preformato o actualiza uno existente
 */
function guardarPreformato() {
    // Validar campos requeridos
    if (!$("#titulo-preformato").val()) {
        Swal.fire({
            icon: "warning",
            title: "Campo requerido",
            text: "Por favor ingrese un nombre para el preformato"
        });
        return;
    }
    
    // Obtener el contenido del editor Summernote
    let contenido = $('#obs-preformato').summernote('code');
    
    // Determinar si estamos en modo creación o edición
    const idPreformato = $("#id-preformato").val();
    const modo = idPreformato ? "edicion" : "creacion";
    const operacion = modo === "edicion" ? "actualizarPreformato" : "crearPreformato";
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', operacion);
    formData.append('nombre', $("#titulo-preformato").val());
    formData.append('contenido', contenido);
    formData.append('tipo', $("#aplicar-a").val());
    formData.append('creado_por', $("#propietario").val() || 1);
    
    // Si estamos en modo edición, agregar el ID
    if (modo === "edicion") {
        formData.append('id_preformato', idPreformato);
    }
    
    // Mostrar spinner o indicador de carga
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
                // Mostrar mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: modo === "edicion" ? 'Preformato actualizado' : 'Preformato creado',
                    text: response.message,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Limpiar formulario
                    limpiarFormulario();
                    
                    // Recargar la tabla de preformatos
                    const aplicarA = document.getElementById('aplicar-a').value;
                    if (aplicarA) {
                        cargarPreformatosPorTipo(aplicarA);
                    } else {
                        cargarPreformatos();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al guardar el preformato',
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
 * Maneja el evento de editar un preformato
 * @param {number} idPreformato - ID del preformato a editar
 */
function editarPreformato(idPreformato) {
    // Cargar los datos del preformato en el formulario
    cargarPreformatoParaEdicion(idPreformato);
    
    // Cambiar el título del formulario
    $('#titulo-formulario').text('Editar Preformato');
    
    // Cambiar el texto del botón de guardar
    $('#btn-guardar-preformato').text('Actualizar Preformato');
    
    // Mostrar el botón para cancelar la edición
    $('#btn-cancelar-edicion').removeClass('d-none').addClass('d-inline-block');
    
    // Asegurarse que el formulario esté visible
    $('#form-preformato-container').removeClass('d-none');
    
    // Scroll hacia el formulario
    $('html, body').animate({
        scrollTop: $("#preformato-textarea").offset().top - 100
    }, 500);
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
 * Carga los médicos para el selector de propietario
 */
function cargarMedicos() {
    const selectPropietario = document.getElementById('propietario');
    if (!selectPropietario) {
        console.error('No se encontró el elemento select de propietario');
        return;
    }
    
    console.log('Iniciando carga de médicos...');
    
    // Usar la URL completa
    const url = window.location.pathname.includes('index.php') ? 
        'api/doctors' : 
        window.location.origin + '/clinica/api/doctors';
        
    console.log('URL para cargar médicos:', url);
    
    // Realizar petición AJAX usando fetch API con mejor manejo de errores
    fetch(url)
        .then(response => {
            console.log('Respuesta status:', response.status);
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(response => {
            console.log('Respuesta recibida:', response);
            if (response.status === 'success' && response.data && response.data.length > 0) {
                console.log('Médicos obtenidos:', response.data.length);
                // Limpiar selector manteniendo la opción por defecto
                const defaultOption = selectPropietario.querySelector('option[value=""]');
                selectPropietario.innerHTML = '';
                if (defaultOption) {
                    selectPropietario.appendChild(defaultOption);
                }
                
                // Agregar opciones de médicos
                response.data.forEach(function(doctor) {
                    const option = document.createElement('option');
                    option.value = doctor.doctor_id;
                    option.textContent = `${doctor.last_name}, ${doctor.first_name}`;
                    if (doctor.business_name) {
                        option.textContent += ` (${doctor.business_name})`;
                    }
                    selectPropietario.appendChild(option);
                });
            } else {
                console.error('No se obtuvieron médicos o la respuesta no tiene el formato esperado');
                // Añadir una opción por defecto indicando el error
                selectPropietario.innerHTML = '<option value="">No se pudieron cargar los médicos</option>';
            }
        })
        .catch(error => {
            console.error("Error al cargar médicos:", error);
            // Añadir una opción por defecto indicando el error
            selectPropietario.innerHTML = '<option value="">Error al cargar médicos</option>';
            
            // Intentar con la antigua función para compatibilidad
            cargarUsuariosLegacy();
        });
}

/**
 * Función de respaldo para cargar usuarios (para compatibilidad con el sistema anterior)
 */
function cargarUsuariosLegacy() {
    console.log('Intentando cargar usuarios con el método legacy...');
    const selectPropietario = document.getElementById('propietario');
    if (!selectPropietario) return;
    
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
            console.log('Respuesta legacy recibida:', response);
            if (response.status === 'success' && response.data && response.data.length > 0) {
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
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en método legacy:", error);
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

/**
 * Carga un preformato para edición
 * @param {number} idPreformato ID del preformato a editar
 */
function cargarPreformatoParaEdicion(idPreformato) {
    // Mostrar spinner de carga
    $("#modalSpinner").modal("show");
    
    // Crear objeto FormData para enviar los datos
    const formData = new FormData();
    formData.append('operacion', 'getPreformatoById');
    formData.append('id_preformato', idPreformato);
    
    // Realizar petición AJAX para obtener el preformato
    $.ajax({
        url: "ajax/preformatos.ajax.php",
        method: "POST",
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(respuesta) {
            // Ocultar spinner de carga
            console.log(respuesta);
            $("#modalSpinner").modal("hide");
            
            if (respuesta.status === "success") {
                // Cambiar el título del modal si existe
                if ($("#tituloModalPreformato").length) {
                    $("#tituloModalPreformato").text("Editar preformato");
                }
                
                // Llenar el formulario con los datos del preformato
                $("#id-preformato").val(respuesta.data.id_preformato);
                $("#titulo-preformato").val(respuesta.data.nombre);
                
                // Establecer el tipo de preformato
                if ($("#tipo-preformato").length) {
                    $("#tipo-preformato").val(respuesta.data.tipo || "");
                }
                
                // Establecer el propietario
                if ($("#propietario").length) {
                    $("#propietario").val(respuesta.data.creado_por);
                }
                
                // Establecer el valor de "Aplicar a:" basado en el tipo del preformato
                if ($("#aplicar-a").length) {
                    const tipoAplicacion = respuesta.data.tipo_aplicacion || respuesta.data.tipo || "";
                    $("#aplicar-a").val(tipoAplicacion);
                    
                    // Si es necesario, disparar el evento change para actualizar dependencias
                    if (tipoAplicacion) {
                        $("#aplicar-a").trigger('change');
                    }
                }
                
                // Establecer el contenido del editor si se usa Quill
                if (window.editorPreformato) {
                    try {
                        const contenido = respuesta.data.contenido;
                        if (typeof contenido === 'string' && contenido.trim().startsWith('{')) {
                            window.editorPreformato.setContents(JSON.parse(contenido));
                        } else {
                            window.editorPreformato.clipboard.dangerouslyPasteHTML(0, contenido || "");
                        }
                    } catch (e) {
                        console.error("Error al establecer contenido en Quill:", e);
                        window.editorPreformato.clipboard.dangerouslyPasteHTML(0, respuesta.data.contenido || "");
                    }
                }
                
                // Establecer el contenido del editor si se usa Summernote
                if ($('#obs-preformato').summernote) {
                    $('#obs-preformato').summernote('code', respuesta.data.contenido || "");
                } else if ($('#obs-preformato').length) {
                    // Si no hay Summernote pero existe el textarea, establecer el valor directamente
                    $('#obs-preformato').val(respuesta.data.contenido || "");
                }
                
                // Cambiar el texto del botón si existe
                if ($("#btnGuardarPreformato").length) {
                    $("#btnGuardarPreformato").text("Actualizar");
                }
                
                // Hacer scroll al formulario de edición
                $('html, body').animate({
                    scrollTop: $("#form-preformato-textarea").offset().top - 100
                }, 500);
            } else {
                // Mostrar mensaje de error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: respuesta.message || 'No se pudo cargar el preformato para edición',
                    confirmButtonText: 'Aceptar'
                });
            }
        },
        error: function(xhr, status, error) {
            // Ocultar spinner de carga
            $("#modalSpinner").modal("hide");
            
            // Mostrar mensaje de error
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

// Agregar evento al botón de guardar preformato
$(document).on("click", "#btnGuardarPreformato", function() {
    guardarPreformato();
});

// Agregar evento para el botón de editar en la tabla de preformatos
$(document).on("click", ".btnEditarPreformato", function() {
    const idPreformato = $(this).attr("data-id");
    cargarPreformatoParaEdicion(idPreformato);
});

// Actualizar evento para abrir modal de nuevo preformato (asegura modo creación)
$(document).on("click", "#btnNuevoPreformato", function() {
    // Cambiar el título del modal
    $("#tituloModalPreformato").text("Nuevo preformato");
    
    // Reiniciar formulario
    $("#formPreformato").trigger("reset");
    $("#idPreformato").val("");
    
    // Reiniciar editor
    if (editorPreformato) {
        editorPreformato.setContents([{ insert: "" }]);
    }
    
    // Cambiar el texto del botón
    $("#btnGuardarPreformato").text("Guardar");
    
    // Marcar el formulario como en modo creación
    $("#formPreformato").data("modo", "creacion");
    
    // Abrir modal
    $("#modalNuevoPreformato").modal("show");
});

// Document ready function
$(document).ready(function() {
    // Inicializar Summernote en el campo de observaciones
    $('#obs-preformato').summernote({
        height: 250,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onImageUpload: function(files) {
                // Opcional: código para manejar la subida de imágenes
                for (let i = 0; i < files.length; i++) {
                    uploadSummernoteImage(files[i]);
                }
            }
        }
    });

    // Resto del código existente
    // ...existing code...
});