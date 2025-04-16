<?php
/**
 * Script para cargar datos de prueba en las tablas de consultas médicas
 * Este script ejecuta el archivo SQL con datos de prueba para las tablas:
 * - rh_person (pacientes)
 * - consultas
 * - archivos
 * - archivos_consulta
 */

require_once __DIR__ . '/../model/conexion.php';

try {
    $pdo = Conexion::conectar();
    
    echo "Iniciando carga de datos de prueba para consultas médicas...\n";
    
    // Leer y ejecutar el archivo SQL con datos de prueba
    $sql = file_get_contents(__DIR__ . '/datos_prueba_consultas.sql');
    $pdo->exec($sql);
    
    echo "Datos de prueba cargados correctamente.\n";
    echo "Se han insertado:\n";
    echo "- 5 pacientes en la tabla rh_person\n";
    echo "- 6 consultas médicas en la tabla consultas\n";
    echo "- 9 archivos en la tabla archivos\n";
    echo "- 10 relaciones en la tabla archivos_consulta\n";
    
} catch (\Exception $e) {
    echo "Error al cargar los datos de prueba: " . $e->getMessage() . "\n";
}