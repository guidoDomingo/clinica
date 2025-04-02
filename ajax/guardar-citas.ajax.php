<?php
require_once "../controller/citas.controller.php";
require_once "../model/citas.model.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $response = ControllerCitas::ctrSetCita($datos);
    echo $response;
}
