<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once "../controller/user.controller.php";
require_once "../model/conexion.php";

class AjaxUsuarios {
    public function getUsuarios() {
        $userController = new ControllerUser();
        $usuarios = $userController->ctrObtenerUsuarios();
        
        if ($usuarios) {
            echo json_encode([
                'status' => 'success',
                'data' => $usuarios
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se pudieron obtener los usuarios'
            ]);
        }
    }
}

// Procesar la solicitud AJAX
if (isset($_POST['operacion'])) {
    $ajax = new AjaxUsuarios();
    
    switch ($_POST['operacion']) {
        case 'getUsuarios':
            $ajax->getUsuarios();
            break;
    }
}