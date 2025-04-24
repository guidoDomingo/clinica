<?php
// Verificar si el usuario tiene permisos para ver preformatos
if (!isset($_SESSION['id_usuario'])) {
    echo '<script>
        window.location = "login";
    </script>';
    exit;
}
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Preformatos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
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
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Filtros</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <form id="form-filtros">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="filtro-tipo">Tipo de Preformato:</label>
                                            <select class="form-control select2" id="filtro-tipo">
                                                <option value="">Todos</option>
                                                <option value="consulta">Consulta</option>
                                                <option value="receta">Receta de Medicamentos</option>
                                                <option value="receta_anteojos">Receta de Anteojos</option>
                                                <option value="orden_estudios">Orden de Estudios</option>
                                                <option value="orden_cirugias">Orden de Cirugías</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="filtro-propietario">Propietario:</label>
                                            <select class="form-control select2" id="filtro-propietario">
                                                <option value="">Todos</option>
                                                <!-- Las opciones se cargarán dinámicamente -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="filtro-titulo">Título contiene:</label>
                                            <input type="text" class="form-control" id="filtro-titulo" placeholder="Buscar por título">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-right">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="btn-limpiar-filtros">
                                            <i class="fas fa-broom"></i> Limpiar Filtros
                                        </button>
                                        <a href="index.php?ruta=preformatos" class="btn btn-success">
                                            <i class="fas fa-plus"></i> Nuevo Preformato
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Listado de Preformatos</h3>
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
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán dinámicamente -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>#</th>
                                        <th>Título</th>
                                        <th>Tipo</th>
                                        <th>Propietario</th>
                                        <th>Fecha Creación</th>
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

<!-- Modal para ver detalles del preformato -->
<div class="modal fade" id="modalVerPreformatoDt">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-titulo-preformato-dt">Detalles del Preformato</h4>
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
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h3 class="card-title">Contenido</h3>
                            </div>
                            <div class="card-body">
                                <div id="modal-contenido-preformato-dt"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-editar-preformato">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<script src="view/js/preformatos_datatable.js"></script>