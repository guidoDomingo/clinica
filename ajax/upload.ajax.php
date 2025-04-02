<?php
require_once "../model/archivos.model.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
        $uploadDir = '../view/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $persona = $_POST["id_persona_file"];
        $usuario = $_POST["id_usuario"];
        
        $allowedTypes = [
            'application/pdf', // PDF
            'image/jpeg',      // JPG
            'image/png',       // PNG
            'image/gif',       // GIF
            'image/webp',     // WEBP
            'image/svg+xml'    // SVG
        ];

        $maxSize = 25 * 1024 * 1024; // 25 MB

        $errors = [];
        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES['files']['name'][$key]);
            $fileSize = $_FILES['files']['size'][$key];
            $fileType = $_FILES['files']['type'][$key];
            $filePath = $uploadDir . $fileName;

            // Validar tipo de archivo
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "El archivo $fileName no es un PDF o una imagen válida.";
                continue;
            }

            // Validar tamaño del archivo
            if ($fileSize > $maxSize) {
                $errors[] = "El archivo $fileName excede el tamaño máximo de 25 MB.";
                continue;
            }

            // Mover el archivo al directorio de subida
            if (move_uploaded_file($tmpName, $filePath)) {
                echo "El archivo $fileName se ha subido correctamente.<br>";
                
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
                // return $resultado;
                


            } else {
                $errors[] = "Error al subir el archivo $fileName.";
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo $error . "<br>";
            }
        }
    } else {
        echo "No se han seleccionado archivos para subir.";
    }
}
?>