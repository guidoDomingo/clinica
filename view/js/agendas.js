/**
 * Archivo: agendas.js
 * Descripción: Maneja la interacción del usuario con el módulo de agendas médicas
 */

// Verificación de carga del script
console.log("✅ El archivo agendas.js se ha cargado correctamente");

$(document).ready(function() {
    // Alerta de verificación

    // Cargar médicos al iniciar
    cargarMedicos();

    // Cargar consultorios al iniciar
    cargarConsultorios();
    
    // Cargar turnos al iniciar
    cargarTurnos();
    
    // Cargar salas al iniciar
    cargarSalas();
   
    console.log("✅ El módulo de agendas se ha inicializado - Document Ready");
    // Inicialización de componentes
    $('.select2').select2();
    
    try {
        // Inicializar DateTimePicker para los campos de hora
        $('#horaInicio, #horaFin').datetimepicker({
            format: 'HH:mm',
            stepping: 15,
            icons: {
                up: 'fas fa-chevron-up',
                down: 'fas fa-chevron-down',
                previous: 'fas fa-chevron-left',
                next: 'fas fa-chevron-right'
            }
        });

        // Inicializar DatePicker para fechas
        $('#fechaInicio, #fechaFin').datetimepicker({
            format: 'YYYY-MM-DD',
            icons: {
                up: 'fas fa-chevron-up',
                down: 'fas fa-chevron-down',
                previous: 'fas fa-chevron-left',
                next: 'fas fa-chevron-right'
            }
        });
    } catch (e) {
        console.error("Error al inicializar datetimepicker:", e);
        toastr.warning("No se pudo inicializar el selector de fecha/hora. Verifique que la biblioteca esté incluida correctamente.", "Advertencia");
    }

    // Inicializar DataTable para agendas
    var tablaAgendas = $('#tablaAgendas').DataTable({
        "ajax": {
            "url": "../ajax/agendas.ajax.php",
            "type": "POST",
            "data": {"accion": "listar"}
        },
        "columns": [
            {"data": "id"},
            {"data": "estado"},
            {"data": "medico"},
            {"data": "detalle"},
            {"data": "sala"},
            {"data": "acciones"}
        ],
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            }
        }
    });

    // Inicializar DataTable para bloqueos
    var tablaBloqueos = $('#tablaBloqueos').DataTable({
        "ajax": {
            "url": "../ajax/agendas.ajax.php",
            "type": "POST",
            "data": {"accion": "listarBloqueos"}
        },
        "columns": [
            {"data": "id"},
            {"data": "medico"},
            {"data": "fecha_inicio"},
            {"data": "fecha_fin"},
            {"data": "motivo"},
            {"data": "acciones"}
        ],
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            }
        }
    });



    // Evento para el botón Nueva Agenda
    $("#btnNuevaAgenda").click(function() {
        limpiarFormularioAgenda();
    });

    // Evento para el botón Nuevo Bloqueo
    $("#btnNuevoBloqueo").click(function() {
        limpiarFormularioBloqueo();
    });

    // Evento para guardar agenda
    $("#formAgenda").submit(function(e) {
        e.preventDefault();
        guardarAgenda();
    });
    
    // Evento para el botón guardar agenda
    $("#btnGuardarAgenda").click(function() {
        $("#formAgenda").submit();
    });

    // Evento para guardar bloqueo
    $("#formBloqueo").submit(function(e) {
        e.preventDefault();
        guardarBloqueo();
    });

    // Evento para checkbox "todos los días"
    $("#checkboxTodos").change(function() {
        if($(this).is(':checked')) {
            $("input[name='dia_semana'][value='1']").prop('checked', true);
            $("input[name='dia_semana'][value='2']").prop('checked', true);
            $("input[name='dia_semana'][value='3']").prop('checked', true);
            $("input[name='dia_semana'][value='4']").prop('checked', true);
            $("input[name='dia_semana'][value='5']").prop('checked', true);
            $("input[name='dia_semana'][value='6']").prop('checked', false);
            $("input[name='dia_semana'][value='7']").prop('checked', false);
        } else {
            $("input[name='dia_semana']").prop('checked', false);
        }
    });

    // Delegación de eventos para botones de editar y eliminar
    $("#tablaAgendas").on("click", ".btnEditarAgenda", function() {
        var idAgenda = $(this).attr("idAgenda");
        editarAgenda(idAgenda);
    });

    $("#tablaAgendas").on("click", ".btnEliminarAgenda", function() {
        var idAgenda = $(this).attr("idAgenda");
        eliminarAgenda(idAgenda);
    });

    $("#tablaBloqueos").on("click", ".btnEditarBloqueo", function() {
        var idBloqueo = $(this).attr("idBloqueo");
        editarBloqueo(idBloqueo);
    });

    $("#tablaBloqueos").on("click", ".btnEliminarBloqueo", function() {
        var idBloqueo = $(this).attr("idBloqueo");
        eliminarBloqueo(idBloqueo);
    });
});

/**
 * Función para cargar los médicos en el select
 */
function cargarMedicos() {
    console.log("🔍 Iniciando carga de médicos...");
    $.ajax({
        url: "ajax/agendas.ajax.php",
        method: "POST",
        data: {"accion": "cargarMedicos"},
        dataType: "json",
        success: function(respuesta) {
            console.log("✅ Respuesta recibida para médicos:", respuesta);
            
            // Verificar si la respuesta es un array y tiene elementos
            if (!Array.isArray(respuesta) || respuesta.length === 0) {
                console.warn("⚠️ La respuesta no es un array o está vacía", respuesta);
                toastr.warning("No se encontraron médicos para cargar", "Advertencia");
                return;
            }
            
            // Limpiar select
            $("#medicoAgenda").empty();
            $("#medicoAgenda").append('<option value="">-- Seleccione --</option>');
            
            // Agregar opciones
            respuesta.forEach(function(medico) {
                $("#medicoAgenda").append('<option value="' + medico.id + '">' + medico.nombre + ' - ' + medico.especialidad + '</option>');
                console.log("➕ Médico agregado:", medico.id, medico.nombre);
            });

            // Hacer lo mismo para el select de bloqueos
            $("#medicoBloqueo").empty();
            $("#medicoBloqueo").append('<option value="">-- Seleccione --</option>');
            
            respuesta.forEach(function(medico) {
                $("#medicoBloqueo").append('<option value="' + medico.id + '">' + medico.nombre + '</option>');
            });
            
            console.log("✅ Médicos cargados correctamente en los selectores");
        },
        error: function(xhr, status, error) {
            console.error("❌ Error al cargar médicos:", error);
            console.error("Detalles del error:", xhr.responseText);
            toastr.error("Error al cargar médicos", "Error");
        }
    });
}

/**
 * Función para cargar los consultorios en el select
 */
function cargarConsultorios() {
    console.log("🔍 Iniciando carga de consultorios...");
    $.ajax({
        url: "../ajax/agendas.ajax.php",
        method: "POST",
        data: {"accion": "cargarConsultorios"},
        dataType: "json",
        success: function(respuesta) {
            console.log("✅ Respuesta recibida para consultorios:", respuesta);
            
            // Verificar si la respuesta es un array y tiene elementos
            if (!Array.isArray(respuesta) || respuesta.length === 0) {
                console.warn("⚠️ La respuesta no es un array o está vacía", respuesta);
                toastr.warning("No se encontraron consultorios para cargar", "Advertencia");
                return;
            }
            
            // Limpiar select
            $("#consultorioAgenda").empty();
            $("#consultorioAgenda").append('<option value="">-- Seleccione --</option>');
            
            // Agregar opciones
            respuesta.forEach(function(consultorio) {
                $("#consultorioAgenda").append('<option value="' + consultorio.id + '">' + consultorio.nombre + '</option>');
                console.log("➕ Consultorio agregado:", consultorio.id, consultorio.nombre);
            });
            
            console.log("✅ Consultorios cargados correctamente en el selector");
        },
        error: function(xhr, status, error) {
            console.error("❌ Error al cargar consultorios:", error);
            console.error("Detalles del error:", xhr.responseText);
            toastr.error("Error al cargar consultorios", "Error");
        }
    });
}

/**
 * Función para cargar los turnos en el select
 */
function cargarTurnos() {
    console.log("🔍 Iniciando carga de turnos...");
    $.ajax({
        url: "../ajax/agendas.ajax.php",
        method: "POST",
        data: {"accion": "cargarTurnos"},
        dataType: "json",
        success: function(respuesta) {
            console.log("✅ Respuesta recibida para turnos:", respuesta);
            
            // Verificar si la respuesta es un array y tiene elementos
            if (!Array.isArray(respuesta) || respuesta.length === 0) {
                console.warn("⚠️ La respuesta no es un array o está vacía", respuesta);
                toastr.warning("No se encontraron turnos para cargar", "Advertencia");
                return;
            }
            
            // Limpiar select
            $("#turnoAgenda").empty();
            $("#turnoAgenda").append('<option value="">-- Seleccione --</option>');
            
            // Agregar opciones
            respuesta.forEach(function(turno) {
                // Priorizar el nombre del turno si existe
                if (turno.nombre) {
                    $("#turnoAgenda").append('<option value="' + turno.id + '">' + turno.nombre + '</option>');
                    console.log("➕ Turno agregado por nombre:", turno.id, turno.nombre);
                } else {
                    // Intentar usar fecha y hora si están disponibles
                    let fecha = turno.fecha || "";
                    let hora = turno.hora_inicio || "";
                    
                    if (fecha && hora) {
                        // Formatear fecha y hora para mostrar
                        let turnoTexto = fecha + ' ' + hora;
                        $("#turnoAgenda").append('<option value="' + turno.id + '">' + turnoTexto + '</option>');
                        console.log("➕ Turno agregado por fecha/hora:", turno.id, turnoTexto);
                    } else {
                        // Si no hay datos suficientes, mostrar un texto genérico con el ID
                        $("#turnoAgenda").append('<option value="' + turno.id + '">Turno ' + turno.id + '</option>');
                        console.log("➕ Turno agregado con ID genérico:", turno.id);
                    }
                }
            });
            
            console.log("✅ Turnos cargados correctamente en el selector");
        },
        error: function(xhr, status, error) {
            console.error("❌ Error al cargar turnos:", error);
            console.error("Detalles del error:", xhr.responseText);
            toastr.error("Error al cargar turnos", "Error");
        }
    });
}

/**
 * Función para cargar los consultorios como salas en el select
 */
function cargarSalas() {
    console.log("🔍 Iniciando carga de consultorios como salas...");
    $.ajax({
        url: "../ajax/agendas.ajax.php",
        method: "POST",
        data: {"accion": "cargarSalas"},
        dataType: "json",
        success: function(respuesta) {
            console.log("✅ Respuesta recibida para consultorios/salas:", respuesta);
            
            // Verificar si la respuesta es un array y tiene elementos
            if (!Array.isArray(respuesta) || respuesta.length === 0) {
                console.warn("⚠️ La respuesta no es un array o está vacía", respuesta);
                toastr.warning("No se encontraron consultorios/salas para cargar", "Advertencia");
                return;
            }
            
            // Limpiar select
            $("#salaAgenda").empty();
            $("#salaAgenda").append('<option value="">-- Seleccione --</option>');
            
            // Agregar opciones
            respuesta.forEach(function(sala) {
                $("#salaAgenda").append('<option value="' + sala.id + '">' + sala.nombre + '</option>');
                console.log("➕ Consultorio/Sala agregada:", sala.id, sala.nombre);
            });
            
            console.log("✅ Consultorios/Salas cargados correctamente en el selector");
        },
        error: function(xhr, status, error) {
            console.error("❌ Error al cargar consultorios/salas:", error);
            console.error("Detalles del error:", xhr.responseText);
            toastr.error("Error al cargar consultorios/salas", "Error");
        }
    });
}

/**
 * Función para guardar una agenda
 */
function guardarAgenda() {
    // Obtener los días seleccionados
    var diasSeleccionados = [];
    $("input[name='dia_semana']:checked").each(function() {
        diasSeleccionados.push($(this).val());
    });

    // Validar que al menos un día esté seleccionado
    if (diasSeleccionados.length === 0) {
        toastr.warning("Debe seleccionar al menos un día de la semana", "Advertencia");
        return;
    }

    // Crear objeto con los datos del formulario
    var datosAgenda = {
        "accion": $("#idAgenda").val() ? "actualizar" : "guardar",
        "id": $("#idAgenda").val(),
        "medico_id": $("#medicoAgenda").val(),
        "dias": diasSeleccionados.join(','),
        "hora_inicio": $("#horaInicio").val(),
        "hora_fin": $("#horaFin").val(),
        "duracion_turno": $("#intervaloAgenda").val(),
        "consultorio_id": $("#salaAgenda").val(),
        "turno_id": $("#turnoAgenda").val(),
        "estado": $("#estadoAgenda").val()
    };

    console.log("Datos de la agenda a guardar:", datosAgenda);

    $.ajax({
        url: "../ajax/agendas.ajax.php",
        method: "POST",
        data: datosAgenda,
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.ok) {
                toastr.success(respuesta.mensaje, "Éxito");
                $("#tablaAgendas").DataTable().ajax.reload();
                limpiarFormularioAgenda();
            } else {
                toastr.error(respuesta.mensaje, "Error");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al guardar agenda:", error);
            console.error("Detalles del error:", xhr.responseText);
            
            // Intentar parsear la respuesta como JSON
            try {
                var respuestaError = JSON.parse(xhr.responseText);
                if (respuestaError && respuestaError.mensaje) {
                    toastr.error(respuestaError.mensaje, "Error");
                } else {
                    toastr.error("Error al guardar agenda: " + error, "Error");
                }
            } catch (e) {
                // Si no es JSON, mostrar un mensaje genérico
                toastr.error("Error al guardar agenda. Por favor, contacte al administrador.", "Error");
                console.error("Error al parsear respuesta:", e);
            }
        }
    });
}

/**
 * Función para guardar un bloqueo
 */
function guardarBloqueo() {
    // Crear objeto con los datos del formulario
    var datosBloqueo = {
        "accion": $("#idBloqueo").val() ? "actualizarBloqueo" : "guardarBloqueo",
        "id": $("#idBloqueo").val(),
        "medico_id": $("#medicoBloqueo").val(),
        "fecha_inicio": $("#fechaInicio").val(),
        "fecha_fin": $("#fechaFin").val(),
        "motivo": $("#motivoBloqueo").val()
    };

    $.ajax({
        url: "../ajax/agendas.ajax.php",
        method: "POST",
        data: datosBloqueo,
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.ok) {
                toastr.success(respuesta.mensaje, "Éxito");
                $("#tablaBloqueos").DataTable().ajax.reload();
                limpiarFormularioBloqueo();
            } else {
                toastr.error(respuesta.mensaje, "Error");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al guardar bloqueo:", error);
            toastr.error("Error al guardar bloqueo", "Error");
        }
    });
}

/**
 * Función para editar una agenda
 */
function editarAgenda(idAgenda) {
    $.ajax({
        url: "../ajax/agendas.ajax.php",
        method: "POST",
        data: {
            "accion": "obtener",
            "id": idAgenda
        },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.ok) {
                var agenda = respuesta.datos;
                
                // Llenar formulario con datos
                $("#idAgenda").val(agenda.id);
                $("#medicoAgenda").val(agenda.medico_id).trigger('change');
                
                // Marcar días seleccionados
                var dias = agenda.dias.split(',');
                $("input[name='dia_semana']").prop('checked', false);
                dias.forEach(function(dia) {
                    $("input[name='dia_semana'][value='" + dia + "']").prop('checked', true);
                });
                
                $("#horaInicio").val(agenda.hora_inicio);
                $("#horaFin").val(agenda.hora_fin);
                $("#intervaloAgenda").val(agenda.duracion_turno);
                $("#consultorioAgenda").val(agenda.consultorio_id).trigger('change');
                $("#estadoAgenda").val(agenda.estado);
                
                // Desplazarse al formulario
                $('html, body').animate({
                    scrollTop: $("#formAgenda").offset().top - 70
                }, 500);
            } else {
                toastr.error(respuesta.mensaje, "Error");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener agenda:", error);
            toastr.error("Error al obtener agenda", "Error");
        }
    });
}

/**
 * Función para editar un bloqueo
 */
function editarBloqueo(idBloqueo) {
    $.ajax({
        url: "../ajax/agendas.ajax.php",
        method: "POST",
        data: {
            "accion": "obtenerBloqueo",
            "id": idBloqueo
        },
        dataType: "json",
        success: function(respuesta) {
            if (respuesta.ok) {
                var bloqueo = respuesta.datos;
                
                // Llenar formulario con datos
                $("#idBloqueo").val(bloqueo.id);
                $("#medicoBloqueo").val(bloqueo.medico_id).trigger('change');
                $("#fechaInicio").val(bloqueo.fecha_inicio);
                $("#fechaFin").val(bloqueo.fecha_fin);
                $("#motivoBloqueo").val(bloqueo.motivo);
                
                // Desplazarse al formulario
                $('html, body').animate({
                    scrollTop: $("#formBloqueo").offset().top - 70
                }, 500);
            } else {
                toastr.error(respuesta.mensaje, "Error");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener bloqueo:", error);
            toastr.error("Error al obtener bloqueo", "Error");
        }
    });
}

/**
 * Función para eliminar una agenda
 */
function eliminarAgenda(idAgenda) {
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
            $.ajax({
                url: "../ajax/agendas.ajax.php",
                method: "POST",
                data: {
                    "accion": "eliminar",
                    "id": idAgenda
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.ok) {
                        toastr.success(respuesta.mensaje, "Éxito");
                        $("#tablaAgendas").DataTable().ajax.reload();
                    } else {
                        toastr.error(respuesta.mensaje, "Error");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al eliminar agenda:", error);
                    toastr.error("Error al eliminar agenda", "Error");
                }
            });
        }
    });
}

/**
 * Función para eliminar un bloqueo
 */
function eliminarBloqueo(idBloqueo) {
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
            $.ajax({
                url: "../ajax/agendas.ajax.php",
                method: "POST",
                data: {
                    "accion": "eliminarBloqueo",
                    "id": idBloqueo
                },
                dataType: "json",
                success: function(respuesta) {
                    if (respuesta.ok) {
                        toastr.success(respuesta.mensaje, "Éxito");
                        $("#tablaBloqueos").DataTable().ajax.reload();
                    } else {
                        toastr.error(respuesta.mensaje, "Error");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al eliminar bloqueo:", error);
                    toastr.error("Error al eliminar bloqueo", "Error");
                }
            });
        }
    });
}

/**
 * Función para limpiar el formulario de agenda
 */
function limpiarFormularioAgenda() {
    $("#formAgenda")[0].reset();
    $("#idAgenda").val("");
    $("#medicoAgenda").val("").trigger('change');
    $("#salaAgenda").val("").trigger('change');
    $("#intervaloAgenda").val("15");
    $("#estadoAgenda").val("1");
}

/**
 * Función para limpiar el formulario de bloqueo
 */
function limpiarFormularioBloqueo() {
    $("#formBloqueo")[0].reset();
    $("#idBloqueo").val("");
    $("#medicoBloqueo").val("").trigger('change');
}