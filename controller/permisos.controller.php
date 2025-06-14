<?php

class PermisosController {
    
    private $permisosModel;
    
    public function __construct() {
        $this->permisosModel = new PermisosModel();
    }
      /**
     * Verifica si el usuario actual tiene un permiso específico
     * 
     * @param string $permiso Nombre del permiso a verificar
     * @return bool True si tiene el permiso, False si no
     */
    public static function tienePermiso($permiso) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $userId = $_SESSION['user_id'];
        $permisos = new PermisosModel();
        return $permisos->tienePermiso($userId, $permiso);
    }
    
    /**
     * Restringe el acceso a una página si el usuario no tiene el permiso requerido
     * 
     * @param string $permiso Nombre del permiso requerido
     * @param string $redirectUrl URL a redirigir si no tiene permisos (opcional)
     * @return void
     */
    public static function restringirAcceso($permiso, $redirectUrl = 'index.php?ruta=login') {
        if (!self::tienePermiso($permiso)) {
            echo '<script>
                  Swal.fire({
                      icon: "error",
                      title: "Acceso denegado",
                      text: "No tienes permisos suficientes para acceder a esta sección",
                      showConfirmButton: true
                  }).then(function() {
                      window.location.href = "'.$redirectUrl.'";
                  });
                  </script>';
            exit;
        }
    }
      /**
     * Obtiene todos los permisos del usuario actual
     * 
     * @return array Lista de permisos
     */
    public static function getPermisosUsuarioActual() {
        if (!isset($_SESSION['user_id'])) {
            return [];
        }
        
        $userId = $_SESSION['user_id'];
        $permisos = new PermisosModel();
        return $permisos->getPermisosByUsuario($userId);
    }
      /**
     * Obtiene todos los roles del usuario actual
     * 
     * @return array Lista de roles
     */
    public static function getRolesUsuarioActual() {
        if (!isset($_SESSION['user_id'])) {
            return [];
        }
        
        $userId = $_SESSION['user_id'];
        $permisos = new PermisosModel();
        return $permisos->getRolesByUsuario($userId);
    }
    
    /**
     * Método para llamar a la vista de gestión de roles y permisos
     */
    public function ctrMostrarVistaRolesPermisos() {
        // Primero verificamos que el usuario tenga permisos para administrar roles
        if (!self::tienePermiso('administrar_roles')) {
            echo '<script>
                  Swal.fire({
                      icon: "error",
                      title: "Acceso denegado",
                      text: "No tienes permisos para administrar roles y permisos",
                      showConfirmButton: true
                  }).then(function() {
                      window.location.href = "index.php?ruta=inicio";
                  });
                  </script>';
            return;
        }
        
        include "view/modules/roles.php";
    }
}
