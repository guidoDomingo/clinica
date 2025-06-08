<?php
/**
 * Script de limpieza de archivos innecesarios relacionados con WhatsApp
 * Este script elimina archivos redundantes de la implementación de WhatsApp
 * que no son necesarios para el funcionamiento del sistema
 */

// Iniciar registro de la operación
$log_file = __DIR__ . '/logs/limpieza_whatsapp_' . date('Y-m-d_H-i-s') . '.log';
$total_eliminados = 0;

// Asegurarse de que el directorio de logs existe
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Función para registrar eventos en el log
function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    echo "$message\n";
}

log_message("Iniciando limpieza de archivos innecesarios de WhatsApp...");

// Lista de archivos a eliminar
$archivos_a_eliminar = [
    // Archivos duplicados o innecesarios relacionados con WhatsApp y PDF
    'ajax/enviar_media.php',            // Redundante con enviar_pdf_whatsapp.php
    'ajax/pdf_uploader.php',            // Script de prueba ya no utilizado
    'ajax/upload_pdf.php',              // Script de prueba ya no utilizado
    'ajax/send_pdf_test.php',           // Script de prueba ya no utilizado
    'test_pdf_whatsapp.php',            // Script de prueba
    'test_pdf_url_especifica.html',     // Página de prueba HTML
    'test_pdf_simple.html',             // Página de prueba HTML
    'test_pdf_reserva.php',             // Script de prueba
    'test_pdf_local.php',               // Script de prueba
    'test_pdf_urls.php',                // Script de prueba
    'test_whatsapp.php',                // Script de prueba
    'test_whatsapp_http_client.php',    // Script de prueba
    'test_api_whatsapp.php',            // Script de prueba
    'test_api_guzzle.php',              // Script de prueba
    'test_api_direct.php',              // Script de prueba
    'test_simple.html',                 // Archivo HTML de prueba
    'api/test_whatsapp.php',            // Script de prueba en carpeta API
    'view/modules/test_whatsapp.php',   // Módulo de prueba para vista
    'whatsapp_diagnostico.php',         // Archivo de diagnóstico no necesario
    'whatsapp_config.php',              // Archivo de configuración redundante
    'whatsapp_api_test.php'             // Script de prueba de API
];

// Eliminar archivos
foreach ($archivos_a_eliminar as $archivo) {
    $ruta_completa = __DIR__ . '/' . $archivo;
    if (file_exists($ruta_completa)) {
        if (unlink($ruta_completa)) {
            log_message("Eliminado: $archivo");
            $total_eliminados++;
        } else {
            log_message("ERROR: No se pudo eliminar $archivo");
        }
    } else {
        log_message("Archivo no encontrado: $archivo");
    }
}

// Limpiar directorio temporal PDF WhatsApp
$pdf_whatsapp_dir = __DIR__ . '/uploads/pdf_whatsapp';
if (is_dir($pdf_whatsapp_dir)) {
    log_message("Limpiando directorio: $pdf_whatsapp_dir");
    $archivos = glob($pdf_whatsapp_dir . '/*');
    foreach ($archivos as $archivo) {
        if (is_file($archivo)) {
            if (unlink($archivo)) {
                log_message("Eliminado archivo temporal: " . basename($archivo));
                $total_eliminados++;
            }
        }
    }
}

// Verificar si el directorio pdf_whatsapp está vacío y eliminarlo si es así
if (is_dir($pdf_whatsapp_dir) && count(glob($pdf_whatsapp_dir . '/*')) === 0) {
    if (rmdir($pdf_whatsapp_dir)) {
        log_message("Eliminado directorio vacío: uploads/pdf_whatsapp");
    } else {
        log_message("No se pudo eliminar el directorio: uploads/pdf_whatsapp");
    }
}

// Resumen final
log_message("¡Limpieza finalizada!");
log_message("Total de archivos eliminados: $total_eliminados");
log_message("Se ha creado un registro detallado en: $log_file");

echo "\n==========================================\n";
echo "   LIMPIEZA DE WHATSAPP COMPLETADA         \n";
echo "   Se eliminaron $total_eliminados archivos\n";
echo "==========================================\n";
?>
