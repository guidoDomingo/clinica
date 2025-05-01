<?php
// Iniciamos la sesión al principio del archivo antes de cualquier salida
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class ControllerUser {
    static public function ctrLoginUser() {

        // Si ya hay una sesión activa, redirige al home
        if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] === "ok") {
            echo '<script>window.location.href = "home";</script>';
            exit();
        }

        // Verifica si los datos fueron enviados via POST
        if (isset($_POST['usuario']) && isset($_POST['password'])) {
            // Sanitiza los datos de entrada
            $usuario = strip_tags($_POST['usuario']); // Elimina etiquetas HTML/PHP
            $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8'); // Convierte caracteres especiales
            \Api\Core\Logger::info($usuario, "user: {$usuario}");
            \Api\Core\Logger::info($password, "user: {$password}");
            require_once "model/conexion.php";
            $db = Conexion::conectar();
            
            try {
                $stmt = $db->prepare("SELECT u.*, r.reg_name, r.reg_lastname FROM sys_users u JOIN sys_register r ON u.reg_id = r.reg_id WHERE u.user_email = :email AND u.user_is_active = true");
                $stmt->execute(['email' => $usuario]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Log the fetched user data
                require_once "api/core/Logger.php";
                \Api\Core\Logger::info($user, "Login attempt for user: {$usuario}");

                if ($user) {
                    $passwordValid = false;
                    $storedHash = $user['user_pass'];
                    
                    // Verificar con password_verify para hashes modernos
                    if (password_verify($password, $storedHash)) {
                        $passwordValid = true;
                    }
                    // Verificar hash MD5
                    else if ($storedHash === md5($password)) {
                        $passwordValid = true;
                    }
                    // Comparación directa (para pruebas o hashes antiguos)
                    else if ($storedHash === $password) {
                        $passwordValid = true;
                    }
                    
                    if ($passwordValid) {
                        $_SESSION["iniciarSesion"] = "ok";
                        $_SESSION["perfil_user"] = "ADMIN"; // You might want to get this from user roles table
                        $_SESSION["usuario"] = $user['reg_name'] . ' ' . $user['reg_lastname'];
                        $_SESSION["user_id"] = $user['user_id'];
                        
                        // Guardar el ID del doctor si existe en alguna tabla relacionada
                        $doctorId = self::obtenerDoctorIdPorUsuario($user['user_id']);
                        if ($doctorId) {
                            $_SESSION["doctor_id"] = $doctorId;
                        }
                        
                        // Update last login time
                        $updateStmt = $db->prepare("UPDATE sys_users SET user_last_login = CURRENT_TIMESTAMP WHERE user_id = :user_id");
                        $updateStmt->execute(['user_id' => $user['user_id']]);
                        
                        // Verificar si el usuario tiene perfil completo en rh_person
                        require_once "controller/profile.controller.php";
                        $hasCompleteProfile = ControllerProfile::ctrHasCompleteProfile($user['user_id']);
                        
                        if (!$hasCompleteProfile) {
                            // Si no tiene perfil completo, redirigir a la página de perfil
                            echo '<script>
                                Swal.fire({
                                    icon: "info",
                                    title: "Perfil incompleto",
                                    text: "Para continuar usando el sistema, necesitas completar tu información personal",
                                    confirmButtonText: "Completar perfil",
                                    allowOutsideClick: false,
                                    allowEscapeKey: false
                                }).then((result) => {
                                    window.location.href = "perfil";
                                });
                            </script>';
                            exit();
                        } else {
                            // Si tiene perfil completo, redirigir al home como es habitual
                            echo '<script>window.location.href = "home";</script>';
                            exit();
                        }
                    } else {
                        echo "<script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de autenticación',
                                text: 'Las credenciales proporcionadas son incorrectas. Por favor, inténtelo de nuevo.',
                                confirmButtonText: 'Aceptar',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = 'login';
                                }
                            });
                        </script>"; 
                    }
                } else {
                    echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de autenticación',
                            text: 'Las credenciales proporcionadas son incorrectas. Por favor, inténtelo de nuevo.',
                            confirmButtonText: 'Aceptar',
                            customClass: {
                                confirmButton: 'btn btn-primary'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'login';
                            }
                        });
                    </script>"; 
                }   
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage(), 3, "logs/application.log");
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error del sistema',
                        text: 'Ha ocurrido un error en el sistema. Por favor, inténtelo más tarde.',
                        confirmButtonText: 'Aceptar',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                </script>";
            }
        }
    }
    
    /**
     * Obtiene todos los usuarios activos del sistema
     * @return array|false Array con los usuarios o false si hay error
     */
    public function ctrObtenerUsuarios() {
        try {
            $stmt = Conexion::conectar()->prepare("
                SELECT u.user_id as id_usuario, r.reg_name as nombre, r.reg_lastname as apellido 
                FROM sys_users u 
                JOIN sys_register r ON u.reg_id = r.reg_id 
                WHERE u.user_is_active = true 
                ORDER BY r.reg_name, r.reg_lastname
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el ID del doctor asociado a un usuario del sistema
     * @param int $userId ID del usuario
     * @return int|null ID del doctor o null si no está asociado
     */
    private static function obtenerDoctorIdPorUsuario($userId) {
        try {
            $db = Conexion::conectar();
            
            // Primero verificamos si hay una relación directa en la tabla de doctores
            $stmt = $db->prepare("
                SELECT doctor_id 
                FROM doctor 
                WHERE user_id = :user_id
                LIMIT 1
            ");
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && isset($result['doctor_id'])) {
                return $result['doctor_id'];
            }
            
            // Si no encontramos en la tabla doctor, verificamos roles de usuario
            $stmt = $db->prepare("
                SELECT ur.role_id
                FROM sys_user_roles ur
                INNER JOIN sys_roles r ON ur.role_id = r.role_id
                WHERE ur.user_id = :user_id 
                AND (r.role_name LIKE '%doctor%' OR r.role_name LIKE '%médico%')
                LIMIT 1
            ");
            
            $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
            $stmt->execute();
            $roleResult = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si el usuario tiene rol de doctor pero no hay registro en la tabla doctor,
            // asumimos que el ID del doctor es el mismo que el del usuario
            if ($roleResult) {
                return $userId;
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error obteniendo ID del doctor: " . $e->getMessage());
            return null;
        }
    }
}