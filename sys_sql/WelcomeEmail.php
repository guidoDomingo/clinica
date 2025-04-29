<?php
class WelcomeEmail
{
    private $userName;
    private $startLink;
    private $pass;

    // Constructor para inicializar el nombre del usuario y el enlace de inicio
    public function __construct($userName, $startLink, $pass)
    {
        $this->userName = $userName;
        $this->startLink = $startLink;
        $this->pass = $pass;
    }

    // Método para generar el cuerpo del correo en HTML
    public function getBody()
    {
        return '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Bienvenido a Nuestro Servicio</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }
                .header {
                    text-align: center;
                    padding: 20px 0;
                }
                .header h1 {
                    color: #333333;
                    margin: 0;
                }
                .content {
                    padding: 20px;
                    color: #555555;
                    line-height: 1.6;
                }
                .content h2 {
                    color: #333333;
                }
                .footer {
                    text-align: center;
                    padding: 20px;
                    font-size: 12px;
                    color: #777777;
                }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    margin: 20px 0;
                    font-size: 16px;
                    color: #ffffff;
                    background-color: #007BFF;
                    text-decoration: none;
                    border-radius: 5px;
                }
                .button:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <h1>¡Bienvenido a Nuestro Servicio!</h1>
                </div>
                <div class="content">
                    <h2>Hola ' . htmlspecialchars($this->userName) . ',</h2>
                    <p>Gracias por unirte a nuestra plataforma. Estamos emocionados de tenerte con nosotros y esperamos que disfrutes de todos los beneficios que ofrecemos.</p>
                    <p>Aquí hay algunas cosas que puedes hacer para empezar:</p>
                    <ul>
                        <li>Completa tu perfil para personalizar tu experiencia.</li>
                        <li>Explora nuestras características principales.</li>
                        <li>Consulta nuestro centro de ayuda si tienes alguna pregunta.</li>
                    </ul>
                    <p>Si tienes alguna duda, no dudes en contactarnos. Estamos aquí para ayudarte.</p>
                    <a href="' . htmlspecialchars($this->startLink) . '" class="button">Comenzar Ahora</a>
                    <p>Guarde su contraseña en un lugar seguro.</p>
                    <p>'.$this->pass.'</p>
                </div>
                <div class="footer">
                    <p>Si no te registraste en nuestro servicio, por favor ignora este correo.</p>
                    <p>&copy; 2023 Tu Empresa. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ';
    }
}
?>