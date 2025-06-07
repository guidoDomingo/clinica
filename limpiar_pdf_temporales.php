<?php
/**
 * Script para eliminar archivos PDF temporales antiguos
 * Este script puede ser ejecutado mediante un cron job diario
 */

$directorio = __DIR__ . '/pdf_reservas';
$tiempoMaximo = 24 * 60 * 60; // 24 horas en segundos

// Verificar que el directorio existe
if (!is_dir($directorio)) {
    exit("El directorio $directorio no existe.");
}

// Obtener todos los archivos PDF
$archivos = glob($directorio . '/*.pdf');

// Contador de archivos eliminados
$eliminados = 0;

// Revisar cada archivo
foreach ($archivos as $archivo) {
    // Obtener el tiempo de modificación del archivo
    $tiempoModificacion = filemtime($archivo);
    
    // Calcular la antigüedad del archivo
    $antiguedad = time() - $tiempoModificacion;
    
    // Si el archivo es más antiguo que el tiempo máximo, eliminarlo
    if ($antiguedad > $tiempoMaximo) {
        if (unlink($archivo)) {
            $eliminados++;
        }
    }
}

echo "Limpieza completada. Se eliminaron $eliminados archivos PDF antiguos.\n";
?>
