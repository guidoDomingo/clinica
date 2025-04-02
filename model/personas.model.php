<?php
require_once "conexion.php";
class ModelPersonas {
    public static function mdlGetPersonaParam($datos) {
        $where = " WHERE 1=1 "; // Iniciar con una condición siempre verdadera para facilitar la concatenación
        $params = [];

        if (!empty($datos['documento'])) {
            $where .= " AND documento = :documento";
            $params[':documento'] = $datos['documento'];
        }

        if (!empty($datos['nro_ficha'])) {
            $where .= " AND nro_ficha = :nro_ficha";
            $params[':nro_ficha'] = $datos['nro_ficha'];
        }

        if (!empty($datos['nombres'])) {
            $where .= " AND nombres ILIKE :nombres";
            $params[':nombres'] = '%' . $datos['nombres'] . '%';
        }

        $sql = "SELECT id_persona, documento, nro_ficha, nombres, apellidos, fecha_registro 
                FROM public.personas " . $where;

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
