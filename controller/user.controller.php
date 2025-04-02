<?php
class ControllerUser {
    static public function ctrLoginUser() {
        // Verifica si la sesión ya ha sido iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica si los datos fueron enviados via POST
        if (isset($_POST['usuario']) && isset($_POST['password'])) {
            // Sanitiza los datos de entrada
            $usuario = strip_tags($_POST['usuario']); // Elimina etiquetas HTML/PHP
            $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8'); // Convierte caracteres especiales

            // Verifica las credenciales (aquí debes consultar la base de datos)
            if ($usuario === '1' && password_verify($password, password_hash('1', PASSWORD_DEFAULT))) {
                $_SESSION["iniciarSesion"] = "ok";
                $_SESSION["perfil_user"] = "ADMIN";
                echo '<script> window.location = "home"; </script>';
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
        }
    }
}