<?php
// require_once "../controller/archivos.controller.php";
require_once "../model/archivos.model.php";

class ArchivosAjax {
    public function ajaxGetArchivosMega($datos) {
        // Obtener los resultados de la búsqueda
        $response = ModelArchivos::mdlGetArchivosMega($datos);
        echo json_encode($response);
    }
    
    /**
     * Obtiene los archivos asociados a una consulta específica
     * @param int $idConsulta ID de la consulta
     */
    public function ajaxGetArchivosPorConsulta($idConsulta) {
        $response = ModelArchivos::mdlGetArchivosPorConsulta($idConsulta);
        echo json_encode([
            'status' => !empty($response) ? 'success' : 'warning',
            'data' => $response,
            'message' => empty($response) ? 'No hay archivos asociados a esta consulta.' : ''
        ]);
    }
}

// Obtener cuota de archivos
if (isset($_POST["id_persona"]) && isset($_POST["operacion"]) && $_POST["operacion"] == "mega") {
    $cuota = new ArchivosAjax();
    $datos = array();
    foreach ($_POST as $key => $value) {
        $datos[$key] = $value;
    }     
    $cuota->ajaxGetArchivosMega($datos);
}

// Obtener archivos por ID de consulta
if (isset($_POST["id_consulta"]) && isset($_POST["operacion"]) && $_POST["operacion"] == "archivosPorConsulta") {
    $archivos = new ArchivosAjax();
    $archivos->ajaxGetArchivosPorConsulta($_POST["id_consulta"]);
}