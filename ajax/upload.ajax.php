<?php
require_once "../model/archivos.model.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
        $uploadDir = '../view/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $persona = isset($_POST["id_persona_file"]) ? $_POST["id_persona_file"] : null;
        
        // Verificar que el ID de persona sea válido
        if (!$persona || empty($persona)) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'message' => 'Debe seleccionar un paciente para subir archivos.'));
            exit;
        }
        $usuario = isset($_POST["id_usuario"]) ? $_POST["id_usuario"] : 1; // Valor predeterminado si no existe
        $consulta = isset($_POST["id_consulta"]) ? $_POST["id_consulta"] : null; // Obtener ID de consulta si existe
        
        $allowedTypes = [
            'application/pdf',                                          // PDF
            'image/jpeg', 'image/jpg',                                 // JPG
            'image/png',                                               // PNG
            'image/gif',                                               // GIF
            'image/webp',                                              // WEBP
            'image/svg+xml',                                           // SVG
            'application/msword',                                      // DOC
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
            'application/vnd.ms-excel',                               // XLS
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // XLSX
            'application/vnd.ms-powerpoint',                          // PPT
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' // PPTX
        ];

        $maxSize = 25 * 1024 * 1024; // 25 MB

        $response = array('status' => 'success', 'message' => '', 'files' => array());
        $errors = [];
        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['files']['name'][$key]);
            $fileSize = $_FILES['files']['size'][$key];
            $fileType = $_FILES['files']['type'][$key];
            $filePath = $uploadDir . $fileName;

            // Validar tipo de archivo
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "El archivo $fileName no es un tipo de archivo permitido.";
                continue;
            }

            // Validar tamaño del archivo
            if ($fileSize > $maxSize) {
                $errors[] = "El archivo $fileName excede el tamaño máximo de 25 MB.";
                continue;
            }

            // Mover el archivo al directorio de subida
            if (move_uploaded_file($tmpName, $filePath)) {
                $response['files'][] = array(
                    'name' => $fileName,
                    'size' => $fileSize,
                    'type' => $fileType,
                    'path' => $filePath
                );
                
                $checksum = "";
                // Verifica si el archivo existe
                if (file_exists($filePath)) {
                    // Calcula el checksum SHA-256 del archivo
                    $checksum = hash_file('sha256', $filePath);
                } 
                $datos = array();
                $datos["id_persona"] = $persona;
                $datos["nombre_archivo"] = $fileName;
                $datos["tamano_archivo"] = $fileSize;
                $datos["ruta_archivo"] = $filePath;
                $datos["origen"] = "consulta";
                $datos["id_usuario"] = $usuario;
                $datos["checksum"] = $checksum;  
                $datos["tipo_archivo"] = $fileType;
                $resultado = ModelArchivos::mdlSetArchivo($datos);
                
                // Añadir resultado a la respuesta
                if ($resultado === "ok") {
                    $response['message'] .= "El archivo $fileName se ha subido correctamente. ";
                      // Si hay un ID de consulta, relacionar el archivo con la consulta
                    if ($consulta) {
                        // Log para depuración
                        $log_dir = "../logs";
                        if (!is_dir($log_dir)) {
                            mkdir($log_dir, 0777, true);
                        }
                        error_log(date('Y-m-d H:i:s') . " - Intentando relacionar archivo '$fileName' con consulta ID: $consulta\n", 3, "$log_dir/archivos.log");
                        
                        // Obtener el ID del archivo recién insertado
                        $idArchivo = ModelArchivos::mdlGetUltimoIdArchivo();
                        
                        if ($idArchivo) {
                            error_log(date('Y-m-d H:i:s') . " - ID de archivo obtenido: $idArchivo\n", 3, "$log_dir/archivos.log");
                            
                            // Relacionar el archivo con la consulta
                            $relacionado = ModelArchivos::mdlRelacionarArchivoConsulta($consulta, $idArchivo);
                            
                            if ($relacionado === "ok") {
                                error_log(date('Y-m-d H:i:s') . " - Archivo relacionado correctamente con la consulta\n", 3, "$log_dir/archivos.log");
                                $response['message'] .= "Archivo vinculado a la consulta #$consulta. ";
                            } else {
                                error_log(date('Y-m-d H:i:s') . " - Error al relacionar archivo: $relacionado\n", 3, "$log_dir/archivos.log");
                                $errors[] = "Error al relacionar el archivo $fileName con la consulta: $relacionado";
                            }
                        } else {
                            error_log(date('Y-m-d H:i:s') . " - No se pudo obtener el ID del archivo\n", 3, "$log_dir/archivos.log");
                            $errors[] = "Error al obtener el ID del archivo $fileName.";
                        }
                    }
                } else {
                    $errors[] = "Error al guardar la información del archivo $fileName en la base de datos.";
                }
            } else {
                $errors[] = "Error al subir el archivo $fileName.";
            }
        }

        if (!empty($errors)) {
            $response['status'] = 'error';
            $response['message'] = implode(" ", $errors);
        }
        
        // Devolver respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header('Content-Type: application/json');
        echo json_encode(array('status' => 'error', 'message' => 'No se han seleccionado archivos para subir.'));
    }
}
?>