<?php
/**
 * Script para limpiar logs antiguos de WhatsApp
 * Este script se puede configurar como tarea programada (cron job) para ejecutarse mensualmente
 */

// Configuración
$logDir = __DIR__ . '/logs';
$pdfTempDir = __DIR__ . '/pdf_temp';
$pdfReservasDir = __DIR__ . '/pdf_reservas';
$maxAgeInDays = 90; // Eliminar logs más antiguos de 90 días
$maxTempPdfAgeInDays = 7; // Eliminar PDFs temporales más antiguos de 7 días

// Función para limpiar archivos antiguos
function cleanOldFiles($directory, $pattern, $maxAgeInDays) {
    // Verificar si el directorio existe
    if (!is_dir($directory)) {
        echo "Directorio no encontrado: $directory\n";
        return false;
    }
    
    // Calcular la fecha límite
    $cutoffTime = time() - ($maxAgeInDays * 86400); // 86400 segundos = 1 día
    
    // Encontrar archivos que coincidan con el patrón
    $files = glob("$directory/$pattern");
    
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            // Verificar la fecha de modificación del archivo
            $fileTime = filemtime($file);
            if ($fileTime < $cutoffTime) {
                // Intentar eliminar el archivo
                if (unlink($file)) {
                    echo "Eliminado: " . basename($file) . "\n";
                    $count++;
                } else {
                    echo "Error al eliminar: " . basename($file) . "\n";
                }
            }
        }
    }
    
    echo "Total de archivos eliminados en $directory: $count\n";
    return true;
}

// Ejecutar limpieza
echo "=== Iniciando limpieza de archivos antiguos ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

echo "--- Limpieza de logs de WhatsApp ---\n";
cleanOldFiles($logDir, "whatsapp_envios_*.log", $maxAgeInDays);

echo "\n--- Limpieza de PDFs temporales ---\n";
cleanOldFiles($pdfTempDir, "*.pdf", $maxTempPdfAgeInDays);

echo "\n--- Limpieza de PDFs de reservas ---\n";
cleanOldFiles($pdfReservasDir, "reserva_*.pdf", $maxTempPdfAgeInDays);

echo "\n=== Limpieza finalizada ===\n";

// Si se ejecuta como tarea programada, el resultado se puede guardar en un archivo de log
// Ejemplo de configuración cron para ejecutarse el primer día de cada mes a las 3:00 AM:
// 0 3 1 * * php /ruta/a/clinica/limpiar_logs.php >> /ruta/a/clinica/logs/cleanup_log.txt 2>&1
