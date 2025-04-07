$(document).ready(function() {
    // Cargar datos del usuario al iniciar
    loadUserProfile();
    
    // Manejar el formulario de información del usuario
    $('#formUserInfo').on('submit', function(e) {
        e.preventDefault();
        updateUserInfo();
    });
    
    // Manejar el formulario de cambio de contraseña
    $('#formChangePassword').on('submit', function(e) {
        e.preventDefault();
        changePassword();
    });
    
    // Manejar el botón de cambiar foto
    $('#btnChangePhoto').on('click', function() {
        $('#modalChangePhoto').modal('show');
    });
    
    // Vista previa de la foto seleccionada
    $('#profilePhoto').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#photoPreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Guardar la nueva foto de perfil
    $('#btnSavePhoto').on('click', function() {
        uploadProfilePhoto();
    });
});

/**
 * Cargar los datos del perfil del usuario
 */
function loadUserProfile() {
    $.ajax({
        url: 'api/users/profile',
        method: 'GET',
        success: function(response) {
            if (response.status === 'success') {
                const userData = response.data;
                
                // Actualizar la información en la página
                $('#userFullName').text(userData.reg_name + ' ' + userData.reg_lastname);
                $('#userEmail').text(userData.user_email);
                $('#userRoles').text(userData.roles.map(role => role.role_name).join(', '));
                $('#userLastLogin').text(userData.user_last_login ? new Date(userData.user_last_login).toLocaleString() : 'Nunca');
                
                // Llenar el formulario con los datos del usuario
                $('#inputName').val(userData.reg_name);
                $('#inputLastName').val(userData.reg_lastname);
                $('#inputEmail').val(userData.user_email);
                $('#inputPhone').val(userData.reg_phone);
                $('#inputDocument').val(userData.reg_document);
                
                // Mostrar la foto de perfil si existe
                if (userData.profile_photo) {
                    $('#userProfileImage').attr('src', 'view/uploads/profile/' + userData.profile_photo);
                }
            } else {
                Swal.fire('Error', 'No se pudieron cargar los datos del perfil', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error cargando perfil:', xhr.responseText);
            Swal.fire('Error', 'Error al cargar los datos del perfil', 'error');
        }
    });
}

/**
 * Actualizar la información del usuario
 */
function updateUserInfo() {
    const formData = {
        reg_name: $('#inputName').val(),
        reg_lastname: $('#inputLastName').val(),
        user_email: $('#inputEmail').val(),
        reg_phone: $('#inputPhone').val()
    };
    
    $.ajax({
        url: 'api/users/update-profile',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire('¡Éxito!', 'Información actualizada correctamente', 'success');
                loadUserProfile(); // Recargar los datos actualizados
            } else {
                Swal.fire('Error', response.message || 'Error al actualizar la información', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error actualizando perfil:', xhr.responseText);
            Swal.fire('Error', xhr.responseJSON?.message || 'Error al actualizar la información', 'error');
        }
    });
}

/**
 * Cambiar la contraseña del usuario
 */
function changePassword() {
    const currentPassword = $('#currentPassword').val();
    const newPassword = $('#newPassword').val();
    const confirmPassword = $('#confirmPassword').val();
    
    // Validar que las contraseñas coincidan
    if (newPassword !== confirmPassword) {
        Swal.fire('Error', 'Las contraseñas nuevas no coinciden', 'error');
        return;
    }
    
    // Validar longitud mínima
    if (newPassword.length < 6) {
        Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }
    
    $.ajax({
        url: 'api/users/change-password',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword
        }),
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire('¡Éxito!', 'Contraseña actualizada correctamente', 'success');
                $('#formChangePassword')[0].reset();
            } else {
                Swal.fire('Error', response.message || 'Error al cambiar la contraseña', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error cambiando contraseña:', xhr.responseText);
            Swal.fire('Error', xhr.responseJSON?.message || 'Error al cambiar la contraseña', 'error');
        }
    });
}

/**
 * Subir una nueva foto de perfil
 */
function uploadProfilePhoto() {
    const fileInput = $('#profilePhoto')[0];
    if (!fileInput.files || !fileInput.files[0]) {
        Swal.fire('Error', 'Por favor seleccione una imagen', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('profile_photo', fileInput.files[0]);
    
    $.ajax({
        url: 'api/users/upload-photo',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire('¡Éxito!', 'Foto de perfil actualizada correctamente', 'success');
                $('#modalChangePhoto').modal('hide');
                $('#formChangePhoto')[0].reset();
                $('#photoPreview').hide();
                
                // Actualizar la imagen de perfil en la página
                if (response.data && response.data.photo_url) {
                    $('#userProfileImage').attr('src', response.data.photo_url);
                } else {
                    loadUserProfile(); // Recargar todo el perfil
                }
            } else {
                Swal.fire('Error', response.message || 'Error al actualizar la foto de perfil', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error subiendo foto:', xhr.responseText);
            Swal.fire('Error', xhr.responseJSON?.message || 'Error al subir la foto de perfil', 'error');
        }
    });
}