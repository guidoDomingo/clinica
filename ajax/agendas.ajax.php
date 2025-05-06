<?php
require_once "../controller/agendas.controller.php";

class AjaxAgendas {
    /**
     * Obtiene todas las agendas médicas
     */
    public function ajaxObtenerAgendas() {
        $agendas = ControllerAgendas::ctrObtenerAgendas();
        echo json_encode(["status" => "success", "data" => $agendas]);
    }

    /**
     * Obtiene una agenda específica por ID
     */
    public function ajaxObtenerAgendaPorId() {
        if (isset($_POST["agenda_id"])) {
            $agenda = ControllerAgendas::ctrObtenerAgendaPorId($_POST["agenda_id"]);
            echo json_encode(["status" => "success", "data" => $agenda]);
        } else {
            echo json_encode(["status" => "error", "message" => "ID de agenda no proporcionado"]);
        }
    }

    /**
     * Obtiene agendas por médico
     */
    public function ajaxObtenerAgendasPorMedico() {
        if (isset($_POST["medico_id"])) {
            $agendas = ControllerAgendas::ctrObtenerAgendasPorMedico($_POST["medico_id"]);
            echo json_encode(["status" => "success", "data" => $agendas]);
        } else {
            echo json_encode(["status" => "error", "message" => "ID de médico no proporcionado"]);
        }
    }

    /**
     * Guarda una agenda (crear o actualizar)
     */
    public function ajaxGuardarAgenda() {
        if (isset($_POST["medico_id"])) {
            $datos = [
                "agenda_id" => isset($_POST["agenda_id"]) ? $_POST["agenda_id"] : "",
                "medico_id" => $_POST["medico_id"],
                "agenda_descripcion" => isset($_POST["agenda_descripcion"]) ? $_POST["agenda_descripcion"] : "",
                "agenda_estado" => isset($_POST["agenda_estado"]) ? $_POST["agenda_estado"] === "true" : true
            ];

            $resultado = ControllerAgendas::ctrGuardarAgenda($datos);
            echo json_encode($resultado);
        } else {
            echo json_encode(["error" => true, "mensaje" => "Datos incompletos"]);
        }
    }

    /**
     * Elimina una agenda
     */
    public function ajaxEliminarAgenda() {
        if (isset($_POST["agenda_id"])) {
            $resultado = ControllerAgendas::ctrEliminarAgenda($_POST["agenda_id"]);
            echo json_encode($resultado);
        } else {
            echo json_encode(["error" => true, "mensaje" => "ID de agenda no proporcionado"]);
        }
    }

    /**
     * Obtiene los detalles de horarios de una agenda
     */
    public function ajaxObtenerDetallesAgenda() {
        if (isset($_POST["agenda_id"])) {
            $detalles = ControllerAgendas::ctrObtenerDetallesAgenda($_POST["agenda_id"]);
            echo json_encode(["status" => "success", "data" => $detalles]);
        } else {
            echo json_encode(["status" => "error", "message" => "ID de agenda no proporcionado"]);
        }
    }
    
    /**
     * Obtiene un detalle específico de agenda por ID
     */
    public function ajaxObtenerDetalleAgenda() {
        if (isset($_POST["detalle_id"])) {
            $detalle = ControllerAgendas::ctrObtenerDetalleAgenda($_POST["detalle_id"]);
            echo json_encode(["status" => "success", "data" => $detalle]);
        } else {
            echo json_encode(["status" => "error", "message" => "ID de detalle no proporcionado"]);
        }
    }

    /**
     * Guarda un detalle de horario (crear o actualizar)
     */
    public function ajaxGuardarDetalleAgenda() {
        if (isset($_POST["agenda_id"]) && isset($_POST["dia_semana"]) && 
            isset($_POST["turno_id"]) && isset($_POST["sala_id"]) && 
            isset($_POST["hora_inicio"]) && isset($_POST["hora_fin"])) {
            
            $datos = [
                "detalle_id" => isset($_POST["detalle_id"]) ? $_POST["detalle_id"] : "",
                "agenda_id" => $_POST["agenda_id"],
                "turno_id" => $_POST["turno_id"],
                "sala_id" => $_POST["sala_id"],
                "dia_semana" => $_POST["dia_semana"],
                "hora_inicio" => $_POST["hora_inicio"],
                "hora_fin" => $_POST["hora_fin"],
                "intervalo_minutos" => isset($_POST["intervalo_minutos"]) ? $_POST["intervalo_minutos"] : 15,
                "cupo_maximo" => isset($_POST["cupo_maximo"]) ? $_POST["cupo_maximo"] : 1,
                "detalle_estado" => isset($_POST["detalle_estado"]) ? $_POST["detalle_estado"] === "true" : true
            ];

            $resultado = ControllerAgendas::ctrGuardarDetalleAgenda($datos);
            echo json_encode($resultado);
        } else {
            echo json_encode(["error" => true, "mensaje" => "Datos incompletos"]);
        }
    }

    /**
     * Elimina un detalle de horario
     */
    public function ajaxEliminarDetalleAgenda() {
        if (isset($_POST["detalle_id"])) {
            $resultado = ControllerAgendas::ctrEliminarDetalleAgenda($_POST["detalle_id"]);
            echo json_encode($resultado);
        } else {
            echo json_encode(["error" => true, "mensaje" => "ID de detalle no proporcionado"]);
        }
    }

    /**
     * Obtiene todos los médicos disponibles
     */
    public function ajaxObtenerMedicos() {
        $medicos = ControllerAgendas::ctrObtenerMedicos();
        echo json_encode(["status" => "success", "data" => $medicos]);
    }

    /**
     * Obtiene todos los turnos disponibles
     */
    public function ajaxObtenerTurnos() {
        $turnos = ControllerAgendas::ctrObtenerTurnos();
        echo json_encode(["status" => "success", "data" => $turnos]);
    }

    /**
     * Obtiene todas las salas disponibles
     */
    public function ajaxObtenerSalas() {
        $salas = ControllerAgendas::ctrObtenerSalas();
        echo json_encode(["status" => "success", "data" => $salas]);
    }
}

// Procesar las solicitudes AJAX
if (isset($_POST["action"])) {
    $ajax = new AjaxAgendas();
    
    switch ($_POST["action"]) {
        case "obtenerAgendas":
            $ajax->ajaxObtenerAgendas();
            break;
        case "obtenerAgendaPorId":
            $ajax->ajaxObtenerAgendaPorId();
            break;
        case "obtenerAgendasPorMedico":
            $ajax->ajaxObtenerAgendasPorMedico();
            break;
        case "guardarAgenda":
            $ajax->ajaxGuardarAgenda();
            break;
        case "eliminarAgenda":
            $ajax->ajaxEliminarAgenda();
            break;
        case "obtenerDetallesAgenda":
            $ajax->ajaxObtenerDetallesAgenda();
            break;
        case "obtenerDetalleAgenda":
            $ajax->ajaxObtenerDetalleAgenda();
            break;
        case "guardarDetalleAgenda":
            $ajax->ajaxGuardarDetalleAgenda();
            break;
        case "eliminarDetalleAgenda":
            $ajax->ajaxEliminarDetalleAgenda();
            break;
        case "obtenerMedicos":
            $ajax->ajaxObtenerMedicos();
            break;
        case "obtenerTurnos":
            $ajax->ajaxObtenerTurnos();
            break;
        case "obtenerSalas":
            $ajax->ajaxObtenerSalas();
            break;
    }
}