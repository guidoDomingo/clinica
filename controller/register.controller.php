<?php
require_once __DIR__ . "/../sys_sql/WelcomeEmail.php";
// Incluye el autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



class ControllerRegister {
    static public function ctrRegisterNew(){
            $datos = array();
            foreach ($_POST as $key => $value) {
                $datos[$key] = $value;
            }
            // var_dump($datos);
            if (isset($datos["regName"]) && isset($datos["regEmail"]) && isset($datos["regTel"])) {
            $correo = $datos["regEmail"];
            $securepass = generarContrasenaSegura($correo);
            $datos["securepass"] = $securepass;
            $datos["sendemail"] = false;
            $datos["estado"] = "INACTIVO";
            $insertReg = ModelRegister::mdlSetRegister($datos);
            if ($insertReg =="ok") {
            // Datos del usuario
            $userName = $datos["regName"];
            $startLink = "app.miclinica.com.py";
            // Crear una instancia de la clase WelcomeEmail
            $welcomeEmail = new WelcomeEmail($userName, $startLink, $securepass);

            // Obtener el cuerpo del correo
            $emailBody = $welcomeEmail->getBody();
            $destinatario = $datos["regEmail"];
            // Crea una instancia de PHPMailer
            $mail = new PHPMailer(true);

            try {
                $mail->CharSet = 'UTF-8'; // Establecer la codificación a UTF-8
                $mail->isSMTP();
                $mail->Host = 'mail.agiltienda.com.py';
                $mail->SMTPAuth = true;
                $mail->Username = 'soporte@miclinica.com.py';
                $mail->Password = '=@?;Gg,bB&H5';
                // $mail->SMTPSecure = 'tls';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Encriptación
                $mail->Port = 587;

                // Destinatarios
                $mail->setFrom('soporte@miclinica.com.py', 'Administrador');
                $mail->addAddress($destinatario, 'Cliente');

                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = 'Asunto del correo';
                $mail->Body    = $emailBody;

                // Enviar el correo
                if ($mail->send()) {
                    echo 'El correo ha sido enviado exitosamente.';
                    // Actualizar el campo `envio_de_correo_exitoso` en la base de datos
                    $envioExitoso = true;
                    $datos["sendemail"] = $envioExitoso;
                } else {
                    echo 'Error al enviar el correo.';
                    $envioExitoso = false;
                    $datos["sendemail"] = $envioExitoso;
                }
            } catch (Exception $e) {
                echo "El correo no pudo ser enviado. Error: {$mail->ErrorInfo}";
                $envioExitoso = false;
                $datos["sendemail"] = $envioExitoso;
            }
        }//cierre de insert exitoso 
            // Actualizar la base de datos con el estado del envío
            if ($envioExitoso && $insertReg =="ok") {
                // var_dump($datos);
                $updateRegister = ModelRegister::mdlUpdateRegister($datos);
                var_dump($updateRegister);
                
                echo " El estado del envío se ha actualizado en la base de datos.";
            } else {
                echo " No se pudo enviar el correo. Verifica la configuración.";
            }
           
        }
        }
       
}
function generarContrasenaSegura($correo) {
    // Extraer la parte antes del @ del correo
    $parteCorreo = strtok($correo, '@');
    
    // Definir conjuntos de caracteres
    $numeros = "0123456789";
    $especiales = "!@#$%^&*()_+";
    $letras = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    
    // Asegurar al menos un número y un carácter especial
    $caracterEspecial = $especiales[rand(0, strlen($especiales) - 1)];
    $numero = $numeros[rand(0, strlen($numeros) - 1)];
    
    // Generar el resto de la contraseña con caracteres aleatorios
    $restoCaracteres = substr(str_shuffle($letras . $numeros . $especiales), 0, 6);
    
    // Combinar todo
    $contrasena = $caracterEspecial . $numero . $restoCaracteres;
    
    // Mezclar la contraseña para mayor seguridad
    $contrasena = str_shuffle($contrasena);
    
    // Asegurar que la contraseña tenga exactamente 8 caracteres
    $contrasena = substr($contrasena, 0, 8);
    
    return $contrasena;
}