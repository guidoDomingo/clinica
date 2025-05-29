<?php
/**
 * Script para verificar y habilitar la extensión PostgreSQL en PHP
 */

// Cabecera HTML
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de PostgreSQL</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Verificación de la Extensión PostgreSQL</h1>';

// Verificar si la extensión está cargada
if (extension_loaded('pdo_pgsql')) {
    echo '<div class="alert alert-success">
        <h4>¡La extensión pdo_pgsql está correctamente habilitada!</h4>
        <p>Tu sistema está configurado correctamente para usar PostgreSQL con PHP.</p>
    </div>';
} else {
    echo '<div class="alert alert-danger">
        <h4>La extensión pdo_pgsql no está habilitada</h4>
        <p>Necesitas habilitar la extensión pdo_pgsql en tu configuración de PHP para que la aplicación funcione correctamente.</p>
    </div>';
    
    // Mostrar información de PHP
    echo '<div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h2>Información de PHP</h2>
        </div>
        <div class="card-body">
            <p><strong>Versión de PHP:</strong> ' . phpversion() . '</p>
            <p><strong>Archivo php.ini:</strong> ' . php_ini_loaded_file() . '</p>
        </div>
    </div>';
    
    // Instrucciones para Windows (Laragon)
    echo '<div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h2>Cómo habilitar pdo_pgsql en Windows con Laragon</h2>
        </div>
        <div class="card-body">
            <ol>
                <li>Haz clic derecho en el ícono de Laragon en la bandeja del sistema</li>
                <li>Selecciona <strong>PHP > Extensiones > pdo_pgsql</strong> para habilitarla</li>
                <li>Alternativamente, edita el archivo php.ini:</li>
                <li>Busca <code>extension=pdo_pgsql</code> y quita el punto y coma (;) del inicio si está comentada</li>
                <li>También asegúrate de que <code>extension=pgsql</code> está habilitada</li>
                <li>Guarda los cambios y reinicia Laragon</li>
            </ol>
            <p class="mt-3">
                <a href="?enable_extension=1" class="btn btn-primary">Intentar habilitar automáticamente</a>
            </p>
        </div>
    </div>';
    
    // Código para intentar habilitar la extensión automáticamente
    if (isset($_GET['enable_extension']) && $_GET['enable_extension'] == 1) {
        $php_ini = php_ini_loaded_file();
        
        if ($php_ini && is_writable($php_ini)) {
            $ini_content = file_get_contents($php_ini);
            
            // Buscar y descomentar las extensiones
            $ini_content = str_replace(';extension=pdo_pgsql', 'extension=pdo_pgsql', $ini_content);
            $ini_content = str_replace(';extension=pgsql', 'extension=pgsql', $ini_content);
            
            // Guardar los cambios
            if (file_put_contents($php_ini, $ini_content)) {
                echo '<div class="alert alert-info mt-3">
                    <h4>Cambios aplicados</h4>
                    <p>Se ha modificado el archivo php.ini. Por favor, reinicia Laragon para que los cambios surtan efecto.</p>
                </div>';
            } else {
                echo '<div class="alert alert-danger mt-3">
                    <h4>Error al modificar php.ini</h4>
                    <p>No se pudo escribir en el archivo php.ini. Necesitarás hacer los cambios manualmente.</p>
                </div>';
            }
        } else {
            echo '<div class="alert alert-danger mt-3">
                <h4>No se puede escribir en php.ini</h4>
                <p>El archivo php.ini no se puede modificar automáticamente. Necesitarás hacer los cambios manualmente.</p>
                <p>Ruta del archivo: ' . ($php_ini ?: 'No detectado') . '</p>
            </div>';
        }
    }
}

// Mostrar extensiones PHP cargadas
echo '<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h2>Extensiones PHP cargadas</h2>
    </div>
    <div class="card-body">
        <pre>' . implode(', ', get_loaded_extensions()) . '</pre>
    </div>
</div>';

// Probar conexión a PostgreSQL
echo '<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h2>Prueba de conexión a PostgreSQL</h2>
    </div>
    <div class="card-body">';

if (extension_loaded('pdo_pgsql')) {
    try {
        $contrasena = "admin";
        $usuario = "postgres";
        $nombreBaseDeDatos = "clinica";
        $rutaServidor = "localhost";
        $puerto = "5432";
        
        $link = new PDO("pgsql:host=$rutaServidor;port=$puerto;dbname=$nombreBaseDeDatos", $usuario, $contrasena);
        $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo '<div class="alert alert-success">
            <h4>¡Conexión exitosa!</h4>
            <p>La conexión a la base de datos PostgreSQL fue exitosa.</p>
        </div>';
        
        // Listar tablas para verificar
        $tablas = $link->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name")->fetchAll(PDO::FETCH_COLUMN);
        
        echo '<div class="mt-3">
            <h5>Tablas disponibles:</h5>
            <ul>';
        foreach ($tablas as $tabla) {
            echo '<li>' . htmlspecialchars($tabla) . '</li>';
        }
        echo '</ul>
        </div>';
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">
            <h4>Error de conexión</h4>
            <p>No se pudo conectar a la base de datos PostgreSQL: ' . htmlspecialchars($e->getMessage()) . '</p>
        </div>';
    }
} else {
    echo '<div class="alert alert-warning">
        <h4>Prueba de conexión no disponible</h4>
        <p>La extensión pdo_pgsql no está habilitada, por lo que no se puede realizar la prueba de conexión.</p>
    </div>';
}

echo '</div>
</div>';

// Crear un script para reiniciar el servidor web
echo '<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h2>Reiniciar Laragon</h2>
    </div>
    <div class="card-body">
        <p>Después de habilitar las extensiones, necesitas reiniciar Laragon para que los cambios surtan efecto.</p>
        <p>
            <button onclick="window.location.reload()" class="btn btn-info">Refrescar página después de reiniciar</button>
        </p>
    </div>
</div>';

echo '</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
?>
