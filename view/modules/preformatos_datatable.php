<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Listado de Preformatos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="home">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php?ruta=preformatos">Preformatos</a></li>
                        <li class="breadcrumb-item active">Listado</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Filtros -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-info collapsed-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="form-filtros">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="filtro-tipo">Tipo de preformato:</label>
                                            <select class="form-control select2" id="filtro-tipo" style="width: 100%;">
                                                <option value="" selected>Todos</option>
                                                <option value="consulta">Consulta</option>
                                                <option value="receta">Receta de medicamentos</option>
                                                <option value="receta_anteojos">Receta de anteojos</option>
                                                <option value="orden_estudios">Orden de Estudios</option>
                                                <option value="orden_cirugias">Orden de cirugías</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="filtro-propietario">Propietario:</label>
                                            <select class="form-control select2" id="filtro-propietario" style="width: 100%;">
                                                <option value="" selected>Todos</option>
                                                <!-- Se cargarán dinámicamente -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="filtro-titulo">Título:</label>
                                            <input type="text" class="form-control" id="filtro-titulo" placeholder="Buscar por título">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-right">
                                        <button type="button" id="btn-limpiar-filtros" class="btn btn-default">Limpiar filtros</button>
                                        <button type="submit" id="btn-filtrar" class="btn btn-primary">Filtrar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DataTable -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Listado completo de preformatos</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="tabla-preformatos-dt" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Título</th>
                                        <th>Tipo</th>
                                        <th>Propietario</th>
                                        <th>Fecha de creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Se cargará dinámicamente con AJAX -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>#</th>
                                        <th>Título</th>
                                        <th>Tipo</th>
                                        <th>Propietario</th>
                                        <th>Fecha de creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </tfoot>
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
<div class="modal fade" id="modalVerPreformatoDt" tabindex="-1" role="dialog" aria-labelledby="modalVerPreformatoDtLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-titulo-preformato-dt"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Tipo:</strong> <span id="modal-tipo-preformato"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Propietario:</strong> <span id="modal-propietario-preformato"></span></p>
                    </div>
                </div>
                <hr>
                <div id="modal-contenido-preformato-dt"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a href="#" id="btn-editar-preformato" class="btn btn-primary">Editar</a>
            </div>
        </div>
    </div>
</div>

<!-- Script para preformatos DataTable -->
<script src="view/js/preformatos_datatable.js"></script>