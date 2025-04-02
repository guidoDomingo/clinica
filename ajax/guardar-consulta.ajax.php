<?php
require_once "../controller/consultas.controller.php";
require_once "../model/consultas.model.php";

class TableConsultas {
    public function ajaxInsertarConsulta($datos) {
        $response = ControllerConsulta::ctrSetConsulta($datos);
        echo $response;
    }

    public function ajaxEliminarConsulta($id) {
        $response = ControllerConsulta::ctrEliminarConsulta($id);
        echo json_encode($response);
    }
}

// Procesar la inserción de una nueva consulta
if (isset($_POST["motivoscomunes"]) && isset($_POST["formatoConsulta"])) {
    $insertar = new TableConsultas();
    $datos = array();

    // Recopilar todos los datos del formulario
    foreach ($_POST as $key => $value) {
        $datos[$key] = $value;
    }

    // Manejar archivos adjuntos (si existen)
    if (!empty($_FILES)) {
        $archivos = $_FILES; // Aquí puedes procesar los archivos subidos
        // Ejemplo: Guardar archivos en una carpeta
        $rutaDestino = '../view/uploads/'; // Cambia esto por la ruta donde quieras guardar los archivos
        foreach ($archivos as $archivo) {
            $nombreArchivo = basename($archivo['name']);
            $rutaCompleta = $rutaDestino . $nombreArchivo;
            if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                // Archivo guardado correctamente
                $datos['archivos'][] = $rutaCompleta; // Guardar la ruta en los datos
            } else {
                // Error al guardar el archivo
            }
        }
    }

    // Insertar los datos de la consulta
    $insertar->ajaxInsertarConsulta($datos);
}

// Procesar la eliminación de una consulta
if (isset($_POST["idConsulta"]) && isset($_POST["operacion"])) {
    $crud = new TableConsultas();
    if ($_POST["operacion"] == "eliminarconsulta") {
        $crud->ajaxEliminarConsulta($_POST["idConsulta"]);
    }
}
?>