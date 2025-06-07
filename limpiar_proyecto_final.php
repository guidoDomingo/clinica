<?php
/**
 * Script de limpieza final del proyecto
 * Este script elimina todos los archivos que no son necesarios para el funcionamiento del sistema
 * Incluye archivos de prueba, depuración, versiones antiguas, documentación redundante, 
 * scripts de verificación/corrección y HTML no esenciales.
 */

// Iniciar registro de la operación
$log_file = __DIR__ . '/logs/limpieza_final_' . date('Y-m-d_H-i-s') . '.log';
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

log_message("Iniciando limpieza final del proyecto...");

// Lista de archivos a eliminar
$archivos_a_eliminar = [
    // Archivos de prueba y temporales
    'ajax_test_slots.php',
    'api_test_reservas.php',
    'bash.exe.stackdump',
    'check_agenda_structure.php',
    'check_and_enable_pgsql.php',
    'check_http_client.php',
    'check_pgsql.php',
    'check_rs_servicios.php',
    'check_servicios_reservas.php',
    'check_simple_rs_servicios.php',
    'check_table.php',
    'check_table2.php',
    'corregir_bd_servicios.php',
    'corregir_formato_dias.php',
    'crear_tabla_simple.php',
    'crear_tablas_servicios.php',
    'crear_todas_tablas_servicios.php',
    'enable_pgsql.bat',
    'enable_pgsql.php',
    'fix_ajax_undefined_var.php',
    'fix_alerts_comprehensive.php',
    'fix_alerts_final.php',
    'fix_alerts.php',
    'fix_database_tables.php',
    'fix_database.bat',
    'fix_database.php',
    'fix_horarios_servicios.php',
    'fix_notifications_path.php',
    'fix_notifications.php',
    'fix_reservas_function.php',
    'generar_diagnostico.php',
    'icd11_description_test.html',
    'icd11_iframe_test.html',
    'icd11_integration_test.html',
    'icd11_service_test.php',
    'icd11_test.html',
    'ir_a_diagnostico.html',
    'list_tables.php',
    'php_test.php',
    'quick_fix.php',
    'reparar_agenda_medico.php',
    'reparar_visualizacion_reservas.php',
    'servicios_database.sql',
    'test_output.json',
    'test_syntax.bat',
    'syntax_check.php',
    'SOLUCION_HORARIOS_PASADOS.md',
    'SLOTS_SOLUTION_README_NEW.md',
    'servicios_js_update.js',

    // Scripts utilizados que ya no son necesarios
    'crear_tabla_reservas.php',
    'crear_tabla_reservas_ui.php',
    'crear_reserva_hoy.php',
    'crear_tabla_servicios_proveedores.php',
    'crear_tabla_servicios_requisitos.php',
    'crear_tabla_servicios_reservas.php',
    'crear_tabla_servicios_tarifas.php',
    'eliminar_archivos_prueba.php',
    'limpiar_proyecto.php',
    'limpiar_proyecto_avanzado.php',
    'ejecutar_creacion_tabla.php',
    
    // Documentación redundante o antigua
    'diagnostico_whatsapp_pdf_soluciones.md',
    'doctor_estado_fix.md',
    'FIXES_SERVICIOS_README.md',
    'HORARIOS_SLOTS_FIX.md',
    'icd11_fix_summary.md',
    'icd11_integration_summary.md',
    'README_SERVICIOS.md',
    
    // Archivos HTML innecesarios
    'diagnostico_horarios.html',
    'monitor_reservas.html',
    
    // AdminLTE Template zip (ya instalado)
    'AdminLTE-3.2.0.zip',
    
    // Scripts de implementación (ya completados)
    'actualizar_funcion_reservas.php',
    'ajax_filtrar_reservas.php',
    'ajax_slots_simplificados.php',
    'depurador_reservas.php',
    'implementacion_reservas.php',
    'insertar_datos_proveedores.php',
    'insertar_preformatos.php',
    'reparar_sistema_reservas.php',
    'verificacion_final_reservas.php',
    'verificar_reservas_implementacion.php'
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

// Limpiar archivos PDF temporales
$directorios_temporales = [
    __DIR__ . '/pdf_temp',
    __DIR__ . '/pdfs_temp',
    __DIR__ . '/temp'
];

foreach ($directorios_temporales as $directorio) {
    if (is_dir($directorio)) {
        log_message("Limpiando directorio temporal: $directorio");
        $archivos = glob($directorio . '/*');
        foreach ($archivos as $archivo) {
            if (is_file($archivo)) {
                if (unlink($archivo)) {
                    log_message("Eliminado archivo temporal: " . basename($archivo));
                    $total_eliminados++;
                }
            }
        }
    }
}

// Verificar y eliminar archivos PDF antiguos (más de 7 días)
$pdf_reservas_dir = __DIR__ . '/pdf_reservas';
if (is_dir($pdf_reservas_dir)) {
    log_message("Limpiando PDFs de reservas antiguos (>7 días)...");
    $archivos = glob($pdf_reservas_dir . '/*.pdf');
    $tiempo_limite = time() - (7 * 24 * 60 * 60); // 7 días
    $pdf_antiguos_eliminados = 0;
    
    foreach ($archivos as $archivo) {
        if (is_file($archivo) && filemtime($archivo) < $tiempo_limite) {
            if (unlink($archivo)) {
                $pdf_antiguos_eliminados++;
                $total_eliminados++;
            }
        }
    }
    
    log_message("Se eliminaron $pdf_antiguos_eliminados PDFs antiguos.");
}

// Eliminar logs antiguos
$logs_dir = __DIR__ . '/logs';
if (is_dir($logs_dir)) {
    log_message("Limpiando logs antiguos (>30 días)...");
    $archivos = glob($logs_dir . '/*.log');
    $tiempo_limite = time() - (30 * 24 * 60 * 60); // 30 días
    $logs_eliminados = 0;
    
    foreach ($archivos as $archivo) {
        if (is_file($archivo) && filemtime($archivo) < $tiempo_limite) {
            if (unlink($archivo)) {
                $logs_eliminados++;
                $total_eliminados++;
            }
        }
    }
    
    log_message("Se eliminaron $logs_eliminados logs antiguos.");
}

// Resumen final
log_message("¡Limpieza finalizada!");
log_message("Total de archivos eliminados: $total_eliminados");
log_message("Se ha creado un registro detallado en: $log_file");

echo "\n==========================================\n";
echo "   LIMPIEZA FINAL COMPLETADA               \n";
echo "   Se eliminaron $total_eliminados archivos\n";
echo "==========================================\n";
?>
