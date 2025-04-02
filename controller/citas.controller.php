<?php
// consultas.controller.php
class ControllerCitas {
    public static function ctrSetCita($datos) {
        $resultado = ModelCitas::mdlSetCita($datos);
        return $resultado;
    }
}
?>