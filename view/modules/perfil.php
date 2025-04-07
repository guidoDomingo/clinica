<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location = "index.php?route=login";</script>';
    exit;
}
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Perfil de Usuario</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?route=home">Inicio</a></li>
                        <li class="breadcrumb-item active">Perfil</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">

                    <!-- Profile Image -->
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <img class="profile-user-img img-fluid img-circle" id="userProfileImage"
                                    src="view/dist/img/default-user.png" alt="Foto de perfil del usuario">
                            </div>

                            <h3 class="profile-username text-center" id="userFullName">Nombre del Usuario</h3>

                            <p class="text-muted text-center" id="userEmail">correo@ejemplo.com</p>

                            <div class="text-center mb-3">
                                <button type="button" class="btn btn-primary btn-sm" id="btnChangePhoto">
                                    <i class="fas fa-camera"></i> Cambiar Foto
                                </button>
                            </div>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Roles</b> <span class="float-right" id="userRoles">-</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Último acceso</b> <span class="float-right" id="userLastLogin">-</span>
                                </li>
                            </ul>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#userInfo"
                                        data-toggle="tab">Información Personal</a></li>
                                <li class="nav-item"><a class="nav-link" href="#changePassword"
                                        data-toggle="tab">Cambiar Contraseña</a></li>
                            </ul>
                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="active tab-pane" id="userInfo">
                                    <form id="formUserInfo" class="form-horizontal">
                                        <div class="form-group row">
                                            <label for="inputName" class="col-sm-2 col-form-label">Nombre</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="inputName"
                                                    placeholder="Nombre" name="reg_name">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputLastName" class="col-sm-2 col-form-label">Apellido</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="inputLastName"
                                                    placeholder="Apellido" name="reg_lastname">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                                            <div class="col-sm-10">
                                                <input type="email" class="form-control" id="inputEmail"
                                                    placeholder="Email" name="user_email">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputPhone" class="col-sm-2 col-form-label">Teléfono</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="inputPhone"
                                                    placeholder="Teléfono" name="reg_phone">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputDocument" class="col-sm-2 col-form-label">Documento</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="inputDocument"
                                                    placeholder="Documento" name="reg_document" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="offset-sm-2 col-sm-10">
                                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.tab-pane -->

                                <div class="tab-pane" id="changePassword">
                                    <form id="formChangePassword" class="form-horizontal">
                                        <div class="form-group row">
                                            <label for="currentPassword" class="col-sm-3 col-form-label">Contraseña
                                                Actual</label>
                                            <div class="col-sm-9">
                                                <input type="password" class="form-control" id="currentPassword"
                                                    placeholder="Contraseña actual" name="current_password">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="newPassword" class="col-sm-3 col-form-label">Nueva
                                                Contraseña</label>
                                            <div class="col-sm-9">
                                                <input type="password" class="form-control" id="newPassword"
                                                    placeholder="Nueva contraseña" name="new_password">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="confirmPassword" class="col-sm-3 col-form-label">Confirmar
                                                Contraseña</label>
                                            <div class="col-sm-9">
                                                <input type="password" class="form-control" id="confirmPassword"
                                                    placeholder="Confirmar nueva contraseña" name="confirm_password">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="offset-sm-3 col-sm-9">
                                                <button type="submit" class="btn btn-primary">Cambiar
                                                    Contraseña</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.tab-pane -->
                            </div>
                            <!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<!-- Modal para cambiar foto de perfil -->
<div class="modal fade" id="modalChangePhoto" tabindex="-1" role="dialog" aria-labelledby="modalChangePhotoLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalChangePhotoLabel">Cambiar Foto de Perfil</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formChangePhoto">
                    <div class="form-group">
                        <label for="profilePhoto">Seleccionar nueva foto</label>
                        <input type="file" class="form-control-file" id="profilePhoto" name="profile_photo"
                            accept="image/*">
                    </div>
                    <div class="text-center mt-3 mb-3">
                        <img id="photoPreview" class="img-fluid img-circle"
                            style="max-width: 200px; max-height: 200px; display: none;" alt="Vista previa">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSavePhoto">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="view/js/profile.js"></script>