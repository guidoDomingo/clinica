<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Preformatos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="home">Home</a></li>
                        <li class="breadcrumb-item active">Preformatos</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Formulario para crear/editar preformatos -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#preformato-textarea" data-toggle="tab">Preformato textarea</a></li>
                                <li class="nav-item"><a class="nav-link" href="#preformato-generico" data-toggle="tab">Preformato genérico</a></li>
                                <li class="nav-item"><a class="nav-link" href="#otros" data-toggle="tab">Otros</a></li>
                            </ul>
                        </div><!-- /.card-header -->
                        
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Tab para preformatos de textarea -->
                                <div class="active tab-pane" id="preformato-textarea">
                                    <form id="form-preformato-textarea">
                                        <input type="hidden" id="id-preformato" value="">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="propietario">Propietario</label>
                                                    <select class="form-control" id="propietario" required>
                                                        <option value="" selected disabled>Seleccionar</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="aplicar-a">Aplicar a:</label>
                                                    <select class="form-control" id="aplicar-a" required>
                                                        <option value="" selected disabled>Seleccionar</option>
                                                        <option value="consulta">Consulta</option>
                                                        <option value="receta">Receta de medicamentos</option>
                                                        <option value="receta_anteojos">Receta de anteojos</option>
                                                        <option value="orden_estudios">Orden de Estudios</option>
                                                        <option value="orden_cirugias">Orden de cirugías</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="titulo-preformato">Título</label>
                                                    <input type="text" class="form-control" id="titulo-preformato" placeholder="Título del preformato" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tipo-preformato">Tipo</label>
                                                    <select class="form-control" id="tipo-preformato">
                                                        <option value="" selected disabled>Seleccionar...</option>
                                                        <option value="Texas">Texas</option>
                                                        <option value="Receta de lente">Receta de lente</option>
                                                        <option value="California">California</option>
                                                        <option value="Delaware">Delaware</option>
                                                        <option value="Tennessee">Tennessee</option>
                                                        <option value="Formulario de receta">Formulario de receta</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="obs-preformato">Obs:</label>
                                            <textarea id="obs-preformato" class="form-control" rows="10" required></textarea>
                                        </div>
                                        <div class="form-group text-right">
                                            <button type="button" class="btn btn-default" id="btn-limpiar-preformato">Limpiar</button>
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Tab para preformatos genéricos -->
                                <div class="tab-pane" id="preformato-generico">
                                    <div class="form-group">
                                        <p>Configuración de preformatos genéricos (para futuras implementaciones)</p>
                                    </div>
                                </div>
                                
                                <!-- Tab para otros tipos de preformatos -->
                                <div class="tab-pane" id="otros">
                                    <div class="form-group">
                                        <p>Otras configuraciones de preformatos (para futuras implementaciones)</p>
                                    </div>
                                </div>
                            </div>
                            <!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            
            <!-- Lista de Preformatos -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Preformatos</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="tabla-preformatos" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Título</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-preformatos">
                                    <!-- Aquí se cargarán los preformatos -->
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<!-- Modal para ver preformato -->
<div class="modal fade" id="modalVerPreformato" tabindex="-1" role="dialog" aria-labelledby="modalVerPreformatoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-titulo-preformato"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modal-contenido-preformato"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Script para preformatos -->
<script src="view/js/preformatos.js"></script>