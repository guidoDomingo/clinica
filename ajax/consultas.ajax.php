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
