<?php
/**
 * Script de limpieza final para preparación de producción
 * 
 * Este script elimina todos los archivos y carpetas innecesarios 
 * antes de subir el proyecto a producción.
 */

// Inicializar contador y registro
$log_file = __DIR__ . '/logs/limpieza_produccion_' . date('Y-m-d_H-i-s') . '.log';
$total_eliminados = 0;
$total_directorios = 0;

// Asegurar que el directorio de logs existe
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

// Función para eliminar un directorio recursivamente
function eliminar_directorio($dir) {
    global $total_eliminados, $total_directorios;
    
    if (!file_exists($dir)) {
        return;
    }
    
    if (!is_dir($dir)) {
        if (unlink($dir)) {
            log_message("Eliminado archivo: $dir");
            $total_eliminados++;
        }
        return;
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        
        if (is_dir($path)) {
            eliminar_directorio($path);
        } else {
            if (unlink($path)) {
                log_message("Eliminado archivo: $path");
                $total_eliminados++;
            }
        }
    }
    
    if (rmdir($dir)) {
        log_message("Eliminado directorio: $dir");
        $total_directorios++;
    }
}

log_message("Iniciando limpieza para preparación de producción...");

// 1. ARCHIVOS DE PRUEBA Y DIAGNÓSTICO
$archivos_de_prueba = [
    // Archivos bat y de configuración de desarrollo
    'activar_extensiones_php.bat',
    'configurar_mantenimiento_automatico.bat',
    'fix_database.bat',
    'test_syntax.bat',
    
    // Archivos de diagnóstico y test
    'diagnostico_envio_pdf_basico.php',
    'diagnostico_envio_pdf_completo.php',
    'diagnostico_reserva.php',
    'diagnostico_whatsapp_pdf.php',
    
    // Scripts de verificación y corrección
    'check_http_client.php',
    'check_table.php',
    'check_table2.php',
    'fix_ajax_undefined_var.php',
    'fix_alerts.php',
    'fix_alerts_comprehensive.php',
    'fix_alerts_final.php',
    'fix_database.php',
    'fix_database_tables.php',
    'fix_notifications.php',
    'fix_notifications_path.php',
    'quick_fix.php',
    'syntax_check.php',
    
    // Scripts de test
    'test_actualizacion_slots.php',
    'test_ajax_error.php',
    'test_api_connection.php',
    'test_api_direct.php',
    'test_api_guzzle.php',
    'test_api_whatsapp.php',
    'test_horarios_pasados.php',
    'test_icd11_service.php',
    'test_pdf_local.php',
    'test_pdf_reserva.php',
    'test_pdf_simple.html',
    'test_pdf_urls.php',
    'test_pdf_url_especifica.html',
    'test_simple.html',
    'test_slots.php',
    'test_slots_fechas_futuras.php',
    'test_slots_future.php',
    'test_slots_futuro.php',
    'test_slots_realdate.php',
    'test_slots_reserva_ui.php',
    'test_solucion_slots.php',
    'test_subir_y_enviar_pdf.php',
    'test_whatsapp.php',
    'test_whatsapp_http_client.php',
    'icd11_iframe_test.html',
    'icd11_integration_test.html',
    'icd11_service_test.php',
    
    // Scripts de limpieza (ya no serán necesarios en producción)
    'eliminar_archivos_prueba.php',
    'limpiar_logs.php',
    'limpiar_pdf_temporales.php',
    'limpiar_proyecto.php',
    'limpiar_proyecto_avanzado.php',
    'limpiar_proyecto_final.php',
    'limpiar_whatsapp_innecesario.php',
    
    // Documentación de desarrollo
    'diagnostico_whatsapp_pdf_soluciones.md',
    'DOCUMENTACION_ENVIO_PDF.md',
    'DOCUMENTACION_WHATSAPP_PDF.md',
    'SLOTS_ACTUALIZACION_SOLUTION.md',
    'SLOTS_DATE_SOLUTION.md',
    'SLOTS_SOLUTION_README.md',
    'SLOTS_SOLUTION_README_NEW.md',
    'SOLUCION_HORARIOS_PASADOS.md',
    
    // Archivos de filtro y ajax de prueba
    'ajax_filtrar_reservas.php'
];

// Eliminar archivos de prueba
foreach ($archivos_de_prueba as $archivo) {
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

// 2. DIRECTORIOS DE DESARROLLO Y PRUEBAS
$directorios_innecesarios = [
    'AdminLTE-temp',    // Plantillas temporales
    'sys_prompt',       // Información del sistema de desarrollo
    'sys_sql',          // SQL de desarrollo
    'test',             // Tests (si existe)
    'tests',            // Tests (si existe)
    'node_modules',     // Dependencias de Node.js (no necesarias en producción)
    'pdfs_temp',        // Directorio temporal redundante
    'temp'              // Archivos temporales
];

foreach ($directorios_innecesarios as $directorio) {
    $ruta_completa = __DIR__ . '/' . $directorio;
    if (is_dir($ruta_completa)) {
        log_message("Eliminando directorio: $directorio");
        eliminar_directorio($ruta_completa);
    }
}

// 3. LIMPIAR ARCHIVOS DE PRUEBA EN SUBDIRECTORIOS
// Limpiar carpeta ajax
$ajax_files_to_remove = glob(__DIR__ . '/ajax/test_*.php');
foreach ($ajax_files_to_remove as $file) {
    if (unlink($file)) {
        log_message("Eliminado: " . basename($file));
        $total_eliminados++;
    }
}

// Limpiar carpeta api
$api_files_to_remove = glob(__DIR__ . '/api/test_*.php');
foreach ($api_files_to_remove as $file) {
    if (unlink($file)) {
        log_message("Eliminado: api/" . basename($file));
        $total_eliminados++;
    }
}

// Limpiar archivos temporales de pdf_temp
$pdf_temp_files = glob(__DIR__ . '/pdf_temp/*');
foreach ($pdf_temp_files as $file) {
    if (is_file($file)) {
        if (unlink($file)) {
            log_message("Eliminado archivo temporal: pdf_temp/" . basename($file));
            $total_eliminados++;
        }
    }
}

// Asegurar que se mantengan los directorios necesarios vacíos
$directorios_a_mantener = [
    'pdf_temp',
    'uploads'
];

foreach ($directorios_a_mantener as $directorio) {
    $ruta_completa = __DIR__ . '/' . $directorio;
    if (!is_dir($ruta_completa)) {
        mkdir($ruta_completa, 0755, true);
        log_message("Creado directorio necesario: $directorio");
    }
    
    // Crear archivo .htaccess para proteger el directorio
    $htaccess = $ruta_completa . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Options -Indexes\nDeny from all");
        log_message("Creado archivo .htaccess en: $directorio");
    }
    
    // Crear archivo index.html vacío para evitar listado de directorios
    $index_html = $ruta_completa . '/index.html';
    if (!file_exists($index_html)) {
        file_put_contents($index_html, "<html><head><title>Acceso denegado</title></head><body><h1>Acceso denegado</h1></body></html>");
        log_message("Creado archivo index.html en: $directorio");
    }
}

// 4. Eliminar archivos de desarrollo y logs antiguos en la carpeta logs
$logs_antiguos = [];
$dir = __DIR__ . '/logs';
if (is_dir($dir)) {
    foreach (new DirectoryIterator($dir) as $file) {
        if ($file->isFile() && !$file->isDot()) {
            $filename = $file->getFilename();
            
            // Mantener los logs de aplicación actuales
            if ($filename === 'application.log' || $filename === 'database.log' || $filename === 'README.md') {
                continue;
            }
            
            // Verificar si es un log de limpieza o logs antiguos
            if (strpos($filename, 'limpieza_') !== false || 
                strpos($filename, 'test_') !== false ||
                $file->getMTime() < strtotime('-30 days')) {
                
                $logs_antiguos[] = $file->getPathname();
            }
        }
    }
}

// Eliminar logs antiguos
foreach ($logs_antiguos as $log) {
    if (unlink($log)) {
        log_message("Eliminado log antiguo: " . basename($log));
        $total_eliminados++;
    }
}

// 5. Optimizar la estructura de .htaccess
$htaccess_path = __DIR__ . '/.htaccess';
if (file_exists($htaccess_path)) {
    $htaccess_content = file_get_contents($htaccess_path);
    
    // Agregar reglas de seguridad y optimización para producción
    $production_rules = "
# Reglas de seguridad para producción
# Deshabilitar listado de directorios
Options -Indexes

# Proteger archivos sensibles
<FilesMatch \"^(\.htaccess|\.htpasswd|\.git|\.env|config\.php|composer\.(json|lock))$\">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Optimización de caché para archivos estáticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg \"access plus 1 year\"
    ExpiresByType image/jpeg \"access plus 1 year\"
    ExpiresByType image/gif \"access plus 1 year\"
    ExpiresByType image/png \"access plus 1 year\"
    ExpiresByType text/css \"access plus 1 month\"
    ExpiresByType text/javascript \"access plus 1 month\"
    ExpiresByType application/javascript \"access plus 1 month\"
</IfModule>
";
    
    if (strpos($htaccess_content, 'Reglas de seguridad para producción') === false) {
        file_put_contents($htaccess_path, $htaccess_content . $production_rules);
        log_message("Optimizado archivo .htaccess para producción");
    }
}

// 6. Eliminar este script al final
$this_script = __FILE__;
// Se comentó esta línea para evitar que el script se elimine a sí mismo durante la ejecución
// register_shutdown_function(function() use ($this_script) { @unlink($this_script); });

// Resumen final
log_message("¡Limpieza para producción finalizada!");
log_message("Total de archivos eliminados: $total_eliminados");
log_message("Total de directorios eliminados: $total_directorios");
log_message("Se ha creado un registro detallado en: $log_file");

echo "\n===================================================\n";
echo "   LIMPIEZA PARA PRODUCCIÓN COMPLETADA               \n";
echo "   Se eliminaron $total_eliminados archivos          \n";
echo "   Se eliminaron $total_directorios directorios      \n";
echo "===================================================\n";
echo "\nTu proyecto ahora está listo para producción.\n";
echo "Recuerda revisar la configuración de accesos a bases de datos y rutas absolutas antes de desplegar.\n";
