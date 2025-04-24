<div class="col-md-3">

            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <!-- <div class="text-center">
                  <img class="profile-user-img img-fluid img-circle"
                       src="view/dist/img/user4-128x128.jpg"
                       alt="User profile picture">
                </div> -->

                <h3 class="profile-username text-center" id="profile-username"></h3>

                <p class="text-muted text-center" id="profile-ci"></p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>Cuota</b> <a class="float-right" id="cuota-valor"></a>
                  </li>
                  <li class="list-group-item">
                    <b>Consultas</b> <a class="float-right" id="txtCantConsulta"></a>
                  </li>
                  <li class="list-group-item">
                    <b>Ultima consulta</b> <a class="float-right" id="txtUltConsulta"></a>
                  </li>
                </ul>

                <!-- <a href="#" class="btn btn-primary btn-block"><b>Follow</b></a> -->
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

            <!-- Lista de Consultas -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Todas las consultas</h3>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table id="tabla-consultas" class="table table-bordered table-striped table-sm">
                    <thead>
                      <tr>
                        <th>Fecha</th>
                        <th>Paciente</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- Datos cargados por DataTable -->
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <!-- /.card -->
          </div>