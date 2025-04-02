<?php
// require_once "../controller/persona.controller.php";
require_once "../model/personas.model.php";

class TablePersonas {
    public function ajaxBuscarPersonaParam($datos) {
        // Obtener los resultados de la búsqueda
        $response = ModelPersonas::mdlGetPersonaParam($datos);
        // var_dump($response);
        // return;
        // Verificar si hay resultados
        if (empty($response)) {
            // Si no hay resultados, retornar un JSON con un mensaje de aviso
            echo json_encode([
                'status' => 'warning',
                'message' => 'No se encontraron resultados para la búsqueda.'
            ]);
        } else {
            // Si hay resultados, retornar los datos en formato JSON
            echo json_encode([
                'status' => 'success',
                'data' => $response
            ]);
        }
    }
    // public function ajaxBuscarPersonaParam($datos) {
    //     $response = ModelPersonas::mdlGetPersonaParam($datos);
    //     echo json_encode($response);
    // }
    

    public function ajaxEliminarConsulta($id) {
        $response = ControllerConsulta::ctrEliminarConsulta($id);
        echo json_encode($response);
    }
}



// Procesar la consulta de una persona
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['operacion']) && $_POST['operacion'] === 'buscarparam') {
    // Validar y sanitizar los datos de entrada manualmente
    $datos = [
        'documento' => $_POST['documento'] ?? '',
        'nro_ficha' => $_POST['nro_ficha'] ?? '',
        'nombres' => $_POST['nombres'] ?? ''
    ];

    // Sanitizar los datos (eliminar espacios en blanco y caracteres no deseados)
    $datos = array_map('trim', $datos); // Eliminar espacios en blanco
    $datos = array_map('htmlspecialchars', $datos); // Convertir caracteres especiales en entidades HTML

    $personaLike = new TablePersonas();
    $personaLike->ajaxBuscarPersonaParam($datos);
}
// Procesar la consulta de una persona
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['operacion']) && $_POST['operacion'] === 'insertPersona') {
    // Validar y sanitizar los datos de entrada manualmente
     $datos = array();
     foreach ($_POST as $key => $value) {
        $datos[$key] =$value;
     }
     var_dump($datos );
}