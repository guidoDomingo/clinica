<?php
require_once 'config/config.php';
require_once 'model/conexion.php';
require_once 'model/consultas.model.php';

$id_consulta = 19;

$consultaModel = new ModelConsulta();
$datos_consulta = $consultaModel->obtenerConsulta($id_consulta);

echo "<pre>";
echo "Datos de la consulta #$id_consulta:\n";
print_r($datos_consulta);

if ($datos_consulta) {
    $id_persona = $datos_consulta['id_persona'];
    $persona = $consultaModel->obtenerDatosPersona($id_persona);
    
    echo "\nDatos de la persona #$id_persona:\n";
    print_r($persona);
}

echo "</pre>";
?>
