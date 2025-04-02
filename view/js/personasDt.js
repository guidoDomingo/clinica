$(document).ready(function() {
    let tbBarcodReport;
    cargarDataTable();

    function reloadDataTable(data) {
        if ($.fn.DataTable.isDataTable('#tblPersonas')) {
            tbBarcodReport.clear().rows.add(data).draw(); // Solo actualizar datos sin destruir la tabla
        } else {
            cargarDataTable(data);
        }
    }

    $(document).on("click", "#btnFiltrarPersonas", function(e) {
        e.preventDefault();
        const filtros = {
            documento: $('#validarDocumento').val().trim(),
            nombre: $('#validarNombre').val().trim(),
            apellido: $('#validarApellidos').val().trim(),
            sexo: $('#validarSexo').val().trim(),
            propietario: 2
        };
        
        $.post("ajax/personasDt.ajax.php", filtros, function(response) {
            reloadDataTable(response.data);
        }, "json");
    });

    $(document).on("click", "#btnLimpiarPersonas", function(e) {
        e.preventDefault();
        limpiarBuscarPersona();
        reloadDataTable([]);
    });

    function cargarDataTable(data = []) {
        tbBarcodReport = $('#tblPersonas').DataTable({
            destroy: true, // Asegura que no haya duplicación de instancias
            data: data,
            dom: "Bfrtip",
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            language: { url: "view/js/Spanish.json" },
            order: [[11, "asc"]], 
            columns: [
                { data: "id_persona", render: (_, __, ___, meta) => meta.row + 1 },
                { data: "documento", render: (data, _, row) => `<a href="index.php?ruta=perfil&id_persona=${row.id_persona}" target="_blank" class="enlace-perfil">${data}</a>` },
                { data: "nombres" },
                { data: "apellidos" },
                { data: "fecha_nacimiento", render: (data) => `${calcularEdad(data)} años` },
                { data: "nro_ficha" },
                { data: "telefono" },
                { data: "menor", render: (data) => data ? 'SI' : 'NO' },
                { data: "tutor" },
                { data: "documento_tutor" },
                { data: "activo", visible: false },
                { data: null, render: (_, __, row) => generarBotones(row) }
            ]
        });
    }

    function generarBotones(row) {
        let iconoEstado = row.activo === 'SI' ? '<i class="fa-solid fa-toggle-on"></i>' : '<i class="fa-solid fa-toggle-off"></i>';
        let btnClass = row.activo === 'SI' ? 'btn-success' : 'btn-dark';

        return `
            <div class="btn-group">
                <button type="button" class="btn ${btnClass} btn-inactivar" title="Inactivar" btnId="${row.id_persona}" btnEstadoPersona="${row.activo}">${iconoEstado}</button>
                <button type="button" class="btn btn-primary btn-modificar" title="Modificar" btnId="${row.id_persona}"><i class="fa-solid fa-pen-to-square"></i></button>
                <button type="button" class="btn btn-danger btn-eliminar" title="Eliminar" btnId="${row.id_persona}"><i class="fa-solid fa-trash"></i></button>
                <button type="button" class="btn btn-dark btn-doctor" title="Doctor" btnId="${row.id_persona}"><i class="fa-solid fa-star"></i></button>
            </div>`;
    }

    $('#tblPersonas').on('click', '.btn-inactivar', function() {
        const id_persona = $(this).attr('btnId');
        const estado = $(this).attr('btnEstadoPersona') === 'SI' ? 'NO' : 'SI';

        $.post('ajax/persona.ajax.php', { id_persona, propietario: 2, operacion: "updateEstadoPersonaById", activo: estado }, function(response) {
            if (response.success) {
                // console.log("editar "+response.success)
                const filtros = {
                    documento: $('#validarDocumento').val().trim(),
                    nombre: $('#validarNombre').val().trim(),
                    apellido: $('#validarApellidos').val().trim(),
                    sexo: $('#validarSexo').val().trim(),
                    propietario: 2
                };
                
                $.post("ajax/personasDt.ajax.php", filtros, function(response) {
                    reloadDataTable(response.data);
                }, "json");
                // let rowIndex = tbBarcodReport.row($(this).parents('tr')).index();
                // tbBarcodReport.cell(rowIndex, 10).data(estado).draw();
            } else {
                mostrarAlerta("error", response.message);
            }
        }, "json");
    });

    $('#tblPersonas').on('click', '.btn-eliminar', function() {
        let id_persona = $(this).attr('btnId');
        Swal.fire({
            title: "¿Estás seguro?",
            text: "¡No podrás deshacer esta acción!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, ¡elimínalo!",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax/persona.ajax.php', { id_persona, propietario: 2, operacion: "deletePersonaById" }, function(response) {
                    if (response.success) {
                        // mostrarAlerta("success", response.message);
                        // tbBarcodReport.row($(this).parents('tr')).remove().draw();
                        const filtros = {
                            documento: $('#validarDocumento').val().trim(),
                            nombre: $('#validarNombre').val().trim(),
                            apellido: $('#validarApellidos').val().trim(),
                            sexo: $('#validarSexo').val().trim(),
                            propietario: 2
                        };
                        
                        $.post("ajax/personasDt.ajax.php", filtros, function(response) {
                            reloadDataTable(response.data);
                        }, "json");
                    } else {
                        mostrarAlerta("warning", response.message);
                    }
                }, "json");
            }
        });
    });

    document.getElementById('btnEditarPersona').addEventListener('click', function() {
        const formData = new FormData(document.getElementById('personaEditarForm'));
        formData.append('operacion', 'editarPersona');
        formData.append('id_persona', $('#idPersona').val());
        formData.append('propietario', $('#txtPropietario').val());

        fetch('ajax/persona.ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // mostrarAlerta("success", data.message);
                document.getElementById('personaEditarForm').reset();
                $('#modalEditarPersonas').modal('hide');
                // reloadDataTable(data);
                const filtros = {
                    documento: $('#validarDocumento').val().trim(),
                    nombre: $('#validarNombre').val().trim(),
                    apellido: $('#validarApellidos').val().trim(),
                    sexo: $('#validarSexo').val().trim(),
                    propietario: 2
                };
                
                $.post("ajax/personasDt.ajax.php", filtros, function(response) {
                    reloadDataTable(response.data);
                }, "json");
            } else {
                mostrarAlerta("warning", data.message);
            }
        })
        .catch(error => mostrarAlerta("error", "Ocurrió un error al procesar la solicitud."));
    });

    document.getElementById('btnGuardarPersona').addEventListener('click', function() {
        const formData = new FormData(document.getElementById('personaForm'));
        formData.append('operacion', 'insertPersona');

        fetch('ajax/persona.ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarAlerta("success", data.message);
                document.getElementById('personaForm').reset();
                $('#modalAgregarPersonas').modal('hide');
                // reloadDataTable(data);
                const filtros = {
                    documento: $('#validarDocumento').val().trim(),
                    nombre: $('#validarNombre').val().trim(),
                    apellido: $('#validarApellidos').val().trim(),
                    sexo: $('#validarSexo').val().trim(),
                    propietario: 2
                };
                
                $.post("ajax/personasDt.ajax.php", filtros, function(response) {
                    reloadDataTable(response.data);
                }, "json");
            } else {
                mostrarAlerta("warning", data.message);
            }
        })
        .catch(error => mostrarAlerta("error", "Ocurrió un error al procesar la solicitud."));
    });

    function mostrarAlerta(tipo, mensaje) {
        Swal.fire({ position: "center", icon: tipo, title: mensaje, showConfirmButton: false, timer: 1500 });
    }

    document.getElementById('modalAgregarPersona').addEventListener('click', function() {
    $('#modalAgregarPersonas').modal('show');
    });
    $('#tblPersonas').on('click', '.btn-modificar', function() {
        let id_persona = $(this).attr('btnId');
        let propietario = 2;
        let operacion = "selectbyIdModificar";
        let formData = new FormData();
        formData.append("id_persona", id_persona);
        formData.append("propietario", propietario);
        formData.append("operacion", operacion);
        // console.log(formData)
        // return;
        $.ajax({
            type: 'POST',
            url: 'ajax/persona.ajax.php',
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            success: function(response) {
                // console.log(response.menor)
                // return;
                if (response.status === 'success') {
                    $('#idPersona').val(response.data.id_persona);
                    $('#txtPropietario').val(response.data.propietario);
                    $('#EditperDocument').val(response.data.documento);
                    $('#EditperDate').val(response.data.fecha_nacimiento);
                    $('#EditperName').val(response.data.nombres);
                    $('#EditperLastname').val(response.data.apellidos);
                    $('#EditperPhone').val(response.data.telefono);
                    $('#EditperSex').val(response.data.sexo);
                    $('#EditperFicha').val(response.data.nro_ficha);
                    $('#EditperAdrress').val(response.data.direccion);
                    $('#EditperEmail').val(response.data.email);
                    $('#EditperDpto').val(response.data.departamento);
                    $('#EditperCity').val(response.data.ciudad);
                    $('#EditperMenor').val(response.data.menor.toString());
                    $('#EditperTutor').val(response.data.tutor);
                    $('#EditperDocTutor').val(response.data.documento_tutor);
                    $('#modalEditarPersonas').modal('show');
                } else if (response.status === 'warning') {
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
                    title: "Error al realizar la búsqueda: " + error,
                    text: error,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });
    });
    $('#tblPersonas').on('click', '.btn-doctor', function() {
        let id_persona = $(this).attr('btnId');
        let propietario = 2;
        let operacion = "selectbyIdModificar";
        let formData = new FormData();
        formData.append("id_persona", id_persona);
        formData.append("propietario", propietario);
        formData.append("operacion", operacion);
        // console.log(formData)
        // return;
        $.ajax({
            type: 'POST',
            url: 'ajax/persona.ajax.php',
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            success: function(response) {
                // console.log(response.menor)
                // return;
                if (response.status === 'success') {
                    $('#idPersonaMed').val(response.data.id_persona);
                    $('#txtPropietarioMed').val(response.data.propietario);
                    $('#medDocument').val(response.data.documento);
                    $('#medDate').val(response.data.fecha_nacimiento);
                    $('#medName').val(response.data.nombres);
                    $('#medLastName').val(response.data.apellidos);
                    $('#modalAgregarMedico').modal('show');
                } else if (response.status === 'warning') {
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
                    title: "Error al realizar la búsqueda: " + error,
                    text: error,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });
    });
    var especialidades = [
        { "id": 1, "text": "Oftalmología General" },
        { "id": 2, "text": "Cirugía Refractiva" },
        { "id": 3, "text": "Retina y Vítreo" },
        { "id": 4, "text": "Glaucoma" },
        { "id": 5, "text": "Córnea y Enfermedades Externas" },
        { "id": 6, "text": "Oftalmología Pediátrica" },
        { "id": 7, "text": "Neuroftalmología" },
        { "id": 8, "text": "Órbita y Oculoplastia" },
        { "id": 9, "text": "Estrabismo" },
        { "id": 10, "text": "Uveítis" }
      ];
      
      $('#medEspecialidad').select2({
        data: especialidades,
        // placeholder: "Seleccione especialidades",
        allowClear: true
      });

    //   document.getElementById('btnCrearMedico').addEventListener('click', function() {
    //     const formData = new FormData(document.getElementById('personaEditarForm'));
    //     formData.append('operacion', 'editarPersona');
    //     formData.append('id_persona', $('#idPersona').val());
    //     formData.append('propietario', $('#txtPropietario').val());

    //     fetch('ajax/persona.ajax.php', {
    //         method: 'POST',
    //         body: formData
    //     })
    //     .then(response => response.json())
    //     .then(data => {
    //         if (data.success) {
    //             // mostrarAlerta("success", data.message);
    //             document.getElementById('personaEditarForm').reset();
    //             $('#modalEditarPersonas').modal('hide');
    //             // reloadDataTable(data);
    //             const filtros = {
    //                 documento: $('#validarDocumento').val().trim(),
    //                 nombre: $('#validarNombre').val().trim(),
    //                 apellido: $('#validarApellidos').val().trim(),
    //                 sexo: $('#validarSexo').val().trim(),
    //                 propietario: 2
    //             };
                
    //             $.post("ajax/personasDt.ajax.php", filtros, function(response) {
    //                 reloadDataTable(response.data);
    //             }, "json");
    //         } else {
    //             mostrarAlerta("warning", data.message);
    //         }
    //     })
    //     .catch(error => mostrarAlerta("error", "Ocurrió un error al procesar la solicitud."));
    // });
    document.getElementById('btnCrearMedico').addEventListener('click', function() {
        // Validar campos requeridos antes de enviar
        if (!validarFormularioMedico()) {
            return;
        }
    
        const formData = new FormData(document.getElementById('personaMedForm'));
        
        // Obtener especialidades seleccionadas (Select2 multiple)
        const especialidades = $('#medEspecialidad').val();
        if (especialidades && especialidades.length > 0) {
            formData.append('especialidades', JSON.stringify(especialidades));
        }
        
        // Agregar campos adicionales
        formData.append('operacion', 'crearMedico');
        formData.append('id_persona_med', $('#idPersonaMed').val());
        formData.append('propietario_med', $('#txtPropietarioMed').val());
    
        fetch('ajax/medico.ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarAlerta("success", data.message);
                document.getElementById('personaMedForm').reset();
                $('#modalEditarMedico').modal('hide');
                
                // Recargar datos si es necesario
                const filtros = {
                    documento: $('#medDocument').val().trim(),
                    propietario: $('#txtPropietarioMed').val()
                };
                
                $.post("ajax/medicosDt.ajax.php", filtros, function(response) {
                    reloadDataTable(response.data);
                }, "json");
            } else {
                mostrarAlerta("warning", data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta("error", "Ocurrió un error al procesar la solicitud.");
        });
    });
    
    // Función para validar el formulario de médico
    // function validarFormularioMedico() {
    //     let valido = true;
    //     const camposRequeridos = [
    //         'medProfesion', 'direccionCorp', 'emailCorp', 'nameCorp', 
    //         'rucCorp', 'whatsappCorp', 'EditperMenor'
    //     ];
    
    //     // Validar campos requeridos
    //     camposRequeridos.forEach(campo => {
    //         const elemento = document.getElementById(campo);
    //         if (!elemento.value.trim()) {
    //             elemento.classList.add('is-invalid');
    //             valido = false;
    //         } else {
    //             elemento.classList.remove('is-invalid');
    //         }
    //     });
    
    //     // Validar email profesional
    //     const email = document.getElementById('emailCorp').value.trim();
    //     if (email && !validarEmail(email)) {
    //         document.getElementById('emailCorp').classList.add('is-invalid');
    //         mostrarAlerta("warning", "Por favor ingrese un email profesional válido.");
    //         valido = false;
    //     }
    
    //     // Validar RUC (10 o 13 dígitos)
    //     const ruc = document.getElementById('rucCorp').value.trim();
    //     if (ruc && !/^[0-9]{10,13}$/.test(ruc)) {
    //         document.getElementById('rucCorp').classList.add('is-invalid');
    //         mostrarAlerta("warning", "El RUC debe tener entre 10 y 13 dígitos numéricos.");
    //         valido = false;
    //     }
    
    //     // Validar WhatsApp (formato ecuatoriano)
    //     const whatsapp = document.getElementById('whatsappCorp').value.trim();
    //     if (whatsapp && !/^09[0-9]{8}$/.test(whatsapp)) {
    //         document.getElementById('whatsappCorp').classList.add('is-invalid');
    //         mostrarAlerta("warning", "El número de WhatsApp debe comenzar con 09 y tener 10 dígitos.");
    //         valido = false;
    //     }
    
    //     // Validar al menos una especialidad seleccionada
    //     const especialidades = $('#medEspecialidad').val();
    //     if (!especialidades || especialidades.length === 0) {
    //         $('#medEspecialidad').next('.select2-container').find('.select2-selection').addClass('is-invalid');
    //         mostrarAlerta("warning", "Por favor seleccione al menos una especialidad.");
    //         valido = false;
    //     } else {
    //         $('#medEspecialidad').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
    //     }
    
    //     return valido;
    // }
    // Función para validar el formulario de médico (actualizada)
function validarFormularioMedico() {
    let valido = true;
    const camposRequeridos = [
        'medProfesion', 'direccionCorp', 'emailCorp', 'nameCorp', 
        'rucCorp', 'whatsappCorp', 'planMedico'
    ];

    // Validar campos requeridos
    camposRequeridos.forEach(campo => {
        const elemento = document.getElementById(campo);
        if (!elemento.value.trim()) {
            elemento.classList.add('is-invalid');
            valido = false;
        } else {
            elemento.classList.remove('is-invalid');
        }
    });

    // Validar email profesional
    const email = document.getElementById('emailCorp').value.trim();
    if (email && !validarEmail(email)) {
        document.getElementById('emailCorp').classList.add('is-invalid');
        mostrarAlerta("warning", "Por favor ingrese un email profesional válido.");
        valido = false;
    }

    // Validar RUC (10 o 13 dígitos)
    const ruc = document.getElementById('rucCorp').value.trim();
    if (ruc && !/^[0-9]{10,13}$/.test(ruc)) {
        document.getElementById('rucCorp').classList.add('is-invalid');
        mostrarAlerta("warning", "El RUC debe tener entre 10 y 13 dígitos numéricos.");
        valido = false;
    }

    // Validar WhatsApp (formato ecuatoriano)
    const whatsapp = document.getElementById('whatsappCorp').value.trim();
    if (whatsapp && !/^09[0-9]{8}$/.test(whatsapp)) {
        document.getElementById('whatsappCorp').classList.add('is-invalid');
        mostrarAlerta("warning", "El número de WhatsApp debe comenzar con 09 y tener 10 dígitos.");
        valido = false;
    }

    // Validar al menos una especialidad seleccionada
    const especialidades = $('#medEspecialidad').val();
    if (!especialidades || especialidades.length === 0) {
        $('#medEspecialidad').next('.select2-container').find('.select2-selection').addClass('is-invalid');
        mostrarAlerta("warning", "Por favor seleccione al menos una especialidad.");
        valido = false;
    } else {
        $('#medEspecialidad').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
    }

    // Validar plan médico seleccionado
    const planMedico = document.getElementById('planMedico').value;
    if (!planMedico) {
        document.getElementById('planMedico').classList.add('is-invalid');
        mostrarAlerta("warning", "Por favor seleccione un plan médico.");
        valido = false;
    } else {
        document.getElementById('planMedico').classList.remove('is-invalid');
        
        // Actualizar importe según el plan seleccionado
        actualizarImporteSegunPlan(planMedico);
    }

    // Validar que el importe sea un número válido
    const importe = document.getElementById('importeMed').value;
    if (!importe || isNaN(importe) || parseFloat(importe) <= 0) {
        document.getElementById('importeMed').classList.add('is-invalid');
        mostrarAlerta("warning", "El importe no es válido o no se ha calculado.");
        valido = false;
    } else {
        document.getElementById('importeMed').classList.remove('is-invalid');
    }

    return valido;
}

    // Función para validar formato de email
    function validarEmail(email) {
        const re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Función para mostrar alertas (debes implementarla según tu framework)
    function mostrarAlerta(tipo, mensaje) {
        // Implementación según tu sistema de alertas (Toast, SweetAlert, etc.)
        console.log(`[${tipo}] ${mensaje}`);
        // Ejemplo con SweetAlert:
        // Swal.fire({
        //     icon: tipo,
        //     title: mensaje,
        //     showConfirmButton: false,
        //     timer: 3000
        // });
    }
    // Función para actualizar el importe según el plan seleccionado
function actualizarImporteSegunPlan(plan) {
    let importe = 0;
    
    switch(plan) {
        case 'Personal':
            importe = 99000;
            break;
        case 'Básico':
            importe = 120000;
            break;
        case 'Profesional':
            importe = 180000;
            break;
        case 'Premiun':
            importe = 330000;
            break;
        default:
            importe = 0;
    }
    
    document.getElementById('importeMed').value = importe.toFixed(0);
}

// Event listener para actualizar el importe cuando cambia el plan
document.getElementById('planMedico').addEventListener('change', function() {
    const planSeleccionado = this.value;
    actualizarImporteSegunPlan(planSeleccionado);
});
});
