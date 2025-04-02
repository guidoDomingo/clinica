<?php
// consultas.model.php
class ModelCitas {
    public static function mdlSetCita($datos) {
        var_dump($datos);
        // Aquí debes implementar la lógica para insertar los datos en la base de datos
        // Por ejemplo:
        // $sql = "INSERT INTO consultas (campo1, campo2, ...) VALUES (:valor1, :valor2, ...)";
        // $stmt = $pdo->prepare($sql);
        // $stmt->execute($datos);
        // return "ok"; // o un mensaje de error si falla

        // Ejemplo simplificado:
        return "ok"; // Simula una inserción exitosa
    }
}
