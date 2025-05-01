<?php
require_once "../controller/preformatos.controller.php";

class PreformatosAjax {
    /**
     * Obtiene todos los motivos comunes activos
     */
    public function ajaxGetMotivosComunes() {
        $motivos = ControllerPreformatos::ctrGetMotivosComunes();
        echo json_encode([
            'status' => 'success',
            'data' => $motivos
        ]);
    }
    
    /**
     * Obtiene todos los preformatos activos de un tipo específico
     * @param string $tipo Tipo de preformato ('consulta', 'receta', etc.)
     * @param integer $doctorId ID del médico conectado (opcional)
     */
    public function ajaxGetPreformatos($tipo, $doctorId = null) {
        $preformatos = ControllerPreformatos::ctrGetPreformatos($tipo, $doctorId);
        echo json_encode([
            'status' => 'success',
            'data' => $preformatos
        ]);
    }
    
    /**
     * Obtiene todos los preformatos con opciones de filtrado
     * @param array $filtros Filtros a aplicar (tipo, propietario, título)
     */
    public function ajaxGetAllPreformatos($filtros = []) {
        $preformatos = ControllerPreformatos::ctrGetAllPreformatos($filtros);
        // Añadir información de diagnóstico
        echo json_encode([
            'status' => 'success',
            'data' => $preformatos,
            'debug_info' => [
                'filtros_aplicados' => $filtros,
                'total_registros' => count($preformatos)
            ]
        ]);
    }
    
    /**
     * Obtiene la lista de usuarios para el selector de propietarios
     */
    public function ajaxGetUsuarios() {
        $usuarios = ControllerPreformatos::ctrGetUsuarios();
        echo json_encode([
            'status' => 'success',
            'data' => $usuarios
        ]);
    }
    
    /**
     * Obtiene un preformato por su ID
     * @param int $idPreformato ID del preformato a obtener
     */
    public function ajaxGetPreformatoById($idPreformato) {
        $preformato = ControllerPreformatos::ctrGetPreformatoById($idPreformato);
        
        if ($preformato) {
            echo json_encode([
                'status' => 'success',
                'data' => $preformato
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudo obtener la información del preformato'
            ]);
        }
    }
    
    /**
     * Crea un nuevo preformato
     * @param array $datos Datos del preformato
     */
    public function ajaxCrearPreformato($datos) {
        $resultado = ControllerPreformatos::ctrCrearPreformato($datos);
        
        if ($resultado === "ok") {
            echo json_encode([
                'status' => 'success',
                'message' => 'Preformato creado correctamente'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al crear el preformato'
            ]);
        }
    }
    
    /**
     * Actualiza un preformato existente
     * @param array $datos Datos del preformato
     */
    public function ajaxActualizarPreformato($datos) {
        $resultado = ControllerPreformatos::ctrActualizarPreformato($datos);
        
        if ($resultado === "ok") {
            echo json_encode([
                'status' => 'success',
                'message' => 'Preformato actualizado correctamente'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al actualizar el preformato'
            ]);
        }
    }
    
    /**
     * Elimina un preformato
     * @param int $idPreformato ID del preformato a eliminar
     */
    public function ajaxEliminarPreformato($idPreformato) {
        $resultado = ControllerPreformatos::ctrEliminarPreformato($idPreformato);
        
        if ($resultado === "ok") {
            echo json_encode([
                'status' => 'success',
                'message' => 'Preformato eliminado correctamente'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al eliminar el preformato'
            ]);
        }
    }
}

// Procesar solicitudes AJAX
if (isset($_POST['operacion'])) {
    $preformatos = new PreformatosAjax();
    
    // Guardar información de diagnóstico
    error_log("Operación solicitada: " . $_POST['operacion']);
    
    switch ($_POST['operacion']) {
        case 'getMotivosComunes':
            $preformatos->ajaxGetMotivosComunes();
            break;
            
        case 'getPreformatosConsulta':
            $doctorId = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : null;
            $preformatos->ajaxGetPreformatos('consulta', $doctorId);
            break;
            
        case 'getPreformatosReceta':
            $doctorId = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : null;
            $preformatos->ajaxGetPreformatos('receta', $doctorId);
            break;
            
        case 'getPreformatosRecetaAnteojos':
            $doctorId = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : null;
            $preformatos->ajaxGetPreformatos('receta_anteojos', $doctorId);
            break;
            
        case 'getPreformatosOrdenEstudios':
            $doctorId = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : null;
            $preformatos->ajaxGetPreformatos('orden_estudios', $doctorId);
            break;
            
        case 'getPreformatosOrdenCirugias':
            $doctorId = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : null;
            $preformatos->ajaxGetPreformatos('orden_cirugias', $doctorId);
            break;
            
        case 'getAllPreformatos':
            $filtros = isset($_POST['filtros']) ? $_POST['filtros'] : [];
            error_log("Filtros recibidos: " . json_encode($filtros));
            $preformatos->ajaxGetAllPreformatos($filtros);
            break;
            
        case 'getUsuarios':
            $preformatos->ajaxGetUsuarios();
            break;
            
        case 'getPreformatoById':
            if (isset($_POST['id_preformato'])) {
                $preformatos->ajaxGetPreformatoById($_POST['id_preformato']);
            }
            break;
            
        case 'crearPreformato':
            $datos = [
                'nombre' => $_POST['nombre'],
                'contenido' => $_POST['contenido'],
                'tipo' => $_POST['tipo'],
                'creado_por' => $_POST['creado_por']
            ];
            $preformatos->ajaxCrearPreformato($datos);
            break;
            
        case 'actualizarPreformato':
            $datos = [
                'id_preformato' => $_POST['id_preformato'],
                'nombre' => $_POST['nombre'],
                'contenido' => $_POST['contenido'],
                'tipo' => $_POST['tipo'],
                'creado_por' => $_POST['creado_por']
            ];
            $preformatos->ajaxActualizarPreformato($datos);
            break;
            
        case 'eliminarPreformato':
            if (isset($_POST['id_preformato'])) {
                $preformatos->ajaxEliminarPreformato($_POST['id_preformato']);
            }
            break;
            
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Operación no reconocida'
            ]);
    }
} else {
    // Si no hay operación especificada pero se accede directamente a la URL, mostrar todos los preformatos
    $preformatos = new PreformatosAjax();
    $preformatos->ajaxGetAllPreformatos([]);
}