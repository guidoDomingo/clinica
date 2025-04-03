document.addEventListener("DOMContentLoaded", function() {
const motivosConsultaOculares = [
  "Dolor ocular",
  "Visión borrosa",
  "Ojo rojo",
  "Sensibilidad a la luz (fotofobia)",
  "Lagrimeo excesivo",
  "Sequedad ocular",
  "Picazón o comezón en los ojos",
  "Cuerpo extraño en el ojo",
  "Disminución de la visión",
  "Visión doble (diplopía)",
  "Dolor de cabeza asociado a la visión",
  "Ojos hinchados",
  "Secreción ocular",
  "Ojos llorosos",
  "Problemas con lentes de contacto",
  "Dificultad para ver de noche",
  "Manchas o moscas volantes",
  "Dolor alrededor de los ojos",
  "Cambios en la percepción de colores",
  "Ojos cruzados (estrabismo)"
];


// Seleccionar el elemento <select> por su ID
const motivoConsulta = document.querySelector('#motivoscomunes');

if (motivoConsulta) {
  motivosConsultaOculares.forEach(motivo => {
    const optionElement = document.createElement('option');
    optionElement.value = motivo;
    optionElement.textContent = motivo;
    motivoConsulta.appendChild(optionElement);
  });
} else {
  console.warn('Elemento con id "motivoscomunes" no encontrado.');
}
 
  const formatoConsulta = [
    "Sin formato",
    "Visión borrosa"
  ];

  const selectFormato = document.querySelector('#formatoConsulta');

  if (selectFormato) {
    formatoConsulta.forEach(motivo => {
      const optionElement = document.createElement('option');
      optionElement.value = motivo;
      optionElement.textContent = motivo;
      selectFormato.appendChild(optionElement);
    });
  } 
 

  const formatoReceta = [
    "Sin formato",
    "Alergias",
    "Chalazium"  
  ];
  const selectFormatoReceta = document.querySelector('#formatoreceta');

  if (formatoReceta) {
    formatoReceta.forEach(motivo => {
      const optionElement = document.createElement('option');
      optionElement.value = motivo;
      optionElement.textContent = motivo;
      selectFormatoReceta.appendChild(optionElement);
    });
  }
 
  $('#btnGuardarConsulta').click(function(e) {
      e.preventDefault(); // Evita el comportamiento predeterminado del botón

      let idPersona = $('#idPersona').val().trim(); // Obtener el valor del campo oculto

      if (!idPersona) {
          Swal.fire({
              position: "center",
              icon: "error",
              title: "Error",
              text: "El campo 'Persona' es obligatorio.",
              showConfirmButton: true
          });
          return; // Detiene la ejecución si el campo está vacío
      }

      Swal.fire({
          title: "¿Estás seguro?",
          text: "¿Deseas guardar la consulta?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Sí, guardar",
          cancelButtonText: "Cancelar"
      }).then((result) => {
          if (result.isConfirmed) {
              var formData = new FormData($('#tblConsulta')[0]); // Serializa los datos del formulario

              $.ajax({
                  type: 'POST',
                  url: 'ajax/guardar-consulta.ajax.php', // URL del backend
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
                          // Puedes recargar la tabla o limpiar el formulario si es necesario
                          // $('#tablaDatos').DataTable().ajax.reload();
                          // $('#tblConsulta')[0].reset();
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
          }
      });
  });
// });


document.getElementById('btnBuscarPersona').addEventListener('click', function() {
  var documento = document.getElementById('txtdocumento').value;
  var ficha = document.getElementById('txtficha').value;
  buscar(documento, ficha);
});

function buscar(parametro1, parametro2) {
  // Obtener el valor del campo de nombres (si es necesario)
  var txtnombres = document.getElementById("paciente").value;

  // Llamar a la función buscarPaciente con los valores obtenidos
  buscarPaciente(parametro1, parametro2, txtnombres);
}


function buscarPaciente(txtdocumento, txtficha, txtnombres) {
   // Validar que al menos uno de los parámetros tenga valor
   if (!txtdocumento && !txtficha && !txtnombres) {
    Swal.fire({
      position: "top-end",
      icon: "warning",
      title: "Debe ingresar al menos un criterio de búsqueda.",
      showConfirmButton: false,
      timer: 1300
    });
    return; // Detener la ejecución de la función si no hay criterios de búsqueda
  }
  var formData = new FormData();
  formData.append("documento", txtdocumento);
  formData.append("nro_ficha", txtficha);
  formData.append("nombres", txtnombres);
  formData.append("operacion", "buscarparam");

  $.ajax({
    type: 'POST',
    url: 'ajax/persona.ajax.php',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      // console.log(response);

      try {
        // Parsear la respuesta JSON
        var data = JSON.parse(response);

        // if (data.status === 'success' && Array.isArray(data.data) && data.data.length > 0) {
          if (data.status === 'success' && typeof data.data === 'object' && data.data !== null) {
          
          // Cargar los datos en los campos correspondientes
          document.getElementById('idPersona').value = data.data.id_persona || '';
          document.getElementById('txtdocumento').value = data.data.documento || '';
          document.getElementById('txtficha').value = data.data.nro_ficha || '';
          document.getElementById('paciente').value = data.data.nombres+" "+data.data.apellidos || '';
          document.getElementById('id_persona_file').value = data.data.id_persona || '';

          
          //Carga los datos en el timeline
          buscarConsultas(data.data.id_persona);
          buscarConsultaIdPersona(data.data.id_persona);
          // Obtén el elemento <h3> por su id
          const profileUsername = document.getElementById('profile-username'); 
          const profileCi = document.getElementById('profile-ci');   
          // // Actualiza el contenido del <h3> con el nombre y apellido
          if (data.data.nombres && data.data.apellidos) {
              profileUsername.textContent = `${data.data.nombres} ${data.data.apellidos}`;
          } else {
              profileUsername.textContent = ''; // Si no hay datos, deja el contenido vacío
          }
          if (data.data.documento) {
            profileCi.textContent = `${data.data.documento}`;
        } else {
          profileCi.textContent = ''; // Si no hay datos, deja el contenido vacío
        }
         //Carga cuota de archivos
        buscarArchivos(data.data.id_persona) ;
        } else if (data.status === 'warning') {
          Swal.fire({
            position: "top-end",
            icon: "warning",
            title: data.message,
            showConfirmButton: false,
            timer: 1300
          });
          limpiarBuscarPersona();
          
        } else {
          Swal.fire({
            position: "top-end",
            icon: "warning",
            title: "No se encontraron resultados.",
            showConfirmButton: false,
            timer: 1300
          });
          
          document.getElementById('txtficha').value = "";
          document.getElementById('paciente').value = "";
          document.getElementById('idPersona').value = "";
          document.getElementById('id_persona_file').value = "";
          
        }
      } catch (error) {
        Swal.fire({
          position: "top-end",
          icon: "center",
          title: "Error al procesar la respuesta del servidor.",
          showConfirmButton: false,
          timer: 1300
        });
        
        document.getElementById('txtficha').value = "";
        document.getElementById('paciente').value = "";
        document.getElementById('idPersona').value = "";
        // Manejar errores de parsing JSON
        // console.error('Error al parsear la respuesta JSON:', error);
        
      }
    },
    error: function(xhr, status, error) {
      Swal.fire({
        position: "center",
        icon: "warning",
        title: "Error al realizar la búsqueda: " + error,
        text: error,
        showConfirmButton: false,
        timer: 1500
      });
    }
  });
}

document.getElementById('btnLimpiarPersona').addEventListener('click', function() {
  limpiarBuscarPersona();
});
function limpiarBuscarPersona() { 
  document.getElementById('idPersona').value = ""; 
  document.getElementById('paciente').value = "";
  document.getElementById('txtficha').value = "";
  document.getElementById('txtdocumento').value = "";
  document.getElementById('txtdocumento').focus();
  document.getElementById('id_persona_file').value = "";

  const txtCantConsulta = document.getElementById('txtCantConsulta');
  const txtUltConsulta = document.getElementById('txtUltConsulta');
  txtCantConsulta.textContent = '';
  txtUltConsulta.textContent ='';
  const cuotaValorElement = document.getElementById('cuota-valor');
  cuotaValorElement.textContent = ''; 
  const profileUsername = document.getElementById('profile-username'); 
  const profileCi = document.getElementById('profile-ci'); 
  profileUsername.textContent = '';
  profileCi.textContent = '';
  $('#timeline').empty();
      
  
}



function buscarConsultas(persona) {
  var formData = new FormData();
  formData.append("id_persona", persona);
  formData.append("operacion", "buscarConsultaPersona");

  $.ajax({
      type: 'POST',
      url: 'ajax/consultas.ajax.php',
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        // console.log(response);
        // return;
          try {
              var data = JSON.parse(response);

              if (data.status === 'success') {
                  // Limpiar el contenedor de consultas
                  $('#timeline').empty();

                  // Recorrer las consultas y agregarlas al timeline
                  data.data.forEach(function(consulta) {
                      var consultaHtml = `
                      <div class="timeline timeline-inverse">
                          <div>
                              <i class="fas fa-envelope bg-primary"></i>
                              <div class="timeline-item">
                                  <span class="time"><i class="far fa-clock"></i> ${consulta.fecha_registro}</span>
                                  <h3 class="timeline-header"><a href="#">Consulta #${consulta.id_consulta}</a></h3>
                                  <div class="timeline-body">
                                      <strong>Motivo:</strong> ${consulta.motivoscomunes}<br>
                                      <strong>Nota:</strong> ${consulta.txtnota}<br>
                                      <strong>Próxima consulta:</strong> ${consulta.proximaconsulta}
                                  </div>
                                  <div class="timeline-footer">
                                      <a href="#" class="btn btn-primary btn-sm idConsulta="${consulta.id_consulta}">Ver</a>
                                      <a href="#" class="btn btn-warning btn-sm idConsulta="${consulta.id_consulta}"">Modificar</a>
                                  </div>
                                  <div class="post clearfix">
                                    <form class="form-horizontal">
                                      <div class="input-group input-group-sm mb-0">
                                        <input class="form-control form-control-sm" placeholder="Agregar comentario">
                                        <div class="input-group-append">
                                          <button type="button" class="btn btn-success">Send</button>
                                        </div>
                                      </div>
                                    </form>
                                  </div>
                              </div>
                              
                          </div>
                          
                          </div>
                      `;
                      $('#timeline').append(consultaHtml);
                  });
              } else if (data.status === 'warning') {
                  Swal.fire({
                      icon: 'warning',
                      title: 'Aviso',
                      text: data.message,
                      confirmButtonText: 'Aceptar',
                      timer: 5000,
                      timerProgressBar: true
                  });
              } else {
                  Swal.fire({
                      icon: 'error',
                      title: 'Error',
                      text: data.message || 'Error desconocido al obtener las consultas.',
                      confirmButtonText: 'Aceptar',
                      timer: 5000,
                      timerProgressBar: true
                  });
              }
          } catch (error) {
              console.error('Error al procesar la respuesta:', error);
              Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'Error al procesar la respuesta del servidor.',
                  confirmButtonText: 'Aceptar',
                  timer: 5000,
                  timerProgressBar: true
              });
          }
      },
      error: function(xhr, status, error) {
          Swal.fire({
              position: "center",
              icon: "warning",
              title: "Error al realizar la búsqueda: " + error,
              text: error,
              showConfirmButton: false,
              timer: 1500
          });
      }
  });
}
});//cierre de document laoded
   
const btnSubirArchivos = document.getElementById('btnSubirArchivos');

if (btnSubirArchivos) {
    btnSubirArchivos.addEventListener('click', function() {
        // Obtener el valor del campo oculto
        let idPersona = document.getElementById('id_persona_file')?.value.trim() || '';

        // Validar que el campo oculto tenga valor
        if (!idPersona) {
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Por favor, seleccione una persona 😊",
                showConfirmButton: false,
                timer: 1500
            });
            volverAlTop("container-fluid");
            return; // Detiene la ejecución si el campo está vacío
        }

        const fileInput = document.getElementById('files');
        const errorDiv = document.getElementById('error');
        
        if (!fileInput || !errorDiv) {
            console.warn('Elementos del formulario no encontrados');
            return;
        }

        errorDiv.textContent = '';

        // Tipos de archivo permitidos
        const allowedTypes = [
            'application/pdf', // PDF
            'image/jpeg',      // JPG
            'image/png',       // PNG
            'image/gif',       // GIF
            'image/webp',     // WEBP
            'image/svg+xml'    // SVG
        ];

        // Tamaño máximo permitido (25 MB)
        const maxSize = 25 * 1024 * 1024; // 25 MB

        if (fileInput.files.length === 0) {
            Swal.fire({
                position: "center",
                icon: "info",
                title: 'Por favor, selecciona al menos un archivo 😊',
                text: "",
                showConfirmButton: false,
                timer: 1500
            });
        } else {
            let valid = true;
            for (let i = 0; i < fileInput.files.length; i++) {
                const file = fileInput.files[i];

                // Validar tipo de archivo
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        position: "center",
                        icon: "warning",
                        title: 'El archivo ' + file.name + ' no es un PDF o una imagen válida (JPEG, PNG, GIF, WEBP, SVG).',
                        text: file.name,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    valid = false;
                    break;
                }

                // Validar tamaño del archivo
                if (file.size > maxSize) {
                    Swal.fire({
                        position: "center",
                        icon: "warning",
                        title: 'El archivo ' + file.name + ' excede el tamaño máximo de 25 MB.',
                        text: file.name,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    valid = false;
                    break;
                }
            }

            // Si todos los archivos son válidos, enviar el formulario
            if (valid) {
                const formData = new FormData(document.getElementById('uploadForm'));
                // Agregar el valor de idPersona al FormData
                formData.append('id_persona', idPersona);
                formData.append('id_usuario', "1");

                fetch('ajax/upload.ajax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "Archivos subidos correctamente.",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    volverAlTop("container-fluid");
                })
                .catch(error => {
                    Swal.fire({
                        position: "center",
                        icon: "warning",
                        title: "Hubo un error al subir los archivos: " + error,
                        text: error,
                        showConfirmButton: false,
                        timer: 1500
                    });
                });
            }
        }
});
function volverAlTop(idDelDiv) {
  const div = document.getElementById(idDelDiv); // Obtén el div por su ID
  if (div) {
      div.scrollIntoView({
          behavior: 'smooth' // Desplazamiento suave
      });
  } else {
      console.error(`No se encontró el div con ID: ${idDelDiv}`);
  }
}

function buscarConsultaIdPersona(persona) {
  var formData = new FormData();
  formData.append("id_persona", persona);
  formData.append("operacion", "resumenConsulta");
  $.ajax({
    type: 'POST',
    url: 'ajax/consultas.ajax.php',
    data: formData,
    dataType: "json",
    processData: false,
    contentType: false,
    success: function(response) { 
      const txtCantConsulta = document.getElementById('txtCantConsulta');
      const txtUltConsulta = document.getElementById('txtUltConsulta');
      if (response) {        
        txtCantConsulta.textContent = response.cantidad_consultas+' veces'; // Actualizar el valor
        txtUltConsulta.textContent = response.maxima_fecha_registro; // Actualizar el valor
      } else{
        txtCantConsulta.textContent = '0';
        txtUltConsulta.textContent ='Primera consulta';
      }
      

    }
  });
}