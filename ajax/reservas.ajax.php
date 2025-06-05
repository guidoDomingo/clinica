<?php
/**
 * Controlador AJAX para operaciones relacionadas con reservas
 */

// Aseguramos que todas las rutas sean relativas al directorio raíz
$rutaBase = dirname(__FILE__, 2); // Obtiene la ruta del directorio raíz (dos niveles arriba)
require_once $rutaBase . "/model/conexion.php";
require_once $rutaBase . "/model/reservas.model.php";

// Configurar cabeceras para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Iniciar sesión si no está iniciada
if (!isset($_SESSION)) {
    session_start();
}

// Verificar si es una solicitud POST con acción especificada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $modelo = new ReservasModel();
    
    switch ($accion) {
        case 'actualizar_estado':
            // Verificar parámetros necesarios
            if (isset($_POST['id']) && isset($_POST['estado'])) {
                $id = intval($_POST['id']);
                $estado = $_POST['estado'];
                
                // Actualizar estado
                $resultado = $modelo->actualizarEstado($id, $estado);
                
                if ($resultado) {
                    echo json_encode(['exito' => true, 'mensaje' => 'Estado actualizado correctamente']);
                } else {
                    echo json_encode(['exito' => false, 'mensaje' => 'Error al actualizar el estado']);
                }
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'Faltan parámetros requeridos']);
            }
            break;
            
        case 'registrar_confirmacion':
            // Verificar parámetros necesarios
            if (isset($_POST['id'])) {
                $id = intval($_POST['id']);
                $metodo = $_POST['metodo'] ?? 'whatsapp';
                
                // Registrar confirmación
                $resultado = $modelo->registrarConfirmacion($id, $metodo);
                
                if ($resultado) {
                    echo json_encode(['exito' => true, 'mensaje' => 'Confirmación registrada correctamente']);
                } else {
                    echo json_encode(['exito' => false, 'mensaje' => 'Error al registrar la confirmación']);
                }
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'ID de reserva no especificado']);
            }
            break;
            
        default:
            echo json_encode(['exito' => false, 'mensaje' => 'Acción no reconocida']);
            break;
    }
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'Solicitud no válida']);
}
?>
