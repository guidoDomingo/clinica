<?php
/**
 * Script para eliminar archivos de prueba, diagnóstico, mantenimiento y temporales
 * Este script mueve los archivos a una carpeta de respaldo antes de eliminarlos
 * 
 * Se han ampliado las categorías para incluir más tipos de archivos que no son
 * necesarios en un entorno de producción.
 */

// Definir la carpeta de respaldo
$backupFolder = 'backups/limpieza_' . date('Y-m-d_H-i-s');

// Crear la carpeta de respaldo si no existe
if (!is_dir($backupFolder)) {
    if (!mkdir($backupFolder, 0777, true)) {
        die("Error: No se pudo crear la carpeta de respaldo $backupFolder");
    }
}

// Lista completa de archivos a eliminar, organizados por categorías
$testFiles = [
    // Archivos de diagnóstico
    'diagnostico_whatsapp_pdf_soluciones.md',
    'DOCUMENTACION_ENVIO_PDF.md',
    'DOCUMENTACION_WHATSAPP_PDF.md',
    'SLOTS_ACTUALIZACION_SOLUTION.md',
    'SLOTS_DATE_SOLUTION.md',
    'SLOTS_SOLUTION_README.md',
    'SLOTS_SOLUTION_README_NEW.md',
    'SOLUCION_CONSULTAS.md',
    'SOLUCION_HORARIOS_PASADOS.md',
      // Scripts de mantenimiento y limpieza
    'activar_extensiones_php.bat',
    'ajax_filtrar_reservas.php',
    'configurar_mantenimiento_automatico.bat',
    'limpiar_logs.php',
    'limpiar_pdf_temporales.php',
    'limpiar_proyecto.php',
    'limpiar_proyecto_avanzado.php',
    'limpiar_proyecto_final.php',
    'limpiar_whatsapp_innecesario.php',
    'limpieza_produccion.php',
    'mantenimiento_sistema.php',
    'quick_fix.php',
    'syntax_check.php',
    
    // Archivos auxiliares
    'phpinfo.php',
    
    // Scripts de listado que pueden ser reemplazados por la interfaz
    'listar_reservas.php',
    'listar_todas_reservas.php',
    'historial_reservas.php',
    
    // Archivos Ajax de prueba y respaldo
    'ajax/enviar_media.php.new',
    'ajax/guardar-consulta.ajax.php.bak',
    'ajax/send_pdf_test.php',
    'ajax/pdf_uploader.php',
    'ajax/subir_pdf.php',
    'ajax/upload_pdf.php',
];

// Contador de archivos procesados
$moved = 0;
$errors = [];

echo "Iniciando proceso de limpieza de archivos de prueba...\n";
echo "Los archivos serán respaldados en: $backupFolder\n\n";

// Procesar cada archivo
foreach ($testFiles as $fileName) {
    if (file_exists($fileName)) {
        echo "Procesando: $fileName...";
        
        // Respaldar el archivo
        if (copy($fileName, "$backupFolder/$fileName")) {
            // Eliminar el archivo original
            if (unlink($fileName)) {
                echo " [ELIMINADO]\n";
                $moved++;
            } else {
                echo " [ERROR: No se pudo eliminar]\n";
                $errors[] = "No se pudo eliminar: $fileName";
            }
        } else {
            echo " [ERROR: No se pudo respaldar]\n";
            $errors[] = "No se pudo respaldar: $fileName";
        }
    } else {
        echo "Archivo no encontrado: $fileName\n";
    }
}

// Mostrar resumen
echo "\n--- Resumen del proceso ---\n";
echo "Archivos respaldados y eliminados: $moved\n";
echo "Errores encontrados: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\nDetalle de errores:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}

echo "\nProceso completado. Los archivos han sido respaldados en: $backupFolder\n";

// Finalmente, respaldar y eliminar este propio script
echo "\nFinalizando: Respaldando y eliminando este script...\n";
if (copy(__FILE__, "$backupFolder/" . basename(__FILE__))) {
    if (unlink(__FILE__)) {
        echo "Script de limpieza eliminado. El proceso ha finalizado completamente.\n";
        $moved++;
    } else {
        echo "ERROR: No se pudo eliminar este script. Elimínelo manualmente.\n";
        $errors[] = "No se pudo eliminar: " . basename(__FILE__);
    }
} else {
    echo "ERROR: No se pudo respaldar este script.\n";
    $errors[] = "No se pudo respaldar: " . basename(__FILE__);
}

// Crear un archivo README en la carpeta de respaldo
$readmeContent = "# Respaldo de archivos de prueba\n\n";
$readmeContent .= "Fecha de respaldo: " . date('Y-m-d H:i:s') . "\n\n";
$readmeContent .= "Estos archivos fueron respaldados antes de ser eliminados del proyecto por considerarse archivos de prueba no necesarios para producción.\n\n";
$readmeContent .= "## Lista de archivos\n\n";

foreach ($testFiles as $fileName) {
    if (file_exists("$backupFolder/$fileName")) {
        $readmeContent .= "- `$fileName`\n";
    }
}

file_put_contents("$backupFolder/README.md", $readmeContent);
echo "Se ha creado un archivo README.md en la carpeta de respaldo con información sobre los archivos.\n";
