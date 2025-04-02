<?php
// require_once "../controller/archivos.controller.php";
require_once "../model/archivos.model.php";

class ArchivosAjax {
    public function ajaxGetArchivosMega($datos) {
        // Obtener los resultados de la búsqueda
        $response = ModelArchivos::mdlGetArchivosMega($datos);
        echo json_encode($response);
    }
    
}
if (isset($_POST["id_persona"]) && isset($_POST["operacion"]) && $_POST["operacion"] == "mega") {
    $cuota = new ArchivosAjax();
    $datos = array();
    foreach ($_POST as $key => $value) {
        $datos[$key] = $value;
    }     
    $cuota->ajaxGetArchivosMega($datos);

}
