<?php
require_once 'model/conexion.php';
\ = Conexion::conectar();
\ = \->prepare('SELECT column_name, data_type FROM information_schema.columns WHERE table_name = \'agendas_detalle\' ORDER BY ordinal_position');
\->execute();
\ = \->fetchAll(PDO::FETCH_ASSOC);
echo '<pre>';
print_r(\);
echo '</pre>';
?>
