/**
 * Módulo para gestión de personas
 * Basado en la tabla rh_person
 */

// DataTable para mostrar la lista de personas
let tablaPersonas;

// Inicializar componentes cuando el documento esté listo
$(document).ready(function () {
  // Inicializar DataTable
  inicializarTabla();

  // Configurar eventos de botones usando jQuery
  $("#btnFiltrarPersonas").on("click", filtrarPersonas);
  $("#btnLimpiarPersonas").on("click", limpiarFiltros);
  $("#btnNuevaPersona").on("click", abrirModalNuevaPersona);

  // Configurar eventos de formularios
  $("#btnGuardarPersona").on("click", guardarPersona);
  $("#btnEditarPersona").on("click", actualizarPersona);
  $("#btnSubirFoto").on("click", function () {
    $("#inputFotoPerfil").click();
  });
  $("#btnEditSubirFoto").on("click", function () {
    $("#inputEditFotoPerfil").click();
  });

  // Configurar preview de imagen
  $("#inputFotoPerfil").on("change", mostrarPreviewImagen);
  $("#inputEditFotoPerfil").on("change", mostrarPreviewImagenEdit);

  // Configurar comportamiento para menores de edad
  $("#perMenor").on("change", toggleCamposTutor);
  $("#EditperMenor").on("change", toggleCamposTutorEdit);

  // Agregar un log para verificar que los eventos se han configurado
  console.log("Eventos configurados correctamente");
});

/**
 * Inicializa la tabla de personas con DataTables
 */
function inicializarTabla() {
  tablaPersonas = $("#tblPersonas").DataTable({
    ajax: {
      url: "api/persons",
      dataSrc: function (json) {
        console.log("Datos recibidos:", json);
            if (Array.isArray(json.data)) {
                return json.data;
            } else if (json.data?.data) {
                return json.data.data;
            }
            return [];
      },
    },
    columns: [
      { data: "person_id" },
      { data: "document_number" },
      { data: "first_name" },
      { data: "last_name" },
      {
        data: "birth_date",
        render: function (data) {
          return calcularEdad(data) + " años";
        },
      },
      { data: "record_number" },
      { data: "phone_number" },
      {
        data: "is_minor",
        render: function (data) {
          return data ? "SÍ" : "NO";
        },
      },
      { data: "guardian_name" },
      { data: "guardian_document" },
      {
        data: "is_active",
        render: function (data) {
          return data ? "Activo" : "Inactivo";
        },
      },
      {
        data: null,
        render: function (data) {
          const btnVer = `<button class="btn btn-info btn-sm btn-ver" btnId="${data.person_id}"><i class="fas fa-eye"></i></button>`;
          const btnEditar = `<button class="btn btn-primary btn-sm btn-modificar" btnId="${data.person_id}"><i class="fas fa-edit"></i></button>`;
          const btnEliminar = `<button class="btn btn-danger btn-sm btn-eliminar" btnId="${data.person_id}"><i class="fas fa-trash"></i></button>`;
          const btnActivar = data.is_active
            ? `<button class="btn btn-warning btn-sm btn-inactivar" btnId="${data.person_id}"><i class="fas fa-ban"></i></button>`
            : `<button class="btn btn-success btn-sm btn-activar" btnId="${data.person_id}"><i class="fas fa-check"></i></button>`;

          return `<div class="btn-group">${btnVer} ${btnEditar} ${btnEliminar} ${btnActivar}</div>`;
        },
      },
    ],
    responsive: true,
    language: {
      url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json",
    },
  });

  // Configurar eventos para los botones de acción
  $("#tblPersonas").on("click", ".btn-ver", verPersona);
  $("#tblPersonas").on("click", ".btn-modificar", editarPersona);
  $("#tblPersonas").on("click", ".btn-eliminar", eliminarPersona);
  $("#tblPersonas").on("click", ".btn-inactivar", cambiarEstadoPersona);
  $("#tblPersonas").on("click", ".btn-activar", cambiarEstadoPersona);
}

/**
 * Filtra la tabla de personas según los criterios ingresados
 */
function filtrarPersonas() {
  const filtros = {
    document: $("#validarDocumento").val().trim(),
    name: $("#validarNombre").val().trim(),
    lastname: $("#validarApellidos").val().trim(),
    record: $("#validarFicha").val().trim(),
    gender: $("#validarSexo").val() !== "0" ? $("#validarSexo").val() : "",
  };

  // Construir URL con parámetros de búsqueda
  let url = "api/persons/search?";
  for (const key in filtros) {
    if (filtros[key]) {
      url += `${key}=${encodeURIComponent(filtros[key])}&`;
    }
  }

  // Actualizar datos de la tabla
  tablaPersonas.ajax.url(url).load();
}

/**
 * Limpia los filtros de búsqueda
 */
function limpiarFiltros() {
  document.getElementById("validarDocumento").value = "";
  document.getElementById("validarNombre").value = "";
  document.getElementById("validarApellidos").value = "";
  document.getElementById("validarFicha").value = "";
  document.getElementById("validarSexo").value = "0";

  // Recargar tabla con todos los datos
  tablaPersonas.ajax.url("api/persons").load();
}

/**
 * Abre el modal para crear una nueva persona
 */
function abrirModalNuevaPersona() {
  console.log("Función abrirModalNuevaPersona ejecutada");

  try {
    // Limpiar formulario
    $("#personaForm")[0].reset();
    $("#previewFotoPerfil").attr("src", "view/dist/img/user-default.jpg");
    $("#previewFotoPerfil").show();

    // Ocultar campos de tutor por defecto
    $("#divTutor").hide();
    $("#divDocTutor").hide();

    // Mostrar modal usando jQuery
    $("#modalAgregarPersonas").modal("show");
    console.log("Modal mostrado correctamente");
  } catch (error) {
    console.error("Error al abrir el modal:", error);
  }
}

/**
 * Guarda una nueva persona
 */
function guardarPersona() {
  // Validar campos requeridos
  if (!validarFormularioPersona()) {
    return;
  }

  // Obtener datos del formulario
  const formData = new FormData(document.getElementById("personaForm"));

  // Preparar datos para enviar como JSON
  const personaData = {
    document_number: formData.get("perDocument"),
    birth_date: formData.get("perDate"),
    first_name: formData.get("perName"),
    last_name: formData.get("perLastname"),
    phone_number: formData.get("perPhone"),
    gender: formData.get("perSex"),
    record_number: formData.get("perFicha"),
    address: formData.get("perAdrress"),
    email: formData.get("perEmail"),
    department_id:
      formData.get("perDpto") !== "0"
        ? parseInt(formData.get("perDpto"))
        : null,
    city_id:
      formData.get("perCity") !== "0"
        ? parseInt(formData.get("perCity"))
        : null,
    is_minor: formData.get("perMenor") === "true",
    guardian_name:
      formData.get("perMenor") === "true" ? formData.get("perTutor") : null,
    guardian_document:
      formData.get("perMenor") === "true" ? formData.get("perDocTutor") : null,
    is_active: true,
  };

  // Enviar datos al servidor
  fetch("api/persons", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(personaData),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log(data);
      // Verificar la estructura de la respuesta
      if (
        data.status === "success" &&
        data.data &&
        typeof data.data === "object"
      ) {
        console.log("ID de persona:", data.data.person_id);
        const personId = data.data.person_id;
      
        // Si hay una foto para subir, hacerlo después de crear la persona
        const inputFoto = document.getElementById("inputFotoPerfil");
        if (inputFoto.files.length > 0) {
          subirFotoPerfil(personId, inputFoto.files[0]);
        } else {
          mostrarAlerta("success", "Persona guardada correctamente");
          $("#modalAgregarPersonas").modal("hide");
          tablaPersonas.ajax.reload();
        }
      } else {
        mostrarAlerta("error", data.message || "Error al guardar la persona");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al procesar la solicitud");
    });
}

/**
 * Carga los datos de una persona para edición
 */
function editarPersona() {
  const personId = $(this).attr("btnId");

  // Obtener datos de la persona
  fetch(`api/persons/show?id=${personId}`)
    .then((response) => response.json())
    .then((respuesta) => {
      console.log(respuesta);
      const data = respuesta.data;
      if (!data || !data.person_id) {
        mostrarAlerta("error", "Error al cargar los datos de la persona");
        return;
      }

      //let data = data.data;
      // Llenar formulario con datos
      document.getElementById("idPersona").value = data.person_id;
      document.getElementById("EditperDocument").value = data.document_number;
      document.getElementById("EditperDate").value = data.birth_date;
      document.getElementById("EditperName").value = data.first_name;
      document.getElementById("EditperLastname").value = data.last_name;
      document.getElementById("EditperPhone").value = data.phone_number;
      document.getElementById("EditperSex").value = data.gender;
      document.getElementById("EditperFicha").value = data.record_number;
      document.getElementById("EditperAdrress").value = data.address;
      document.getElementById("EditperEmail").value = data.email;
      document.getElementById("EditperDpto").value = data.department_id || "0";
      document.getElementById("EditperCity").value = data.city_id || "0";
      document.getElementById("EditperMenor").value = data.is_minor ? "true" : "false";
      document.getElementById("EditperTutor").value = data.guardian_name || "N/A";
      document.getElementById("EditperDocTutor").value = data.guardian_document || "";

      // Mostrar/ocultar campos de tutor
      toggleCamposTutorEdit();

      // Mostrar foto de perfil si existe
      const previewImg = document.getElementById("previewEditFotoPerfil");
      if (data.profile_photo) {
        previewImg.src = `view/uploads/profile/${data.profile_photo}`;
        previewImg.style.display = "block";
      } else {
        previewImg.src = "view/dist/img/user-default.jpg";
        previewImg.style.display = "block";
      }

      // Mostrar modal
      $("#modalEditarPersonas").modal("show");
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al cargar los datos de la persona");
    });
}

/**
 * Actualiza los datos de una persona
 */
function actualizarPersona() {
  // Validar campos requeridos
  if (!validarFormularioPersonaEdit()) {
    return;
  }

  const personId = document.getElementById("idPersona").value;

  // Obtener datos del formulario
  const formData = new FormData(document.getElementById("personaEditarForm"));

  // Preparar datos para enviar como JSON
  const personaData = {
    document_number: formData.get("EditperDocument"),
    birth_date: formData.get("EditperDate"),
    first_name: formData.get("EditperName"),
    last_name: formData.get("EditperLastname"),
    phone_number: formData.get("EditperPhone"),
    gender: formData.get("EditperSex"),
    record_number: formData.get("EditperFicha"),
    address: formData.get("EditperAdrress"),
    email: formData.get("EditperEmail"),
    department_id:
      formData.get("EditperDpto") !== "0"
        ? parseInt(formData.get("EditperDpto"))
        : null,
    city_id:
      formData.get("EditperCity") !== "0"
        ? parseInt(formData.get("EditperCity"))
        : null,
    is_minor: formData.get("EditperMenor") === "true",
    guardian_name:
      formData.get("EditperMenor") === "true"
        ? formData.get("EditperTutor")
        : null,
    guardian_document:
      formData.get("EditperMenor") === "true"
        ? formData.get("EditperDocTutor")
        : null,
  };

  // Enviar datos al servidor
  fetch(`api/persons?id=${personId}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(personaData),
  })
    .then((response) => response.json())
    .then((respuesta) => {
      const data = respuesta.data;
      if (data.person_id) {
        // Si hay una foto para subir, hacerlo después de actualizar la persona
        const inputFoto = document.getElementById("inputEditFotoPerfil");
        if (inputFoto.files.length > 0) {
          subirFotoPerfil(personId, inputFoto.files[0]);
        } else {
          mostrarAlerta("success", "Persona actualizada correctamente");
          $("#modalEditarPersonas").modal("hide");
          tablaPersonas.ajax.reload();
        }
      } else {
        mostrarAlerta(
          "error",
          data.message || "Error al actualizar la persona"
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al procesar la solicitud");
    });
}

/**
 * Elimina una persona
 */
function eliminarPersona() {
  const personId = $(this).attr("btnId");

  Swal.fire({
    title: "¿Está seguro?",
    text: "Esta acción no se puede revertir",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(`api/persons?id=${personId}`, {
        method: "DELETE",
      })
        .then((response) => response.json())
        .then((respuesta) => {
          const data = respuesta.data;
          if (data.message) {
            mostrarAlerta("success", "Persona eliminada correctamente");
            tablaPersonas.ajax.reload();
          } else {
            mostrarAlerta(
              "error",
              data.message || "Error al eliminar la persona"
            );
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          mostrarAlerta("error", "Error al procesar la solicitud");
        });
    }
  });
}

/**
 * Cambia el estado de una persona (activo/inactivo)
 */
function cambiarEstadoPersona() {
  const personId = $(this).attr("btnId");
  const activar = $(this).hasClass("btn-activar");

  // Obtener datos actuales de la persona
  fetch(`api/persons/show?id=${personId}`)
    .then((response) => response.json())
    .then((data) => {
      // Actualizar solo el estado
      const personaData = {
        is_active: activar,
      };

      // Enviar actualización
      return fetch(`api/persons?id=${personId}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(personaData),
      });
    })
    .then((response) => response.json())
    .then((respuesta) => {
      const data = respuesta.data;
      if (data.person_id) {
        mostrarAlerta(
          "success",
          `Persona ${activar ? "activada" : "desactivada"} correctamente`
        );
        tablaPersonas.ajax.reload();
      } else {
        mostrarAlerta(
          "error",
          data.message ||
            `Error al ${activar ? "activar" : "desactivar"} la persona`
        );
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al procesar la solicitud");
    });
}

/**
 * Muestra los detalles de una persona
 */
function verPersona() {
  const personId = $(this).attr("btnId");

  // Obtener datos de la persona
  fetch(`api/persons/show?id=${personId}`)
    .then((response) => response.json())
    .then((respuesta) => {
      const data = respuesta.data;
      // Construir HTML con los detalles
      let html = `
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="${
                      data.profile_photo
                        ? "view/uploads/profile/" + data.profile_photo
                        : "view/dist/img/user-default.jpg"
                    }" 
                         class="img-fluid rounded-circle mb-3" style="max-width: 150px;">
                    <h4>${data.first_name} ${data.last_name}</h4>
                    <p class="text-muted">${data.document_number}</p>
                </div>
                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tr>
                            <th>Edad</th>
                            <td>${calcularEdad(data.birth_date)} años</td>
                        </tr>
                        <tr>
                            <th>Ficha</th>
                            <td>${data.record_number || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Teléfono</th>
                            <td>${data.phone_number || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Género</th>
                            <td>${data.gender || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Dirección</th>
                            <td>${data.address || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>${data.email || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Es menor</th>
                            <td>${data.is_minor ? "Sí" : "No"}</td>
                        </tr>
                    </table>
                    ${
                      data.is_minor
                        ? `
                    <table class="table table-bordered">
                        <tr>
                            <th colspan="2" class="bg-light">Información del tutor</th>
                        </tr>
                        <tr>
                            <th>Nombre del tutor</th>
                            <td>${data.guardian_name || "N/A"}</td>
                        </tr>
                        <tr>
                            <th>Documento del tutor</th>
                            <td>${data.guardian_document || "N/A"}</td>
                        </tr>
                    </table>
                    `
                        : ""
                    }
                </div>
            </div>
        `;

      // Mostrar modal con detalles
      Swal.fire({
        title: "Detalles de la persona",
        html: html,
        width: "800px",
        showCloseButton: true,
        showConfirmButton: false,
      });
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta("error", "Error al cargar los detalles de la persona");
    });
}

/**
 * Sube una foto de perfil para una persona
 */
function subirFotoPerfil(personId, file) {
  const formData = new FormData();
  formData.append("profile_photo", file);

  fetch(`api/persons/upload-photo?id=${personId}`, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((respuesta) => {
      const data = respuesta.data;
      if (data.message) {
        mostrarAlerta(
          "success",
          "Persona guardada y foto subida correctamente"
        );
        $("#modalAgregarPersonas").modal("hide");
        $("#modalEditarPersonas").modal("hide");
        tablaPersonas.ajax.reload();
      } else {
        mostrarAlerta(
          "warning",
          data.message ||
            "La persona se guardó pero hubo un error al subir la foto"
        );
        $("#modalAgregarPersonas").modal("hide");
        $("#modalEditarPersonas").modal("hide");
        tablaPersonas.ajax.reload();
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      mostrarAlerta(
        "warning",
        "La persona se guardó pero hubo un error al subir la foto"
      );
      $("#modalAgregarPersonas").modal("hide");
      $("#modalEditarPersonas").modal("hide");
      tablaPersonas.ajax.reload();
    });
}

/**
 * Muestra una vista previa de la imagen seleccionada
 */
function mostrarPreviewImagen() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("previewFotoPerfil").src = e.target.result;
      document.getElementById("previewFotoPerfil").style.display = "block";
    };
    reader.readAsDataURL(file);
  }
}

/**
 * Muestra una vista previa de la imagen seleccionada en el formulario de edición
 */
function mostrarPreviewImagenEdit() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("previewEditFotoPerfil").src = e.target.result;
      document.getElementById("previewEditFotoPerfil").style.display = "block";
    };
    reader.readAsDataURL(file);
  }
}

/**
 * Muestra/oculta los campos de tutor según si es menor de edad
 */
function toggleCamposTutor() {
  const esmenor = document.getElementById("perMenor").value === "true";
  document.getElementById("divTutor").style.display = esmenor
    ? "block"
    : "none";
  document.getElementById("divDocTutor").style.display = esmenor
    ? "block"
    : "none";
}

/**
 * Muestra/oculta los campos de tutor en el formulario de edición
 */
function toggleCamposTutorEdit() {
  const esmenor = document.getElementById("EditperMenor").value === "true";
  document.getElementById("divEditTutor").style.display = esmenor
    ? "block"
    : "none";
  document.getElementById("divEditDocTutor").style.display = esmenor
    ? "block"
    : "none";
}

/**
 * Valida los campos requeridos del formulario de persona
 */
function validarFormularioPersona() {
  const documento = document.getElementById("perDocument").value;
  const fecha = document.getElementById("perDate").value;
  const nombre = document.getElementById("perName").value;
  const apellido = document.getElementById("perLastname").value;
  const sexo = document.getElementById("perSex").value;

  if (!documento || !fecha || !nombre || !apellido || !sexo) {
    mostrarAlerta(
      "warning",
      "Por favor complete todos los campos obligatorios"
    );
    return false;
  }

  // Validar campos de tutor si es menor
  const esmenor = document.getElementById("perMenor").value === "true";
  if (esmenor) {
    const tutor = document.getElementById("perTutor").value;
    const docTutor = document.getElementById("perDocTutor").value;

    if (tutor === "N/A" || !docTutor) {
      mostrarAlerta(
        "warning",
        "Para menores de edad, debe completar la información del tutor"
      );
      return false;
    }
  }

  return true;
}

/**
 * Valida los campos requeridos del formulario de edición de persona
 */
function validarFormularioPersonaEdit() {
  const documento = document.getElementById("EditperDocument").value;
  const fecha = document.getElementById("EditperDate").value;
  const nombre = document.getElementById("EditperName").value;
  const apellido = document.getElementById("EditperLastname").value;
  const sexo = document.getElementById("EditperSex").value;

  if (!documento || !fecha || !nombre || !apellido || !sexo) {
    mostrarAlerta(
      "warning",
      "Por favor complete todos los campos obligatorios"
    );
    return false;
  }

  // Validar campos de tutor si es menor
  const esmenor = document.getElementById("EditperMenor").value === "true";
  if (esmenor) {
    const tutor = document.getElementById("EditperTutor").value;
    const docTutor = document.getElementById("EditperDocTutor").value;

    if (tutor === "N/A" || !docTutor) {
      mostrarAlerta(
        "warning",
        "Para menores de edad, debe completar la información del tutor"
      );
      return false;
    }
  }

  return true;
}

/**
 * Calcula la edad a partir de una fecha de nacimiento
 */
function calcularEdad(fechaNacimiento) {
  const fechaNac = new Date(fechaNacimiento);
  const hoy = new Date();
  let edad = hoy.getFullYear() - fechaNac.getFullYear();
  const mes = hoy.getMonth() - fechaNac.getMonth();

  if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
    edad--;
  }

  return edad;
}

/**
 * Muestra una alerta con SweetAlert2
 */
function mostrarAlerta(tipo, mensaje) {
  Swal.fire({
    position: "center",
    icon: tipo,
    title: mensaje,
    showConfirmButton: false,
    timer: 1500,
  });
}
