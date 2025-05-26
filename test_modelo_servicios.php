<?php
// Script para probar la conexión a la base de datos utilizando los modelos existentes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir los archivos necesarios
require_once "model/conexion.php";
require_once "model/servicios.model.php";

echo "<h1>Prueba del Modelo de Servicios</h1>";

// Intentar una conexión simple primero
try {
    $conn = Conexion::conectar();
    if ($conn) {
        echo "<p style='color:green'>Conexión a la base de datos exitosa!</p>";
        
        // Obtener la versión de PostgreSQL
        $stmt = $conn->query("SELECT version()");
        $version = $stmt->fetchColumn();
        echo "<p>Versión de PostgreSQL: $version</p>";
    } else {
        echo "<p style='color:red'>Error de conexión: No se pudo establecer la conexión.</p>";
        echo "<p>Revise los logs en: c:/laragon/www/clinica/logs/database.log</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// Probar la función mdlObtenerMedicosDisponiblesPorFecha
echo "<h2>Prueba de mdlObtenerMedicosDisponiblesPorFecha</h2>";

try {
    $fecha = date('Y-m-d'); // Fecha actual
    echo "<p>Buscando médicos para la fecha: $fecha</p>";
    
    $medicos = ModeloServicios::mdlObtenerMedicosDisponiblesPorFecha($fecha);
    
    echo "<h3>Resultado:</h3>";
    echo "<pre>";
    print_r($medicos);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error al buscar médicos: " . $e->getMessage() . "</p>";
}
?>
