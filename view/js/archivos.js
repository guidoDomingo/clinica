function buscarArchivos(persona) {
    var formData = new FormData();
    formData.append("id_persona", persona);
    formData.append("operacion", "mega");
    $.ajax({
      type: 'POST',
      url: 'ajax/archivos.ajax.php',
      data: formData,
      dataType: "json",
      processData: false,
      contentType: false,
      success: function(response) {
        const cuotaValorElement = document.getElementById('cuota-valor');
        if (response) {        
          cuotaValorElement.textContent = response.cuota; // Actualizar el valor
        } else {
          cuotaValorElement.textContent = '0'; // Actualizar el valor
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