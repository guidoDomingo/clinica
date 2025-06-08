<?php
/**
 * Script avanzado para limpieza profunda del proyecto
 * Identifica y elimina archivos innecesarios basados en patrones y análisis de uso
 */

// Configuración
$dirBase = __DIR__;
$ejecutarEliminacion = true; // Cambiar a false para solo simular (no elimina archivos)
$modoInteractivo = false; // Cambiar a true para confirmar cada eliminación

// Log personalizado
function logMensaje($mensaje, $tipo = 'info') {
    $prefijos = [
        'info' => '[INFO] ',
        'success' => '[✓] ',
        'error' => '[✗] ',
        'warning' => '[!] ',
        'header' => "\n=== ",
        'footer' => '=== ',
        'question' => '[?] ',
    ];
    
    $prefijo = isset($prefijos[$tipo]) ? $prefijos[$tipo] : '';
    echo $prefijo . $mensaje . "\n";
}

// Función para eliminar un archivo
function eliminarArchivo($ruta, $ejecutar = true, $interactivo = false) {
    if (!file_exists($ruta)) {
        return ['success' => false, 'error' => 'no_existe'];
    }
    
    // Modo interactivo: solicitar confirmación
    if ($interactivo) {
        echo "[?] ¿Eliminar el archivo " . basename($ruta) . "? (s/n): ";
        $respuesta = trim(fgets(STDIN));
        if (strtolower($respuesta) !== 's') {
            return ['success' => false, 'error' => 'cancelado_por_usuario'];
        }
    }
    
    if ($ejecutar) {
        try {
            if (unlink($ruta)) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'error_al_eliminar'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    } else {
        return ['success' => true, 'simulated' => true];
    }
}

// Función para buscar archivos por patrón
function buscarArchivos($patron, $dirBase) {
    return glob($dirBase . '/' . $patron);
}

// Función para analizar si un archivo es referenciado en otros archivos
function archivoEsReferenciado($archivo, $dirBase) {
    $nombreArchivo = basename($archivo);
    $resultado = shell_exec('findstr /S /I "' . $nombreArchivo . '" "' . $dirBase . '\\*.php" "' . $dirBase . '\\*.js" "' . $dirBase . '\\*.html"');
    return !empty($resultado);
}

// Contadores
$totales = [
    'encontrados' => 0,
    'eliminados' => 0,
    'errores' => 0,
    'omitidos' => 0,
    'categorias' => []
];

// Mostrar información del modo de ejecución
logMensaje("LIMPIEZA AVANZADA DEL PROYECTO", 'header');
logMensaje("Fecha de ejecución: " . date('Y-m-d H:i:s'));
logMensaje("Directorio base: " . $dirBase);
logMensaje("Modo: " . ($ejecutarEliminacion ? 'Eliminación real' : 'Simulación (no se eliminarán archivos)'));
logMensaje("Interactivo: " . ($modoInteractivo ? 'Sí (solicitará confirmación)' : 'No (automático)'));
logMensaje("");

// Definir categorías y patrones de archivos a eliminar
$categorias = [
    'check_files' => [
        'descripcion' => 'Archivos de verificación y comprobación',
        'patrones' => [
            'check_*.php',
            'verificar_*.php',
            'verify_*.php'
        ],
        'excluir' => [
            'verificar_autenticacion.php'
        ]
    ],
    'fix_files' => [
        'descripcion' => 'Archivos de corrección y arreglos',
        'patrones' => [
            'fix_*.php',
            'fix_*.bat',
            'corregir_*.php',
            'reparar_*.php'
        ]
    ],
    'temp_archives' => [
        'descripcion' => 'Archivos ZIP y comprimidos',
        'patrones' => [
            '*.zip',
            '*.rar',
            '*.tar',
            '*.gz',
            '*.7z'
        ]
    ],
    'table_creation' => [
        'descripcion' => 'Scripts de creación de tablas',
        'patrones' => [
            'crear_tabla_*.php',
            '*_tables.php',
            '*_database.sql',
            '*.sql'
        ],
        'excluir' => [
            'db_structure.sql',
            'schema.sql',
            'initial_data.sql'
        ]
    ],
    'documentation_md' => [
        'descripcion' => 'Archivos de documentación redundantes',
        'patrones' => [
            '*_FIX.md',
            '*_SOLUTION.md',
            '*_README.md',
        ],
        'excluir' => [
            'README.md',
            'DOCUMENTACION_WHATSAPP_PDF.md'
        ]
    ],
    'redundant_html' => [
        'descripcion' => 'Archivos HTML redundantes',
        'patrones' => [
            '*.html'
        ],
        'excluir' => [
            'index.html',
            'login.html',
            'dashboard.html',
            'monitor_reservas.html'
        ]
    ],
    'icd11_test_files' => [
        'descripcion' => 'Archivos de prueba ICD11',
        'patrones' => [
            'icd11_*.html',
            'icd11_*.php'
        ]
    ],
    'crear_files' => [
        'descripcion' => 'Scripts de creación de estructuras',
        'patrones' => [
            'crear_*.php'
        ]
    ]
];

// Procesar cada categoría
foreach ($categorias as $categoria => $info) {
    logMensaje($info['descripcion'], 'header');
    
    $archivosEnCategoria = 0;
    $eliminadosEnCategoria = 0;
    $erroresEnCategoria = 0;
    $omitidosEnCategoria = 0;
    
    // Lista de archivos a excluir
    $excluir = isset($info['excluir']) ? $info['excluir'] : [];
    
    foreach ($info['patrones'] as $patron) {
        $archivos = buscarArchivos($patron, $dirBase);
        
        foreach ($archivos as $archivo) {
            $nombreBase = basename($archivo);
            
            // Verificar si este archivo debe ser excluido
            if (in_array($nombreBase, $excluir)) {
                logMensaje("Excluido por configuración: " . $nombreBase, 'info');
                $omitidosEnCategoria++;
                continue;
            }
            
            $archivosEnCategoria++;
            $totales['encontrados']++;
            
            $nombreRelativo = str_replace($dirBase . '/', '', $archivo);
            
            // Verificar si el archivo es referenciado en otros archivos
            if (archivoEsReferenciado($archivo, $dirBase)) {
                logMensaje("Omitido (referenciado en otros archivos): " . $nombreRelativo, 'warning');
                $omitidosEnCategoria++;
                $totales['omitidos']++;
                continue;
            }
            
            $resultado = eliminarArchivo($archivo, $ejecutarEliminacion, $modoInteractivo);
            
            if ($resultado['success']) {
                if (isset($resultado['simulated'])) {
                    logMensaje("Se eliminaría: " . $nombreRelativo, 'info');
                } else {
                    logMensaje("Eliminado: " . $nombreRelativo, 'success');
                    $eliminadosEnCategoria++;
                    $totales['eliminados']++;
                }
            } else {
                if ($resultado['error'] === 'cancelado_por_usuario') {
                    logMensaje("Omitido por el usuario: " . $nombreRelativo, 'warning');
                    $omitidosEnCategoria++;
                    $totales['omitidos']++;
                } else {
                    $mensaje = "Error al eliminar: " . $nombreRelativo;
                    if ($resultado['error'] == 'no_existe') {
                        $mensaje .= " (no existe)";
                    } else {
                        $mensaje .= " (" . $resultado['error'] . ")";
                    }
                    logMensaje($mensaje, 'error');
                    $erroresEnCategoria++;
                    $totales['errores']++;
                }
            }
        }
    }
    
    // Resumen de la categoría
    if ($archivosEnCategoria > 0) {
        logMensaje("Resumen de {$info['descripcion']}: {$archivosEnCategoria} encontrados, {$eliminadosEnCategoria} eliminados, {$omitidosEnCategoria} omitidos, {$erroresEnCategoria} errores", 'footer');
    } else {
        logMensaje("No se encontraron archivos en esta categoría", 'footer');
    }
    
    $totales['categorias'][$categoria] = [
        'encontrados' => $archivosEnCategoria,
        'eliminados' => $eliminadosEnCategoria,
        'omitidos' => $omitidosEnCategoria,
        'errores' => $erroresEnCategoria
    ];
    
    logMensaje("");
}

// Verificar directorios vacíos
logMensaje("Verificando directorios vacíos", 'header');
$directoriosAVerificar = [
    'temp',
    'cache',
    'logs',
    'uploads/temp',
    'pdf_temp',
    'pdfs_temp'
];

foreach ($directoriosAVerificar as $dir) {
    $rutaCompleta = $dirBase . '/' . $dir;
    if (is_dir($rutaCompleta)) {
        $archivos = scandir($rutaCompleta);
        $archivos = array_diff($archivos, ['.', '..', '.gitkeep', '.htaccess']);
        
        if (empty($archivos)) {
            logMensaje("Directorio vacío encontrado: {$dir}/", 'info');
            
            // Crear archivo .gitkeep para mantener el directorio en repositorio
            $gitkeepFile = $rutaCompleta . '/.gitkeep';
            if (!file_exists($gitkeepFile) && $ejecutarEliminacion) {
                file_put_contents($gitkeepFile, '# Este archivo es para mantener el directorio en el repositorio');
                logMensaje("Creado archivo .gitkeep en {$dir}/", 'success');
            }
        } else {
            logMensaje("Directorio no vacío: {$dir}/ (" . count($archivos) . " elementos)", 'info');
        }
    } else {
        logMensaje("El directorio {$dir}/ no existe", 'warning');
    }
}

// Resumen final
logMensaje("RESUMEN DE LA LIMPIEZA AVANZADA", 'header');
logMensaje("Total de archivos analizados: " . $totales['encontrados']);
logMensaje("Total de archivos eliminados: " . $totales['eliminados']);
logMensaje("Total de archivos omitidos: " . $totales['omitidos']);
logMensaje("Total de errores: " . $totales['errores']);

// Detalles por categoría
logMensaje("\nDesglose por categoría:");
foreach ($categorias as $categoria => $info) {
    $cat = $totales['categorias'][$categoria];
    if ($cat['encontrados'] > 0) {
        logMensaje("- {$info['descripcion']}: {$cat['encontrados']} encontrados, {$cat['eliminados']} eliminados, {$cat['omitidos']} omitidos", 'info');
    }
}

logMensaje("\nLimpieza avanzada completada " . ($ejecutarEliminacion ? "exitosamente" : "(simulación)") . " el " . date('Y-m-d H:i:s'), 'footer');
