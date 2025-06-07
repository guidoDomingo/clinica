<?php
/**
 * Script de mantenimiento del proyecto
 * Este script está diseñado para ejecutarse periódicamente (una tarea programada)
 * Elimina archivos temporales, logs antiguos y PDFs que ya no son necesarios
 */

$log_file = __DIR__ . '/logs/mantenimiento_' . date('Y-m-d_H-i-s') . '.log';

// Asegurarse de que el directorio de logs existe
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Función para registrar eventos en el log
function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

log_message("Iniciando mantenimiento programado del proyecto...");
$total_eliminados = 0;

// 1. Limpiar archivos PDF temporales (más de 24 horas)
$directorios_temporales = [
    __DIR__ . '/pdf_temp',
    __DIR__ . '/pdfs_temp',
    __DIR__ . '/temp'
];

$tiempo_limite_temp = time() - (24 * 60 * 60); // 24 horas

foreach ($directorios_temporales as $directorio) {
    if (is_dir($directorio)) {
        log_message("Limpiando directorio temporal: $directorio");
        $archivos = glob($directorio . '/*');
        foreach ($archivos as $archivo) {
            if (is_file($archivo) && filemtime($archivo) < $tiempo_limite_temp) {
                if (unlink($archivo)) {
                    log_message("Eliminado archivo temporal: " . basename($archivo));
                    $total_eliminados++;
                }
            }
        }
    }
}

// 2. Limpiar PDFs de reservas antiguos (más de 30 días)
$pdf_reservas_dir = __DIR__ . '/pdf_reservas';
if (is_dir($pdf_reservas_dir)) {
    log_message("Limpiando PDFs de reservas antiguos (>30 días)...");
    $archivos = glob($pdf_reservas_dir . '/*.pdf');
    $tiempo_limite = time() - (30 * 24 * 60 * 60); // 30 días
    $pdf_antiguos_eliminados = 0;
    
    foreach ($archivos as $archivo) {
        if (is_file($archivo) && filemtime($archivo) < $tiempo_limite) {
            if (unlink($archivo)) {
                log_message("Eliminado PDF antiguo: " . basename($archivo));
                $pdf_antiguos_eliminados++;
                $total_eliminados++;
            }
        }
    }
    
    log_message("Se eliminaron $pdf_antiguos_eliminados PDFs antiguos.");
}

// 3. Limpiar logs antiguos (más de 60 días)
$logs_dir = __DIR__ . '/logs';
if (is_dir($logs_dir)) {
    log_message("Limpiando logs antiguos (>60 días)...");
    $archivos = glob($logs_dir . '/*.log');
    $tiempo_limite = time() - (60 * 24 * 60 * 60); // 60 días
    $logs_eliminados = 0;
    
    foreach ($archivos as $archivo) {
        if (is_file($archivo) && filemtime($archivo) < $tiempo_limite) {
            if (unlink($archivo)) {
                log_message("Eliminado log antiguo: " . basename($archivo));
                $logs_eliminados++;
                $total_eliminados++;
            }
        }
    }
    
    log_message("Se eliminaron $logs_eliminados logs antiguos.");
}

// 4. Verificar y limpiar la carpeta de uploads (archivos temporales)
$uploads_dir = __DIR__ . '/uploads/temp';
if (is_dir($uploads_dir)) {
    log_message("Limpiando archivos temporales en uploads/temp (>7 días)...");
    $archivos = glob($uploads_dir . '/*');
    $tiempo_limite = time() - (7 * 24 * 60 * 60); // 7 días
    $uploads_eliminados = 0;
    
    foreach ($archivos as $archivo) {
        if (is_file($archivo) && filemtime($archivo) < $tiempo_limite) {
            if (unlink($archivo)) {
                log_message("Eliminado archivo temporal de uploads: " . basename($archivo));
                $uploads_eliminados++;
                $total_eliminados++;
            }
        }
    }
    
    log_message("Se eliminaron $uploads_eliminados archivos temporales de uploads.");
}

// 5. Limpiar archivos de caché antiguos
$cache_dir = __DIR__ . '/cache';
if (is_dir($cache_dir)) {
    log_message("Limpiando archivos de caché antiguos (>7 días)...");
    $archivos = glob($cache_dir . '/*');
    $tiempo_limite = time() - (7 * 24 * 60 * 60); // 7 días
    $cache_eliminados = 0;
    
    foreach ($archivos as $archivo) {
        if (is_file($archivo) && filemtime($archivo) < $tiempo_limite) {
            if (unlink($archivo)) {
                log_message("Eliminado archivo de caché: " . basename($archivo));
                $cache_eliminados++;
                $total_eliminados++;
            }
        }
    }
    
    log_message("Se eliminaron $cache_eliminados archivos de caché.");
}

// Resumen final
log_message("Mantenimiento finalizado");
log_message("Total de archivos eliminados: $total_eliminados");

// Si se ejecuta desde la línea de comandos, mostrar resumen
if (php_sapi_name() === 'cli') {
    echo "Mantenimiento completado. Se eliminaron $total_eliminados archivos.\n";
    echo "Log guardado en: $log_file\n";
}

?>
