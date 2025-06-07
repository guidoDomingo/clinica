<?php
/**
 * Script para probar directamente la conexión a la API de WhatsApp
 */

// Mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Prueba Directa de API WhatsApp</h1>";

// Verificar que cURL está disponible
if (!function_exists('curl_init')) {
    die("<p style='color:red'>Error: La extensión cURL no está disponible en este servidor.</p>");
}

// Configuración de la API
$apiUrl = 'http://aventisdev.com:8082/media.php';
$username = 'admin';
$password = '1234';

// Datos de prueba
$telefono = isset($_GET['telefono']) ? $_GET['telefono'] : '';
$mediaUrl = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';
$mediaCaption = 'Prueba directa desde PHP';

// Si no hay teléfono, mostrar un formulario
if (empty($telefono)) {
    ?>
    <form method="get">
        <div style="margin-bottom:15px">
            <label for="telefono">Teléfono (formato internacional sin +):</label>
            <input type="text" id="telefono" name="telefono" placeholder="Ejemplo: 595982313358">
        </div>
        <div>
            <button type="submit">Probar API</button>
        </div>
    </form>
    <?php
    exit;
}

echo "<p>Probando API con teléfono: <strong>$telefono</strong></p>";
echo "<p>URL del PDF: <a href='$mediaUrl' target='_blank'>$mediaUrl</a></p>";

// Preparar datos para la solicitud
$postData = [
    'telefono' => $telefono,
    'mediaUrl' => $mediaUrl,
    'mediaCaption' => $mediaCaption
];

echo "<h2>Datos a enviar:</h2>";
echo "<pre>" . htmlspecialchars(json_encode($postData, JSON_PRETTY_PRINT)) . "</pre>";

// Inicializar cURL
$ch = curl_init();

// Configurar cURL
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_VERBOSE => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => "$username:$password",
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: application/json'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 30
]);

// Capturar la salida verbose para depuración
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

// Ejecutar la solicitud
$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Obtener información de depuración (verbose)
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
fclose($verbose);

// Mostrar resultados
echo "<h2>Resultado de la solicitud:</h2>";

if ($error) {
    echo "<p style='color:red'>Error cURL: " . htmlspecialchars($error) . "</p>";
} else {
    echo "<p>Código HTTP: <strong>$httpCode</strong></p>";
    echo "<p>Respuesta:</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Intentar decodificar la respuesta si es JSON
    $responseData = json_decode($response, true);
    if ($responseData !== null) {
        echo "<p>Respuesta decodificada:</p>";
        echo "<pre>" . htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT)) . "</pre>";
    }
}

echo "<h2>Información detallada:</h2>";
echo "<pre>" . htmlspecialchars($verboseLog) . "</pre>";

curl_close($ch);
?>
