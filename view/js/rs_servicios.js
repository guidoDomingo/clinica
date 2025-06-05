/**
 * Script para manejar el CRUD de servicios (rs_servicios)
 * Este archivo maneja las operaciones de crear, leer, actualizar y eliminar servicios
 */

// Variables globales
let tablaServicios;
let tablaTipos;

// The alertify object is already defined globally in alertify.js
// No need to redefine it here

// Inicializar cuando el documento esté listo
$(document).ready(function () {
    console.log("Inicializando rs_servicios.js v1.0.1");
      // Inicializar la tabla de servicios con DataTables
    tablaServicios = $('#tblServicios').DataTable({
        "ajax": {
            "url": "ajax/rs_servicios.ajax.php",
            "type": "POST",
            "data": function (d) {
                d.accion = "listar";
                // Agregar parámetros de filtrado si están presentes
                const codigo = $("#validarCodigo").val();
                const descripcion = $("#validarDescripcion").val();
                const tipo = $("#validarTipoServicio").val();
                
                if (codigo || descripcion || (tipo && tipo !== "0")) {
                    d.accion = "filtrar";
                    d.codigo = codigo;
                    d.descripcion = descripcion;
                    d.tipo = tipo;
                }
            },
            "dataSrc": ""
        },
        "columns": [
            { "data": "serv_id" },
            { "data": "serv_codigo" },
            { "data": "serv_descripcion" },
            { "data": "categoria_nombre" },
            {
                "data": null, "render": function (data) {
                    return data.serv_tte || "30 min";
                }
            },
            {
                "data": "serv_monto",
                "render": function (data) {
                    return parseFloat(data).toFixed(2);
                }
            },
            {
                "data": "is_active",
                "render": function (data) {
                    return data == true ?
                        '<span class="badge badge-success">Activo</span>' :
                        '<span class="badge badge-danger">Inactivo</span>';
                }
            },
            {
                "defaultContent": `
                <div class='btn-group'>
                    <button class='btn btn-info btn-sm btnEditar'>
                        <i class='fas fa-edit'></i>
                    </button>
                    <button class='btn btn-danger btn-sm btnEliminar'>
                        <i class='fas fa-trash-alt'></i>
                    </button>
                </div>`,
                "orderable": false
            }
        ],
        "responsive": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
        },
        "order": [[0, 'desc']]
    });

    // Evento para abrir el modal de agregar servicio
    $("#modalAgregarServicio").click(function () {
        $("#servicioForm")[0].reset();
        cargarTiposServicio();
        $("#modalAgregarServicios").modal("show");
    });

    // Evento para abrir el modal de gestionar tipos
    $("#modalGestionarTipos").click(function () {
        cargarTablaTipos();
        $("#modalTiposServicio").modal("show");
        console.log("Modal de tipos de servicio abierto");
    });

    // Evento para guardar nuevo servicio
    $("#btnGuardarServicio").click(function () {
        guardarServicio();
    });

    // Evento para editar servicio
    $("#tblServicios").on("click", ".btnEditar", function () {
        let data = tablaServicios.row($(this).parents("tr")).data();
        editarServicio(data);
    });
    // Evento para eliminar servicio
    $("#tblServicios").on("click", ".btnEliminar", function () {
        let data = tablaServicios.row($(this).parents("tr")).data();
        eliminarServicio(data.serv_id);
    });

    // Evento para guardar servicio editado
    $("#btnEditarServicio").click(function () {
        actualizarServicio();
    });

    // Evento para filtrar servicios
    $("#btnFiltrarServicios").click(function () {
        filtrarServicios();
    });

    // Evento para limpiar filtros
    $("#btnLimpiarServicios").click(function () {
        limpiarFiltros();
    });

    // Evento para agregar nuevo tipo
    $("#btnAgregarTipo").click(function () {
        agregarTipoServicio();
    });
    
    // Evento para abrir modal de envío de PDF
    $("#btnAbrirModalPDF").click(function() {
        $("#modalEnviarPDF").modal("show");
    });
    
    // Evento para enviar PDF
    $("#btnEnviarPDF").click(function() {
        enviarPDFManual();
    });
    
    // Iniciar al cargarse la página
    cargarTiposServicio();
});

// Función para cargar tipos de servicio en select, actualiza los selectores de agregar y filtrar
// El selector para editar se maneja en la función editarServicio
function cargarTiposServicio() {
    $.ajax({
        url: "ajax/rs_servicios.ajax.php",
        type: "POST",
        data: {
            accion: "listarTipos"
        },
        dataType: "json",
        success: function (respuesta) {
            console.log("Tipos de servicio cargados:", respuesta);
            
            // Limpiar select de agregar servicio
            $("#servTipo").html('<option value="" selected>Seleccionar...</option>');
            
            // Agregar opciones al select de agregar
            respuesta.forEach(function (tipo) {
                $("#servTipo").append('<option value="' + tipo.tserv_cod + '">' + tipo.servicio + '</option>');
            });
            
            // Actualizar el filtro de búsqueda
            const valorActual = $("#validarTipoServicio").val();
            $("#validarTipoServicio").html('<option value="0">Seleccionar tipo</option>');
            
            respuesta.forEach(function (tipo) {
                $("#validarTipoServicio").append('<option value="' + tipo.tserv_cod + '">' + tipo.servicio + '</option>');
            });
            
            // Restaurar el valor seleccionado si existe
            if (valorActual && valorActual !== "0") {
                $("#validarTipoServicio").val(valorActual);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al cargar tipos de servicio:", error);
            toastr.error("Error al cargar tipos de servicio");
        }
    });
}

/**
 * Función para enviar un documento PDF al paciente
 * @param {string} telefono - Número de teléfono del paciente en formato internacional (ej: 595982313358)
 * @param {string} mediaUrl - URL del PDF a enviar
 * @param {string} mediaCaption - Texto descriptivo que acompañará al PDF
 * @returns {Promise} - Promesa que resuelve con la respuesta del servidor
 */
function enviarPDFPaciente(telefono, mediaUrl, mediaCaption) {
    console.log("Enviando PDF al paciente:", { telefono, mediaUrl, mediaCaption });
    
    // Validación básica
    if (!telefono || !mediaUrl) {
        alertify.error("Se requiere teléfono y URL del documento");
        return Promise.reject(new Error("Datos incompletos"));
    }
    
    // Formatear el teléfono si es necesario (eliminar espacios, guiones, etc.)
    telefono = telefono.replace(/[^0-9]/g, "");
      // Verificar formato internacional
    if (!/^\d{9,15}$/.test(telefono)) {
        alertify.error("Formato de teléfono inválido. Use formato internacional sin '+' (ej: 595982313358)");
        return Promise.reject(new Error("Formato de teléfono inválido"));
    }
    
    // Mostrar notificación de envío en proceso
    const toastEnviando = toastr.info("Enviando documento al paciente...", null, {timeOut: 0, extendedTimeOut: 0});
    
    // Realizar la petición al servidor
    return fetch("ajax/enviar_media.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            telefono: telefono,
            mediaUrl: mediaUrl,
            mediaCaption: mediaCaption || "Documento de la Clínica"
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })    .then(data => {
        console.log("Respuesta del servidor:", data);
        
        // Cerrar notificación de enviando
        toastr.clear(toastEnviando);
        
        if (data.success) {
            alertify.success("Documento enviado correctamente al paciente");
            return data;
        } else {
            alertify.error(data.error || "Error al enviar el documento");
            throw new Error(data.error || "Error en la respuesta del servidor");
        }
    })    .catch(error => {
        console.error("Error al enviar documento:", error);
        
        // Cerrar notificación de enviando
        toastr.clear(toastEnviando);
        
        alertify.error("Error al enviar el documento. Revise la consola para más detalles.");
        throw error;
    });
}

/**
 * Envía un PDF manualmente utilizando los datos del formulario
 */
function enviarPDFManual() {
    // Obtener datos del formulario
    const telefono = $("#enviarPDF_telefono").val().trim();
    const mediaUrl = $("#enviarPDF_url").val().trim();
    const mediaCaption = $("#enviarPDF_descripcion").val().trim();
    
    // Validación básica
    if (!telefono) {
        alertify.error("Debe ingresar un número de teléfono");
        return;
    }
    
    if (!mediaUrl) {
        alertify.error("Debe proporcionar una URL válida para el PDF");
        return;
    }
    
    // Deshabilitar el botón durante el envío
    $("#btnEnviarPDF").prop('disabled', true);
    
    // Mostrar mensaje inicial
    toastr.info("Preparando envío del documento...");
    
    // Llamar a la función de envío
    enviarPDFPaciente(telefono, mediaUrl, mediaCaption)
        .then(response => {
            console.log("PDF enviado con éxito:", response);
            alertify.success("Documento enviado correctamente al número: " + telefono);
            $("#modalEnviarPDF").modal("hide");
        })
        .catch(error => {
            console.error("Error al enviar PDF:", error);
            alertify.error("No se pudo enviar el documento. Revise la consola para más detalles.");
        })
        .finally(() => {
            // Re-habilitar el botón
            $("#btnEnviarPDF").prop('disabled', false);
        });
}

// Función para cargar tabla de tipos
function cargarTablaTipos() {
    if (tablaTipos) {
        tablaTipos.destroy();
    }
    tablaTipos = $('#tblTiposServicio').DataTable({
        "ajax": {
            "url": "ajax/rs_servicios.ajax.php",
            "type": "POST",
            "data": {
                accion: "listarTipos"
            },
            "dataSrc": ""
        },
        "columns": [
            { "data": "tserv_cod" },
            { "data": "servicio" },
            {
                "defaultContent": `
                <div class='btn-group'>
                    <button class='btn btn-warning btn-sm btnEditarTipo'>
                        <i class='fas fa-edit'></i>
                    </button>
                    <button class='btn btn-danger btn-sm btnEliminarTipo'>
                        <i class='fas fa-trash-alt'></i>
                    </button>
                </div>`,
                "orderable": false
            }
        ],
        "responsive": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
        }
    });

    // Evento para editar tipo
    $("#tblTiposServicio").on("click", ".btnEditarTipo", function () {
        let data = tablaTipos.row($(this).parents("tr")).data();
        editarTipoServicio(data);
    });

    // Evento para eliminar tipo
    $("#tblTiposServicio").on("click", ".btnEliminarTipo", function () {
        let data = tablaTipos.row($(this).parents("tr")).data();
        eliminarTipoServicio(data.tserv_cod);
    });
}

// Función para guardar servicio
function guardarServicio() {
    let servicio = {
        serv_codigo: $("#servCodigo").val(),
        tserv_cod: $("#servTipo").val(),
        serv_descripcion: $("#servDescripcion").val(),
        serv_tte: $("#servDuracion").val(),
        serv_monto: $("#servPrecio").val(),
        is_active: $("#servEstado").val()
    };

    // Validación básica
    if (!servicio.serv_codigo || !servicio.tserv_cod || !servicio.serv_descripcion) {
        alertify.error("Todos los campos marcados son obligatorios");
        return;
    }
    $.ajax({
        url: "ajax/rs_servicios.ajax.php",
        type: "POST",
        data: {
            accion: "crear",
            servicio: servicio
        },
        dataType: "json",
        success: function (respuesta) {
            if (respuesta.exito) {
                $("#modalAgregarServicios").modal("hide");
                toastr.success("Servicio registrado correctamente");
                tablaServicios.ajax.reload();
            } else {
                toastr.error(respuesta.mensaje || "Error al registrar servicio");
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al guardar servicio:", error);
            toastr.error("Error al guardar servicio");
        }
    });
}

// Función para editar servicio
function editarServicio(data) {
    // Guardar el ID del tipo de servicio para seleccionarlo después
    const tipoServicioId = data.tserv_cod;
    
    console.log("Editando servicio con ID:", data.serv_id);
    console.log("Tipo de servicio a seleccionar:", tipoServicioId);
    
    // Establecer los valores básicos del formulario primero
    $("#idServicio").val(data.serv_id);
    $("#EditServCodigo").val(data.serv_codigo);
    $("#EditServDescripcion").val(data.serv_descripcion);
    $("#EditServDuracion").val(data.serv_tte || "30");
    $("#EditServPrecio").val(data.serv_monto);
    $("#EditServEstado").val(data.is_active.toString());
    
    // Primero cargar los tipos de servicio
    $.ajax({
        url: "ajax/rs_servicios.ajax.php",
        type: "POST",
        data: {
            accion: "listarTipos"
        },
        dataType: "json",
        success: function (respuesta) {
            console.log("Tipos de servicio cargados:", respuesta);
            
            // Limpiar el select de tipos
            $("#EditServTipo").empty();
            $("#EditServTipo").append('<option value="">Seleccionar...</option>');
            
            // Variable para verificar si encontramos un match
            let encontradoMatch = false;
            
            // Agregar opciones y marcar la seleccionada
            respuesta.forEach(function (tipo) {
                const selected = (tipo.tserv_cod == tipoServicioId) ? 'selected' : '';
                if (tipo.tserv_cod == tipoServicioId) {
                    encontradoMatch = true;
                }
                $("#EditServTipo").append('<option value="' + tipo.tserv_cod + '" ' + selected + '>' + tipo.servicio + '</option>');
            });
            
            // Si no se encontró la opción, podríamos mostrar un mensaje
            if (!encontradoMatch && tipoServicioId) {
                console.warn("No se encontró el tipo de servicio con ID:", tipoServicioId);
                toastr.warning("El tipo de servicio seleccionado anteriormente no está disponible");
            }
            
            // Forzar la selección correcta después de agregar todas las opciones
            $("#EditServTipo").val(tipoServicioId);
            
            // Verificar que el tipo de servicio se haya seleccionado correctamente
            console.log("Valor final de EditServTipo:", $("#EditServTipo").val());
            
            // Mostrar el modal después de que todo esté cargado
            $("#modalEditarServicios").modal("show");
        },
        error: function (xhr, status, error) {
            console.error("Error al cargar tipos de servicio:", error);
            toastr.error("Error al cargar tipos de servicio");
            
            // Aun así mostramos el modal con los datos básicos
            $("#modalEditarServicios").modal("show");
        }
    });
}

// Función para actualizar servicio
function actualizarServicio() {
    let servicio = {
        serv_id: $("#idServicio").val(),
        serv_codigo: $("#EditServCodigo").val(),
        tserv_cod: $("#EditServTipo").val(),
        serv_descripcion: $("#EditServDescripcion").val(),
        serv_tte: $("#EditServDuracion").val(),
        serv_monto: $("#EditServPrecio").val(),
        is_active: $("#EditServEstado").val()
    };

    console.log("Actualizando servicio:", servicio);

    // Validación básica
    if (!servicio.serv_codigo || !servicio.tserv_cod || !servicio.serv_descripcion) {
        alertify.error("Todos los campos marcados son obligatorios");
        console.error("Validación fallida:", {
            codigo: !servicio.serv_codigo, 
            tipo: !servicio.tserv_cod, 
            descripcion: !servicio.serv_descripcion
        });
        return;
    }
    
    $.ajax({
        url: "ajax/rs_servicios.ajax.php",
        type: "POST",
        data: {
            accion: "actualizar",
            servicio: servicio
        },
        dataType: "json",
        success: function (respuesta) {
            console.log("Respuesta del servidor:", respuesta);
            if (respuesta.exito) {
                $("#modalEditarServicios").modal("hide");
                tablaServicios.ajax.reload();
                alertify.success("Servicio actualizado correctamente");
            } else {
                alertify.error(respuesta.mensaje || "Error al actualizar servicio");
                console.error("Error en la respuesta:", respuesta.mensaje);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al actualizar servicio:", error);
            console.error("Detalles del error:", {xhr: xhr, status: status});
            alertify.error("Error al actualizar servicio: " + error);
        }
    });
}

// Función para eliminar servicio
function eliminarServicio(id) {
    Swal.fire({
        title: "Eliminar servicio",
        text: "¿Está seguro de eliminar este servicio?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "ajax/rs_servicios.ajax.php",
                type: "POST",
                data: {
                    accion: "eliminar",
                    id: id
                },
                dataType: "json",
                success: function (respuesta) {
                    if (respuesta.exito) {
                        tablaServicios.ajax.reload();
                        toastr.success("Servicio eliminado correctamente");
                    } else {
                        toastr.error(respuesta.mensaje || "Error al eliminar servicio");
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error al eliminar servicio:", error);
                    toastr.error("Error al eliminar servicio");
                }
            });
        }
    });
}

// Función para filtrar servicios
function filtrarServicios() {
    // Obtener valores de los filtros
    const codigo = $("#validarCodigo").val();
    const descripcion = $("#validarDescripcion").val();
    const tipo = $("#validarTipoServicio").val();
    
    console.log("Filtrando servicios con:", {
        codigo: codigo,
        descripcion: descripcion,
        tipo: tipo
    });
    
    // Recargar la tabla para aplicar los filtros
    tablaServicios.ajax.reload();
}

// Función para limpiar filtros
function limpiarFiltros() {
    $("#validarCodigo").val("");
    $("#validarDescripcion").val("");
    $("#validarTipoServicio").val("0");
    
    console.log("Limpiando filtros");
    
    // Recargar la tabla sin filtros
    tablaServicios.ajax.reload();
}

// Función para agregar nuevo tipo de servicio
function agregarTipoServicio() {
    let nuevoTipo = $("#nuevoTipoServicio").val();

    if (!nuevoTipo) {
        alertify.error("Ingrese el nombre del tipo de servicio");
        return;
    }
    $.ajax({
        url: "ajax/rs_servicios.ajax.php",
        type: "POST",
        data: {
            accion: "crearTipo",
            nombre: nuevoTipo
        },
        dataType: "json",
        success: function (respuesta) {
            if (respuesta.exito) {
                $("#nuevoTipoServicio").val("");
                tablaTipos.ajax.reload();
                cargarTiposServicio(); // Actualiza los selectores
                alertify.success("Tipo de servicio agregado correctamente");
            } else {
                alertify.error(respuesta.mensaje || "Error al agregar tipo de servicio");
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al agregar tipo de servicio:", error);
            alertify.error("Error al agregar tipo de servicio");
        }
    });
}

// Función para editar tipo de servicio
function editarTipoServicio(data) {
    alertify.prompt(
        "Editar tipo de servicio",
        "Nombre del tipo de servicio",
        data.servicio,
        function (evt, value) {
            if (!value) {
                alertify.error("Ingrese el nombre del tipo de servicio");
                return;
            }
            $.ajax({
                url: "ajax/rs_servicios.ajax.php",
                type: "POST",
                data: {
                    accion: "actualizarTipo",
                    id: data.tserv_cod,
                    nombre: value
                },
                dataType: "json",
                success: function (respuesta) {
                    if (respuesta.exito) {
                        console.log("Tipo de servicio actualizado:", respuesta);
                        
                        // Recargar la tabla de tipos
                        tablaTipos.ajax.reload();
                        
                        // Actualizar los selectores
                        cargarTiposServicio();
                        
                        // Mostrar mensaje de éxito
                        alertify.success("Tipo de servicio actualizado correctamente");
                    } else {
                        console.error("Error en la respuesta:", respuesta.mensaje);
                        alertify.error(respuesta.mensaje || "Error al actualizar tipo de servicio");
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error al actualizar tipo de servicio:", error);
                    console.error("Detalles:", {xhr: xhr, status: status});
                    alertify.error("Error al actualizar tipo de servicio");
                }
            });
        },
        function () {
            // Cancelar edición
            console.log("Edición de tipo de servicio cancelada");
        }
    );
}

// Función para eliminar tipo de servicio
function eliminarTipoServicio(id) {
    alertify.confirm(
        "Eliminar tipo de servicio",
        "¿Está seguro de eliminar este tipo de servicio? Esta acción podría afectar a los servicios asociados.",
        function () {
            $.ajax({
                url: "ajax/rs_servicios.ajax.php",
                type: "POST",
                data: {
                    accion: "eliminarTipo",
                    id: id
                },
                dataType: "json",
                success: function (respuesta) {
                    if (respuesta.exito) {
                        tablaTipos.ajax.reload();
                        cargarTiposServicio();
                        alertify.success("Tipo de servicio eliminado correctamente");
                    } else {
                        alertify.error(respuesta.mensaje || "Error al eliminar tipo de servicio");
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error al eliminar tipo de servicio:", error);
                    alertify.error("Error al eliminar tipo de servicio");
                }
            });
        },
        function () {
            // Cancelar eliminación
        }
    );
}
