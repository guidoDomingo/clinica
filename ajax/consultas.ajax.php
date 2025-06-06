<?php
require_once "../controller/consultas.controller.php";
require_once "../model/consultas.model.php";

class ConsultaAjax {
    public function ajaxGetConsultaPersona($persona) {
        $response = ModelConsulta::mdlGetConsultaPersona($persona);
        echo $response;
    }
    public function ajaxGetResumenConsulta($datos) {
        $response = ModelConsulta::mdlGetConsultaResumen($datos);
        echo json_encode($response);
    }
    
    public function ajaxGetHistorialConsultas($idPersona) {
        $response = ModelConsulta::mdlGetConsultaPersona($idPersona);
        // Asegurarse de que la respuesta sea un JSON válido
        $data = json_decode($response, true);
        if (isset($data['status']) && $data['status'] === 'success' && isset($data['data'])) {
            echo json_encode($data['data']);
        } else {
            echo json_encode([]);
        }
    }
    
    public function ajaxGetDetalleConsulta($idConsulta) {
        $response = ModelConsulta::mdlGetDetalleConsulta($idConsulta);
        echo $response; // El modelo ya devuelve un JSON formateado
    }
    
    public function ajaxGetAllConsultas() {
        $response = ModelConsulta::mdlGetAllConsultas();
        
        // Verificar si hay error y devolverlo como JSON válido
        if(isset($response['error'])) {
            echo json_encode(['error' => $response['error']]);
            return;
        }
        
        // Asegurar que la respuesta sea JSON válido
        echo json_encode($response);
    }
    
    public function ajaxGetConsultasByPaciente($idPersona) {
        $response = ModelConsulta::mdlGetConsultasByPaciente($idPersona);
        
        // Verificar si hay error y devolverlo como JSON válido
        if(isset($response['error'])) {
            echo json_encode(['error' => $response['error']]);
            return;
        }
        
        // Asegurar que la respuesta sea JSON válido
        echo json_encode($response);
    }
}
// Procesar la eliminación de una consulta
if (isset($_POST["id_persona"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "buscarConsultaPersona") {
    $getConsultaPersona = new ConsultaAjax();
    $getConsultaPersona->ajaxGetConsultaPersona($_POST["id_persona"]);
    
}
// Procesar consulta cantidad de consultas y ultima consulta
if (isset($_POST["id_persona"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "resumenConsulta") {
    $getResumenConsulta = new ConsultaAjax();
    $datos = array();
    foreach ($_POST as $key => $value) {
        $datos[$key] = $value;
    }
    $getResumenConsulta->ajaxGetResumenConsulta($datos);
    
}

// Procesar historial de consultas
if (isset($_POST["id_persona"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "historialConsultas") {
    $historialConsultas = new ConsultaAjax();
    $historialConsultas->ajaxGetHistorialConsultas($_POST["id_persona"]);
}

// Procesar detalle de consulta
if (isset($_POST["id_consulta"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "detalleConsulta") {
    $detalleConsulta = new ConsultaAjax();
    $detalleConsulta->ajaxGetDetalleConsulta($_POST["id_consulta"]);
}

// Procesar lista de todas las consultas
if (isset($_POST["operacion"]) && $_POST["operacion"] === "getAllConsultas") {
    $allConsultas = new ConsultaAjax();
    $allConsultas->ajaxGetAllConsultas();
}

// Procesar consultas por paciente
if (isset($_POST["id_persona"]) && isset($_POST["operacion"]) && $_POST["operacion"] === "getConsultasByPaciente") {
    $consultasPaciente = new ConsultaAjax();
    $consultasPaciente->ajaxGetConsultasByPaciente($_POST["id_persona"]);
}