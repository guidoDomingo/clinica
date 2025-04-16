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
     * @param string $tipo Tipo de preformato ('consulta' o 'receta')
     */
    public function ajaxGetPreformatos($tipo) {
        $preformatos = ControllerPreformatos::ctrGetPreformatos($tipo);
        echo json_encode([
            'status' => 'success',
            'data' => $preformatos
        ]);
    }
    
    /**
     * Crea un nuevo motivo común
     * @param array $datos Datos del motivo común
     */
    public function ajaxCrearMotivoComun($datos) {
        $resultado = ControllerPreformatos::ctrCrearMotivoComun($datos);
        
        if ($resultado === "ok") {
            echo json_encode([
                'status' => 'success',
                'message' => 'Motivo común creado correctamente'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al crear el motivo común'
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
}

// Procesar solicitudes AJAX
if (isset($_POST['operacion'])) {
    $preformatos = new PreformatosAjax();
    
    switch ($_POST['operacion']) {
        case 'getMotivosComunes':
            $preformatos->ajaxGetMotivosComunes();
            break;
            
        case 'getPreformatosConsulta':
            $preformatos->ajaxGetPreformatos('consulta');
            break;
            
        case 'getPreformatosReceta':
            $preformatos->ajaxGetPreformatos('receta');
            break;
            
        case 'crearMotivoComun':
            $datos = [
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'] ?? '',
                'creado_por' => $_POST['creado_por'] ?? 1
            ];
            $preformatos->ajaxCrearMotivoComun($datos);
            break;
            
        case 'crearPreformato':
            $datos = [
                'nombre' => $_POST['nombre'],
                'contenido' => $_POST['contenido'],
                'tipo' => $_POST['tipo'],
                'creado_por' => $_POST['creado_por'] ?? 1
            ];
            $preformatos->ajaxCrearPreformato($datos);
            break;
    }
}