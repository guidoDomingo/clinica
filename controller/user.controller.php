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

                if ($user && $password === $user['user_pass'] ) {
                    $_SESSION["iniciarSesion"] = "ok";
                    $_SESSION["perfil_user"] = "ADMIN"; // You might want to get this from user roles table
                    $_SESSION["usuario"] = $user['reg_name'] . ' ' . $user['reg_lastname'];
                    $_SESSION["user_id"] = $user['user_id'];
                    
                    // Update last login time
                    $updateStmt = $db->prepare("UPDATE sys_users SET user_last_login = CURRENT_TIMESTAMP WHERE user_id = :user_id");
                    $updateStmt->execute(['user_id' => $user['user_id']]);
                    
                    echo '<script>window.location.href = "home";</script>';
                    exit();
                
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
}