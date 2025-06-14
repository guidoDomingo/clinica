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

    // Verificar si el perfil está completo
    checkProfileCompletion();
});

/**
 * Verifica si el perfil del usuario está completo
 * Si no está completo, muestra un modal para completarlo
 */
function checkProfileCompletion() {
    $.ajax({
        url: 'ajax/profile.ajax.php',
        type: 'POST',
        data: {
            action: 'checkProfile'
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                const hasCompleteProfile = response.complete;
                // Verificar si no hay datos completos en rh_person
                if (!hasCompleteProfile) {
                    Swal.fire({
                        title: 'Completa tu perfil',
                        text: 'Para continuar usando el sistema, necesitas completar tu información personal',
                        icon: 'info',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'Completar perfil',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('a[href="#userInfo"]').tab('show');
                        }
                    });
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al verificar perfil completo:', error);
        }
    });
}

/**
 * Cargar los datos del perfil del usuario
 */
function loadUserProfile() {
    $.ajax({
        url: 'controller/profile.controller.php',
        type: 'POST',
        data: {
            action: 'getProfile'
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                const userData = response.data;
                
                // Actualizar la información en la página
                $('#userFullName').text(userData.first_name + ' ' + userData.last_name);
                $('#userEmail').text(userData.user_email);
                if (userData.roles && userData.roles.length > 0) {
                    $('#userRoles').text(userData.roles.map(role => role.role_name).join(', '));
                }
                $('#userLastLogin').text(userData.user_last_login ? new Date(userData.user_last_login).toLocaleString() : 'Nunca');
                
                // Llenar el formulario con los datos del usuario
                $('#inputName').val(userData.first_name);
                $('#inputLastName').val(userData.last_name);
                $('#inputEmail').val(userData.user_email);
                $('#inputPhone').val(userData.phone_number || userData.reg_phone);
                $('#inputDocument').val(userData.document_number || userData.reg_document);
                $('#inputAddress').val(userData.address || '');
                if (userData.birth_date) {
                    $('#inputBirthDate').val(userData.birth_date);
                }
                if (userData.gender) {
                    $('#inputGender').val(userData.gender);
                }
                
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
    // Validaciones básicas
    if (!$('#inputName').val() || !$('#inputLastName').val() || !$('#inputEmail').val()) {
        Swal.fire('Error', 'Los campos nombre, apellido y correo son obligatorios', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'updateProfile');
    formData.append('first_name', $('#inputName').val());
    formData.append('last_name', $('#inputLastName').val());
    formData.append('email', $('#inputEmail').val());
    formData.append('phone', $('#inputPhone').val());
    formData.append('document', $('#inputDocument').val());
    formData.append('address', $('#inputAddress').val());
    formData.append('birth_date', $('#inputBirthDate').val());
    formData.append('gender', $('#inputGender').val());
    
    $.ajax({
        url: 'controller/profile.controller.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if (data.status === 'success') {
                    Swal.fire('¡Éxito!', 'Información actualizada correctamente', 'success');
                    loadUserProfile(); // Recargar los datos actualizados
                } else {
                    Swal.fire('Error', data.message || 'Error al actualizar la información', 'error');
                }
            } catch (e) {
                console.error('Error parsing response:', e, response);
                Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error actualizando perfil:', xhr.responseText);
            Swal.fire('Error', 'Error al actualizar la información', 'error');
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
    
    const formData = new FormData();
    formData.append('action', 'changePassword');
    formData.append('current_password', currentPassword);
    formData.append('new_password', newPassword);
    
    $.ajax({
        url: 'controller/profile.controller.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if (data.status === 'success') {
                    Swal.fire('¡Éxito!', 'Contraseña actualizada correctamente', 'success');
                    $('#formChangePassword')[0].reset();
                } else {
                    Swal.fire('Error', data.message || 'Error al cambiar la contraseña', 'error');
                }
            } catch (e) {
                console.error('Error parsing response:', e, response);
                Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error cambiando contraseña:', xhr.responseText);
            Swal.fire('Error', 'Error al cambiar la contraseña', 'error');
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
    formData.append('action', 'uploadPhoto');
    formData.append('profile_photo', fileInput.files[0]);
    
    $.ajax({
        url: 'controller/profile.controller.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const data = JSON.parse(response);
                if (data.status === 'success') {
                    Swal.fire('¡Éxito!', 'Foto de perfil actualizada correctamente', 'success');
                    $('#modalChangePhoto').modal('hide');
                    $('#formChangePhoto')[0].reset();
                    $('#photoPreview').hide();
                    
                    // Actualizar la imagen de perfil en la página
                    if (data.data && data.data.photo_url) {
                        $('#userProfileImage').attr('src', data.data.photo_url);
                    } else {
                        loadUserProfile(); // Recargar todo el perfil
                    }

                    setInterval(() => {
                        window.location.reload(); // Recargar la página para reflejar los cambios
                    }, 2000); // Actualizar la imagen cada segundo para evitar caché
                    
                } else {
                    Swal.fire('Error', data.message || 'Error al actualizar la foto de perfil', 'error');
                }
            } catch (e) {
                console.error('Error parsing response:', e, response);
                Swal.fire('Error', 'Error al procesar la respuesta del servidor', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error subiendo foto:', xhr.responseText);
            Swal.fire('Error', 'Error al subir la foto de perfil', 'error');
        }
    });
}