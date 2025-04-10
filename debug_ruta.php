<?php
// Archivo para depurar el acceso a rutas

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si hay una ruta especificada
echo "<h2>Depuración de Rutas</h2>";
echo "<p>Ruta solicitada: " . (isset($_GET['ruta']) ? $_GET['ruta'] : 'No hay ruta') . "</p>";

// Verificar estado de sesión
echo "<p>Estado de sesión: " . (isset($_SESSION['iniciarSesion']) && $_SESSION['iniciarSesion'] == 'ok' ? 'Sesión iniciada' : 'Sin sesión') . "</p>";

// Verificar existencia del archivo rh_personas.php
$archivo_rh_personas = __DIR__ . '/view/modules/rh_personas.php';
echo "<p>Archivo rh_personas.php: " . (file_exists($archivo_rh_personas) ? 'Existe' : 'No existe') . "</p>";
echo "<p>Ruta completa: {$archivo_rh_personas}</p>";

// Mostrar información de sesión
echo "<h3>Variables de sesión:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Mostrar información de GET
echo "<h3>Variables GET:</h3>";
echo "<pre>";
print_r($_GET);
echo "</pre>";
?>