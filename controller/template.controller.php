<?php
/**
 * Controlador de plantillas
 * Maneja la carga y visualización de las plantillas del sistema
 */
class ControllerTemplate {
    /**
     * Método para cargar la plantilla principal del sistema
     * @return void
     */
    public function ctrTemplate() {
        // Incluir la plantilla principal
        include "view/template.php";
    }
      /**
     * Validar acceso según permisos
     * @param string $permiso Nombre del permiso requerido
     * @return boolean
     */
    public static function validarPermiso($permiso) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        require_once "controller/permisos.controller.php";
        return PermisosController::tienePermiso($permiso);
    }
}