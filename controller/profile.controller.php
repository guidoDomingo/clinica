<?php
require_once dirname(__FILE__) . "/../model/profile.model.php";
// Verificar si la clase Response existe, si no, no incluirla
if (file_exists(dirname(__FILE__) . "/../api/core/Response.php")) {
    require_once dirname(__FILE__) . "/../api/core/Response.php";
}

class ControllerProfile {
    /**
     * Obtiene los datos del perfil del usuario
     * @param int $userId ID del usuario
     * @return array|null Datos del perfil o null si no se encuentra
     */
    static public function ctrGetUserProfile($userId) {
        return ModelProfile::mdlGetUserProfile($userId);
    }
    
    /**
     * Verifica si un usuario tiene un perfil completo en rh_person
     * @param int $userId ID del usuario
     * @return bool True si tiene perfil completo, false en caso contrario
     */
    static public function ctrHasCompleteProfile($userId) {
        return ModelProfile::mdlHasCompleteProfile($userId);
    }
    
    /**
     * Cambia la contraseña del usuario
     * @param int $userId ID del usuario
     * @param string $currentPassword Contraseña actual
     * @param string $newPassword Nueva contraseña
     * @return string|bool "ok" si se cambió correctamente, mensaje de error en caso contrario
     */
    static public function ctrChangePassword($userId, $currentPassword, $newPassword) {
        // Verificar que la contraseña actual sea correcta
        if (!ModelProfile::mdlVerifyPassword($userId, $currentPassword)) {
            return "La contraseña actual no es correcta";
        }
        
        // Verificar que la nueva contraseña cumpla con los requisitos mínimos
        if (strlen($newPassword) < 6) {
            return "La nueva contraseña debe tener al menos 6 caracteres";
        }
        
        // Cambiar la contraseña
        $result = ModelProfile::mdlChangePassword($userId, $newPassword);
        
        if ($result === "ok") {
            return "ok";
        } else {
            return "Error al cambiar la contraseña: " . $result;
        }
    }
    
    /**
     * Actualiza los datos del perfil del usuario
     * @param int $userId ID del usuario
     * @param array $userData Datos del usuario a actualizar
     * @return string "ok" si se actualizó correctamente, mensaje de error en caso contrario
     */
    static public function ctrUpdateProfile($userId, $userData) {
        // Validar los datos mínimos
        if (!isset($userData['first_name']) || !isset($userData['last_name']) || !isset($userData['email'])) {
            return "Faltan datos obligatorios";
        }
        
        // Actualizar los datos en sys_users
        $result = ModelProfile::mdlUpdateUserData($userId, [
            'user_email' => $userData['email']
        ]);
        
        if ($result !== "ok") {
            return "Error al actualizar los datos del usuario: " . $result;
        }
        
        // Verificar si ya existe un registro en rh_person para este usuario
        $personId = ModelProfile::mdlGetPersonIdByUserId($userId);
        
        if ($personId) {
            // Actualizar los datos en rh_person
            $result = ModelProfile::mdlUpdatePersonData($personId, [
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'phone_number' => $userData['phone'] ?? null,
                'document_number' => $userData['document'] ?? null,
                'address' => $userData['address'] ?? null,
                'email' => $userData['email'],
                'birth_date' => $userData['birth_date'] ?? null,
                'gender' => $userData['gender'] ?? null
            ]);
        } else {
            // Crear un nuevo registro en rh_person
            $personData = [
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'phone_number' => $userData['phone'] ?? null,
                'document_number' => $userData['document'] ?? null,
                'address' => $userData['address'] ?? null,
                'email' => $userData['email'],
                'birth_date' => $userData['birth_date'] ?? null,
                'gender' => $userData['gender'] ?? null
            ];
            
            $personId = ModelProfile::mdlCreatePersonProfile($personData);
            
            if (!$personId || $personId === "error") {
                return "Error al crear el perfil de persona";
            }
            
            // Vincular el usuario con la persona en person_system_user
            $result = ModelProfile::mdlLinkPersonWithUser($personId, $userId);
        }
        
        return $result === "ok" ? "ok" : "Error al actualizar el perfil: " . $result;
    }
    
    /**
     * Actualiza la foto de perfil del usuario
     * @param int $userId ID del usuario
     * @param array $file Archivo de imagen
     * @return array ["status" => "success|error", "message" => "string", "data" => array()]
     */
    static public function ctrUpdateProfilePhoto($userId, $file) {
        // Validar que sea una imagen
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            return [
                "status" => "error",
                "message" => "El archivo debe ser una imagen (JPG, PNG o GIF)"
            ];
        }
        
        // Configurar la ruta donde se guardará la imagen
        $uploadDir = 'view/uploads/profile/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generar un nombre único para el archivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'user_' . $userId . '_' . time() . '.' . $extension;
        $targetFile = $uploadDir . $fileName;
        
        // Mover el archivo a la carpeta de destino
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Actualizar la ruta de la imagen en la base de datos
            $result = ModelProfile::mdlUpdateProfilePhoto($userId, $fileName);
            
            if ($result === "ok") {
                return [
                    "status" => "success",
                    "message" => "Foto de perfil actualizada correctamente",
                    "data" => [
                        "photo_url" => $targetFile
                    ]
                ];
            } else {
                // Si hubo un error al actualizar la base de datos, eliminar el archivo
                unlink($targetFile);
                
                return [
                    "status" => "error",
                    "message" => "Error al actualizar la foto de perfil en la base de datos"
                ];
            }
        } else {
            return [
                "status" => "error",
                "message" => "Error al subir la imagen"
            ];
        }
    }
}

// Manejador de solicitudes AJAX para el perfil de usuario
if (isset($_POST['action']) && !empty($_POST['action'])) {
    session_start();
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    if (!$userId) {
        echo json_encode([
            "status" => "error",
            "message" => "Usuario no autenticado"
        ]);
        exit;
    }
    
    switch ($_POST['action']) {
        case 'getProfile':
            $profile = ControllerProfile::ctrGetUserProfile($userId);
            
            if ($profile) {
                echo json_encode([
                    "status" => "success",
                    "data" => $profile
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se pudo obtener el perfil del usuario"
                ]);
            }
            break;
            
        case 'updateProfile':
            $userData = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'document' => $_POST['document'] ?? '',
                'address' => $_POST['address'] ?? '',
                'birth_date' => $_POST['birth_date'] ?? null,
                'gender' => $_POST['gender'] ?? ''
            ];
            
            $result = ControllerProfile::ctrUpdateProfile($userId, $userData);
            
            if ($result === "ok") {
                // Actualizar el estado del perfil en la sesión
                $_SESSION['profile_complete'] = ControllerProfile::ctrHasCompleteProfile($userId);
                
                echo json_encode([
                    "status" => "success",
                    "message" => "Perfil actualizado correctamente",
                    "profile_complete" => $_SESSION['profile_complete']
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => $result
                ]);
            }
            break;
            
        case 'changePassword':
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword)) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Todos los campos son obligatorios"
                ]);
                break;
            }
            
            $result = ControllerProfile::ctrChangePassword($userId, $currentPassword, $newPassword);
            
            if ($result === "ok") {
                echo json_encode([
                    "status" => "success",
                    "message" => "Contraseña actualizada correctamente"
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => $result
                ]);
            }
            break;
            
        case 'uploadPhoto':
            if (!isset($_FILES['profile_photo'])) {
                echo json_encode([
                    "status" => "error",
                    "message" => "No se ha enviado ninguna imagen"
                ]);
                break;
            }
            
            $result = ControllerProfile::ctrUpdateProfilePhoto($userId, $_FILES['profile_photo']);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode([
                "status" => "error",
                "message" => "Acción no reconocida"
            ]);
    }
    exit;
}