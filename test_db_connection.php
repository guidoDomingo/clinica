<?php
// Simple PostgreSQL connection test
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$contrasena = "admin";
$usuario = "postgres";
$nombreBaseDeDatos = "clinica";
$rutaServidor = "localhost";
$puerto = "5432";

echo "Intentando conectar a PostgreSQL...<br>";
echo "Host: $rutaServidor<br>";
echo "Puerto: $puerto<br>";
echo "Base de datos: $nombreBaseDeDatos<br>";
echo "Usuario: $usuario<br>";

try {
    $link = new PDO("pgsql:host=$rutaServidor;port=$puerto;dbname=$nombreBaseDeDatos", $usuario, $contrasena);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<br><strong style='color:green'>¡Conexión exitosa!</strong><br>";
    
    // Probar una consulta simple
    $query = "SELECT version()";
    $stmt = $link->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchColumn();
    echo "<br>Versión de PostgreSQL: $result<br>";
    
    // Probar si existen las tablas necesarias
    $tables = [
        'agendas_cabecera',
        'agendas_detalle',
        'rh_doctors',
        'rh_person'
    ];
    
    echo "<br><strong>Verificando tablas:</strong><br>";
    foreach ($tables as $table) {
        $query = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = :tablename
        ) AS table_exists";
        $stmt = $link->prepare($query);
        $stmt->bindParam(':tablename', $table, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchColumn();
        
        echo "Tabla $table: " . ($result == 't' ? 
            "<span style='color:green'>Existe</span>" : 
            "<span style='color:red'>No existe</span>") . "<br>";
    }
    
} catch (PDOException $e) {
    echo "<br><strong style='color:red'>Error de conexión: " . $e->getMessage() . "</strong><br>";
}
?>
