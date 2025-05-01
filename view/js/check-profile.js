/**
 * Script para verificar si el perfil del usuario está completo
 * Si no está completo, redirige al usuario a la página de perfil
 */
$(document).ready(function() {
    // Verificar perfil en todas las páginas
    checkUserProfile();
    
    // Interceptar todos los enlaces de navegación
    $(document).on('click', 'a', function(e) {
        // Obtener la URL del enlace
        var href = $(this).attr('href');
        
        // Si es una URL interna y no es la página de perfil o login
        if (href && href !== '#' && !href.includes('javascript') && 
            !href.includes('perfil') && !href.includes('login') && 
            !href.includes('logout')) {
            
            // Verificar si el perfil está completo antes de permitir la navegación
            if (sessionStorage.getItem('profile_complete') !== 'true') {
                e.preventDefault(); // Prevenir la navegación
                
                Swal.fire({
                    title: 'Perfil incompleto',
                    text: 'Para acceder a esta sección, primero debes completar tu información personal',
                    icon: 'warning',
                    confirmButtonText: 'Completar perfil',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    window.location.href = 'perfil';
                });
            }
        }
    });
});

/**
 * Verifica si el perfil del usuario está completo
 * Si no está completo, muestra un mensaje y redirige a la página de perfil
 */
function checkUserProfile() {
    // Si estamos en la página de perfil, no necesitamos hacer la verificación
    if (window.location.href.includes('perfil')) {
        // Si estamos en la página de perfil, vamos a forzar una nueva verificación cada vez
        // que se cargue para actualizar el estado después de completar el perfil
        sessionStorage.removeItem('profile_complete');
        return;
    }
    
    $.ajax({
        url: 'ajax/profile.ajax.php',
        type: 'POST',
        data: {
            action: 'checkProfile'
            // Eliminamos el parámetro forceUpdate que no está siendo procesado correctamente
        },
        dataType: 'json',
        success: function(response) {
            console.log("Verificación de perfil:", response);
            
            // Guardar el estado del perfil en sessionStorage para usarlo en el interceptor de enlaces
            sessionStorage.setItem('profile_complete', response.complete ? 'true' : 'false');
            
            if (response.status === 'success' && !response.complete && response.redirect) {
                Swal.fire({
                    title: 'Perfil incompleto',
                    text: 'Para continuar usando el sistema, necesitas completar tu información personal',
                    icon: 'info',
                    confirmButtonText: 'Completar perfil',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Modificar la URL para usar la ruta directa en lugar de index.php?route=...
                        window.location.href = 'perfil';
                    }
                });
            } else if (response.status === 'error' && response.redirect) {
                // Modificar la URL para usar la ruta directa en lugar de index.php?route=...
                if (response.redirect.includes('route=login')) {
                    window.location.href = 'login';
                } else {
                    window.location.href = response.redirect;
                }
            }
        },
        error: function(xhr, status, error) {
            console.error("Error verificando perfil:", error);
            // Mostrar error en consola para depuración
            console.error("Respuesta del servidor:", xhr.responseText);
        }
    });
}