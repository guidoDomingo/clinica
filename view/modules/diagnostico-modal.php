<!-- Modal para selección de diagnóstico -->
<div class="modal fade" id="diagnosticoModal" tabindex="-1" aria-labelledby="diagnosticoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="diagnosticoModalLabel">Selección de código y diagnóstico</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Busque y seleccione un diagnóstico en la herramienta de codificación, luego copie el código y la descripción</p>
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label for="selected-code" class="form-label"><strong>Código ICD-11:</strong></label>
              <div class="input-group">
                <input type="text" id="selected-code" class="form-control" placeholder="Ej: MD12">
                <button class="btn btn-outline-secondary" type="button" id="search-code-btn" title="Buscar diagnóstico por código">
                  <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-outline-secondary" type="button" id="clear-code-btn">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <div class="form-text">Introduzca el código y presione <i class="fas fa-search"></i> para buscar</div>
            </div>
          </div>
          <div class="col-md-9">
            <div class="form-group">
              <label for="selected-diagnosis" class="form-label"><strong>Diagnóstico:</strong></label>
              <div class="input-group">
                <input type="text" id="selected-diagnosis" class="form-control" placeholder="Ej: Tos">
                <button class="btn btn-outline-secondary" type="button" id="clear-diagnosis-btn">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <div class="form-text">Introduzca o copie el diagnóstico desde la herramienta</div>
            </div>
          </div>
        </div>
        <div class="row mt-3">
          <div class="col-12 text-end">
            <div id="api-status" class="d-inline-block me-2"></div>
            <button id="save-selection-btn" class="btn btn-success">
              <i class="fas fa-save"></i> Guardar selección
            </button>
            <button id="copy-to-clipboard-btn" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Copiar al portapapeles">
              <i class="fas fa-clipboard"></i> Copiar
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Botón para abrir el modal -->
<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#diagnosticoModal">
  <i class="fas fa-stethoscope"></i> Seleccionar diagnóstico
</button>

<!-- Añadir JavaScript para el funcionamiento del modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Inicializar tooltips de Bootstrap
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  });
  
  // Lógica para limpiar campos
  document.getElementById('clear-code-btn').addEventListener('click', function() {
    document.getElementById('selected-code').value = '';
  });
  
  document.getElementById('clear-diagnosis-btn').addEventListener('click', function() {
    document.getElementById('selected-diagnosis').value = '';
  });
  
  // Ejemplo de lógica para copiar al portapapeles
  document.getElementById('copy-to-clipboard-btn').addEventListener('click', function() {
    var code = document.getElementById('selected-code').value;
    var diagnosis = document.getElementById('selected-diagnosis').value;
    var textToCopy = code + ' - ' + diagnosis;
    
    navigator.clipboard.writeText(textToCopy).then(function() {
      alert('Copiado al portapapeles: ' + textToCopy);
    }).catch(function(err) {
      console.error('Error al copiar: ', err);
    });
  });
  
  // Aquí puedes añadir la lógica para la búsqueda de códigos y guardado
});
</script>