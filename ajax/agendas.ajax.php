<?php
/**
 * Archivo: agendas.ajax.php
 * Descripción: Maneja las solicitudes AJAX para el módulo de agendas médicas
 */

require_once "../controller/agendas.controller.php";
require_once "../model/agendas.model.php";

class AgendasAjax {
    
    /**
     * Método para listar todas las agendas
     */
    public function listarAgendas() {
        try {
            $agendas = AgendasController::ctrListarAgendas();
            
            if (!is_array($agendas)) {
                // Si no es un array, devolver un array vacío
                echo json_encode(["data" => []]);
                return;
            }
            
            $data = [];
            
            for ($i = 0; $i < count($agendas); $i++) {
                // Formatear días de la semana
                $diasSemana = $this->formatearDiasSemana($agendas[$i]["dia_semana"]);
                
                // Formatear horario
                $horario = $agendas[$i]["hora_inicio"] . " - " . $agendas[$i]["hora_fin"];
                
                // Formatear estado (se puede personalizar según necesidades)
                $estado = '<span class="badge badge-success">Activo</span>';
                
                // Formatear detalle
                $detalle = '<strong>Días:</strong> ' . $diasSemana . '<br>' . 
                          '<strong>Horario:</strong> ' . $horario . '<br>' . 
                          '<strong>Turno:</strong> ' . $agendas[$i]["turno_nombre"];
                
                // Botones de acciones
                $acciones = '<div class="btn-group">';
                $acciones .= '<button class="btn btn-warning btn-sm btnEditarAgenda" idAgenda="'.$agendas[$i]["id"].'" title="Editar"><i class="fas fa-pencil-alt"></i></button>';
                $acciones .= '<button class="btn btn-danger btn-sm btnEliminarAgenda" idAgenda="'.$agendas[$i]["id"].'" title="Eliminar"><i class="fas fa-trash"></i></button>';
                $acciones .= '</div>';
                
                // Agregar el registro al array de datos
                $data[] = [
                    "id" => $agendas[$i]["id"],
                    "estado" => $estado,
                    "medico" => $agendas[$i]["medico_nombre"],
                    "detalle" => $detalle,
                    "sala" => $agendas[$i]["consultorio_nombre"],
                    "acciones" => $acciones
                ];
            }
            
            // Usar json_encode para generar el JSON correctamente
            echo json_encode(["data" => $data], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en listarAgendas: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/ajax.log");
            echo json_encode(["data" => []]);
        }
    }
    
    /**
     * Método para listar todos los bloqueos
     */
    public function listarBloqueos() {
        try {
            $bloqueos = AgendasController::ctrListarBloqueos();
            
            if (!is_array($bloqueos)) {
                // Si no es un array, devolver un array vacío
                echo json_encode(["data" => []]);
                return;
            }
            
            $data = [];
            
            for ($i = 0; $i < count($bloqueos); $i++) {
                // Formatear fechas
                $fechaInicio = date("d/m/Y", strtotime($bloqueos[$i]["fecha_inicio"]));
                $fechaFin = date("d/m/Y", strtotime($bloqueos[$i]["fecha_fin"]));
                
                // Botones de acciones
                $acciones = '<div class="btn-group">';
                $acciones .= '<button class="btn btn-warning btn-sm btnEditarBloqueo" idBloqueo="'.$bloqueos[$i]["id"].'" title="Editar"><i class="fas fa-pencil-alt"></i></button>';
                $acciones .= '<button class="btn btn-danger btn-sm btnEliminarBloqueo" idBloqueo="'.$bloqueos[$i]["id"].'" title="Eliminar"><i class="fas fa-trash"></i></button>';
                $acciones .= '</div>';
                
                // Agregar el registro al array de datos
                $data[] = [
                    "id" => $bloqueos[$i]["id"],
                    "medico" => $bloqueos[$i]["medico_nombre"],
                    "fecha_inicio" => $fechaInicio,
                    "fecha_fin" => $fechaFin,
                    "motivo" => $bloqueos[$i]["motivo"],
                    "acciones" => $acciones
                ];
            }
            
            // Usar json_encode para generar el JSON correctamente
            echo json_encode(["data" => $data], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en listarBloqueos: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/ajax.log");
            echo json_encode(["data" => []]);
        }
    }
    
    /**
     * Método para cargar médicos
     */
    public function cargarMedicos() {
        $medicos = AgendasController::ctrCargarMedicos();
        echo json_encode($medicos);
    }
    
    /**
     * Método para cargar consultorios
     */
    public function cargarConsultorios() {
        $consultorios = AgendasController::ctrCargarConsultorios();
        echo json_encode($consultorios);
    }
    
    /**
     * Método para cargar turnos
     */
    public function cargarTurnos() {
        $turnos = AgendasController::ctrCargarTurnos();
        echo json_encode($turnos);
    }
    
    /**
     * Método para cargar salas
     */
    public function cargarSalas() {
        $salas = AgendasController::ctrCargarSalas();
        echo json_encode($salas);
    }
    
    /**
     * Método para guardar una agenda
     */
    public function guardarAgenda() {
        $datos = array(
            "medico_id" => $_POST["medico_id"],
            "dias" => $_POST["dias"],
            "hora_inicio" => $_POST["hora_inicio"],
            "hora_fin" => $_POST["hora_fin"],
            "duracion_turno" => $_POST["duracion_turno"],
            "consultorio_id" => $_POST["consultorio_id"],
            "estado" => $_POST["estado"],
            "turno_id" => $_POST["turno_id"]
        );
        
        $respuesta = AgendasController::ctrGuardarAgenda($datos);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Método para actualizar una agenda
     */
    public function actualizarAgenda() {
        $datos = array(
            "id" => $_POST["id"],
            "medico_id" => $_POST["medico_id"],
            "dias" => $_POST["dias"],
            "hora_inicio" => $_POST["hora_inicio"],
            "hora_fin" => $_POST["hora_fin"],
            "duracion_turno" => $_POST["duracion_turno"],
            "consultorio_id" => $_POST["consultorio_id"],
            "estado" => $_POST["estado"]
        );
        
        $respuesta = AgendasController::ctrActualizarAgenda($datos);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Método para guardar un bloqueo
     */
    public function guardarBloqueo() {
        $datos = array(
            "medico_id" => $_POST["medico_id"],
            "fecha_inicio" => $_POST["fecha_inicio"],
            "fecha_fin" => $_POST["fecha_fin"],
            "motivo" => $_POST["motivo"]
        );
        
        $respuesta = AgendasController::ctrGuardarBloqueo($datos);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Método para actualizar un bloqueo
     */
    public function actualizarBloqueo() {
        $datos = array(
            "id" => $_POST["id"],
            "medico_id" => $_POST["medico_id"],
            "fecha_inicio" => $_POST["fecha_inicio"],
            "fecha_fin" => $_POST["fecha_fin"],
            "motivo" => $_POST["motivo"]
        );
        
        $respuesta = AgendasController::ctrActualizarBloqueo($datos);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Método para obtener una agenda
     */
    public function obtenerAgenda() {
        $id = $_POST["id"];
        $respuesta = AgendasController::ctrObtenerAgenda($id);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Método para obtener un bloqueo
     */
    public function obtenerBloqueo() {
        $id = $_POST["id"];
        $respuesta = AgendasController::ctrObtenerBloqueo($id);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Método para eliminar una agenda
     */
    public function eliminarAgenda() {
        $id = $_POST["id"];
        $respuesta = AgendasController::ctrEliminarAgenda($id);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Método para eliminar un bloqueo
     */
    public function eliminarBloqueo() {
        $id = $_POST["id"];
        $respuesta = AgendasController::ctrEliminarBloqueo($id);
        
        echo json_encode($respuesta);
    }
    
    /**
     * Método para formatear los días de la semana
     */
    private function formatearDiasSemana($dias) {
        $diasArray = explode(',', $dias);
        $diasFormateados = [];
        
        $nombresDias = [
            '1' => 'Lunes',
            '2' => 'Martes',
            '3' => 'Miércoles',
            '4' => 'Jueves',
            '5' => 'Viernes',
            '6' => 'Sábado',
            '7' => 'Domingo'
        ];
        
        foreach ($diasArray as $dia) {
            if (isset($nombresDias[$dia])) {
                $diasFormateados[] = $nombresDias[$dia];
            }
        }
        
        // Si son todos los días de lunes a viernes
        if (count($diasFormateados) == 5 && 
            in_array('Lunes', $diasFormateados) && 
            in_array('Martes', $diasFormateados) && 
            in_array('Miércoles', $diasFormateados) && 
            in_array('Jueves', $diasFormateados) && 
            in_array('Viernes', $diasFormateados)) {
            return 'Lunes a Viernes';
        }
        
        // Si son todos los días de la semana
        if (count($diasFormateados) == 7) {
            return 'Todos los días';
        }
        
        return implode(', ', $diasFormateados);
    }
}

// Procesar la solicitud AJAX
// Asegurar que la respuesta sea siempre JSON
header('Content-Type: application/json; charset=utf-8');

try {
    if (isset($_POST["accion"])) {
        $agendas = new AgendasAjax();
        
        switch ($_POST["accion"]) {
            case "listar":
                $agendas->listarAgendas();
                break;
                
            case "listarBloqueos":
                $agendas->listarBloqueos();
                break;
                
            case "cargarMedicos":
                $agendas->cargarMedicos();
                break;
                
            case "cargarConsultorios":
                $agendas->cargarConsultorios();
                break;
                
            case "cargarTurnos":
                $agendas->cargarTurnos();
                break;
                
            case "cargarSalas":
                $agendas->cargarSalas();
                break;
                
            case "guardar":
                $agendas->guardarAgenda();
                break;
                
            case "actualizar":
                $agendas->actualizarAgenda();
                break;
                
            case "guardarBloqueo":
                $agendas->guardarBloqueo();
                break;
                
            case "actualizarBloqueo":
                $agendas->actualizarBloqueo();
                break;
                
            case "obtener":
                $agendas->obtenerAgenda();
                break;
                
            case "obtenerBloqueo":
                $agendas->obtenerBloqueo();
                break;
                
            case "eliminar":
                $agendas->eliminarAgenda();
                break;
                
            case "eliminarBloqueo":
                $agendas->eliminarBloqueo();
                break;
                
            default:
                echo json_encode(array("ok" => false, "mensaje" => "Acción no válida"));
                break;
        }
    } else {
        echo json_encode(array("ok" => false, "mensaje" => "No se especificó ninguna acción"));
    }
} catch (Exception $e) {
    // Registrar el error pero devolver una respuesta JSON válida
    error_log("Error en agendas.ajax.php: " . $e->getMessage(), 3, "c:/laragon/www/clinica/logs/ajax.log");
    echo json_encode(array("ok" => false, "mensaje" => "Error al procesar la solicitud"));
}