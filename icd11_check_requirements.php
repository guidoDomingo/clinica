<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Requisitos ICD-11 API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .status {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .status-ok {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .status-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }
        .status-error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .instructions {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        code {
            background-color: #f8f9fa;
            border: 1px solid #eee;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: Consolas, Monaco, 'Andale Mono', monospace;
        }
        pre {
            background-color: #f8f9fa;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verificación de Requisitos para ICD-11 API</h1>
        
        <?php
        // Verificar la extensión cURL
        $curlEnabled = function_exists('curl_init');
        
        // Verificar la versión de PHP
        $phpVersion = PHP_VERSION;
        $phpOk = version_compare($phpVersion, '7.2.0') >= 0;
          // Verificar allow_url_fopen (como alternativa a cURL)
        $allowUrlFopen = ini_get('allow_url_fopen');
        
        // Verificar soporte HTTPS
        $httpsAvailable = in_array('https', stream_get_wrappers());
        $openssl = extension_loaded('openssl');
        
        // Verificar los permisos de escritura en los directorios relevantes
        $ajaxDirWritable = is_writable(__DIR__ . '/ajax');
        $cacheDirWritable = is_dir(__DIR__ . '/cache') && is_writable(__DIR__ . '/cache');
        
        // Verificar disponibilidad de JSON
        $jsonAvailable = function_exists('json_encode') && function_exists('json_decode');
        
        // Determinar el estado general
        if (!$httpsAvailable) {
            $overallStatus = 'error';
            $statusMessage = 'ERROR CRÍTICO: El soporte HTTPS no está habilitado. La API ICD-11 requiere conexiones HTTPS. Habilite la extensión OpenSSL en php.ini.';
        } 
        else if ($curlEnabled) {
            $overallStatus = 'ok';
            $statusMessage = 'Todos los requisitos necesarios están cumplidos para usar la API ICD-11.';
        } 
        else if ($allowUrlFopen) {
            $overallStatus = 'warning';
            $statusMessage = 'La extensión cURL no está disponible, pero allow_url_fopen está habilitado. Se usará un modo alternativo con funcionalidad limitada.';
        } 
        else {
            $overallStatus = 'error';
            $statusMessage = 'No se puede conectar a la API ICD-11. Falta la extensión cURL y allow_url_fopen está deshabilitado.';
        }
        ?>
          <div class="status status-<?php echo $overallStatus; ?>">
            <strong>Estado: <?php echo !$httpsAvailable ? 'ERROR CRÍTICO' : ($curlEnabled ? 'OK' : ($allowUrlFopen ? 'Limitado' : 'No disponible')); ?></strong>
            <p><?php echo $statusMessage; ?></p>
            
            <?php if (!$httpsAvailable): ?>
            <div class="instructions">
                <h3>Cómo habilitar HTTPS en PHP</h3>
                <ol>
                    <li>Abra su archivo <code>php.ini</code> (generalmente en <code><?php echo php_ini_loaded_file(); ?></code>)</li>
                    <li>Busque la línea <code>;extension=openssl</code> y quite el punto y coma del inicio (descomente)</li>
                    <li>Reinicie su servidor web (Apache, Nginx, etc.)</li>
                    <li>Si está usando XAMPP, Laragon u otro paquete, puede habilitar OpenSSL desde el panel de control de extensiones</li>
                </ol>
                <p><strong>Nota:</strong> Sin soporte HTTPS habilitado, no es posible conectarse a la API ICD-11, que requiere conexiones seguras.</p>
            </div>
            <?php endif; ?>
        </div>
        
        <h2>Resultados de la verificación</h2>
        
        <table>
            <tr>
                <th>Requisito</th>
                <th>Estado</th>
                <th>Detalles</th>
            </tr>
            <tr>
                <td>Extensión cURL</td>
                <td><?php echo $curlEnabled ? '✅ Habilitado' : '❌ No disponible'; ?></td>
                <td>Necesaria para conexiones a la API externa</td>
            </tr>
            <tr>
                <td>Versión PHP</td>
                <td><?php echo $phpOk ? '✅ Compatible' : '❌ Desactualizado'; ?></td>
                <td>Versión actual: <?php echo $phpVersion; ?> (Recomendada: 7.2 o superior)</td>
            </tr>            <tr>
                <td>allow_url_fopen</td>
                <td><?php echo $allowUrlFopen ? '✅ Habilitado' : '❌ Deshabilitado'; ?></td>
                <td>Alternativa a cURL para conexiones simples</td>
            </tr>
            <tr>
                <td>Soporte HTTPS</td>
                <td><?php echo $httpsAvailable ? '✅ Habilitado' : '❌ <strong>CRÍTICO: No disponible</strong>'; ?></td>
                <td><?php 
                    if ($httpsAvailable) {
                        echo 'Disponible para conexiones seguras';
                    } else {
                        echo '<strong>La API requiere conexiones HTTPS. Por favor habilite la extensión OpenSSL en php.ini</strong>';
                    }
                ?></td>
            </tr>
            <tr>
                <td>OpenSSL</td>
                <td><?php echo $openssl ? '✅ Habilitado' : '❌ No disponible'; ?></td>
                <td>Necesario para conexiones seguras HTTPS</td>
            </tr>
            <tr>
                <td>JSON</td>
                <td><?php echo $jsonAvailable ? '✅ Disponible' : '❌ No disponible'; ?></td>
                <td>Necesario para procesar respuestas de la API</td>
            </tr>
            <tr>
                <td>Permisos de escritura en /ajax</td>
                <td><?php echo $ajaxDirWritable ? '✅ OK' : '❌ Sin permisos'; ?></td>
                <td>Necesario para almacenamiento de archivos temporales</td>
            </tr>
            <tr>
                <td>Permisos de escritura en /cache</td>
                <td><?php echo $cacheDirWritable ? '✅ OK' : '❌ Sin permisos'; ?></td>
                <td>Necesario para caché de resultados</td>
            </tr>
        </table>
        
        <?php if (!$curlEnabled): ?>
            <div class="instructions">
                <h3>Instrucciones para habilitar cURL</h3>
                <p>La extensión cURL de PHP es necesaria para conectarse correctamente a la API de ICD-11. Siga estos pasos para habilitarla:</p>
                
                <h4>Para Windows (XAMPP, WAMP, Laragon):</h4>
                <ol>
                    <li>Localice su archivo <code>php.ini</code> (típicamente en la carpeta de instalación de PHP)</li>
                    <li>Busque la línea <code>;extension=curl</code></li>
                    <li>Quite el punto y coma del inicio (descomentarla): <code>extension=curl</code></li>
                    <li>Guarde el archivo y reinicie su servidor web</li>
                </ol>
                
                <pre>
# En php.ini:
# Cambie esto:
;extension=curl

# Por esto:
extension=curl</pre>
                
                <h4>Para Linux (Ubuntu, Debian):</h4>
                <ol>
                    <li>Ejecute el siguiente comando en terminal:</li>
                </ol>
                <pre>sudo apt-get update
sudo apt-get install php-curl
sudo service apache2 restart</pre>
                
                <h4>Para cPanel o servidores compartidos:</h4>
                <p>Contacte a su proveedor de hosting para solicitar la habilitación de la extensión cURL para PHP.</p>
                
                <p><strong>Nota:</strong> Después de hacer estos cambios, recargue esta página para verificar si cURL ya está habilitado.</p>
            </div>
        <?php endif; ?>
        
        <h2>Probar conexión con API ICD-11</h2>
        <p>Haga clic en el botón para probar la conexión con la API de ICD-11:</p>
        
        <button id="test-api" style="padding: 8px 16px; margin-top: 10px; cursor: pointer;">
            Probar Conexión
        </button>
        
        <div id="test-result" style="margin-top: 20px;"></div>
    </div>
    
    <script>
        document.getElementById('test-api').addEventListener('click', async function() {
            const resultDiv = document.getElementById('test-result');
            resultDiv.innerHTML = '<div style="padding: 10px; background-color: #e9ecef;">Probando conexión...</div>';
            
            try {
                // Crear FormData para la solicitud
                const formData = new FormData();
                formData.append('action', 'searchByCode');
                formData.append('code', 'MD12');
                
                // Probar primero con el endpoint principal
                try {
                    const response = await fetch('ajax/icd11.ajax.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div style="padding: 15px; background-color: #d4edda; color: #155724; border-radius: 5px;">
                                <strong>Conexión exitosa a la API principal!</strong>
                                <pre style="margin-top: 10px; max-height: 200px; overflow-y: auto;">${JSON.stringify(data, null, 2)}</pre>
                            </div>
                        `;
                        return;
                    }
                } catch (error) {
                    console.warn('Error en API principal, probando fallback:', error);
                }
                
                // Si fallamos, probar con el fallback
                const fallbackResponse = await fetch('ajax/icd11_local.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!fallbackResponse.ok) {
                    throw new Error(`Error HTTP en fallback: ${fallbackResponse.status}`);
                }
                
                const fallbackData = await fallbackResponse.json();
                
                resultDiv.innerHTML = `
                    <div style="padding: 15px; background-color: #fff3cd; color: #856404; border-radius: 5px;">
                        <strong>Conexión a API principal fallida, pero modo fallback funcionando:</strong>
                        <pre style="margin-top: 10px; max-height: 200px; overflow-y: auto;">${JSON.stringify(fallbackData, null, 2)}</pre>
                    </div>
                `;
            } catch (error) {
                resultDiv.innerHTML = `
                    <div style="padding: 15px; background-color: #f8d7da; color: #721c24; border-radius: 5px;">
                        <strong>Error de conexión:</strong> ${error.message}
                    </div>
                `;
            }
        });
    </script>
</body>
</html>
