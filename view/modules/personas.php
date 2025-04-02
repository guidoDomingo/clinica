<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Administrar personas</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Blank Page</li>
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
            <!-- Botón para agregar persona -->
            <div class="col-md-1">
              <button type="button" class="btn btn-info" id="modalAgregarPersona">
                <i class="fa-solid fa-user-plus"></i>
              </button>
            </div>

            <!-- Campo de Documento -->
            <div class="col-md-2">
              <input type="text" class="form-control" id="validarDocumento" placeholder="Documento" required>
            </div>

            <!-- Campo de Ficha -->
            <div class="col-md-1">
              <input type="text" class="form-control" id="validarFicha" placeholder="Ficha" required>
            </div>

            <!-- Campo de Nombres -->
            <div class="col-md-2">
              <input type="text" class="form-control" id="validarNombre" placeholder="Nombres" required>
            </div>

            <!-- Campo de Apellidos -->
            <div class="col-md-2">
              <input type="text" class="form-control" id="validarApellidos" placeholder="Apellidos" required>
            </div>

            <!-- Campo de Sexo -->
            <div class="col-md-2">
              <select class="form-control" id="validarSexo" required>
                <option value="0">Seleccionar</option>
                <option value="F">F</option>
                <option value="M">M</option>
              </select>
            </div>

            <!-- Botones de Acción -->
            <div class="col-md-2">
              <div class="btn-group" role="group">
                <button class="btn btn-primary" type="button" id="btnFiltrarPersonas">
                  <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <button type="button" class="btn btn-secondary" id="btnLimpiarPersonas">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
        </div> 
           
          
      </div>
        <div class="card-body">
        <!-- <table id="tbBarcodReport" class="table table-bordered table-hover dt-responsive tbBarcodReport" width="100%"> -->
        <table id="tblPersonas" class="table table-bordered table-striped dt-responsive tblPersonas" width="100%">
                  <thead>
                  <tr>
                    <th>#</th>
                    <th>Documento</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Edad</th>
                    <th>Ficha</th>
                    <th>Teléfono</th>
                    <th>Menor</th>
                    <th>Tutor</th>
                    <th>Doc.Tutor</th>
                    <th>Edo</th>
                    <th>Acciones</th>
                  </tr>
                  </thead>
                  <tbody>
                  
                 
                  </tbody>
                  <tfoot>
                  <tr>
                  <th>#</th>
                    <th>Documento</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Edad</th>
                    <th>Ficha</th>
                    <th>Teléfono</th>
                    <th>Menor</th>
                    <th>Tutor</th>
                    <th>Doc.Tutor</th>
                    <th>Edo</th>
                    <th>Acciones</th>
                  </tr>
                  </tfoot>
          </table>
        </div>
        <!-- /.card-body -->
        <!-- <div class="card-footer">
          Footer
        </div> -->
        <!-- /.card-footer-->
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
     
  </div>

<!-- Agregar -->
<div class="modal fade" id="modalAgregarPersonas" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Registrar persona</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="personaForm" action="post">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="perDocument">Documento</label>
                <input type="text" class="form-control" id="perDocument" name="perDocument" placeholder="Cédula">
              </div>
              <div class="form-group col-md-6">
                <label for="perDate">Nacimiento</label>
                <input type="date" class="form-control" id="perDate" name="perDate" >
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="perName">Nombres</label>
                <input type="text" class="form-control" id="perName" name="perName" placeholder="Nombres">
              </div>
              <div class="form-group col-md-6">
                <label for="perLastname">Apellidos</label>
                <input type="text" class="form-control" id="perLastname" name="perLastname" placeholder="Apellidos">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-5">
                <label for="perPhone">Teléfono</label>
                <input type="text" class="form-control" id="perPhone" name="perPhone"> 
              </div>
              <div class="form-group col-md-4">
                <label for="perSex">Sexo</label>
                <select id="perSex" name="perSex" class="form-control" require>
                  <option value="" selected>Seleccionar...</option>
                  <option value="F">F</option>
                  <option value="M">M</option>
                </select>
              </div>
              <div class="form-group col-md-3">
                <label for="perFicha">Ficha</label>
                <input type="text" class="form-control" id="perFicha" name="perFicha">
              </div>
            </div>
            <div class="form-group">
              <label for="perAdrress">Dirección</label>
              <input type="text" class="form-control" id="perAdrress" name="perAdrress" placeholder="Ubicación">
            </div>
            <div class="form-group">
              <label for="perEmail">Email</label>
              <input type="email" class="form-control" id="perEmail" name="perEmail" placeholder="Email">
            </div>
            <div class="form-row">
            <div class="form-group col-md-6">
                <label for="perDpto">Departamento</label>
                <select id="perDpto" name="perDpto" class="form-control">
                  <option value="0" selected>N/A</option>
                  <!-- <option>...</option> -->
                </select>
              </div>
              <div class="form-group col-md-6">
                <label for="perCity">Ciudad</label>
                <select id="perCity" name="perCity" class="form-control">
                  <option value="0" selected>N/A</option>
                  <!-- <option>...</option> -->
                </select>
              </div>
              
            </div>
            <div class="form-row">
            <div class="form-group col-md-2">
                <label for="perMenor">Es menor</label>
                <select id="perMenor" name="perMenor" class="form-control">
                  <option value="false">NO</option>
                  <option value="true">SI</option>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label for="perTutor">Tutor</label>
                <select id="perTutor" name="perTutor" class="form-control">
                  <option value="N/A" selected>Seleccionar...</option>
                  <option value="Tio">Tio/a</option>
                  <option value="Otro">Otro</option>
                </select>
              </div>
              
              <div class="form-group col-md-4">
                <label for="perDocTutor">Documento</label>
                <input type="text" class="form-control" id="perDocTutor" name="perDocTutor">
              </div>
            </div>
            
            <!-- <button type="submit" class="btn btn-primary">Sign in</button> -->
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
              <button type="button" class="btn btn-primary" id="btnGuardarPersona" name="btnGuardarPersona">Guardar</button>
            </div>
          </form>
      </div>
      
    </div>
  </div>
</div>
<!-- Editar -->
<div class="modal fade" id="modalEditarPersonas" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Editar persona</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="personaEditarForm" action="post">
          <input type="hidden" id="idPersona" name="idPersona" require> 
          <input type="hidden" id="txtPropietario" name="txtPropietario" require> 
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="EditperDocument">Documento</label>
                <input type="text" class="form-control" id="EditperDocument" name="EditperDocument" placeholder="Cédula">
              </div>
              <div class="form-group col-md-6">
                <label for="EditperDate">Nacimiento</label>
                <input type="date" class="form-control" id="EditperDate" name="EditperDate" >
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="EditperName">Nombres</label>
                <input type="text" class="form-control" id="EditperName" name="EditperName" placeholder="Nombres">
              </div>
              <div class="form-group col-md-6">
                <label for="EditperLastname">Apellidos</label>
                <input type="text" class="form-control" id="EditperLastname" name="EditperLastname" placeholder="Apellidos">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-5">
                <label for="EditperPhone">Teléfono</label>
                <input type="text" class="form-control" id="EditperPhone" name="EditperPhone"> 
              </div>
              <div class="form-group col-md-4">
                <label for="EditperSex">Sexo</label>
                <select id="EditperSex" name="EditperSex" class="form-control" require>
                  <option value="" selected>Seleccionar...</option>
                  <option value="F">F</option>
                  <option value="M">M</option>
                </select>
              </div>
              <div class="form-group col-md-3">
                <label for="EditperFicha">Ficha</label>
                <input type="text" class="form-control" id="EditperFicha" name="EditperFicha">
              </div>
            </div>
            <div class="form-group">
              <label for="EditperAdrress">Dirección</label>
              <input type="text" class="form-control" id="EditperAdrress" name="EditperAdrress" placeholder="Ubicación">
            </div>
            <div class="form-group">
              <label for="EditperEmail">Email</label>
              <input type="email" class="form-control" id="EditperEmail" name="EditperEmail" placeholder="Email">
            </div>
            <div class="form-row">
            <div class="form-group col-md-6">
                <label for="EditperDpto">Departamento</label>
                <select id="EditperDpto" name="EditperDpto" class="form-control">
                  <option value="0" selected>N/A</option>
                  <!-- <option>...</option> -->
                </select>
              </div>
              <div class="form-group col-md-6">
                <label for="EditperCity">Ciudad</label>
                <select id="EditperCity" name="EditperCity" class="form-control">
                  <option value="0" selected>N/A</option>
                  <!-- <option>...</option> -->
                </select>
              </div>
              
            </div>
            <div class="form-row">
            <div class="form-group col-md-2">
                <label for="EditperMenor">Es menor</label>
                <select id="EditperMenor" name="EditperMenor" class="form-control">
                  <option value="false">NO</option>
                  <option value="true">SI</option>
                </select>
              </div>
              <div class="form-group col-md-6">
                <label for="EditperTutor">Tutor</label>
                <select id="EditperTutor" name="EditperTutor" class="form-control">
                  <option value="N/A" selected>Seleccionar...</option>
                  <option value="Tio">Tio/a</option>
                  <option value="Otro">Otro</option>
                </select>
              </div>
              
              <div class="form-group col-md-4">
                <label for="EditperDocTutor">Documento</label>
                <input type="text" class="form-control" id="EditperDocTutor" name="EditperDocTutor">
              </div>
            </div>
            
            <!-- <button type="submit" class="btn btn-primary">Sign in</button> -->
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
              <button type="button" class="btn btn-primary" id="btnEditarPersona" name="btnEditarPersona">Guardar</button>
            </div>
          </form>
      </div>
      
    </div>
  </div>
</div>
<!-- Medico -->
<div class="modal fade" id="modalAgregarMedico" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Agregar <i class="fa-solid fa-user-doctor"></i></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="personaMedForm" action="post">
          <input type="hidden" id="idPersonaMed" name="idPersonaMed" require> 
          <input type="hidden" id="txtPropietarioMed" name="txtPropietarioMed" require> 
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="medDocument">Documento</label>
                <input type="text" class="form-control" id="medDocument" name="medDocument" readonly>
              </div>
              <div class="form-group col-md-6">
                <label for="medDate">Nacimiento</label>
                <input type="date" class="form-control" id="medDate" name="medDate" readonly>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="medName">Nombres</label>
                <input type="text" class="form-control" id="medName" name="medName" readonly>
              </div>
              <div class="form-group col-md-6">
                <label for="medLastName">Apellidos</label>
                <input type="text" class="form-control" id="medLastName" name="medLastName" readonly>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="medProfesion">Profesión</label>
                <select id="medProfesion" name="medProfesion" class="form-control" require>
                  <option value="" selected>Seleccionar...</option>
                  <option value="Oftalmólogo">Oftalmólogo</option> 
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="medEspecialidad">Especialidades</label>
                <select class="select2" id="medEspecialidad" multiple="multiple" data-placeholder="Seleccionar" data-dropdown-css-class="select2-purple" style="width: 100%;">
                </select>
              </div>
            </div>
            <div class="form-group">
              <label for="direccionCorp">Dirección corporativa</label>
              <input type="text" class="form-control" id="direccionCorp" name="direccionCorp" placeholder="Ubicación" require>
            </div>
            <div class="form-group">
              <label for="emailCorp">Email profesional</label>
              <input type="email" class="form-control" id="emailCorp" name="emailCorp" placeholder="Email" require>
            </div>
            <div class="form-group">
              <label for="nameCorp">Denominación corporativa</label>
              <input type="text" class="form-control" id="nameCorp" name="nameCorp" placeholder="Ubicación" require>
            </div>
            <div class="form-row">
            <div class="form-group col-md-6">
                <label for="rucCorp">Ruc</label>
                <input type="text" class="form-control" id="rucCorp" name="rucCorp" require>
              </div>
              
              <div class="form-group col-md-6">
                <label for="whatsappCorp">Whatsapp (0991123456)</label>
                <input type="text" class="form-control" id="whatsappCorp" name="whatsappCorp" require>
              </div>
            </div>
         
            <div class="form-row">
            <div class="form-group col-md-8">
                <label for="planMedico">Elegir plan</label>
                <select id="planMedico" name="planMedico" class="form-control" require>
                  <option value="">Seleccionar</option>
                  <option value="Personal">Personal</option>
                  <option value="Básico">Básico</option>
                  <option value="Profesional">Profesional</option>
                  <option value="Premiun">Premiun</option>
                </select>
              </div>
              <div class="form-group col-md-4">
                <label for="importeMed">Documento</label>
                <input type="text" class="form-control" id="importeMed" name="importeMed" readonly>
              </div>
            </div>
            
            <!-- <button type="submit" class="btn btn-primary">Sign in</button> -->
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
              <button type="button" class="btn btn-primary" id="btnCrearMedico" name="btnCrearMedico">Guardar</button>
            </div>
          </form>
      </div>
      
    </div>
  </div>
</div>