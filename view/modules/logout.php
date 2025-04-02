<?php
session_start(); // Iniciar la sesión si no está iniciada
session_destroy(); // Destruir la sesión
// header("Location: ?ruta=login"); // Redirigir al login
echo '<script> window.location = "start"; </script>';
// exit();
?>
// date_default_timezone_set('America/Asuncion');
// $logFile = 'registro.txt';
// $logMessage = 'Cierre de sesión   el ' . date('Y-m-d H:i:s') . "\n";
// // $logMessage = 'Cierre de sesión ' . $_SESSION["legajo_user"] . ' el ' . date('Y-m-d H:i:s') . "\n";
// $fileHandle = fopen($logFile, 'a');
// fwrite($fileHandle, $logMessage);
// fclose($fileHandle);
// session_destroy();
// echo '<script> window.location = "login"; </script>';