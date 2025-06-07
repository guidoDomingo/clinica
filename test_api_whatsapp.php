<?php
/**
 * Script para diagnosticar conexión con la API de WhatsApp
 */

// Habilitar todos los errores para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de la API
$apiUrl = 'http://aventisdev.com:8082/media.php';
$username = 'admin';
$password = '1234';

echo "<h1>Diagnóstico de conexión a API WhatsApp</h1>";

// Verificar si está instalado cURL
if (!function_exists('curl_version')) {
    echo "<p style='color: red;'>ERROR: cURL no está instalado en este servidor. Se requiere para usar la API.</p>";
    exit;
} else {
    echo "<p style='color: green;'>OK: cURL está instalado.</p>";
}

// Función para probar la conexión
function testApiConnection() {
    global $apiUrl, $username, $password;
    
    echo "<h2>Intentando conexión a: {$apiUrl}</h2>";
    
    // Inicializar cURL
    $ch = curl_init($apiUrl);
    
    // Configurar la solicitud cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true); // Solo obtener encabezados
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    // Para depuración, ver información completa
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    
    // Permitir usar HTTPS sin verificar certificado (para entornos de desarrollo)
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
    // Ejecutar la solicitud
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);
    
    // Cerrar la sesión cURL
    curl_close($ch);
    
    // Mostrar resultados
    echo "<h3>Resultado de la prueba de conexión:</h3>";
    
    if ($response === false) {
        echo "<p style='color: red;'>ERROR: No se pudo conectar a la API. Error: {$error}</p>";
    } else {
        echo "<p style='color: green;'>Conexión establecida. Código HTTP: {$httpCode}</p>";
        
        if ($httpCode >= 400) {
            echo "<p style='color: orange;'>ADVERTENCIA: La API devolvió un código de error HTTP.</p>";
        }
    }
    
    echo "<h3>Detalles de la solicitud:</h3>";
    echo "<pre>" . htmlspecialchars($headerSent) . "</pre>";
    
    return ($httpCode > 0 && $httpCode < 400);
}

// Función para probar el envío de un PDF
function testSendPdf($telefono) {
    global $apiUrl, $username, $password;
    
    // Usar una URL de prueba pública para PDF
    $pdfUrl = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
    
    echo "<h2>Intentando enviar PDF de prueba a: {$telefono}</h2>";
    
    // Preparar los datos para la solicitud
    $postData = [
        'telefono' => $telefono,
        'mediaUrl' => $pdfUrl,
        'mediaCaption' => 'Prueba de envío de PDF desde la Clínica'
    ];
    
    echo "<h3>Datos a enviar:</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($postData, JSON_PRETTY_PRINT)) . "</pre>";
    
    // Inicializar cURL
    $ch = curl_init($apiUrl);
    
    // Configurar la solicitud cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ]);
    
    // Para depuración, ver información completa
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    
    // Permitir usar HTTPS sin verificar certificado (para entornos de desarrollo)
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
    // Ejecutar la solicitud
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT);
    
    // Cerrar la sesión cURL
    curl_close($ch);
    
    // Mostrar resultados
    echo "<h3>Resultado del envío:</h3>";
    
    if ($response === false) {
        echo "<p style='color: red;'>ERROR: No se pudo enviar el PDF. Error: {$error}</p>";
    } else {
        echo "<p style='color: green;'>Respuesta recibida. Código HTTP: {$httpCode}</p>";
        
        if ($httpCode >= 400) {
            echo "<p style='color: orange;'>ADVERTENCIA: La API devolvió un código de error HTTP.</p>";
        }
        
        echo "<h4>Respuesta de la API:</h4>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        // Intentar decodificar la respuesta JSON
        $responseData = json_decode($response, true);
        if ($responseData !== null) {
            echo "<h4>Respuesta JSON decodificada:</h4>";
            echo "<pre>" . htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT)) . "</pre>";
        }
    }
    
    echo "<h3>Detalles de la solicitud enviada:</h3>";
    echo "<pre>" . htmlspecialchars($headerSent) . "</pre>";
}

// Ejecutar prueba de conexión
$connectionOk = testApiConnection();

// Mostrar formulario para probar envío
echo "<h2>Prueba de envío de PDF por WhatsApp</h2>";

if (isset($_POST['telefono'])) {
    $telefono = trim($_POST['telefono']);
    if (!empty($telefono)) {
        testSendPdf($telefono);
    } else {
        echo "<p style='color: red;'>ERROR: Por favor ingrese un número de teléfono.</p>";
    }
}

?>
<form method="post" style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
    <div style="margin-bottom: 15px;">
        <label for="telefono" style="display: block; margin-bottom: 5px; font-weight: bold;">Teléfono (formato internacional sin +):</label>
        <input type="text" name="telefono" id="telefono" style="padding: 8px; width: 300px;" placeholder="Ejemplo: 595982313358" value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
    </div>
    
    <div>
        <button type="submit" style="padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer;">Probar envío de PDF</button>
    </div>
</form>

<h2>Documentación de la API</h2>
<p>Asegúrese de que la documentación de la API coincide con estos parámetros:</p>
<ul>
    <li><strong>URL:</strong> <?php echo htmlspecialchars($apiUrl); ?></li>
    <li><strong>Método:</strong> POST</li>
    <li><strong>Autenticación:</strong> Basic Auth (Username: <?php echo htmlspecialchars($username); ?>, Password: <?php echo htmlspecialchars($password); ?>)</li>
    <li><strong>Content-Type:</strong> application/x-www-form-urlencoded</li>
    <li><strong>Parámetros:</strong>
        <ul>
            <li><strong>telefono:</strong> Número de teléfono (formato internacional sin +)</li>
            <li><strong>mediaUrl:</strong> URL pública del PDF a enviar</li>
            <li><strong>mediaCaption:</strong> Texto descriptivo para el PDF</li>
        </ul>
    </li>
</ul>

<p>Nota: Es posible que necesite actualizar la documentación o la implementación si los parámetros han cambiado.</p>
