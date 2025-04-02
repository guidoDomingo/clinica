<?php
namespace Api\Core;

/**
 * Mailer Class
 * 
 * Handles email sending functionality for the application
 */
class Mailer
{
    /**
     * Send a welcome email to a newly registered user
     * 
     * @param array $registration The registration data
     * @param array $user The user data
     * @return bool Whether the email was sent successfully
     */
    public static function sendWelcomeEmail($registration, $user)
    {
        $to = $registration['reg_email'];
        $subject = 'Bienvenido a MiClinica - Detalles de su cuenta';
        
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
        
        // Set email headers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: MiClinica <noreply@miclinica.com>\r\n";
        
        // Send email
        // In a production environment, you would use a proper email library or service
        // For now, we'll use PHP's mail function as a placeholder
        if (mail($to, $subject, $body, $headers)) {
            return true;
        }
        
        return false;
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
        $to = $email;
        $subject = 'MiClinica - Restablecimiento de contraseña';
        
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
        
        // Set email headers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: MiClinica <noreply@miclinica.com>\r\n";
        
        // Send email
        if (mail($to, $subject, $body, $headers)) {
            return true;
        }
        
        return false;
    }
}