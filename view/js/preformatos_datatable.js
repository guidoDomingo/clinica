/**
 * Gestión de preformatos con DataTables y filtros avanzados
 */
$(document).ready(function () {
    // Inicializar Select2 para mejorar los filtros
    $('.select2').select2({
        width: '100%',
        placeholder: 'Seleccione una opción'
    });

    // Cargar usuarios para el filtro de propietario
    cargarUsuariosParaFiltro();

    // Inicializar DataTable
    let tablaPreformatos = $('#tabla-preformatos-dt').DataTable({
        ajax: {
            url: 'ajax/preformatos.ajax.php',
            type: 'POST',
            data: function (d) {
                d.action = 'obtenerTodosPreformatos';
                d.filtroTipo = $('#filtro-tipo').val();
                d.filtroPropietario = $('#filtro-propietario').val();
                d.filtroTitulo = $('#filtro-titulo').val();
            },
            dataSrc: function (json) {
                if (json.error) {
                    console.error('Error al cargar preformatos:', json.error);
                    return [];
                }
                return json.data || [];
            }
        },
        columns: [
            { data: 'id' },
            { data: 'titulo' },
            { 
                data: 'tipo',
                render: function (data) {
                    let tipoLabel = '';
                    switch (data) {
                        case 'consulta':
                            tipoLabel = '<span class="badge badge-primary">Consulta</span>';
                            break;
                        case 'receta':
                            tipoLabel = '<span class="badge badge-success">Receta</span>';
                            break;
                        case 'receta_anteojos':
                            tipoLabel = '<span class="badge badge-info">Receta Anteojos</span>';
                            break;
                        case 'orden_estudios':
                            tipoLabel = '<span class="badge badge-warning">Orden Estudios</span>';
                            break;
                        case 'orden_cirugias':
                            tipoLabel = '<span class="badge badge-danger">Orden Cirugías</span>';
                            break;
                        default:
                            tipoLabel = '<span class="badge badge-secondary">' + data + '</span>';
                    }
                    return tipoLabel;
                }
            },
            { data: 'nombre_usuario' },
            { data: 'fecha_creacion' },
            {
                data: null,
                render: function (data, type, row) {
                    return `
                        <div class="btn-group">
                            <button type="button" class="btn btn-info btn-sm btn-ver-preformato" data-id="${row.id}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm btn-editar-preformato" data-id="${row.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm btn-eliminar-preformato" data-id="${row.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        responsive: true,
        autoWidth: false,
        language: {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "Último",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    });

    // Manejar el envío del formulario de filtros
    $('#form-filtros').on('submit', function (e) {
        e.preventDefault();
        tablaPreformatos.ajax.reload();
    });

    // Manejar el botón de limpiar filtros
    $('#btn-limpiar-filtros').on('click', function () {
        $('#filtro-tipo').val('').trigger('change');
        $('#filtro-propietario').val('').trigger('change');
        $('#filtro-titulo').val('');
        tablaPreformatos.ajax.reload();
    });

    // Manejar el botón de ver preformato
    $('#tabla-preformatos-dt').on('click', '.btn-ver-preformato', function () {
        const idPreformato = $(this).data('id');
        verPreformato(idPreformato);
    });

    // Manejar el botón de editar preformato
    $('#tabla-preformatos-dt').on('click', '.btn-editar-preformato', function () {
        const idPreformato = $(this).data('id');
        window.location.href = 'index.php?ruta=preformatos&id=' + idPreformato;
    });

    // Manejar el botón de editar dentro del modal
    $('#btn-editar-preformato').on('click', function () {
        const idPreformato = $(this).data('id');
        window.location.href = 'index.php?ruta=preformatos&id=' + idPreformato;
    });

    // Manejar el botón de eliminar preformato
    $('#tabla-preformatos-dt').on('click', '.btn-eliminar-preformato', function () {
        const idPreformato = $(this).data('id');
        eliminarPreformato(idPreformato, tablaPreformatos);
    });
});

// Función para cargar los usuarios para el filtro
function cargarUsuariosParaFiltro() {
    $.ajax({
        url: 'ajax/preformatos.ajax.php',
        type: 'POST',
        data: {
            action: 'obtenerUsuarios'
        },
        dataType: 'json',
        success: function (respuesta) {
            if (respuesta.error) {
                console.error('Error al cargar usuarios:', respuesta.error);
                return;
            }

            let selectUsuarios = $('#filtro-propietario');
            selectUsuarios.empty();
            selectUsuarios.append('<option value="">Todos</option>');

            if (respuesta.data && respuesta.data.length > 0) {
                respuesta.data.forEach(function (usuario) {
                    selectUsuarios.append('<option value="' + usuario.id + '">' + usuario.nombre + '</option>');
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Error en la petición AJAX:', error);
        }
    });
}

// Función para ver un preformato
function verPreformato(idPreformato) {
    $.ajax({
        url: 'ajax/preformatos.ajax.php',
        type: 'POST',
        data: {
            action: 'obtenerPreformatoPorId',
            id: idPreformato
        },
        dataType: 'json',
        success: function (respuesta) {
            if (respuesta.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al cargar el preformato: ' + respuesta.error
                });
                return;
            }

            // Establecer id para el botón de editar
            $('#btn-editar-preformato').data('id', idPreformato);

            // Llenar campos del modal
            $('#modal-titulo-preformato-dt').text(respuesta.titulo);
            
            // Formatear el tipo
            let tipoTexto = '';
            switch (respuesta.tipo) {
                case 'consulta': tipoTexto = 'Consulta'; break;
                case 'receta': tipoTexto = 'Receta de Medicamentos'; break;
                case 'receta_anteojos': tipoTexto = 'Receta de Anteojos'; break;
                case 'orden_estudios': tipoTexto = 'Orden de Estudios'; break;
                case 'orden_cirugias': tipoTexto = 'Orden de Cirugías'; break;
                default: tipoTexto = respuesta.tipo;
            }
            
            $('#modal-tipo-preformato').text(tipoTexto);
            $('#modal-propietario-preformato').text(respuesta.nombre_usuario);
            
            // Mostrar el contenido con formato
            $('#modal-contenido-preformato-dt').html(respuesta.contenido);

            // Mostrar el modal
            $('#modalVerPreformatoDt').modal('show');
        },
        error: function (xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error en la petición: ' + error
            });
        }
    });
}

// Función para eliminar un preformato
function eliminarPreformato(idPreformato, tabla) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "¡Esta acción no se puede deshacer!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax/preformatos.ajax.php',
                type: 'POST',
                data: {
                    action: 'eliminarPreformato',
                    id: idPreformato
                },
                dataType: 'json',
                success: function (respuesta) {
                    if (respuesta.exito) {
                        Swal.fire(
                            '¡Eliminado!',
                            'El preformato ha sido eliminado.',
                            'success'
                        );
                        tabla.ajax.reload();
                    } else {
                        Swal.fire(
                            'Error',
                            respuesta.error || 'No se pudo eliminar el preformato.',
                            'error'
                        );
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire(
                        'Error',
                        'Error en la petición: ' + error,
                        'error'
                    );
                }
            });
        }
    });
}