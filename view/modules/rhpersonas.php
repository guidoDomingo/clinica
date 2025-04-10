<?php
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
    echo '<script>window.location.href = "login";</script>';
    exit();
}
?>
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
                        <li class="breadcrumb-item active">Gestión de Personas</li>
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
                        <button type="button" class="btn btn-info" id="btnNuevaPersona">
                            <i class="fas fa-user-plus"></i>
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
                            <option value="O">O</option>
                            <option value="U">U</option>
                        </select>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="col-md-2">
                        <div class="btn-group" role="group">
                            <button class="btn btn-primary" type="button" id="btnFiltrarPersonas">
                                <i class="fas fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-secondary" id="btnLimpiarPersonas">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <table id="tblPersonas" class="table table-bordered table-striped dt-responsive" width="100%">
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
                            <th>Estado</th>
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
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Modal Agregar Persona -->
<div class="modal fade" id="modalAgregarPersonas" tabindex="-1" role="dialog"
    aria-labelledby="modalAgregarPersonasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarPersonasLabel">Registrar persona</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="personaForm" action="post">
                    <div class="row">
                        <!-- Columna izquierda para datos principales -->
                        <div class="col-md-8">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="perDocument">Documento *</label>
                                    <input type="text" class="form-control" id="perDocument" name="perDocument"
                                        placeholder="Número de documento" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="perDate">Fecha de Nacimiento *</label>
                                    <input type="date" class="form-control" id="perDate" name="perDate" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="perName">Nombres *</label>
                                    <input type="text" class="form-control" id="perName" name="perName"
                                        placeholder="Nombres" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="perLastname">Apellidos *</label>
                                    <input type="text" class="form-control" id="perLastname" name="perLastname"
                                        placeholder="Apellidos" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="perPhone">Teléfono</label>
                                    <input type="text" class="form-control" id="perPhone" name="perPhone"
                                        placeholder="Número de teléfono">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="perSex">Género *</label>
                                    <select id="perSex" name="perSex" class="form-control" required>
                                        <option value="" selected>Seleccionar...</option>
                                        <option value="F">F</option>
                                        <option value="M">M</option>
                                        <option value="O">O</option>
                                        <option value="U">U</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="perFicha">Ficha</label>
                                    <input type="text" class="form-control" id="perFicha" name="perFicha"
                                        placeholder="Número de ficha">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="perAdrress">Dirección</label>
                                <input type="text" class="form-control" id="perAdrress" name="perAdrress"
                                    placeholder="Dirección completa">
                            </div>
                            <div class="form-group">
                                <label for="perEmail">Email</label>
                                <input type="email" class="form-control" id="perEmail" name="perEmail"
                                    placeholder="Correo electrónico">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="perDpto">Departamento</label>
                                    <select id="perDpto" name="perDpto" class="form-control">
                                        <option value="0" selected>N/A</option>
                                        <!-- Opciones de departamentos -->
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="perCity">Ciudad</label>
                                    <select id="perCity" name="perCity" class="form-control">
                                        <option value="0" selected>N/A</option>
                                        <!-- Opciones de ciudades -->
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="perMenor">Es menor de edad</label>
                                    <select id="perMenor" name="perMenor" class="form-control">
                                        <option value="false">NO</option>
                                        <option value="true">SÍ</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4" id="divTutor">
                                    <label for="perTutor">Nombre del Tutor</label>
                                    <input type="text" class="form-control" id="perTutor" name="perTutor"
                                        placeholder="Nombre completo del tutor">
                                </div>
                                <div class="form-group col-md-4" id="divDocTutor">
                                    <label for="perDocTutor">Documento del Tutor</label>
                                    <input type="text" class="form-control" id="perDocTutor" name="perDocTutor"
                                        placeholder="Documento del tutor">
                                </div>
                            </div>
                        </div>

                        <!-- Columna derecha para foto de perfil -->
                        <div class="col-md-4 text-center">
                            <div class="form-group">
                                <label>Foto de Perfil</label>
                                <div class="mt-2">
                                    <img id="previewFotoPerfil" src="view/dist/img/user-default.jpg"
                                        class="img-fluid rounded-circle" style="max-width: 150px; max-height: 150px;">
                                </div>
                                <div class="mt-3">
                                    <input type="file" id="inputFotoPerfil" name="inputFotoPerfil" accept="image/*"
                                        style="display: none;">
                                    <button type="button" id="btnSubirFoto" class="btn btn-primary btn-sm">
                                        <i class="fas fa-camera"></i> Seleccionar foto
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="btnGuardarPersona">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Persona -->
<div class="modal fade" id="modalEditarPersonas" tabindex="-1" role="dialog" aria-labelledby="modalEditarPersonasLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarPersonasLabel">Editar persona</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="personaEditarForm" action="post">
                    <input type="hidden" id="idPersona" name="idPersona" required>
                    <div class="row">
                        <!-- Columna izquierda para datos principales -->
                        <div class="col-md-8">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="EditperDocument">Documento *</label>
                                    <input type="text" class="form-control" id="EditperDocument" name="EditperDocument"
                                        placeholder="Número de documento" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="EditperDate">Fecha de Nacimiento *</label>
                                    <input type="date" class="form-control" id="EditperDate" name="EditperDate"
                                        required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="EditperName">Nombres *</label>
                                    <input type="text" class="form-control" id="EditperName" name="EditperName"
                                        placeholder="Nombres" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="EditperLastname">Apellidos *</label>
                                    <input type="text" class="form-control" id="EditperLastname" name="EditperLastname"
                                        placeholder="Apellidos" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="EditperPhone">Teléfono</label>
                                    <input type="text" class="form-control" id="EditperPhone" name="EditperPhone"
                                        placeholder="Número de teléfono">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="EditperSex">Género *</label>
                                    <select id="EditperSex" name="EditperSex" class="form-control" required>
                                        <option value="" selected>Seleccionar...</option>
                                        <option value="F">F</option>
                                        <option value="M">M</option>
                                        <option value="O">O</option>
                                        <option value="U">U</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="EditperFicha">Ficha</label>
                                    <input type="text" class="form-control" id="EditperFicha" name="EditperFicha"
                                        placeholder="Número de ficha">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="EditperAdrress">Dirección</label>
                                <input type="text" class="form-control" id="EditperAdrress" name="EditperAdrress"
                                    placeholder="Dirección completa">
                            </div>
                            <div class="form-group">
                                <label for="EditperEmail">Email</label>
                                <input type="email" class="form-control" id="EditperEmail" name="EditperEmail"
                                    placeholder="Correo electrónico">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="EditperDpto">Departamento</label>
                                    <select id="EditperDpto" name="EditperDpto" class="form-control">
                                        <option value="0" selected>N/A</option>
                                        <!-- Opciones de departamentos -->
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="EditperCity">Ciudad</label>
                                    <select id="EditperCity" name="EditperCity" class="form-control">
                                        <option value="0" selected>N/A</option>
                                        <!-- Opciones de ciudades -->
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="EditperMenor">Es menor de edad</label>
                                    <select id="EditperMenor" name="EditperMenor" class="form-control">
                                        <option value="false">NO</option>
                                        <option value="true">SÍ</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4" id="divEditTutor">
                                    <label for="EditperTutor">Nombre del Tutor</label>
                                    <input type="text" class="form-control" id="EditperTutor" name="EditperTutor"
                                        placeholder="Nombre completo del tutor">
                                </div>
                                <div class="form-group col-md-4" id="divEditDocTutor">
                                    <label for="EditperDocTutor">Documento del Tutor</label>
                                    <input type="text" class="form-control" id="EditperDocTutor" name="EditperDocTutor"
                                        placeholder="Documento del tutor">
                                </div>
                            </div>
                        </div>

                        <!-- Columna derecha para foto de perfil -->
                        <div class="col-md-4 text-center">
                            <div class="form-group">
                                <label>Foto de Perfil</label>
                                <div class="mt-2">
                                    <img id="previewEditFotoPerfil" src="view/dist/img/user-default.jpg"
                                        class="img-fluid rounded-circle" style="max-width: 150px; max-height: 150px;">
                                </div>
                                <div class="mt-3">
                                    <input type="file" id="inputEditFotoPerfil" name="inputEditFotoPerfil"
                                        accept="image/*" style="display: none;">
                                    <button type="button" id="btnEditSubirFoto" class="btn btn-primary btn-sm">
                                        <i class="fas fa-camera"></i> Cambiar foto
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="btnEditarPersona">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="view/js/rhPersonas.js"></script>
<script src="view/js/test-modal.js"></script>