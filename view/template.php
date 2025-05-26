<?php
   if (session_status() === PHP_SESSION_NONE) {
        session_start();
   }


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AdminLTE 3 | Legacy User Menu</title>

    <link rel="stylesheet" href="view/css/custom.css">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="view/plugins/fontawesome-free/css/all.min.css">
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <!-- Theme style -->
    <!-- <link rel="stylesheet" href="view/dist/css/adminlte.min.css"> -->
    <link rel="stylesheet" href="view/dist/css/adminlte.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="view/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="view/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <!-- summernote -->
    <link rel="stylesheet" href="view/plugins/summernote/summernote-bs4.min.css">
    <!-- dropzonejs -->
    <link rel="stylesheet" href="view/plugins/dropzone/min/dropzone.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="view/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="view/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="view/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="view/css/frmConsulta.css">
    <link rel="stylesheet" href="view/css/consultas.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="view/plugins/toastr/toastr.min.css">
    <!-- Tempus Dominus Bootstrap 4 (DateTimePicker) -->
    <link rel="stylesheet" href="view/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">

    <!-- jQuery -->
    <script src="view/plugins/jquery/jquery.min.js"></script>
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="view/dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    <!-- <script src="view/dist/js/demo.js"></script> -->
    <script src="https://kit.fontawesome.com/8faaf42ade.js" crossorigin="anonymous"></script>
    <!-- Select2 -->
    <script src="view/plugins/select2/js/select2.full.min.js"></script>
    <!-- Summernote -->
    <script src="view/plugins/summernote/summernote-bs4.min.js"></script>
    <!-- dropzonejs -->
    <script src="view/plugins/dropzone/min/dropzone.min.js"></script>
    <!-- DataTables & Plugins -->
    <script src="view/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="view/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="view/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="view/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="view/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="view/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
    <!-- SweetAlert2  -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Toastr -->
    <script src="view/plugins/toastr/toastr.min.js"></script>
    <!-- Moment.js -->
    <script src="view/plugins/moment/moment.min.js"></script>
    <!-- Tempus Dominus Bootstrap 4 (DateTimePicker) -->
    <script src="view/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
</head>

<?php 

if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {
    echo '<body class="sidebar-mini layout-navbar-fixed layout-footer-fixed text-sm control-sidebar-slide-open sidebar-collapse layout-fixed" style="height: auto;">';
    echo '<div class="wrapper">'; 
    include "view/nav/navbar.php";
    include "view/nav/sidebar.php"; 
    
    // Verificar si el usuario tiene perfil completo (excepto en la página de perfil)
    $rutaActual = isset($_GET["ruta"]) ? $_GET["ruta"] : "home";
    if ($rutaActual != "perfil" && $rutaActual != "logout") {
        // Incluir el controlador de perfil si no está incluido
        if (!class_exists('ControllerProfile')) {
            require_once "controller/profile.controller.php";
        }
        
        // Verificar si tenemos el estado del perfil en la sesión, si no, consultar la BD
        $hasCompleteProfile = isset($_SESSION['profile_complete']) ? $_SESSION['profile_complete'] : false;
        if (!$hasCompleteProfile) {
            $hasCompleteProfile = ControllerProfile::ctrHasCompleteProfile($_SESSION['user_id']);
            // Guardar el resultado en la sesión para futuras consultas
            $_SESSION['profile_complete'] = $hasCompleteProfile;
        }
        
        if (!$hasCompleteProfile) {
            // Almacenar la ruta original a la que quería acceder el usuario
            $_SESSION['redirect_after_profile'] = $rutaActual;
            
            // Redirigir a la página de perfil con un mensaje claro
            echo '<script>
                Swal.fire({
                    icon: "warning",
                    title: "Perfil incompleto",
                    text: "Para acceder a esta sección del sistema, primero debes completar tu información personal.",
                    confirmButtonText: "Completar perfil",
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    window.location.href = "perfil";
                });
            </script>';
            exit();
        }
    } else if ($rutaActual == "perfil") {
        // Si estamos en la página de perfil, asegurarnos de que el estado se actualizará al salir
        $_SESSION['check_profile_on_next_page'] = true;
    }      // Manejo de páginas con inicio de sesión
    if(isset($_GET["ruta"])){
        if ($_GET["ruta"] == "home" || $_GET["ruta"] == "logout"|| $_GET["ruta"] == "consultas" || $_GET["ruta"] == "personas" || $_GET["ruta"] == "roles" || $_GET["ruta"] == "perfil" || $_GET["ruta"] == "rhpersonas" || $_GET["ruta"] == "preformatos" || $_GET["ruta"] == "agendas" || $_GET["ruta"] == "servicios") {
            include "view/modules/".$_GET["ruta"].".php";
        } else {
            include "view/modules/404.php";
        }
    } else {
        include "view/modules/home.php";
    }
    
    include "view/nav/footer.php";
    echo '</div>'; // Cierre del div wrapper
} else {
    // Manejo de páginas sin iniciar sesión
    if (!isset($_GET["ruta"])) {
        echo '<body class="hold-transition layout-top-nav">';
        echo '<div class="wrapper">'; 
            include "view/nav/web-navbar.php";
                include "view/modules/start.php";
            include "view/nav/web-footer.php";
        echo '</div>'; // Cierre del div wrapper    
    } else if(isset($_GET["ruta"])) {
        if ($_GET["ruta"] == "login") {
            echo '<body class="hold-transition sidebar-mini layout-navbar-fixed sidebar-collapse login-page">';
            include "modules/".$_GET["ruta"].".php";
        } else if($_GET["ruta"] == "register"){
            echo '<body class="hold-transition register-page">';
            include "modules/".$_GET["ruta"].".php";
        }else if ($_GET["ruta"] == "start" || $_GET["ruta"] == "web-servicios") {
            echo '<body class="hold-transition layout-top-nav">';
            echo '<div class="wrapper">'; 
                include "view/nav/web-navbar.php";
                include "modules/".$_GET["ruta"].".php";
                include "view/nav/web-footer.php";
            echo '</div>'; // Cierre del div wrapper
        } else {
            echo '<body class="hold-transition layout-top-nav">';
            echo '<div class="wrapper">';
                include "view/modules/404.php";
            echo '</div>'; // Cierre del div wrapper
        }
    } else {
        echo "página no encontrada";
    }
}
?>

<!-- Template JS (siempre se carga) -->
<script src="view/js/template.js"></script>

<!-- <script src="view/js/check-profile.js"></script> -->

<!-- Cargar JavaScript específico según el módulo activo -->
<?php
// Definir la ruta actual (home por defecto)
$ruta = isset($_GET["ruta"]) ? $_GET["ruta"] : "home";

// Cargar scripts según la página activa
switch ($ruta) {
    case "consultas":
        echo '<script src="view/js/consultas.js"></script>';
        echo '<script src="view/js/cargar_datos.js"></script>';
        break;
        
    case "preformatos":
        // No es necesario incluir el script aquí ya que se incluye directamente en el archivo del módulo
        break;
        
    case "citas":
    case "agendas":
        echo '<script src="view/js/agendas.js"></script>';
        break;
        
    case "archivos":
        echo '<script src="view/js/archivos.js"></script>';
        break;
        
    case "register":
        echo '<script src="view/js/register.js"></script>';
        break;
          case "personas":
    case "rhpersonas":
        echo '<script src="view/js/personas.js"></script>';
        break;
          case "roles":
        echo '<script src="view/js/roles.js"></script>';
        break;
        
    case "servicios":
        // Removed servicios.js inclusion since it's already included in the module file
        break;
        
    // Caso por defecto para el home o páginas que no requieren JS específico
    default:
        break;
}
?>
</body>

</html>