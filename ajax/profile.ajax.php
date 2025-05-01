<?php
require_once dirname(__FILE__) . "/../controller/profile.controller.php";

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class ProfileAjax {
    /**
     * Verifica si el perfil del usuario está completo
     */
    public function checkProfileCompletion() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Usuario no autenticado',
                'redirect' => 'login'
            ]);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Forzar verificación directa desde la base de datos
        $hasCompleteProfile = ControllerProfile::ctrHasCompleteProfile($userId);
        
        // Actualizar el estado en la sesión para que el PHP pueda usarlo también
        $_SESSION['profile_complete'] = $hasCompleteProfile;
        
        echo json_encode([
            'status' => 'success',
            'complete' => $hasCompleteProfile,
            'redirect' => $hasCompleteProfile ? null : 'perfil',
            'timestamp' => time() // Añadir timestamp para evitar caché
        ]);
    }
}

// Procesar solicitudes AJAX
if (isset($_POST['action'])) {
    $ajax = new ProfileAjax();
    
    switch ($_POST['action']) {
        case 'checkProfile':
            $ajax->checkProfileCompletion();
            break;
            
        default:
            error_log('Acción no reconocida en profile.ajax.php: ' . $_POST['action']);
            echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida: ' . $_POST['action']]);
            break;
    }
} else {
    error_log('No se recibió la acción en profile.ajax.php');
    echo json_encode(['status' => 'error', 'message' => 'No se especificó ninguna acción']);
}
?>