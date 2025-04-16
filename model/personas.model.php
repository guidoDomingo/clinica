<?php
require_once "conexion.php";
class ModelPersonas {
    public static function mdlGetPersonaParam($datos) {
        $where = " WHERE 1=1 "; // Iniciar con una condición siempre verdadera para facilitar la concatenación
        $params = [];

        if (!empty($datos['documento'])) {
            $where .= " AND document_number = :documento";
            $params[':documento'] = $datos['documento'];
        }

        if (!empty($datos['nro_ficha'])) {
            $where .= " AND record_number = :nro_ficha";
            $params[':nro_ficha'] = $datos['nro_ficha'];
        }

        if (!empty($datos['nombres'])) {
            $where .= " AND first_name ILIKE :nombres";
            $params[':nombres'] = '%' . $datos['nombres'] . '%';
        }

        $sql = "SELECT person_id as id_persona, document_number as documento, record_number as nro_ficha, first_name as nombres, last_name as apellidos, created_at as fecha_registro 
                FROM public.rh_person " . $where;

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}