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
                document.getElementById('txtCantConsulta').textContent = response.cantidad || '0';
                document.getElementById('txtUltConsulta').textContent = response.ultima || 'Sin consultas';
            } else {
                document.getElementById('txtCantConsulta').textContent = '0';
                document.getElementById('txtUltConsulta').textContent = 'Sin consultas';
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener resumen de consulta:", error);
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
}

/**
 * Función para guardar la consulta
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
    
    $.ajax({
        type: 'POST',
        url: 'ajax/guardar-consulta.ajax.php',
        data: formData,
        dataType: "text",
        processData: false,
        contentType: false,
        success: function(response) {
            if (response === "ok") {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Consulta guardada correctamente",
                    showConfirmButton: false,
                    timer: 1500
                });
                
                // Actualizar información después de guardar
                obtenerResumenConsulta(idPersona);
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
    
    // Enviar el formulario
    const formData = new FormData(document.getElementById('uploadForm'));
    
    $.ajax({
        type: 'POST',
        url: 'ajax/upload.ajax.php',
        data: formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(response) {
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
            } else {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error al subir los archivos",
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function(xhr, status, error) {
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