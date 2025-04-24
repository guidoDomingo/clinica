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
     * @param string $tipo Tipo de preformato ('consulta', 'receta', etc.)
     * @return array Arreglo con los preformatos
     */
    public static function ctrGetPreformatos($tipo) {
        return ModelPreformatos::mdlGetPreformatos($tipo);
    }
    
    /**
     * Obtiene todos los preformatos con opciones de filtrado
     * @param array $filtros Filtros a aplicar (tipo, propietario, título)
     * @return array Arreglo con los preformatos filtrados
     */
    public static function ctrGetAllPreformatos($filtros = []) {
        // Si hay filtros, usamos el método con filtros, sino el ordenado simple
        if (!empty($filtros)) {
            return ModelPreformatos::mdlGetAllPreformatos($filtros);
        } else {
            return ModelPreformatos::mdlGetAllPreformatosOrdered();
        }
    }
    
    /**
     * Obtiene la lista de usuarios para el selector de propietarios
     * @return array Arreglo con los usuarios
     */
    public static function ctrGetUsuarios() {
        return ModelPreformatos::mdlGetUsuarios();
    }
    
    /**
     * Obtiene un preformato por su ID
     * @param int $idPreformato ID del preformato a obtener
     * @return array|false Arreglo con los datos del preformato o false si no existe
     */
    public static function ctrGetPreformatoById($idPreformato) {
        return ModelPreformatos::mdlGetPreformatoById($idPreformato);
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
        if (!in_array($datos['tipo'], ['consulta', 'receta', 'receta_anteojos', 'orden_estudios', 'orden_cirugias'])) {
            return "error_tipo";
        }
        
        return ModelPreformatos::mdlCrearPreformato($datos);
    }
    
    /**
     * Actualiza un preformato existente
     * @param array $datos Datos del preformato
     * @return string 'ok' si se actualizó correctamente, 'error' en caso contrario
     */
    public static function ctrActualizarPreformato($datos) {
        // Validar que el ID exista
        if (empty($datos['id_preformato'])) {
            return "error_id";
        }
        
        // Validar que el nombre y contenido no estén vacíos
        if (empty($datos['nombre']) || empty($datos['contenido'])) {
            return "error_datos";
        }
        
        // Validar que el tipo sea válido
        if (!in_array($datos['tipo'], ['consulta', 'receta', 'receta_anteojos', 'orden_estudios', 'orden_cirugias'])) {
            return "error_tipo";
        }
        
        return ModelPreformatos::mdlActualizarPreformato($datos);
    }
    
    /**
     * Elimina un preformato (inactivación lógica)
     * @param int $idPreformato ID del preformato a eliminar
     * @return string 'ok' si se eliminó correctamente, 'error' en caso contrario
     */
    public static function ctrEliminarPreformato($idPreformato) {
        // Validar que el ID exista
        if (empty($idPreformato)) {
            return "error_id";
        }
        
        return ModelPreformatos::mdlEliminarPreformato($idPreformato);
    }
}