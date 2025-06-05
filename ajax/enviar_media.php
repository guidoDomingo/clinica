<?php
/**
 * Controlador para enviar archivos multimedia (PDF) a través de la API externa
 * Este script maneja la integración con la API de envío de documentos/media
 */

// Configurar cabeceras para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Aseguramos que todas las rutas sean relativas al directorio raíz
$rutaBase = dirname(__FILE__, 2); // Obtiene la ruta del directorio raíz (dos niveles arriba)
require_once $rutaBase . "/model/conexion.php";

// Iniciar sesión si no está iniciada
if (!isset($_SESSION)) {
    session_start();
}

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del cuerpo de la solicitud
    $datos = json_decode(file_get_contents('php://input'), true);
    
    // Verificar si los datos necesarios existen
    if (isset($datos['telefono']) && isset($datos['mediaUrl']) && isset($datos['mediaCaption'])) {
        
        // Validar el teléfono (asegurarse que tiene el formato correcto)
        $telefono = trim($datos['telefono']);
        // Validar que el teléfono tenga un formato válido (puedes ajustar esta validación según tus necesidades)
        if (!preg_match('/^\d{9,15}$/', $telefono)) {
            echo json_encode(['success' => false, 'error' => 'Formato de teléfono inválido']);
            exit;
        }
        
        // Preparar datos para la solicitud a la API externa
        $apiUrl = 'http://aventisdev.com:8082/media.php';
        $username = 'admin';
        $password = '1234';
        
        // Crear contexto para la solicitud HTTP con autenticación básica
        $postData = http_build_query([
            'telefono' => $telefono,
            'mediaUrl' => $datos['mediaUrl'],
            'mediaCaption' => $datos['mediaCaption']
        ]);
        
        $authHeader = base64_encode($username . ':' . $password);
        
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
                            "Authorization: Basic " . $authHeader . "\r\n",
                'method'  => 'POST',
                'content' => $postData
            ]
        ];
          // Registrar el intento de envío para depuración
        error_log('Enviando documento a ' . $telefono);
        error_log('URL de documento: ' . $datos['mediaUrl']);
        error_log('Datos de envío: ' . $postData);
        
        // Realizar solicitud a la API externa
        $context = stream_context_create($options);
        
        // Capturar errores de la petición
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            error_log("Error en la petición HTTP: $errstr");
            return true;
        });
        
        $result = file_get_contents($apiUrl, false, $context);
        
        // Restaurar el manejador de errores
        restore_error_handler();
          if ($result === FALSE) {
            // Manejar error de conexión
            $httpCode = 'Desconocido';
            if (isset($http_response_header)) {
                $partes = explode(' ', $http_response_header[0]);
                if (isset($partes[1])) {
                    $httpCode = $partes[1];
                }
            }
            
            error_log('Error al conectar con la API externa. Código HTTP: ' . $httpCode);
            error_log('Headers de respuesta: ' . print_r($http_response_header, true));
            
            echo json_encode([
                'success' => false, 
                'error' => 'Error al conectar con la API externa', 
                'httpCode' => $httpCode
            ]);
        } else {
            // Decodificar respuesta
            $response = json_decode($result, true);
            
            // Registrar respuesta para depuración
            error_log('Respuesta de la API: ' . $result);
            
            // Verificar si la respuesta fue exitosa
            if (isset($response['status']) && $response['status'] === 'ok') {
                error_log('Envío de documento exitoso a: ' . $telefono);
                echo json_encode([
                    'success' => true, 
                    'data' => $response,
                    'message' => 'Documento enviado correctamente al número: ' . $telefono
                ]);
            } else {
                error_log('Error en la respuesta de la API: ' . $result);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Error en la respuesta de la API', 
                    'response' => $response,
                    'rawResponse' => $result
                ]);
            }
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Faltan parámetros requeridos']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>
