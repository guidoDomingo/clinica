<?php
/**
 * Helpers para manejar permisos en las vistas
 */

/**
 * Verifica si el usuario actual tiene un permiso específico
 * @param string $permiso El permiso a verificar
 * @return boolean True si tiene el permiso, false en caso contrario
 */
function tiene_permiso($permiso) {
    return PermisosController::tienePermiso($permiso);
}

/**
 * Muestra u oculta un elemento según si el usuario tiene un permiso específico
 * @param string $permiso El permiso a verificar
 * @return string La clase 'd-none' si no tiene permiso, cadena vacía si lo tiene
 */
function mostrar_si_tiene_permiso($permiso) {
    return tiene_permiso($permiso) ? '' : 'd-none';
}

/**
 * Obtiene la lista de permisos del usuario actual
 * @return array Lista de permisos
 */
function get_permisos_usuario() {
    return PermisosController::getPermisosUsuarioActual();
}

/**
 * Obtiene la lista de roles del usuario actual
 * @return array Lista de roles
 */
function get_roles_usuario() {
    return PermisosController::getRolesUsuarioActual();
}
