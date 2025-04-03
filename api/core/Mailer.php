<?php
namespace Api\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Mailer Class
 * 
 * Handles email sending functionality for the application using PHPMailer with Mailtrap
 */
class Mailer
{
    private static function getMailer()
    {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = '403823a30f75f1'; // Replace with your Mailtrap username
            $mail->Password = 'dd01ed75f12dbf'; // Replace with your Mailtrap password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 2525;
            $mail->CharSet = 'UTF-8';
            
            // Default sender
            $mail->setFrom('noreply@miclinica.com', 'MiClinica');
            
            return $mail;
        } catch (Exception $e) {
            error_log("Mailer Error: {$e->getMessage()}");
            return null;
        }
    }
    
    /**
     * Send a welcome email to a newly registered user
     * 
     * @param array $registration The registration data
     * @param array $user The user data
     * @return bool Whether the email was sent successfully
     */
    public static function sendWelcomeEmail($registration, $user)
    {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($registration['reg_email']);
            $mail->Subject = 'Bienvenido a MiClinica - Detalles de su cuenta';
            
            // Create email body
            $body = "<html><body>";
            $body .= "<h2>¡Bienvenido a MiClinica!</h2>";
            $body .= "<p>Estimado/a {$registration['reg_name']} {$registration['reg_lastname']},</p>";
            $body .= "<p>Su cuenta ha sido creada exitosamente. A continuación, encontrará sus credenciales de acceso:</p>";
            $body .= "<p><strong>Usuario:</strong> {$user['user_email']}</p>";
            $body .= "<p><strong>Contraseña:</strong> {$user['user_pass']}</p>";
            $body .= "<p>Por favor, cambie su contraseña después del primer inicio de sesión por motivos de seguridad.</p>";
            $body .= "<p>Gracias por registrarse en nuestro sistema.</p>";
            $body .= "<p>Atentamente,<br>El equipo de MiClinica</p>";
            $body .= "</body></html>";
            
            $mail->isHTML(true);
            $mail->Body = $body;
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: {$e->getMessage()}");
            return false;
        }
    }
    
    /**
     * Send a password reset email
     * 
     * @param string $email The recipient email
     * @param string $resetToken The password reset token
     * @return bool Whether the email was sent successfully
     */
    public static function sendPasswordResetEmail($email, $resetToken)
    {
        $mail = self::getMailer();
        if (!$mail) return false;
        
        try {
            $mail->addAddress($email);
            $mail->Subject = 'MiClinica - Restablecimiento de contraseña';
            
            // Create reset URL
            $resetUrl = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?token={$resetToken}";
            
            // Create email body
            $body = "<html><body>";
            $body .= "<h2>Restablecimiento de contraseña</h2>";
            $body .= "<p>Ha solicitado restablecer su contraseña. Haga clic en el siguiente enlace para crear una nueva contraseña:</p>";
            $body .= "<p><a href='{$resetUrl}'>{$resetUrl}</a></p>";
            $body .= "<p>Si no solicitó este restablecimiento, puede ignorar este correo electrónico.</p>";
            $body .= "<p>Atentamente,<br>El equipo de MiClinica</p>";
            $body .= "</body></html>";
            
            $mail->isHTML(true);
            $mail->Body = $body;
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: {$e->getMessage()}");
            return false;
        }
    }
}