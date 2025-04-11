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
}