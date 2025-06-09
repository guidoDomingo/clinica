<?php
// Incluir los archivos de depuración
require_once "../logs/debug_guardar.php";
require_once "../logs/debug_guardar_detallado.php"; 

// Iniciar log de depuración con información detallada
debug_log("Iniciando proceso de guardar consulta", ["POST" => $_POST, "FILES" => $_FILES], "[CONSULTA]");
debug_detallado('INICIO', "Iniciando proceso de guardar consulta", ["POST" => $_POST], 'info');

// Verificar las extensiones PHP cargadas
debug_detallado('CONFIG', "Verificando configuración PHP", [
    'version_php' => phpversion(),
    'extensiones' => get_loaded_extensions(),
    'memoria_limite' => ini_get('memory_limit'),
    'tiempo_ejecucion' => ini_get('max_execution_time')
], 'debug');

require_once "../controller/consultas.controller.php";
require_once "../model/consultas.model.php";

// Verificar que los archivos se hayan cargado correctamente
debug_detallado('ARCHIVOS', "Verificando carga de archivos", [
    'controller_existe' => class_exists('ControllerConsulta'),
    'model_existe' => class_exists('ModelConsulta')
], 'info');

// Verificar estructura de tablas relevantes
debug_estructura_tabla('consultas');
debug_estructura_tabla('rh_person');

class TableConsultas {
    public function ajaxInsertarConsulta($datos) {
        debug_detallado('INSERTAR', "Llamando al controlador para insertar consulta", ['datos' => array_keys($datos)], 'info');
        $response = ControllerConsulta::ctrSetConsulta($datos);
        debug_detallado('INSERTAR', "Respuesta del controlador", ['response' => $response], 'info');
        echo $response;
    }

    public function ajaxEliminarConsulta($id) {
        debug_detallado('ELIMINAR', "Llamando al controlador para eliminar consulta", ['id' => $id], 'info');
        $response = ControllerConsulta::ctrEliminarConsulta($id);
        debug_detallado('ELIMINAR', "Respuesta del controlador", ['response' => $response], 'info');
        echo json_encode($response);
    }
}

// Procesar la inserción o actualización de una consulta
if (isset($_POST["motivoscomunes"]) && isset($_POST["formatoConsulta"])) {
    debug_detallado('PROCESO', "Iniciando procesamiento de consulta", [
        'motivoscomunes' => $_POST["motivoscomunes"],
        'formatoConsulta' => $_POST["formatoConsulta"]
    ], 'info');
    
    $insertar = new TableConsultas();
    $datos = array();

    // Recopilar todos los datos del formulario
    foreach ($_POST as $key => $value) {
        $datos[$key] = $value;
        // Log solo para campos críticos para evitar sobrecarga
        if (in_array($key, ['idPersona', 'id_user', 'id_reserva', 'txtmotivo'])) {
            debug_detallado('DATOS', "Campo crítico recibido", [$key => $value], 'info');
        }
    }

    // Verificar campos obligatorios
    $camposRequeridos = ['idPersona', 'motivoscomunes', 'txtmotivo'];
    $camposFaltantes = [];
    
    foreach ($camposRequeridos as $campo) {
        if (!isset($datos[$campo]) || empty($datos[$campo])) {
            $camposFaltantes[] = $campo;
        }
    }
    
    if (!empty($camposFaltantes)) {
        $mensajeError = "Faltan campos requeridos: " . implode(', ', $camposFaltantes);
        debug_detallado('VALIDACION', $mensajeError, [], 'error');
        echo "error: " . $mensajeError;
        exit;
    }

    // Manejar archivos adjuntos (si existen)
    if (!empty($_FILES)) {
        debug_detallado('ARCHIVOS', "Procesando archivos adjuntos", ['files' => array_keys($_FILES)], 'info');
        
        $archivos = $_FILES;
        $rutaDestino = '../view/uploads/'; // Cambia esto por la ruta donde quieras guardar los archivos
        
        // Verificar si el directorio existe, si no, crearlo
        if (!is_dir($rutaDestino)) {
            if (!mkdir($rutaDestino, 0777, true)) {
                debug_detallado('ARCHIVOS', "Error al crear directorio para archivos", ['ruta' => $rutaDestino], 'error');
            }
        }
        
        foreach ($archivos as $nombreCampo => $archivo) {
            if (is_array($archivo['name'])) {
                // Múltiples archivos
                for ($i = 0; $i < count($archivo['name']); $i++) {
                    if (!empty($archivo['name'][$i])) {
                        $nombreArchivo = uniqid() . '_' . basename($archivo['name'][$i]);
                        $rutaCompleta = $rutaDestino . $nombreArchivo;
                        
                        if (move_uploaded_file($archivo['tmp_name'][$i], $rutaCompleta)) {
                            debug_detallado('ARCHIVOS', "Archivo guardado correctamente", [
                                'nombre' => $archivo['name'][$i], 
                                'ruta' => $rutaCompleta
                            ], 'success');
                            $datos['archivos'][] = $rutaCompleta;
                        } else {
                            debug_detallado('ARCHIVOS', "Error al guardar archivo", [
                                'nombre' => $archivo['name'][$i], 
                                'error' => error_get_last()
                            ], 'error');
                        }
                    }
                }
            } else {
                // Un solo archivo
                if (!empty($archivo['name'])) {
                    $nombreArchivo = uniqid() . '_' . basename($archivo['name']);
                    $rutaCompleta = $rutaDestino . $nombreArchivo;
                    
                    if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                        debug_detallado('ARCHIVOS', "Archivo guardado correctamente", [
                            'nombre' => $archivo['name'], 
                            'ruta' => $rutaCompleta
                        ], 'success');
                        $datos['archivos'][] = $rutaCompleta;
                    } else {
                        debug_detallado('ARCHIVOS', "Error al guardar archivo", [
                            'nombre' => $archivo['name'], 
                            'error' => error_get_last()
                        ], 'error');
                    }
                }
            }
        }
    }    // Verificar si es una actualización o una nueva consulta
    if (isset($datos['id_consulta']) && !empty($datos['id_consulta'])) {
        // Es una actualización
        debug_detallado('ACTUALIZACION', "Detectada actualización de consulta existente", [
            'id_consulta' => $datos['id_consulta']
        ], 'info');
        
        try {
            $insertar->ajaxInsertarConsulta($datos); // El modelo ya maneja la diferencia
            debug_detallado('ACTUALIZACION', "Consulta actualizada correctamente", [], 'success');
        } catch (Exception $e) {
            debug_detallado('ACTUALIZACION', "Error al actualizar consulta", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');
            echo "Error de actualización: " . $e->getMessage();
        }
    } else {
        // Es una nueva consulta
        debug_detallado('INSERCION', "Detectada creación de nueva consulta", [], 'info');
        
        // Registrar datos para depuración
        $log_dir = "../logs";
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        
        // Registrar sólo los campos importantes para no llenar el log
        $log_data = [
            'id_persona' => $datos['idPersona'] ?? 'no-id',
            'txtmotivo' => $datos['txtmotivo'] ?? 'no-motivo',
            'consulta-textarea' => substr($datos['consulta-textarea'] ?? '', 0, 100) . '...',
        ];
        error_log(date('Y-m-d H:i:s') . " - Guardando consulta: " . json_encode($log_data) . "\n", 3, "$log_dir/consultas.log");
        
        try {
            debug_detallado('INSERCION', "Llamando al controlador para guardar nueva consulta", [
                'datos_clave' => [
                    'id_persona' => $datos['idPersona'] ?? null,
                    'id_user' => $datos['id_user'] ?? null
                ]
            ], 'info');
            
            $response = ControllerConsulta::ctrSetConsulta($datos);
            debug_detallado('INSERCION', "Respuesta recibida del controlador", ['response' => $response], 'info');
            
            // Si la respuesta incluye el ID de la consulta, devolvemos ese ID
            if (is_numeric($response)) {
                echo "ok id:" . $response;
                debug_detallado('INSERCION', "Consulta guardada exitosamente", ['id_consulta' => $response], 'success');
                error_log(date('Y-m-d H:i:s') . " - Consulta guardada exitosamente con ID: $response\n", 3, "$log_dir/consultas.log");
            } else {
                echo $response;
                debug_detallado('INSERCION', "Error al guardar consulta (respuesta no numérica)", ['response' => $response], 'error');
                error_log(date('Y-m-d H:i:s') . " - Error al guardar consulta: $response\n", 3, "$log_dir/consultas.log");
            }
        } catch (Exception $e) {
            $error_msg = "Error al guardar consulta: " . $e->getMessage();
            debug_detallado('INSERCION', "Excepción al guardar consulta", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 'error');
            error_log(date('Y-m-d H:i:s') . " - $error_msg\n", 3, "$log_dir/consultas.log");
            echo $error_msg;
        }
    }
}

// Procesar la eliminación de una consulta
if (isset($_POST["idConsulta"]) && isset($_POST["operacion"])) {
    $crud = new TableConsultas();
    if ($_POST["operacion"] == "eliminarconsulta") {
        $crud->ajaxEliminarConsulta($_POST["idConsulta"]);
    }
}
?>