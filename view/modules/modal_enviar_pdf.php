<div class="modal fade" id="modalEnviarPDF" tabindex="-1" role="dialog" aria-labelledby="modalEnviarPDFLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEnviarPDFLabel">Enviar Confirmación de Reserva</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formEnviarPDF">
          <div class="form-group">
            <label for="enviarPDF_telefono">Teléfono del Paciente</label>
            <input type="text" class="form-control" id="enviarPDF_telefono" placeholder="Ejemplo: 595982313358">
            <small class="form-text text-muted">Ingrese el número en formato internacional sin el signo +</small>
          </div>
          <div class="form-group">
            <label for="enviarPDF_url">URL del Documento</label>
            <input type="text" class="form-control" id="enviarPDF_url" value="https://www.google.com/imgres?q=pdf%20de%20salud&imgurl=https%3A%2F%2Fimgv2-2-f.scribdassets.com%2Fimg%2Fdocument%2F71495665%2Foriginal%2F09236410d4%2F1%3Fv%3D1&imgrefurl=https%3A%2F%2Fwww.scribd.com%2Fdoc%2F71495665%2FSALUD-PUBLICA&docid=Lx0PDCBlGb4YyM&tbnid=ovHCMb6aJsz5lM&vet=12ahUKEwiR3Pjak9mNAxXAqZUCHX4WF2QQM3oECG0QAA..i&w=768&h=1024&hcb=2&ved=2ahUKEwiR3Pjak9mNAxXAqZUCHX4WF2QQM3oECG0QAA">
          </div>
          <div class="form-group">
            <label for="enviarPDF_descripcion">Descripción</label>
            <input type="text" class="form-control" id="enviarPDF_descripcion" value="DATOS DE PRUEBA">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btnEnviarPDF">Enviar PDF</button>
      </div>
    </div>
  </div>
</div>
