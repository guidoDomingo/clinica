/**
 * Funciones para gestionar permisos en la interfaz de usuario
 */

// Objeto global para almacenar los permisos del usuario
const userPermisos = {};

/**
 * Inicializa el sistema de permisos en el lado del cliente
 * @param {Object} permisos Objeto con los permisos del usuario
 */
function initPermisos(permisos) {
    // Guardar permisos en el objeto global
    Object.assign(userPermisos, permisos);
    
    // Aplicar permisos a la interfaz
    aplicarPermisosUI();
    
    console.log('Permisos inicializados:', userPermisos);
}

/**
 * Verifica si el usuario tiene un permiso específico
 * @param {String} permiso Nombre del permiso a verificar
 * @return {Boolean} True si tiene el permiso, false en caso contrario
 */
function tienePermiso(permiso) {
    // Si no hay permisos inicializados o es un array vacío, devolver false
    if (!userPermisos || !userPermisos.permisos || !Array.isArray(userPermisos.permisos)) {
        return false;
    }
    
    // Verificar si el usuario tiene el permiso específico
    return userPermisos.permisos.some(p => p.perm_name === permiso);
}

/**
 * Aplica los permisos a la interfaz de usuario
 * Oculta elementos según los permisos
 */
function aplicarPermisosUI() {
    // Solo aplicamos permisos si realmente tenemos datos de permisos
    const tienePermisosCargados = permisosInicializados();
    
    // Si no hay permisos cargados pero el usuario está en páginas que requieren autenticación,
    // simplemente no aplicamos restricciones (mejor permitir acceso que bloquear incorrectamente)
    if (!tienePermisosCargados) {
        console.log('No hay permisos inicializados, no se aplican restricciones de UI');
        return;
    }
    
    console.log('Aplicando permisos a la interfaz de usuario');
    
    // Elementos con atributo data-permiso
    document.querySelectorAll('[data-permiso]').forEach(element => {
        const permiso = element.getAttribute('data-permiso');
        
        if (!tienePermiso(permiso)) {
            // Si no tiene permiso, ocultar el elemento
            element.classList.add('d-none');
            
            // Si es un botón o un enlace, deshabilitarlo también
            if (element.tagName === 'BUTTON' || element.tagName === 'A') {
                element.disabled = true;
                element.classList.add('disabled');
            }
        }
    });
    
    // Elementos con atributo data-requires-any-permiso (necesita al menos uno de los permisos listados)
    document.querySelectorAll('[data-requires-any-permiso]').forEach(element => {
        const permisos = element.getAttribute('data-requires-any-permiso').split(',');
        let tieneAlgunPermiso = false;
        
        for (const permiso of permisos) {
            if (tienePermiso(permiso.trim())) {
                tieneAlgunPermiso = true;
                break;
            }
        }
        
        if (!tieneAlgunPermiso) {
            // Si no tiene ninguno de los permisos, ocultar el elemento
            element.classList.add('d-none');
            
            // Si es un botón o un enlace, deshabilitarlo también
            if (element.tagName === 'BUTTON' || element.tagName === 'A') {
                element.disabled = true;
                element.classList.add('disabled');
            }
        }
    });
    
    // Elementos con atributo data-requires-all-permiso (necesita todos los permisos listados)
    document.querySelectorAll('[data-requires-all-permiso]').forEach(element => {
        const permisos = element.getAttribute('data-requires-all-permiso').split(',');
        let tieneTodosLosPermisos = true;
        
        for (const permiso of permisos) {
            if (!tienePermiso(permiso.trim())) {
                tieneTodosLosPermisos = false;
                break;
            }
        }
        
        if (!tieneTodosLosPermisos) {
            // Si no tiene todos los permisos, ocultar el elemento
            element.classList.add('d-none');
            
            // Si es un botón o un enlace, deshabilitarlo también
            if (element.tagName === 'BUTTON' || element.tagName === 'A') {
                element.disabled = true;
                element.classList.add('disabled');
            }
        }
    });
}

/**
 * Verifica si los permisos ya están inicializados
 * @returns {Boolean} True si los permisos ya están inicializados
 */
function permisosInicializados() {
    return userPermisos && userPermisos.permisos && userPermisos.permisos.length > 0;
}

/**
 * Carga los permisos del usuario desde el servidor
 * Solo se ejecuta si los permisos no han sido inicializados directamente en la página
 */
function cargarPermisos() {
    // Si los permisos ya están inicializados, no hacemos nada
    if (permisosInicializados()) {
        console.log('Permisos ya inicializados, no es necesario cargar desde el servidor');
        return;
    }
    
    // Intenta cargar los permisos mediante AJAX
    fetch('ajax/permisos.ajax.php', {
        method: 'POST',
        body: new URLSearchParams({
            operacion: 'getPermisosUsuario'
        }),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Verificar estructura de datos para debugging
            console.log('Datos de permisos recibidos:', data);
            initPermisos(data.data);
        } else {
            console.log('No se pudieron cargar permisos:', data.message);
            // Si el usuario no está autenticado, inicializamos con permisos vacíos
            if (data.message === 'Usuario no autenticado') {
                initPermisos({
                    permisos: [],
                    roles: []
                });
            }
        }
    })
    .catch(error => {
        console.error('Error al cargar permisos:', error);
    });
}

// Cargar permisos cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', cargarPermisos);
