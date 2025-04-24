<?php
// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo '<script>window.location = "index.php?ruta=login";</script>';
    exit;
}
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Listado de Preformatos</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Preformatos</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Filtros de búsqueda</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="form-filtros" class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filtro-tipo">Tipo de preformato</label>
                            <select class="form-control select2" id="filtro-tipo" name="filtro-tipo">
                                <option value="">Todos los tipos</option>
                                <option value="consulta">Consulta</option>
                                <option value="receta">Receta de medicamentos</option>
                                <option value="receta_anteojos">Receta de anteojos</option>
                                <option value="orden_estudios">Orden de estudios</option>
                                <option value="orden_cirugias">Orden de cirugías</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filtro-propietario">Propietario</label>
                            <select class="form-control select2" id="filtro-propietario" name="filtro-propietario">
                                <option value="">Todos los propietarios</option>
                                <!-- Se cargará dinámicamente desde JavaScript -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="filtro-titulo">Título</label>
                            <input type="text" class="form-control" id="filtro-titulo" name="filtro-titulo" placeholder="Buscar por título...">
                        </div>
                    </div>
                    <div class="col-12 text-right">
                        <button type="button" class="btn btn-default" id="btn-limpiar-filtros">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de preformatos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Preformatos disponibles</h3>
                <div class="card-tools">
                    <a href="index.php?ruta=preformatos" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Nuevo Preformato
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="tabla-preformatos-dt" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Propietario</th>
                            <th>Fecha creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Se cargará dinámicamente desde JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal para ver preformato -->
<div class="modal fade" id="modalVerPreformatoDt">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal-titulo-preformato-dt">Preformato</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tipo:</strong> <span id="modal-tipo-preformato"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Propietario:</strong> <span id="modal-propietario-preformato"></span>
                    </div>
                </div>
                <div class="form-group">
                    <div id="modal-contenido-preformato-dt" class="border p-3 bg-light"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-editar-preformato">Editar</button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables y JS necesarios -->
<script src="view/js/preformatos_datatable.js"></script>