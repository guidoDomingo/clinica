<?php
// require_once "../consultas.model.php";

class ControllerConsulta {
    public static function ctrSetConsulta($datos) {
       
        $resultado = ModelConsulta::mdlSetConsulta($datos);
        return $resultado;
    }

    public static function ctrEliminarConsulta($id) {
        return ModelConsulta::mdlEliminarConsulta($id);
    }
}
?>