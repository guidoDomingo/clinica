<?php
// Módulo de gestión de servicios para la clínica
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Administrar Servicios</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Servicios</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <div class="form-row">
            <!-- Botón para agregar servicio -->
            <div class="col-md-1">
              <button type="button" class="btn btn-info" id="modalAgregarServicio">
                <i class="fas fa-plus-circle"></i>
              </button>
            </div>
            <!-- Botón para gestionar tipos de servicio -->          <div class="col-md-1">
              <button type="button" class="btn btn-warning" id="modalGestionarTipos">
                <i class="fas fa-tags"></i>
              </button>
            </div>
            <!-- Botón para enviar PDF -->
            <!-- <div class="col-md-1">
              <button type="button" class="btn btn-primary" id="btnAbrirModalPDF" title="Enviar PDF">
                <i class="fas fa-file-pdf"></i>
              </button>
            </div> -->

            <!-- Campo de Código -->
            <div class="col-md-2">
              <input type="text" class="form-control" id="validarCodigo" placeholder="Código" required>
            </div>

            <!-- Campo de Descripción -->
            <div class="col-md-3">
              <input type="text" class="form-control" id="validarDescripcion" placeholder="Descripción" required>
            </div>

            <!-- Campo de Tipo de Servicio -->
            <div class="col-md-3">
              <select class="form-control" id="validarTipoServicio" required>
                <option value="0">Seleccionar tipo</option>
                <!-- Los tipos se cargarán dinámicamente desde la BD -->
              </select>
            </div>

            <!-- Botones de Acción -->
            <div class="col-md-2">
              <div class="btn-group" role="group">
                <button class="btn btn-primary" type="button" id="btnFiltrarServicios">
                  <i class="fas fa-search"></i>
                </button>
                <button type="button" class="btn btn-secondary" id="btnLimpiarServicios">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
          
        <div class="card-body">
          <table id="tblServicios" class="table table-bordered table-striped dt-responsive tblServicios" width="100%">
            <thead>
              <tr>
                <th>#</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Duración</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- Los datos se cargarán dinámicamente -->
            </tbody>
            <tfoot>
              <tr>
                <th>#</th>
                <th>Código</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Duración</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </tfoot>          </table>
        </div>
        <div class="card-footer">
          <div class="row">
            <div class="col-12 text-right">
              <span class="text-muted">Utilice la tabla para ver, editar o eliminar servicios.</span>
            </div>
          </div>
        </div>
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
     
  </div>

<!-- Modal Agregar Servicio -->
<div class="modal fade" id="modalAgregarServicios" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Registrar Servicio</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="servicioForm" action="post">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="servCodigo">Código</label>
              <input type="text" class="form-control" id="servCodigo" name="servCodigo" placeholder="Código del servicio">
            </div>
            <div class="form-group col-md-6">
              <label for="servTipo">Tipo de Servicio</label>
              <select id="servTipo" name="servTipo" class="form-control" required>
                <option value="" selected>Seleccionar...</option>
                <!-- Se cargará dinámicamente -->
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="servDescripcion">Descripción</label>
            <input type="text" class="form-control" id="servDescripcion" name="servDescripcion" placeholder="Descripción del servicio">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="servDuracion">Duración (minutos)</label>
              <input type="number" class="form-control" id="servDuracion" name="servDuracion" value="30" min="5">
            </div>
            <div class="form-group col-md-6">
              <label for="servPrecio">Precio</label>
              <input type="number" step="0.01" class="form-control" id="servPrecio" name="servPrecio" placeholder="0.00">
            </div>
          </div>
          <div class="form-group">
            <label for="servEstado">Estado</label>
            <select id="servEstado" name="servEstado" class="form-control">
              <option value="true" selected>Activo</option>
              <option value="false">Inactivo</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnGuardarServicio" name="btnGuardarServicio">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Servicio -->
<div class="modal fade" id="modalEditarServicios" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Editar Servicio</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="servicioEditarForm" action="post">
          <input type="hidden" id="idServicio" name="idServicio" required>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="EditServCodigo">Código</label>
              <input type="text" class="form-control" id="EditServCodigo" name="EditServCodigo" placeholder="Código del servicio">
            </div>
            <div class="form-group col-md-6">
              <label for="EditServTipo">Tipo de Servicio</label>
              <select id="EditServTipo" name="EditServTipo" class="form-control" required>
                <option value="" selected>Seleccionar...</option>
                <!-- Se cargará dinámicamente -->
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="EditServDescripcion">Descripción</label>
            <input type="text" class="form-control" id="EditServDescripcion" name="EditServDescripcion" placeholder="Descripción del servicio">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="EditServDuracion">Duración (minutos)</label>
              <input type="number" class="form-control" id="EditServDuracion" name="EditServDuracion" value="30" min="5">
            </div>
            <div class="form-group col-md-6">
              <label for="EditServPrecio">Precio</label>
              <input type="number" step="0.01" class="form-control" id="EditServPrecio" name="EditServPrecio" placeholder="0.00">
            </div>
          </div>
          <div class="form-group">
            <label for="EditServEstado">Estado</label>
            <select id="EditServEstado" name="EditServEstado" class="form-control">
              <option value="true">Activo</option>
              <option value="false">Inactivo</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" id="btnEditarServicio" name="btnEditarServicio">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Gestionar Tipos de Servicio -->
<div class="modal fade" id="modalTiposServicio" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Gestionar Tipos de Servicio</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <div class="input-group">
            <input type="text" class="form-control" id="nuevoTipoServicio" placeholder="Nuevo tipo de servicio">
            <div class="input-group-append">
              <button class="btn btn-primary" type="button" id="btnAgregarTipo">Agregar</button>
            </div>
          </div>
        </div>
        <table id="tblTiposServicio" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <!-- Se cargará dinámicamente -->
          </tbody>
        </table>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Incluir Modal para enviar PDFs -->
<?php include "modal_enviar_pdf.php"; ?>
