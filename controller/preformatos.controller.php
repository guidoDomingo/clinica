<?php
require_once __DIR__ . "/../model/preformatos.model.php";

class ControllerPreformatos {
    /**
     * Obtiene todos los motivos comunes activos
     * @return array Arreglo con los motivos comunes
     */
    public static function ctrGetMotivosComunes() {
        return ModelPreformatos::mdlGetMotivosComunes();
    }
    
    /**
     * Obtiene todos los preformatos activos de un tipo específico
     * @param string $tipo Tipo de preformato ('consulta' o 'receta')
     * @return array Arreglo con los preformatos
     */
    public static function ctrGetPreformatos($tipo) {
        return ModelPreformatos::mdlGetPreformatos($tipo);
    }
    
    /**
     * Crea un nuevo motivo común
     * @param array $datos Datos del motivo común
     * @return string 'ok' si se creó correctamente, 'error' en caso contrario
     */
    public static function ctrCrearMotivoComun($datos) {
        // Validar que el nombre no esté vacío
        if (empty($datos['nombre'])) {
            return "error_nombre";
        }
        
        return ModelPreformatos::mdlCrearMotivoComun($datos);
    }
    
    /**
     * Crea un nuevo preformato
     * @param array $datos Datos del preformato
     * @return string 'ok' si se creó correctamente, 'error' en caso contrario
     */
    public static function ctrCrearPreformato($datos) {
        // Validar que el nombre y contenido no estén vacíos
        if (empty($datos['nombre']) || empty($datos['contenido'])) {
            return "error_datos";
        }
        
        // Validar que el tipo sea válido
        if ($datos['tipo'] != 'consulta' && $datos['tipo'] != 'receta') {
            return "error_tipo";
        }
        
        return ModelPreformatos::mdlCrearPreformato($datos);
    }
}