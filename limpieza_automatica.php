<?php
/**
 * Script de limpieza automática de archivos temporales
 * 
 * Este script puede ser configurado como una tarea programada (cron job)
 * para ejecutarse de forma periódica y mantener el sistema limpio
 * 
 * Uso recomendado: Programar ejecución diaria
 */

// Configuración
$dias_eliminar = 1; // Eliminar archivos con más de X días
$timestamp_limite = time() - ($dias_eliminar * 86400);
$directorios = [
    'pdf_temp',
    'ajax/temp/pdfs_web',
    'uploads/temp'
];

// Log de actividad
$log_file = 'logs/limpieza_automatica.log';
$log = "\n--- Limpieza automática: " . date('Y-m-d H:i:s') . " ---\n";
$total_eliminados = 0;

// Procesar cada directorio
foreach ($directorios as $directorio) {
    if (!is_dir($directorio)) {
        $log .= "Directorio no encontrado: {$directorio}\n";
        continue;
    }
    
    $log .= "Procesando: {$directorio}\n";
    $dir_eliminados = 0;
    
    // Obtener archivos recursivamente
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directorio, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $archivo) {
        if ($archivo->isFile()) {
            $ruta = $archivo->getPathname();
            
            // Omitir archivos especiales
            $nombre = basename($ruta);
            if ($nombre === '.htaccess' || $nombre === 'index.html' || $nombre === '.gitkeep') {
                continue;
            }
            
            // Verificar tiempo
            if ($archivo->getMTime() < $timestamp_limite) {
                if (unlink($ruta)) {
                    $log .= "  - Eliminado: {$ruta}\n";
                    $dir_eliminados++;
                    $total_eliminados++;
                } else {
                    $log .= "  - ERROR al eliminar: {$ruta}\n";
                }
            }
        }
    }
    
    $log .= "  Total en {$directorio}: {$dir_eliminados} archivos eliminados\n";
}

// Rotar logs antiguos
$log .= "\nRotando logs antiguos...\n";
$log_dir = 'logs';
$dias_rotar_logs = 30; // Rotar logs con más de 30 días
$timestamp_logs = time() - ($dias_rotar_logs * 86400);
$logs_rotados = 0;

// Crear directorio de archivo si no existe
$archive_dir = $log_dir . '/archive';
if (!is_dir($archive_dir) && !mkdir($archive_dir, 0755, true)) {
    $log .= "ERROR: No se pudo crear el directorio de archivo: {$archive_dir}\n";
} else {
    $log_files = glob($log_dir . '/*.log');
    foreach ($log_files as $log_file_path) {
        // Omitir el archivo de log actual
        if ($log_file_path === $log_file) continue;
        
        if (is_file($log_file_path) && filemtime($log_file_path) < $timestamp_logs) {
            $new_name = $archive_dir . '/' . basename($log_file_path) . '.' . date('Y-m-d', filemtime($log_file_path));
            if (rename($log_file_path, $new_name)) {
                $log .= "  - Log rotado: " . basename($log_file_path) . "\n";
                $logs_rotados++;
            } else {
                $log .= "  - ERROR al rotar log: " . basename($log_file_path) . "\n";
            }
        }
    }
    $log .= "  Total logs rotados: {$logs_rotados}\n";
}

// Resumen final
$log .= "\nResumen de limpieza:\n";
$log .= "- Fecha de ejecución: " . date('Y-m-d H:i:s') . "\n";
$log .= "- Archivos eliminados: {$total_eliminados}\n";
$log .= "- Logs rotados: {$logs_rotados}\n";

// Guardar log
file_put_contents($log_file, $log, FILE_APPEND);

echo $log;
echo "\nLimpieza completada. Ver detalles en {$log_file}\n";
