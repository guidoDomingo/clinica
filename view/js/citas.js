document.addEventListener("DOMContentLoaded", function() {
// Lista de servicios en formato JSON
const servicios = [
    {"id": 1, "descripcion": "Consulta oftalmológica general", "importe": 50.00},
    {"id": 2, "descripcion": "Examen de agudeza visual", "importe": 30.00},
    {"id": 3, "descripcion": "Tonometría (medición de presión ocular)", "importe": 40.00},
    {"id": 4, "descripcion": "Fondo de ojo", "importe": 60.00},
    {"id": 5, "descripcion": "Topografía corneal", "importe": 80.00},
    {"id": 6, "descripcion": "Retinografía", "importe": 70.00},
    {"id": 7, "descripcion": "Cirugía de cataratas", "importe": 1500.00},
    {"id": 8, "descripcion": "Cirugía refractiva (LASIK)", "importe": 2000.00},
    {"id": 9, "descripcion": "Tratamiento de glaucoma", "importe": 120.00},
    {"id": 10, "descripcion": "Revisión postoperatoria", "importe": 25.00}
];

// Función para cargar los servicios en el select
function cargarServicios() {
    const selectServicios = $("#ctservicios"); // Obtener el select con jQuery

    // Limpiar opciones existentes (excepto la primera)
    selectServicios.empty().append('<option selected="selected">Seleccionar</option>');

    // Recorrer la lista de servicios y agregar opciones al select
    servicios.forEach(servicio => {
        const option = new Option(
            `${servicio.descripcion} - $${servicio.importe.toFixed(2)}`, // Texto del option
            servicio.id // Valor del option (id del servicio)
        );
        selectServicios.append(option); // Agregar la opción al select
    });

    // Inicializar Select2 en el select
    selectServicios.select2({
        theme: 'bootstrap4', // Usar el tema de Bootstrap 4
        placeholder: "Seleccione un servicio", // Placeholder
        allowClear: true // Permitir limpiar la selección
    });
}

// Llamar a la función para cargar los servicios cuando el documento esté listo
$(document).ready(function() {
    cargarServicios();
});

// document.getElementById("tblCitas").addEventListener("keydown", function(event) {
//     if (event.key === "Enter" && event.target.tagName !== "TEXTAREA") {
//         event.preventDefault();
//     }
// });
let tblCitas = document.getElementById("tblCitas");
if (tblCitas) {
    tblCitas.addEventListener("keydown", function(event) {
        if (event.key === "Enter" && event.target.tagName !== "TEXTAREA") {
            event.preventDefault();
        }
    });
} else {
    console.error('Elemento con id "tblCitas" no encontrado.');
}


 // Lista de médicos en formato JSON
 const medicos = [
    {"id": 1, "nombre": "Dr. Juan Pérez", "horario_atencion": "Lunes a Viernes, 8:00 AM - 12:00 PM"},
    {"id": 2, "nombre": "Dra. María Gómez", "horario_atencion": "Lunes a Jueves, 2:00 PM - 6:00 PM"},
    {"id": 3, "nombre": "Dr. Carlos López", "horario_atencion": "Martes y Jueves, 9:00 AM - 1:00 PM"},
    {"id": 4, "nombre": "Dra. Ana Martínez", "horario_atencion": "Miércoles y Viernes, 10:00 AM - 2:00 PM"},
    {"id": 5, "nombre": "Dr. Luis Rodríguez", "horario_atencion": "Lunes a Viernes, 4:00 PM - 8:00 PM"},
    {"id": 6, "nombre": "Dra. Sofía Fernández", "horario_atencion": "Lunes, Miércoles y Viernes, 7:00 AM - 11:00 AM"},
    {"id": 7, "nombre": "Dr. Pedro Sánchez", "horario_atencion": "Martes y Jueves, 3:00 PM - 7:00 PM"},
    {"id": 8, "nombre": "Dra. Laura Díaz", "horario_atencion": "Lunes a Viernes, 1:00 PM - 5:00 PM"},
    {"id": 9, "nombre": "Dr. Jorge Ramírez", "horario_atencion": "Miércoles y Viernes, 8:00 AM - 12:00 PM"},
    {"id": 10, "nombre": "Dra. Carmen Ruiz", "horario_atencion": "Lunes a Jueves, 9:00 AM - 1:00 PM"}
];

// Obtener el elemento <select>
const selectMedicos = document.getElementById("cttratante");

// Cargar las opciones en el <select>
medicos.forEach(medico => {
    const option = document.createElement("option");
    option.value = medico.id; // Valor del option (id del médico)
    option.textContent = `${medico.nombre} - ${medico.horario_atencion}`; // Texto del option
    selectMedicos.appendChild(option);
});

const rtte = [
    {"id": 1, "nombre": "Dr. Juan Pérez", "horario_atencion": "Lunes a Viernes, 8:00 AM - 12:00 PM"},
    {"id": 2, "nombre": "Dra. María Gómez", "horario_atencion": "Lunes a Jueves, 2:00 PM - 6:00 PM"},
    {"id": 3, "nombre": "Dr. Carlos López", "horario_atencion": "Martes y Jueves, 9:00 AM - 1:00 PM"},
    {"id": 4, "nombre": "Dra. Ana Martínez", "horario_atencion": "Miércoles y Viernes, 10:00 AM - 2:00 PM"},
    {"id": 5, "nombre": "Dr. Luis Rodríguez", "horario_atencion": "Lunes a Viernes, 4:00 PM - 8:00 PM"},
    {"id": 6, "nombre": "Dra. Sofía Fernández", "horario_atencion": "Lunes, Miércoles y Viernes, 7:00 AM - 11:00 AM"},
    {"id": 7, "nombre": "Dr. Pedro Sánchez", "horario_atencion": "Martes y Jueves, 3:00 PM - 7:00 PM"},
    {"id": 8, "nombre": "Dra. Laura Díaz", "horario_atencion": "Lunes a Viernes, 1:00 PM - 5:00 PM"},
    {"id": 9, "nombre": "Dr. Jorge Ramírez", "horario_atencion": "Miércoles y Viernes, 8:00 AM - 12:00 PM"},
    {"id": 10, "nombre": "Dra. Carmen Ruiz", "horario_atencion": "Lunes a Jueves, 9:00 AM - 1:00 PM"}
];
const selectMedicosRtte = document.getElementById("ctremitente");
// Cargar las opciones en el <select>
rtte.forEach(medico => {
    const option = document.createElement("option");
    option.value = medico.id; // Valor del option (id del médico)
    option.textContent = `${medico.nombre}`; // Texto del option
    selectMedicosRtte.appendChild(option);
});

 // Lista de seguros médicos en formato JSON
 const seguros = [
    {"id": 1, "denominacion": "Seguro Salud Total", "planes": ["Básico", "Premium", "Familiar"]},
    {"id": 2, "denominacion": "MediCare Plus", "planes": ["Individual", "Empresarial", "Oro"]},
    {"id": 3, "denominacion": "Salud Segura", "planes": ["Estándar", "Plus", "VIP"]},
    {"id": 4, "denominacion": "Vida y Salud", "planes": ["Básico", "Avanzado", "Familiar"]},
    {"id": 5, "denominacion": "Plan Médico Nacional", "planes": ["Individual", "Familiar", "Empresarial"]},
    {"id": 6, "denominacion": "Salud en Casa", "planes": ["Básico", "Intermedio", "Completo"]},
    {"id": 7, "denominacion": "Seguro Médico Integral", "planes": ["Estándar", "Premium", "Familiar"]},
    {"id": 8, "denominacion": "Salud Primera", "planes": ["Básico", "Plus", "VIP"]},
    {"id": 9, "denominacion": "Plan Salud Familiar", "planes": ["Familiar Básico", "Familiar Plus", "Familiar Premium"]},
    {"id": 10, "denominacion": "Seguro Médico Global", "planes": ["Individual", "Familiar", "Empresarial"]}
];

// Obtener el elemento <select>
const selectSeguros = document.getElementById("ctseguro");

// Cargar las opciones en el <select>
seguros.forEach(seguro => {
    const option = document.createElement("option");
    option.value = seguro.id; // Valor del option (id del seguro)
    option.textContent = `${seguro.denominacion} - Planes: ${seguro.planes.join(", ")}`; // Texto del option
    selectSeguros.appendChild(option);
});


$(document).ready(function() {
    // Escuchar el clic en el botón "Guardar"
    $('#btnsavecitas').click(function(e) {
        e.preventDefault(); // Evita cualquier comportamiento por defecto del botón

        // Validación básica de campos obligatorios
        if ($('#ctcedula').val() === '' || $('#ctnombres').val() === '' || $('#ctservicios').val() === 'Seleccionar' || $('#cttratante').val() === 'Seleccionar') {
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Por favor, complete todos los campos obligatorios",
                showConfirmButton: false,
                timer: 1500
            });
            return false;
        }

        var formData = new FormData($('#tblCitas')[0]); // Serializa los datos del formulario, incluyendo archivos

        $.ajax({
            type: 'POST',
            url: 'ajax/guardar-citas.ajax.php', // Cambia esta URL por la que corresponda a tu backend
            data: formData,
            processData: false, // No procesar los datos
            contentType: false, // No establecer el tipo de contenido
            success: function(response) {
                if (response == "ok") {
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Registro exitoso!",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    // Aquí puedes recargar una tabla o realizar otras acciones necesarias
                    // tbFallas.ajax.reload(null, false);
                    // resetFormAgregarLinea();
                    // closeModal();
                } else {
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error en el registro",
                        text: response,
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    position: "center",
                    icon: "warning",
                    title: "Error en la solicitud",
                    text: error,
                    showConfirmButton: false,
                    timer: 1500
                });
                console.error('Error al enviar el formulario:', error);
            }
        });
    });
});
});//cierre de document laoded