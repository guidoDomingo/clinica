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
     * @param int $doctorId ID del doctor para filtrar preformatos (opcional)
     * @return array Arreglo con los preformatos
     */
    public static function ctrGetPreformatos($tipo, $doctorId = null) {
        error_log("ctrGetPreformatos - Tipo: $tipo, Doctor ID: " . ($doctorId ? $doctorId : 'ninguno'));
        
        try {
            if ($doctorId) {
                // Primero intentamos obtener el doctor_id asociado al usuario
                $db = Conexion::conectar();
                $stmt = $db->prepare(
                    "SELECT 
                        d.doctor_id
                    FROM person_system_user psu 
                    JOIN rh_doctors d ON psu.person_id = d.person_id
                    WHERE psu.system_user_id = :user_id
                    LIMIT 1"
                );
                
                $stmt->bindParam(":user_id", $doctorId, PDO::PARAM_INT);
                $stmt->execute();
                $doctorResult = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($doctorResult) {
                    $doctorId = $doctorResult['doctor_id'];
                    error_log("Doctor ID encontrado para usuario $doctorId: " . $doctorResult['doctor_id']);
                } else {
                    // Si no se encontró como user_id, verificar si es directamente un doctor_id
                    $stmt = $db->prepare("SELECT doctor_id FROM rh_doctors WHERE doctor_id = :doctor_id LIMIT 1");
                    $stmt->bindParam(":doctor_id", $doctorId, PDO::PARAM_INT);
                    $stmt->execute();
                    $doctorDirecto = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($doctorDirecto) {
                        error_log("Doctor ID confirmado directamente: " . $doctorId);
                    } else {
                        error_log("No se encontró doctor para el ID proporcionado: " . $doctorId);
                        // No se establece a null para permitir que se intente filtrar por el ID original
                    }
                }
            }
            
            // Ahora obtenemos los preformatos filtrando por tipo y opcionalmente por doctor_id
            return ModelPreformatos::mdlGetPreformatos($tipo, $doctorId);
        } catch (Exception $e) {
            error_log("Error en ctrGetPreformatos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene todos los preformatos con opciones de filtrado
     * @param array $filtros Filtros a aplicar (tipo, propietario, título)
     * @return array Arreglo con los preformatos filtrados
     */
    public static function ctrGetAllPreformatos($filtros = []) {
        // Si hay filtro de usuario (creado_por), extraerlo para pasarlo a la función específica
        $usuarioId = !empty($filtros['creado_por']) ? $filtros['creado_por'] : null;
        
        // Si hay filtros, usamos el método con filtros, sino el ordenado simple
        if (!empty($filtros) && (isset($filtros['tipo']) || isset($filtros['titulo']))) {
            return ModelPreformatos::mdlGetAllPreformatos($filtros);
        } else {
            // Pasamos el ID del usuario para filtrar, incluso cuando no hay otros filtros
            return ModelPreformatos::mdlGetAllPreformatosOrdered($usuarioId);
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